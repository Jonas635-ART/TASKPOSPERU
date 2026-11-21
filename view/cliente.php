<?php
if (!defined('APP_INIT')) { http_response_code(403); exit(); }
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
    <link rel="icon" type="image/jpeg" href="view/img/logo.jpg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="view/css/cliente.css">
</head>

<body class="text-slate-800 antialiased">

    <!-- Navegación Superior -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="px-3 sm:px-6 py-2.5 sm:py-3 flex justify-between items-center gap-2">
            <div class="flex items-center gap-3 lg:gap-6 min-w-0">
                <div class="flex items-center gap-2 min-w-0">
                    <img src="view/img/logo.jpg" alt="PosPerú" class="w-8 h-8 rounded-lg object-cover shrink-0 shadow-sm">
                    <span class="font-bold text-base sm:text-lg text-slate-900 tracking-tight whitespace-nowrap">PosPerú <span class="text-indigo-600 text-xs sm:text-sm font-black">PRO</span></span>
                    <span class="hidden lg:inline-block ml-2 text-xs font-semibold text-slate-500 uppercase tracking-wider border-l border-slate-300 pl-3 whitespace-nowrap">Portal del Cliente</span>
                </div>
                <!-- Navegación Desktop -->
                <div class="hidden md:flex items-center gap-1 bg-indigo-50 p-1 rounded-lg shrink-0">
                    <a href="index.php?action=dashboard" class="px-3.5 py-1.5 bg-indigo-600 text-white text-sm font-semibold rounded-md shadow-sm whitespace-nowrap">Mis Requerimientos</a>
                    <button type="button" data-open-cuenta class="px-3.5 py-1.5 text-indigo-700 hover:bg-indigo-100 text-sm font-semibold rounded-md transition inline-flex items-center gap-1.5 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span>Mi Cuenta</span>
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-2.5 sm:gap-4 shrink-0">
                <!-- Campana de Notificaciones -->
                <div class="relative">
                    <button id="btnNotificaciones" class="relative p-1.5 text-slate-400 hover:text-slate-600 transition focus:outline-none rounded-lg hover:bg-slate-100" title="Ver Notificaciones" aria-label="Ver Notificaciones">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>

                        <!-- El punto rojo SOLO aparece si hay notificaciones nuevas -->
                        <?php if (!empty($notificaciones)): ?>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-rose-500 rounded-full border border-white animate-pulse"></span>
                        <?php endif; ?>
                    </button>

                    <!-- Mini Ventana Desplegable -->
                    <div id="menuNotificaciones" class="hidden absolute -right-14 sm:right-0 mt-3 w-[calc(100vw-1.5rem)] max-w-xs sm:w-80 bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.12)] border border-slate-200 overflow-hidden z-50 transform origin-top-right transition-all">
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
                                <?php foreach ($notificaciones as $notif):
                                    $esCliente = (strtolower($_SESSION['usuario']['rol']) === 'cliente');
                                    $tituloNotif = $esCliente ? 'Ticket Resuelto' : 'Nuevo Ticket Registrado';
                                    $colorIcono = $esCliente ? 'text-emerald-500 bg-emerald-50' : 'text-indigo-500 bg-indigo-50';
                                    $fechaFormato = date('d M Y - h:i a', strtotime($notif['fecha_actualizacion'] ?? $notif['fecha_creacion']));
                                ?>
                                    <a href="index.php?action=ver_ticket&id=<?= $notif['id_requerimiento'] ?>" class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 border-b border-slate-50 transition cursor-pointer group">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 <?= $colorIcono ?>">
                                            <?php if ($esCliente): ?>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            <?php else: ?>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
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

                <!-- Perfil Usuario -->
                <button type="button" data-open-cuenta class="flex items-center gap-2.5 pl-2.5 sm:pl-4 border-l border-slate-200 text-left hover:opacity-85 transition" title="Mi Cuenta">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs sm:text-sm uppercase shrink-0">
                        <?= htmlspecialchars(mb_substr($usuario['nombre'], 0, 1)) ?>
                    </div>
                    <div class="hidden sm:block text-sm">
                        <p class="font-semibold text-slate-800 leading-tight">Hola, <?= htmlspecialchars(explode(' ', $usuario['nombre'])[0]) ?></p>
                        <p class="text-xs text-slate-500 capitalize"><?= htmlspecialchars($usuario['rol']) ?> Corporativo</p>
                    </div>
                </button>

                <a href="index.php?action=logout" class="flex items-center gap-1.5 text-sm text-slate-500 hover:text-rose-600 transition p-1.5 sm:p-0 sm:ml-1 rounded-lg hover:bg-rose-50 sm:hover:bg-transparent" title="Cerrar Sesión" aria-label="Cerrar Sesión">
                    <svg class="w-5 h-5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span class="hidden lg:inline font-medium whitespace-nowrap">Cerrar Sesión</span>
                </a>
            </div>
        </div>

        <!-- Barra de Navegación Móvil -->
        <div class="flex md:hidden items-center gap-1.5 px-3 py-2 bg-indigo-50/70 border-t border-indigo-100">
            <a href="index.php?action=dashboard" class="flex-1 text-center px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-md shadow-sm">
                Mis Requerimientos
            </a>
            <button type="button" data-open-cuenta class="flex-1 px-3 py-1.5 bg-white text-indigo-700 border border-indigo-200 hover:bg-indigo-100 text-xs font-semibold rounded-md transition inline-flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span>Mi Cuenta</span>
            </button>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto p-3 sm:p-6 lg:p-8 space-y-5 sm:space-y-6">

        <!-- Hero Section -->
        <div class="bg-hero-gradient rounded-2xl p-5 sm:p-8 border border-indigo-100 shadow-sm relative overflow-hidden">
            <div class="relative z-10">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-100/80 text-indigo-700 text-[11px] sm:text-xs font-bold uppercase tracking-wider mb-3 sm:mb-4 border border-indigo-200">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    Mesa de Ayuda Corporativa
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-2 leading-tight">Centro de Ayuda y Requerimientos TI</h1>
                <p class="text-slate-600 max-w-2xl text-xs sm:text-sm leading-relaxed">¿Tienes un problema técnico o necesitas un nuevo acceso? Registra tu solicitud y nuestro equipo de soporte te responderá a la brevedad.</p>
            </div>
            <!-- Decoración fondo -->
            <div class="hidden sm:block absolute right-0 top-0 w-64 h-full bg-gradient-to-l from-indigo-100/50 to-transparent pointer-events-none"></div>
        </div>

        <!-- Formulario: Nuevo Requerimiento -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-start sm:items-center gap-3">
                    <div class="w-8 h-8 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-800">Nuevo Requerimiento de Soporte TI</h2>
                        <p class="text-xs text-slate-500">Completa los detalles a continuación. Los campos marcados con asterisco (*) son prioritarios.</p>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                <!-- Alerta para mensajes JS -->
                <div id="alertaReq" class="hidden px-4 py-3 rounded-lg text-sm font-semibold mb-5 flex items-center gap-2"></div>

                <form id="formRequerimiento" enctype="multipart/form-data" class="space-y-4 sm:space-y-5">
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
                                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </span>
                            <select name="prioridad" required class="w-full pl-9 pr-9 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 appearance-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium truncate">
                                <option value="baja">Baja - Consulta general o mejora (No bloquea)</option>
                                <option value="media" selected>Media - Falla parcial (Trabajo posible)</option>
                                <option value="alta">Alta - Falla total (Bloqueo de trabajo)</option>
                                <option value="critica">Crítica - Afectación masiva / Caída de sistema</option>
                            </select>
                            <svg class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Descripción Técnica -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Descripción Técnica <span class="text-rose-500">*</span></label>
                        <textarea name="descripcion_tecnica" required rows="4" placeholder="Describe detalladamente el incidente ..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition resize-none"></textarea>
                    </div>
                    <div class="pt-1">
                        <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1">Capturas de Pantalla / Evidencias <span class="text-slate-400 font-normal text-xs">(Opcional)</span></label>
                        <p class="text-[11px] text-slate-500 mb-2">Puedes subir hasta un máximo de 3 imágenes (JPG, PNG, WEBP).</p>
                        <input type="file" name="evidencias[]" multiple accept="image/jpeg, image/png, image/webp" onchange="if(this.files.length > 3) { alert('Solo puedes subir un máximo de 3 imágenes.'); this.value = ''; }"
                            class="w-full text-xs sm:text-sm text-slate-500 file:mr-3 sm:file:mr-4 file:py-1.5 sm:file:py-2 file:px-3 sm:file:px-4 file:rounded-lg file:border-0 file:text-xs sm:file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition cursor-pointer border border-slate-200 rounded-lg bg-white p-1">
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2.5 sm:gap-3 pt-4 border-t border-slate-100">
                        <button type="reset" class="w-full sm:w-auto px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-lg transition text-center">Limpiar</button>
                        <button type="submit" id="btnReq" class="w-full sm:w-auto px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-md flex items-center justify-center gap-2 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            <span>Enviar Requerimiento</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Historial de Solicitudes -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-200 flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-800">Historial de Solicitudes</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Monitorea el progreso de tus tickets de soporte.</p>
                </div>
                <?php if (!empty($requerimientos)): ?>
                    <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-xs font-bold rounded-full shrink-0">
                        <?= count($requerimientos) ?> <?= count($requerimientos) === 1 ? 'ticket' : 'tickets' ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Vista Móvil (Tarjetas) -->
            <div class="md:hidden divide-y divide-slate-100">
                <?php if (empty($requerimientos)): ?>
                    <div class="p-8 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <p class="font-medium text-slate-600 text-sm">No tienes requerimientos registrados.</p>
                            <p class="text-xs mt-1">Tus tickets aparecerán aquí una vez que envíes el formulario.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($requerimientos as $req):
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
                        $prio = strtolower($req['prioridad']);
                        $bgPrio = ($prio === 'alta' || $prio === 'critica') ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600';
                    ?>
                        <div class="p-4 space-y-3 hover:bg-slate-50/50 transition">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono text-xs font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">#TK-<?= htmlspecialchars($req['id_requerimiento']) ?></span>
                                    <span class="px-1.5 py-0.5 <?= $bgPrio ?> text-[10px] font-bold rounded uppercase"><?= htmlspecialchars($prio) ?></span>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full <?= $bgEstado ?> border text-[11px] font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $dotEstado ?>"></span> <?= ucfirst($estado) ?>
                                </span>
                            </div>

                            <div>
                                <p class="font-bold text-slate-800 text-sm leading-snug"><?= htmlspecialchars($req['titulo']) ?></p>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2"><?= htmlspecialchars($req['descripcion_tecnica']) ?></p>
                            </div>

                            <div class="flex items-center justify-between gap-3 pt-2 border-t border-slate-100">
                                <div class="text-xs text-slate-500 min-w-0">
                                    <p class="font-medium text-slate-700 truncate">
                                        <?= !empty($req['responsable']) ? 'Asignado: ' . htmlspecialchars($req['responsable']) : 'En evaluación' ?>
                                    </p>
                                    <p class="text-[11px] text-slate-400"><?= date('d M Y · h:i A', strtotime($req['fecha_creacion'])) ?></p>
                                </div>
                                <a href="index.php?action=ver_ticket&id=<?= $req['id_requerimiento'] ?>"
                                    class="px-3 py-1.5 bg-white border border-slate-200 hover:border-indigo-300 hover:text-indigo-600 text-slate-700 text-xs font-semibold rounded-md shadow-sm transition inline-flex items-center gap-1.5 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Detalle
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Vista Tablet / Desktop (Tabla) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 text-[10px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="p-4 pl-5">ID</th>
                            <th class="p-4">TÍTULO Y DESCRIPCIÓN</th>
                            <th class="p-4">FECHA DE REGISTRO</th>
                            <th class="p-4">ESTADO</th>
                            <th class="p-4">ASIGNACIÓN</th>
                            <th class="p-4 text-center">ACCIÓN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php if (empty($requerimientos)): ?>
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
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
                                    <!-- ID del ticket -->
                                    <td class="p-4 pl-5 align-top">
                                        <span class="font-mono text-xs font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">#TK-<?= htmlspecialchars($req['id_requerimiento']) ?></span>
                                    </td>

                                    <!-- Título y Descripción -->
                                    <td class="p-4 align-top max-w-md">
                                        <div class="flex items-center gap-2 mb-1">
                                            <p class="font-bold text-slate-800"><?= htmlspecialchars($req['titulo']) ?></p>
                                            <span class="px-1.5 py-0.5 <?= $bgPrio ?> text-[9px] font-bold rounded uppercase"><?= htmlspecialchars($prio) ?></span>
                                        </div>
                                        <p class="text-xs text-slate-500 truncate" title="<?= htmlspecialchars($req['descripcion_tecnica']) ?>">
                                            <?= htmlspecialchars($req['descripcion_tecnica']) ?>
                                        </p>
                                    </td>

                                    <!-- Fecha -->
                                    <td class="p-4 align-top text-xs">
                                        <p class="font-medium text-slate-800"><?= date('d M Y', strtotime($req['fecha_creacion'])) ?></p>
                                        <p class="text-slate-400 mt-0.5"><?= date('h:i A', strtotime($req['fecha_creacion'])) ?></p>
                                    </td>

                                    <!-- Estado -->
                                    <td class="p-4 align-top">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full <?= $bgEstado ?> border text-xs font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full <?= $dotEstado ?>"></span> <?= ucfirst($estado) ?>
                                        </span>
                                    </td>

                                    <!-- Asignación -->
                                    <td class="p-4 align-top">
                                        <?php if (!empty($req['responsable'])): ?>
                                            <!-- Muestra el responsable cuando ya está asignado -->
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-[10px] font-bold uppercase border border-indigo-200">
                                                    <?= substr(htmlspecialchars($req['responsable']), 0, 2) ?>
                                                </div>
                                                <div class="text-xs">
                                                    <p class="font-semibold text-slate-700"><?= htmlspecialchars($req['responsable']) ?></p>
                                                    <p class="text-emerald-600 text-[10px] font-medium">Asignado</p>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <!-- Estado por defecto cuando aún no tiene asignación -->
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full border border-dashed border-slate-300 flex items-center justify-center text-slate-400 text-xs">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                </div>
                                                <div class="text-xs">
                                                    <p class="font-semibold text-slate-500">En evaluación</p>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Columna de Acción (Enlace al detalle) -->
                                    <td class="p-4 align-top text-center">
                                        <a href="index.php?action=ver_ticket&id=<?= $req['id_requerimiento'] ?>"
                                            class="px-3 py-1.5 bg-white border border-slate-200 hover:border-indigo-300 hover:text-indigo-600 text-slate-600 text-xs font-semibold rounded-md shadow-sm transition inline-flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            Detalle
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Modal: Mi Cuenta -->
    <div id="modalMiCuenta" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-3 sm:p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg my-auto overflow-hidden animate-fade-in">
            <!-- Cabecera -->
            <div class="px-4 sm:px-6 py-3.5 sm:py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between gap-2">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm sm:text-base uppercase shrink-0">
                        <?= htmlspecialchars(mb_substr($usuario['nombre'], 0, 1)) ?>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm sm:text-base font-bold text-slate-900">Mi Cuenta</h3>
                        <p class="text-xs text-slate-500 truncate">Administra tus datos personales y seguridad</p>
                    </div>
                </div>
                <button type="button" id="btnCerrarMiCuenta" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-200/60 transition shrink-0" aria-label="Cerrar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Cuerpo -->
            <div class="p-4 sm:p-6 space-y-4 sm:space-y-5 max-h-[82vh] overflow-y-auto">
                <!-- Alerta global del modal -->
                <div id="alertaCuenta" class="hidden px-4 py-3 rounded-lg text-xs sm:text-sm font-semibold flex items-center gap-2"></div>

                <!-- Resumen de Datos -->
                <div class="bg-slate-50 rounded-xl border border-slate-200 divide-y divide-slate-200">
                    <!-- Nombre -->
                    <div class="px-3.5 sm:px-4 py-3 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nombre</p>
                            <p class="text-sm font-semibold text-slate-800 truncate"><?= htmlspecialchars($usuario['nombre']) ?></p>
                        </div>
                        <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-700 text-[11px] font-bold rounded-full uppercase shrink-0">
                            <?= htmlspecialchars($usuario['rol']) ?>
                        </span>
                    </div>

                    <!-- Correo electrónico -->
                    <div class="px-3.5 sm:px-4 py-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3">
                            <div class="min-w-0">
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Correo Electrónico</p>
                                <p id="cuentaCorreoTexto" class="text-sm font-semibold text-slate-800 break-all sm:truncate"><?= htmlspecialchars($usuario['correo'] ?? '') ?></p>
                            </div>
                            <button type="button" id="btnToggleEditarCorreo" class="w-full sm:w-auto px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition shrink-0 text-center">
                                Editar correo
                            </button>
                        </div>

                        <!-- Formulario desplegable: Editar correo -->
                        <form id="formEditarCorreo" class="hidden mt-3 pt-3 border-t border-slate-200 space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Nuevo correo electrónico</label>
                                <input type="email" name="nuevo_correo" id="inputNuevoCorreo" required value="<?= htmlspecialchars($usuario['correo'] ?? '') ?>"
                                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Contraseña actual (para confirmar)</label>
                                <input type="password" name="password_actual" required placeholder="••••••••"
                                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            </div>
                            <div class="flex flex-col-reverse sm:flex-row justify-end gap-2">
                                <button type="button" id="btnCancelarCorreo" class="w-full sm:w-auto px-3 py-2 sm:py-1.5 text-xs font-semibold text-slate-600 bg-slate-200/70 hover:bg-slate-200 rounded-lg transition text-center">Cancelar</button>
                                <button type="submit" id="btnGuardarCorreo" class="w-full sm:w-auto px-4 py-2 sm:py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition text-center">Guardar correo</button>
                            </div>
                        </form>
                    </div>

                    <!-- Contraseña (siempre oculta como ********) -->
                    <div class="px-3.5 sm:px-4 py-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3">
                            <div>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Contraseña</p>
                                <p class="text-sm font-mono font-bold text-slate-700 tracking-widest select-none">********</p>
                            </div>
                            <button type="button" id="btnToggleEditarPassword" class="w-full sm:w-auto px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition shrink-0 text-center">
                                Cambiar contraseña
                            </button>
                        </div>

                        <!-- Formulario desplegable: Cambiar contraseña -->
                        <form id="formEditarPassword" class="hidden mt-3 pt-3 border-t border-slate-200 space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Contraseña actual</label>
                                <input type="password" name="password_actual" required placeholder="••••••••"
                                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nueva contraseña</label>
                                    <input type="password" name="nueva_password" required minlength="6" placeholder="Mín. 6 caracteres"
                                        class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Confirmar contraseña</label>
                                    <input type="password" name="confirm_password" required minlength="6" placeholder="Repetir contraseña"
                                        class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                                </div>
                            </div>
                            <div class="flex flex-col-reverse sm:flex-row justify-end gap-2">
                                <button type="button" id="btnCancelarPassword" class="w-full sm:w-auto px-3 py-2 sm:py-1.5 text-xs font-semibold text-slate-600 bg-slate-200/70 hover:bg-slate-200 rounded-lg transition text-center">Cancelar</button>
                                <button type="submit" id="btnGuardarPassword" class="w-full sm:w-auto px-4 py-2 sm:py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition text-center">Actualizar contraseña</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Zona de Peligro: Eliminar Cuenta -->
                <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-3.5 sm:p-4">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-bold text-rose-800">Eliminar cuenta</h4>
                            <p class="text-xs text-rose-700/90 mt-0.5 leading-relaxed">
                                Esta acción es permanente e irreversible. Se eliminarán tu cuenta y todos tus requerimientos registrados.
                            </p>
                        </div>
                        <button type="button" id="btnToggleEliminarCuenta" class="w-full sm:w-auto px-3 py-2 sm:py-1.5 text-xs font-semibold text-rose-700 bg-white border border-rose-300 hover:bg-rose-600 hover:text-white hover:border-rose-600 rounded-lg transition shrink-0 text-center">
                            Eliminar cuenta
                        </button>
                    </div>

                    <!-- Confirmación de eliminación -->
                    <form id="formEliminarCuenta" class="hidden mt-3 pt-3 border-t border-rose-200 space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-rose-800 mb-1">Ingresa tu contraseña actual para confirmar la eliminación definitiva</label>
                            <input type="password" name="password_actual" required placeholder="Tu contraseña actual"
                                class="w-full px-3 py-2 bg-white border border-rose-300 rounded-lg text-sm focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none">
                        </div>
                        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2">
                            <button type="button" id="btnCancelarEliminar" class="w-full sm:w-auto px-3 py-2 sm:py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 rounded-lg transition text-center">Cancelar</button>
                            <button type="submit" id="btnConfirmarEliminar" class="w-full sm:w-auto px-4 py-2 sm:py-1.5 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-sm transition text-center">Sí, eliminar definitivamente</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Archivo JS separado -->
    <script src="view/javascript/cliente.js?v=<?= filemtime(__DIR__ . '/javascript/cliente.js') ?>"></script>
</body>

</html>