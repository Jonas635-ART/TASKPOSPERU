document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.kanban-card');
    const columns = document.querySelectorAll('.kanban-column');

    let draggedCard = null;

    // Configurar eventos de arrastre a las tarjetas
    cards.forEach(card => {
        card.addEventListener('dragstart', (e) => {
            draggedCard = card;
            card.classList.add('dragging');
            e.dataTransfer.setData('text/plain', card.getAttribute('data-id'));
        });

        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
            draggedCard = null;
        });
    });

    // Configurar zonas de soltar (columnas)
    columns.forEach(column => {
        column.addEventListener('dragover', (e) => {
            e.preventDefault();
            column.classList.add('drag-over');
        });

        column.addEventListener('dragleave', () => {
            column.classList.remove('drag-over');
        });

        column.addEventListener('drop', async (e) => {
            e.preventDefault();
            column.classList.remove('drag-over');

            if (!draggedCard) return;

            const cardId = draggedCard.getAttribute('data-id');
            const nuevoEstado = column.getAttribute('data-estado');
            const cardsContainer = column.querySelector('.kanban-cards-container');

            // 1. Mover visualmente la tarjeta de contenedor inmediatamente
            cardsContainer.appendChild(draggedCard);

            // 2. Actualizar contadores dinámicamente
            actualizarContadores();

            // 3. Enviar actualización a PostgreSQL vía Fetch API
            const formData = new FormData();
            formData.append('id_requerimiento', cardId);
            formData.append('estado', nuevoEstado);
            formData.append('prioridad', 'media'); // Mantiene prioridad por defecto o extrae la actual

            try {
                const response = await fetch('index.php?action=api_actualizar_estado', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (!data.success) {
                    alert('Error al guardar en la base de datos.');
                    window.location.reload();
                }
            } catch (err) {
                console.error("Error de conexión:", err);
                window.location.reload();
            }
        });
    });

    // Función para actualizar las etiquetas de conteo numérico de cada columna
    function actualizarContadores() {
        columns.forEach(col => {
            const countBadge = col.querySelector('.count-badge');
            const count = col.querySelectorAll('.kanban-card').length;
            if (countBadge) countBadge.textContent = count;
        });
    }
});