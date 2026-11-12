<?php

namespace Tests\Feature;

use App\Livewire\OrderTracking;
use App\Livewire\QuickOrder;
use App\Livewire\RiderPanel;
use App\Models\Business;
use App\Models\Order;
use App\Models\Product;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MapDisabledTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_form_keeps_the_address_field_without_map_or_gps_controls(): void
    {
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id]);

        Livewire::actingAs(User::factory()->customer()->create())
            ->test(QuickOrder::class)
            ->set('selectedBusiness', $business->id)
            ->call('toggleProduct', $product->id)
            ->assertSee('DIRECCIÓN DE ENTREGA')
            ->assertDontSee('delivery-map')
            ->assertDontSee('Usar mi ubicación');
    }

    public function test_rider_panel_shows_the_delivery_address_without_map_or_gps_controls(): void
    {
        $rider = Rider::factory()->create();
        $order = Order::factory()->create([
            'rider_id' => $rider->id,
            'status' => 'En Camino',
            'address' => 'Dirección escrita para entregar',
        ]);

        Livewire::actingAs($rider->user)
            ->test(RiderPanel::class)
            ->assertSee($order->address)
            ->assertSee('Entregado')
            ->assertDontSee('Compartir GPS')
            ->assertDontSee('Ver Mapa');
    }

    public function test_customer_tracking_keeps_order_status_and_address_without_map(): void
    {
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'En Camino',
            'address' => 'Calle del pedido sin mapa',
        ]);

        Livewire::actingAs($customer)
            ->test(OrderTracking::class, ['id' => $order->id])
            ->assertSee('En Camino')
            ->assertSee($order->address)
            ->assertDontSee('order-tracking-map');
    }
}
