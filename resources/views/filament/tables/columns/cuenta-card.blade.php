@php
    $record = $getRecord();
    $isEfectivo = $record->tipo === 'efectivo';
    
    // Determinar estilos para tarjetas (no efectivo)
    $gradient = match ($record->tipo) {
        'credito' => 'bg-gradient-to-br from-gray-800 via-gray-900 to-black',
        'debito' => 'bg-gradient-to-br from-blue-600 via-blue-700 to-blue-900',
        'ahorro' => 'bg-gradient-to-br from-purple-600 via-purple-700 to-purple-900',
        default => 'bg-gradient-to-br from-gray-600 to-gray-800',
    };
    
    $tipoLabel = match ($record->tipo) {
        'credito' => 'Tarjeta de Crédito',
        'debito' => 'Tarjeta de Débito',
        'ahorro' => 'Cuenta de Ahorro',
        'efectivo' => 'Efectivo',
        default => ucfirst($record->tipo),
    };
@endphp

@if($isEfectivo)
    <!-- DISEÑO TIPO BILLETE (Efectivo) -->
    <div class="bg-gradient-to-r from-emerald-700 via-emerald-600 to-emerald-700 !text-white rounded-md p-4 shadow-xl relative overflow-hidden flex flex-col justify-between min-h-[200px] w-full transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl border-[6px] border-emerald-900/50 group">
        
        <!-- Textura de billete (líneas sutiles diagonales) -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0IiBoZWlnaHQ9IjQiPjxyZWN0IHdpZHRoPSI0IiBoZWlnaHQ9IjQiIGZpbGw9Im5vbmUiLz48cG9seWdvbiBmaWxsPSJyZ2JhKDI1NSwyNTUsMjU1LDAuMDcpIiBwb2ludHM9IjQsMCAwLDQgMCwwIi8+PC9zdmc+')] opacity-60 pointer-events-none"></div>

        <!-- Bordes internos decorativos del billete -->
        <div class="absolute inset-0 border-[1px] border-emerald-300/30 m-2 rounded-sm pointer-events-none"></div>
        <div class="absolute inset-0 border-[1px] border-emerald-300/20 m-[14px] rounded-sm pointer-events-none"></div>

        <!-- Sello central (Marca de agua - Banco) -->
        <div class="absolute inset-0 flex items-center justify-center opacity-10 pointer-events-none mix-blend-overlay">
            <svg class="w-32 h-32 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L1 12h3v9h6v-6h4v6h6v-9h3L12 2zm0 2.83l5 4.5V20h-2v-6H9v6H7V9.33l5-4.5z"/>
            </svg>
        </div>

        <!-- Encabezado del Billete -->
        <div class="flex justify-between items-start relative z-10">
            <div class="flex flex-col">
                <span class="!text-emerald-100 text-[10px] font-bold tracking-[0.2em] uppercase">BANCO DE TUS FINANZAS</span>
                <span class="!text-white text-xs font-serif italic opacity-90">{{ $record->nombre }}</span>
            </div>
            
            <!-- Número de serie falso -->
            <span class="!text-emerald-100 font-mono text-xs tracking-widest bg-emerald-900/30 px-2 py-0.5 rounded opacity-80 border border-emerald-800/50">
                SN-{{ str_pad($record->id, 6, '0', STR_PAD_LEFT) }}
            </span>
        </div>

        <!-- Centro: Denominación / Saldo -->
        <div class="flex flex-col items-center justify-center my-3 relative z-10">
            <div class="!text-white text-4xl font-serif font-bold drop-shadow-md flex items-center">
                <span class="text-2xl mr-1 !text-emerald-200 opacity-90 font-sans">$</span>
                {{ number_format($record->saldo_actual, 2) }}
            </div>
            <span class="!text-emerald-200 text-[10px] uppercase tracking-[0.3em] mt-1 opacity-80">Pesos</span>
        </div>

        <!-- Pie: Firma y sello de esquina -->
        <div class="flex justify-between items-end relative z-10">
            <div class="flex flex-col items-center">
                <div class="w-16 h-[1px] bg-emerald-200/40 mb-1"></div>
                <span class="!text-emerald-100 text-[7px] uppercase tracking-widest opacity-60">El Portador</span>
            </div>
            
            <!-- Sello numérico de esquina -->
            <div class="text-xl font-serif font-bold opacity-80 !text-emerald-100 border-2 border-emerald-300/30 rounded-full w-9 h-9 flex items-center justify-center bg-emerald-900/30 shadow-inner">
                $
            </div>
        </div>
    </div>

@else
    <!-- DISEÑO TIPO TARJETA (Crédito, Débito, Ahorro) -->
    <div class="{{ $gradient }} !text-white rounded-2xl p-6 shadow-xl relative overflow-hidden flex flex-col justify-between min-h-[200px] w-full transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl border border-white/10 group">
        
        <!-- Brillo sutil (Glassmorphism highlight) -->
        <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl pointer-events-none"></div>

        <!-- Encabezado: Chip y Tipo -->
        <div class="flex justify-between items-start relative z-10">
            <!-- Ícono de Chip -->
            <svg class="w-11 h-8 opacity-90 drop-shadow-md" viewBox="0 0 40 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="40" height="30" rx="6" fill="url(#chip-grad-{{ $record->id }})" fill-opacity="0.9"/>
                <path d="M10 0V30M30 0V30M0 15H40M10 10H30M10 20H30" stroke="#926B00" stroke-width="1.5" stroke-opacity="0.5"/>
                <defs>
                    <linearGradient id="chip-grad-{{ $record->id }}" x1="0" y1="0" x2="40" y2="30" gradientUnits="userSpaceOnUse">
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
                <div class="w-8 h-8 rounded-full bg-red/60 shadow-sm"></div>
                <div class="w-8 h-8 rounded-full bg-white/30 backdrop-blur-sm border border-white/20"></div>
            </div>
        </div>
    </div>
@endif
