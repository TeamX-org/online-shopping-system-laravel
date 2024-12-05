<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $name = $this->faker->unique()->word();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'description' => $this->faker->sentence,
            'images' => ['test-product.jpg'], // Add default test image
            'is_active' => true,
            'is_featured' => false,
            'on_sale' => false
        ];
    }
}