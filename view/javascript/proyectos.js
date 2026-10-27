document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const btnAbrir = document.getElementById('btnAbrirMenu');
    const btnCerrar = document.getElementById('btnCerrarMenu');

    function toggleMenu() {
        if (!sidebar) return;
        sidebar.classList.toggle('-translate-x-full');
        if (overlay.classList.contains('hidden')) {
            overlay.classList.remove('hidden', 'pointer-events-none');
            setTimeout(() => overlay.classList.remove('opacity-0'), 10);
        } else {
            overlay.classList.add('opacity-0', 'pointer-events-none');
            setTimeout(() => overlay.classList.add('hidden'), 300);
        }
    }

    if (btnAbrir) btnAbrir.addEventListener('click', toggleMenu);
    if (btnCerrar) btnCerrar.addEventListener('click', toggleMenu);
    if (overlay) overlay.addEventListener('click', toggleMenu); // Corregido: se cambió } por );
    
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

    const inputBuscar = document.querySelector('input[placeholder="Buscar un proyecto"]');
    const filasProyectos = document.querySelectorAll('tbody tr');

    if (inputBuscar) {
        inputBuscar.addEventListener('keyup', (e) => {
            const busqueda = e.target.value.toLowerCase().trim();

            filasProyectos.forEach(fila => {
                // Obtener el nombre del proyecto en la primera celda
                const nombreProyecto = fila.querySelector('td:first-child p.font-bold');
                
                if (nombreProyecto) {
                    const texto = nombreProyecto.textContent.toLowerCase();
                    if (texto.includes(busqueda)) {
                        fila.style.display = '';
                    } else {
                        fila.style.display = 'none';
                    }
                }
            });
        });
    }
});

// Funciones del Modal de Proyectos
function abrirModalProyecto() {
    const modal = document.getElementById('modalProyecto');
    const content = document.getElementById('modalContent');
    
    // Limpiar formulario para nuevo proyecto
    document.getElementById('id_proyecto').value = '';
    document.getElementById('nombre_proyecto').value = '';
    document.getElementById('descripcion_proyecto').value = '';
    document.getElementById('modalTitle').textContent = 'Nuevo Proyecto';
    
    // Desmarcar todos los checkboxes de miembros
    document.querySelectorAll('.miembro-checkbox').forEach(chk => chk.checked = false);

    modal.classList.remove('hidden');
    // Pequeño delay para la animación de Tailwind
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function editarProyecto(id, nombre, descripcion, miembrosIds) {
    abrirModalProyecto();
    document.getElementById('modalTitle').textContent = 'Editar Proyecto';
    document.getElementById('id_proyecto').value = id;
    document.getElementById('nombre_proyecto').value = nombre;
    document.getElementById('descripcion_proyecto').value = descripcion;
    
    // Marcar los checkboxes de los usuarios asignados
    if (miembrosIds) {
        const ids = miembrosIds.toString().split(',');
        document.querySelectorAll('.miembro-checkbox').forEach(chk => {
            chk.checked = ids.includes(chk.value);
        });
    }
}

function cerrarModalProyecto() {
    const modal = document.getElementById('modalProyecto');
    const content = document.getElementById('modalContent');
    
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300); // Esperar que termine la transición
}