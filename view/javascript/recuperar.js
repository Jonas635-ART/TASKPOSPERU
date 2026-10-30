// view/javascript/recuperar.js
document.addEventListener('DOMContentLoaded', () => {
    const tabSolicitar = document.getElementById('tabSolicitar');
    const tabCanjear   = document.getElementById('tabCanjear');
    const formSolicitar = document.getElementById('formSolicitar');
    const formCanjear   = document.getElementById('formCanjear');
    const mensajeAlerta = document.getElementById('mensaje-alerta');

    function cambiarTab(modo) {
        mensajeAlerta.style.display = 'none';
        const esSolicitar = (modo === 'solicitar');

        tabSolicitar.classList.toggle('active', esSolicitar);
        tabCanjear.classList.toggle('active', !esSolicitar);
        tabSolicitar.setAttribute('aria-selected', esSolicitar ? 'true' : 'false');
        tabCanjear.setAttribute('aria-selected', !esSolicitar ? 'true' : 'false');

        formSolicitar.style.display = esSolicitar ? 'block' : 'none';
        formCanjear.style.display   = esSolicitar ? 'none' : 'block';
    }

    if (tabSolicitar) tabSolicitar.addEventListener('click', () => cambiarTab('solicitar'));
    if (tabCanjear)   tabCanjear.addEventListener('click', () => cambiarTab('canjear'));

    // 1. Enviar solicitud al Admin
    if (formSolicitar) {
        formSolicitar.addEventListener('submit', async (e) => {
            e.preventDefault();
            mensajeAlerta.style.display = 'none';

            const btn = document.getElementById('btnSolicitar');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Enviando...';

            try {
                const formData = new FormData(formSolicitar);
                const res = await fetch('index.php?action=api_solicitar_reset', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    const correo = document.getElementById('correo_solicitud').value;
                    const inputCanje = document.getElementById('correo_canje');
                    if (inputCanje && correo) inputCanje.value = correo;
                    cambiarTab('canjear');
                    mostrarAlerta(data.message, true);
                } else {
                    mostrarAlerta(data.message || 'No se pudo registrar la solicitud.', false);
                }
            } catch (err) {
                mostrarAlerta('Error de conexión con el servidor.', false);
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    }

    // 2. Canjear código temporal y guardar nueva contraseña
    if (formCanjear) {
        formCanjear.addEventListener('submit', async (e) => {
            e.preventDefault();
            mensajeAlerta.style.display = 'none';

            const password = document.getElementById('password').value;
            const confirm  = document.getElementById('confirm_password').value;

            if (password.length < 6) {
                mostrarAlerta('La nueva contraseña debe tener al menos 6 caracteres.', false);
                return;
            }
            if (password !== confirm) {
                mostrarAlerta('Las contraseñas no coinciden.', false);
                return;
            }

            const btn = document.getElementById('btnCanjear');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Verificando código...';

            try {
                const formData = new FormData(formCanjear);
                const res = await fetch('index.php?action=api_recuperar', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    mostrarAlerta(data.message, true);
                    setTimeout(() => {
                        window.location.href = data.redirect || 'index.php?action=login';
                    }, 1800);
                } else {
                    mostrarAlerta(data.message || 'Código inválido.', false);
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            } catch (err) {
                mostrarAlerta('Error de conexión con el servidor.', false);
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    }

    function mostrarAlerta(mensaje, esExito) {
        mensajeAlerta.textContent = mensaje;
        mensajeAlerta.style.display = 'block';
        if (esExito) {
            mensajeAlerta.style.background = '#f0fdf4';
            mensajeAlerta.style.color = '#15803d';
            mensajeAlerta.style.borderColor = '#86efac';
        } else {
            mensajeAlerta.style.background = '#fef2f2';
            mensajeAlerta.style.color = '#b91c1c';
            mensajeAlerta.style.borderColor = '#f87171';
        }
    }
});

