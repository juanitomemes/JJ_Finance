@php
    $record = $getRecord();
    
    // Determinar estilos basados en el tipo de cuenta
    $gradient = match ($record->tipo) {
        'credito' => 'bg-gradient-to-br from-gray-800 via-gray-900 to-black text-white',
        'debito' => 'bg-gradient-to-br from-blue-600 via-blue-700 to-blue-900 text-white',
        'efectivo' => 'bg-gradient-to-br from-emerald-500 via-emerald-600 to-emerald-800 text-white',
        'ahorro' => 'bg-gradient-to-br from-purple-600 via-purple-700 to-purple-900 text-white',
        default => 'bg-gradient-to-br from-gray-600 to-gray-800 text-white',
    };
    
    $tipoLabel = match ($record->tipo) {
        'credito' => 'Tarjeta de Crédito',
        'debito' => 'Tarjeta de Débito',
        'efectivo' => 'Efectivo',
        'ahorro' => 'Cuenta de Ahorro',
        default => ucfirst($record->tipo),
    };
@endphp

<div class="{{ $gradient }} rounded-2xl p-6 shadow-xl relative overflow-hidden flex flex-col justify-between min-h-[200px] w-full transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl border border-white/10 group">
    
    <!-- Elementos decorativos de fondo para dar look premium -->
    <div class="absolute top-0 right-0 -mr-12 -mt-12 w-40 h-40 rounded-full bg-white opacity-5 group-hover:opacity-10 transition-opacity duration-300 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 -ml-12 -mb-12 w-32 h-32 rounded-full bg-white opacity-5 group-hover:opacity-10 transition-opacity duration-300 pointer-events-none"></div>
    
    <!-- Brillo sutil (Glassmorphism highlight) -->
    <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl pointer-events-none"></div>

    <!-- Encabezado: Chip y Tipo -->
    <div class="flex justify-between items-start relative z-10">
        <!-- Ícono de Chip -->
        <svg class="w-12 h-9 opacity-90 drop-shadow-md" viewBox="0 0 40 30" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="40" height="30" rx="6" fill="url(#chip-grad)" fill-opacity="0.9"/>
            <path d="M10 0V30M30 0V30M0 15H40M10 10H30M10 20H30" stroke="#926B00" stroke-width="1.5" stroke-opacity="0.5"/>
            <defs>
                <linearGradient id="chip-grad" x1="0" y1="0" x2="40" y2="30" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#FFDF70" />
                    <stop offset="1" stop-color="#D4A017" />
                </linearGradient>
            </defs>
        </svg>
        
        <span class="text-[10px] font-bold tracking-[0.2em] uppercase opacity-75 bg-white/10 px-2 py-1 rounded-md backdrop-blur-sm">
            {{ $tipoLabel }}
        </span>
    </div>

    <!-- Cuerpo: Saldo -->
    <div class="mt-6 relative z-10">
        <span class="text-xs font-medium opacity-75 uppercase tracking-wider block mb-1">Saldo Actual</span>
        <div class="text-3xl font-extrabold tracking-tight drop-shadow-lg flex items-baseline">
            <span class="text-lg mr-1 opacity-80">$</span>
            {{ number_format($record->saldo_actual, 2) }}
            <span class="text-sm ml-1 opacity-80 font-medium">MXN</span>
        </div>
    </div>

    <!-- Pie: Nombre de la cuenta y logos -->
    <div class="mt-6 flex justify-between items-end relative z-10">
        <div class="font-semibold tracking-wide text-lg truncate pr-4 drop-shadow-md">
            {{ $record->nombre }}
        </div>
        
        <!-- Círculos decorativos estilo Master/Visa -->
        <div class="flex items-center space-x-[-12px] opacity-60">
            <div class="w-8 h-8 rounded-full bg-white opacity-40 mix-blend-overlay"></div>
            <div class="w-8 h-8 rounded-full bg-white opacity-40 mix-blend-overlay"></div>
        </div>
    </div>
</div>
