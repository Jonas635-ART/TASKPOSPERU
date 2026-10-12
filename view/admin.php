<?php $usuario = $_SESSION['usuario']; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo | PosPerú</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    <!-- Navbar -->
    <header class="bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center shadow-sm">
        <div class="flex items-center gap-3">
            <span class="text-xl font-black text-indigo-600 tracking-tight">PosPerú</span>
            <span class="text-[10px] bg-rose-100 text-rose-700 font-bold px-2 py-0.5 rounded-md uppercase border border-rose-200">Admin</span>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right hidden md:block">
                <p class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($usuario['nombre']) ?></p>
                <p class="text-xs text-slate-500"><?= htmlspecialchars($usuario['correo']) ?></p>
            </div>
            <a href="index.php?action=logout" class="text-xs font-semibold bg-white hover:bg-rose-50 text-slate-600 hover:text-rose-600 px-4 py-2 rounded-lg border border-slate-200 hover:border-rose-200 transition-colors">
                Cerrar Sesión
            </a>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="max-w-7xl mx-auto p-6 mt-4">
        <h1 class="text-2xl font-bold text-slate-800 mb-6">Resumen General</h1>
        
        <!-- Tarjetas de Métricas (Mockup) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Requerimientos</span>
                <p class="text-3xl font-black mt-2 text-slate-800">0</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-500">En Proceso</span>
                <p class="text-3xl font-black mt-2 text-amber-600">0</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-500">Proyectos Activos</span>
                <p class="text-3xl font-black mt-2 text-indigo-600">0</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-500">Completados</span>
                <p class="text-3xl font-black mt-2 text-emerald-600">0</p>
            </div>
        </div>

        <!-- Tabla de Requerimientos Recientes -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-lg text-slate-800">Requerimientos Recientes</h3>
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-sm transition">
                    + Nuevo Proyecto
                </button>
            </div>
            <div class="p-8 text-center text-slate-500 text-sm">
                Aún no hay requerimientos registrados. (Aquí integraremos la tabla con PostgreSQL).
            </div>
        </div>
    </main>
</body>
</html>