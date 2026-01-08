<?php
namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource {
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Gestión de Entregas';

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Section::make('Detalles del Pedido')
                ->schema([
                    Forms\Components\TextInput::make('customer_name')
                        ->label('Nombre del Cliente')
                        ->required(),
                    Forms\Components\TextInput::make('phone')
                        ->label('Teléfono')
                        ->required(),
                    Forms\Components\TextInput::make('total')
                        ->label('Total a Cobrar')
                        ->numeric()
                        ->prefix('$')
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->options([
                            'Pendiente' => 'Pendiente',
                            'En Camino' => 'En Camino',
                            'Entregado' => 'Entregado',
                        ])
                        ->default('Pendiente')
                        ->required(),
                ])
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('created_at')->label('Recibido')->since()->sortable(),
            Tables\Columns\TextColumn::make('customer_name')->label('Cliente')->searchable(),
            Tables\Columns\TextColumn::make('total')->label('Total')->money('usd'),
            Tables\Columns\SelectColumn::make('status')
                ->label('Estado')
                ->options([
                    'Pendiente' => '🔴 Pendiente',
                    'En Camino' => '🟡 En Camino',
                    'Entregado' => '🟢 Entregado',
                ]),
        ])
        ->defaultSort('created_at', 'desc')
        ->poll('10s');
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
