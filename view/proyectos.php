<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!defined('APP_INIT')) { http_response_code(403); exit(); }
$usuario = $_SESSION['usuario'];
$rol = strtolower($usuario['rol']);
$esAdmin = ($rol === 'admin');

// Variables de diseño unificadas
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
    <title>Proyectos | PosPerú PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="view/css/developer.css">
    <!-- Enlace al nuevo CSS de proyectos -->
    <link rel="stylesheet" href="view/css/proyectos.css">
</head>
<body class="flex h-screen overflow-hidden text-slate-800 antialiased bg-slate-50">

    <!-- SIDEBAR UNIFICADO -->
    <aside id="sidebar" class="sidebar-bg w-64 flex-shrink-0 flex flex-col h-full text-slate-300 transition-transform duration-300 fixed inset-y-0 left-0 z-50 transform -translate-x-full lg:static lg:translate-x-0"> 
        <!-- Modificado: Se agregó justify-between para separar el logo del botón -->
        <div class="h-16 flex items-center px-6 border-b border-slate-700/50 justify-between">
            <div class="flex items-center gap-3 w-full">
                <!-- Modificado: Se agregó shrink-0 -->
                <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center text-white shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <span class="text-lg font-bold text-white tracking-wide truncate">PosPerú <span class="text-indigo-400 font-black text-sm">PRO</span></span>
            </div>
            
            <!-- NUEVO: Botón Cerrar (Solo visible en móviles) -->
            <button id="btnCerrarMenu" class="lg:hidden text-slate-400 hover:text-white p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto py-4 px-3 space-y-6">
            <div>
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Operaciones</p>
                <nav class="space-y-1">
                    <a href="index.php?action=dashboard" class="sidebar-item-active flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Panel Principal
                    </a>
                    <a href="index.php?action=proyectos" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                        Proyectos
                    </a>
                    <a href="index.php?action=kanban" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        Kanban
                    </a>
                    <a href="index.php?action=mis_asignaciones" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-800 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Mis asignaciones
                    </a>
                </nav>
            </div>
        </div>

        <div class="p-4 border-t border-slate-700/50">
            <div class="flex items-center gap-3 bg-slate-800/50 p-2.5 rounded-lg border border-slate-700 cursor-pointer hover:bg-slate-800 transition">
                <!-- Modificado: Se agregó shrink-0 para evitar que el avatar se deforme -->
                <div class="w-9 h-9 rounded bg-indigo-900/50 text-indigo-300 flex items-center justify-center font-bold text-sm border border-indigo-500/30 uppercase shrink-0">
                    <?= substr(htmlspecialchars($usuario['nombre']), 0, 2) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate"><?= htmlspecialchars(explode(' ', $usuario['nombre'])[0]) ?></p>
                    <p class="text-[10px] text-slate-400 truncate"><?= htmlspecialchars($usuario['correo']) ?></p>
                </div>
                <!-- Modificado: Se agregó shrink-0 y un poco de padding (p-1) -->
                <a href="index.php?action=logout" class="text-slate-400 hover:text-rose-400 transition shrink-0 p-1" title="Cerrar sesión">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </a>
            </div>
        </div>
    </aside>
    <!-- CONTENIDO PRINCIPAL -->
    <main class="flex-1 flex flex-col h-full min-w-0 overflow-hidden bg-slate-50 relative w-full">
        <!-- Top Navbar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 shrink-0 shadow-sm z-10 gap-2 sm:gap-4">
            
            <!-- Botón Menú Hamburguesa (Solo Móvil) -->
            <button id="btnAbrirMenu" class="lg:hidden text-slate-500 hover:text-indigo-600 focus:outline-none p-1 shrink-0 -ml-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button> 
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Buscar proyectos</h1>

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
    
            
            <?php if ($esAdmin): ?>
            <button onclick="abrirModalProyecto()" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Crear proyecto
            </button>
            <?php endif; ?>
        </header>

        <!-- Filtros y Búsqueda -->
        <div class="px-8 pb-4 border-b border-slate-200 shrink-0 space-y-4">
            <!-- Barra de búsqueda -->
            <div class="relative">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" placeholder="Buscar un proyecto" class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
            </div>
            
            <!-- Píldoras de filtro -->
            <div class="flex flex-wrap gap-3">
                <button class="px-3 py-1.5 border border-slate-300 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-50 flex items-center gap-1.5 transition">
                    Encargado <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <button class="px-3 py-1.5 border border-slate-300 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-50 flex items-center gap-1.5 transition">
                    Miembros <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <button class="px-3 py-1.5 border border-slate-300 rounded-full text-xs font-semibold text-slate-600 hover:bg-slate-50 flex items-center gap-1.5 transition">
                    Estado <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
            </div>
        </div>

        <!-- Tabla de Proyectos Scrollable alimentada con DATOS REALES -->
        <div class="flex-1 overflow-x-auto overflow-y-auto custom-scrollbar">
            <table class="w-full min-w-[800px] text-left border-collapse">
                <thead class="sticky top-0 bg-white z-10">
                    <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500 tracking-wide">
                        <th class="py-3 px-8 font-medium">Nombre</th>
                        <th class="py-3 px-6 font-medium">Miembros</th>
                        <th class="py-3 px-6 font-medium">Tickets</th>
                        <th class="py-3 px-8 font-medium text-right flex items-center justify-end gap-1 cursor-pointer hover:text-slate-700">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg> 
                            Última modificación
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($proyectos)): ?>
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-500 text-sm">No hay proyectos registrados aún.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($proyectos as $p): ?>
                        <tr class="hover:bg-slate-50/50 transition group cursor-pointer">
                            <td class="py-4 px-8 flex items-center gap-4">
                                <div class="w-8 h-8 rounded-md shrink-0 flex items-center justify-center <?= $p['icono'] ?>">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700 group-hover:text-indigo-600 transition"><?= htmlspecialchars($p['nombre']) ?></p>
                                    <p class="text-[11px] font-medium text-emerald-600 mt-0.5">Activo</p>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <!-- Avatares superpuestos generados desde BD -->
                                <div class="flex -space-x-1.5">
                                    <?php foreach ($p['miembros'] as $iniciales => $color): ?>
                                        <div class="w-6 h-6 rounded-full <?= $color ?> text-slate-700 text-[10px] font-bold flex items-center justify-center border-2 border-white relative z-10 hover:z-20 hover:scale-110 transition-transform cursor-pointer" title="<?= htmlspecialchars($iniciales) ?>">
                                            <?= htmlspecialchars($iniciales) ?>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <!-- Botón de opciones (tres puntitos) -->
                                    <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-500 text-[10px] font-bold flex items-center justify-center border-2 border-white relative z-10 hover:z-20 hover:scale-110 transition-transform cursor-pointer">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <button type="button" onclick="abrirModalTickets(<?= (int)$p['id'] ?>)"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-xs font-bold text-slate-600 hover:border-indigo-300 hover:text-indigo-600 transition"
                                        title="Agregar o quitar tickets de este proyecto">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    <span class="contador-tickets" data-proyecto="<?= (int)$p['id'] ?>"><?= (int)$p['total_tickets'] ?></span>
                                    <span class="font-semibold text-slate-400">· Gestionar</span>
                                </button>
                            </td>
                            <td class="py-4 px-8 text-right flex items-center justify-end gap-3">
                            <span class="text-xs text-slate-500 font-medium"><?= htmlspecialchars($p['fecha']) ?></span>
                            
                            <?php if ($esAdmin): ?>
                            <div class="flex items-center gap-2 ml-4 border-l border-slate-200 pl-4 opacity-0 group-hover:opacity-100 transition-opacity">
                               <button onclick="editarProyecto(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nombre'])) ?>', '<?= htmlspecialchars(addslashes($p['descripcion'])) ?>', '<?= $p['miembros_ids'] ?>')" class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center hover:bg-indigo-100 hover:text-indigo-600 transition" title="Editar">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <button onclick="if(confirm('¿Eliminar proyecto y todos sus tickets asociados?')) window.location.href='index.php?action=eliminar_proyecto&id=<?= $p['id'] ?>'" class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center hover:bg-rose-100 hover:text-rose-600 transition" title="Eliminar">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                            <?php endif; ?>
                        </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    <!-- MODAL CREAR / EDITAR PROYECTO -->
    <div id="modalProyecto" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center transition-opacity">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="modalContent">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="text-lg font-black text-slate-800" id="modalTitle">Nuevo Proyecto</h3>
                <button onclick="cerrarModalProyecto()" class="text-slate-400 hover:text-rose-500 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form action="index.php?action=guardar_proyecto" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="id_proyecto" id="id_proyecto" value="">
                
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nombre del Proyecto <span class="text-rose-500">*</span></label>
                    <input type="text" name="nombre" id="nombre_proyecto" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Descripción</label>
                    <textarea name="descripcion" id="descripcion_proyecto" rows="2" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-indigo-500 focus:bg-white transition resize-none"></textarea>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Miembros del Proyecto</label>
                    <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50 border border-slate-200 rounded-lg max-h-40 overflow-y-auto custom-scrollbar">
                        <?php foreach($usuarios_tecnicos ?? [] as $ut): ?>
                        <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer hover:bg-slate-100 p-1 rounded transition">
                            <input type="checkbox" name="miembros[]" value="<?= $ut['id_usuario'] ?>" class="miembro-checkbox w-4 h-4 text-indigo-600 bg-white border-slate-300 rounded focus:ring-indigo-500">
                            <span class="truncate"><?= htmlspecialchars($ut['nombre']) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="cerrarModalProyecto()" class="flex-1 px-4 py-2 border border-slate-200 text-slate-600 text-sm font-bold rounded-lg hover:bg-slate-50 transition">Cancelar</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 transition">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Fondo oscuro del menú lateral en móvil (usado por proyectos.js) -->
    <div id="sidebarOverlay" class="hidden opacity-0 pointer-events-none fixed inset-0 bg-slate-900/50 z-40 lg:hidden transition-opacity duration-300"></div>

    <!-- MODAL GESTIONAR TICKETS DEL PROYECTO -->
    <div id="modalTickets" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center sm:p-4" role="dialog" aria-modal="true" aria-labelledby="modalTicketsTitulo">
        <div class="bg-white w-full sm:max-w-2xl max-h-[90dvh] flex flex-col rounded-t-2xl sm:rounded-2xl shadow-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-start gap-3 bg-slate-50 shrink-0">
                <div class="min-w-0">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tickets del proyecto</p>
                    <h3 class="text-lg font-black text-slate-800 truncate" id="modalTicketsTitulo">Cargando...</h3>
                </div>
                <button type="button" onclick="cerrarModalTickets()" class="text-slate-400 hover:text-rose-500 transition p-1 shrink-0" aria-label="Cerrar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Agregar ticket -->
            <form id="formAgregarTicket" class="px-5 py-4 border-b border-slate-100 shrink-0">
                <label for="selectTicketDisponible" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Agregar ticket</label>
                <div class="flex flex-col sm:flex-row gap-2">
                    <select id="selectTicketDisponible" class="flex-1 min-w-0 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-indigo-500"></select>
                    <button type="submit" id="btnAgregarTicket" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed shrink-0">Agregar</button>
                </div>
                <p class="text-[11px] text-slate-400 mt-1.5">Si el ticket ya pertenece a otro proyecto, se moverá a este.</p>
            </form>

            <!-- Lista de tickets del proyecto -->
            <div class="flex-1 overflow-y-auto custom-scrollbar px-5 py-4">
                <p id="mensajeTickets" class="hidden mb-3 text-xs font-semibold px-3 py-2 rounded-lg" role="status"></p>
                <ul id="listaTicketsProyecto" class="space-y-2"></ul>
                <p id="sinTicketsProyecto" class="hidden text-center text-sm text-slate-500 py-8">Este proyecto todavía no tiene tickets.</p>
            </div>
        </div>
    </div>

    <!-- Enlace al JS  -->
    <script src="view/javascript/proyectos.js?v=<?= filemtime('view/javascript/proyectos.js') ?>"></script>
</body>
</html>