<?php
if (!defined('APP_INIT')) { http_response_code(403); exit(); }
$usuario = $_SESSION['usuario'];
$paginaActiva = 'mis_asignaciones';
$asignaciones = $asignaciones ?? [];
$resumen = $resumen ?? ['total' => 0, 'pendiente' => 0, 'en proceso' => 0, 'terminado' => 0];

$estilosEstado = [
    'pendiente'  => ['Pendiente',  'bg-slate-100 text-slate-600 border-slate-200',     'bg-slate-400'],
    'en proceso' => ['En Proceso', 'bg-blue-50 text-blue-700 border-blue-100',         'bg-blue-500'],
    'terminado'  => ['Terminado',  'bg-emerald-50 text-emerald-700 border-emerald-100', 'bg-emerald-500'],
];
$estilosPrioridad = [
    'critica' => 'bg-rose-100 text-rose-700 border-rose-200',
    'alta'    => 'bg-orange-100 text-orange-700 border-orange-200',
    'media'   => 'bg-amber-100 text-amber-700 border-amber-200',
    'baja'    => 'bg-slate-100 text-slate-600 border-slate-200',
];
$filtros = [
    'todos'      => ['Todas',       $resumen['total']],
    'pendiente'  => ['Pendientes',  $resumen['pendiente']],
    'en proceso' => ['En proceso',  $resumen['en proceso']],
    'terminado'  => ['Terminadas',  $resumen['terminado']],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis asignaciones | PosPerú PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="view/css/developer.css">
</head>
<body class="flex h-dvh overflow-hidden text-slate-800 antialiased bg-slate-50">

    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 min-w-0 flex flex-col h-full overflow-hidden relative">

        <!-- Top Navbar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center gap-3 px-4 lg:px-6 shrink-0 shadow-sm z-10">
            <button id="btnMenu" type="button" class="lg:hidden -ml-1 p-2 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-100" aria-label="Abrir menú" aria-controls="sidebar" aria-expanded="false">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>

            <div class="flex-1 min-w-0 max-w-xl">
                <div class="relative w-full">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input id="buscarAsignacion" type="search" placeholder="Buscar en mis tickets..." aria-label="Buscar en mis asignaciones" class="w-full pl-10 pr-4 py-2 bg-slate-100 border-transparent focus:bg-white border focus:border-indigo-500 rounded-lg text-sm outline-none transition">
                </div>
            </div>

            <!-- Notificaciones -->
            <div class="relative ml-auto shrink-0">
                <button id="btnNotificaciones" class="relative text-slate-400 hover:text-slate-600 transition focus:outline-none p-1" aria-label="Ver notificaciones">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <?php if (!empty($notificaciones)): ?>
                        <span class="absolute top-0 right-0 w-2 h-2 bg-rose-500 rounded-full border border-white animate-pulse"></span>
                    <?php endif; ?>
                </button>
                <div id="menuNotificaciones" class="hidden absolute right-0 mt-3 w-[calc(100vw-2rem)] max-w-xs sm:w-80 sm:max-w-none bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-slate-200 overflow-hidden z-50">
                    <div class="px-4 py-3 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Notificaciones</span>
                        <?php if (!empty($notificaciones)): ?>
                            <span class="bg-rose-100 text-rose-700 text-[9px] font-black px-2 py-0.5 rounded-full uppercase"><?= count($notificaciones) ?> Nuevas</span>
                        <?php endif; ?>
                    </div>
                    <div class="max-h-72 overflow-y-auto custom-scrollbar">
                        <?php if (empty($notificaciones)): ?>
                            <div class="px-4 py-8 text-center text-sm text-slate-500 font-medium">No hay notificaciones recientes.</div>
                        <?php else: foreach ($notificaciones as $notif): ?>
                            <a href="index.php?action=ver_ticket&id=<?= (int)$notif['id_requerimiento'] ?>" class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 border-b border-slate-50 transition group">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-indigo-500 bg-indigo-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-slate-800 mb-0.5 group-hover:text-indigo-600 transition">Nuevo Ticket Registrado</p>
                                    <p class="text-[11px] text-slate-500 truncate mb-1"><span class="font-semibold">TK-<?= (int)$notif['id_requerimiento'] ?>:</span> <?= htmlspecialchars($notif['titulo']) ?></p>
                                    <p class="text-[9px] font-bold text-slate-400"><?= date('d M Y - h:i a', strtotime($notif['fecha_creacion'])) ?></p>
                                </div>
                            </a>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- Contenido -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-4 lg:p-6">
            <div class="mb-5">
                <h1 class="text-xl lg:text-2xl font-black text-slate-800">Mis asignaciones</h1>
                <p class="text-sm text-slate-500">Tickets en los que eres el responsable, <?= htmlspecialchars(explode(' ', $usuario['nombre'])[0]) ?>.</p>
            </div>

            <!-- Filtros por estado (también sirven de resumen) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6" role="group" aria-label="Filtrar por estado">
                <?php foreach ($filtros as $clave => [$texto, $cantidad]): ?>
                    <button type="button" data-filtro="<?= htmlspecialchars($clave) ?>" aria-pressed="<?= $clave === 'todos' ? 'true' : 'false' ?>"
                            class="filtro-asignacion text-left bg-white border border-slate-200 rounded-xl px-4 py-3 shadow-sm hover:border-indigo-300 transition aria-pressed:border-indigo-500 aria-pressed:ring-2 aria-pressed:ring-indigo-100">
                        <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider"><?= $texto ?></span>
                        <span class="block text-2xl font-black text-slate-800 leading-tight"><?= (int)$cantidad ?></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <?php if (empty($asignaciones)): ?>
                <div class="bg-white border border-dashed border-slate-300 rounded-2xl p-10 text-center">
                    <svg class="w-10 h-10 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <p class="text-sm font-bold text-slate-700">No tienes tickets asignados</p>
                    <p class="text-xs text-slate-500 mt-1">Cuando te asignen como responsable de un ticket aparecerá aquí.</p>
                </div>
            <?php else: ?>
                <div id="listaAsignaciones" class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-4">
                    <?php foreach ($asignaciones as $a):
                        $est  = strtolower($a['estado']);
                        $prio = strtolower($a['prioridad']);
                        [$estTexto, $estClase, $estPunto] = $estilosEstado[$est] ?? $estilosEstado['pendiente'];
                        $total = (int)$a['total_subtareas'];
                        $hechas = (int)$a['subtareas_completadas'];
                        $avance = $total > 0 ? round($hechas * 100 / $total) : 0;
                    ?>
                    <article class="asignacion bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:shadow-md transition flex flex-col" data-estado="<?= htmlspecialchars($est) ?>">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="font-mono text-[11px] font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100">#TK-<?= (int)$a['id_requerimiento'] ?></span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[9px] font-bold uppercase px-2 py-0.5 rounded border <?= $estilosPrioridad[$prio] ?? $estilosPrioridad['baja'] ?>"><?= htmlspecialchars($prio) ?></span>
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full border <?= $estClase ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $estPunto ?>"></span><?= $estTexto ?>
                                </span>
                            </div>
                        </div>

                        <h2 class="text-sm font-bold text-slate-800 leading-snug line-clamp-2 mb-1"><?= htmlspecialchars($a['titulo']) ?></h2>
                        <p class="text-xs text-slate-500 line-clamp-2 mb-3"><?= htmlspecialchars($a['descripcion_tecnica']) ?></p>

                        <dl class="grid grid-cols-2 gap-x-3 gap-y-1 text-[11px] mb-3">
                            <dt class="text-slate-400 font-semibold">Proyecto</dt>
                            <dd class="text-slate-700 font-medium truncate text-right"><?= $a['proyecto'] ? htmlspecialchars($a['proyecto']) : '<span class="italic text-slate-400">Sin proyecto</span>' ?></dd>
                            <dt class="text-slate-400 font-semibold">Solicitante</dt>
                            <dd class="text-slate-700 font-medium truncate text-right"><?= htmlspecialchars($a['solicitante']) ?></dd>
                            <dt class="text-slate-400 font-semibold">Creado</dt>
                            <dd class="text-slate-700 font-medium text-right"><?= date('d/m/Y', strtotime($a['fecha_creacion'])) ?></dd>
                        </dl>

                        <?php if ($total > 0): ?>
                        <div class="mb-3">
                            <div class="flex justify-between text-[10px] font-semibold text-slate-500 mb-1">
                                <span>Subtareas</span><span><?= $hechas ?> / <?= $total ?></span>
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500 rounded-full" style="width: <?= $avance ?>%"></div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <a href="index.php?action=ver_ticket&id=<?= (int)$a['id_requerimiento'] ?>" class="mt-auto pt-3 border-t border-slate-100 text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center justify-end gap-1">
                            Ver detalle &rarr;
                        </a>
                    </article>
                    <?php endforeach; ?>
                </div>
                <p id="sinResultados" class="hidden text-center text-sm text-slate-500 py-10">No hay tickets que coincidan con el filtro.</p>
            <?php endif; ?>
        </div>
    </main>

    <script src="view/javascript/mis_asignaciones.js?v=<?= filemtime(__DIR__ . '/javascript/mis_asignaciones.js') ?>"></script>
</body>
</html>

