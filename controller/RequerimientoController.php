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
            $resultado = $reqModel->registrar($id_solicitante, $titulo, $prioridad, $descripcion_tecnica);

            echo json_encode($resultado);
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
    }
?>