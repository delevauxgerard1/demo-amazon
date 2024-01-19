<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'title' => $this->faker->randomElement([
                'Smartwatch with Health Tracking',
                'Robot Vacuum with Smart Mapping',
                'Limited Edition Sneakers',
                'Elegant Lace Evening Dress',
                'Orthopedic Dog Bed with Memory Foam',
                'High-Quality Acrylic Paint Set',
                'Wireless Noise-Canceling Headphones',
                '1000 Thread Count Egyptian Cotton Sheets',
                'Designer Handbag with Leather Details',
                'Home Security Camera with Night Vision',
            ]),
            'description' => $this->faker->paragraph,
            'image' => $this->faker->imageUrl,
            'price' => $this->faker->randomFloat(2, 10, 100),
            'category' => $this->faker->numberBetween(1, 6),
            'created_at' => $this->faker->dateTimeThisMonth,
            'updated_at' => $this->faker->dateTimeThisMonth,
        ];
    }
}
