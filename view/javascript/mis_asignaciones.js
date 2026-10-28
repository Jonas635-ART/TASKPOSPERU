document.addEventListener('DOMContentLoaded', () => {
    // ---------- Cajón lateral (móvil / tablet) ----------
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const btnMenu = document.getElementById('btnMenu');
    const btnCerrar = document.getElementById('btnCerrarSidebar');

    function toggleSidebar(abrir) {
        if (!sidebar || !backdrop) return;
        sidebar.classList.toggle('-translate-x-full', !abrir);
        backdrop.classList.toggle('hidden', !abrir);
        if (btnMenu) btnMenu.setAttribute('aria-expanded', abrir ? 'true' : 'false');
    }
    if (btnMenu) btnMenu.addEventListener('click', () => toggleSidebar(true));
    if (btnCerrar) btnCerrar.addEventListener('click', () => toggleSidebar(false));
    if (backdrop) backdrop.addEventListener('click', () => toggleSidebar(false));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') toggleSidebar(false); });

    // ---------- Notificaciones ----------
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

    // ---------- Filtro por estado + búsqueda ----------
    const tarjetas = document.querySelectorAll('.asignacion');
    const filtros = document.querySelectorAll('.filtro-asignacion');
    const buscador = document.getElementById('buscarAsignacion');
    const sinResultados = document.getElementById('sinResultados');
    let filtroActivo = 'todos';

    function aplicarFiltros() {
        const q = (buscador ? buscador.value : '').trim().toLowerCase();
        let visibles = 0;
        tarjetas.forEach(t => {
            const coincideEstado = filtroActivo === 'todos' || t.dataset.estado === filtroActivo;
            const coincideTexto = !q || t.textContent.toLowerCase().includes(q);
            const mostrar = coincideEstado && coincideTexto;
            t.classList.toggle('hidden', !mostrar);
            if (mostrar) visibles++;
        });
        if (sinResultados) sinResultados.classList.toggle('hidden', visibles > 0 || tarjetas.length === 0);
    }

    filtros.forEach(btn => {
        btn.addEventListener('click', () => {
            filtroActivo = btn.dataset.filtro;
            filtros.forEach(b => b.setAttribute('aria-pressed', b === btn ? 'true' : 'false'));
            aplicarFiltros();
        });
    });
    if (buscador) buscador.addEventListener('input', aplicarFiltros);
});

