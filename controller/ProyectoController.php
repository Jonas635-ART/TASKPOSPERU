<?php
require_once __DIR__ . '/../model/Proyecto.php';

class ProyectoController {
   public function index() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['usuario'])) { header("Location: index.php?action=login"); exit(); }

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

        require_once __DIR__ . '/../model/Proyecto.php';
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
                'fecha' => $fecha_formato
            ];
            $i++;
        }

        require_once __DIR__ . '/../view/proyectos.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../model/Proyecto.php';
            $model = new Proyecto();
            
            $id = !empty($_POST['id_proyecto']) ? (int)$_POST['id_proyecto'] : null;
            $nombre = trim($_POST['nombre']);
            $descripcion = trim($_POST['descripcion']);
            $miembros = $_POST['miembros'] ?? []; // Array con los IDs de los usuarios seleccionados
            
            $model->guardar($id, $nombre, $descripcion, $miembros);
            header("Location: index.php?action=proyectos");
            exit();
        }
    }

    public function eliminar() {
        if (isset($_GET['id'])) {
            require_once __DIR__ . '/../model/Proyecto.php';
            $model = new Proyecto();
            $model->eliminar((int)$_GET['id']);
        }
        header("Location: index.php?action=proyectos");
        exit();
    }
}
?>