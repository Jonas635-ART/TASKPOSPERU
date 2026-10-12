document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const mensajeError = document.getElementById('mensaje-error');
    const btnSubmit = document.getElementById('btnSubmit');

    if (!loginForm) return;

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault(); // Evita que la página se recargue

        // Ocultar errores previos
        mensajeError.style.display = 'none';
        mensajeError.textContent = '';
        
        // Bloquear botón y mostrar estado de carga
        const originalBtnHtml = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = 'Verificando...';

        const formData = new FormData(loginForm);

        try {
            const response = await fetch('index.php?action=api_login', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Redireccionar si las credenciales son correctas
                window.location.href = data.redirect;
            } else {
                // Mostrar error si las credenciales fallan
                mensajeError.textContent = data.message;
                mensajeError.style.display = 'block';
                
                // Restaurar botón
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalBtnHtml;
            }
        } catch (error) {
            console.error('Error en la petición AJAX:', error);
            mensajeError.textContent = 'Error de conexión con el servidor.';
            mensajeError.style.display = 'block';
            
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalBtnHtml;
        }
    });
});