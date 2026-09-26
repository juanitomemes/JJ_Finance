<?php

namespace App\Filament\Resources\CuentaResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MovimientosRelationManager extends RelationManager
{
    protected static string $relationship = 'movimientos';

    public function form(Form $form): Form
    {
        return \App\Filament\Resources\MovimientoResource::form($form);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->label('#')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo de movimiento')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ingreso' => 'success',
                        'gasto' => 'danger',
                        'ahorro' => 'info',
                        'transferencia' => 'warning',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'ingreso' => 'heroicon-m-arrow-trending-up',
                        'gasto' => 'heroicon-m-arrow-trending-down',
                        'ahorro' => 'heroicon-m-banknotes',
                        'transferencia' => 'heroicon-m-arrows-right-left',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto')
                    ->money('MXN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('categoria.nombre')
                    ->label('Categoria')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
