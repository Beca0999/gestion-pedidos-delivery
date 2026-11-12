<?php

namespace Tests\Feature;

use App\Exceptions\MercadoPagoCheckoutConfigurationException;
use App\Livewire\QuickOrder;
use App\Models\Business;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\MercadoPagoCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MercadoPagoCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_card_payment_without_credentials_shows_a_friendly_error_and_does_not_create_an_order(): void
    {
        $this->assertTrue(class_exists(\MercadoPago\MercadoPagoConfig::class));
        $this->assertTrue(class_exists(\MercadoPago\Client\Preference\PreferenceClient::class));

        config(['services.mercadopago.access_token' => null]);
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id]);
        $customer = User::factory()->customer()->create();

        Livewire::actingAs($customer)->test(QuickOrder::class)
            ->set('selectedBusiness', $business->id)
            ->call('toggleProduct', $product->id)
            ->set('customer_name', 'Cliente de prueba')
            ->set('phone', '5551234567')
            ->set('address', 'Calle de prueba')
            ->set('payment_method', 'Tarjeta')
            ->call('saveOrder')
            ->assertHasErrors(['payment'])
            ->assertSee('El pago con tarjeta no está configurado');

        $this->assertSame(0, Order::count());
    }

    public function test_error_pages_do_not_expose_exception_details(): void
    {
        $this->get('/missing-page')
            ->assertNotFound()
            ->assertSee('Página no encontrada')
            ->assertDontSee('SQLSTATE');
    }

    public function test_local_checkout_allows_http_without_return_urls(): void
    {
        config([
            'app.env' => 'local',
            'app.url' => 'http://127.0.0.1:8000',
        ]);
        $order = Order::factory()->create();

        $payload = app(MercadoPagoCheckoutService::class)->buildPreferencePayload($order);

        $this->assertTrue((bool) config('services.mercadopago.sandbox'));
        $this->assertArrayNotHasKey('back_urls', $payload);
        $this->assertArrayNotHasKey('auto_return', $payload);
        $this->assertSame((string) $order->id, $payload['external_reference']);
    }

    public function test_production_checkout_includes_return_urls_when_app_url_is_public_https(): void
    {
        config([
            'app.env' => 'production',
            'app.url' => 'https://shop.example.com',
        ]);
        $order = Order::factory()->create();

        $payload = app(MercadoPagoCheckoutService::class)->buildPreferencePayload($order);

        $this->assertSame('approved', $payload['auto_return']);
        $this->assertStringStartsWith('https://shop.example.com/', $payload['back_urls']['success']);
    }

    public function test_production_environment_hides_checkout_configuration_details(): void
    {
        config([
            'services.mercadopago.access_token' => 'test-token',
            'app.env' => 'production',
            'app.url' => 'http://localhost',
        ]);
        $business = Business::factory()->create();
        $product = Product::factory()->create(['business_id' => $business->id]);
        $customer = User::factory()->customer()->create();

        Livewire::actingAs($customer)->test(QuickOrder::class)
            ->set('selectedBusiness', $business->id)
            ->call('toggleProduct', $product->id)
            ->set('customer_name', 'Cliente de prueba')
            ->set('phone', '5551234567')
            ->set('address', 'Calle de prueba')
            ->set('payment_method', 'Tarjeta')
            ->call('saveOrder')
            ->assertHasErrors(['payment'])
            ->assertSee('No pudimos iniciar el pago en este momento')
            ->assertDontSee('APP_URL')
            ->assertDontSee('HTTPS');

        $this->assertSame(1, Order::count());
    }

    public function test_checkout_service_rejects_non_public_return_urls_before_calling_mercado_pago(): void
    {
        config([
            'services.mercadopago.access_token' => 'test-token',
            'app.url' => 'http://127.0.0.1:8000',
        ]);
        $order = Order::factory()->create();

        $this->expectException(MercadoPagoCheckoutConfigurationException::class);
        app(MercadoPagoCheckoutService::class)->createPreference($order);
    }
}
