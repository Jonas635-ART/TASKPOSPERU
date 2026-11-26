document.addEventListener('DOMContentLoaded', () => {
    const board = document.getElementById('kanbanBoard');
    const cards = document.querySelectorAll('.kanban-card');
    const columns = document.querySelectorAll('.kanban-column');
    const tabs = document.querySelectorAll('.kanban-tab');

    let draggedCard = null;

    // ==========================================================
    // 1. Mover tarjeta a una columna y persistir en base de datos
    // ==========================================================
    async function moverTarjeta(card, targetColumn) {
        if (!card || !targetColumn) return;

        const cardId = card.getAttribute('data-id');
        const nuevoEstado = targetColumn.getAttribute('data-estado');
        const originColumn = card.closest('.kanban-column');
        const estadoAnterior = originColumn ? originColumn.getAttribute('data-estado') : card.getAttribute('data-estado');

        if (!nuevoEstado || nuevoEstado === estadoAnterior) return;

        const targetContainer = targetColumn.querySelector('.kanban-cards-container');
        if (!targetContainer) return;

        // Mover visualmente de inmediato
        targetContainer.appendChild(card);
        card.setAttribute('data-estado', nuevoEstado);
        const select = card.querySelector('.kanban-move select');
        if (select) select.value = nuevoEstado;

        actualizarContadores();

        // Guardar en PostgreSQL (sin enviar prioridad para conservar la actual del ticket)
        const formData = new FormData();
        formData.append('id_requerimiento', cardId);
        formData.append('estado', nuevoEstado);

        try {
            const response = await fetch('index.php?action=api_actualizar_estado', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (!data.success) {
                alert(data.message || 'Error al guardar en la base de datos.');
                window.location.reload();
            }
        } catch (err) {
            console.error('Error de conexión:', err);
            window.location.reload();
        }
    }

    // ==========================================================
    // 2. Drag & Drop nativo HTML5 + Táctil + Selector "Mover a"
    //    (Centralizado para soportar tarjetas cargadas en vivo)
    // ==========================================================
    let touchState = null;

    function limpiarArrastreTactil() {
        if (!touchState) return;
        clearTimeout(touchState.holdTimer);
        if (touchState.autoScrollRaf) cancelAnimationFrame(touchState.autoScrollRaf);
        if (touchState.ghost) touchState.ghost.remove();
        touchState.card.classList.remove('dragging');
        if (board) board.style.scrollSnapType = '';
        columns.forEach(col => col.classList.remove('drag-over'));
        tabs.forEach(tab => tab.classList.remove('drag-over'));
        touchState = null;
    }

    function iniciarFantasmaTactil(state) {
        state.active = true;
        const card = state.card;
        const rect = card.getBoundingClientRect();

        state.offsetX = state.startX - rect.left;
        state.offsetY = state.startY - rect.top;

        const ghost = card.cloneNode(true);
        ghost.classList.add('kanban-drag-ghost');
        ghost.style.width = `${rect.width}px`;
        ghost.style.transform = `translate3d(${rect.left}px, ${rect.top}px, 0) rotate(2deg) scale(1.03)`;
        document.body.appendChild(ghost);
        state.ghost = ghost;

        card.classList.add('dragging');
        if (board) board.style.scrollSnapType = 'none'; // Evita saltos bruscos durante el auto-scroll
        if (navigator.vibrate) navigator.vibrate(30);
    }

    function detectarDestinoEnPunto(x, y) {
        const el = document.elementFromPoint(x, y);
        if (!el) return { col: null, tab: null };
        return {
            col: el.closest('.kanban-column'),
            tab: el.closest('.kanban-tab')
        };
    }

    function actualizarAutoScroll() {
        if (!touchState || !touchState.active || !board) return;

        const bRect = board.getBoundingClientRect();
        const edge = 56;
        const maxSpeed = 14;
        let dx = 0;

        if (touchState.x < bRect.left + edge) {
            dx = -maxSpeed * (1 - Math.max(0, touchState.x - bRect.left) / edge);
        } else if (touchState.x > bRect.right - edge) {
            dx = maxSpeed * (1 - Math.max(0, bRect.right - touchState.x) / edge);
        }

        if (dx !== 0) {
            board.scrollLeft += dx;
        }

        // Scroll vertical dentro de la columna bajo el dedo
        if (touchState.targetColumn) {
            const container = touchState.targetColumn.querySelector('.kanban-cards-container');
            if (container) {
                const cRect = container.getBoundingClientRect();
                if (touchState.y < cRect.top + 45) {
                    container.scrollTop -= 8;
                } else if (touchState.y > cRect.bottom - 45) {
                    container.scrollTop += 8;
                }
            }
        }

        touchState.autoScrollRaf = requestAnimationFrame(actualizarAutoScroll);
    }

    function vincularEventosTarjeta(card) {
        if (!card || card.dataset.bound === '1') return;
        card.dataset.bound = '1';

        // Drag & Drop HTML5 (Escritorio)
        card.addEventListener('dragstart', (e) => {
            draggedCard = card;
            card.classList.add('dragging');
            e.dataTransfer.setData('text/plain', card.getAttribute('data-id'));
        });

        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
            columns.forEach(col => col.classList.remove('drag-over'));
            tabs.forEach(tab => tab.classList.remove('drag-over'));
            draggedCard = null;
        });

        // Drag & Drop táctil (Celulares y Tablets)
        card.addEventListener('touchstart', (e) => {
            if (e.touches.length !== 1) return;
            if (e.target.closest('a, select, button, input, label')) return;

            const touch = e.touches[0];
            touchState = {
                card,
                startX: touch.clientX,
                startY: touch.clientY,
                x: touch.clientX,
                y: touch.clientY,
                active: false,
                ghost: null,
                targetColumn: null,
                holdTimer: null,
                autoScrollRaf: null
            };

            touchState.holdTimer = setTimeout(() => {
                if (!touchState) return;
                iniciarFantasmaTactil(touchState);
                touchState.autoScrollRaf = requestAnimationFrame(actualizarAutoScroll);
            }, 200);
        }, { passive: true });

        card.addEventListener('touchmove', (e) => {
            if (!touchState) return;
            const touch = e.touches[0];
            touchState.x = touch.clientX;
            touchState.y = touch.clientY;

            if (!touchState.active) {
                const dist = Math.hypot(touch.clientX - touchState.startX, touch.clientY - touchState.startY);
                if (dist > 10) {
                    limpiarArrastreTactil();
                }
                return;
            }

            e.preventDefault();

            const left = touch.clientX - touchState.offsetX;
            const top = touch.clientY - touchState.offsetY;
            touchState.ghost.style.transform = `translate3d(${left}px, ${top}px, 0) rotate(2deg) scale(1.03)`;

            const { col, tab } = detectarDestinoEnPunto(touch.clientX, touch.clientY);
            columns.forEach(c => c.classList.toggle('drag-over', c === col));
            tabs.forEach(t => t.classList.toggle('drag-over', t === tab));

            if (tab) {
                const estado = tab.getAttribute('data-estado');
                touchState.targetColumn = document.querySelector(`.kanban-column[data-estado="${estado}"]`);
            } else {
                touchState.targetColumn = col;
            }
        }, { passive: false });

        card.addEventListener('touchend', () => {
            if (!touchState) return;
            if (touchState.active && touchState.targetColumn) {
                moverTarjeta(touchState.card, touchState.targetColumn);
            }
            limpiarArrastreTactil();
        });

        card.addEventListener('touchcancel', limpiarArrastreTactil);

        // Selector "Mover a"
        const select = card.querySelector('.kanban-move select');
        if (select) {
            select.addEventListener('change', () => {
                const targetCol = document.querySelector(`.kanban-column[data-estado="${select.value}"]`);
                moverTarjeta(card, targetCol);
            });
        }
    }

    cards.forEach(vincularEventosTarjeta);

    columns.forEach(column => {
        column.addEventListener('dragover', (e) => {
            e.preventDefault();
            column.classList.add('drag-over');
        });

        column.addEventListener('dragleave', (e) => {
            if (!column.contains(e.relatedTarget)) {
                column.classList.remove('drag-over');
            }
        });

        column.addEventListener('drop', (e) => {
            e.preventDefault();
            column.classList.remove('drag-over');
            if (draggedCard) {
                moverTarjeta(draggedCard, column);
            }
        });
    });

    // También permitir soltar directamente sobre las pestañas superiores en vista compacta
    tabs.forEach(tab => {
        tab.addEventListener('dragover', (e) => {
            e.preventDefault();
            tab.classList.add('drag-over');
        });
        tab.addEventListener('dragleave', () => tab.classList.remove('drag-over'));
        tab.addEventListener('drop', (e) => {
            e.preventDefault();
            tab.classList.remove('drag-over');
            if (!draggedCard) return;
            const estado = tab.getAttribute('data-estado');
            const targetCol = document.querySelector(`.kanban-column[data-estado="${estado}"]`);
            moverTarjeta(draggedCard, targetCol);
        });
    });

    // ==========================================================
    // 5. Pestañas de columnas + sincronización con scroll-snap
    // ==========================================================
    function marcarTabActiva(estado) {
        tabs.forEach(tab => {
            if (tab.getAttribute('data-estado') === estado) {
                tab.setAttribute('aria-current', 'true');
            } else {
                tab.removeAttribute('aria-current');
            }
        });
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            const estado = tab.getAttribute('data-estado');
            const col = document.querySelector(`.kanban-column[data-estado="${estado}"]`);
            if (col && board) {
                board.scrollTo({ left: col.offsetLeft - board.offsetLeft, behavior: 'smooth' });
                marcarTabActiva(estado);
            }
        });
    });

    if (board && columns.length) {
        if ('onscrollsnapchange' in HTMLElement.prototype) {
            board.addEventListener('scrollsnapchange', (e) => {
                const snapped = e.snapTargetInline;
                if (snapped && snapped.dataset.estado) {
                    marcarTabActiva(snapped.dataset.estado);
                }
            });
        } else if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && entry.intersectionRatio >= 0.6) {
                        marcarTabActiva(entry.target.getAttribute('data-estado'));
                    }
                });
            }, { root: board, threshold: 0.6 });
            columns.forEach(col => observer.observe(col));
        }
    }

    // ==========================================================
    // 6. Buscador en tiempo real del tablero
    // ==========================================================
    const searchInput = document.querySelector('header input[type="text"]');
    function aplicarFiltroBusqueda() {
        const q = searchInput ? searchInput.value.trim().toLowerCase() : '';
        document.querySelectorAll('.kanban-card').forEach(card => {
            const coincide = !q || card.textContent.toLowerCase().includes(q);
            card.classList.toggle('hidden', !coincide);
        });
        actualizarContadores();
    }
    if (searchInput) {
        searchInput.addEventListener('input', aplicarFiltroBusqueda);
    }

    // ==========================================================
    // 7. Cajón lateral (Sidebar) en móvil / tablet
    // ==========================================================
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const btnMenu = document.getElementById('btnMenu');
    const btnCerrarSidebar = document.getElementById('btnCerrarSidebar');

    function toggleSidebar(abrir) {
        if (!sidebar || !backdrop) return;
        sidebar.classList.toggle('-translate-x-full', !abrir);
        backdrop.classList.toggle('hidden', !abrir);
        if (btnMenu) btnMenu.setAttribute('aria-expanded', abrir ? 'true' : 'false');
    }

    if (btnMenu) btnMenu.addEventListener('click', () => toggleSidebar(true));
    if (btnCerrarSidebar) btnCerrarSidebar.addEventListener('click', () => toggleSidebar(false));
    if (backdrop) backdrop.addEventListener('click', () => toggleSidebar(false));

    // ==========================================================
    // 8. Menú de notificaciones
    // ==========================================================
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

    // ==========================================================
    // 9. Contadores de columnas y pestañas
    // ==========================================================
    function actualizarContadores() {
        columns.forEach(col => {
            const estado = col.getAttribute('data-estado');
            const visibles = col.querySelectorAll('.kanban-card:not(.hidden)').length;
            const countBadge = col.querySelector('.count-badge');
            if (countBadge) countBadge.textContent = visibles;

            const tabCount = document.querySelector(`.tab-count[data-estado="${estado}"]`);
            if (tabCount) tabCount.textContent = visibles;
        });
    }

    // ==========================================================
    // 10. Sincronización automática en segundo plano (cada 4 s)
    // ==========================================================
    let syncEnCurso = false;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function actualizarCampanaNotificaciones(notificaciones, esCliente) {
        if (!btnNotif || !menuNotif) return;

        let dot = btnNotif.querySelector('span.bg-rose-500');
        if (notificaciones.length > 0 && !dot) {
            dot = document.createElement('span');
            dot.className = 'absolute top-0 right-0 w-2 h-2 bg-rose-500 rounded-full border border-white animate-pulse';
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

    function crearTarjetaKanban(t) {
        const idCard = Number(t.id_requerimiento);
        const prio = (t.prioridad || 'media').toLowerCase();
        const prioBg = (prio === 'alta' || prio === 'critica')
            ? 'bg-rose-100 text-rose-700 border-rose-200'
            : (prio === 'media' ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-slate-100 text-slate-600 border-slate-200');
        const estadoCard = (t.estado || 'pendiente').toLowerCase();
        const totalSub = Number(t.total_subtareas) || 0;
        const compSub = Number(t.subtareas_completadas) || 0;
        const fechaEntrega = t.fecha_entrega || '';

        let fechaEntregaHtml = '';
        if (fechaEntrega) {
            const hoy = new Date().toISOString().slice(0, 10);
            const vencido = estadoCard !== 'terminado' && fechaEntrega < hoy;
            const venceHoy = estadoCard !== 'terminado' && fechaEntrega === hoy;
            const clsFecha = vencido
                ? 'bg-rose-50 text-rose-700 border border-rose-200'
                : (venceHoy ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-indigo-50 text-indigo-700 border border-indigo-100');
            const [y, m, d] = fechaEntrega.split('-');
            fechaEntregaHtml = `
                <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-md ${clsFecha}">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    ${escapeHtml(`${d}/${m}/${y}`)}
                </span>`;
        }

        const subtareasHtml = (totalSub > 0 || fechaEntregaHtml) ? `
            <div class="mb-3 flex flex-wrap items-center gap-1.5">
                ${totalSub > 0 ? `
                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">
                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    ${compSub} / ${totalSub} Subtareas
                </span>` : ''}
                ${fechaEntregaHtml}
            </div>` : '';

        const responsableHtml = t.responsable ? `
            <div class="w-5 h-5 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-[9px] uppercase shrink-0" title="Responsable: ${escapeHtml(t.responsable)}">
                ${escapeHtml(String(t.responsable).slice(0, 2))}
            </div>
            <span class="text-[10px] text-slate-600 font-medium truncate max-w-[120px]">${escapeHtml(t.responsable)}</span>`
            : '<span class="text-[10px] text-slate-400 italic">Sin asignar</span>';

        const card = document.createElement('div');
        card.className = 'kanban-card bg-white p-4 rounded-xl shadow-sm border border-slate-200/80 cursor-grab active:cursor-grabbing hover:shadow-md transition-all duration-200 ring-2 ring-indigo-400';
        card.setAttribute('draggable', 'true');
        card.setAttribute('data-id', String(idCard));
        card.setAttribute('data-estado', estadoCard);

        card.innerHTML = `
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="font-mono text-[11px] font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100">#TK-${idCard}</span>
                <span class="text-[9px] font-bold uppercase px-2 py-0.5 rounded border ${prioBg}">${escapeHtml(prio)}</span>
            </div>
            <h3 class="font-bold text-slate-800 text-xs mb-1 line-clamp-2 leading-snug">${escapeHtml(t.titulo)}</h3>
            <p class="text-[11px] text-slate-500 line-clamp-2 mb-3 leading-relaxed">${escapeHtml(t.descripcion_tecnica)}</p>
            ${subtareasHtml}
            <div class="kanban-move items-center gap-2 mb-3">
                <label for="mover-${idCard}" class="text-[10px] font-bold text-slate-400 uppercase shrink-0">Mover a</label>
                <select id="mover-${idCard}" class="flex-1 min-w-0 bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 rounded-lg px-2 py-2 outline-none focus:border-indigo-500">
                    <option value="pendiente" ${estadoCard === 'pendiente' ? 'selected' : ''}>Pendiente</option>
                    <option value="en proceso" ${estadoCard === 'en proceso' ? 'selected' : ''}>En Proceso</option>
                    <option value="terminado" ${estadoCard === 'terminado' ? 'selected' : ''}>Terminado</option>
                </select>
            </div>
            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                <div class="flex items-center gap-1.5 min-w-0">
                    ${responsableHtml}
                </div>
                <a href="index.php?action=ver_ticket&id=${idCard}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-0.5 py-1 pl-2 shrink-0">
                    Detalle &rarr;
                </a>
            </div>
        `;

        setTimeout(() => card.classList.remove('ring-2', 'ring-indigo-400'), 3500);
        return card;
    }

    async function sincronizarKanban() {
        if (document.visibilityState !== 'visible' || draggedCard || touchState || syncEnCurso) return;
        syncEnCurso = true;

        try {
            const res = await fetch('index.php?action=api_sync_estado', { cache: 'no-store' });
            if (!res.ok) return;
            const data = await res.json();
            if (!data.success) return;

            if (Array.isArray(data.notificaciones)) {
                actualizarCampanaNotificaciones(data.notificaciones, !!data.es_cliente);
            }

            if (!Array.isArray(data.tickets)) return;

            let huboCambios = false;
            const idsRecibidos = new Set(data.tickets.map(t => String(t.id_requerimiento)));

            // Eliminar tarjetas de tickets borrados
            document.querySelectorAll('.kanban-card').forEach(card => {
                if (!idsRecibidos.has(card.getAttribute('data-id'))) {
                    card.remove();
                    huboCambios = true;
                }
            });

            // Insertar nuevas tarjetas y mover las existentes si cambió su estado
            for (let i = data.tickets.length - 1; i >= 0; i--) {
                const t = data.tickets[i];
                const idStr = String(t.id_requerimiento);
                const card = document.querySelector(`.kanban-card[data-id="${idStr}"]`);

                if (!card) {
                    const targetCol = document.querySelector(`.kanban-column[data-estado="${t.estado}"] .kanban-cards-container`)
                        || document.querySelector('.kanban-column[data-estado="pendiente"] .kanban-cards-container');
                    if (targetCol) {
                        const nuevaCard = crearTarjetaKanban(t);
                        vincularEventosTarjeta(nuevaCard);
                        targetCol.insertBefore(nuevaCard, targetCol.firstElementChild);
                        huboCambios = true;
                    }
                    continue;
                }

                const estadoActual = card.getAttribute('data-estado');
                if (estadoActual && estadoActual !== t.estado) {
                    const targetCol = document.querySelector(`.kanban-column[data-estado="${t.estado}"] .kanban-cards-container`);
                    if (targetCol) {
                        targetCol.appendChild(card);
                        card.setAttribute('data-estado', t.estado);
                        const select = card.querySelector('.kanban-move select');
                        if (select) select.value = t.estado;
                        huboCambios = true;
                    }
                }
            }

            if (huboCambios) {
                aplicarFiltroBusqueda();
            }
        } catch (err) {
            // Silencioso en segundo plano
        } finally {
            syncEnCurso = false;
        }
    }

    setInterval(sincronizarKanban, 4000);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') sincronizarKanban();
    });
    window.addEventListener('focus', sincronizarKanban);
});
