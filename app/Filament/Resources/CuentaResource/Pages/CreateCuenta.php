<?php

namespace App\Filament\Resources\CuentaResource\Pages;

use App\Filament\Resources\CuentaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCuenta extends CreateRecord
{
    protected static string $resource = CuentaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        
        if (isset($data['tipo']) && $data['tipo'] === 'credito' && isset($data['saldo_inicial'])) {
            $data['saldo_inicial'] = -abs((float) $data['saldo_inicial']);
        }
        
        return $data;
    }
}
