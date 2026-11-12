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
        $isAdmin = auth()->user()->hasRole('admin');

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
                            ->required(),
                        
                        Forms\Components\Select::make('rider_id')
                            ->label('Asignar Repartidor')
                            ->relationship('rider', 'name')
                            ->disabled(! $isAdmin)
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

        if (auth()->user()?->hasRole('owner')) {
            $query->whereHas('business', fn ($businessQuery) => $businessQuery->where('user_id', auth()->id()));
        }

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
