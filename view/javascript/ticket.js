document.addEventListener('DOMContentLoaded', () => {

    // 1. Añadir Nueva Subtarea
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
                    window.location.reload(); 
                }
            }
        });
    }

    // 2. Marcar/Desmarcar Subtarea (Guardar Estado)
    document.body.addEventListener('change', async (e) => {
        // Detectar si el elemento que cambió es un checkbox de subtarea
        if (e.target.classList.contains('subtarea-check')) {
            const idSubtarea = e.target.getAttribute('data-id');
            const nuevoEstado = e.target.checked ? 'terminado' : 'pendiente';

            // Si es una tarea recién agregada sin ID (por precaución)
            if (!idSubtarea) {
                alert('Refresca la página primero para sincronizar la tarea con la base de datos.');
                e.target.checked = !e.target.checked;
                return;
            }

            const formData = new FormData();
            formData.append('id_subtarea', idSubtarea);
            formData.append('estado', nuevoEstado);

            try {
                const res = await fetch('index.php?action=api_marcar_subtarea', { method: 'POST', body: formData });
                const data = await res.json();
                
                if (data.success) {
                    // Actualizar estilos visuales (tachar el texto)
                    const label = e.target.nextElementSibling.querySelector('label');
                    if (nuevoEstado === 'terminado') {
                        label.classList.add('line-through', 'text-slate-400');
                    } else {
                        label.classList.remove('line-through', 'text-slate-400');
                    }
                } else {
                    alert('Hubo un error al guardar en la base de datos.');
                    e.target.checked = !e.target.checked; // Revertir el check visual
                }
            } catch (error) {
                console.error("Error de conexión: ", error);
                e.target.checked = !e.target.checked;
            }
        }
    });

    // 3. Añadir Comentario
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

    // 4. Actualizar Estado General del Ticket
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

    // 5. Asignar Responsable
    const selResponsable = document.getElementById('selResponsable');
    if (selResponsable) {
        selResponsable.addEventListener('change', async (e) => {
            const idUsuario = e.target.value;
            if (!idUsuario) return;

            const idTicket = selResponsable.getAttribute('data-ticket');
            
            const formData = new FormData();
            formData.append('id_requerimiento', idTicket);
            formData.append('id_usuario', idUsuario);

            const res = await fetch('index.php?action=api_asignar_responsable', { method: 'POST', body: formData });
            const data = await res.json();
            
            if (data.success) {
                window.location.reload(); 
            } else {
                alert('Error al asignar responsable');
            }
        });
    }
});