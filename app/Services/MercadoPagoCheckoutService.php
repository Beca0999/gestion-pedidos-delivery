<?php

namespace App\Services;

use App\Exceptions\MercadoPagoCheckoutConfigurationException;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use RuntimeException;

class MercadoPagoCheckoutService
{
    public function createPreference(Order $order): string
    {
        $accessToken = config('services.mercadopago.access_token');

        if (! is_string($accessToken) || trim($accessToken) === '') {
            throw new RuntimeException('MercadoPago access token is not configured.');
        }

        MercadoPagoConfig::setAccessToken($accessToken);

        $request = $this->buildPreferencePayload($order);

        if ($request['items'] === []) {
            throw new RuntimeException('Cannot create a checkout preference without order items.');
        }

        $options = new RequestOptions;
        $options->setCustomHeaders(["X-Idempotency-Key: delivery-order-{$order->id}"]);

        $preference = (new PreferenceClient)->create($request, $options);
        $checkoutUrl = config('services.mercadopago.sandbox')
            ? $preference->sandbox_init_point
            : $preference->init_point;

        if (! is_string($checkoutUrl) || ! $this->isMercadoPagoUrl($checkoutUrl)) {
            throw new RuntimeException('MercadoPago did not return a valid checkout URL.');
        }

        DB::table('orders')->where('id', $order->id)->update([
            'payment_id' => $preference->id,
            'updated_at' => now(),
        ]);

        return $checkoutUrl;
    }

    public function buildPreferencePayload(Order $order): array
    {
        $order->loadMissing('products');

        $request = [
            'external_reference' => (string) $order->id,
            'items' => $order->products->map(fn ($product) => [
                'title' => $product->name,
                'quantity' => (int) $product->pivot->quantity,
                'unit_price' => (float) $product->pivot->unit_price,
                'currency_id' => 'MXN',
            ])->all(),
            'shipments' => [
                'cost' => max(
                    0,
                    (float) $order->total - $order->products->sum(
                        fn ($product) => (float) $product->pivot->unit_price * (int) $product->pivot->quantity
                    ),
                ),
            ],
        ];

        if (config('app.env') === 'local') {
            return $request;
        }

        $this->assertPublicHttpsAppUrl();
        $trackingUrl = rtrim((string) config('app.url'), '/').route('order.tracking', $order->id, false);

        return [
            ...$request,
            'back_urls' => [
                'success' => $trackingUrl,
                'pending' => $trackingUrl,
                'failure' => $trackingUrl,
            ],
            'auto_return' => 'approved',
        ];
    }

    private function assertPublicHttpsAppUrl(): void
    {
        $parts = parse_url((string) config('app.url'));
        $host = strtolower(rtrim((string) ($parts['host'] ?? ''), '.'));
        $ip = filter_var($host, FILTER_VALIDATE_IP);
        $isPublicIp = $ip !== false
            && filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
        $isPublicHostname = $ip === false
            && filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false
            && str_contains($host, '.')
            && ! collect(['.localhost', '.local', '.test', '.invalid', '.example'])
                ->contains(fn (string $suffix) => str_ends_with($host, $suffix));

        if (($parts['scheme'] ?? '') !== 'https' || (! $isPublicIp && ! $isPublicHostname)) {
            throw new MercadoPagoCheckoutConfigurationException(
                'El checkout de Mercado Pago requiere APP_URL con HTTPS y una dirección pública. Usa un dominio o túnel HTTPS y vuelve a intentar el pago.',
            );
        }
    }

    private function isMercadoPagoUrl(string $url): bool
    {
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');

        return ($parts['scheme'] ?? '') === 'https'
            && filter_var($url, FILTER_VALIDATE_URL) !== false
            && preg_match('/^(www\.|sandbox\.)?mercadopago\.com(\.[a-z]{2,3})?$/', $host) === 1;
    }
}
