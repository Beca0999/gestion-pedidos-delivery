<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Order;
use App\Models\Product;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_orders_have_the_expected_burger_and_fries_association(): void
    {
        Artisan::call('db:seed');
        Artisan::call('db:seed');

        $burgerBusinessId = Business::where('name', 'Hamburguesas Running')->value('id');
        $pizzaBusinessId = Business::where('name', 'Pizzas Running')->value('id');
        $burgerId = Product::where('business_id', $burgerBusinessId)
            ->where('name', 'Hamburguesa')
            ->value('id');
        $friesId = Product::where('business_id', $burgerBusinessId)
            ->where('name', 'Papas')
            ->value('id');
        $pizzaId = Product::where('business_id', $pizzaBusinessId)
            ->where('name', 'Pizza')
            ->value('id');
        $garlicBreadId = Product::where('business_id', $pizzaBusinessId)
            ->where('name', 'Pan de ajo')
            ->value('id');

        $burgerOrders = DB::table('order_product')
            ->where('product_id', $burgerId)
            ->count();
        $burgerOrdersWithFries = DB::table('order_product as burger')
            ->join('order_product as fries', 'burger.order_id', '=', 'fries.order_id')
            ->where('burger.product_id', $burgerId)
            ->where('fries.product_id', $friesId)
            ->count();

        $this->assertSame(50, $burgerOrders);
        $this->assertSame(30, $burgerOrdersWithFries);
        $this->assertSame(0.6, $burgerOrdersWithFries / $burgerOrders);
        $pizzaOrders = DB::table('order_product')->where('product_id', $pizzaId)->count();
        $pizzaOrdersWithGarlicBread = DB::table('order_product as pizza')
            ->join('order_product as side', 'pizza.order_id', '=', 'side.order_id')
            ->where('pizza.product_id', $pizzaId)
            ->where('side.product_id', $garlicBreadId)
            ->count();
        $this->assertSame(50, $pizzaOrders);
        $this->assertSame(30, $pizzaOrdersWithGarlicBread);
        $this->assertSame(0.6, $pizzaOrdersWithGarlicBread / $pizzaOrders);
        $this->assertSame(152, Order::count());
        $this->assertSame(23, User::count());
        $this->assertSame(15, Business::count());
        $this->assertSame(1, Rider::count());
        $this->assertSame(58, Product::count());
        $this->assertSame(15, DB::table('orders')->whereNotNull('business_id')->distinct('business_id')->count('business_id'));
        $this->assertSame(15, DB::table('orders')->whereNotNull('business_id')->distinct('business_id')->count('business_id'));
        $this->assertSame(52, Order::where('email', 'like', 'recommendation-seed-%')->count());
    }
}
