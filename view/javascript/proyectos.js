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


// ==========================================================
// Gestión de tickets dentro de un proyecto (admin y developer)
// ==========================================================
let proyectoTicketsActual = null;

const ESTILO_ESTADO_TICKET = {
    'pendiente':  'bg-slate-100 text-slate-600',
    'en proceso': 'bg-blue-50 text-blue-700',
    'terminado':  'bg-emerald-50 text-emerald-700'
};
const ESTILO_PRIORIDAD_TICKET = {
    'critica': 'bg-rose-100 text-rose-700',
    'alta':    'bg-orange-100 text-orange-700',
    'media':   'bg-amber-100 text-amber-700',
    'baja':    'bg-slate-100 text-slate-600'
};

function crearEtiqueta(texto, clases) {
    const span = document.createElement('span');
    span.className = `text-[9px] font-bold uppercase px-1.5 py-0.5 rounded ${clases}`;
    span.textContent = texto;
    return span;
}

function mostrarMensajeTickets(texto, esError) {
    const msg = document.getElementById('mensajeTickets');
    if (!msg) return;
    msg.textContent = texto;
    msg.className = `mb-3 text-xs font-semibold px-3 py-2 rounded-lg ${esError ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700'}`;
    clearTimeout(mostrarMensajeTickets._t);
    mostrarMensajeTickets._t = setTimeout(() => msg.classList.add('hidden'), 3000);
}

async function abrirModalTickets(idProyecto) {
    proyectoTicketsActual = idProyecto;
    const modal = document.getElementById('modalTickets');
    document.getElementById('modalTicketsTitulo').textContent = 'Cargando...';
    document.getElementById('listaTicketsProyecto').innerHTML = '';
    document.getElementById('mensajeTickets').classList.add('hidden');
    modal.classList.remove('hidden');
    await cargarTicketsProyecto();
}

function cerrarModalTickets() {
    document.getElementById('modalTickets').classList.add('hidden');
    proyectoTicketsActual = null;
}

async function cargarTicketsProyecto() {
    if (!proyectoTicketsActual) return;
    try {
        const res = await fetch(`index.php?action=api_proyecto_tickets&id_proyecto=${encodeURIComponent(proyectoTicketsActual)}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'No se pudo cargar el proyecto.');

        document.getElementById('modalTicketsTitulo').textContent = data.proyecto.nombre;
        renderTicketsProyecto(data.asignados);
        renderTicketsDisponibles(data.disponibles);
        actualizarContadorProyecto(data.asignados.length);
    } catch (err) {
        document.getElementById('modalTicketsTitulo').textContent = 'Error';
        mostrarMensajeTickets(err.message || 'Error de conexión.', true);
    }
}

function renderTicketsProyecto(tickets) {
    const lista = document.getElementById('listaTicketsProyecto');
    const vacio = document.getElementById('sinTicketsProyecto');
    lista.replaceChildren();
    vacio.classList.toggle('hidden', tickets.length > 0);

    tickets.forEach(t => {
        const li = document.createElement('li');
        li.className = 'flex items-center gap-3 p-3 border border-slate-200 rounded-xl hover:border-slate-300 transition';

        const info = document.createElement('div');
        info.className = 'flex-1 min-w-0';

        const fila = document.createElement('div');
        fila.className = 'flex items-center gap-1.5 flex-wrap mb-0.5';
        const id = document.createElement('a');
        id.href = `index.php?action=ver_ticket&id=${encodeURIComponent(t.id_requerimiento)}`;
        id.className = 'font-mono text-[11px] font-bold text-indigo-600 hover:underline';
        id.textContent = `#TK-${t.id_requerimiento}`;
        fila.append(
            id,
            crearEtiqueta(t.prioridad, ESTILO_PRIORIDAD_TICKET[t.prioridad] || ESTILO_PRIORIDAD_TICKET.baja),
            crearEtiqueta(t.estado, ESTILO_ESTADO_TICKET[t.estado] || ESTILO_ESTADO_TICKET.pendiente)
        );

        const titulo = document.createElement('p');
        titulo.className = 'text-sm font-semibold text-slate-700 truncate';
        titulo.textContent = t.titulo;

        const resp = document.createElement('p');
        resp.className = 'text-[11px] text-slate-400 truncate';
        resp.textContent = t.responsable ? `Responsable: ${t.responsable}` : 'Sin responsable';

        info.append(fila, titulo, resp);

        const btnQuitar = document.createElement('button');
        btnQuitar.type = 'button';
        btnQuitar.className = 'shrink-0 px-2.5 py-1.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition';
        btnQuitar.textContent = 'Quitar';
        btnQuitar.title = 'Quitar del proyecto (el ticket no se elimina)';
        btnQuitar.addEventListener('click', () => quitarTicketDeProyecto(t.id_requerimiento, t.titulo, btnQuitar));

        li.append(info, btnQuitar);
        lista.appendChild(li);
    });
}

