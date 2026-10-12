document.addEventListener('DOMContentLoaded', () => {
    const formRequerimiento = document.getElementById('formRequerimiento');
    const alertaReq = document.getElementById('alertaReq');
    const btnReq = document.getElementById('btnReq');

    if (!formRequerimiento) return;

    formRequerimiento.addEventListener('submit', async (e) => {
        e.preventDefault(); // Evita que la página haga un postback tradicional

        // Limpiar alertas anteriores
        alertaReq.className = 'hidden px-4 py-3 rounded-lg text-sm font-semibold mb-5 flex items-center gap-2';
        alertaReq.innerHTML = '';
        
        // Bloquear el botón y mostrar estado de carga
        const originalBtnHtml = btnReq.innerHTML;
        btnReq.disabled = true;
        btnReq.classList.add('opacity-75', 'cursor-not-allowed');
        btnReq.innerHTML = `
            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Enviando...</span>
        `;

        const formData = new FormData(formRequerimiento);

        try {
            const response = await fetch('index.php?action=api_nuevo_requerimiento', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            // Configurar diseño de alerta según la respuesta
            alertaReq.classList.remove('hidden');
            
            if (data.success) {
                // Mensaje de Éxito (Verde)
                alertaReq.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
                alertaReq.innerHTML = `
                    <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    ${data.message}
                `;
                
                formRequerimiento.reset(); // Limpiar el formulario

                // Recargar página tras un breve retraso para actualizar la tabla
                setTimeout(() => {
                    window.location.reload();
                }, 1500);

            } else {
                // Mensaje de Error (Rojo)
                alertaReq.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
                alertaReq.innerHTML = `
                    <svg class="w-5 h-5 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                    ${data.message}
                `;
                
                // Restaurar botón para volver a intentar
                btnReq.disabled = false;
                btnReq.classList.remove('opacity-75', 'cursor-not-allowed');
                btnReq.innerHTML = originalBtnHtml;
            }
        } catch (error) {
            console.error('Error al enviar el requerimiento:', error);
            alertaReq.classList.remove('hidden');
            alertaReq.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
            alertaReq.innerHTML = `
                <svg class="w-5 h-5 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                Ocurrió un error al intentar conectarse al servidor.
            `;
            
            btnReq.disabled = false;
            btnReq.classList.remove('opacity-75', 'cursor-not-allowed');
            btnReq.innerHTML = originalBtnHtml;
        }
    });
});