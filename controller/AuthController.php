<?php
if (!defined('APP_INIT')) { http_response_code(403); exit(); }
require_once __DIR__ . '/../model/Usuario.php';
require_once __DIR__ . '/../config/Mailer.php';

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
                $env = Database::getEnv();

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
    // Recuperación autónoma de contraseña por correo (código 6 dígitos)
    // ==========================================================

    public function recuperar() {
        require_once __DIR__ . '/../view/recuperar.php';
    }

    // AJAX (POST): Genera código de 6 dígitos y lo envía al correo del usuario
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
        $res = $usuarioModel->generarCodigoRecuperacion($correo);

        if (!empty($res['error'])) {
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error interno. Intenta nuevamente.']);
            exit();
        }

        if (!empty($res['cooldown'])) {
            echo json_encode([
                'success' => false,
                'message' => "Ya enviamos un código recientemente. Espera {$res['espera']} segundos antes de solicitar otro."
            ]);
            exit();
        }

        if (!empty($res['existe'])) {
            $envio = Mailer::enviarCodigoRecuperacion(
                $res['usuario']['correo'],
                $res['usuario']['nombre'],
                $res['codigo'],
                $res['minutos']
            );

            if (!$envio['success']) {
                echo json_encode(['success' => false, 'message' => $envio['error']]);
                exit();
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Si el correo está registrado, enviamos un código de 6 dígitos a tu bandeja de entrada (válido por 15 minutos).'
        ]);
        exit();
    }

    // AJAX (POST): Verifica el código de 6 dígitos y actualiza la contraseña
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
        $resultado = $usuarioModel->restablecerConCodigo($correo, $codigo, $password);

        if ($resultado['success']) {
            $resultado['redirect'] = 'index.php?action=login';
        }

        echo json_encode($resultado);
        exit();
    }

    // ==========================================================
    // Gestión de cuenta propia (perfil del usuario autenticado)
    // ==========================================================

    // AJAX (POST): Actualizar correo electrónico propio
    public function actualizarCorreo() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit();
        }

        $idUsuario      = (int)$_SESSION['usuario']['id'];
        $nuevoCorreo    = trim($_POST['nuevo_correo'] ?? '');
        $passwordActual = $_POST['password_actual'] ?? '';

        if (empty($nuevoCorreo) || empty($passwordActual)) {
            echo json_encode(['success' => false, 'message' => 'Completa el nuevo correo y tu contraseña actual.']);
            exit();
        }

        if (!filter_var($nuevoCorreo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Ingresa un correo electrónico válido.']);
            exit();
        }

        $usuarioModel = new Usuario();
        $resultado = $usuarioModel->actualizarCorreo($idUsuario, $nuevoCorreo, $passwordActual);

        if ($resultado['success']) {
            $_SESSION['usuario']['correo'] = $resultado['correo'];
        }

        echo json_encode($resultado);
        exit();
    }

    // AJAX (POST): Cambiar contraseña propia
    public function actualizarPassword() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit();
        }

        $idUsuario       = (int)$_SESSION['usuario']['id'];
        $passwordActual  = $_POST['password_actual'] ?? '';
        $nuevaPassword   = $_POST['nueva_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($passwordActual) || empty($nuevaPassword) || empty($confirmPassword)) {
            echo json_encode(['success' => false, 'message' => 'Completa todos los campos de contraseña.']);
            exit();
        }

        if (strlen($nuevaPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres.']);
            exit();
        }

        if ($nuevaPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'La confirmación de contraseña no coincide.']);
            exit();
        }

        $usuarioModel = new Usuario();
        $resultado = $usuarioModel->actualizarPassword($idUsuario, $passwordActual, $nuevaPassword);

        echo json_encode($resultado);
        exit();
    }

    // AJAX (POST): Eliminar cuenta propia definitivamente
    public function eliminarCuenta() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit();
        }

        $idUsuario      = (int)$_SESSION['usuario']['id'];
        $passwordActual = $_POST['password_actual'] ?? '';

        if (empty($passwordActual)) {
            echo json_encode(['success' => false, 'message' => 'Ingresa tu contraseña actual para confirmar la eliminación.']);
            exit();
        }

        $usuarioModel = new Usuario();
        $resultado = $usuarioModel->eliminarCuenta($idUsuario, $passwordActual);

        if ($resultado['success']) {
            // Eliminar archivos de evidencia asociados a los tickets borrados
            $dirUploads = __DIR__ . '/../uploads/';
            foreach ($resultado['tickets_eliminados'] ?? [] as $idReq) {
                $archivos = glob($dirUploads . "tk_{$idReq}_*.*");
                if ($archivos) {
                    foreach ($archivos as $archivo) {
                        if (is_file($archivo)) {
                            @unlink($archivo);
                        }
                    }
                }
            }

            // Cerrar sesión de forma segura
            session_unset();
            session_destroy();
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }

            echo json_encode([
                'success'  => true,
                'message'  => $resultado['message'],
                'redirect' => 'index.php?action=login',
            ]);
            exit();
        }

        echo json_encode($resultado);
        exit();
    }
}
