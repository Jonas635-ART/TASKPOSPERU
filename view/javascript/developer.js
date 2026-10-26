document.addEventListener('DOMContentLoaded', () => {
    const filterInput = document.getElementById('filterTable');
    const globalSearchInput = document.getElementById('searchInput');
    const table = document.getElementById('ticketsTable');
    const rowCountDisplay = document.getElementById('rowCount');

    // Atajo de teclado global (Ctrl + K) para enfocarse en el buscador superior
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            globalSearchInput.focus();
        }
    });

    // Función de filtrado en cliente para la tabla
    function filterTable(searchTerm) {
        if (!table) return;
        
        const term = searchTerm.toLowerCase();
        const tbody = table.querySelector('tbody');
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
