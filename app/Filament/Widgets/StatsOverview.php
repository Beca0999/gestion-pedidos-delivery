<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Business;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->hasRole(['admin', 'owner']) ?? false;
    }

    protected function getStats(): array
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return [
                Stat::make('Ventas de hoy', '$' . number_format(
                    Order::where('status', 'Entregado')->whereDate('created_at', today())->sum('total'),
                    2
                ))->description('Todos los negocios')->color('success'),
                Stat::make('Pedidos hoy', Order::whereDate('created_at', today())->count()),
                Stat::make('Negocios', Business::count()),
            ];
        }

        $ownedOrders = Order::whereHas('business', fn ($query) => $query->where('user_id', $user->id));

        return [
            Stat::make('Ventas de hoy', '$' . number_format(
                (clone $ownedOrders)->where('status', 'Entregado')->whereDate('created_at', today())->sum('total'),
                2
            ))
                ->description('Monto total recaudado')
                ->color('success'),
            Stat::make('Pedidos pendientes', (clone $ownedOrders)->where('status', 'Pendiente')->count())
                ->description('De tus negocios')->color('warning'),
            Stat::make('Mis negocios', Business::where('user_id', $user->id)->count()),
        ];
    }
}
