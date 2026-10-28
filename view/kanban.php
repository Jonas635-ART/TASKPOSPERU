<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!defined('APP_INIT')) { http_response_code(403); exit(); }
$usuario = $_SESSION['usuario'];
if (!isset($kanban)) {
    $kanban = ['pendiente' => [], 'en proceso' => [], 'terminado' => []];
}

$rol = strtolower($usuario['rol']);
$esAdmin = ($rol === 'admin');

// Variables de diseño unificadas (Estilo índigo para todos)
$bgLogo = 'bg-indigo-500';
$textLogo = 'text-indigo-400';
$badgeTexto = $esAdmin ? 'ADMIN' : 'PRO';
$tituloMenu = 'Operaciones';
$bgAvatar = 'bg-indigo-900/50 text-indigo-300 border-indigo-500/30';
$labelRol = $esAdmin ? 'Admin' : 'Developer';

// Definición de columnas (estado => configuración visual)
$columnas = [
    'pendiente' => [
        'id' => 'col-pendiente', 'titulo' => 'Pendientes',
        'bg' => 'bg-slate-200/60 border-slate-300/60', 'texto' => 'text-slate-600',
        'punto' => 'bg-slate-400', 'badge' => 'bg-slate-300 text-slate-700',
    ],
    'en proceso' => [
        'id' => 'col-en-proceso', 'titulo' => 'En Proceso',
        'bg' => 'bg-blue-50/80 border-blue-200/60', 'texto' => 'text-blue-700',
        'punto' => 'bg-blue-500 animate-pulse', 'badge' => 'bg-blue-200 text-blue-800',
    ],
    'terminado' => [
        'id' => 'col-terminado', 'titulo' => 'Terminado',
        'bg' => 'bg-emerald-50/80 border-emerald-200/60', 'texto' => 'text-emerald-700',
        'punto' => 'bg-emerald-500', 'badge' => 'bg-emerald-200 text-emerald-800',
    ],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kanban de Requerimientos | PosPerú PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="view/css/developer.css">
    <link rel="stylesheet" href="view/css/kanban.css?v=<?= time() ?>">
</head>
<body class="flex h-dvh overflow-hidden text-slate-800 antialiased">

    <!-- SIDEBAR UNIFICADO (cajón deslizable en móvil/tablet, fijo en escritorio) -->
    <aside id="sidebar" class="sidebar-bg bg-slate-900 w-64 flex-shrink-0 flex flex-col h-full text-slate-300 transition-transform duration-300 fixed inset-y-0 left-0 z-40 -translate-x-full lg:static lg:translate-x-0">
        <div class="h-16 flex items-center px-6 border-b border-slate-700/50">
            <div class="flex items-center gap-3 w-full">
                <div class="w-8 h-8 <?= $bgLogo ?> rounded-lg flex items-center justify-center text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <span class="text-lg font-bold text-white tracking-wide">PosPerú <span class="<?= $textLogo ?> font-black text-sm"><?= $badgeTexto ?></span></span>
                <!-- Cerrar cajón (solo móvil/tablet) -->
                <button id="btnCerrarSidebar" type="button" class="ml-auto lg:hidden text-slate-400 hover:text-white p-1" aria-label="Cerrar menú">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto py-4 px-3 space-y-6">
            <div>
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2"><?= $tituloMenu ?></p>
                <nav class="space-y-1">
                    <a href="index.php?action=dashboard" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-800 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Panel Principal
                    </a>
                    <a href="index.php?action=proyectos" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                        Proyectos
                    </a>
                    <a href="index.php?action=kanban" aria-current="page" class="sidebar-item-active flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium bg-slate-800 text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        Tablero Kanban
                    </a>
                </nav>
            </div>
        </div>

        <div class="p-4 border-t border-slate-700/50">
            <div class="flex items-center gap-3 bg-slate-800/50 p-2.5 rounded-lg border border-slate-700">
                <div class="w-9 h-9 rounded <?= $bgAvatar ?> flex items-center justify-center font-bold text-sm border uppercase">
                    <?= substr(htmlspecialchars($usuario['nombre']), 0, 2) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate"><?= htmlspecialchars(explode(' ', $usuario['nombre'])[0]) ?></p>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider truncate"><?= htmlspecialchars($labelRol) ?></p>
                </div>
                <a href="index.php?action=logout" class="text-slate-400 hover:text-rose-400 transition" title="Cerrar sesión">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- Fondo oscuro detrás del cajón (solo móvil/tablet) -->
    <div id="sidebarBackdrop" class="hidden fixed inset-0 bg-slate-900/50 z-30 lg:hidden"></div>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="flex-1 min-w-0 flex flex-col h-full overflow-hidden bg-slate-50 relative">
        
        <!-- Top Navbar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between gap-3 px-4 lg:px-6 shrink-0 shadow-sm z-10">
            <!-- Botón menú (solo móvil/tablet) -->
            <button id="btnMenu" type="button" class="lg:hidden -ml-1 p-2 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-100" aria-label="Abrir menú" aria-controls="sidebar" aria-expanded="false">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>

            <div class="flex-1 min-w-0 max-w-2xl">
                <div class="relative w-full">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" placeholder="Buscar tickets..." aria-label="Buscar tickets en Kanban" class="w-full pl-10 pr-4 py-2 bg-slate-100 border-transparent focus:bg-white border focus:border-indigo-500 rounded-lg text-sm outline-none transition">
                </div>
            </div>
            
            <!-- Campana de Notificaciones y Botón de Acción -->
            <div class="flex items-center gap-3 sm:gap-4 shrink-0">
    
                <!-- Contenedor Relativo para la Campana y el Menú Desplegable -->
                <div class="relative">
                    <button id="btnNotificaciones" class="relative text-slate-400 hover:text-slate-600 transition focus:outline-none p-1" title="Ver Notificaciones" aria-label="Ver notificaciones">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        
                        <!-- El punto rojo SOLO aparece si hay notificaciones nuevas -->
                        <?php if (!empty($notificaciones)): ?>
                            <span class="absolute top-0 right-0 w-2 h-2 bg-rose-500 rounded-full border border-white animate-pulse"></span>
                        <?php endif; ?>
                    </button>

                    <!-- Mini Ventana Desplegable (se ajusta al ancho de la pantalla en móvil) -->
                    <div id="menuNotificaciones" class="hidden absolute -right-11 sm:right-0 mt-3 w-[calc(100vw-2rem)] max-w-xs sm:w-80 sm:max-w-none bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-slate-200 overflow-hidden z-50 transform origin-top-right transition-all">
                        <div class="px-4 py-3 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Notificaciones</span>
                            <?php if (!empty($notificaciones)): ?>
                                <span class="bg-rose-100 text-rose-700 text-[9px] font-black px-2 py-0.5 rounded-full uppercase"><?= count($notificaciones) ?> Nuevas</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="max-h-72 overflow-y-auto custom-scrollbar">
                            <?php if (empty($notificaciones)): ?>
                                <div class="px-4 py-8 text-center text-sm text-slate-500 font-medium">No hay notificaciones recientes.</div>
                            <?php else: ?>
                                <?php foreach($notificaciones as $notif): 
                                    // Verifica el rol en tiempo real para pintar el diseño
                                    $esCliente = (strtolower($_SESSION['usuario']['rol']) === 'cliente');
                                    $tituloNotif = $esCliente ? 'Ticket Resuelto' : 'Nuevo Ticket Registrado';
                                    $colorIcono = $esCliente ? 'text-emerald-500 bg-emerald-50' : 'text-indigo-500 bg-indigo-50';
                                    $fechaFormato = date('d M Y - h:i a', strtotime($notif['fecha_actualizacion'] ?? $notif['fecha_creacion']));
                                ?>
                                    <a href="index.php?action=ver_ticket&id=<?= (int)$notif['id_requerimiento'] ?>" class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 border-b border-slate-50 transition cursor-pointer group">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 <?= $colorIcono ?>">
                                            <?php if ($esCliente): ?>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <?php else: ?>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-slate-800 mb-0.5 group-hover:text-indigo-600 transition"><?= $tituloNotif ?></p>
                                            <p class="text-[11px] text-slate-500 truncate mb-1"><span class="font-semibold">TK-<?= (int)$notif['id_requerimiento'] ?>:</span> <?= htmlspecialchars($notif['titulo']) ?></p>
                                            <p class="text-[9px] font-bold text-slate-400"><?= $fechaFormato ?></p>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Botón Nuevo Ticket (solo icono en celulares) -->
                <a href="index.php?action=nuevo_ticket" class="p-2 sm:px-4 sm:py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition flex items-center gap-2 whitespace-nowrap" aria-label="Nuevo Ticket">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span class="hidden sm:inline">Nuevo Ticket</span>
                </a>
            </div>
        </header>

        <!-- Area Principal Kanban -->
        <div class="flex-1 flex flex-col p-4 lg:p-6 min-h-0">
            
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1 mb-4 lg:mb-6 shrink-0">
                <h1 class="text-xl lg:text-2xl font-black text-slate-800">Tablero Kanban</h1>
                <span class="text-xs sm:text-sm text-slate-500">
                    <span class="hint-drag">Arrastra las tarjetas para cambiar su estado</span>
                    <span class="hint-touch">Mantén presionada una tarjeta para arrastrarla o usa «Mover a»</span>
                </span>
            </div>

            <!-- Pestañas de columnas (solo móvil/tablet): saltan a la columna y marcan la visible -->
            <nav aria-label="Columnas del tablero" class="lg:hidden shrink-0 mb-3">
                <div class="grid grid-cols-3 gap-1 p-1 bg-slate-200/70 rounded-xl">
                    <?php foreach ($columnas as $estado => $col): ?>
                        <a href="#<?= $col['id'] ?>" data-estado="<?= htmlspecialchars($estado) ?>" <?= $estado === 'pendiente' ? 'aria-current="true"' : '' ?>
                           class="kanban-tab flex items-center justify-center gap-1.5 px-2 py-2 rounded-lg text-[11px] sm:text-xs font-bold text-slate-500 transition">
                            <span class="w-2 h-2 rounded-full <?= $col['punto'] ?>"></span>
                            <span class="truncate"><?= $col['titulo'] ?></span>
                            <span class="tab-count text-[10px] font-black opacity-70" data-estado="<?= htmlspecialchars($estado) ?>"><?= count($kanban[$estado]) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </nav>

            <!-- Contenedor de Columnas:
                 móvil = 1 columna por pantalla con desplazamiento horizontal
                 tablet = 2 columnas visibles
                 escritorio = las 3 columnas en grilla -->
            <div id="kanbanBoard" class="kanban-board flex lg:grid lg:grid-cols-3 gap-4 lg:gap-6 flex-1 min-h-0 overflow-x-auto lg:overflow-x-visible snap-x snap-mandatory lg:snap-none pb-2 lg:pb-4 custom-scrollbar">
                
                <?php foreach ($columnas as $estado => $col): ?>
                <div id="<?= $col['id'] ?>" class="kanban-column <?= $col['bg'] ?> p-3 lg:p-4 rounded-2xl flex flex-col min-h-0 border w-[85%] sm:w-[60%] md:w-[calc(50%-0.5rem)] lg:w-auto shrink-0 snap-start" data-estado="<?= htmlspecialchars($estado) ?>">
                    <div class="flex justify-between items-center mb-3 lg:mb-4 px-1 shrink-0">
                        <h2 class="text-xs font-black uppercase tracking-wider <?= $col['texto'] ?> flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full <?= $col['punto'] ?>"></span> <?= $col['titulo'] ?>
                        </h2>
                        <span class="count-badge <?= $col['badge'] ?> font-bold text-xs px-2 py-0.5 rounded-full"><?= count($kanban[$estado]) ?></span>
                    </div>
                    
                    <div class="kanban-cards-container flex-1 overflow-y-auto space-y-3 pr-1 custom-scrollbar min-h-0 pb-2">
                        <?php foreach ($kanban[$estado] as $t): ?>
                            <?php include __DIR__ . '/partials/kanban_card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
        </div>
    </main>


    <script src="view/javascript/kanban.js?v=<?= time() ?>"></script>
</body>
</html>
