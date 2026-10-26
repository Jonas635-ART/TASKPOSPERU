<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kanban de Requerimientos | PosPerú PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="view/css/developer.css">
    <link rel="stylesheet" href="view/css/kanban.css">
</head>
<body class="flex h-screen overflow-hidden text-slate-800 antialiased">

    <!-- SIDEBAR UNIFICADO -->
    <aside class="sidebar-bg bg-slate-900 w-64 flex-shrink-0 flex flex-col h-full text-slate-300 transition-all duration-300">
        <div class="h-16 flex items-center px-6 border-b border-slate-700/50">
            <div class="flex items-center gap-3 w-full">
                <div class="w-8 h-8 <?= $bgLogo ?> rounded-lg flex items-center justify-center text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <span class="text-lg font-bold text-white tracking-wide">PosPerú <span class="<?= $textLogo ?> font-black text-sm"><?= $badgeTexto ?></span></span>
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
                    <a href="index.php?action=kanban" class="sidebar-item-active flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium bg-slate-800 text-white transition-colors">
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

    <!-- CONTENIDO PRINCIPAL -->
    <main class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50 relative">
        
        <!-- Top Navbar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0 shadow-sm z-10">
            <div class="flex-1 max-w-2xl">
                <div class="relative w-full">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" placeholder="Buscar tickets en Kanban..." class="w-full pl-10 pr-4 py-2 bg-slate-100 border-transparent focus:bg-white border focus:border-indigo-500 rounded-lg text-sm outline-none transition">
                </div>
            </div>
            
                        <!-- Campana de Notificaciones y Botón de Acción -->
<div class="flex items-center gap-4">
    
    <!-- Contenedor Relativo para la Campana y el Menú Desplegable -->
    <div class="relative">
        <button id="btnNotificaciones" class="relative text-slate-400 hover:text-slate-600 transition focus:outline-none" title="Ver Notificaciones">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            
            <!-- El punto rojo SOLO aparece si hay notificaciones nuevas -->
            <?php if (!empty($notificaciones)): ?>
                <span class="absolute -top-1 -right-1 w-2 h-2 bg-rose-500 rounded-full border border-white animate-pulse"></span>
            <?php endif; ?>
        </button>

        <!-- Mini Ventana Desplegable -->
        <div id="menuNotificaciones" class="hidden absolute right-0 mt-3 w-80 bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-slate-200 overflow-hidden z-50 transform origin-top-right transition-all">
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
                        <a href="index.php?action=ver_ticket&id=<?= $notif['id_requerimiento'] ?>" class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 border-b border-slate-50 transition cursor-pointer group">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 <?= $colorIcono ?>">
                                <?php if ($esCliente): ?>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <?php else: ?>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-800 mb-0.5 group-hover:text-indigo-600 transition"><?= $tituloNotif ?></p>
                                <p class="text-[11px] text-slate-500 truncate mb-1"><span class="font-semibold">TK-<?= $notif['id_requerimiento'] ?>:</span> <?= htmlspecialchars($notif['titulo']) ?></p>
                                <p class="text-[9px] font-bold text-slate-400"><?= $fechaFormato ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Botón Nuevo Ticket (El tuyo, intacto) -->
    <a href="index.php?action=nuevo_ticket" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition flex items-center gap-2 whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Nuevo Ticket
    </a>
</div>
        </header>

        <!-- Area Principal Kanban -->
        <div class="flex-1 flex flex-col p-6 min-h-0">
            
            <div class="flex justify-between items-center mb-6 shrink-0">
                <h1 class="text-2xl font-black text-slate-800">Tablero Kanban</h1>
                <span class="text-sm text-slate-500">Arrastra las tarjetas para cambiar su estado</span>
            </div>

            <!-- Contenedor de Columnas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 flex-1 min-h-0 min-w-[900px] pb-4">
                
                <!-- COLUMNA 1: PENDIENTE -->
                <div class="kanban-column bg-slate-200/60 p-4 rounded-2xl flex flex-col min-h-0 border border-slate-300/60" data-estado="pendiente">
                    <div class="flex justify-between items-center mb-4 px-1 shrink-0">
                        <h2 class="text-xs font-black uppercase tracking-wider text-slate-600 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span> Pendientes
                        </h2>
                        <span class="count-badge bg-slate-300 text-slate-700 font-bold text-xs px-2 py-0.5 rounded-full"><?= count($kanban['pendiente']) ?></span>
                    </div>
                    
                    <div class="kanban-cards-container flex-1 overflow-y-auto space-y-3 pr-1 custom-scrollbar min-h-0 pb-2">
                        <?php foreach ($kanban['pendiente'] as $t): ?>
                            <?php include __DIR__ . '/partials/kanban_card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- COLUMNA 2: EN PROCESO -->
                <div class="kanban-column bg-blue-50/80 p-4 rounded-2xl flex flex-col min-h-0 border border-blue-200/60" data-estado="en proceso">
                    <div class="flex justify-between items-center mb-4 px-1 shrink-0">
                        <h2 class="text-xs font-black uppercase tracking-wider text-blue-700 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse"></span> En Proceso
                        </h2>
                        <span class="count-badge bg-blue-200 text-blue-800 font-bold text-xs px-2 py-0.5 rounded-full"><?= count($kanban['en proceso']) ?></span>
                    </div>

                    <div class="kanban-cards-container flex-1 overflow-y-auto space-y-3 pr-1 custom-scrollbar min-h-0 pb-2">
                        <?php foreach ($kanban['en proceso'] as $t): ?>
                            <?php include __DIR__ . '/partials/kanban_card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- COLUMNA 3: TERMINADO -->
                <div class="kanban-column bg-emerald-50/80 p-4 rounded-2xl flex flex-col min-h-0 border border-emerald-200/60" data-estado="terminado">
                    <div class="flex justify-between items-center mb-4 px-1 shrink-0">
                        <h2 class="text-xs font-black uppercase tracking-wider text-emerald-700 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Terminado
                        </h2>
                        <span class="count-badge bg-emerald-200 text-emerald-800 font-bold text-xs px-2 py-0.5 rounded-full"><?= count($kanban['terminado']) ?></span>
                    </div>

                    <div class="kanban-cards-container flex-1 overflow-y-auto space-y-3 pr-1 custom-scrollbar min-h-0 pb-2">
                        <?php foreach ($kanban['terminado'] as $t): ?>
                            <?php include __DIR__ . '/partials/kanban_card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>
    </main>
        <script>
document.addEventListener('DOMContentLoaded', () => {
    const btnNotif = document.getElementById('btnNotificaciones');
    const menuNotif = document.getElementById('menuNotificaciones');

    if (btnNotif && menuNotif) {
        // Abrir/Cerrar al dar clic en la campana
        btnNotif.addEventListener('click', (e) => {
            e.stopPropagation(); // Evita que el clic se propague al document
            menuNotif.classList.toggle('hidden');
        });

        // Cerrar al hacer clic en cualquier parte fuera de la ventana de notificaciones
        document.addEventListener('click', (e) => {
            if (!menuNotif.contains(e.target) && !btnNotif.contains(e.target)) {
                menuNotif.classList.add('hidden');
            }
        });
    }
});
</script>

    <script src="view/javascript/kanban.js"></script>
</body>
</html>