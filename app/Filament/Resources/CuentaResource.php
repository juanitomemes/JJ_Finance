<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CuentaResource\Pages;
use App\Filament\Resources\CuentaResource\RelationManagers;
use App\Models\Cuenta;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CuentaResource extends Resource
{
    protected static ?string $model = Cuenta::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Cuentas / Monederos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make('Información de la Cuenta')
                    ->schema([

                        Forms\Components\TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej. Tarjeta BBVA, Efectivo...'),
                        Forms\Components\Select::make('tipo')
                            ->required()
                            ->options([
                                'efectivo' => 'Efectivo',
                                'debito' => 'Tarjeta de Débito',
                                'credito' => 'Tarjeta de Crédito',
                                'ahorro' => 'Cuenta de Ahorro',
                            ]),
                        Forms\Components\TextInput::make('saldo_inicial')
                            ->label('Saldo Inicial')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0.00),
                        Forms\Components\TextInput::make('saldo_actual')
                            ->label('Saldo Actual')
                            ->disabled()
                            ->numeric()
                            ->prefix('$')
                            ->default(0.00)
                            ->visibleOn('edit'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'efectivo' => 'gray',
                        'debito' => 'info',
                        'credito' => 'danger',
                        'ahorro' => 'success',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('saldo_inicial')
                    ->money('MXN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('saldo_actual')
                    ->money('MXN')
                    ->sortable()
                    ->weight('bold')
                    ->color(fn ($record) => $record->saldo_actual >= 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListCuentas::route('/'),
            'create' => Pages\CreateCuenta::route('/create'),
            'edit' => Pages\EditCuenta::route('/{record}/edit'),
        ];
    }
}
