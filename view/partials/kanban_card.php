<?php
if (!defined('APP_INIT')) { http_response_code(403); exit(); }
if (!isset($t) || empty($t)) { return; }

$prio = strtolower($t['prioridad'] ?? 'media');
$prioBg = ($prio === 'alta' || $prio === 'critica') 
    ? 'bg-rose-100 text-rose-700 border-rose-200' 
    : (($prio === 'media') ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-slate-100 text-slate-600 border-slate-200');

$estadoCard = strtolower($t['estado'] ?? 'pendiente');
$opcionesEstado = ['pendiente' => 'Pendiente', 'en proceso' => 'En Proceso', 'terminado' => 'Terminado'];
$idCard = (int)$t['id_requerimiento'];
?>
<div class="kanban-card bg-white p-4 rounded-xl shadow-sm border border-slate-200/80 cursor-grab active:cursor-grabbing hover:shadow-md transition-all duration-200" 
     draggable="true" 
     data-id="<?= $idCard ?>"
     data-estado="<?= htmlspecialchars($estadoCard) ?>">
    
    <div class="flex items-center justify-between gap-2 mb-2">
        <span class="font-mono text-[11px] font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100">#TK-<?= $idCard ?></span>
        <span class="text-[9px] font-bold uppercase px-2 py-0.5 rounded border <?= $prioBg ?>"><?= htmlspecialchars($prio) ?></span>
    </div>

    <h3 class="font-bold text-slate-800 text-xs mb-1 line-clamp-2 leading-snug"><?= htmlspecialchars($t['titulo']) ?></h3>
    <p class="text-[11px] text-slate-500 line-clamp-2 mb-3 leading-relaxed"><?= htmlspecialchars($t['descripcion_tecnica']) ?></p>

    <!-- Subtareas Pill -->
    <?php if (($t['total_subtareas'] ?? 0) > 0): ?>
    <div class="mb-3">
        <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">
            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            <?= (int)($t['subtareas_completadas'] ?? 0) ?> / <?= (int)($t['total_subtareas'] ?? 0) ?> Subtareas
        </span>
    </div>
    <?php endif; ?>

    <!-- Mover a (alternativa al arrastre en pantallas táctiles / pequeñas; visibilidad en kanban.css) -->
    <div class="kanban-move items-center gap-2 mb-3">
        <label for="mover-<?= $idCard ?>" class="text-[10px] font-bold text-slate-400 uppercase shrink-0">Mover a</label>
        <select id="mover-<?= $idCard ?>" class="flex-1 min-w-0 bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 rounded-lg px-2 py-2 outline-none focus:border-indigo-500">
            <?php foreach ($opcionesEstado as $valor => $texto): ?>
                <option value="<?= htmlspecialchars($valor) ?>" <?= $valor === $estadoCard ? 'selected' : '' ?>><?= $texto ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Footer de la Tarjeta -->
    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
        <div class="flex items-center gap-1.5 min-w-0">
            <?php if (!empty($t['responsable'])): ?>
                <div class="w-5 h-5 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-[9px] uppercase shrink-0" title="Responsable: <?= htmlspecialchars($t['responsable']) ?>">
                    <?= htmlspecialchars(mb_substr($t['responsable'], 0, 2)) ?>
                </div>
                <span class="text-[10px] text-slate-600 font-medium truncate max-w-[120px]"><?= htmlspecialchars($t['responsable']) ?></span>
            <?php else: ?>
                <span class="text-[10px] text-slate-400 italic">Sin asignar</span>
            <?php endif; ?>
        </div>

        <a href="index.php?action=ver_ticket&id=<?= $idCard ?>" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-0.5 py-1 pl-2 shrink-0">
            Detalle &rarr;
        </a>
    </div>
</div>
