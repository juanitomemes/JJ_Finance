<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MetaAhorroResource\Pages;
use App\Models\MetaAhorro;
use App\Models\Movimiento;
use App\Models\Categoria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Card;

class MetaAhorroResource extends Resource
{
    protected static ?string $model = MetaAhorro::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $modelLabel = 'Meta de Ahorro';
    protected static ?string $pluralModelLabel = 'Metas de Ahorro';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make('Llene los campos del formulario')
                    ->schema([
                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->id()),
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre de la Meta')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('monto_objetivo')
                            ->label('Monto Objetivo')
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\DatePicker::make('fecha_limite')
                            ->label('Fecha Límite')
                            ->nullable(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Meta')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto_objetivo')
                    ->label('Objetivo')
                    ->money('MXN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto_actual')
                    ->label('Ahorrado')
                    ->money('MXN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('progreso')
                    ->label('Progreso')
                    ->state(function (MetaAhorro $record): string {
                        if ($record->monto_objetivo <= 0) return '0%';
                        $pct = ($record->monto_actual / $record->monto_objetivo) * 100;
                        return number_format(min(100, $pct), 0) . '%';
                    })
                    ->badge()
                    ->color(fn ($state) => 
                        (intval($state) >= 100) ? 'success' : 
                        ((intval($state) >= 75) ? 'info' :    
                        ((intval($state) >= 30) ? 'warning' : 
                        'danger'))                            
                    )
                    ->icon(fn ($state) => 
                        (intval($state) >= 100) ? 'heroicon-m-check-badge' : 'heroicon-m-sparkles'
                    ),
                Tables\Columns\TextColumn::make('fecha_limite')
                    ->label('Límite')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('aportar')
                    ->label('Ahorrar')
                    ->icon('heroicon-m-banknotes')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('cuenta_id')
                            ->label('Cuenta Origen')
                            ->relationship('user.cuentas', 'nombre', fn ($query) => $query->where('user_id', auth()->id()))
                            ->required()
                            ->reactive(),
                        Forms\Components\TextInput::make('monto')
                            ->label('Monto a Ahorrar')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->rules([
                                fn (Forms\Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $cuentaId = $get('cuenta_id');
                                    if (!$cuentaId) return;

                                    $cuenta = \App\Models\Cuenta::find($cuentaId);
                                    if (!$cuenta) return;

                                    // Lógica inteligente híbrida: Ignorar tarjetas de crédito
                                    if ($cuenta->tipo === 'credito') {
                                        return;
                                    }

                                    if ($value > $cuenta->saldo_actual) {
                                        $saldoFormateado = '$' . number_format($cuenta->saldo_actual, 2);
                                        $fail("Saldo insuficiente en tu cuenta de {$cuenta->tipo}. Saldo disponible: {$saldoFormateado}.");
                                    }
                                },
                            ]),
                        Forms\Components\DatePicker::make('fecha')
                            ->label('Fecha')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('descripcion')
                            ->label('Nota/Comentario')
                            ->default('Aporte a la meta')
                            ->required(),
                    ])
                    ->action(function (MetaAhorro $record, array $data): void {
                        $categoria = Categoria::firstOrCreate(
                            ['nombre' => 'Ahorro', 'user_id' => auth()->id()],
                            ['tipo' => 'gasto']
                        );

                        Movimiento::create([
                            'user_id' => auth()->id(),
                            'cuenta_id' => $data['cuenta_id'],
                            'meta_id' => $record->id,
                            'categoria_id' => $categoria->id,
                            'tipo' => 'ahorro',
                            'monto' => $data['monto'],
                            'fecha' => $data['fecha'],
                            'descripcion' => $data['descripcion'],
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('¡Ahorro Guardado!')
                            ->body("Se han transferido $" . number_format($data['monto'], 2) . " a la meta: {$record->nombre}.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListMetaAhorros::route('/'),
            'create' => Pages\CreateMetaAhorro::route('/create'),
            'edit' => Pages\EditMetaAhorro::route('/{record}/edit'),
        ];
    }
}
