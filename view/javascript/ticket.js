document.addEventListener('DOMContentLoaded', () => {

    // 1. Añadir Nueva Subtarea
    const inputSubtarea = document.getElementById('inputSubtarea');
    const selResponsableSubtareaNueva = document.getElementById('selResponsableSubtareaNueva');
    const btnAgregarSubtarea = document.getElementById('btnAgregarSubtarea');

    async function crearSubtarea() {
        if (!inputSubtarea || inputSubtarea.value.trim() === '') return;

        const idTicket = inputSubtarea.getAttribute('data-ticket');
        const desc = inputSubtarea.value.trim();
        const idResp = selResponsableSubtareaNueva ? selResponsableSubtareaNueva.value : '';

        const formData = new FormData();
        formData.append('id_requerimiento', idTicket);
        formData.append('descripcion', desc);
        if (idResp) {
            formData.append('id_responsable', idResp);
        }

        if (btnAgregarSubtarea) {
            btnAgregarSubtarea.disabled = true;
            btnAgregarSubtarea.textContent = '...';
        }

        try {
            const res = await fetch('index.php?action=api_agregar_subtarea', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.success) {
                window.location.reload();
            } else {
                alert('No se pudo agregar la subtarea.');
                if (btnAgregarSubtarea) {
                    btnAgregarSubtarea.disabled = false;
                    btnAgregarSubtarea.textContent = 'Añadir';
                }
            }
        } catch (error) {
            console.error('Error al agregar subtarea:', error);
            if (btnAgregarSubtarea) {
                btnAgregarSubtarea.disabled = false;
                btnAgregarSubtarea.textContent = 'Añadir';
            }
        }
    }

    if (inputSubtarea) {
        inputSubtarea.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && inputSubtarea.value.trim() !== '') {
                e.preventDefault();
                crearSubtarea();
            }
        });
    }

    if (btnAgregarSubtarea) {
        btnAgregarSubtarea.addEventListener('click', (e) => {
            e.preventDefault();
            crearSubtarea();
        });
    }

    // 2. Marcar/Desmarcar Subtarea y Asignar Responsable de Subtarea
    document.body.addEventListener('change', async (e) => {
        // 2a. Checkbox de estado de subtarea
        if (e.target.classList.contains('subtarea-check')) {
            const idSubtarea = e.target.getAttribute('data-id');
            const nuevoEstado = e.target.checked ? 'terminado' : 'pendiente';

            if (!idSubtarea) {
                alert('Refresca la página primero para sincronizar la tarea con la base de datos.');
                e.target.checked = !e.target.checked;
                return;
            }

            const formData = new FormData();
            formData.append('id_subtarea', idSubtarea);
            formData.append('estado', nuevoEstado);

            try {
                const res = await fetch('index.php?action=api_marcar_subtarea', { method: 'POST', formData, body: formData });
                const data = await res.json();

                if (data.success) {
                    const label = e.target.nextElementSibling.querySelector('label');
                    if (nuevoEstado === 'terminado') {
                        label.classList.add('line-through', 'text-slate-400');
                    } else {
                        label.classList.remove('line-through', 'text-slate-400');
                    }
                } else {
                    alert('Hubo un error al guardar en la base de datos.');
                    e.target.checked = !e.target.checked;
                }
            } catch (error) {
                console.error("Error de conexión: ", error);
                e.target.checked = !e.target.checked;
            }
        }

        // 2b. Selector de responsable de subtarea
        if (e.target.classList.contains('subtarea-responsable')) {
            const selectEl = e.target;
            const idSubtarea = selectEl.getAttribute('data-subtarea');
            const idUsuario = selectEl.value;

            const formData = new FormData();
            formData.append('id_subtarea', idSubtarea);
            formData.append('id_usuario', idUsuario);

            selectEl.disabled = true;
            try {
                const res = await fetch('index.php?action=api_asignar_subtarea', { method: 'POST', body: formData });
                const data = await res.json();
                selectEl.disabled = false;

                if (data.success) {
                    selectEl.classList.add('border-emerald-500', 'bg-emerald-50');
                    setTimeout(() => {
                        selectEl.classList.remove('border-emerald-500', 'bg-emerald-50');
                    }, 900);
                } else {
                    alert('Error al asignar responsable a la subtarea.');
                }
            } catch (error) {
                console.error('Error al asignar responsable de subtarea:', error);
                selectEl.disabled = false;
                alert('Error de conexión al asignar responsable.');
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

    // 5. Asignar Responsable General del Ticket
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
