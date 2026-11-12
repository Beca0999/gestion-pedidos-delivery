<?php

namespace Tests\Feature;

use App\Livewire\QuickOrder;
use App\Models\Order;
use App\Models\Business;
use App\Models\Product;
use App\Models\User;
use App\Services\BusinessRecommendationService;
use App\Services\ProductRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ProductRecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_the_highest_confidence_recommendations_across_businesses(): void
    {
        $owner = User::factory()->owner()->create();
        $business = Business::factory()->create(['user_id' => $owner->id]);
        $source = Product::factory()->create(['business_id' => $business->id]);
        $recommended = Product::factory()->create(['business_id' => $business->id]);
        $otherBusinessProduct = Product::factory()->create();

        DB::table('product_recommendations')->insert([
            [
                'product_id' => $source->id,
                'recommended_product_id' => $recommended->id,
                'support' => 0.3,
                'confidence' => 0.6,
                'lift' => 1.5,
            ],
            [
                'product_id' => $source->id,
                'recommended_product_id' => $otherBusinessProduct->id,
                'support' => 0.2,
                'confidence' => 0.9,
                'lift' => 1.2,
            ],
        ]);

        $results = app(ProductRecommendationService::class)->forProduct($source);

        $this->assertSame([$otherBusinessProduct->id, $recommended->id], $results->modelKeys());
        $this->assertSame($otherBusinessProduct->business_id, $results->first()->business_id);

        $businessRecommendations = app(ProductRecommendationService::class)
            ->forBusiness($business);
        $this->assertSame([$recommended->id], $businessRecommendations->modelKeys());

        $this->getJson(route('api.products.recommendations', $source))
            ->assertOk()
            ->assertJsonPath('recommendations.0.business_name', $otherBusinessProduct->business->name);
    }

    public function test_cart_recommendations_appear_below_the_menu_after_adding_the_source_product(): void
    {
        $business = Business::factory()->create(['name' => 'Pizzería Running']);
        $source = Product::factory()->create([
            'business_id' => $business->id,
            'name' => 'Pizza',
        ]);
        $recommended = Product::factory()->create([
            'business_id' => $business->id,
            'name' => 'Papas',
        ]);

        DB::table('product_recommendations')->insert([
            'product_id' => $source->id,
            'recommended_product_id' => $recommended->id,
            'support' => 0.3,
            'confidence' => 0.6,
            'lift' => 1.5,
        ]);

        Livewire::test(QuickOrder::class)
            ->set('selectedBusiness', $business->id)
            ->assertCount('businessProductRecommendations', 0)
            ->assertDontSee('Sugerencias para ti')
            ->call('toggleProduct', $source->id)
            ->assertCount('businessProductRecommendations', 1)
            ->assertSee('Sugerencias para ti')
            ->assertSee('Si ya agregaste Pizza,')
            ->assertSee('quizá también te guste Papas')
            ->assertDontSee('Apriori')
            ->assertDontSee('Quienes pidieron aquí también compraron')
            ->assertSee('Agregar')
            ->assertSeeInOrder(['Toca para agregar', 'Sugerencias para ti']);
    }

    public function test_recommendations_are_hidden_again_when_the_cart_is_empty(): void
    {
        $business = Business::factory()->create(['name' => 'Negocio recién agregado']);
        $source = Product::factory()->create(['business_id' => $business->id]);
        $recommended = Product::factory()->create(['business_id' => $business->id]);

        DB::table('product_recommendations')->insert([
            'product_id' => $source->id,
            'recommended_product_id' => $recommended->id,
            'support' => 0.3,
            'confidence' => 0.6,
            'lift' => 1.5,
        ]);

        Livewire::test(QuickOrder::class)
            ->set('selectedBusiness', $business->id)
            ->assertDontSee('Sugerencias para ti')
            ->call('toggleProduct', $source->id)
            ->assertSee('Sugerencias para ti')
            ->call('removeProduct', 0)
            ->assertCount('businessProductRecommendations', 0)
            ->assertDontSee('Sugerencias para ti');
    }

    public function test_it_returns_restaurants_related_to_the_selected_business(): void
    {
        $business = Business::factory()->create();
        $recommended = Business::factory()->create();
        $other = Business::factory()->create();

        DB::table('business_recommendations')->insert([
            [
                'business_id' => $business->id,
                'recommended_business_id' => $recommended->id,
                'support' => 0.3,
                'confidence' => 0.6,
                'lift' => 1.5,
            ],
            [
                'business_id' => $business->id,
                'recommended_business_id' => $other->id,
                'support' => 0.2,
                'confidence' => 0.9,
                'lift' => 1.2,
            ],
        ]);

        $results = app(BusinessRecommendationService::class)->forBusiness($business);

        $this->assertSame([$other->id, $recommended->id], $results->modelKeys());

        $this->getJson(route('api.businesses.recommendations', $business))
            ->assertOk()
            ->assertJsonPath('recommendations.0.name', $other->name);
    }

    public function test_storefront_shows_up_to_three_personal_or_popular_business_suggestions(): void
    {
        $source = Business::factory()->create(['name' => 'Restaurante anterior']);
        $recommended = Business::factory()->create(['name' => 'Sugerencia personalizada']);
        $customer = User::factory()->customer()->create();
        $order = \App\Models\Order::factory()->create([
            'customer_id' => $customer->id,
            'business_id' => $source->id,
            'status' => 'Entregado',
        ]);
        DB::table('business_recommendations')->insert([
            'business_id' => $source->id,
            'recommended_business_id' => $recommended->id,
            'support' => 0.2,
            'confidence' => 0.8,
            'lift' => 1.4,
        ]);

        Livewire::actingAs($customer)
            ->test(QuickOrder::class)
            ->assertSee('Recomendados para ti')
            ->assertSee('Sugerencia personalizada');

        auth()->logout();
        Livewire::test(QuickOrder::class)
            ->assertSee('Negocios populares')
            ->assertSee('Restaurante anterior');
    }

    public function test_storefront_recommendations_are_available_after_selecting_a_business_in_the_url(): void
    {
        $business = Business::factory()->create(['name' => 'Pizzería sugerida']);

        $this->get(route('business.menu', $business))
            ->assertOk()
            ->assertSee('Pizzería sugerida')
            ->assertSee('EL MENÚ');
    }

    public function test_order_tracking_shows_other_businesses_after_a_cash_order(): void
    {
        $business = Business::factory()->create(['name' => 'Negocio de origen']);
        $recommendedBusiness = Business::factory()->create(['name' => 'Negocio sugerido']);
        $customer = User::factory()->customer()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'business_id' => $business->id,
            'payment_method' => 'Efectivo',
        ]);
        DB::table('business_recommendations')->insert([
            'business_id' => $business->id,
            'recommended_business_id' => $recommendedBusiness->id,
            'support' => 0.2,
            'confidence' => 0.8,
            'lift' => 1.4,
        ]);

        $this->actingAs($customer)
            ->get(route('order.tracking', $order->id))
            ->assertOk()
            ->assertSee('También podrías pedir en')
            ->assertSee('Negocio sugerido')
            ->assertSee(route('business.menu', $recommendedBusiness));
    }

    public function test_switching_to_a_recommended_restaurant_does_not_discard_a_nonempty_basket(): void
    {
        $business = Business::factory()->create();
        $recommended = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id]);
        $recommendation = Product::factory()->create(['business_id' => $recommended->id]);

        $component = Livewire::test(QuickOrder::class)
            ->set('selectedBusiness', $business->id)
            ->call('toggleProduct', $product->id);

        $component->call('browseRecommendedBusiness', $recommended->id)
            ->assertHasErrors(['business-switch'])
            ->assertSet('selectedBusiness', $business->id)
            ->assertCount('selectedProducts', 1);

        $component->call('removeProduct', 0)
            ->call('browseRecommendedBusiness', $recommended->id)
            ->assertHasNoErrors()
            ->assertSet('selectedBusiness', $recommended->id)
            ->assertCount('selectedProducts', 0);

        $this->assertDatabaseHas('products', ['id' => $recommendation->id]);
    }

    public function test_menu_shows_same_business_product_and_cross_business_restaurant_recommendations(): void
    {
        $business = Business::factory()->create(['name' => 'Restaurante de origen']);
        $recommendedBusiness = Business::factory()->create(['name' => 'Restaurante sugerido']);
        $source = Product::factory()->create(['business_id' => $business->id]);
        $recommendedProduct = Product::factory()->create([
            'business_id' => $recommendedBusiness->id,
            'name' => 'Producto relacionado',
        ]);
        $sameBusinessProduct = Product::factory()->create([
            'business_id' => $business->id,
            'name' => 'Producto de este restaurante',
        ]);

        DB::table('product_recommendations')->insert([
            [
                'product_id' => $source->id,
                'recommended_product_id' => $recommendedProduct->id,
                'support' => 0.2,
                'confidence' => 0.5,
                'lift' => 1.1,
            ],
            [
                'product_id' => $source->id,
                'recommended_product_id' => $sameBusinessProduct->id,
                'support' => 0.2,
                'confidence' => 0.5,
                'lift' => 1.1,
            ],
        ]);
        DB::table('business_recommendations')->insert([
            'business_id' => $business->id,
            'recommended_business_id' => $recommendedBusiness->id,
            'support' => 0.2,
            'confidence' => 0.5,
            'lift' => 1.1,
        ]);

        Livewire::test(QuickOrder::class)
            ->set('selectedBusiness', $business->id)
            ->call('toggleProduct', $source->id)
            ->assertSee('Producto de este restaurante')
            ->assertDontSee('Producto relacionado')
            ->assertSee('Restaurante sugerido')
            ->assertSee('Ver menú')
            ->assertSee('También visitan estos restaurantes');
    }
}
