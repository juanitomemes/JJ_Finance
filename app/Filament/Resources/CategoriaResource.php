<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaResource\Pages;
use App\Filament\Resources\CategoriaResource\RelationManagers;
use App\Models\Categoria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoriaResource extends Resource
{
    protected static ?string $model = Categoria::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Card::make('Llene los campos del formulario')
                ->schema([
                    Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->required()
                            ->label('Nombre de la categoria')
                            ->placeholder('Ingrese el nombre de la categoria')
                            ->maxLength(255),
                        Forms\Components\Select::make('tipo')
                            ->options([
                                'ingreso' => 'Ingreso',
                                'gasto' => 'Gasto',
                            ])
                            ->label('Tipo de movimiento')
                            ->required(),
                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->id()),
                    ])
                ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->label('#')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ingreso' => 'success',
                        'gasto' => 'danger',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'ingreso' => 'heroicon-m-arrow-trending-up',
                        'gasto' => 'heroicon-m-arrow-trending-down',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo')
                 ->options([
                    'ingreso' => 'Ingreso',
                    'gasto' => 'Gasto',
                 ])
                 ->placeholder('Filtrar por tipo')
                 ->label('Tipo'),

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->button()
                ->color('success')
                // Solo el propietario puede editar; las categorías globales (user_id=null) son de solo lectura.
                ->visible(fn (Categoria $record): bool => $record->user_id !== null),
                Tables\Actions\DeleteAction::make()
                ->button()
                ->color('danger')
                // Solo se pueden eliminar categorías propias, nunca las globales del sistema.
                ->visible(fn (Categoria $record): bool => $record->user_id !== null)
                ->successNotification(
                    Notification::make()
                        ->title('Categoría eliminada')
                        ->body('La categoría ha sido eliminada exitosamente.')
                        ->success()
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function (Builder $query) {
                $query->whereNull('user_id')
                      ->orWhere('user_id', auth()->id());
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategorias::route('/'),
            'create' => Pages\CreateCategoria::route('/create'),
            'edit' => Pages\EditCategoria::route('/{record}/edit'),
        ];
    }
}
