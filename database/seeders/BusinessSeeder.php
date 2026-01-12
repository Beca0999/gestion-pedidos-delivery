<?php
namespace Database\Seeders;
use App\Models\Business;
use App\Models\Product;
use App\Models\Rider;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Negocios de Prueba
        $biz1 = Business::create([
            'name' => 'Pollo Feliz 🍗',
            'delivery_price' => 35.00,
            'is_open' => true,
        ]);

        $biz2 = Business::create([
            'name' => 'Pizzería La Romana 🍕',
            'delivery_price' => 25.00,
            'is_open' => true,
        ]);

        // 2. Crear Productos para el portafolio
        Product::create(['name' => 'Pollo Entero Asado', 'price' => 210.00, 'business_id' => $biz1->id]);
        Product::create(['name' => 'Medio Pollo + Complementos', 'price' => 145.00, 'business_id' => $biz1->id]);
        
        Product::create(['name' => 'Pizza Peperoni Grande', 'price' => 180.00, 'business_id' => $biz2->id]);
        Product::create(['name' => 'Papas Gajo Sazonadas', 'price' => 65.00, 'business_id' => $biz2->id]);

        // 3. Crear Repartidores de ejemplo
        Rider::create(['name' => 'Moises (Repartidor Pro)', 'phone' => '1234567890']);
        Rider::create(['name' => 'Nacho Delivery', 'phone' => '0987654321']);
    }
}
