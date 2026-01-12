<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Rider;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();

        // Si es REPARTIDOR, no mostramos las estadísticas de ventas
        if (Rider::where('user_id', $user->id)->exists()) {
            return [
                Stat::make('Mis Pedidos Pendientes', 
                    Order::where('rider_id', $user->rider->id)
                        ->where('status', '!=', 'Entregado')
                        ->count())
                    ->description('Pedidos que tienes por entregar')
                    ->descriptionIcon('heroicon-m-truck')
                    ->color('warning'),
            ];
        }

        // Si es Admin o Dueño, mostramos lo que ya tenías
        return [
            Stat::make('Ventas Hoy', '$' . number_format(Order::whereDate('created_at', today())->sum('total'), 2))
                ->description('Monto total recaudado')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Pedidos Pendientes', Order::where('status', 'Pendiente')->count())
                ->description('Órdenes por preparar')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
