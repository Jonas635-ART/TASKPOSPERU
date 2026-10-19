<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$usuario = $_SESSION['usuario'];
if (!isset($kanban)) {
    $kanban = ['pendiente' => [], 'en proceso' => [], 'terminado' => []];
}
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

    <!-- SIDEBAR (Barra lateral oscura) -->
    <aside class="sidebar-bg w-64 flex-shrink-0 flex flex-col h-full text-slate-300 transition-all duration-300">
        <div class="h-16 flex items-center px-6 border-b border-slate-700/50">
            <div class="flex items-center gap-3 w-full">
                <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <span class="text-lg font-bold text-white tracking-wide">PosPerú <span class="text-indigo-400 font-black text-sm">PRO</span></span>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto py-4 px-3 space-y-6">
            <div>
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Operaciones</p>
                <nav class="space-y-1">
                    <a href="index.php?action=dashboard" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Panel Principal
                    </a>
                    <a href="#" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                        Proyectos
                    </a>
                    <a href="index.php?action=kanban" class="sidebar-item-active flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        Kanban
                    </a>
                </nav>
            </div>
        </div>

        <div class="p-4 border-t border-slate-700/50">
            <div class="flex items-center gap-3 bg-slate-800/50 p-2.5 rounded-lg border border-slate-700 cursor-pointer hover:bg-slate-800 transition">
                <div class="w-9 h-9 rounded bg-indigo-900/50 text-indigo-300 flex items-center justify-center font-bold text-sm border border-indigo-500/30 uppercase">
                    <?= substr(htmlspecialchars($usuario['nombre']), 0, 2) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate"><?= htmlspecialchars(explode(' ', $usuario['nombre'])[0]) ?></p>
                    <p class="text-[10px] text-slate-400 truncate"><?= htmlspecialchars($usuario['correo']) ?></p>
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
            
            <div class="flex items-center gap-4 ml-6">
                <button class="relative text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <span class="absolute -top-1 -right-1 w-2 h-2 bg-rose-500 rounded-full border border-white"></span>
                </button>
            </div>
        </header>

        <!-- Area Scrollable (ZONA ROJA DEL KANBAN) -->
        <div class="flex-1 overflow-x-auto overflow-y-hidden p-6 custom-scrollbar">
            
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-black text-slate-800">Tablero Kanban</h1>
                <span class="text-sm text-slate-500">Arrastra las tarjetas para cambiar su estado</span>
            </div>

            <!-- Contenedor del Kanban -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 h-[calc(100%-4rem)] min-w-[900px]">
                
                <!-- COLUMNA 1: PENDIENTE -->
                <div class="kanban-column bg-slate-200/60 p-4 rounded-2xl flex flex-col h-full border border-slate-300/60" data-estado="pendiente">
                    <div class="flex justify-between items-center mb-4 px-1 shrink-0">
                        <h2 class="text-xs font-black uppercase tracking-wider text-slate-600 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span> Pendientes
                        </h2>
                        <span class="count-badge bg-slate-300 text-slate-700 font-bold text-xs px-2 py-0.5 rounded-full"><?= count($kanban['pendiente']) ?></span>
                    </div>
                    
                    <div class="kanban-cards-container flex-1 overflow-y-auto space-y-3 pr-1 custom-scrollbar pb-4">
                        <?php foreach ($kanban['pendiente'] as $t): ?>
                            <?php include __DIR__ . '/partials/kanban_card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- COLUMNA 2: EN PROCESO -->
                <div class="kanban-column bg-blue-50/80 p-4 rounded-2xl flex flex-col h-full border border-blue-200/60" data-estado="en proceso">
                    <div class="flex justify-between items-center mb-4 px-1 shrink-0">
                        <h2 class="text-xs font-black uppercase tracking-wider text-blue-700 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse"></span> En Proceso
                        </h2>
                        <span class="count-badge bg-blue-200 text-blue-800 font-bold text-xs px-2 py-0.5 rounded-full"><?= count($kanban['en proceso']) ?></span>
                    </div>

                    <div class="kanban-cards-container flex-1 overflow-y-auto space-y-3 pr-1 custom-scrollbar pb-4">
                        <?php foreach ($kanban['en proceso'] as $t): ?>
                            <?php include __DIR__ . '/partials/kanban_card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- COLUMNA 3: TERMINADO -->
                <div class="kanban-column bg-emerald-50/80 p-4 rounded-2xl flex flex-col h-full border border-emerald-200/60" data-estado="terminado">
                    <div class="flex justify-between items-center mb-4 px-1 shrink-0">
                        <h2 class="text-xs font-black uppercase tracking-wider text-emerald-700 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Terminado
                        </h2>
                        <span class="count-badge bg-emerald-200 text-emerald-800 font-bold text-xs px-2 py-0.5 rounded-full"><?= count($kanban['terminado']) ?></span>
                    </div>

                    <div class="kanban-cards-container flex-1 overflow-y-auto space-y-3 pr-1 custom-scrollbar pb-4">
                        <?php foreach ($kanban['terminado'] as $t): ?>
                            <?php include __DIR__ . '/partials/kanban_card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script src="view/javascript/kanban.js"></script>
</body>
</html>