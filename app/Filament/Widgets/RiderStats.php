<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RiderStats extends BaseWidget
{
    public static function canView(): bool
    {
        // Solo se muestra si el usuario tiene un perfil de repartidor
        return auth()->check() && auth()->user()->rider !== null;
    }

    protected function getStats(): array
    {
        $riderId = auth()->user()->rider->id;

        return [
            Stat::make('Mis Entregas Pendientes', 
                Order::where('rider_id', $riderId)
                     ->where('status', '!=', 'Entregado')
                     ->count())
                ->description('Pedidos que tienes asignados para entregar')
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning'),
        ];
    }
}
