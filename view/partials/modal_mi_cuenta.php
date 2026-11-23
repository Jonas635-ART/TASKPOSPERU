<?php
// Modal reutilizable de gestión de cuenta propia (cliente, developer, admin).
// Requiere sesión iniciada ($_SESSION['usuario']).
if (!defined('APP_INIT')) { http_response_code(403); exit(); }
$usuarioCuenta = $_SESSION['usuario'] ?? ($usuario ?? ['nombre' => 'Usuario', 'correo' => '', 'rol' => 'cliente']);
$esClienteCuenta = strtolower($usuarioCuenta['rol'] ?? '') === 'cliente';
$textoEliminarCuenta = $esClienteCuenta
    ? 'Esta acción es permanente e irreversible. Se eliminarán tu cuenta y todos tus requerimientos registrados.'
    : 'Esta acción es permanente e irreversible. Se eliminará tu cuenta, tus comentarios y los requerimientos que hayas creado.';
?>
<!-- Modal: Mi Cuenta -->
<div id="modalMiCuenta" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-3 sm:p-4 overflow-y-auto text-slate-800">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg my-auto overflow-hidden">
        <!-- Cabecera -->
        <div class="px-4 sm:px-6 py-3.5 sm:py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between gap-2">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm sm:text-base uppercase shrink-0">
                    <?= htmlspecialchars(mb_substr($usuarioCuenta['nombre'], 0, 1)) ?>
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
                        <p class="text-sm font-semibold text-slate-800 truncate"><?= htmlspecialchars($usuarioCuenta['nombre']) ?></p>
                    </div>
                    <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-700 text-[11px] font-bold rounded-full uppercase shrink-0">
                        <?= htmlspecialchars($usuarioCuenta['rol']) ?>
                    </span>
                </div>

                <!-- Correo electrónico -->
                <div class="px-3.5 sm:px-4 py-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Correo Electrónico</p>
                            <p id="cuentaCorreoTexto" class="text-sm font-semibold text-slate-800 break-all sm:truncate"><?= htmlspecialchars($usuarioCuenta['correo'] ?? '') ?></p>
                        </div>
                        <button type="button" id="btnToggleEditarCorreo" class="w-full sm:w-auto px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition shrink-0 text-center">
                            Editar correo
                        </button>
                    </div>

                    <!-- Formulario desplegable: Editar correo -->
                    <form id="formEditarCorreo" class="hidden mt-3 pt-3 border-t border-slate-200 space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nuevo correo electrónico</label>
                            <input type="email" name="nuevo_correo" id="inputNuevoCorreo" required value="<?= htmlspecialchars($usuarioCuenta['correo'] ?? '') ?>"
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
                            <?= htmlspecialchars($textoEliminarCuenta) ?>
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
<script src="view/javascript/mi_cuenta.js?v=<?= filemtime(__DIR__ . '/../javascript/mi_cuenta.js') ?>"></script>
