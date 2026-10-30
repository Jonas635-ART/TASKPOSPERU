<?php
if (!defined('APP_INIT')) { http_response_code(403); exit(); }
$usuario = $_SESSION['usuario'];
$paginaActiva = 'usuarios_reset';
$usuarios = $usuarios ?? [];
$notificaciones = $notificaciones ?? [];

$pendientesCount = 0;
foreach ($usuarios as $u) {
    if (($u['estado_reset'] ?? '') === 'pendiente') {
        $pendientesCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accesos & Contraseñas | PosPerú ADMIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="view/css/developer.css">
</head>
<body class="flex h-dvh overflow-hidden text-slate-800 antialiased bg-slate-50">

    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 min-w-0 flex flex-col h-full overflow-hidden relative">

        <!-- Top Navbar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center gap-3 px-4 lg:px-6 shrink-0 shadow-sm z-10">
            <button id="btnMenu" type="button" class="lg:hidden -ml-1 p-2 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-100" aria-label="Abrir menú">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>

            <div class="flex-1 min-w-0 max-w-xl">
                <div class="relative w-full">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input id="buscarUsuario" type="search" placeholder="Buscar usuario por nombre o correo..." class="w-full pl-10 pr-4 py-2 bg-slate-100 border-transparent focus:bg-white border focus:border-indigo-500 rounded-lg text-sm outline-none transition">
                </div>
            </div>

            <?php if ($pendientesCount > 0): ?>
                <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    <?= $pendientesCount ?> Solicitud<?= $pendientesCount === 1 ? '' : 'es' ?> pendiente<?= $pendientesCount === 1 ? '' : 's' ?>
                </span>
            <?php endif; ?>
        </header>

        <!-- Contenido Principal -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-4 lg:p-6">
            <div class="mb-6">
                <h1 class="text-xl lg:text-2xl font-black text-slate-800">Recuperación de Contraseñas (Admin)</h1>
                <p class="text-sm text-slate-500">Genera códigos temporales de un solo uso (válidos por 30 minutos) para los usuarios que olvidaron su contraseña.</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[680px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-5">Usuario</th>
                                <th class="py-3.5 px-4">Rol</th>
                                <th class="py-3.5 px-4">Estado de Recuperación</th>
                                <th class="py-3.5 px-4">Última Solicitud</th>
                                <th class="py-3.5 px-5 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm" id="tablaUsuariosReset">
                            <?php foreach ($usuarios as $u):
                                $rolU = strtolower($u['rol']);
                                $rolBadge = $rolU === 'admin'
                                    ? 'bg-rose-100 text-rose-700 border-rose-200'
                                    : ($rolU === 'developer' ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-slate-100 text-slate-600 border-slate-200');

                                $estadoReset = $u['estado_reset'] ?? '';
                                $codigoVigente = !empty($u['codigo_vigente']);
                            ?>
                            <tr class="fila-usuario hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-5">
                                    <p class="font-bold text-slate-800"><?= htmlspecialchars($u['nombre']) ?></p>
                                    <p class="text-xs text-slate-400"><?= htmlspecialchars($u['correo']) ?></p>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded border <?= $rolBadge ?>"><?= htmlspecialchars($u['rol']) ?></span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if ($estadoReset === 'pendiente'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Solicitud pendiente
                                        </span>
                                    <?php elseif ($estadoReset === 'codigo_generado' && $codigoVigente): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Código activo (30m)
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">Sin solicitud activa</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-500">
                                    <?= !empty($u['fecha_solicitud']) ? date('d/m/Y H:i', strtotime($u['fecha_solicitud'])) : '—' ?>
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <button type="button"
                                            onclick="generarCodigoReset(<?= (int)$u['id_usuario'] ?>, this)"
                                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition <?= $estadoReset === 'pendiente' ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-indigo-50 hover:text-indigo-600' ?>">
                                        Generar código temporal
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- MODAL CON EL CÓDIGO GENERADO -->
    <div id="modalCodigo" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden border border-slate-200">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-base font-black text-slate-800">Código Temporal Generado</h3>
                <button type="button" onclick="cerrarModalCodigo()" class="text-slate-400 hover:text-rose-500 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-xs text-slate-500">
                    Comparte este código con <strong id="modalUsuarioNombre" class="text-slate-800"></strong> (<span id="modalUsuarioCorreo" class="font-mono"></span>). Solo se muestra una vez y vence en <strong>30 minutos</strong>:
                </p>

                <div class="flex items-center justify-between gap-2 bg-indigo-50 border border-indigo-200 rounded-xl px-4 py-3">
                    <span id="modalCodigoTexto" class="font-mono text-xl font-black text-indigo-700 tracking-wider select-all"></span>
                    <button type="button" id="btnCopiarCodigo" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition shrink-0">
                        Copiar
                    </button>
                </div>

                <p class="text-[11px] text-slate-400">
                    El usuario debe ingresar a <strong>¿Olvidaste tu contraseña? &rarr; 2. Ya tengo código</strong> en la pantalla de Login.
                </p>

                <div class="pt-2 flex justify-end">
                    <button type="button" onclick="window.location.reload()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-lg transition">
                        Listo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            const btnMenu = document.getElementById('btnMenu');
            const btnCerrar = document.getElementById('btnCerrarSidebar');

            function toggleSidebar(abrir) {
                if (!sidebar || !backdrop) return;
                sidebar.classList.toggle('-translate-x-full', !abrir);
                backdrop.classList.toggle('hidden', !abrir);
            }
            if (btnMenu) btnMenu.addEventListener('click', () => toggleSidebar(true));
            if (btnCerrar) btnCerrar.addEventListener('click', () => toggleSidebar(false));
            if (backdrop) backdrop.addEventListener('click', () => toggleSidebar(false));

            const buscador = document.getElementById('buscarUsuario');
            const filas = document.querySelectorAll('.fila-usuario');
            if (buscador) {
                buscador.addEventListener('input', () => {
                    const q = buscador.value.trim().toLowerCase();
                    filas.forEach(f => {
                        f.classList.toggle('hidden', q && !f.textContent.toLowerCase().includes(q));
                    });
                });
            }

            const btnCopiar = document.getElementById('btnCopiarCodigo');
            if (btnCopiar) {
                btnCopiar.addEventListener('click', async () => {
                    const codigo = document.getElementById('modalCodigoTexto').textContent;
                    try {
                        await navigator.clipboard.writeText(codigo);
                        btnCopiar.textContent = '¡Copiado!';
                        setTimeout(() => { btnCopiar.textContent = 'Copiar'; }, 1800);
                    } catch (_) {}
                });
            }
        });

        async function generarCodigoReset(idUsuario, btn) {
            const textoOriginal = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Generando...';

            try {
                const formData = new FormData();
                formData.append('id_usuario', idUsuario);

                const res = await fetch('index.php?action=api_generar_codigo_reset', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (!data.success) {
                    alert(data.message || 'No se pudo generar el código.');
                    btn.disabled = false;
                    btn.textContent = textoOriginal;
                    return;
                }

                document.getElementById('modalUsuarioNombre').textContent = data.usuario.nombre;
                document.getElementById('modalUsuarioCorreo').textContent = data.usuario.correo;
                document.getElementById('modalCodigoTexto').textContent = data.codigo;
                document.getElementById('modalCodigo').classList.remove('hidden');
            } catch (err) {
                alert('Error de conexión con el servidor.');
            } finally {
                btn.disabled = false;
                btn.textContent = textoOriginal;
            }
        }

        function cerrarModalCodigo() {
            window.location.reload();
        }
    </script>
</body>
</html>

