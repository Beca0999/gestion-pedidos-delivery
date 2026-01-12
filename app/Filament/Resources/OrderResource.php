<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Rider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        // DETERMINAMOS SI EL USUARIO ES UN REPARTIDOR
        $isRider = auth()->user()->rider !== null;

        return $form
            ->schema([
                Forms\Components\Section::make('Gestión de Pedido 🚀')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'Pendiente' => 'Pendiente 📥',
                                'En Preparación' => 'En Cocina 🔥',
                                'En Camino' => 'En Camino 🛵',
                                'Entregado' => 'Entregado ✅',
                            ])
                            ->required()
                            ->disabled($isRider), // EL REPARTIDOR NO EDITA EL STATUS AQUÍ
                        
                        Forms\Components\Select::make('rider_id')
                            ->label('Asignar Repartidor')
                            ->relationship('rider', 'name')
                            ->disabled($isRider || auth()->id() !== 1) // SOLO EL ADMIN (ID 1) ASIGNA
                            ->placeholder('Selecciona repartidor...'),
                    ])->columns(2),

                Forms\Components\Section::make('Detalles')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')->disabled(),
                        Forms\Components\TextInput::make('address')->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID'),
                Tables\Columns\TextColumn::make('customer_name')->label('Cliente'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('rider.name')->label('Asignado a'),
            ])
            ->actions([
                // SOLO EL DUEÑO VE EL BOTÓN DE EDITAR/GESTIONAR
                Tables\Actions\EditAction::make()
                    ->hidden(fn () => auth()->user()->rider !== null),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // SI ES REPARTIDOR: Solo ve sus propios pedidos en la tabla
        if (auth()->user()->rider) {
            return $query->where('rider_id', auth()->user()->rider->id);
        }

        // SI ES DUEÑO (Admin): Ve todo para asignar
        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
