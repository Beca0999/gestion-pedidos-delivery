<?php
namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsAdmin extends BaseWidget
{
    public static function canView(): bool {
        return auth()->id() === 1; // SOLO TÚ (Admin) lo ves
    }

    protected function getStats(): array {
        return [
            Stat::make('Ventas Totales', '$' . number_format(Order::where('status', 'Entregado')->sum('total'), 2))
                ->color('success'),
            Stat::make('Pedidos Hoy', Order::whereDate('created_at', today())->count()),
        ];
    }
}
