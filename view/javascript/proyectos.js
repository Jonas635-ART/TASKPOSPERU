document.addEventListener('DOMContentLoaded', () => {
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