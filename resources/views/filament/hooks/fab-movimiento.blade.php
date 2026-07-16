@if(request()->routeIs('filament.admin.pages.dashboard'))
    <div style="position: fixed; bottom: 2rem; right: 2rem; z-index: 50;">
        <a href="{{ route('filament.admin.resources.movimientos.create') }}"
           style="display: flex; align-items: center; justify-content: center; width: 4rem; height: 4rem; background-color: rgb(245 158 11); color: white; border-radius: 9999px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -4px rgba(0, 0, 0, 0.2); transition: transform 0.2s;"
           onmouseover="this.style.transform='scale(1.1)'; this.style.backgroundColor='rgb(217 119 6)';"
           onmouseout="this.style.transform='scale(1)'; this.style.backgroundColor='rgb(245 158 11)';"
           title="Agregar Movimiento">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 2rem; height: 2rem;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </a>
    </div>
@endif
