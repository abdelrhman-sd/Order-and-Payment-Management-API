<?php

namespace Database\Seeders;

use App\Models\Product;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::insert([
            [
                'name'  => 'Laptop',
                'price' => 1200.00,
                'stock' => 30,
            ],
            [
                'name'  => 'Mouse',
                'price' => 25.99,
                'stock' => 35,
            ],
            [
                'name'  => 'Keyboard',
                'price' => 49.99,
                'stock' => 70,
            ],
            [
                'name'  => 'Monitor',
                'price' => 299.99,
                'stock' => 40,
            ],
            [
                'name'  => 'USB-C Cable',
                'price' => 12.50,
                'stock' => 105,
            ],
            [
                'name'  => 'Headphones',
                'price' => 89.99,
                'stock' => 55,
            ],
            [
                'name'  => 'Webcam',
                'price' => 65.00,
                'stock' => 35,
            ],
            [
                'name'  => 'External SSD',
                'price' => 149.99,
                'stock' => 23,
            ],
            [
                'name'  => 'Desk Lamp',
                'price' => 35.00,
                'stock' => 14,
            ],
            [
                'name'  => 'Gaming Chair',
                'price' => 249.99,
                'stock' => 6,
            ],
        ]);
    }
}
