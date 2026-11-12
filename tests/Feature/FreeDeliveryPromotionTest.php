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

class FreeDeliveryPromotionTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_is_free_when_one_restaurant_order_reaches_three_hundred_pesos(): void
    {
        $business = Business::factory()->create(['delivery_price' => 40]);
        $firstProduct = Product::factory()->create([
            'business_id' => $business->id,
            'price' => 150,
        ]);
        $secondProduct = Product::factory()->create([
            'business_id' => $business->id,
            'price' => 150,
        ]);
        $customer = User::factory()->customer()->create();

        Livewire::actingAs($customer)->test(QuickOrder::class)
            ->set('selectedBusiness', $business->id)
            ->call('toggleProduct', $firstProduct->id)
            ->call('toggleProduct', $secondProduct->id)
            ->assertSet('subtotal', 300)
            ->assertSet('delivery_cost', 0)
            ->assertSet('total', 300)
            ->set('customer_name', $customer->name)
            ->set('phone', '5551234567')
            ->set('address', 'Calle de prueba')
            ->set('deliveryLatitude', 19.432608)
            ->set('deliveryLongitude', -99.133209)
            ->set('payment_method', 'Efectivo')
            ->call('saveOrder')
            ->assertHasNoErrors()
            ->assertRedirect(route('order.tracking', Order::query()->latest('id')->value('id')));

        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertSame(300.0, (float) $order->total);
    }

    public function test_delivery_is_charged_below_three_hundred_pesos(): void
    {
        $business = Business::factory()->create(['delivery_price' => 40]);
        $product = Product::factory()->create([
            'business_id' => $business->id,
            'price' => 299,
        ]);
        $customer = User::factory()->customer()->create();

        Livewire::actingAs($customer)->test(QuickOrder::class)
            ->set('selectedBusiness', $business->id)
            ->call('toggleProduct', $product->id)
            ->assertSet('subtotal', 299)
            ->assertSet('delivery_cost', 40)
            ->assertSet('total', 339);
    }

    public function test_business_card_hides_the_threshold_and_repeated_products_show_a_quantity(): void
    {
        $business = Business::factory()->create(['delivery_price' => 40]);
        $product = Product::factory()->create([
            'business_id' => $business->id,
            'price' => 100,
        ]);

        Livewire::test(QuickOrder::class)
            ->set('selectedBusiness', null)
            ->assertDontSee('Envío gratis desde $300')
            ->set('selectedBusiness', $business->id)
            ->assertDontSee('Envío gratis desde $300')
            ->call('toggleProduct', $product->id)
            ->call('toggleProduct', $product->id)
            ->assertCount('selectedProducts', 1)
            ->assertSet('selectedProducts.0.qty', 2)
            ->assertSee('200.00')
            ->assertSee('$40.00')
            ->assertSee('Agrega $100.00 para obtener envío gratis');
    }

    public function test_cart_quantity_controls_increment_decrement_and_remove_at_zero(): void
    {
        $business = Business::factory()->create(['delivery_price' => 40]);
        $product = Product::factory()->create([
            'business_id' => $business->id,
            'price' => 100,
        ]);

        Livewire::test(QuickOrder::class)
            ->set('selectedBusiness', $business->id)
            ->call('toggleProduct', $product->id)
            ->call('changeQuantity', 0, 1)
            ->assertSet('selectedProducts.0.qty', 2)
            ->assertSet('subtotal', 200)
            ->call('changeQuantity', 0, -1)
            ->assertSet('selectedProducts.0.qty', 1)
            ->call('changeQuantity', 0, -1)
            ->assertCount('selectedProducts', 0)
            ->assertSet('subtotal', 0);
    }
}
