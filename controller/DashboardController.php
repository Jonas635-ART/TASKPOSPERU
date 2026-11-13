<?php
if (!defined('APP_INIT')) { http_response_code(403); exit(); }

class DashboardController {
    public function index() {
        $usuario = $_SESSION['usuario'];
        $rol = strtolower($usuario['rol']);

        require_once __DIR__ . '/../model/Requerimiento.php';
        $reqModel = new Requerimiento();

        if ($rol === 'admin' || $rol === 'developer') {
            $notificaciones = $reqModel->obtenerNotificacionesTecnicos();
            $requerimientos = $reqModel->listarTodos();
            $estadisticas   = $reqModel->obtenerEstadisticasGlobales();
            $proyectosKpi   = ($rol === 'admin') ? $reqModel->obtenerKpiProyectos() : [];

            $enProceso = (int)$estadisticas['en_proceso'];
            $criticos  = 0;

            foreach ($requerimientos as $req) {
                if (strtolower($req['prioridad']) === 'critica' && strtolower($req['estado']) !== 'terminado') {
                    $criticos++;
                }
            }

            require_once __DIR__ . '/../view/developer.php';
        } elseif ($rol === 'cliente') {
            $notificaciones = $reqModel->obtenerNotificacionesCliente($usuario['id']);
            $requerimientos = $reqModel->listarPorUsuario($usuario['id']);
            require_once __DIR__ . '/../view/cliente.php';
        } else {
            header("Location: index.php?action=logout");
            exit();
        }
    }

    // AJAX (GET): Sincronización ligera en segundo plano para notificaciones, KPIs y Kanban
    public function syncEstado() {
        header('Content-Type: application/json');

        $usuario   = $_SESSION['usuario'];
        $rol       = strtolower($usuario['rol']);
        $esCliente = ($rol === 'cliente');

        require_once __DIR__ . '/../model/Requerimiento.php';
        $reqModel = new Requerimiento();

        $rawNotif = $esCliente
            ? $reqModel->obtenerNotificacionesCliente($usuario['id'])
            : $reqModel->obtenerNotificacionesTecnicos();

        $notificaciones = [];
        foreach ($rawNotif as $n) {
            $fechaBase = $n['fecha_actualizacion'] ?? $n['fecha_creacion'] ?? 'now';
            $notificaciones[] = [
                'id_requerimiento' => (int)$n['id_requerimiento'],
                'titulo'           => $n['titulo'],
                'titulo_notif'     => $esCliente ? 'Ticket Resuelto' : 'Nuevo Ticket Registrado',
                'fecha_formato'    => date('d M Y - h:i a', strtotime($fechaBase)),
            ];
        }

        $respuesta = [
            'success'        => true,
            'es_cliente'     => $esCliente,
            'notificaciones' => $notificaciones,
        ];

        if (!$esCliente) {
            $estadisticas = $reqModel->obtenerEstadisticasGlobales();
            $tickets      = $reqModel->listarParaKanban();
            $criticos     = 0;

            $mapaTickets = [];
            foreach ($tickets as $t) {
                $est  = strtolower($t['estado']);
                $prio = strtolower($t['prioridad']);
                if ($prio === 'critica' && $est !== 'terminado') {
                    $criticos++;
                }
                $mapaTickets[] = [
                    'id_requerimiento'      => (int)$t['id_requerimiento'],
                    'estado'                => $est,
                    'prioridad'             => $prio,
                    'responsable'           => $t['responsable'] ?? null,
                    'total_subtareas'       => (int)($t['total_subtareas'] ?? 0),
                    'subtareas_completadas' => (int)($t['subtareas_completadas'] ?? 0),
                ];
            }

            $estadisticas['criticos']   = $criticos;
            $respuesta['estadisticas']  = $estadisticas;
            $respuesta['proyectos_kpi'] = ($rol === 'admin') ? $reqModel->obtenerKpiProyectos() : [];
            $respuesta['tickets']       = $mapaTickets;
        }

        echo json_encode($respuesta);
        exit();
    }
}
