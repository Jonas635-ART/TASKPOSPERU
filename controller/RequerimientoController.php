<?php
require_once __DIR__ . '/../model/Requerimiento.php';

class RequerimientoController {
    
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
            $prioridades_validas = ['baja', 'media', 'alta', 'critica'];
            if (!in_array($prioridad, $prioridades_validas)) {
                $prioridad = 'media';
            }

            $reqModel = new Requerimiento();
            
            // 2. Ejecutar pasando los 5 parámetros en el orden EXACTO del modelo
            $id_req = $reqModel->registrar($id_proyecto, $titulo, $descripcion_tecnica, $prioridad, $id_solicitante);

            // 3. Formatear la respuesta JSON según el resultado
            if ($id_req) {
                // ==========================================
                // NUEVO: Procesar Evidencias Físicas
                // ==========================================
                if (!empty($_FILES['evidencias']['name'][0])) {
                    $upload_dir = __DIR__ . '/../uploads/';
                    if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
                    
                    $total_archivos = min(count($_FILES['evidencias']['name']), 3); 
                    
                    for ($i = 0; $i < $total_archivos; $i++) {
                        if ($_FILES['evidencias']['error'][$i] === 0) {
                            $ext = strtolower(pathinfo($_FILES['evidencias']['name'][$i], PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                                $nombre_archivo = "tk_{$id_req}_" . time() . "_{$i}.{$ext}";
                                move_uploaded_file($_FILES['evidencias']['tmp_name'][$i], $upload_dir . $nombre_archivo);
                            }
                        }
                    }
                }
                // ==========================================

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

        $id_requerimiento = $_GET['id'] ?? null;
        if (!$id_requerimiento) {
            header("Location: index.php?action=dashboard"); exit();
        }

        $reqModel = new Requerimiento();
        $ticket = $reqModel->obtenerDetalle($id_requerimiento);

        if (!$ticket) {
            echo "El ticket no existe."; exit();
        }

        $subtareas = $reqModel->listarSubtareas($id_requerimiento);
        $comentarios = $reqModel->listarComentarios($id_requerimiento);
        $evidencias = $reqModel->listarEvidencias($id_requerimiento);
        $tecnicos = $reqModel->listarTecnicos();
        
        require_once __DIR__ . '/../view/ticket_detalle.php';
    }
    // Procesar el formulario de nuevo ticket
    public function guardarTicket() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../model/Requerimiento.php';
            $reqModel = new Requerimiento();
            
            // Capturar el proyecto de forma opcional
            $id_proyecto = !empty($_POST['id_proyecto']) ? (int)$_POST['id_proyecto'] : null;
            
            $titulo = $_POST['titulo'] ?? '';
            $descripcion = $_POST['descripcion_tecnica'] ?? '';
            $prioridad = $_POST['prioridad'] ?? 'media';
            $id_responsable = !empty($_POST['id_responsable']) ? (int)$_POST['id_responsable'] : null;
            $subtareas = $_POST['subtareas'] ?? []; 
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
                    foreach ($subtareas as $subtarea) {
                        if (!empty(trim($subtarea))) {
                            $reqModel->insertarSubtarea($id_req, $subtarea);
                        }
                    }

                    // 4. Procesar Evidencias (Límite 3 imágenes)
                    if (!empty($_FILES['evidencias']['name'][0])) {
                        $upload_dir = __DIR__ . '/../uploads/';
                        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
                        
                        $total_archivos = min(count($_FILES['evidencias']['name']), 3); 
                        
                        for ($i = 0; $i < $total_archivos; $i++) {
                            if ($_FILES['evidencias']['error'][$i] === 0) {
                                $ext = strtolower(pathinfo($_FILES['evidencias']['name'][$i], PATHINFO_EXTENSION));
                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                                    $nombre_archivo = "tk_{$id_req}_" . time() . "_{$i}.{$ext}";
                                    
                                    // Solo movemos el archivo a la carpeta uploads. Ya no guardamos en BD.
                                    move_uploaded_file($_FILES['evidencias']['tmp_name'][$i], $upload_dir . $nombre_archivo);
                                }
                            }
                        }
                    }

