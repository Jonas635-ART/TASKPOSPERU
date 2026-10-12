// view/javascript/register.js
document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('registerForm');
    const mensajeAlerta = document.getElementById('mensaje-alerta');
    const btnSubmit = document.getElementById('btnSubmit');

    if (!registerForm) return;

    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Limpiar y ocultar alertas previas
        mensajeAlerta.style.display = 'none';
        mensajeAlerta.textContent = '';
        
        // Obtener contraseñas para validación local rápida
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('confirm_password').value;

        if (password !== confirm) {
            mostrarAlerta('Las contraseñas no coinciden.', false);
            return;
        }

        // Bloquear botón para evitar múltiples envíos
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
                // Registro exitoso: Alerta verde y redirección
                mostrarAlerta(data.message, true);
                
                // Esperar 2 segundos para que el usuario lea el mensaje antes de redirigir
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            } else {
                // Error del servidor (ej. correo duplicado, código inválido)
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

    // Función auxiliar para pintar la alerta de verde (éxito) o rojo (error)
    function mostrarAlerta(mensaje, esExito) {
        mensajeAlerta.textContent = mensaje;
        mensajeAlerta.style.display = 'block';
        
        if (esExito) {
            mensajeAlerta.style.background = '#f0fdf4'; // bg-green-50
            mensajeAlerta.style.color = '#15803d';      // text-green-700
            mensajeAlerta.style.borderColor = '#86efac';// border-green-300
        } else {
            mensajeAlerta.style.background = '#fef2f2'; // bg-red-50
            mensajeAlerta.style.color = '#b91c1c';      // text-red-700
            mensajeAlerta.style.borderColor = '#f87171';// border-red-400
        }
    }
});