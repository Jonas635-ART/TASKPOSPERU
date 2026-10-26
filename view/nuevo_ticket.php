<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$usuario = $_SESSION['usuario'];

// Lógica Dinámica: Detectar rol para la barra lateral
$rol = strtolower($usuario['rol']);
$esAdmin = ($rol === 'admin');
$esTecnico = ($rol === 'admin' || $rol === 'developer');

$bgLogo = $esAdmin ? 'bg-rose-500' : 'bg-indigo-500';
$textLogo = $esAdmin ? 'text-rose-400' : 'text-indigo-400';
$badgeTexto = $esAdmin ? 'ADMIN' : 'PRO';
$tituloMenu = $esAdmin ? 'Gestión Global' : 'Operaciones';
$bgAvatar = $esAdmin ? 'bg-rose-900/50 text-rose-300 border-rose-500/30' : 'bg-indigo-900/50 text-indigo-300 border-indigo-500/30';
$textRol = $esAdmin ? 'text-rose-400' : 'text-slate-400';
$labelRol = $esAdmin ? 'Admin' : $usuario['correo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Ticket | PosPerú PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="view/css/developer.css">
</head>
<body class="flex h-screen overflow-hidden text-slate-800 antialiased bg-slate-50">

    <!-- SIDEBAR DINÁMICO (Mantiene la navegación sin tapar la pantalla) -->
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
                    <a href="index.php?action=kanban" class="sidebar-item hover:bg-slate-800 hover:text-white flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
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
                    <p class="text-[10px] <?= $textRol ?> font-bold uppercase tracking-wider truncate"><?= htmlspecialchars($labelRol) ?></p>
                </div>
            </div>
        </div>
    </aside>

    <!-- CONTENIDO PRINCIPAL (Formulario) -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        
        <!-- Navbar Superior -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0 shadow-sm z-10">
            <h1 class="text-lg font-black text-slate-800 tracking-tight">Registrar Nuevo Ticket</h1>
            
            <a href="index.php?action=dashboard" class="text-sm font-semibold text-slate-500 hover:text-indigo-600 transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Volver al Panel
            </a>
        </header>

        <!-- Área del Formulario Scrollable -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto p-6 flex justify-center custom-scrollbar">
            
            <div class="w-full max-w-2xl bg-white rounded-xl border border-slate-200 shadow-sm p-8 mt-4 h-fit mb-10">
                <form action="index.php?action=guardar_ticket" method="POST" enctype="multipart/form-data" class="space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <!-- Bloque de Proyecto (Solo para Admin y Developer) -->
                        <?php if ($esTecnico): ?>
                        <div class="md:col-span-2 mb-2">
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Proyecto Asociado <span class="text-slate-400 font-normal text-xs normal-case">(Opcional)</span>
                            </label>
                            <div class="relative">
                                <select name="id_proyecto" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 appearance-none transition">
                                    <option value="">-- Sin proyecto asignado (Ticket independiente) --</option>
                                    <?php foreach($proyectos ?? [] as $p): ?>
                                        <option value="<?= $p['id_proyecto'] ?? $p['id'] ?>">
                                            <?= htmlspecialchars($p['nombre_proyecto'] ?? $p['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <svg class="w-4 h-4 text-slate-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Título del Requerimiento *</label>
                            <input type="text" name="titulo" required placeholder="Ej: Error al emitir boleta electrónica" 
                                   class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Descripción Técnica *</label>
                            <textarea name="descripcion_tecnica" required rows="4" placeholder="Detalla el problema o requerimiento..." 
                                      class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Prioridad</label>
                            <select name="prioridad" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition font-medium">
                                <option value="baja">Baja (Mantenimiento / UI)</option>
                                <option value="media" selected>Media (Funcionalidad no crítica)</option>
                                <option value="alta">Alta (Falla en módulo principal)</option>
                                <option value="critica">Crítica (Caída del sistema / Facturación)</option>
                            </select>
                        </div>

                        <!-- Selector de Desarrollador -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Asignar a Desarrollador</label>
                            <select name="id_responsable" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition font-medium">
                                <option value="">-- Dejar en bandeja Pendiente --</option>
                                <?php foreach ($tecnicos ?? [] as $tec): ?>
                                    <option value="<?= $tec['id_usuario'] ?>"><?= htmlspecialchars($tec['nombre']) ?> (<?= strtoupper($tec['rol']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-4">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Subtareas Técnicas</label>
                        <div id="contenedor-subtareas" class="space-y-2 mb-2">
                            <input type="text" name="subtareas[]" placeholder="Ej: Actualizar endpoint de facturación..." class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm outline-none focus:border-indigo-500">
                        </div>
                        <button type="button" onclick="agregarSubtarea()" class="text-xs font-bold text-indigo-600 hover:underline">+ Agregar otra subtarea</button>
                    </div>

                    <div class="border-t border-slate-200 pt-4">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Evidencias (Imágenes)</label>
                        <p class="text-[10px] text-slate-500 mb-2">Puedes subir hasta un máximo de 3 imágenes (JPG, PNG).</p>
                        <input type="file" name="evidencias[]" id="inputArchivos" multiple accept="image/*" onchange="validarLimite(this)"
                               class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition cursor-pointer">
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                        <a href="index.php?action=dashboard" class="px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-lg transition">Cancelar</a>
                        <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition">Guardar Ticket</button>
                    </div>
                </form>

                <script>
                    function agregarSubtarea() {
                        const contenedor = document.getElementById('contenedor-subtareas');
                        const input = document.createElement('input');
                        input.type = 'text';
                        input.name = 'subtareas[]';
                        input.placeholder = 'Nueva subtarea...';
                        input.className = 'w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm outline-none focus:border-indigo-500 mt-2';
                        contenedor.appendChild(input);
                    }

                    function validarLimite(input) {
                        if (input.files.length > 3) {
                            alert("Solo puedes subir un máximo de 3 imágenes por requerimiento.");
                            input.value = ""; // Limpia la selección
                        }
                    }
                </script>
            </div>
            
        </div>
    </main>
</body>
</html>