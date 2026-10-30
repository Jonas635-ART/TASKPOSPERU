<?php
if (!defined('APP_INIT')) { http_response_code(403); exit(); }
require_once __DIR__ . '/../model/Usuario.php';

class AuthController {
    
    // Muestra la vista HTML del login
    public function index() {
        require_once __DIR__ . '/../view/login.php';
    }

    // Procesa la petición AJAX desde JavaScript
    public function procesarLogin() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = trim($_POST['correo'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($correo) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Completa todos los campos.']);
                exit();
            }

            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->obtenerPorCorreo($correo);

            if ($usuario && password_verify($password, $usuario['password'])) {
                session_regenerate_id(true); // Seguridad
                
                $_SESSION['usuario'] = [
                    'id'     => $usuario['id_usuario'],
                    'nombre' => $usuario['nombre'],
                    'correo' => $usuario['correo'],
                    'rol'    => $usuario['rol']
                ];

                echo json_encode([
                    'success' => true, 
                    'redirect' => 'index.php?action=dashboard'
                ]);
                exit();
            }

            echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas.']);
            exit();
        }
    }

    public function register() {
        if (isset($_SESSION['usuario'])) {
            header("Location: index.php?action=dashboard");
            exit();
        }
        require_once __DIR__ . '/../view/register.php';
    }

    // Procesa el registro con validación de código para roles elevados
    public function procesarRegistro() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre       = trim($_POST['nombre'] ?? '');
            $correo       = trim($_POST['correo'] ?? '');
            $password     = $_POST['password'] ?? '';
            $confirm      = $_POST['confirm_password'] ?? '';
            $codigoAcceso = trim($_POST['codigo_acceso'] ?? '');

            if (empty($nombre) || empty($correo) || empty($password) || empty($confirm)) {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
                exit();
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Ingresa un correo electrónico válido.']);
                exit();
            }

            if (strlen($password) < 6) {
                echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
                exit();
            }

            if ($password !== $confirm) {
                echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
                exit();
            }

            $rolAsignado = 'cliente';

            // Validar código fijo desde el archivo .env
            if (!empty($codigoAcceso)) {
                $envFile = __DIR__ . '/../.env';
                $env = parse_ini_file($envFile);

                if (!empty($env['TOKEN_ADMIN']) && hash_equals($env['TOKEN_ADMIN'], $codigoAcceso)) {
                    $rolAsignado = 'admin';
                } elseif (!empty($env['TOKEN_DEV']) && hash_equals($env['TOKEN_DEV'], $codigoAcceso)) {
                    $rolAsignado = 'developer';
                } else {
                    echo json_encode(['success' => false, 'message' => 'El código de autorización corporativo es inválido.']);
                    exit();
                }
            }

            $usuarioModel = new Usuario();
            $resultado = $usuarioModel->registrar($nombre, $correo, $password, $rolAsignado);

            if ($resultado['success']) {
                $mensajeRol = ($rolAsignado !== 'cliente') 
                    ? "Cuenta registrada con permisos de " . strtoupper($rolAsignado) . ". Redirigiendo..."
                    : "Cuenta registrada correctamente. Redirigiendo...";

                echo json_encode(['success' => true, 'message' => $mensajeRol, 'redirect' => 'index.php?action=login']);
            } else {
                echo json_encode($resultado);
            }
            exit();
        }
    }

    // ==========================================================
    // Recuperación de contraseña asistida por el Administrador
    // ==========================================================

    // Vista pública de recuperación (Solicitar código / Canjear código)
    public function recuperar() {
        require_once __DIR__ . '/../view/recuperar.php';
    }

    // AJAX (POST): El usuario envía una solicitud de restablecimiento al Admin
    public function solicitarRecuperacion() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit();
        }

        $correo = trim($_POST['correo'] ?? '');
        if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Ingresa un correo electrónico válido.']);
            exit();
        }

        $usuarioModel = new Usuario();
        $usuarioModel->crearSolicitudReset($correo);

        echo json_encode([
            'success' => true,
            'message' => 'Solicitud registrada. Pídele al Administrador tu código temporal para continuar en la pestaña «Ya tengo código».'
        ]);
        exit();
    }

    // AJAX (POST): El usuario canjea el código temporal que le entregó el Admin
    public function procesarRecuperacion() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit();
        }

        $correo   = trim($_POST['correo'] ?? '');
        $codigo   = trim($_POST['codigo_temporal'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (empty($correo) || empty($codigo) || empty($password) || empty($confirm)) {
            echo json_encode(['success' => false, 'message' => 'Completa todos los campos.']);
            exit();
        }

        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres.']);
            exit();
        }

        if ($password !== $confirm) {
            echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
            exit();
        }

        $usuarioModel = new Usuario();
        $resultado = $usuarioModel->restablecerConCodigoAdmin($correo, $codigo, $password);

        if ($resultado['success']) {
            $resultado['redirect'] = 'index.php?action=login';
        }

        echo json_encode($resultado);
        exit();
    }

    // Vista de administración de accesos y generación de códigos temporales (Solo Admin)
    public function gestionAccesosAdmin() {
        require_once __DIR__ . '/../model/Requerimiento.php';
        $reqModel = new Requerimiento();
        $notificaciones = $reqModel->obtenerNotificacionesTecnicos();

        $usuarioModel = new Usuario();
        $usuarios = $usuarioModel->listarUsuariosConEstadoReset();

        require_once __DIR__ . '/../view/usuarios_reset.php';
    }

    // AJAX (POST, Solo Admin): Genera un código temporal para el usuario indicado
    public function generarCodigoAdmin() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit();
        }

        $id_usuario = (int)($_POST['id_usuario'] ?? 0);
        $id_admin   = (int)($_SESSION['usuario']['id'] ?? 0);

        if ($id_usuario <= 0 || $id_admin <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Usuario inválido.']);
            exit();
        }

        $usuarioModel = new Usuario();
        $resultado = $usuarioModel->generarCodigoTemporalAdmin($id_usuario, $id_admin);

        if (!$resultado) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'No se pudo generar el código para este usuario.']);
            exit();
        }

        echo json_encode([
            'success' => true,
            'codigo'  => $resultado['codigo'],
            'minutos' => $resultado['minutos'],
            'usuario' => [
                'nombre' => $resultado['usuario']['nombre'],
                'correo' => $resultado['usuario']['correo'],
            ],
        ]);
        exit();
    }
}
?>
