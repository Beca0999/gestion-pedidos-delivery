<?php

namespace Tests\Feature;

use App\Livewire\RiderPanel;
use App\Models\Business;
use App\Models\Order;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RiderLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_rider_can_share_and_stop_gps_while_an_order_is_in_transit(): void
    {
        $rider = Rider::factory()->create();
        Order::factory()->create([
            'rider_id' => $rider->id,
            'status' => 'En Camino',
        ]);

        Livewire::actingAs($rider->user)->test(RiderPanel::class)
            ->call('updateLocation', 19.432608, -99.133209)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('riders', [
            'id' => $rider->id,
            'latitude' => 19.432608,
            'longitude' => -99.133209,
        ]);
        $this->assertNotNull($rider->fresh()->location_updated_at);

        Livewire::actingAs($rider->user)->test(RiderPanel::class)
            ->call('stopSharingLocation');

        $this->assertDatabaseHas('riders', [
            'id' => $rider->id,
            'latitude' => null,
            'longitude' => null,
            'location_updated_at' => null,
        ]);
    }

    public function test_rider_cannot_share_gps_without_an_order_in_transit(): void
    {
        $rider = Rider::factory()->create();

        Livewire::actingAs($rider->user)->test(RiderPanel::class)
            ->call('updateLocation', 19.432608, -99.133209)
            ->assertHasErrors(['location']);

        $this->assertNull($rider->fresh()->latitude);
    }

    public function test_customer_tracking_page_shows_delivery_and_recent_rider_gps(): void
    {
        $customer = User::factory()->customer()->create();
        $rider = Rider::factory()->create([
            'latitude' => 19.433,
            'longitude' => -99.134,
            'location_updated_at' => now(),
        ]);
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'business_id' => Business::factory(),
            'rider_id' => $rider->id,
            'status' => 'En Camino',
            'delivery_latitude' => 19.432608,
            'delivery_longitude' => -99.133209,
        ]);

        $this->actingAs($customer)
            ->get(route('order.tracking', $order->id))
            ->assertOk()
            ->assertSee('Mapa del pedido')
            ->assertSee('GPS actualizado');
    }
}
