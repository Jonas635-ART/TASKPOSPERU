<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$usuario = $_SESSION['usuario'] ?? null;

if (!isset($ticket) || empty($ticket)) {
    echo "<h2 style='text-align:center; padding: 50px; font-family: sans-serif;'>Error: No se cargaron los datos del ticket.</h2>";
    echo "<div style='text-align:center;'><a href='index.php?action=dashboard'>Volver al Tablero</a></div>";
    exit();
}

if (!isset($comentarios)) { $comentarios = []; }
if (!isset($subtareas))   { $subtareas = []; }
if (!isset($evidencias))  { $evidencias = []; }
if (!isset($tecnicos))    { $tecnicos = []; } // Prevención de errores para el selector

// Lógica de colores según estado
$estadoSeguro = strtolower($ticket['estado'] ?? 'pendiente');
$bgEstadoPrincipal = ($estadoSeguro === 'pendiente') ? 'bg-rose-100 text-rose-700' : (($estadoSeguro === 'en proceso') ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700');
$dotEstadoPrincipal = ($estadoSeguro === 'pendiente') ? 'bg-rose-500' : (($estadoSeguro === 'en proceso') ? 'bg-blue-500' : 'bg-emerald-500');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #TK-<?= htmlspecialchars($ticket['id_requerimiento']) ?> | PosPerú</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="view/css/ticket.css">
</head>
<body class="flex h-screen overflow-hidden text-slate-800 antialiased bg-[#f9fafb]">

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        
        <header class="h-14 border-b border-slate-200 flex items-center justify-between px-6 shrink-0 bg-white">
            <div class="flex items-center gap-2 text-sm text-slate-500 font-medium">
                <a href="index.php?action=dashboard" class="text-indigo-600 hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver al Tablero
                </a>
                <span>/</span> <span>Ticket</span> <span>/</span> <span class="text-slate-800">TK-<?= htmlspecialchars($ticket['id_requerimiento']) ?></span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-semibold text-slate-500"><?= htmlspecialchars($usuario['nombre']) ?></span>
                <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold uppercase">
                    <?= substr($usuario['nombre'], 0, 2) ?>
                </div>
            </div>
        </header>

        <!-- Contenedor Principal con Scroll -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
            <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- COLUMNA IZQUIERDA -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Tarjeta Principal -->
                    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-700 text-[10px] font-bold rounded uppercase">Prioridad: <?= htmlspecialchars($ticket['prioridad']) ?></span>
                            <span class="px-2 py-0.5 <?= $bgEstadoPrincipal ?> text-[10px] font-bold rounded uppercase flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full <?= $dotEstadoPrincipal ?>"></span> <?= htmlspecialchars(ucfirst($ticket['estado'])) ?>
                            </span>
                        </div>
                        
                        <h1 class="text-2xl font-black text-slate-800 mb-3 leading-tight"><?= htmlspecialchars($ticket['titulo']) ?></h1>
                        <div class="text-sm text-slate-600 leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($ticket['descripcion_tecnica']) ?></div>
                    </div>

                    <!-- Subtareas -->
                    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                        <h2 class="text-base font-bold text-slate-800 flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            Subtareas Técnicas
                        </h2>
                        
                        <div class="space-y-3" id="lista-subtareas">
                            <?php if (empty($subtareas)): ?>
                                <p class="text-xs text-slate-500 italic">No hay subtareas registradas.</p>
                            <?php else: ?>
                                <?php foreach ($subtareas as $sub): ?>
                                <div class="flex items-start gap-3 p-2 hover:bg-slate-50 rounded group">
                                    <!-- AQUI AGREGAMOS EL DATA-ID PARA EL JAVASCRIPT -->
                                    <input type="checkbox" data-id="<?= $sub['id_subtarea'] ?>" <?= $sub['estado'] === 'terminado' ? 'checked' : '' ?> class="subtarea-check mt-1 w-4 h-4 text-indigo-600 rounded border-slate-300 cursor-pointer">
                                    <div class="flex-1">
                                        <label class="text-sm font-medium text-slate-700 <?= $sub['estado'] === 'terminado' ? 'line-through text-slate-400' : '' ?>"><?= htmlspecialchars($sub['descripcion']) ?></label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Añadir Subtarea -->
                        <?php if ($usuario['rol'] !== 'cliente'): ?>
                        <div class="mt-4 pt-3 border-t border-slate-100">
                            <input type="text" id="inputSubtarea" data-ticket="<?= $ticket['id_requerimiento'] ?>" placeholder="Añadir subtarea y presionar Enter..." class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-3 text-xs focus:ring-1 focus:ring-indigo-500 outline-none">
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Comentarios -->
                    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                        <h2 class="text-base font-bold text-slate-800 mb-6">Historial & Comentarios</h2>
                        
                        <div class="space-y-6 mb-6">
                            <?php foreach ($comentarios as $com): 
                                $esPropio = ($com['id_usuario'] === $usuario['id']);
                                $iniciales = substr($com['nombre'], 0, 2);
                            ?>
                            <div class="flex gap-4 <?= $esPropio ? 'flex-row-reverse' : '' ?>">
                                <div class="w-10 h-10 rounded-full <?= $esPropio ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600' ?> font-bold flex items-center justify-center shrink-0 uppercase"><?= htmlspecialchars($iniciales) ?></div>
                                <div class="flex-1 <?= $esPropio ? 'bg-indigo-50 border-indigo-100 rounded-tr-none' : 'bg-slate-50 border-slate-100 rounded-tl-none' ?> rounded-2xl p-4 border">
                                    <div class="flex justify-between items-center mb-1 <?= $esPropio ? 'flex-row-reverse' : '' ?>">
                                        <p class="text-xs font-bold <?= $esPropio ? 'text-indigo-900' : 'text-slate-800' ?>"><?= htmlspecialchars($com['nombre']) ?> <span class="font-normal opacity-70 ml-1">(<?= htmlspecialchars($com['rol']) ?>)</span></p>
                                        <span class="text-[10px] opacity-60"><?= date('d/m/Y h:i A', strtotime($com['fecha_creacion'])) ?></span>
                                    </div>
                                    <p class="text-sm <?= $esPropio ? 'text-indigo-800' : 'text-slate-700' ?> leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($com['mensaje']) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Caja de Input Comentario -->
                        <div class="border border-slate-200 rounded-xl overflow-hidden focus-within:border-indigo-500 transition">
                            <textarea id="txtComentario" rows="3" placeholder="Escribe un comentario o actualización..." class="w-full p-3 text-sm outline-none resize-none bg-slate-50"></textarea>
                            <div class="bg-white border-t border-slate-100 p-2 flex justify-end items-center">
                                <button id="btnComentar" data-ticket="<?= $ticket['id_requerimiento'] ?>" class="px-4 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg> Publicar
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- COLUMNA DERECHA -->
                <div class="space-y-6">
                    
                    <!-- Control de Estado -->
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-1 <?= $dotEstadoPrincipal ?>"></div>
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Control de Estado</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase">Estado del Ticket</label>
                                <select id="selEstado" <?= $usuario['rol'] === 'cliente' ? 'disabled' : '' ?> class="w-full bg-slate-50 border border-slate-200 text-sm font-semibold text-slate-700 rounded-lg p-2 outline-none">
                                    <option value="pendiente" <?= $ticket['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="en proceso" <?= $ticket['estado'] === 'en proceso' ? 'selected' : '' ?>>En Proceso</option>
                                    <option value="terminado" <?= $ticket['estado'] === 'terminado' ? 'selected' : '' ?>>Terminado</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase">Prioridad</label>
                                <select id="selPrioridad" <?= $usuario['rol'] === 'cliente' ? 'disabled' : '' ?> class="w-full bg-slate-50 border border-slate-200 text-sm font-semibold text-slate-700 rounded-lg p-2 outline-none">
                                    <option value="baja" <?= $ticket['prioridad'] === 'baja' ? 'selected' : '' ?>>Baja</option>
                                    <option value="media" <?= $ticket['prioridad'] === 'media' ? 'selected' : '' ?>>Media</option>
                                    <option value="alta" <?= $ticket['prioridad'] === 'alta' ? 'selected' : '' ?>>Alta</option>
                                    <option value="critica" <?= $ticket['prioridad'] === 'critica' ? 'selected' : '' ?>>Crítica</option>
                                </select>
                            </div>

                            <?php if ($usuario['rol'] !== 'cliente'): ?>
                            <button id="btnActualizarEstado" data-ticket="<?= $ticket['id_requerimiento'] ?>" class="w-full bg-slate-800 text-white text-xs font-bold py-2 rounded-lg hover:bg-slate-700 transition">
                                Guardar Cambios
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Personas Involucradas -->
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Involucrados</h3>
                        <div class="space-y-4">
                            <!-- Solicitante -->
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 mb-2 uppercase">Solicitante</p>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 font-bold flex items-center justify-center text-xs uppercase"><?= substr($ticket['solicitante'], 0, 2) ?></div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800 leading-none"><?= htmlspecialchars($ticket['solicitante']) ?></p>
                                        <p class="text-[10px] text-slate-500 mt-1"><?= htmlspecialchars($ticket['correo_solicitante']) ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Responsable -->
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 mb-2 uppercase">Responsable Asignado</p>
                                <?php if ($ticket['responsable']): ?>
                                    <div class="flex items-center gap-3 bg-indigo-50 p-2 rounded-lg border border-indigo-100 mb-2">
                                        <div class="w-8 h-8 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-xs uppercase"><?= substr($ticket['responsable'], 0, 2) ?></div>
                                        <div>
                                            <p class="text-sm font-bold text-indigo-900 leading-none"><?= htmlspecialchars($ticket['responsable']) ?></p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-xs text-slate-400 italic bg-slate-50 p-2 rounded border border-slate-100 mb-2">Aún no hay técnicos asignados.</div>
                                <?php endif; ?>

                                <!-- AQUÍ AGREGAMOS EL SELECTOR PARA ASIGNAR/CAMBIAR RESPONSABLE -->
                                <?php if ($usuario['rol'] !== 'cliente'): ?>
                                <div class="mt-3 pt-3 border-t border-slate-100">
                                    <select id="selResponsable" data-ticket="<?= $ticket['id_requerimiento'] ?>" class="w-full bg-white border border-slate-200 text-xs font-semibold text-slate-700 rounded-lg p-2 outline-none focus:border-indigo-500 cursor-pointer">
                                        <option value="">-- Cambiar / Asignar responsable --</option>
                                        <?php foreach($tecnicos as $tec): ?>
                                            <option value="<?= $tec['id_usuario'] ?>"><?= htmlspecialchars($tec['nombre']) ?> (<?= ucfirst($tec['rol']) ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="h-10"></div>
        </div>
    </main>

    <script src="view/javascript/ticket.js"></script>
</body>
</html>