function renderTicketsDisponibles(tickets) {
    const select = document.getElementById('selectTicketDisponible');
    const btn = document.getElementById('btnAgregarTicket');
    select.replaceChildren();

    if (tickets.length === 0) {
        const opt = new Option('No hay tickets disponibles para agregar', '');
        select.appendChild(opt);
        select.disabled = true;
        btn.disabled = true;
        return;
    }

    select.disabled = false;
    btn.disabled = false;
    select.appendChild(new Option('-- Selecciona un ticket --', ''));

    const sinProyecto = document.createElement('optgroup');
    sinProyecto.label = 'Sin proyecto';
    const enOtro = document.createElement('optgroup');
    enOtro.label = 'En otro proyecto (se moverá)';

    tickets.forEach(t => {
        const texto = `#TK-${t.id_requerimiento} · ${t.titulo}` + (t.proyecto_actual ? ` (${t.proyecto_actual})` : '');
        (t.proyecto_actual ? enOtro : sinProyecto).appendChild(new Option(texto, t.id_requerimiento));
    });

    if (sinProyecto.children.length) select.appendChild(sinProyecto);
    if (enOtro.children.length) select.appendChild(enOtro);
}

function actualizarContadorProyecto(total) {
    const contador = document.querySelector(`.contador-tickets[data-proyecto="${proyectoTicketsActual}"]`);
    if (contador) contador.textContent = total;
}

async function enviarAccionTicket(accion, idRequerimiento) {
    const formData = new FormData();
    formData.append('id_proyecto', proyectoTicketsActual);
    formData.append('id_requerimiento', idRequerimiento);
    const res = await fetch(`index.php?action=${accion}`, { method: 'POST', body: formData });
    const data = await res.json();
    if (!data.success) throw new Error(data.message || 'No se pudo completar la acción.');
}

async function quitarTicketDeProyecto(idRequerimiento, titulo, boton) {
    if (!confirm(`¿Quitar #TK-${idRequerimiento} "${titulo}" de este proyecto?\nEl ticket no se elimina, solo queda sin proyecto.`)) return;
    boton.disabled = true;
    try {
        await enviarAccionTicket('api_proyecto_quitar_ticket', idRequerimiento);
        mostrarMensajeTickets(`#TK-${idRequerimiento} quitado del proyecto.`, false);
        await cargarTicketsProyecto();
    } catch (err) {
        boton.disabled = false;
        mostrarMensajeTickets(err.message, true);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formAgregarTicket');
    const modal = document.getElementById('modalTickets');

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const select = document.getElementById('selectTicketDisponible');
            const btn = document.getElementById('btnAgregarTicket');
            if (!select.value) {
                mostrarMensajeTickets('Selecciona un ticket para agregar.', true);
                return;
            }
            btn.disabled = true;
            try {
                await enviarAccionTicket('api_proyecto_agregar_ticket', select.value);
                mostrarMensajeTickets(`#TK-${select.value} agregado al proyecto.`, false);
                await cargarTicketsProyecto();
            } catch (err) {
                mostrarMensajeTickets(err.message, true);
            } finally {
                btn.disabled = select.disabled;
            }
        });
    }

    if (modal) {
        // Cerrar al hacer clic fuera del contenido o con Escape
        modal.addEventListener('click', (e) => { if (e.target === modal) cerrarModalTickets(); });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) cerrarModalTickets();
        });
    }
});
