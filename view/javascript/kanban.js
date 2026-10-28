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
    // 2. Drag & Drop nativo HTML5 (Escritorio con mouse)
    // ==========================================================
    cards.forEach(card => {
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
    });

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
    // 3. Drag & Drop táctil (Celulares y Tablets)
    //    Mantener presionado (~200 ms) inicia el arrastre;
    //    al acercar el dedo a los bordes, el tablero se desplaza solo.
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

    cards.forEach(card => {
        card.addEventListener('touchstart', (e) => {
            if (e.touches.length !== 1) return;
            // No interceptar toques en enlaces o selectores
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
                // Si el usuario desliza antes de los 200 ms, es scroll normal: cancelar arrastre
                const dist = Math.hypot(touch.clientX - touchState.startX, touch.clientY - touchState.startY);
                if (dist > 10) {
                    limpiarArrastreTactil();
                }
                return;
            }

            // En modo arrastre, bloquear el scroll nativo de la página
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
    });

    // ==========================================================
    // 4. Selector "Mover a" (alternativa rápida y accesible)
    // ==========================================================
    document.querySelectorAll('.kanban-move select').forEach(select => {
        select.addEventListener('change', () => {
            const card = select.closest('.kanban-card');
            const targetCol = document.querySelector(`.kanban-column[data-estado="${select.value}"]`);
            moverTarjeta(card, targetCol);
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
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.trim().toLowerCase();
            cards.forEach(card => {
                const coincide = !q || card.textContent.toLowerCase().includes(q);
                card.classList.toggle('hidden', !coincide);
            });
            actualizarContadores();
        });
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
});
