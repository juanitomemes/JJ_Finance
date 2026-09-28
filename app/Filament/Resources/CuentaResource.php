<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CuentaResource\Pages;
use App\Filament\Resources\CuentaResource\RelationManagers;
use App\Models\Cuenta;
use App\Filament\Resources\MovimientoResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
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
                            ->reactive()
                            ->options([
                                'efectivo' => 'Efectivo',
                                'debito' => 'Tarjeta de Débito',
                                'credito' => 'Tarjeta de Crédito',
                                'ahorro' => 'Cuenta de Ahorro',
                            ]),
                        Forms\Components\TextInput::make('limite_credito')
                            ->label('Límite de Crédito')
                            ->required(fn (callable $get) => $get('tipo') === 'credito')
                            ->visible(fn (callable $get) => $get('tipo') === 'credito')
                            ->numeric()
                            ->prefix('$')
                            ->default(0.00),
                        Forms\Components\TextInput::make('dia_corte')
                            ->label('Día de Corte')
                            ->required(fn (callable $get) => $get('tipo') === 'credito')
                            ->visible(fn (callable $get) => $get('tipo') === 'credito')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(31),
                        Forms\Components\TextInput::make('saldo_inicial')
                            ->label(fn (callable $get) => $get('tipo') === 'credito' ? 'Deuda Inicial (Lo que ya debes)' : 'Saldo Inicial')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0.00),
                        Forms\Components\TextInput::make('saldo_actual')
                            ->label(fn (callable $get) => $get('tipo') === 'credito' ? 'Deuda Actual' : 'Saldo Actual')
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
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->recordUrl(
                fn (Cuenta $record): string => Pages\ViewCuenta::getUrl(['record' => $record])
            )
            ->columns([
                Tables\Columns\Layout\View::make('filament.tables.columns.cuenta-card'),
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\ViewEntry::make('card')
                    ->label('')
                    ->view('filament.tables.columns.cuenta-card')
                    ->columnSpanFull(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MovimientosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCuentas::route('/'),
            'create' => Pages\CreateCuenta::route('/create'),
            'view' => Pages\ViewCuenta::route('/{record}'),
            'edit' => Pages\EditCuenta::route('/{record}/edit'),
        ];
    }
}
