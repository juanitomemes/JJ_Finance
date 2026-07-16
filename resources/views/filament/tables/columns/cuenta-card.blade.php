@php
    $record = $getRecord();
    
    // Determinar estilos basados en el tipo de cuenta
    $gradient = match ($record->tipo) {
        'credito' => 'bg-gradient-to-br from-gray-800 via-gray-900 to-black',
        'debito' => 'bg-gradient-to-br from-blue-600 via-blue-700 to-blue-900',
        'efectivo' => 'bg-gradient-to-br from-emerald-500 via-emerald-600 to-emerald-800',
        'ahorro' => 'bg-gradient-to-br from-purple-600 via-purple-700 to-purple-900',
        default => 'bg-gradient-to-br from-gray-600 to-gray-800',
    };
    
    $tipoLabel = match ($record->tipo) {
        'credito' => 'Tarjeta de Crédito',
        'debito' => 'Tarjeta de Débito',
        'efectivo' => 'Efectivo',
        'ahorro' => 'Cuenta de Ahorro',
        default => ucfirst($record->tipo),
    };
@endphp

<div class="{{ $gradient }} !text-white rounded-2xl p-6 shadow-xl relative overflow-hidden flex flex-col justify-between min-h-[200px] w-full transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl border border-white/10 group">
    
    <!-- Brillo sutil (Glassmorphism highlight) -->
    <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl pointer-events-none"></div>

    <!-- Encabezado: Chip y Tipo -->
    <div class="flex justify-between items-start relative z-10">
        <!-- Ícono de Chip -->
        <svg class="w-11 h-8 opacity-90 drop-shadow-md" viewBox="0 0 40 30" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="40" height="30" rx="6" fill="url(#chip-grad)" fill-opacity="0.9"/>
            <path d="M10 0V30M30 0V30M0 15H40M10 10H30M10 20H30" stroke="#926B00" stroke-width="1.5" stroke-opacity="0.5"/>
            <defs>
                <linearGradient id="chip-grad" x1="0" y1="0" x2="40" y2="30" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#FFDF70" />
                    <stop offset="1" stop-color="#D4A017" />
                </linearGradient>
            </defs>
        </svg>
        
        <span class="!text-white text-[10px] font-bold tracking-[0.2em] uppercase opacity-90 bg-white/20 px-2 py-1 rounded-md backdrop-blur-sm">
            {{ $tipoLabel }}
        </span>
    </div>

    <!-- Cuerpo: Saldo -->
    <div class="mt-6 relative z-10">
        <span class="!text-white text-xs font-medium opacity-80 uppercase tracking-wider block mb-1">Saldo Actual</span>
        <div class="!text-white text-3xl font-extrabold tracking-tight drop-shadow-lg flex items-baseline">
            <span class="!text-white text-lg mr-1 opacity-90">$</span>
            {{ number_format($record->saldo_actual, 2) }}
            <span class="!text-white text-sm ml-1 opacity-90 font-medium">MXN</span>
        </div>
    </div>

    <!-- Pie: Nombre de la cuenta y logos -->
    <div class="mt-6 flex justify-between items-end relative z-10">
        <div class="!text-white font-semibold tracking-wide text-lg truncate pr-4 drop-shadow-md">
            {{ $record->nombre }}
        </div>
        
        <!-- Círculos decorativos estilo Master/Visa -->
        <div class="flex items-center space-x-[-12px]">
            <div class="w-8 h-8 rounded-full shadow-sm" style="background-color: rgba(255, 255, 255, 0.6);"></div>
            <div class="w-8 h-8 rounded-full" style="background-color: rgba(255, 255, 255, 0.3); border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(4px);"></div>
        </div>
    </div>
</div>
