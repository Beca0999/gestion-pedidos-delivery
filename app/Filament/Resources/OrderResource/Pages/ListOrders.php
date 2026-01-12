<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // Esta función se ejecuta cada vez que la página se refresca
    public function boot()
    {
        $user = auth()->user();
        
        // Buscamos si hay algún pedido "Pendiente" que haya llegado en los últimos 30 segundos
        $newOrders = \App\Models\Order::where('status', 'Pendiente')
            ->where('created_at', '>=', now()->subSeconds(30));

        // Si el usuario es dueño de local, solo buscamos sus pedidos
        if ($user->id !== 1) {
            $newOrders->whereHas('business', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        if ($newOrders->exists()) {
            Notification::make()
                ->title('¡Nuevo Pedido Recibido!')
                ->body('Tienes una orden pendiente por preparar.')
                ->success()
                ->send();

            // Inyectamos un pequeño script de JS para el sonido
            $this->dispatch('play-notification-sound');
        }
    }
}
