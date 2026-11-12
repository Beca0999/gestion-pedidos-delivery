<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Order;
use App\Models\Product;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedUser('Administrador', 'admin@example.test', 'admin');
        $owner = $this->seedUser('Dueño de prueba', 'owner@example.test', 'owner');
        $riderUser = $this->seedUser('Repartidor de prueba', 'rider@example.test', 'rider');
        $customers = collect();

        for ($index = 0; $index < 20; $index++) {
            $customers->push($this->seedUser(
                $index === 0 ? 'Cliente de prueba' : "Cliente demo {$index}",
                $index === 0 ? 'customer@example.test' : "customer{$index}@example.test",
                'customer',
            ));
        }

        Rider::updateOrCreate(
            ['user_id' => $riderUser->id],
            ['name' => $riderUser->name, 'phone' => '5550000001', 'is_available' => true],
        );

        $burgerBusiness = Business::updateOrCreate(
            ['user_id' => $owner->id, 'name' => 'Hamburguesas Running'],
            ['delivery_price' => 35, 'is_open' => true],
        );
        $pizzaBusiness = Business::updateOrCreate(
            ['user_id' => $owner->id, 'name' => 'Pizzas Running'],
            ['delivery_price' => 40, 'is_open' => true],
        );

        $restaurants = collect([$burgerBusiness, $pizzaBusiness]);
        $restaurantMenus = [
            [
                'name' => 'Tacos El Güero',
                'delivery_price' => 28,
                'menu' => [
                    ['name' => 'Tacos al pastor', 'price' => 24],
                    ['name' => 'Gringas de pastor', 'price' => 68],
                    ['name' => 'Quesadilla de bistec', 'price' => 55],
                    ['name' => 'Agua de horchata', 'price' => 32],
                ],
            ],
            [
                'name' => 'Sushi Nori',
                'delivery_price' => 35,
                'menu' => [
                    ['name' => 'Rollo California', 'price' => 115],
                    ['name' => 'Rollo empanizado', 'price' => 138],
                    ['name' => 'Yakimeshi mixto', 'price' => 105],
                    ['name' => 'Gyozas de pollo', 'price' => 78],
                ],
            ],
            [
                'name' => 'Wok de Oriente',
                'delivery_price' => 32,
                'menu' => [
                    ['name' => 'Pollo teriyaki', 'price' => 145],
                    ['name' => 'Arroz frito especial', 'price' => 118],
                    ['name' => 'Fideos con verduras', 'price' => 112],
                    ['name' => 'Rollitos primavera', 'price' => 72],
                ],
            ],
            [
                'name' => 'Mariscos El Puerto',
                'delivery_price' => 42,
                'menu' => [
                    ['name' => 'Tacos de pescado', 'price' => 125],
                    ['name' => 'Coctel de camarón', 'price' => 168],
                    ['name' => 'Ceviche de la casa', 'price' => 155],
                    ['name' => 'Tostadas de atún', 'price' => 132],
                ],
            ],
            [
                'name' => 'Antojitos Doña Lupe',
                'delivery_price' => 25,
                'menu' => [
                    ['name' => 'Enchiladas verdes', 'price' => 108],
                    ['name' => 'Sopes de tinga', 'price' => 92],
                    ['name' => 'Chilaquiles rojos', 'price' => 115],
                    ['name' => 'Agua de jamaica', 'price' => 30],
                ],
            ],
            [
                'name' => 'Café Nube',
                'delivery_price' => 22,
                'menu' => [
                    ['name' => 'Panini de pavo', 'price' => 98],
                    ['name' => 'Ensalada de pollo', 'price' => 112],
                    ['name' => 'Croissant de jamón', 'price' => 75],
                    ['name' => 'Latte de vainilla', 'price' => 58],
                ],
            ],
            [
                'name' => 'Crepas Dulce Luna',
                'delivery_price' => 26,
                'menu' => [
                    ['name' => 'Crepa de Nutella', 'price' => 88],
                    ['name' => 'Crepa de fresas', 'price' => 96],
                    ['name' => 'Crepa de jamón y queso', 'price' => 102],
                    ['name' => 'Malteada de chocolate', 'price' => 72],
                ],
            ],
            [
                'name' => 'Alitas Fuego',
                'delivery_price' => 36,
                'menu' => [
                    ['name' => 'Alitas BBQ', 'price' => 145],
                    ['name' => 'Boneless búfalo', 'price' => 152],
                    ['name' => 'Papas gajo', 'price' => 68],
                    ['name' => 'Dedos de queso', 'price' => 82],
                ],
            ],
            [
                'name' => 'Ensaladas Verdes',
                'delivery_price' => 24,
                'menu' => [
                    ['name' => 'Ensalada César', 'price' => 118],
                    ['name' => 'Bowl de quinoa', 'price' => 132],
                    ['name' => 'Wrap de pollo', 'price' => 108],
                    ['name' => 'Jugo verde', 'price' => 55],
                ],
            ],
            [
                'name' => 'Cocina Vegana Raíz',
                'delivery_price' => 28,
                'menu' => [
                    ['name' => 'Bowl de lentejas', 'price' => 125],
                    ['name' => 'Tacos de setas', 'price' => 112],
                    ['name' => 'Hamburguesa de garbanzo', 'price' => 138],
                    ['name' => 'Agua de limón con chía', 'price' => 38],
                ],
            ],
            [
                'name' => 'Tortas La Estación',
                'delivery_price' => 30,
                'menu' => [
                    ['name' => 'Torta de milanesa', 'price' => 105],
                    ['name' => 'Torta de pierna', 'price' => 98],
                    ['name' => 'Torta cubana', 'price' => 138],
                    ['name' => 'Papas a la francesa', 'price' => 58],
                ],
            ],
            [
                'name' => 'Burritos Norteños',
                'delivery_price' => 34,
                'menu' => [
                    ['name' => 'Burrito de arrachera', 'price' => 142],
                    ['name' => 'Burrito de pollo', 'price' => 118],
                    ['name' => 'Burrito vegetariano', 'price' => 108],
                    ['name' => 'Frijoles charros', 'price' => 64],
                ],
            ],
            [
                'name' => 'Helados Estrella',
                'delivery_price' => 20,
                'menu' => [
                    ['name' => 'Helado de vainilla', 'price' => 48],
                    ['name' => 'Helado de chocolate', 'price' => 48],
                    ['name' => 'Banana split', 'price' => 95],
                    ['name' => 'Malteada de fresa', 'price' => 72],
                ],
            ],
        ];

        foreach ($restaurantMenus as $restaurant) {
            $business = Business::updateOrCreate(
                ['user_id' => $owner->id, 'name' => $restaurant['name']],
                ['delivery_price' => $restaurant['delivery_price'], 'is_open' => true],
            );
            $products = collect($restaurant['menu'])
                ->map(fn (array $product) => $this->seedProduct(
                    $business->id,
                    $product['name'],
                    $product['price'],
                ));
            $restaurants->push(['business' => $business, 'products' => $products]);
        }

        $burger = $this->seedProduct($burgerBusiness->id, 'Hamburguesa', 120);
        $fries = $this->seedProduct($burgerBusiness->id, 'Papas', 45);
        $burgerSoda = $this->seedProduct($burgerBusiness->id, 'Refresco', 30);
        $pizza = $this->seedProduct($pizzaBusiness->id, 'Pizza', 150);
        $garlicBread = $this->seedProduct($pizzaBusiness->id, 'Pan de ajo', 55);
        $pizzaSoda = $this->seedProduct($pizzaBusiness->id, 'Refresco', 30);

        for ($index = 0; $index < 100; $index++) {
            $isPizzaOrder = $index >= 50;
            $business = $isPizzaOrder ? $pizzaBusiness : $burgerBusiness;
            $mainProduct = $isPizzaOrder ? $pizza : $burger;
            $sideProduct = $isPizzaOrder ? $garlicBread : $fries;
            $soda = $isPizzaOrder ? $pizzaSoda : $burgerSoda;
            $items = [$mainProduct];

            if ($index % 5 < 3) {
                $items[] = $sideProduct;
            }

            if ($index % 3 === 0) {
                $items[] = $soda;
            }

            $customer = $customers[$index % $customers->count()];
            $order = Order::updateOrCreate(
                ['email' => "seed-order-{$index}@example.test"],
                [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'phone' => '555'.str_pad((string) $index, 7, '0', STR_PAD_LEFT),
                    'address' => "Dirección demo {$index}",
                    'business_id' => $business->id,
                    'status' => 'Entregado',
                    'payment_method' => 'Efectivo',
                    'payment_status' => 'Pendiente',
                    'total' => collect($items)->sum('price'),
                ],
            );

            $order->products()->sync(
                collect($items)->mapWithKeys(fn (Product $product) => [
                    $product->id => ['quantity' => 1, 'unit_price' => $product->price],
                ])->all()
            );
        }

        foreach ($customers as $customerIndex => $customer) {
            $cohort = $restaurants->slice(intdiv($customerIndex, 4) * 3, 3);

            foreach ($cohort as $restaurantIndex => $restaurant) {
                if ($restaurantIndex < 2 && intdiv($customerIndex, 4) === 0) {
                    continue;
                }

                if ($restaurant instanceof Business) {
                    $business = $restaurant;
                    $products = collect($business->id === $burgerBusiness->id
                        ? [$burger, $fries, $burgerSoda]
                        : [$pizza, $garlicBread, $pizzaSoda]);
                } else {
                    $business = $restaurant['business'];
                    $products = $restaurant['products'];
                }
                $items = $products->take(2);
                $order = Order::updateOrCreate(
                    ['email' => "recommendation-seed-{$customerIndex}-{$business->id}@example.test"],
                    [
                        'customer_id' => $customer->id,
                        'customer_name' => $customer->name,
                        'phone' => '555'.str_pad((string) ($customerIndex + 100), 7, '0', STR_PAD_LEFT),
                        'address' => "Dirección demo {$customerIndex}",
                        'business_id' => $business->id,
                        'status' => 'Entregado',
                        'payment_method' => 'Efectivo',
                        'payment_status' => 'Pendiente',
                        'total' => $items->sum('price'),
                    ],
                );

                $order->products()->sync(
                    $items->mapWithKeys(fn (Product $product) => [
                        $product->id => ['quantity' => 1, 'unit_price' => $product->price],
                    ])->all()
                );
            }
        }
    }

    private function seedUser(string $name, string $email, string $role): User
    {
        $user = User::firstOrNew(['email' => $email]);
        $user->name = $name;
        $user->role = $role;

        if (! $user->exists) {
            $user->password = 'password';
        }

        $user->save();

        return $user;
    }

    private function seedProduct(int $businessId, string $name, float $price): Product
    {
        return Product::updateOrCreate(
            ['business_id' => $businessId, 'name' => $name],
            ['price' => $price],
        );
    }
}
