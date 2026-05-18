<?php

namespace App\Filament\Resources\MetaAhorroResource\Pages;

use App\Filament\Resources\MetaAhorroResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMetaAhorro extends EditRecord
{
    protected static string $resource = MetaAhorroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
