<?php

namespace Tests\Feature;

use App\Filament\Resources\BusinessResource\Pages\CreateBusiness;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Business;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BusinessOwnershipSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_are_grouped_by_business_in_the_menu_list(): void
    {
        $owner = User::factory()->owner()->create();
        $pizzaBusiness = Business::factory()->create([
            'name' => 'Pizzas Running',
            'user_id' => $owner->id,
        ]);
        Product::factory()->create([
            'name' => 'Pizza Margarita',
            'business_id' => $pizzaBusiness->id,
        ]);

        $component = Livewire::actingAs($owner)->test(ListProducts::class);

        $this->assertSame(
            'business.name',
            $component->instance()->getTable()->getDefaultGroup()->getId(),
        );
        $component->assertSee('Pizzas Running')->assertSee('Pizza Margarita');
    }

    public function test_owner_can_add_products_to_each_of_their_businesses(): void
    {
        $owner = User::factory()->owner()->create();
        Business::factory()->create([
            'name' => 'Pizzas Running',
            'user_id' => $owner->id,
        ]);
        $otherBusiness = Business::factory()->create([
            'name' => 'Hamburguesas Running',
            'user_id' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->get('/admin/products')
            ->assertOk()
            ->assertSee('Mi Menú');

        Livewire::actingAs($owner)
            ->test(CreateProduct::class)
            ->fillForm([
                'name' => 'Pizza de prueba',
                'price' => 180,
                'business_id' => $otherBusiness->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'name' => 'Pizza de prueba',
            'business_id' => $otherBusiness->id,
        ]);
    }

    public function test_owner_cannot_add_a_product_to_another_owners_business(): void
    {
        $owner = User::factory()->owner()->create();
        $otherOwner = User::factory()->owner()->create();
        $otherBusiness = Business::factory()->create(['user_id' => $otherOwner->id]);

        Livewire::actingAs($owner)
            ->test(CreateProduct::class)
            ->fillForm([
                'name' => 'Producto no autorizado',
                'price' => 100,
                'business_id' => $otherBusiness->id,
            ])
            ->call('create')
            ->assertForbidden();

        $this->assertDatabaseMissing('products', ['name' => 'Producto no autorizado']);
    }

    public function test_owner_cannot_assign_a_created_business_to_another_user(): void
    {
        $owner = User::factory()->owner()->create();
        $otherOwner = User::factory()->owner()->create();

        Livewire::actingAs($owner)
            ->test(CreateBusiness::class)
            ->fillForm([
                'name' => 'Negocio propio',
                'delivery_price' => 25,
                'is_open' => true,
                'user_id' => $otherOwner->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('businesses', [
            'name' => 'Negocio propio',
            'user_id' => $owner->id,
        ]);
        $this->assertDatabaseMissing('businesses', [
            'name' => 'Negocio propio',
            'user_id' => $otherOwner->id,
        ]);
    }

    public function test_owner_cannot_transfer_an_existing_business_to_another_user(): void
    {
        $owner = User::factory()->owner()->create();
        $otherOwner = User::factory()->owner()->create();
        $business = Business::factory()->create(['user_id' => $owner->id]);

        Livewire::actingAs($owner)
            ->test(\App\Filament\Resources\BusinessResource\Pages\EditBusiness::class, [
                'record' => $business->getRouteKey(),
            ])
            ->fillForm(['user_id' => $otherOwner->id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($owner->id, $business->fresh()->user_id);
    }
}
