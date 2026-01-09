<?php
namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class OrderResource extends Resource {
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Gestión de Entregas';

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('created_at')->label('Hora')->since(),
            Tables\Columns\TextColumn::make('customer_name')->label('Cliente'),
            Tables\Columns\TextColumn::make('total')->label('Cobrar')->money('usd'),
            Tables\Columns\SelectColumn::make('status')
                ->options([
                    'Pendiente' => '🔴 Pendiente',
                    'En Camino' => '🟡 En Camino',
                    'Entregado' => '🟢 Entregado',
                ]),
        ])
        ->actions([
            Action::make('print')
                ->label('Imprimir')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn (Order $record): string => route('order.print', $record))
                ->openUrlInNewTab(),
        ])
        ->poll('10s');
    }

    public static function getPages(): array {
        return [ 'index' => Pages\ListOrders::route('/') ];
    }
}
