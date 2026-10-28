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

            if ($password !== $confirm) {
                echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
                exit();
            }

            $rolAsignado = 'cliente';

            // Validar código fijo desde el archivo .env
            if (!empty($codigoAcceso)) {
                $envFile = __DIR__ . '/../.env';
                $env = parse_ini_file($envFile);

                if ($codigoAcceso === $env['TOKEN_ADMIN']) {
                    $rolAsignado = 'admin';
                } elseif ($codigoAcceso === $env['TOKEN_DEV']) {
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
}
?>