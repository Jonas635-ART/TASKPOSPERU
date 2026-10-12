<?php 
$usuario = $_SESSION['usuario'] ?? ['nombre' => 'Usuario', 'rol' => 'cliente']; 
// $requerimientos viene del DashboardController
if (!isset($requerimientos)) $requerimientos = [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal del Cliente | PosPerú PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="view/css/cliente.css">
</head>
<body class="text-slate-800 antialiased">

    <!-- Navegación Superior -->
    <nav class="bg-white border-b border-slate-200 px-6 py-3 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <span class="font-bold text-lg text-slate-900 tracking-tight">PosPerú <span class="text-indigo-600 text-sm font-black">PRO</span></span>
                <span class="ml-2 text-xs font-semibold text-slate-500 uppercase tracking-wider border-l border-slate-300 pl-3">Portal del Cliente</span>
            </div>
            <div class="hidden md:flex items-center gap-1 bg-indigo-50 p-1 rounded-lg">
                <a href="#" class="px-4 py-1.5 bg-indigo-600 text-white text-sm font-semibold rounded-md shadow-sm">Mis Requerimientos</a>
            </div>
        </div>
        
        <div class="flex items-center gap-5">
            <!-- Icono Notificación -->
            <button class="relative text-slate-400 hover:text-indigo-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            </button>
            
            <!-- Perfil Usuario -->
            <div class="flex items-center gap-3 pl-5 border-l border-slate-200">
                <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm uppercase">
                    <?= substr(htmlspecialchars($usuario['nombre']), 0, 1) ?>
                </div>
                <div class="hidden md:block text-sm">
                    <p class="font-semibold text-slate-800 leading-tight">Hola, <?= htmlspecialchars(explode(' ', $usuario['nombre'])[0]) ?></p>
                    <p class="text-xs text-slate-500 capitalize"><?= htmlspecialchars($usuario['rol']) ?> Corporativo</p>
                </div>
            </div>
            
            <a href="index.php?action=logout" class="flex items-center gap-2 text-sm text-slate-500 hover:text-rose-600 transition ml-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span class="hidden md:inline font-medium">Cerrar Sesión</span>
            </a>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
        
        <!-- Hero Section -->
        <div class="bg-hero-gradient rounded-2xl p-8 border border-indigo-100 shadow-sm relative overflow-hidden">
            <div class="relative z-10">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-100/80 text-indigo-700 text-xs font-bold uppercase tracking-wider mb-4 border border-indigo-200">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    Mesa de Ayuda Corporativa
                </span>
                <h1 class="text-3xl font-black text-slate-900 mb-2">Centro de Ayuda y Requerimientos TI</h1>
                <p class="text-slate-600 mb-6 max-w-2xl text-sm leading-relaxed">¿Tienes un problema técnico o necesitas un nuevo acceso? Registra tu solicitud y nuestro equipo de soporte te responderá a la brevedad.</p>
            </div>
            <!-- Decoración fondo -->
            <div class="absolute right-0 top-0 w-64 h-full bg-gradient-to-l from-indigo-100/50 to-transparent pointer-events-none"></div>
        </div>

        <!-- Formulario: Nuevo Requerimiento -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Nuevo Requerimiento de Soporte TI</h2>
                        <p class="text-xs text-slate-500">Completa los detalles a continuación. Los campos marcados con asterisco (*) son prioritarios.</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <!-- Alerta para mensajes JS -->
                <div id="alertaReq" class="hidden px-4 py-3 rounded-lg text-sm font-semibold mb-5 flex items-center gap-2"></div>

                <form id="formRequerimiento" class="space-y-5">
                    <!-- Título -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Título del Requerimiento <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold font-serif text-lg">T</span>
                            <input type="text" name="titulo" required placeholder="Ej. No puedo acceder a la VPN corporativa..." class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                        </div>
                    </div>

                    <!-- Prioridad -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Prioridad de Negocio <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2">
                                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            </span>
                            <select name="prioridad" required class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 appearance-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium">
                                <option value="baja">Baja - Consulta general o mejora (No bloquea)</option>
                                <option value="media" selected>Media - Falla parcial (Trabajo posible)</option>
                                <option value="alta">Alta - Falla total (Bloqueo de trabajo)</option>
                                <option value="critica">Crítica - Afectación masiva / Caída de sistema</option>
                            </select>
                            <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                        </div>
                    </div>

                    <!-- Descripción Técnica -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Descripción Técnica <span class="text-rose-500">*</span></label>
                        <textarea name="descripcion_tecnica" required rows="4" placeholder="Describe detalladamente el incidente, pasos para reproducirlo o errores mostrados..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition resize-none"></textarea>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="reset" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-lg transition">Limpiar</button>
                        <button type="submit" id="btnReq" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-md flex items-center gap-2 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            <span>Enviar Requerimiento</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Solicitudes -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
            <div class="p-5 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-800">Historial de Solicitudes</h2>
                <p class="text-xs text-slate-500 mt-1">Monitorea el progreso de tus tickets de soporte.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 text-[10px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="p-4 pl-5">ID</th>
                            <th class="p-4">TÍTULO Y DESCRIPCIÓN</th>
                            <th class="p-4">FECHA DE REGISTRO</th>
                            <th class="p-4">ESTADO</th>
                            <th class="p-4">ASIGNACIÓN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php if (empty($requerimientos)): ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                        <p class="font-medium text-slate-600">No tienes requerimientos registrados.</p>
                                        <p class="text-xs mt-1">Tus tickets aparecerán aquí una vez que envíes el formulario.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($requerimientos as $req): 
                                // Determinar colores según estado
                                $estado = strtolower($req['estado']);
                                if ($estado === 'pendiente') {
                                    $bgEstado = 'bg-slate-100 text-slate-600 border-slate-200';
                                    $dotEstado = 'bg-slate-400';
                                } elseif ($estado === 'en proceso') {
                                    $bgEstado = 'bg-blue-50 text-blue-700 border-blue-100';
                                    $dotEstado = 'bg-blue-500';
                                } else {
                                    $bgEstado = 'bg-emerald-50 text-emerald-700 border-emerald-100';
                                    $dotEstado = 'bg-emerald-500';
                                }

                                // Color de etiqueta de prioridad
                                $prio = strtolower($req['prioridad']);
                                $bgPrio = ($prio === 'alta' || $prio === 'critica') ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600';
                            ?>
                            <tr class="hover:bg-slate-50/50 transition">
                                <a href="index.php?action=ver_ticket&id=<?= $req['id_requerimiento'] ?>" class="..."> </a>
                                <td class="p-4 pl-5 align-top">
                                    <span class="font-mono text-xs font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">#TK-<?= htmlspecialchars($req['id_requerimiento']) ?></span>
                                </td>
                                <td class="p-4 align-top max-w-md">
                                    <div class="flex items-center gap-2 mb-1">
                                        <p class="font-bold text-slate-800"><?= htmlspecialchars($req['titulo']) ?></p>
                                        <span class="px-1.5 py-0.5 <?= $bgPrio ?> text-[9px] font-bold rounded uppercase"><?= htmlspecialchars($prio) ?></span>
                                    </div>
                                    <p class="text-xs text-slate-500 truncate" title="<?= htmlspecialchars($req['descripcion_tecnica']) ?>">
                                        <?= htmlspecialchars($req['descripcion_tecnica']) ?>
                                    </p>
                                </td>
                                <td class="p-4 align-top text-xs">
                                    <p class="font-medium text-slate-800"><?= date('d M Y', strtotime($req['fecha_creacion'])) ?></p>
                                    <p class="text-slate-400 mt-0.5"><?= date('h:i A', strtotime($req['fecha_creacion'])) ?></p>
                                </td>
                                <td class="p-4 align-top">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full <?= $bgEstado ?> border text-xs font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $dotEstado ?>"></span> <?= ucfirst($estado) ?>
                                    </span>
                                </td>
                                <td class="p-4 align-top">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full border border-dashed border-slate-300 flex items-center justify-center text-slate-400 text-xs">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </div>
                                        <div class="text-xs">
                                            <p class="font-semibold text-slate-500">En evaluación</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </main>

    <!-- Archivo JS separado -->
    <script src="view/javascript/cliente.js"></script>
</body>
</html>