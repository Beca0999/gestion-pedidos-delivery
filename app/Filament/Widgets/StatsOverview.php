<?php
namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Ventas Totales', '$' . number_format(Order::where('status', 'Entregado')->sum('total'), 2))
                ->description('Dinero recaudado de pedidos entregados')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Pedidos Pendientes', Order::where('status', 'Pendiente')->count())
                ->description('Pedidos por procesar')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
