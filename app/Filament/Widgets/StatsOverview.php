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
            Stat::make('Ventas Totales (Hoy)', '$' . number_format(Order::whereDate('created_at', today())->sum('total'), 2))
                ->description('Monto total de todos los pedidos de hoy')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Pedidos Pendientes', Order::where('status', 'Pendiente')->count())
                ->description('Pedidos por procesar')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
