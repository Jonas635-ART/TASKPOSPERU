<?php

if (!defined('APP_INIT')) { http_response_code(403); exit(); }

class DashboardController {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        // Validación estricta de seguridad
        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $usuario = $_SESSION['usuario'];
        $rol = strtolower($usuario['rol']);

        require_once __DIR__ . '/../model/Requerimiento.php';
        $reqModel = new Requerimiento();

        $notificaciones = [];
        if ($rol === 'cliente') {
            $notificaciones = $reqModel->obtenerNotificacionesCliente($usuario['id']);
        } else {
            $notificaciones = $reqModel->obtenerNotificacionesTecnicos();
        }

        // Enrutamiento unificado para equipo técnico (Admin y Developer) apuntando a developer.php
        if ($rol === 'admin' || $rol === 'developer') {
            $requerimientos = $reqModel->listarTodos();
            $estadisticas = $reqModel->obtenerEstadisticasGlobales();
            
            $total = count($requerimientos);
            $pendientes = 0; 
            $enProceso = 0; 
            $terminados = 0;
            $criticos = 0;
            
            foreach ($requerimientos as $req) {
                $est = strtolower($req['estado']);
                $prio = strtolower($req['prioridad']);

                if ($est === 'pendiente') $pendientes++;
                if ($est === 'en proceso') $enProceso++;
                if ($est === 'terminado') $terminados++;

                if ($prio === 'critica' && $est !== 'terminado') {
                    $criticos++;
                }
            }
            
            // Ambos roles usan el mismo archivo
            require_once __DIR__ . '/../view/developer.php';
            
        } elseif ($rol === 'cliente') {
            $requerimientos = $reqModel->listarPorUsuario($usuario['id']);
            require_once __DIR__ . '/../view/cliente.php';
            
        } else {
            header("Location: index.php?action=logout");
            exit();
        }
    }
}
?>