<?php

namespace Tests\Unit\Livewire\Components;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductsPageComponentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_only_shows_active_products()
    {
        // Create 2 products, one active and one inactive
        Product::factory()->create(['is_active' => true]);
        Product::factory()->create(['is_active' => false]);

        // Get only active products
        $activeProducts = Product::where('is_active', 1)->get();

        // Assert that only 1 product is active
        $this->assertEquals(1, $activeProducts->count());
        $this->assertDatabaseCount('products', 2);
    }

    /** @test */
    public function it_correctly_filters_featured_products()
    {
        // Create 2 products, one featured and one not featured
        Product::factory()->create([
            'is_featured' => true,
            'is_active' => true
        ]);
        // Create a product that is not featured
        Product::factory()->create([
            'is_featured' => false,
            'is_active' => true
        ]);

        // Get only featured products
        $featuredProducts = Product::where('is_featured', 1)
            ->where('is_active', 1)
            ->get();

        // Assert that only 1 product is featured
        $this->assertEquals(1, $featuredProducts->count());
    }

    /** @test */
    public function it_correctly_filters_sale_products()
    {
        // Create 2 products, one on sale and one not on sale
        Product::factory()->create([
            'on_sale' => true,
            'is_active' => true
        ]);
        // Create a product that is not on sale
        Product::factory()->create([
            'on_sale' => false,
            'is_active' => true
        ]);

        // Get only sale products
        $saleProducts = Product::where('on_sale', 1)
            ->where('is_active', 1)
            ->get();

        // Assert that only 1 product is on sale
        $this->assertEquals(1, $saleProducts->count());
    }

    /** @test */
    public function product_belongs_to_category_and_brand()
    {
        // Create a category and a brand
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        
        // Create a product that belongs to the category and brand
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id
        ]);

        // Assert that the product belongs to the category and brand
        $this->assertEquals($category->id, $product->category->id);
        $this->assertEquals($brand->id, $product->brand->id);
    }
}