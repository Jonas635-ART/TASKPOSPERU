document.addEventListener('DOMContentLoaded', () => {

    /* --- FORMULARIO DE REQUERIMIENTOS (AJAX) --- */
    const formRequerimiento = document.getElementById('formRequerimiento');
    const alertaReq = document.getElementById('alertaReq');
    const btnReq = document.getElementById('btnReq');

    // Se evalúa con un condicional para no interrumpir el resto de scripts de la página
    if (formRequerimiento) {
        formRequerimiento.addEventListener('submit', async (e) => {
            e.preventDefault(); // Evita que la página haga un postback tradicional

            // Limpiar alertas anteriores
            if (alertaReq) {
                alertaReq.className = 'hidden px-4 py-3 rounded-lg text-sm font-semibold mb-5 flex items-center gap-2';
                alertaReq.innerHTML = '';
            }
            
            // Bloquear el botón y mostrar estado de carga
            let originalBtnHtml = '';
            if (btnReq) {
                originalBtnHtml = btnReq.innerHTML;
                btnReq.disabled = true;
                btnReq.classList.add('opacity-75', 'cursor-not-allowed');
                btnReq.innerHTML = `
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Enviando...</span>
                `;
            }

            const formData = new FormData(formRequerimiento);

            try {
                const response = await fetch('index.php?action=api_nuevo_requerimiento', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                // Configurar diseño de alerta según la respuesta
                if (alertaReq) alertaReq.classList.remove('hidden');
                
                if (data.success) {
                    // Mensaje de Éxito (Verde)
                    if (alertaReq) {
                        alertaReq.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
                        alertaReq.innerHTML = `
                            <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            ${data.message}
                        `;
                    }
                    
                    formRequerimiento.reset(); // Limpiar el formulario

                    // Recargar página tras un breve retraso para actualizar la tabla
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);

                } else {
                    // Mensaje de Error (Rojo)
                    if (alertaReq) {
                        alertaReq.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
                        alertaReq.innerHTML = `
                            <svg class="w-5 h-5 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                            ${data.message}
                        `;
                    }
                    
                    // Restaurar botón para volver a intentar
                    if (btnReq) {
                        btnReq.disabled = false;
                        btnReq.classList.remove('opacity-75', 'cursor-not-allowed');
                        btnReq.innerHTML = originalBtnHtml;
                    }
                }
            } catch (error) {
                console.error('Error al enviar el requerimiento:', error);
                if (alertaReq) {
                    alertaReq.classList.remove('hidden');
                    alertaReq.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
                    alertaReq.innerHTML = `
                        <svg class="w-5 h-5 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        Ocurrió un error al intentar conectarse al servidor.
                    `;
                }
                
                if (btnReq) {
                    btnReq.disabled = false;
                    btnReq.classList.remove('opacity-75', 'cursor-not-allowed');
                    btnReq.innerHTML = originalBtnHtml;
                }
            }
        });
    }


    /* --- MENU NOTIFICACIONES (CAMPANA) --- */
    const btnNotif = document.getElementById('btnNotificaciones');
    const menuNotif = document.getElementById('menuNotificaciones');

    if (btnNotif && menuNotif) {
        // Abrir/Cerrar al dar clic en la campana
        btnNotif.addEventListener('click', (e) => {
            e.stopPropagation(); // Evita que el clic se propague al document
            menuNotif.classList.toggle('hidden');
        });

        // Cerrar al hacer clic en cualquier parte fuera de la ventana de notificaciones
        document.addEventListener('click', (e) => {
            if (!menuNotif.contains(e.target) && !btnNotif.contains(e.target)) {
                menuNotif.classList.add('hidden');
            }
        });
    }

    /* --- MODAL MI CUENTA (PERFIL, CORREO, CONTRASEÑA, ELIMINAR CUENTA) --- */
    const btnsAbrirCuenta = document.querySelectorAll('[data-open-cuenta]');
    const modalMiCuenta = document.getElementById('modalMiCuenta');
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
        alertaCuenta.className = 'px-4 py-3 rounded-lg text-sm font-semibold flex items-center gap-2 ' + (
            exito
                ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                : 'bg-rose-50 text-rose-700 border border-rose-200'
        );
        alertaCuenta.textContent = mensaje;
    };

    const limpiarAlertaCuenta = () => {
        if (!alertaCuenta) return;
        alertaCuenta.className = 'hidden px-4 py-3 rounded-lg text-sm font-semibold flex items-center gap-2';
        alertaCuenta.textContent = '';
    };

    const cerrarFormulariosCuenta = () => {
        if (formEditarCorreo) formEditarCorreo.classList.add('hidden');
        if (formEditarPassword) formEditarPassword.classList.add('hidden');
        if (formEliminarCuenta) formEliminarCuenta.classList.add('hidden');
    };

    const abrirModalCuenta = () => {
        if (!modalMiCuenta) return;
        limpiarAlertaCuenta();
        cerrarFormulariosCuenta();
        modalMiCuenta.classList.remove('hidden');
    };

    const cerrarModalCuenta = () => {
        if (!modalMiCuenta) return;
        modalMiCuenta.classList.add('hidden');
        limpiarAlertaCuenta();
        cerrarFormulariosCuenta();
    };

    if (btnsAbrirCuenta.length > 0 && modalMiCuenta) {
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
    }

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
