<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PresupuestoResource\Pages;
use App\Filament\Resources\PresupuestoResource\RelationManagers;
use App\Models\Presupuesto;
use App\Models\Categoria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use App\Models\User;
use Filament\Forms\Components\Card;
use Filament\Notifications\Notification;

class PresupuestoResource extends Resource
{
    protected static ?string $model = Presupuesto::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([


             Card::make('Llene los campos del formulario')
                ->schema([

                Forms\Components\Select::make('categoria_id')
                    ->required()
                    ->label('Categorias')
                    ->relationship(
                        name: 'categoria',
                        titleAttribute: 'nombre',
                        modifyQueryUsing: fn (Builder $query) => $query
                            ->whereNull('user_id')
                            ->orWhere('user_id', auth()->id())
                    ),
                Forms\Components\TextInput::make('monto_asignado')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('monto_gastado')
                    ->required()
                    ->numeric()
                    ->default(0.00)
                    ->disabled(),
                Forms\Components\Select::make('mes')
                    ->options([
                        'January' => 'Enero',
                        'February' => 'Febrero',
                        'March' => 'Marzo',
                        'April' => 'Abril',
                        'May' => 'Mayo',
                        'June' => 'Junio',
                        'July' => 'Julio',
                        'August' => 'Agosto',
                        'September' => 'Septiembre',
                        'October' => 'Octubre',
                        'November' => 'Noviembre',
                        'December' => 'Diciembre',
                    ])
                    ->required(),
                Forms\Components\Select::make('anio')
                    ->label('Año')
                    ->options(collect(range(date('Y') - 5, date('Y') + 5))->mapWithKeys(fn ($year) => [$year => $year]))
                    ->default(date('Y'))
                    ->required(),

                ])->columns(2)
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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->sortable(),
                Tables\Columns\TextColumn::make('categoria.nombre')
                    ->label('Categoria')
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto_asignado')
                    ->money('MXN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto_gastado')
                    ->money('MXN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('progreso')
                    ->label('% Consumido')
                    ->state(function (Presupuesto $record): string {
                        if ($record->monto_asignado <= 0) {
                            return '0%';
                        }
                        $pct = ($record->monto_gastado / $record->monto_asignado) * 100;
                        return number_format($pct, 0) . '%';
                    })
                    ->badge()
                    ->color(fn (string $state): string => 
                        (intval($state) >= 100) ? 'danger' : 
                        ((intval($state) >= 80) ? 'warning' : 'success')
                    )
                    ->icon(fn (string $state): string => 
                        (intval($state) >= 100) ? 'heroicon-m-exclamation-triangle' : 
                        ((intval($state) >= 80) ? 'heroicon-m-exclamation-circle' : 'heroicon-m-check-circle')
                    ),
                Tables\Columns\TextColumn::make('mes')
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'January' => 'Enero',
                        'February' => 'Febrero',
                        'March' => 'Marzo',
                        'April' => 'Abril',
                        'May' => 'Mayo',
                        'June' => 'Junio',
                        'July' => 'Julio',
                        'August' => 'Agosto',
                        'September' => 'Septiembre',
                        'October' => 'Octubre',
                        'November' => 'Noviembre',
                        'December' => 'Diciembre',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('anio')
                    ->label('Año')
                    ->searchable(),
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
                //
            ])
            ->actions([
                 Tables\Actions\EditAction::make()
                ->button()
                ->color('success'),
                Tables\Actions\DeleteAction::make()
                ->button()
                ->color('danger')
                ->successNotification(
                    Notification::make()
                        ->title('Presupuesto eliminado')
                        ->body('El presupuesto ha sido eliminado exitosamente.')
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
        return parent::getEloquentQuery()->where('user_id', auth()->id());
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
            'index' => Pages\ListPresupuestos::route('/'),
            'create' => Pages\CreatePresupuesto::route('/create'),
            'edit' => Pages\EditPresupuesto::route('/{record}/edit'),
        ];
    }
}
