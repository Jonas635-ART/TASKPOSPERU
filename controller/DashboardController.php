<?php

class DashboardController {
    public function index() {
        // Validación estricta de seguridad
        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $usuario = $_SESSION['usuario'];
        $rol = $usuario['rol'];

        // Enrutamiento basado en el rol
        switch ($rol) {
            case 'admin':
                require_once __DIR__ . '/../view/admin.php';
                break;
            case 'developer':
                require_once __DIR__ . '/../model/Requerimiento.php';
                $reqModel = new Requerimiento();
                
                $estadisticas = $reqModel->obtenerEstadisticasGlobales();
                $requerimientos = $reqModel->listarTodos();
                
                require_once __DIR__ . '/../view/developer.php';
                break;
            case 'cliente':
                // 1. LLAMAR AL MODELO
                require_once __DIR__ . '/../model/Requerimiento.php';
                $reqModel = new Requerimiento();
                
                // 2. OBTENER LOS TICKETS DEL USUARIO (Usando su ID de sesión)
                $requerimientos = $reqModel->listarPorUsuario($usuario['id']);
                
                // 3. CARGAR LA VISTA (Ahora la vista sí tendrá la variable $requerimientos llena)
                require_once __DIR__ . '/../view/cliente.php';
                break;
            default:
                // Si el rol no coincide con ninguno, se fuerza el cierre de sesión
                header("Location: index.php?action=logout");
                exit();
        }
    }
}