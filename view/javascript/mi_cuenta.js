document.addEventListener('DOMContentLoaded', () => {
    const btnsAbrirCuenta = document.querySelectorAll('[data-open-cuenta]');
    const modalMiCuenta = document.getElementById('modalMiCuenta');
    if (!modalMiCuenta || btnsAbrirCuenta.length === 0) return;

    const btnCerrarMiCuenta = document.getElementById('btnCerrarMiCuenta');
    const alertaCuenta = document.getElementById('alertaCuenta');
    const cuentaCorreoTexto = document.getElementById('cuentaCorreoTexto');
    const inputNuevoCorreo = document.getElementById('inputNuevoCorreo');

    const btnToggleEditarCorreo = document.getElementById('btnToggleEditarCorreo');
    const formEditarCorreo = document.getElementById('formEditarCorreo');
    const btnCancelarCorreo = document.getElementById('btnCancelarCorreo');
    const btnGuardarCorreo = document.getElementById('btnGuardarCorreo');

    const btnToggleEditarPassword = document.getElementById('btnToggleEditarPassword');
    const formEditarPassword = document.getElementById('formEditarPassword');
    const btnCancelarPassword = document.getElementById('btnCancelarPassword');
    const btnGuardarPassword = document.getElementById('btnGuardarPassword');

    const btnToggleEliminarCuenta = document.getElementById('btnToggleEliminarCuenta');
    const formEliminarCuenta = document.getElementById('formEliminarCuenta');
    const btnCancelarEliminar = document.getElementById('btnCancelarEliminar');
    const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');

    const mostrarAlertaCuenta = (exito, mensaje) => {
        if (!alertaCuenta) return;
        alertaCuenta.className = 'px-4 py-3 rounded-lg text-xs sm:text-sm font-semibold flex items-center gap-2 ' + (
            exito
                ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                : 'bg-rose-50 text-rose-700 border border-rose-200'
        );
        alertaCuenta.textContent = mensaje;
    };

    const limpiarAlertaCuenta = () => {
        if (!alertaCuenta) return;
        alertaCuenta.className = 'hidden px-4 py-3 rounded-lg text-xs sm:text-sm font-semibold flex items-center gap-2';
        alertaCuenta.textContent = '';
    };

    const cerrarFormulariosCuenta = () => {
        if (formEditarCorreo) formEditarCorreo.classList.add('hidden');
        if (formEditarPassword) formEditarPassword.classList.add('hidden');
        if (formEliminarCuenta) formEliminarCuenta.classList.add('hidden');
    };

    const abrirModalCuenta = () => {
        limpiarAlertaCuenta();
        cerrarFormulariosCuenta();
        modalMiCuenta.classList.remove('hidden');
    };

    const cerrarModalCuenta = () => {
        modalMiCuenta.classList.add('hidden');
        limpiarAlertaCuenta();
        cerrarFormulariosCuenta();
    };

    btnsAbrirCuenta.forEach((btn) => btn.addEventListener('click', abrirModalCuenta));

    if (btnCerrarMiCuenta) {
        btnCerrarMiCuenta.addEventListener('click', cerrarModalCuenta);
    }

    modalMiCuenta.addEventListener('click', (e) => {
        if (e.target === modalMiCuenta) cerrarModalCuenta();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modalMiCuenta.classList.contains('hidden')) {
            cerrarModalCuenta();
        }
    });

    if (btnToggleEditarCorreo && formEditarCorreo) {
        btnToggleEditarCorreo.addEventListener('click', () => {
            const estabaOculto = formEditarCorreo.classList.contains('hidden');
            limpiarAlertaCuenta();
            cerrarFormulariosCuenta();
            if (estabaOculto) {
                if (inputNuevoCorreo && cuentaCorreoTexto) {
                    inputNuevoCorreo.value = cuentaCorreoTexto.textContent.trim();
                }
                formEditarCorreo.classList.remove('hidden');
            }
        });
    }
    if (btnCancelarCorreo && formEditarCorreo) {
        btnCancelarCorreo.addEventListener('click', () => {
            formEditarCorreo.reset();
            formEditarCorreo.classList.add('hidden');
            limpiarAlertaCuenta();
        });
    }

    if (btnToggleEditarPassword && formEditarPassword) {
        btnToggleEditarPassword.addEventListener('click', () => {
            const estabaOculto = formEditarPassword.classList.contains('hidden');
            limpiarAlertaCuenta();
            cerrarFormulariosCuenta();
            if (estabaOculto) {
                formEditarPassword.reset();
                formEditarPassword.classList.remove('hidden');
            }
        });
    }
    if (btnCancelarPassword && formEditarPassword) {
        btnCancelarPassword.addEventListener('click', () => {
            formEditarPassword.reset();
            formEditarPassword.classList.add('hidden');
            limpiarAlertaCuenta();
        });
    }

    if (btnToggleEliminarCuenta && formEliminarCuenta) {
        btnToggleEliminarCuenta.addEventListener('click', () => {
            const estabaOculto = formEliminarCuenta.classList.contains('hidden');
            limpiarAlertaCuenta();
            cerrarFormulariosCuenta();
            if (estabaOculto) {
                formEliminarCuenta.reset();
                formEliminarCuenta.classList.remove('hidden');
            }
        });
    }
    if (btnCancelarEliminar && formEliminarCuenta) {
        btnCancelarEliminar.addEventListener('click', () => {
            formEliminarCuenta.reset();
            formEliminarCuenta.classList.add('hidden');
            limpiarAlertaCuenta();
        });
    }

    // Enviar cambio de correo
    if (formEditarCorreo) {
        formEditarCorreo.addEventListener('submit', async (e) => {
            e.preventDefault();
            limpiarAlertaCuenta();
            const textoOriginal = btnGuardarCorreo ? btnGuardarCorreo.textContent : '';
            if (btnGuardarCorreo) {
                btnGuardarCorreo.disabled = true;
                btnGuardarCorreo.textContent = 'Guardando...';
            }

            try {
                const res = await fetch('index.php?action=api_actualizar_correo', {
                    method: 'POST',
                    body: new FormData(formEditarCorreo),
                });
                const data = await res.json();
                mostrarAlertaCuenta(!!data.success, data.message || 'Error inesperado.');

                if (data.success) {
                    if (cuentaCorreoTexto && data.correo) {
                        cuentaCorreoTexto.textContent = data.correo;
                    }
                    document.querySelectorAll('[data-cuenta-correo]').forEach((el) => {
                        if (data.correo) el.textContent = data.correo;
                    });
                    formEditarCorreo.reset();
                    if (inputNuevoCorreo && data.correo) {
                        inputNuevoCorreo.value = data.correo;
                    }
                    formEditarCorreo.classList.add('hidden');
                }
            } catch (err) {
                console.error(err);
                mostrarAlertaCuenta(false, 'Error de conexión con el servidor.');
            } finally {
                if (btnGuardarCorreo) {
                    btnGuardarCorreo.disabled = false;
                    btnGuardarCorreo.textContent = textoOriginal;
                }
            }
        });
    }

    // Enviar cambio de contraseña
    if (formEditarPassword) {
        formEditarPassword.addEventListener('submit', async (e) => {
            e.preventDefault();
            limpiarAlertaCuenta();
            const textoOriginal = btnGuardarPassword ? btnGuardarPassword.textContent : '';
            if (btnGuardarPassword) {
                btnGuardarPassword.disabled = true;
                btnGuardarPassword.textContent = 'Actualizando...';
            }

            try {
                const res = await fetch('index.php?action=api_actualizar_password', {
                    method: 'POST',
                    body: new FormData(formEditarPassword),
                });
                const data = await res.json();
                mostrarAlertaCuenta(!!data.success, data.message || 'Error inesperado.');

                if (data.success) {
                    formEditarPassword.reset();
                    formEditarPassword.classList.add('hidden');
                }
            } catch (err) {
                console.error(err);
                mostrarAlertaCuenta(false, 'Error de conexión con el servidor.');
            } finally {
                if (btnGuardarPassword) {
                    btnGuardarPassword.disabled = false;
                    btnGuardarPassword.textContent = textoOriginal;
                }
            }
        });
    }

    // Enviar eliminación definitiva de cuenta
    if (formEliminarCuenta) {
        formEliminarCuenta.addEventListener('submit', async (e) => {
            e.preventDefault();
            limpiarAlertaCuenta();
            const textoOriginal = btnConfirmarEliminar ? btnConfirmarEliminar.textContent : '';
            if (btnConfirmarEliminar) {
                btnConfirmarEliminar.disabled = true;
                btnConfirmarEliminar.textContent = 'Eliminando...';
            }

            try {
                const res = await fetch('index.php?action=api_eliminar_cuenta', {
                    method: 'POST',
                    body: new FormData(formEliminarCuenta),
                });
                const data = await res.json();
                mostrarAlertaCuenta(!!data.success, data.message || 'Error inesperado.');

                if (data.success && data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else if (btnConfirmarEliminar) {
                    btnConfirmarEliminar.disabled = false;
                    btnConfirmarEliminar.textContent = textoOriginal;
                }
            } catch (err) {
                console.error(err);
                mostrarAlertaCuenta(false, 'Error de conexión con el servidor.');
                if (btnConfirmarEliminar) {
                    btnConfirmarEliminar.disabled = false;
                    btnConfirmarEliminar.textContent = textoOriginal;
                }
            }
        });
    }
});
