document.addEventListener('DOMContentLoaded', () => {

    // 1. Agregar Subtarea
    const inputSubtarea = document.getElementById('inputSubtarea');
    if (inputSubtarea) {
        inputSubtarea.addEventListener('keypress', async (e) => {
            if (e.key === 'Enter' && inputSubtarea.value.trim() !== '') {
                e.preventDefault();
                const idTicket = inputSubtarea.getAttribute('data-ticket');
                const desc = inputSubtarea.value.trim();
                
                const formData = new FormData();
                formData.append('id_requerimiento', idTicket);
                formData.append('descripcion', desc);

                const res = await fetch('index.php?action=api_agregar_subtarea', { method: 'POST', body: formData });
                const data = await res.json();
                
                if (data.success) {
                    window.location.reload(); // Recarga simple para reflejar cambios
                }
            }
        });
    }

    // 2. Agregar Comentario
    const btnComentar = document.getElementById('btnComentar');
    const txtComentario = document.getElementById('txtComentario');
    if (btnComentar && txtComentario) {
        btnComentar.addEventListener('click', async (e) => {
            e.preventDefault();
            const msg = txtComentario.value.trim();
            if (msg === '') return;

            const idTicket = btnComentar.getAttribute('data-ticket');
            
            const formData = new FormData();
            formData.append('id_requerimiento', idTicket);
            formData.append('mensaje', msg);

            btnComentar.disabled = true;
            btnComentar.textContent = 'Enviando...';

            const res = await fetch('index.php?action=api_agregar_comentario', { method: 'POST', body: formData });
            const data = await res.json();
            
            if (data.success) {
                window.location.reload();
            } else {
                btnComentar.disabled = false;
                btnComentar.textContent = 'Publicar';
            }
        });
    }

    // 3. Actualizar Estado y Prioridad
    const btnActualizarEstado = document.getElementById('btnActualizarEstado');
    if (btnActualizarEstado) {
        btnActualizarEstado.addEventListener('click', async (e) => {
            e.preventDefault();
            const idTicket = btnActualizarEstado.getAttribute('data-ticket');
            const estado = document.getElementById('selEstado').value;
            const prioridad = document.getElementById('selPrioridad').value;

            const formData = new FormData();
            formData.append('id_requerimiento', idTicket);
            formData.append('estado', estado);
            formData.append('prioridad', prioridad);

            btnActualizarEstado.textContent = 'Guardando...';

            const res = await fetch('index.php?action=api_actualizar_estado', { method: 'POST', body: formData });
            const data = await res.json();
            
            if (data.success) {
                window.location.reload();
            } else {
                btnActualizarEstado.textContent = 'Guardar Cambios';
                alert('Error al guardar cambios');
            }
        });
    }
});