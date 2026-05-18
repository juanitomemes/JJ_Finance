<?php

namespace App\Filament\Resources\CategoriaResource\Pages;

use App\Filament\Resources\CategoriaResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCategoria extends EditRecord
{
    protected static string $resource = CategoriaResource::class;

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
            ->title('Categoría actualizada')
            ->body('La categoría ha sido actualizada exitosamente.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->title('Categoría eliminada')
                        ->body('La categoría ha sido eliminada exitosamente.')
                        ->success()
                ),
        ];
    }
}
