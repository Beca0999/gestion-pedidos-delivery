<?php

namespace Tests\Feature;

use App\Livewire\QuickOrder;
use App\Models\Business;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerRequiredForOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_browse_and_build_a_cart_but_cannot_create_an_order(): void
    {
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id]);

        Livewire::test(QuickOrder::class)
            ->set('selectedBusiness', $business->id)
            ->call('toggleProduct', $product->id)
            ->set('customer_name', 'Guest')
            ->set('phone', '5551234567')
            ->set('address', 'Calle de prueba')
            ->set('deliveryLatitude', 19.432608)
            ->set('deliveryLongitude', -99.133209)
            ->call('saveOrder')
            ->assertHasErrors(['auth'])
            ->assertSee('Crear cuenta de cliente')
            ->assertSee('inicia sesión');

        $this->assertSame(0, Order::count());
    }

    public function test_only_a_customer_account_can_create_an_order(): void
    {
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id]);
        $rider = User::factory()->rider()->create();

        Livewire::actingAs($rider)->test(QuickOrder::class)
            ->set('selectedBusiness', $business->id)
            ->call('toggleProduct', $product->id)
            ->set('customer_name', 'Repartidor')
            ->set('phone', '5551234567')
            ->set('address', 'Calle de prueba')
            ->set('deliveryLatitude', 19.432608)
            ->set('deliveryLongitude', -99.133209)
            ->call('saveOrder')
            ->assertHasErrors(['auth'])
            ->assertSee('Esta cuenta no puede realizar pedidos');

        $this->assertSame(0, Order::count());
    }

    public function test_customer_can_create_an_order_without_email_verification_while_local_testing_is_enabled(): void
    {
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id]);
        $customer = User::factory()->unverified()->customer()->create();

        Livewire::actingAs($customer)->test(QuickOrder::class)
            ->set('selectedBusiness', $business->id)
            ->call('toggleProduct', $product->id)
            ->set('customer_name', 'Cliente sin verificar')
            ->set('phone', '5551234567')
            ->set('address', 'Calle de prueba')
            ->set('deliveryLatitude', 19.432608)
            ->set('deliveryLongitude', -99.133209)
            ->call('saveOrder')
            ->assertHasNoErrors();

        $this->assertSame(1, Order::count());
        $this->assertEquals(19.432608, (float) Order::first()->delivery_latitude);
        $this->assertEquals(-99.133209, (float) Order::first()->delivery_longitude);
    }

    public function test_customer_cannot_track_another_customers_order(): void
    {
        $order = Order::factory()->create([
            'customer_id' => User::factory()->customer(),
        ]);
        $otherCustomer = User::factory()->customer()->create();

        $this->actingAs($otherCustomer)
            ->get(route('order.tracking', $order->id))
            ->assertNotFound();
    }

    public function test_guests_do_not_see_active_orders_without_a_customer_account(): void
    {
        Order::factory()->create([
            'customer_id' => null,
            'status' => 'Pendiente',
            'customer_name' => 'Pedido histórico sin dueño',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('VER PEDIDOS')
            ->assertDontSee('Pedido histórico sin dueño');
    }

    public function test_customers_see_only_their_own_active_orders(): void
    {
        $customer = User::factory()->customer()->create();
        $otherCustomer = User::factory()->customer()->create();
        $ownOrder = Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_name' => 'Pedido propio',
            'status' => 'Pendiente',
        ]);
        $otherOrder = Order::factory()->create([
            'customer_id' => $otherCustomer->id,
            'customer_name' => 'Pedido ajeno',
            'status' => 'Pendiente',
        ]);

        $this->actingAs($customer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('VER PEDIDOS')
            ->assertSee(route('order.tracking', $ownOrder->id))
            ->assertDontSee(route('order.tracking', $otherOrder->id));
    }
}
