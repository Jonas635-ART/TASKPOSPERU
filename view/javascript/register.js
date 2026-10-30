// view/javascript/register.js
document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('registerForm');
    const mensajeAlerta = document.getElementById('mensaje-alerta');
    const btnSubmit = document.getElementById('btnSubmit');

    if (!registerForm) return;

    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        mensajeAlerta.style.display = 'none';
        mensajeAlerta.textContent = '';
        
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('confirm_password').value;

        if (password.length < 6) {
            mostrarAlerta('La contraseña debe tener al menos 6 caracteres.', false);
            return;
        }

        if (password !== confirm) {
            mostrarAlerta('Las contraseñas no coinciden.', false);
            return;
        }

        const originalBtnHtml = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = 'Registrando...';

        try {
            const formData = new FormData(registerForm);
            
            const response = await fetch('index.php?action=api_register', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                mostrarAlerta(data.message, true);
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            } else {
                mostrarAlerta(data.message, false);
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalBtnHtml;
            }
        } catch (error) {
            console.error('Error de Fetch:', error);
            mostrarAlerta('Ocurrió un error al comunicarse con el servidor.', false);
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalBtnHtml;
        }
    });

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
