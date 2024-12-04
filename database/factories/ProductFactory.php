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
            'category_id' => Category::factory(), // Create and associate a category
            'brand_id' => Brand::factory(), // Create and associate a brand
            'price' => $this->faker->randomNumber(2),
            'description' => $this->faker->sentence,
        ];
    }
}