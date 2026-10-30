<?php
define('APP_INIT', true);
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// ==========================================================
// Niveles de acceso
// ==========================================================
// 'publico'     -> cualquiera puede entrar (con o sin sesión)
// 'invitado'    -> solo usuarios SIN sesión (si ya inició, va al dashboard)
// 'autenticado' -> cualquier usuario con sesión iniciada
// [roles...]    -> solo usuarios con sesión y uno de los roles indicados
const TECNICOS = ['admin', 'developer'];
const SOLO_ADMIN = ['admin'];

// ==========================================================
// Tabla de rutas: acción => [Controlador, método, acceso]
// ==========================================================
$rutas = [
    // Autenticación
    'login'                   => ['AuthController', 'index',             'invitado'],
    'api_login'               => ['AuthController', 'procesarLogin',     'publico'],
    'register'                => ['AuthController', 'register',          'invitado'],
    'api_register'            => ['AuthController', 'procesarRegistro',        'publico'],
    'recuperar'               => ['AuthController', 'recuperar',               'invitado'],
    'api_solicitar_reset'     => ['AuthController', 'solicitarRecuperacion',   'publico'],
    'api_recuperar'           => ['AuthController', 'procesarRecuperacion',    'publico'],
    'usuarios_reset'          => ['AuthController', 'gestionAccesosAdmin',     SOLO_ADMIN],
    'api_generar_codigo_reset'=> ['AuthController', 'generarCodigoAdmin',      SOLO_ADMIN],

    // Panel principal
    'dashboard'               => ['DashboardController', 'index',        'autenticado'],

    // Tickets (la propiedad del ticket se valida dentro del controlador)
    'ver_ticket'              => ['RequerimientoController', 'verDetalle',         'autenticado'],
    'ver_evidencia'           => ['RequerimientoController', 'verEvidencia',       'autenticado'],
    'api_nuevo_requerimiento' => ['RequerimientoController', 'procesarNuevo',      'autenticado'],
    'api_agregar_comentario'  => ['RequerimientoController', 'agregarComentario',  'autenticado'],

    // Gestión técnica de tickets
    'nuevo_ticket'            => ['RequerimientoController', 'nuevoTicket',        TECNICOS],
    'guardar_ticket'          => ['RequerimientoController', 'guardarTicket',      TECNICOS],
    'api_agregar_subtarea'    => ['RequerimientoController', 'agregarSubtarea',    TECNICOS],
    'api_marcar_subtarea'     => ['RequerimientoController', 'marcarSubtarea',     TECNICOS],
    'api_actualizar_estado'   => ['RequerimientoController', 'actualizarEstado',   TECNICOS],
    'api_asignar_responsable' => ['RequerimientoController', 'asignarResponsable', TECNICOS],
    'kanban'                  => ['RequerimientoController', 'verKanban',          TECNICOS],
    'mis_asignaciones'        => ['RequerimientoController', 'misAsignaciones',    TECNICOS],
    'eliminar_ticket'         => ['RequerimientoController', 'eliminarTicket',     TECNICOS],
    'exportar_excel'          => ['RequerimientoController', 'exportarExcel',      SOLO_ADMIN],

    // Proyectos
    'proyectos'               => ['ProyectoController', 'index',    TECNICOS],
    'guardar_proyecto'        => ['ProyectoController', 'guardar',  SOLO_ADMIN],
    'eliminar_proyecto'       => ['ProyectoController', 'eliminar', SOLO_ADMIN],

    // Tickets dentro de proyectos (agregar / quitar)
    'api_proyecto_tickets'        => ['ProyectoController', 'ticketsProyecto', TECNICOS],
    'api_proyecto_agregar_ticket' => ['ProyectoController', 'agregarTicket',   TECNICOS],
    'api_proyecto_quitar_ticket'  => ['ProyectoController', 'quitarTicket',    TECNICOS],
];

$action = $_GET['action'] ?? 'login';

// ==========================================================
// Cerrar sesión de forma segura
// ==========================================================
if ($action === 'logout') {
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
}

// ==========================================================
// Manejo de error 404 (Ruta no encontrada)
// ==========================================================
if (!isset($rutas[$action])) {
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
    exit();
}

[$controlador, $metodo, $acceso] = $rutas[$action];

// ==========================================================
// Verificación centralizada de sesión y rol
// ==========================================================
$esApi   = strpos($action, 'api_') === 0;
$usuario = $_SESSION['usuario'] ?? null;
$rol     = $usuario ? strtolower($usuario['rol'] ?? '') : null;

/**
 * Corta la petición: JSON con código HTTP para endpoints API,
 * redirección para páginas normales.
 */
function denegarAcceso(bool $esApi, int $codigo, string $mensaje, string $redireccion): void {
    if ($esApi) {
        http_response_code($codigo);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $mensaje]);
    } else {
        header("Location: $redireccion");
    }
    exit();
}

if ($acceso === 'invitado') {
    if ($usuario) {
        header("Location: index.php?action=dashboard");
        exit();
    }
} elseif ($acceso !== 'publico') {
    // A partir de aquí se requiere sesión
    if (!$usuario) {
        denegarAcceso($esApi, 401, 'Sesión expirada. Vuelve a iniciar sesión.', 'index.php?action=login');
    }
    // Y, si la ruta lo indica, un rol específico
    if (is_array($acceso) && !in_array($rol, $acceso, true)) {
        denegarAcceso($esApi, 403, 'No tienes permisos para realizar esta acción.', 'index.php?action=dashboard');
    }
}

// ==========================================================
// Despacho al controlador
// ==========================================================
require_once __DIR__ . "/controller/{$controlador}.php";
(new $controlador())->$metodo();
