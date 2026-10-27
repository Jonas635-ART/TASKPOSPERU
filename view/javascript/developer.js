document.addEventListener('DOMContentLoaded', () => {

    /* --- SIDEBAR / MENU OVERLAY --- */
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
    if (overlay) overlay.addEventListener('click', toggleMenu);


    /* --- NOTIFICACIONES (CAMPANA) --- */
    const btnNotif = document.getElementById('btnNotificaciones');
    const menuNotif = document.getElementById('menuNotificaciones');

    if (btnNotif && menuNotif) {
        // Abrir/Cerrar al dar clic en la campana
        btnNotif.addEventListener('click', (e) => {
            e.stopPropagation();
            menuNotif.classList.toggle('hidden');
        });

        // Cerrar al hacer clic en cualquier parte fuera de la ventana
        document.addEventListener('click', (e) => {
            if (!menuNotif.contains(e.target) && !btnNotif.contains(e.target)) {
                menuNotif.classList.add('hidden');
            }
        });
    }


    /* --- FILTRADO DE TABLA Y ATAJOS DE TECLADO --- */
    const filterInput = document.getElementById('filterTable');
    const globalSearchInput = document.getElementById('searchInput');
    const table = document.getElementById('ticketsTable');
    const rowCountDisplay = document.getElementById('rowCount');

    // Atajo de teclado global (Ctrl + K) para enfocarse en el buscador superior
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            if (globalSearchInput) globalSearchInput.focus();
        }
    });

    // Función de filtrado en cliente para la tabla
    function filterTable(searchTerm) {
        if (!table) return;
        
        const term = searchTerm.toLowerCase();
        const tbody = table.querySelector('tbody');
        if (!tbody) return;
        
        const rows = tbody.querySelectorAll('tr');
        let visibleCount = 0;

        rows.forEach(row => {
            // Ignorar la fila de "No hay tickets"
            if (row.cells.length === 1) return;

            const textContent = row.textContent.toLowerCase();
            if (textContent.includes(term)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (rowCountDisplay) {
            rowCountDisplay.textContent = visibleCount;
        }
    }

    // Escuchar inputs en los campos de búsqueda
    if (filterInput) {
        filterInput.addEventListener('input', (e) => filterTable(e.target.value));
    }

    if (globalSearchInput) {
        globalSearchInput.addEventListener('input', (e) => filterTable(e.target.value));
    }
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
        width: '24rem', // Reduce el ancho masivo por defecto
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        buttonsStyling: false,
        heightAuto: false, // Desactiva los estilos feos por defecto
        customClass: {
            popup: 'rounded-2xl shadow-2xl border border-slate-100 bg-white p-4',
            title: 'text-lg font-black text-slate-800 mt-2',
            htmlContainer: 'text-sm text-slate-500 mt-2',
            icon: 'text-rose-500 border-rose-500 scale-75 mt-2', // Achica el ícono
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

