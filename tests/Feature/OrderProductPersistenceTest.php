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

class OrderProductPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_orders_persist_product_ids_quantities_and_server_prices(): void
    {
        $business = Business::factory()->create(['delivery_price' => 10]);
        $product = Product::factory()->create([
            'business_id' => $business->id,
            'price' => 100,
        ]);
        $customer = User::factory()->customer()->create();

        Livewire::actingAs($customer)->test(QuickOrder::class)
            ->set('selectedBusiness', $business->id)
            ->call('toggleProduct', $product->id)
            ->set('selectedProducts.0.price', 0)
            ->set('customer_name', 'Cliente de prueba')
            ->set('phone', '5551234567')
            ->set('address', 'Calle de prueba')
            ->set('deliveryLatitude', 19.432608)
            ->set('deliveryLongitude', -99.133209)
            ->call('saveOrder')
            ->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();

        $this->assertSame($customer->id, $order->customer_id);
        $this->assertSame(110.0, (float) $order->total);
        $this->assertDatabaseHas('order_product', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100,
        ]);
    }
}
