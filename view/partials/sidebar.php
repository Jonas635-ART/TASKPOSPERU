<?php
// Sidebar para admin/developer.
// Requiere: $usuario (sesión) y $paginaActiva ('dashboard' | 'proyectos' | 'kanban' | 'mis_asignaciones')
if (!defined('APP_INIT')) { http_response_code(403); exit(); }

$esAdminSidebar = strtolower($usuario['rol'] ?? '') === 'admin';
$paginaActiva = $paginaActiva ?? '';

$itemsSidebar = [
    'dashboard' => ['Panel Principal', 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
    'proyectos' => ['Proyectos', 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'],
    'kanban' => ['Tablero Kanban', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
    'mis_asignaciones' => ['Mis asignaciones', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
];
if ($esAdminSidebar) {
    $itemsSidebar['usuarios_reset'] = ['Accesos / Claves', 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z'];
}
?>
<aside id="sidebar" class="sidebar-bg bg-slate-900 w-64 flex-shrink-0 flex flex-col h-full text-slate-300 transition-transform duration-300 fixed inset-y-0 left-0 z-40 -translate-x-full lg:static lg:translate-x-0">
    <div class="h-16 flex items-center px-6 border-b border-slate-700/50">
        <div class="flex items-center gap-3 w-full">
            <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center text-white shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <span class="text-lg font-bold text-white tracking-wide">PosPerú <span class="text-indigo-400 font-black text-sm"><?= $esAdminSidebar ? 'ADMIN' : 'PRO' ?></span></span>
            <button id="btnCerrarSidebar" type="button" class="ml-auto lg:hidden text-slate-400 hover:text-white p-1" aria-label="Cerrar menú">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto py-4 px-3 space-y-6">
        <div>
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Operaciones</p>
            <nav class="space-y-1">
                <?php foreach ($itemsSidebar as $accion => [$texto, $icono]):
                    $activo = ($accion === $paginaActiva); ?>
                    <a href="index.php?action=<?= $accion ?>" <?= $activo ? 'aria-current="page"' : '' ?>
                       class="<?= $activo ? 'sidebar-item-active bg-slate-800 text-white' : 'sidebar-item hover:bg-slate-800 hover:text-white' ?> flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $icono ?>"></path></svg>
                        <?= $texto ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>

    <div class="p-4 border-t border-slate-700/50">
        <div class="flex items-center gap-3 bg-slate-800/50 p-2.5 rounded-lg border border-slate-700">
            <div class="w-9 h-9 rounded bg-indigo-900/50 text-indigo-300 border-indigo-500/30 flex items-center justify-center font-bold text-sm border uppercase shrink-0">
                <?= htmlspecialchars(mb_substr($usuario['nombre'], 0, 2)) ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white truncate"><?= htmlspecialchars(explode(' ', $usuario['nombre'])[0]) ?></p>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider truncate"><?= $esAdminSidebar ? 'Admin' : 'Developer' ?></p>
            </div>
            <a href="index.php?action=logout" class="text-slate-400 hover:text-rose-400 transition shrink-0 p-1" title="Cerrar sesión">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </a>
        </div>
    </div>
</aside>

<!-- Fondo oscuro detrás del cajón (solo móvil/tablet) -->
<div id="sidebarBackdrop" class="hidden fixed inset-0 bg-slate-900/50 z-30 lg:hidden"></div>

