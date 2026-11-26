document.addEventListener('DOMContentLoaded', () => {

    /* --- SIDEBAR / MENU OVERLAY --- */
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const btnAbrir = document.getElementById('btnAbrirMenu');
    const btnCerrar = document.getElementById('btnCerrarMenu');

    function toggleMenu() {
        if (!sidebar) return;
        sidebar.classList.toggle('-translate-x-full');
        if (overlay) {
            if (overlay.classList.contains('hidden')) {
                overlay.classList.remove('hidden', 'pointer-events-none');
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            } else {
                overlay.classList.add('opacity-0', 'pointer-events-none');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }
    }

    if (btnAbrir) btnAbrir.addEventListener('click', toggleMenu);
    if (btnCerrar) btnCerrar.addEventListener('click', toggleMenu);
    if (overlay) overlay.addEventListener('click', toggleMenu);

    /* --- NOTIFICACIONES (CAMPANA) --- */
    const btnNotif = document.getElementById('btnNotificaciones');
    const menuNotif = document.getElementById('menuNotificaciones');

    if (btnNotif && menuNotif) {
        btnNotif.addEventListener('click', (e) => {
            e.stopPropagation();
            menuNotif.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!menuNotif.contains(e.target) && !btnNotif.contains(e.target)) {
                menuNotif.classList.add('hidden');
            }
        });
    }

    /* --- FILTRADO DE TABLA Y ATAJOS DE TECLADO --- */
    const filterInput = document.getElementById('filterTable');
    const globalSearchInput = document.getElementById('searchInput');
    const btnLimpiarSearch = document.getElementById('btnLimpiarSearchInput');
    const table = document.getElementById('ticketsTable');
    const rowCountDisplay = document.getElementById('rowCount');
    const sinResultadosTabla = document.getElementById('sinResultadosTabla');
    const estadoSelect = filterInput
        ? filterInput.closest('.flex-wrap')?.querySelector('select')
        : null;

    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            if (globalSearchInput) globalSearchInput.focus();
        }
    });

    function aplicarFiltrosTabla(searchTerm) {
        const term = (searchTerm !== undefined
            ? searchTerm
            : (globalSearchInput?.value || filterInput?.value || '')
        ).toLowerCase().trim();

        if (btnLimpiarSearch) {
            btnLimpiarSearch.classList.toggle('hidden', term === '');
        }

        // Filtrar tarjetas de KPI de Proyectos si existen
        document.querySelectorAll('[data-proyecto-card]').forEach(card => {
            const nombreProy = (card.getAttribute('data-proyecto-nombre') || card.textContent).toLowerCase();
            card.style.display = (!term || nombreProy.includes(term)) ? '' : 'none';
        });

        if (!table) return;
        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        let estadoFiltro = '';
        if (estadoSelect) {
            const val = estadoSelect.value.toLowerCase().trim();
            if (!val.includes('todos')) {
                estadoFiltro = val;
            }
        }

        const rows = tbody.querySelectorAll('tr[data-ticket-id]');
        let visibleCount = 0;

        rows.forEach(row => {
            const textContent = row.textContent.toLowerCase();
            const rowEstado = (row.getAttribute('data-estado') || '').toLowerCase();
            const rowProyecto = (row.getAttribute('data-proyecto') || '').toLowerCase();

            const coincideTexto = !term || textContent.includes(term) || rowProyecto.includes(term);
            const coincideEstado = !estadoFiltro || rowEstado === estadoFiltro;

            if (coincideTexto && coincideEstado) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (rowCountDisplay) {
            rowCountDisplay.textContent = visibleCount;
        }
        if (sinResultadosTabla) {
            sinResultadosTabla.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
        }
    }

    if (filterInput) {
        filterInput.addEventListener('input', (e) => {
            if (globalSearchInput) globalSearchInput.value = e.target.value;
            aplicarFiltrosTabla(e.target.value);
        });
    }
    if (globalSearchInput) {
        globalSearchInput.addEventListener('input', (e) => {
            if (filterInput) filterInput.value = e.target.value;
            aplicarFiltrosTabla(e.target.value);
        });
        globalSearchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                globalSearchInput.value = '';
                if (filterInput) filterInput.value = '';
                aplicarFiltrosTabla('');
            }
        });
    }
    if (btnLimpiarSearch && globalSearchInput) {
        btnLimpiarSearch.addEventListener('click', () => {
            globalSearchInput.value = '';
            if (filterInput) filterInput.value = '';
            aplicarFiltrosTabla('');
            globalSearchInput.focus();
        });
    }
    if (estadoSelect) {
        estadoSelect.addEventListener('change', () => aplicarFiltrosTabla());
    }

    /* --- SINCRONIZACIÓN AUTOMÁTICA EN SEGUNDO PLANO (POLLING LIGERO) --- */
    let syncEnCurso = false;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function renderEstadoBadge(estado) {
        const est = (estado || 'pendiente').toLowerCase();
        const bgEstado = est === 'pendiente'
            ? 'bg-rose-50 text-rose-700 border-rose-100'
            : (est === 'en proceso'
                ? 'bg-amber-50 text-amber-700 border-amber-100'
                : 'bg-emerald-50 text-emerald-700 border-emerald-100');
        const dotEstado = est === 'pendiente'
            ? 'bg-rose-500'
            : (est === 'en proceso' ? 'bg-amber-500' : 'bg-emerald-500');
        const label = est.charAt(0).toUpperCase() + est.slice(1);
        return `
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full ${bgEstado} border text-[11px] font-semibold">
                <span class="w-1.5 h-1.5 rounded-full ${dotEstado}"></span> ${escapeHtml(label)}
            </span>
        `;
    }

    function renderResponsableHtml(responsable) {
        if (responsable) {
            const iniciales = escapeHtml(String(responsable).slice(0, 2));
            return `
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-cyan-50 text-cyan-700 flex items-center justify-center text-[9px] font-bold uppercase border border-cyan-100">
                        ${iniciales}
                    </div>
                    <div>
                        <p class="font-semibold text-slate-700 text-xs">${escapeHtml(responsable)}</p>
                        <p class="text-[10px] text-slate-400">DevOps / Dev</p>
                    </div>
                </div>
            `;
        }
        return '<span class="text-xs text-slate-400 italic">Sin asignar</span>';
    }

    function crearFilaTicket(t) {
        const id = Number(t.id_requerimiento);
        const estado = (t.estado || 'pendiente').toLowerCase();
        const proyecto = (t.proyecto || '').toLowerCase();
        const responsable = t.responsable || '';
        const fechaEntrega = t.fecha_entrega || '';
        let fechaEntregaHtml = '';
        if (fechaEntrega) {
            const hoy = new Date().toISOString().slice(0, 10);
            const vencido = estado !== 'terminado' && fechaEntrega < hoy;
            const venceHoy = estado !== 'terminado' && fechaEntrega === hoy;
            const clsFecha = vencido
                ? 'text-rose-600 bg-rose-50 border-rose-200'
                : (venceHoy ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-indigo-600 bg-indigo-50 border-indigo-100');
            const [y, m, d] = fechaEntrega.split('-');
            fechaEntregaHtml = `<span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded border text-[10px] font-semibold ${clsFecha}">Entrega: ${escapeHtml(`${d}/${m}/${y}`)}</span>`;
        }

        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50 transition-colors duration-700 group bg-indigo-50/70';
        tr.setAttribute('data-ticket-id', String(id));
        tr.setAttribute('data-estado', estado);
        tr.setAttribute('data-proyecto', proyecto);
        tr.setAttribute('data-responsable', responsable);

        tr.innerHTML = `
            <td class="p-4 pl-6 align-middle">
                <span class="font-mono text-xs font-bold text-indigo-600">#TK-${id}</span>
            </td>
            <td class="p-4 align-middle max-w-sm">
                <p class="font-bold text-slate-800 text-sm group-hover:text-indigo-600 transition-colors">${escapeHtml(t.titulo)}</p>
                <p class="text-[11px] text-slate-500 truncate mt-0.5" title="${escapeHtml(t.descripcion_tecnica)}">
                    ${escapeHtml(t.descripcion_tecnica)}
                </p>
                ${fechaEntregaHtml}
            </td>
            <td class="p-4 align-middle">
                <p class="font-semibold text-slate-700 text-xs">${escapeHtml(t.solicitante)}</p>
                <p class="text-[10px] text-slate-400 capitalize">${escapeHtml(t.rol_solicitante || 'cliente')}</p>
            </td>
            <td class="p-4 align-middle">
                ${renderResponsableHtml(t.responsable)}
            </td>
            <td class="p-4 align-middle">
                ${renderEstadoBadge(estado)}
            </td>
            <td class="p-4 align-middle text-center">
                <div class="flex justify-center gap-2">
                    <a href="index.php?action=ver_ticket&id=${id}"
                        class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition inline-flex items-center justify-center"
                        title="Ver Detalles">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </a>
                    <a href="index.php?action=eliminar_ticket&id=${id}"
                        onclick="confirmarEliminacion(event, this.href, '${id}')"
                        class="text-slate-400 hover:text-rose-600 transition p-1 cursor-pointer"
                        title="Eliminar definitivamente">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </a>
                </div>
            </td>
        `;

        setTimeout(() => tr.classList.remove('bg-indigo-50/70'), 3500);
        return tr;
    }

    function actualizarCampanaNotificaciones(notificaciones, esCliente) {
        if (!btnNotif || !menuNotif) return;

        // Punto rojo en la campana
        let dot = btnNotif.querySelector('span.bg-rose-500');
        if (notificaciones.length > 0 && !dot) {
            dot = document.createElement('span');
            dot.className = 'absolute -top-1 -right-1 w-2 h-2 bg-rose-500 rounded-full border border-white animate-pulse';
            btnNotif.appendChild(dot);
        } else if (notificaciones.length === 0 && dot) {
            dot.remove();
        }

        const headerBar = menuNotif.firstElementChild;
        const listContainer = menuNotif.querySelector('.max-h-72');
        if (headerBar) {
            let badge = headerBar.querySelector('span.bg-rose-100');
            if (notificaciones.length > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'bg-rose-100 text-rose-700 text-[9px] font-black px-2 py-0.5 rounded-full uppercase';
                    headerBar.appendChild(badge);
                }
                badge.textContent = `${notificaciones.length} Nuevas`;
            } else if (badge) {
                badge.remove();
            }
        }

        if (listContainer) {
            if (notificaciones.length === 0) {
                listContainer.innerHTML = '<div class="px-4 py-8 text-center text-sm text-slate-500 font-medium">No hay notificaciones recientes.</div>';
            } else {
                const colorIcono = esCliente ? 'text-emerald-500 bg-emerald-50' : 'text-indigo-500 bg-indigo-50';
                const svgPath = esCliente
                    ? 'M5 13l4 4L19 7'
                    : 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';

                listContainer.innerHTML = notificaciones.map(n => `
                    <a href="index.php?action=ver_ticket&id=${Number(n.id_requerimiento)}" class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 border-b border-slate-50 transition cursor-pointer group">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 ${colorIcono}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${svgPath}"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-800 mb-0.5 group-hover:text-indigo-600 transition">${escapeHtml(n.titulo_notif)}</p>
                            <p class="text-[11px] text-slate-500 truncate mb-1"><span class="font-semibold">TK-${Number(n.id_requerimiento)}:</span> ${escapeHtml(n.titulo)}</p>
                            <p class="text-[9px] font-bold text-slate-400">${escapeHtml(n.fecha_formato)}</p>
                        </div>
                    </a>
                `).join('');
            }
        }
    }

    async function sincronizarDashboard() {
        if (document.visibilityState !== 'visible' || syncEnCurso) return;
        syncEnCurso = true;

        try {
            const res = await fetch('index.php?action=api_sync_estado', { cache: 'no-store' });
            if (!res.ok) return;
            const data = await res.json();
            if (!data.success) return;

            // 1. Actualizar campana de notificaciones
            if (Array.isArray(data.notificaciones)) {
                actualizarCampanaNotificaciones(data.notificaciones, !!data.es_cliente);
            }

            // 2. Actualizar KPIs principales e indicadores de barra superior
            if (data.estadisticas) {
                const elTotal = document.getElementById('kpiTotal');
                const elPend  = document.getElementById('kpiPendientes');
                const elProc  = document.getElementById('kpiEnProceso');
                const elTerm  = document.getElementById('kpiTerminados');

                if (elTotal) elTotal.textContent = data.estadisticas.total;
                if (elPend)  elPend.textContent  = data.estadisticas.pendientes;
                if (elProc)  elProc.textContent  = data.estadisticas.en_proceso;
                if (elTerm)  elTerm.textContent  = data.estadisticas.terminados;

                const badgeProc = document.getElementById('badgeEnProceso');
                const badgeProcCount = document.getElementById('badgeEnProcesoCount');
                const enProcNum = Number(data.estadisticas.en_proceso) || 0;
                if (badgeProc && badgeProcCount) {
                    badgeProcCount.textContent = enProcNum;
                    badgeProc.classList.toggle('hidden', enProcNum === 0);
                }

                const badgeCrit = document.getElementById('badgeCriticos');
                const badgeCritCount = document.getElementById('badgeCriticosCount');
                const critNum = Number(data.estadisticas.criticos) || 0;
                if (badgeCrit && badgeCritCount) {
                    badgeCritCount.textContent = critNum;
                    badgeCrit.classList.toggle('hidden', critNum === 0);
                }
            }

            // 3. Actualizar KPI de Proyectos (Admin)
            const proyGrid = document.getElementById('kpiProyectosGrid');
            if (proyGrid && Array.isArray(data.proyectos_kpi) && data.proyectos_kpi.length > 0) {
                proyGrid.innerHTML = data.proyectos_kpi.map(p => {
                    const total = Number(p.total_tickets) || 0;
                    const pend  = Number(p.pendientes) || 0;
                    const proc  = Number(p.en_proceso) || 0;
                    const term  = Number(p.terminados) || 0;
                    const nombreLower = escapeHtml((p.nombre_proyecto || '').toLowerCase());
                    return `
                        <div class="p-3.5 rounded-lg bg-slate-50/80 border border-slate-200/80 hover:border-indigo-200 hover:bg-indigo-50/20 transition flex flex-col justify-between" data-proyecto-card data-proyecto-nombre="${nombreLower}">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <p class="text-xs font-bold text-slate-700 line-clamp-1" title="${escapeHtml(p.nombre_proyecto)}">
                                    ${escapeHtml(p.nombre_proyecto)}
                                </p>
                                <span class="px-2 py-0.5 text-[11px] font-black rounded-full bg-indigo-100 text-indigo-700 shrink-0">
                                    ${total} ${total === 1 ? 'ticket' : 'tickets'}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-[10px] font-semibold text-slate-500 pt-2 border-t border-slate-200/60">
                                <span class="flex items-center gap-1" title="Pendientes">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> ${pend} pend.
                                </span>
                                <span class="flex items-center gap-1" title="En proceso">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> ${proc} proc.
                                </span>
                                <span class="flex items-center gap-1" title="Terminados">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> ${term} term.
                                </span>
                            </div>
                        </div>
                    `;
                }).join('');
            }

            // 4. Sincronizar filas de la tabla en vivo (nuevos, modificados y eliminados)
            if (table && Array.isArray(data.tickets)) {
                const tbody = table.querySelector('tbody');
                if (tbody) {
                    const idsRecibidos = new Set(data.tickets.map(t => String(t.id_requerimiento)));

                    // Quitar filas de tickets eliminados
                    tbody.querySelectorAll('tr[data-ticket-id]').forEach(row => {
                        if (!idsRecibidos.has(row.getAttribute('data-ticket-id'))) {
                            row.remove();
                        }
                    });

                    // Quitar fila vacía si ahora existen tickets
                    const filaVacia = document.getElementById('filaVaciaTabla');
                    if (data.tickets.length > 0 && filaVacia) {
                        filaVacia.remove();
                    } else if (data.tickets.length === 0 && !filaVacia) {
                        const trVacia = document.createElement('tr');
                        trVacia.id = 'filaVaciaTabla';
                        trVacia.innerHTML = '<td colspan="6" class="p-8 text-center text-slate-500">No hay tickets registrados en el sistema.</td>';
                        tbody.insertBefore(trVacia, sinResultadosTabla || null);
                    }

                    // Insertar nuevos (recorriendo de atrás hacia adelante para mantener los más nuevos arriba) y actualizar existentes
                    const nuevosTickets = [];
                    for (let i = data.tickets.length - 1; i >= 0; i--) {
                        const t = data.tickets[i];
                        const idStr = String(t.id_requerimiento);
                        const row = tbody.querySelector(`tr[data-ticket-id="${idStr}"]`);

                        if (!row) {
                            const nuevaFila = crearFilaTicket(t);
                            tbody.insertBefore(nuevaFila, tbody.firstElementChild);
                            nuevosTickets.push(t);
                            continue;
                        }

                        // Actualizar estado si cambió
                        const estadoActual = row.getAttribute('data-estado');
                        if (estadoActual !== t.estado) {
                            row.setAttribute('data-estado', t.estado);
                            if (row.cells[4]) {
                                row.cells[4].innerHTML = renderEstadoBadge(t.estado);
                            }
                        }

                        // Actualizar responsable si cambió
                        const respNuevo = t.responsable || '';
                        const respActual = row.getAttribute('data-responsable');
                        if (respActual === null || respActual !== respNuevo) {
                            row.setAttribute('data-responsable', respNuevo);
                            if (row.cells[3]) {
                                row.cells[3].innerHTML = renderResponsableHtml(t.responsable);
                            }
                        }

                        // Actualizar proyecto si cambió
                        const proyNuevo = (t.proyecto || '').toLowerCase();
                        if ((row.getAttribute('data-proyecto') || '') !== proyNuevo) {
                            row.setAttribute('data-proyecto', proyNuevo);
                        }
                    }

                    // Notificar de forma sutil si llegaron nuevos requerimientos
                    if (nuevosTickets.length > 0 && typeof Swal !== 'undefined') {
                        const ultimo = nuevosTickets[nuevosTickets.length - 1];
                        const tituloToast = nuevosTickets.length === 1
                            ? `Nuevo ticket #TK-${ultimo.id_requerimiento}`
                            : `${nuevosTickets.length} nuevos requerimientos recibidos`;
                        const textoToast = nuevosTickets.length === 1 ? ultimo.titulo : '';
                        Swal.fire({
                            toast: true,
                            position: 'bottom-end',
                            icon: 'info',
                            title: tituloToast,
                            text: textoToast,
                            showConfirmButton: false,
                            timer: 4500,
                            timerProgressBar: true
                        });
                    }

                    aplicarFiltrosTabla();
                }
            }
        } catch (err) {
            // Silencioso en segundo plano para no interrumpir al usuario
        } finally {
            syncEnCurso = false;
        }
    }

    setInterval(sincronizarDashboard, 4000);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') sincronizarDashboard();
    });
    window.addEventListener('focus', sincronizarDashboard);
});

/* --- FUNCIÓN DE ELIMINACIÓN --- */
window.confirmarEliminacion = function(event, url, idTicket) {
    if (event) event.preventDefault();

    if (typeof Swal === 'undefined') {
        if (confirm('¿Eliminar definitivamente el ticket #TK-' + idTicket + '?')) {
            window.location.href = url;
        }
        return;
    }

    Swal.fire({
        title: '¿Eliminar ticket?',
        text: `Se borrará el ticket #TK-${idTicket}, subtareas, comentarios e imágenes adjuntas.`,
        icon: 'warning',
        width: '24rem',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        buttonsStyling: false,
        heightAuto: false,
        customClass: {
            popup: 'rounded-2xl shadow-2xl border border-slate-100 bg-white p-4',
            title: 'text-lg font-black text-slate-800 mt-2',
            htmlContainer: 'text-sm text-slate-500 mt-2',
            icon: 'text-rose-500 border-rose-500 scale-75 mt-2',
            actions: 'mt-6 w-full flex justify-center gap-3',
            confirmButton: 'bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-bold px-5 py-2.5 transition-colors shadow-sm',
            cancelButton: 'bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-bold px-5 py-2.5 transition-colors border border-slate-200'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
};