                    header("Location: index.php?action=dashboard");
                    exit();
                }
            }
        }
    }

    // Mostrar el formulario para crear un nuevo ticket
    public function nuevoTicket() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        require_once __DIR__ . '/../model/Requerimiento.php';
        $reqModel = new Requerimiento();
        
        // Usamos tu función original para obtener admins y developers
        $tecnicos = $reqModel->listarTecnicos(); 
        
        require_once __DIR__ . '/../view/nuevo_ticket.php';
    }

    // Funciones AJAX para interactuar con la vista de detalle
    public function agregarSubtarea() {
        header('Content-Type: application/json');
        $id_req = $_POST['id_requerimiento'] ?? '';
        $desc = trim($_POST['descripcion'] ?? '');
        
        if($id_req && $desc) {
            $reqModel = new Requerimiento();
            $res = $reqModel->insertarSubtarea($id_req, $desc);
            echo json_encode(['success' => $res]);
        } else { echo json_encode(['success' => false]); }
        exit();
    }

    public function agregarComentario() {
        header('Content-Type: application/json');
        $id_req = $_POST['id_requerimiento'] ?? '';
        $msg = trim($_POST['mensaje'] ?? '');
        $id_usr = $_SESSION['usuario']['id'] ?? '';
        
        if($id_req && $msg && $id_usr) {
            $reqModel = new Requerimiento();
            $res = $reqModel->insertarComentario($id_req, $id_usr, $msg);
            echo json_encode(['success' => $res]);
        } else { echo json_encode(['success' => false]); }
        exit();
    }

    public function actualizarEstado() {
        header('Content-Type: application/json');
        $id_req = $_POST['id_requerimiento'] ?? '';
        $estado = $_POST['estado'] ?? '';
        $prioridad = $_POST['prioridad'] ?? '';
        
        if($id_req && $estado && $prioridad) {
            $reqModel = new Requerimiento();
            $res = $reqModel->actualizarEstadoTicket($id_req, $estado, $prioridad);
            echo json_encode(['success' => $res]);
        } else { echo json_encode(['success' => false]); }
        exit();
    }
    // AJAX: Asignar Responsable
    public function asignarResponsable() {
        header('Content-Type: application/json');
        $id_req = $_POST['id_requerimiento'] ?? '';
        $id_usr = $_POST['id_usuario'] ?? '';
        
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
        $id_sub = $_POST['id_subtarea'] ?? '';
        $estado = $_POST['estado'] ?? 'pendiente'; 
        
        if($id_sub) {
            $reqModel = new Requerimiento();
            $res = $reqModel->actualizarEstadoSubtarea($id_sub, $estado);
            echo json_encode(['success' => $res]);
        } else { 
            echo json_encode(['success' => false, 'message' => 'Falta el ID de la subtarea']); 
        }
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
    public function exportarExcel() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        // Validación de seguridad: Solo Admin puede exportar
        if (!isset($_SESSION['usuario']) || strtolower($_SESSION['usuario']['rol']) !== 'admin') {
            header("Location: index.php?action=dashboard");
            exit();
        }

        // Cargar los datos desde el modelo
        require_once __DIR__ . '/../model/Requerimiento.php';
        $reqModel = new Requerimiento();
        $requerimientos = $reqModel->listarTodos();

        // Cargar PhpSpreadsheet
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Requerimientos');

        // Definir Cabeceras
        $cabeceras = ['A1' => 'ID Ticket', 'B1' => 'Título', 'C1' => 'Prioridad', 'D1' => 'Estado', 'E1' => 'Solicitante', 'F1' => 'Responsable Asignado', 'G1' => 'Fecha de Creación'];
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
?>