<?php
session_start();

$action = $_GET['action'] ?? 'login';

// Enrutador principal (Switch)
switch ($action) {
    
    // 1. Mostrar la vista HTML del Login
    case 'login':
        // Si el usuario ya tiene sesión activa, lo mandamos directo a su panel
        if (isset($_SESSION['usuario'])) {
            header("Location: index.php?action=dashboard");
            exit();
        }
        require_once __DIR__ . '/controller/AuthController.php';
        $auth = new AuthController();
        $auth->index();
        break;
        

    // 2. Procesar las credenciales vía Fetch API (AJAX de JavaScript)
    case 'api_login':
        require_once __DIR__ . '/controller/AuthController.php';
        $auth = new AuthController();
        $auth->procesarLogin();
        break;

    case 'register':
        require_once __DIR__ . '/controller/AuthController.php';
        $auth = new AuthController();
        $auth->register(); // Método que mostrará el formulario de registro
    break; 
    case 'api_register':
        require_once __DIR__ . '/controller/AuthController.php';
        $auth = new AuthController();
        $auth->procesarRegistro(); 
        break;   
    case 'dashboard':
        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php?action=login");
            exit();
        }
        require_once __DIR__ . '/controller/DashboardController.php';
        $dash = new DashboardController();
        $dash->index();
        break;
        case 'api_nuevo_requerimiento':
        require_once __DIR__ . '/controller/RequerimientoController.php';
        $reqController = new RequerimientoController();
        $reqController->procesarNuevo();
        break;
    case 'ver_ticket':
        require_once __DIR__ . '/controller/RequerimientoController.php';
        $reqController = new RequerimientoController();
        $reqController->verDetalle();
        break;
    case 'api_agregar_subtarea':
        require_once __DIR__ . '/controller/RequerimientoController.php';
        $reqController = new RequerimientoController();
        $reqController->agregarSubtarea();
        break;
    case 'api_agregar_comentario':
        require_once __DIR__ . '/controller/RequerimientoController.php';
        $reqController = new RequerimientoController();
        $reqController->agregarComentario();
        break;
    case 'api_actualizar_estado':
        require_once __DIR__ . '/controller/RequerimientoController.php';
        $reqController = new RequerimientoController();
        $reqController->actualizarEstado();
    break;
    case 'kanban':
        require_once __DIR__ . '/controller/RequerimientoController.php';
        $reqController = new RequerimientoController();
        $reqController->verKanban();
    break;
    




    // Cerrar sesión de forma segura
    case 'logout':
        // Destruir todas las variables de sesión
        session_unset();
        session_destroy();
        // Eliminar la cookie de sesión si existe
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        header("Location: index.php?action=login");
        exit();
        break;
    
    case 'api_asignar_responsable':
        require_once __DIR__ . '/controller/RequerimientoController.php';
        $reqController = new RequerimientoController();
        $reqController->asignarResponsable();
        break;
    case 'api_marcar_subtarea':
        require_once __DIR__ . '/controller/RequerimientoController.php';
        $reqController = new RequerimientoController();
        $reqController->marcarSubtarea();
        break;
    
    
    
        // 5. Manejo de error 404 (Ruta no encontrada)
    default:
        http_response_code(404);
        echo "<!DOCTYPE html>
              <html lang='es'>
              <head>
                  <meta charset='UTF-8'>
                  <title>404 | PosPerú</title>
                  <script src='https://cdn.tailwindcss.com'></script>
              </head>
              <body class='bg-slate-100 min-h-screen flex flex-col items-center justify-center p-4'>
                  <div class='text-center'>
                      <h1 class='text-9xl font-black text-indigo-600 mb-4'>404</h1>
                      <h2 class='text-2xl font-bold text-slate-800 mb-2'>Página no encontrada</h2>
                      <p class='text-slate-500 mb-8'>El módulo o la ruta que buscas no existe en el sistema.</p>
                      <a href='index.php?action=login' class='px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md transition duration-150'>
                          Volver al inicio
                      </a>
                  </div>
              </body>
              </html>";
        break;
}