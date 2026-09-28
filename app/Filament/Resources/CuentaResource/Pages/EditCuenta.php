<?php

namespace App\Filament\Resources\CuentaResource\Pages;

use App\Filament\Resources\CuentaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCuenta extends EditRecord
{
    protected static string $resource = CuentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['tipo']) && $data['tipo'] === 'credito') {
            if (isset($data['saldo_inicial'])) {
                $data['saldo_inicial'] = abs((float) $data['saldo_inicial']);
            }
            if (isset($data['saldo_actual'])) {
                $data['saldo_actual'] = abs((float) $data['saldo_actual']);
            }
        }
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['tipo']) && $data['tipo'] === 'credito') {
            if (isset($data['saldo_inicial'])) {
                $data['saldo_inicial'] = -abs((float) $data['saldo_inicial']);
            }
        }
        
        return $data;
    }
}
