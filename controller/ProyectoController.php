<?php
if (!defined('APP_INIT')) { http_response_code(403); exit(); }
require_once __DIR__ . '/../model/Proyecto.php';
require_once __DIR__ . '/../model/Requerimiento.php';

class ProyectoController {

    // Respuesta JSON de error con código HTTP
    private function errorJson(int $codigo, string $mensaje): void {
        http_response_code($codigo);
        echo json_encode(['success' => false, 'message' => $mensaje]);
        exit();
    }

    public function index() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['usuario'])) { header("Location: index.php?action=login"); exit(); }

        $usuario = $_SESSION['usuario'];
        $rol = strtolower($usuario['rol']);

        $reqModel = new Requerimiento();
        $notificaciones = [];
        if ($rol === 'cliente') {
            $notificaciones = $reqModel->obtenerNotificacionesCliente($usuario['id']);
        } else {
            $notificaciones = $reqModel->obtenerNotificacionesTecnicos();
        }

        $proyectoModel = new Proyecto();
        $proyectos_db = $proyectoModel->listarProyectos();
        $usuarios_tecnicos = $proyectoModel->listarUsuariosTecnicos();

        $proyectos = [];
        $paletaIconos = ['bg-emerald-100 text-emerald-600', 'bg-pink-100 text-pink-600', 'bg-blue-100 text-blue-600', 'bg-fuchsia-100 text-fuchsia-600', 'bg-purple-100 text-purple-600'];
        $paletaAvatares = ['bg-yellow-400', 'bg-purple-200', 'bg-amber-300', 'bg-pink-200', 'bg-fuchsia-200', 'bg-slate-300'];
        
        $i = 0;
        foreach ($proyectos_db as $p) {
            $miembros_array = [];
            $miembros_ids = [];
            
            if (!empty($p['miembros_data'])) {
                $lista = explode(',', $p['miembros_data']);
                $j = 0;
                foreach ($lista as $item) {
                    list($nombre_miembro, $id_miembro) = explode(':', $item);
                    $miembros_ids[] = $id_miembro;
                    
                    if ($j < 4) { // Límite de 4 avatares visuales
                        $palabras = explode(' ', trim($nombre_miembro));
                        $iniciales = strtoupper(substr($palabras[0], 0, 1) . (isset($palabras[1]) ? substr($palabras[1], 0, 1) : ''));
                        $miembros_array[$iniciales] = $paletaAvatares[$j % count($paletaAvatares)];
                    }
                    $j++;
                }
                if (count($lista) > 4) {
                    $miembros_array['+' . (count($lista) - 4)] = 'bg-slate-200 text-slate-600';
                }
            } else {
                $miembros_array['--'] = 'bg-slate-100 text-slate-400';
            }

            $fecha_formato = !empty($p['fecha_actualizacion']) ? date('d M Y', strtotime($p['fecha_actualizacion'])) : 'Desconocida';

            $proyectos[] = [
                'id' => $p['id_proyecto'],
                'nombre' => $p['nombre_proyecto'],
                'descripcion' => $p['descripcion'] ?? '',
                'icono' => $paletaIconos[$i % count($paletaIconos)],
                'miembros' => $miembros_array,
                'miembros_ids' => implode(',', $miembros_ids), // Para marcar los checkboxes al editar
                'total_tickets' => (int)($p['total_tickets'] ?? 0),
                'fecha' => $fecha_formato
            ];
            $i++;
        }

        require_once __DIR__ . '/../view/proyectos.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new Proyecto();
            
            $id = !empty($_POST['id_proyecto']) ? (int)$_POST['id_proyecto'] : null;
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $miembros = array_map('intval', (array)($_POST['miembros'] ?? [])); // IDs de los usuarios seleccionados
            
            if ($nombre !== '') {
                $model->guardar($id, $nombre, $descripcion, $miembros);
            }
        }
        header("Location: index.php?action=proyectos");
        exit();
    }

    public function eliminar() {
        if (isset($_GET['id'])) {
            $model = new Proyecto();
            $model->eliminar((int)$_GET['id']);
        }
        header("Location: index.php?action=proyectos");
        exit();
    }

    // ==========================================================
    // Gestión de tickets dentro de un proyecto (admin y developer)
    // ==========================================================

    // AJAX (GET): tickets del proyecto y tickets disponibles para agregar
    public function ticketsProyecto() {
        header('Content-Type: application/json');
        $id_proyecto = (int)($_GET['id_proyecto'] ?? 0);

        $proyecto = $id_proyecto ? (new Proyecto())->obtener($id_proyecto) : false;
        if (!$proyecto) {
            $this->errorJson(404, 'El proyecto no existe.');
        }

        $reqModel = new Requerimiento();
        echo json_encode([
            'success'     => true,
            'proyecto'    => ['id' => (int)$proyecto['id_proyecto'], 'nombre' => $proyecto['nombre_proyecto']],
            'asignados'   => $reqModel->listarPorProyecto($id_proyecto),
            'disponibles' => $reqModel->listarDisponiblesParaProyecto($id_proyecto),
        ]);
        exit();
    }

    // AJAX (POST): agregar (o mover) un ticket al proyecto
    public function agregarTicket() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->errorJson(405, 'Método no permitido.');
        }

        $id_proyecto = (int)($_POST['id_proyecto'] ?? 0);
        $id_req      = (int)($_POST['id_requerimiento'] ?? 0);

        if (!$id_proyecto || !(new Proyecto())->obtener($id_proyecto)) {
            $this->errorJson(404, 'El proyecto no existe.');
        }

        $reqModel = new Requerimiento();
        if (!$id_req || !$reqModel->obtenerDetalle($id_req)) {
            $this->errorJson(404, 'El ticket no existe.');
        }

        echo json_encode(['success' => $reqModel->asignarProyecto($id_req, $id_proyecto)]);
        exit();
    }

    // AJAX (POST): quitar un ticket del proyecto (el ticket NO se borra, queda sin proyecto)
    public function quitarTicket() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->errorJson(405, 'Método no permitido.');
        }

        $id_proyecto = (int)($_POST['id_proyecto'] ?? 0);
        $id_req      = (int)($_POST['id_requerimiento'] ?? 0);

        $reqModel = new Requerimiento();
        $ticket = $id_req ? $reqModel->obtenerDetalle($id_req) : false;

        // Solo se puede quitar si el ticket realmente pertenece a ese proyecto
        if (!$ticket || (int)($ticket['id_proyecto'] ?? 0) !== $id_proyecto) {
            $this->errorJson(404, 'El ticket no pertenece a este proyecto.');
        }

        echo json_encode(['success' => $reqModel->asignarProyecto($id_req, null)]);
        exit();
    }
}
?>
