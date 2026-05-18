<?php

namespace App\Filament\Resources\MovimientoResource\Pages;

use App\Filament\Resources\MovimientoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditMovimiento extends EditRecord
{
    protected static string $resource = MovimientoResource::class;

     protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getSavedNotification(): ?Notification
    {
        return null;
    }
    protected function afterSave(): void
    {
        Notification::make()
            ->title('Movimiento actualizado')
            ->body('El movimiento ha sido actualizado exitosamente.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->title('Movimiento eliminada')
                        ->body('El movimiento ha sido eliminado exitosamente.')
                        ->success()
                ),
        ];
    }
}
