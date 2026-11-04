<?php
if (!defined('APP_INIT')) { http_response_code(403); exit(); }
require_once __DIR__ . '/../model/Requerimiento.php';

class RequerimientoController {

    // ==========================================================
    // Configuración de evidencias (imágenes adjuntas)
    // ==========================================================
    // Tipo MIME real (detectado por contenido) => extensión con la que se guarda
    private const EVIDENCIA_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];
    private const EVIDENCIA_MAX_ARCHIVOS = 3;
    private const EVIDENCIA_MAX_BYTES    = 5 * 1024 * 1024; // 5 MB por imagen
    // Formato de nombre: tk_{id_ticket}_{timestamp}_{n}.{ext}
    private const EVIDENCIA_PATRON       = '/^tk_(\d+)_\d+_\d+\.(jpe?g|png|webp)$/i';

    private static function dirUploads(): string {
        return __DIR__ . '/../uploads/';
    }

    // Admin y developer ven todos los tickets; el cliente solo los que él solicitó.
    private function puedeVerTicket(array $ticket): bool {
        $usuario = $_SESSION['usuario'] ?? null;
        if (!$usuario) {
            return false;
        }
        if (in_array(strtolower($usuario['rol']), ['admin', 'developer'], true)) {
            return true;
        }
        return (int)$ticket['id_solicitante'] === (int)$usuario['id'];
    }

    // Normaliza la prioridad recibida; si no es válida devuelve $porDefecto.
    private function normalizarPrioridad($valor, ?string $porDefecto = 'media'): ?string {
        $valor = strtolower(trim((string)$valor));
        return in_array($valor, Requerimiento::PRIORIDADES, true) ? $valor : $porDefecto;
    }

    // Respuesta JSON de error con código HTTP
    private function errorJson(int $codigo, string $mensaje): void {
        http_response_code($codigo);
        echo json_encode(['success' => false, 'message' => $mensaje]);
        exit();
    }

    // Guarda hasta 3 imágenes validando tamaño y tipo real del archivo (no solo la extensión).
    private function guardarEvidencias(int $id_req): void {
        if (empty($_FILES['evidencias']['name'][0])) {
            return;
        }

        $upload_dir = self::dirUploads();
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $total_archivos = min(count($_FILES['evidencias']['name']), self::EVIDENCIA_MAX_ARCHIVOS);

        for ($i = 0; $i < $total_archivos; $i++) {
            if ($_FILES['evidencias']['error'][$i] !== UPLOAD_ERR_OK) { continue; }
            if ($_FILES['evidencias']['size'][$i] > self::EVIDENCIA_MAX_BYTES) { continue; }

            $tmp  = $_FILES['evidencias']['tmp_name'][$i];
            $mime = $finfo->file($tmp);
            if (!isset(self::EVIDENCIA_MIMES[$mime])) { continue; }

            $nombre_archivo = "tk_{$id_req}_" . time() . "_{$i}." . self::EVIDENCIA_MIMES[$mime];
            move_uploaded_file($tmp, $upload_dir . $nombre_archivo);
        }
    }
    
    public function procesarNuevo() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['usuario'])) {
            echo json_encode(['success' => false, 'message' => 'Sesión expirada.']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_solicitante      = $_SESSION['usuario']['id'];
            
            // 1. Capturar el id_proyecto (puede ser null si es un cliente o no lo eligió)
            $id_proyecto         = !empty($_POST['id_proyecto']) ? (int)$_POST['id_proyecto'] : null;
            
            $titulo              = trim($_POST['titulo'] ?? '');
            $prioridad           = trim($_POST['prioridad'] ?? '');
            $descripcion_tecnica = trim($_POST['descripcion_tecnica'] ?? '');

            if (empty($titulo) || empty($prioridad) || empty($descripcion_tecnica)) {
                echo json_encode(['success' => false, 'message' => 'Completa todos los campos obligatorios (*).']);
                exit();
            }

            // Validar que la prioridad coincida con el CHECK constraint de la BD
            $prioridad = $this->normalizarPrioridad($prioridad);

            $reqModel = new Requerimiento();
            
            // 2. Ejecutar pasando los 5 parámetros en el orden EXACTO del modelo
            $id_req = $reqModel->registrar($id_proyecto, $titulo, $descripcion_tecnica, $prioridad, $id_solicitante);

            // 3. Formatear la respuesta JSON según el resultado
            if ($id_req) {
                // Procesar Evidencias Físicas
                $this->guardarEvidencias($id_req);

                echo json_encode([
                    'success' => true, 
                    'message' => 'Requerimiento registrado correctamente.',
                    'id_requerimiento' => $id_req
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Error al registrar el requerimiento en la base de datos.'
                ]);
            }
            exit();
        }
    }

    public function verDetalle() {
        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php?action=login"); exit();
        }

        $id_requerimiento = (int)($_GET['id'] ?? 0);
        if (!$id_requerimiento) {
            header("Location: index.php?action=dashboard"); exit();
        }

        $reqModel = new Requerimiento();
        $ticket = $reqModel->obtenerDetalle($id_requerimiento);

        // Un cliente solo puede ver sus propios tickets. Se responde igual que
        // si no existiera para no revelar qué IDs son válidos.
        if (!$ticket || !$this->puedeVerTicket($ticket)) {
            http_response_code(404);
            echo "El ticket no existe."; exit();
        }

        $subtareas = $reqModel->listarSubtareas($id_requerimiento);
        $comentarios = $reqModel->listarComentarios($id_requerimiento);
        $tecnicos = $reqModel->listarTecnicos();
        
        require_once __DIR__ . '/../view/ticket_detalle.php';
    }

    // Sirve una imagen de evidencia solo si el usuario puede ver el ticket al que pertenece.
    // La carpeta uploads/ está bloqueada para acceso directo (uploads/.htaccess).
    public function verEvidencia() {
        $archivo = basename((string)($_GET['archivo'] ?? ''));

        if (!preg_match(self::EVIDENCIA_PATRON, $archivo, $coincidencias)) {
            http_response_code(404); exit();
        }

        $ruta = self::dirUploads() . $archivo;
        if (!is_file($ruta)) {
            http_response_code(404); exit();
        }

        $reqModel = new Requerimiento();
        $ticket = $reqModel->obtenerDetalle((int)$coincidencias[1]);
        if (!$ticket || !$this->puedeVerTicket($ticket)) {
            http_response_code(404); exit();
        }

        // Enviar el tipo real del archivo; si no es una imagen permitida, no se sirve
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($ruta);
        if (!isset(self::EVIDENCIA_MIMES[$mime])) {
            http_response_code(404); exit();
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($ruta));
        header('Content-Disposition: inline; filename="' . $archivo . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=3600');
        readfile($ruta);
        exit();
    }

    // Procesar el formulario de nuevo ticket
    public function guardarTicket() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reqModel = new Requerimiento();
            
            // Capturar el proyecto de forma opcional
            $id_proyecto = !empty($_POST['id_proyecto']) ? (int)$_POST['id_proyecto'] : null;
            
            $titulo = $_POST['titulo'] ?? '';
            $descripcion = $_POST['descripcion_tecnica'] ?? '';
            $prioridad = $this->normalizarPrioridad($_POST['prioridad'] ?? 'media');
            $id_responsable = !empty($_POST['id_responsable']) ? (int)$_POST['id_responsable'] : null;
            $subtareas = $_POST['subtareas'] ?? []; 
            $subtareas_resp = $_POST['subtareas_responsable'] ?? [];
            $id_solicitante = $_SESSION['usuario']['id'];

            if (!empty(trim($titulo)) && !empty(trim($descripcion))) {
                
                // 1. Usar tu función 'registrar' con id_proyecto
                $id_req = $reqModel->registrar($id_proyecto, $titulo, $descripcion, $prioridad, $id_solicitante);
                
                if ($id_req) {
                    
                    // 2. Usar tu función 'asignarResponsableTicket'
                    if ($id_responsable) {
                        $reqModel->asignarResponsableTicket($id_req, $id_responsable);
                    }

                    // 3. Usar tu función 'insertarSubtarea'
                    foreach ((array)$subtareas as $idx => $subtarea) {
                        if (!empty(trim($subtarea))) {
                            $id_resp_sub = !empty($subtareas_resp[$idx]) ? (int)$subtareas_resp[$idx] : null;
                            $reqModel->insertarSubtarea($id_req, $subtarea, $id_resp_sub);
                        }
                    }

                    // 4. Procesar Evidencias (Límite 3 imágenes)
                    $this->guardarEvidencias($id_req);

                    header("Location: index.php?action=dashboard");
                    exit();
                }
            }
        }

        // Datos incompletos o error al guardar: volver al formulario en vez de mostrar una página en blanco
        header("Location: index.php?action=nuevo_ticket&error=1");
        exit();
    }

    // Mostrar el formulario para crear un nuevo ticket
    public function nuevoTicket() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        $reqModel = new Requerimiento();
        
        // Usamos tu función original para obtener admins y developers
        $tecnicos = $reqModel->listarTecnicos(); 
        
        require_once __DIR__ . '/../view/nuevo_ticket.php';
    }

    // Funciones AJAX para interactuar con la vista de detalle
    public function agregarSubtarea() {
        header('Content-Type: application/json');
        $id_req = (int)($_POST['id_requerimiento'] ?? 0);
        $desc = trim($_POST['descripcion'] ?? '');
        $id_resp = !empty($_POST['id_responsable']) ? (int)$_POST['id_responsable'] : null;
        
        if($id_req && $desc) {
            $reqModel = new Requerimiento();
            $res = $reqModel->insertarSubtarea($id_req, $desc, $id_resp);
            echo json_encode(['success' => $res]);
        } else { echo json_encode(['success' => false]); }
        exit();
    }

    // AJAX: Asignar o cambiar el responsable de una subtarea
    public function asignarResponsableSubtarea() {
        header('Content-Type: application/json');
        $id_sub = (int)($_POST['id_subtarea'] ?? 0);
        $id_usr = !empty($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : null;

        if (!$id_sub) {
            $this->errorJson(400, 'Falta el ID de la subtarea.');
        }

        $reqModel = new Requerimiento();
        $res = $reqModel->asignarResponsableSubtarea($id_sub, $id_usr);
        echo json_encode(['success' => $res]);
        exit();
    }

    public function agregarComentario() {
        header('Content-Type: application/json');
        $id_req = (int)($_POST['id_requerimiento'] ?? 0);
        $msg = trim($_POST['mensaje'] ?? '');
        $id_usr = $_SESSION['usuario']['id'] ?? '';
        
        if($id_req && $msg && $id_usr) {
            $reqModel = new Requerimiento();
            // Un cliente solo puede comentar en sus propios tickets
            $ticket = $reqModel->obtenerDetalle($id_req);
            if (!$ticket || !$this->puedeVerTicket($ticket)) {
                $this->errorJson(403, 'No tienes acceso a este ticket.');
            }

            $res = $reqModel->insertarComentario($id_req, $id_usr, $msg);
            echo json_encode(['success' => $res]);
        } else { echo json_encode(['success' => false]); }
        exit();
    }

    // AJAX: Cambiar estado (y opcionalmente prioridad) de un ticket.
    // Si no se envía prioridad, se conserva la actual (lo usa el Kanban).
    public function actualizarEstado() {
        header('Content-Type: application/json');
        $id_req    = (int)($_POST['id_requerimiento'] ?? 0);
        $estado    = strtolower(trim($_POST['estado'] ?? ''));
        $prioridad = trim($_POST['prioridad'] ?? '');

        if (!$id_req) {
            $this->errorJson(400, 'Falta el ID del ticket.');
        }
        if (!in_array($estado, Requerimiento::ESTADOS, true)) {
            $this->errorJson(422, 'Estado no válido.');
        }

        $prioridadFinal = null;
        if ($prioridad !== '') {
            $prioridadFinal = $this->normalizarPrioridad($prioridad, null);
            if ($prioridadFinal === null) {
                $this->errorJson(422, 'Prioridad no válida.');
            }
        }

        $reqModel = new Requerimiento();
        $res = $reqModel->actualizarEstadoTicket($id_req, $estado, $prioridadFinal);
        echo json_encode(['success' => $res]);
        exit();
    }

    // AJAX: Asignar Responsable
    public function asignarResponsable() {
        header('Content-Type: application/json');
        $id_req = (int)($_POST['id_requerimiento'] ?? 0);
        $id_usr = (int)($_POST['id_usuario'] ?? 0);
        
        if($id_req && $id_usr) {
            $reqModel = new Requerimiento();
            $res = $reqModel->asignarResponsableTicket($id_req, $id_usr);
            echo json_encode(['success' => $res]);
        } else { echo json_encode(['success' => false]); }
        exit();
    }

    // AJAX: Marcar Subtarea
    public function marcarSubtarea() {
        header('Content-Type: application/json');
        $id_sub = (int)($_POST['id_subtarea'] ?? 0);
        $estado = strtolower(trim($_POST['estado'] ?? 'pendiente'));
        
        if (!$id_sub) {
            $this->errorJson(400, 'Falta el ID de la subtarea');
        }
        if (!in_array($estado, Requerimiento::ESTADOS_SUBTAREA, true)) {
            $this->errorJson(422, 'Estado de subtarea no válido.');
        }

        $reqModel = new Requerimiento();
        $res = $reqModel->actualizarEstadoSubtarea($id_sub, $estado);
        echo json_encode(['success' => $res]);
        exit();
    }

    public function verKanban() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] === 'cliente') {
            header("Location: index.php?action=dashboard");
            exit();
        }

        $reqModel = new Requerimiento();
        $todosTickets = $reqModel->listarParaKanban();

        // Notificaciones para la campana del encabezado
        $notificaciones = $reqModel->obtenerNotificacionesTecnicos();

        // Agrupar requerimientos en los tres contenedores Kanban
        $kanban = [
            'pendiente'  => [],
            'en proceso' => [],
            'terminado'  => []
        ];

        foreach ($todosTickets as $t) {
            $est = strtolower($t['estado']);
            if (array_key_exists($est, $kanban)) {
                $kanban[$est][] = $t;
            } else {
                $kanban['pendiente'][] = $t;
            }
        }

        require_once __DIR__ . '/../view/kanban.php';
    }

    // Tickets donde el usuario logueado es el responsable
    public function misAsignaciones() {
        $usuario = $_SESSION['usuario'];
        $reqModel = new Requerimiento();

        $asignaciones = $reqModel->listarAsignadosA($usuario['id']);
        $notificaciones = $reqModel->obtenerNotificacionesTecnicos();

        $resumen = ['total' => count($asignaciones), 'pendiente' => 0, 'en proceso' => 0, 'terminado' => 0];
        foreach ($asignaciones as $a) {
            $est = strtolower($a['estado']);
            if (isset($resumen[$est])) { $resumen[$est]++; }
        }

        require_once __DIR__ . '/../view/mis_asignaciones.php';
    }

    public function exportarExcel() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        // Validación de seguridad: Solo Admin puede exportar
        if (!isset($_SESSION['usuario']) || strtolower($_SESSION['usuario']['rol']) !== 'admin') {
            header("Location: index.php?action=dashboard");
            exit();
        }

        // Cargar los datos desde el modelo
        $reqModel = new Requerimiento();
        $requerimientos = $reqModel->listarTodos();

        // Cargar PhpSpreadsheet
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Requerimientos');

        // Definir Cabeceras
        $cabeceras = ['A1' => 'ID Ticket', 'B1' => 'Proyecto', 'C1' => 'Título', 'D1' => 'Prioridad', 'E1' => 'Estado', 'F1' => 'Solicitante', 'G1' => 'Responsable Asignado', 'H1' => 'Fecha de Creación'];
        foreach ($cabeceras as $celda => $texto) {
            $sheet->setCellValue($celda, $texto);
            $sheet->getStyle($celda)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($celda)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF312E81');
        }

        $fila = 2;
        foreach ($requerimientos as $req) {
            $fecha = isset($req['fecha_creacion']) ? date('d/m/Y H:i', strtotime($req['fecha_creacion'])) : 'N/A';
            $sheet->setCellValue('A' . $fila, '#TK-' . $req['id_requerimiento']);
            $sheet->setCellValue('B' . $fila, empty($req['proyecto']) ? 'Sin Proyecto' : $req['proyecto']);
            $sheet->setCellValue('C' . $fila, $req['titulo']);
            $sheet->setCellValue('D' . $fila, strtoupper($req['prioridad']));
            $sheet->setCellValue('E' . $fila, strtoupper($req['estado']));
            $sheet->setCellValue('F' . $fila, $req['solicitante']);
            $sheet->setCellValue('G' . $fila, empty($req['responsable']) ? 'Sin Asignar' : $req['responsable']);
            $sheet->setCellValue('H' . $fila, $fecha);
            $fila++;
        }

        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Tickets_General_PosPeru.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    public function exportarExcelProyectos() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['usuario']) || strtolower($_SESSION['usuario']['rol']) !== 'admin') {
            header("Location: index.php?action=dashboard");
            exit();
        }

        $reqModel = new Requerimiento();
        $proyectos = $reqModel->obtenerKpiProyectos();
        $requerimientos = $reqModel->listarTodos();

        // Ordenar proyectos priorizando los que requieren más ayuda (más pendientes/en proceso y urgentes)
        usort($proyectos, function ($a, $b) {
            $activosA = (int)$a['pendientes'] + (int)$a['en_proceso'];
            $activosB = (int)$b['pendientes'] + (int)$b['en_proceso'];
            if ($activosB !== $activosA) return $activosB <=> $activosA;
            return ((int)($b['urgentes_activos'] ?? 0)) <=> ((int)($a['urgentes_activos'] ?? 0));
        });

        require_once __DIR__ . '/../vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // ==========================================================
        // HOJA 1: Resumen y Diagnóstico por Proyecto
        // ==========================================================
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Diagnóstico Proyectos');

        $cabeceras = [
            'A1' => 'ID Proyecto',
            'B1' => 'Proyecto',
            'C1' => 'Técnicos en Proyecto',
            'D1' => 'Total Tickets',
            'E1' => 'Pendientes',
            'F1' => 'En Proceso',
            'G1' => 'Terminados',
            'H1' => 'Críticos / Alta (Activos)',
            'I1' => '% Avance',
            'J1' => 'Diagnóstico / Nivel de Ayuda'
        ];
        foreach ($cabeceras as $celda => $texto) {
            $sheet->setCellValue($celda, $texto);
            $sheet->getStyle($celda)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($celda)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF4C1D95');
        }

        $fila = 2;
        foreach ($proyectos as $p) {
            $total      = (int)$p['total_tickets'];
            $pendientes = (int)$p['pendientes'];
            $enProceso  = (int)$p['en_proceso'];
            $terminados = (int)$p['terminados'];
            $urgentes   = (int)($p['urgentes_activos'] ?? 0);
            $numTec     = (int)($p['total_miembros'] ?? 0);
            $activos    = $pendientes + $enProceso;
            $avance     = $total > 0 ? round(($terminados / $total) * 100, 1) . '%' : '0%';

            // Determinar qué proyecto requiere más ayuda
            if ($activos === 0) {
                $diagnostico = 'AL DÍA / SIN PENDIENTES';
                $colorFondo  = 'FFD1FAE5'; // Verde claro
            } elseif ($urgentes >= 2 || $pendientes >= 3 || ($activos > 0 && $numTec === 0)) {
                $diagnostico = 'REQUIERE APOYO URGENTE';
                $colorFondo  = 'FFFEE2E2'; // Rojo claro
            } elseif ($activos >= 2 || $urgentes === 1) {
                $diagnostico = 'CARGA MEDIA / MONITOREAR';
                $colorFondo  = 'FFFEF3C7'; // Ámbar claro
            } else {
                $diagnostico = 'CARGA CONTROLADA';
                $colorFondo  = 'FFE0E7FF'; // Azul claro
            }

            $miembrosTxt = !empty($p['miembros']) ? $p['miembros'] . " ({$numTec})" : 'Sin miembros asignados (0)';

            $sheet->setCellValue('A' . $fila, '#PR-' . $p['id_proyecto']);
            $sheet->setCellValue('B' . $fila, $p['nombre_proyecto']);
            $sheet->setCellValue('C' . $fila, $miembrosTxt);
            $sheet->setCellValue('D' . $fila, $total);
            $sheet->setCellValue('E' . $fila, $pendientes);
            $sheet->setCellValue('F' . $fila, $enProceso);
            $sheet->setCellValue('G' . $fila, $terminados);
            $sheet->setCellValue('H' . $fila, $urgentes);
            $sheet->setCellValue('I' . $fila, $avance);
            $sheet->setCellValue('J' . $fila, $diagnostico);

            $sheet->getStyle('J' . $fila)->getFont()->setBold(true);
            $sheet->getStyle('J' . $fila)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB($colorFondo);

            $fila++;
        }

        // Contabilizar tickets sin proyecto para que el Admin también los detecte
        $sinProy = ['total' => 0, 'pendiente' => 0, 'en proceso' => 0, 'terminado' => 0, 'urgentes' => 0];
        foreach ($requerimientos as $r) {
            if (empty($r['proyecto'])) {
                $sinProy['total']++;
                $est = strtolower($r['estado']);
                if (isset($sinProy[$est])) $sinProy[$est]++;
                if (in_array(strtolower($r['prioridad']), ['critica', 'alta'], true) && $est !== 'terminado') {
                    $sinProy['urgentes']++;
                }
            }
        }
        if ($sinProy['total'] > 0) {
            $avanceSin = round(($sinProy['terminado'] / $sinProy['total']) * 100, 1) . '%';
            $sheet->setCellValue('A' . $fila, 'S/P');
            $sheet->setCellValue('B' . $fila, 'Tickets Sin Proyecto Asignado');
            $sheet->setCellValue('C' . $fila, 'N/A');
            $sheet->setCellValue('D' . $fila, $sinProy['total']);
            $sheet->setCellValue('E' . $fila, $sinProy['pendiente']);
            $sheet->setCellValue('F' . $fila, $sinProy['en proceso']);
            $sheet->setCellValue('G' . $fila, $sinProy['terminado']);
            $sheet->setCellValue('H' . $fila, $sinProy['urgentes']);
            $sheet->setCellValue('I' . $fila, $avanceSin);
            $sheet->setCellValue('J' . $fila, ($sinProy['pendiente'] + $sinProy['en proceso'] > 0) ? 'ASIGNAR A UN PROYECTO' : 'COMPLETADOS');
            $sheet->getStyle('A' . $fila . ':J' . $fila)->getFont()->setItalic(true);
        }

        foreach (range('A', 'J') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // ==========================================================
        // HOJA 2: Detalle de Tickets Agrupados por Proyecto
        // ==========================================================
        $sheetDetalle = $spreadsheet->createSheet();
        $sheetDetalle->setTitle('Detalle por Proyecto');

        $cabDetalle = [
            'A1' => 'Proyecto',
            'B1' => 'ID Ticket',
            'C1' => 'Título del Requerimiento',
            'D1' => 'Prioridad',
            'E1' => 'Estado',
            'F1' => 'Responsable Asignado',
            'G1' => 'Solicitante',
            'H1' => 'Fecha de Creación'
        ];
        foreach ($cabDetalle as $celda => $texto) {
            $sheetDetalle->setCellValue($celda, $texto);
            $sheetDetalle->getStyle($celda)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheetDetalle->getStyle($celda)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E293B');
        }

        // Ordenar tickets por nombre de proyecto y estado activo primero
        usort($requerimientos, function ($a, $b) {
            $pA = $a['proyecto'] ?? 'ZZZ_Sin Proyecto';
            $pB = $b['proyecto'] ?? 'ZZZ_Sin Proyecto';
            if ($pA !== $pB) return strcmp($pA, $pB);
            return ((int)$b['id_requerimiento']) <=> ((int)$a['id_requerimiento']);
        });

        $filaD = 2;
        foreach ($requerimientos as $req) {
            $fecha = isset($req['fecha_creacion']) ? date('d/m/Y H:i', strtotime($req['fecha_creacion'])) : 'N/A';
            $sheetDetalle->setCellValue('A' . $filaD, empty($req['proyecto']) ? 'Sin Proyecto' : $req['proyecto']);
            $sheetDetalle->setCellValue('B' . $filaD, '#TK-' . $req['id_requerimiento']);
            $sheetDetalle->setCellValue('C' . $filaD, $req['titulo']);
            $sheetDetalle->setCellValue('D' . $filaD, strtoupper($req['prioridad']));
            $sheetDetalle->setCellValue('E' . $filaD, strtoupper($req['estado']));
            $sheetDetalle->setCellValue('F' . $filaD, empty($req['responsable']) ? 'Sin Asignar' : $req['responsable']);
            $sheetDetalle->setCellValue('G' . $filaD, $req['solicitante']);
            $sheetDetalle->setCellValue('H' . $filaD, $fecha);
            $filaD++;
        }

        foreach (range('A', 'H') as $columnID) {
            $sheetDetalle->getColumnDimension($columnID)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Tickets_Por_Proyectos_PosPeru.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    private function _unusedLegacyExportPlaceholder() {
        if (false) {
            return;
        foreach ($cabeceras as $celda => $texto) {
            $sheet->setCellValue($celda, $texto);
            $sheet->getStyle($celda)->getFont()->setBold(true);
        }

        // Llenar datos
        $fila = 2;
        foreach ($requerimientos as $req) {
            $fecha = isset($req['fecha_creacion']) ? date('d/m/Y H:i', strtotime($req['fecha_creacion'])) : 'N/A';
            
            $sheet->setCellValue('A' . $fila, '#TK-' . $req['id_requerimiento']);
            $sheet->setCellValue('B' . $fila, $req['titulo']);
            $sheet->setCellValue('C' . $fila, strtoupper($req['prioridad']));
            $sheet->setCellValue('D' . $fila, strtoupper($req['estado']));
            $sheet->setCellValue('E' . $fila, $req['solicitante']);
            $sheet->setCellValue('F' . $fila, empty($req['responsable']) ? 'Sin Asignar' : $req['responsable']);
            $sheet->setCellValue('G' . $fila, $fecha);
            $fila++;
        }

        // Ajustar el tamaño de las columnas automáticamente
        foreach(range('A','G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Configurar cabeceras HTTP para forzar descarga del Excel
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Tickets_PosPeru.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
        }
    }

    private function _unusedClose() {
    }

    public function eliminarTicket() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        // Validación: Permitir tanto a ADMIN como a DEVELOPER
        $rol = strtolower($_SESSION['usuario']['rol'] ?? '');
        if (!in_array($rol, ['admin', 'developer'])) {
            header("Location: index.php?action=dashboard");
            exit();
        }

        if (isset($_GET['id'])) {
            $id_req = (int)$_GET['id'];
            
            $reqModel = new Requerimiento();

            if ($reqModel->eliminarDefinitivamente($id_req)) {
                // Limpiar también las imágenes físicas en la carpeta uploads/
                $patron = self::dirUploads() . "tk_" . $id_req . "_*.*";
                $archivos = glob($patron);
                if ($archivos) {
                    foreach ($archivos as $archivo) {
                        if (is_file($archivo)) {
                            unlink($archivo);
                        }
                    }
                }
            }
        }

        header("Location: index.php?action=dashboard");
        exit();
    }

}
?>
