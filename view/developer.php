<?php 
$usuario = $_SESSION['usuario']; 
if (!isset($estadisticas)) $estadisticas = ['total' => 0, 'pendientes' => 0, 'en_proceso' => 0, 'terminados' => 0];
if (!isset($requerimientos)) $requerimientos = [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Desarrollo | PosPerú</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="view/css/developer.css">
</head>
<body class="flex h-screen overflow-hidden text-slate-800 antialiased">

    <!-- SIDEBAR -->
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
                    <a href="#" class="sidebar-item-active flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Panel Principal
                    </a>
                    <a href="#" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                        Proyectos
                    </a>
                    <a href="#" class="sidebar-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        Requerimientos
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
                    <input type="text" id="searchInput" placeholder="Buscar tickets, tareas o proyectos (Ctrl + K)..." class="w-full pl-10 pr-4 py-2 bg-slate-100 border-transparent focus:bg-white border focus:border-indigo-500 rounded-lg text-sm outline-none transition">
                </div>
            </div>
            
            <div class="flex items-center gap-4 ml-6">
                <div class="hidden lg:flex items-center gap-3">
                    <span class="flex items-center gap-1.5 px-2.5 py-1 rounded bg-orange-50 text-orange-600 border border-orange-100 text-xs font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> <?= $estadisticas['en_proceso'] ?> En Proceso
                    </span>
                    <span class="flex items-center gap-1.5 px-2.5 py-1 rounded bg-rose-50 text-rose-600 border border-rose-100 text-xs font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> 3 Críticos
                    </span>
                </div>
                <div class="h-6 w-px bg-slate-200 mx-2"></div>
                <button class="relative text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <span class="absolute -top-1 -right-1 w-2 h-2 bg-rose-500 rounded-full border border-white"></span>
                </button>
            </div>
        </header>

        <!-- Area Scrollable -->
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <!-- Header Título -->
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-6">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded border border-emerald-200">SLA Activo • 99.4%</span>
                        <span class="text-xs text-slate-400">Sincronizado hace 2 min</span>
                    </div>
                    <h1 class="text-2xl font-black text-slate-800">Gestión de Requerimientos y Tareas</h1>
                    <p class="text-sm text-slate-500 mt-1 max-w-2xl">Supervisa, asigna y da seguimiento a los tickets del equipo en tiempo real con trazabilidad integral y métricas de desempeño.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-semibold rounded-lg shadow-sm flex items-center gap-2 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Exportar
                    </button>
                    <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-md flex items-center gap-2 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Nuevo Ticket
                    </button>
                </div>
            </div>

            <!-- Dashboard KPIs -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Total -->
                <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-4">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Tickets</p>
                        <div class="w-8 h-8 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <h3 class="text-3xl font-black text-slate-800 leading-none"><?= $estadisticas['total'] ?></h3>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-3 pt-3 border-t border-slate-100">Volumen consolidado</p>
                </div>

                <!-- Pendientes -->
                <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-4">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pendientes</p>
                        <div class="w-8 h-8 rounded bg-rose-50 text-rose-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <h3 class="text-3xl font-black text-slate-800 leading-none"><?= $estadisticas['pendientes'] ?></h3>
                        <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-100 mb-0.5">! Urgentes</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-3 pt-3 border-t border-slate-100">Cola por asignar/atender</p>
                </div>

                <!-- En Proceso -->
                <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-4">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">En Proceso</p>
                        <div class="w-8 h-8 rounded bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <h3 class="text-3xl font-black text-slate-800 leading-none"><?= $estadisticas['en_proceso'] ?></h3>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-3 pt-3 border-t border-slate-100">En desarrollo activo</p>
                </div>

                <!-- Terminados -->
                <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-4">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Terminado</p>
                        <div class="w-8 h-8 rounded bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <h3 class="text-3xl font-black text-slate-800 leading-none"><?= $estadisticas['terminados'] ?></h3>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-3 pt-3 border-t border-slate-100">Resueltos exitosamente</p>
                </div>
            </div>

            <!-- Tabla DataGrid -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <!-- Filtros Tabla -->
                <div class="p-4 border-b border-slate-200 flex flex-wrap gap-3 items-center justify-between bg-slate-50/50">
                    <div class="flex flex-wrap gap-2">
                        <div class="relative">
                            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <input type="text" id="filterTable" placeholder="Buscar por ID, título..." class="pl-8 pr-3 py-1.5 bg-white border border-slate-300 rounded-md text-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none w-48 shadow-sm">
                        </div>
                        <select class="py-1.5 px-3 bg-white border border-slate-300 rounded-md text-xs text-slate-600 shadow-sm outline-none focus:border-indigo-500 cursor-pointer">
                            <option>Estado: Todos</option>
                            <option>Pendiente</option>
                            <option>En Proceso</option>
                            <option>Terminado</option>
                        </select>
                    </div>
                    <div class="text-xs text-slate-500 font-medium">
                        Mostrando <span id="rowCount"><?= count($requerimientos) ?></span> requerimientos
                    </div>
                </div>

                <!-- Tabla -->
                <div class="overflow-x-auto w-full custom-scrollbar">
                    <table class="w-full text-left border-collapse min-w-[800px]" id="ticketsTable">
                        <thead class="bg-white text-[10px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="p-4 pl-6 whitespace-nowrap">ID</th>
                                <th class="p-4">TÍTULO DEL REQUERIMIENTO</th>
                                <th class="p-4">SOLICITANTE</th>
                                <th class="p-4">RESPONSABLE</th>
                                <th class="p-4">ESTADO</th>
                                <th class="p-4 text-center">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php if(empty($requerimientos)): ?>
                                <tr><td colspan="6" class="p-8 text-center text-slate-500">No hay tickets registrados en el sistema.</td></tr>
                            <?php else: ?>
                                <?php foreach($requerimientos as $req): 
                                    $estado = strtolower($req['estado']);
                                    $bgEstado = $estado === 'pendiente' ? 'bg-rose-50 text-rose-700 border-rose-100' : 
                                                ($estado === 'en proceso' ? 'bg-amber-50 text-amber-700 border-amber-100' : 
                                                'bg-emerald-50 text-emerald-700 border-emerald-100');
                                    $dotEstado = $estado === 'pendiente' ? 'bg-rose-500' : 
                                                 ($estado === 'en proceso' ? 'bg-amber-500' : 'bg-emerald-500');
                                ?>
                                <tr class="hover:bg-slate-50 transition group">
                                    <td class="p-4 pl-6 align-middle">
                                        <span class="font-mono text-xs font-bold text-indigo-600">#TK-<?= htmlspecialchars($req['id_requerimiento']) ?></span>
                                    </td>
                                    <td class="p-4 align-middle max-w-sm">
                                        <p class="font-bold text-slate-800 text-sm group-hover:text-indigo-600 transition-colors"><?= htmlspecialchars($req['titulo']) ?></p>
                                        <p class="text-[11px] text-slate-500 truncate mt-0.5" title="<?= htmlspecialchars($req['descripcion_tecnica']) ?>">
                                            <?= htmlspecialchars($req['descripcion_tecnica']) ?>
                                        </p>
                                    </td>
                                    <td class="p-4 align-middle">
                                        <p class="font-semibold text-slate-700 text-xs"><?= htmlspecialchars($req['solicitante']) ?></p>
                                        <p class="text-[10px] text-slate-400 capitalize"><?= htmlspecialchars($req['rol_solicitante']) ?></p>
                                    </td>
                                    <td class="p-4 align-middle">
                                        <?php if($req['responsable']): ?>
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-cyan-50 text-cyan-700 flex items-center justify-center text-[9px] font-bold uppercase border border-cyan-100">
                                                    <?= substr($req['responsable'], 0, 2) ?>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-slate-700 text-xs"><?= htmlspecialchars($req['responsable']) ?></p>
                                                    <p class="text-[10px] text-slate-400">DevOps / Dev</p>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400 italic">Sin asignar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 align-middle">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full <?= $bgEstado ?> border text-[11px] font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full <?= $dotEstado ?>"></span> <?= ucfirst($estado) ?>
                                        </span>
                                    </td>
                                    <td class="p-4 align-middle text-center">
                                        <div class="flex justify-center gap-2">
                                            <button class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition" title="Ver Detalles">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                <a href="index.php?action=ver_ticket&id=<?= $req['id_requerimiento'] ?>" class="..."> 
    Detalle 
</a>
                                            </button>
                                            <button class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded transition" title="Editar / Asignar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="view/javascript/developer.js"></script>
</body>
</html>