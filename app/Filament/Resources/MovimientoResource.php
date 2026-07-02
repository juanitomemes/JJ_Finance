<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovimientoResource\Pages;
use App\Filament\Resources\MovimientoResource\RelationManagers;
use App\Models\Movimiento;
use App\Models\User;
use App\Models\Categoria;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\ImageColumn;
use Filament\Notifications\Notification;

class MovimientoResource extends Resource
{
    protected static ?string $model = Movimiento::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
         ->schema([

                Card::make('Llene los campos del formulario')
                ->schema([

                Forms\Components\Select::make('tipo')
                    ->label('Tipo de movimiento')
                    ->required()
                    ->options([
                        'ingreso' => 'Ingreso',
                        'gasto' => 'Gasto',
                        'ahorro' => 'Ahorro a Meta',
                    ])
                    ->reactive()
                    ->afterStateUpdated(function (callable $set, $state) {
                        $set('categoria_id', null);
                        
                        if ($state === 'ahorro') {
                            $categoria = \App\Models\Categoria::firstOrCreate(
                                ['nombre' => 'Ahorro', 'user_id' => auth()->id()],
                                ['tipo' => 'gasto']
                            );
                            $set('categoria_id', $categoria->id);
                        }
                    }),
                Forms\Components\Select::make('categoria_id')
                    ->label('Categoría')
                    ->required()
                    ->relationship(
                        name: 'categoria',
                        titleAttribute: 'nombre',
                        modifyQueryUsing: function (Builder $query, callable $get) {
                            $tipoMovimiento = $get('tipo');
                            
                            if (!$tipoMovimiento) {
                                return $query->whereRaw('1 = 0');
                            }

                            $tipoCategoria = $tipoMovimiento === 'ahorro' ? 'gasto' : $tipoMovimiento;

                            return $query
                                ->where('tipo', $tipoCategoria)
                                ->where(function ($q) {
                                    $q->whereNull('user_id')
                                      ->orWhere('user_id', auth()->id());
                                });
                        }
                    )
                    ->disabled(fn (callable $get) => empty($get('tipo')) || $get('tipo') === 'ahorro'),
                Forms\Components\Select::make('cuenta_id')
                    ->label('Cuenta / Monedero')
                    ->required()
                    ->relationship(
                        name: 'cuenta',
                        titleAttribute: 'nombre',
                        modifyQueryUsing: fn (Builder $query) => $query->where('user_id', auth()->id())
                    ),
                Forms\Components\Select::make('meta_id')
                    ->label('Meta de Ahorro Destino')
                    ->relationship('metaAhorro', 'nombre', fn (Builder $query) => $query->where('user_id', auth()->id()))
                    ->visible(fn (callable $get) => $get('tipo') === 'ahorro')
                    ->required(fn (callable $get) => $get('tipo') === 'ahorro'),
                Forms\Components\TextInput::make('monto')
                    ->label('Monto')
                    ->required()
                    ->numeric()
                    ->rules([
                        fn (Forms\Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                            $tipo = $get('tipo');
                            if ($tipo !== 'ahorro' && $tipo !== 'gasto') return;

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
                Forms\Components\RichEditor::make('descripcion')
                    ->label('Descripción')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('foto')
                   // ->label('Foto')
                    ->image()
                    ->disk('public')
                    ->directory('movimientos'),
                Forms\Components\DatePicker::make('fecha')
                    ->required()
                    ->default(now()),

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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cuenta.nombre')
                    ->label('Cuenta')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo de movimiento')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ingreso' => 'success',
                        'gasto' => 'danger',
                        'ahorro' => 'info',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'ingreso' => 'heroicon-m-arrow-trending-up',
                        'gasto' => 'heroicon-m-arrow-trending-down',
                        'ahorro' => 'heroicon-m-banknotes',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto')
                    ->money('MXN')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('foto')
                    ->searchable()
                    ->width(100)
                    ->height(100),
                Tables\Columns\TextColumn::make('fecha')
                    ->date()
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
                    'ahorro' => 'Ahorro a Meta',
                 ])
                 ->placeholder('Filtrar por tipo')
                 ->label('Tipo'),
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
                        ->title('Movimiento eliminado')
                        ->body('El movimiento ha sido eliminado exitosamente.')
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
            'index' => Pages\ListMovimientos::route('/'),
            'create' => Pages\CreateMovimiento::route('/create'),
            'edit' => Pages\EditMovimiento::route('/{record}/edit'),
        ];
    }
}
