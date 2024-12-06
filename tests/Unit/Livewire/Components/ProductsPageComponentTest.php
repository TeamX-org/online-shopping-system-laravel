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
        Product::factory()->create(['is_active' => true]);
        Product::factory()->create(['is_active' => false]);

        $activeProducts = Product::where('is_active', 1)->get();

        $this->assertEquals(1, $activeProducts->count());
        $this->assertDatabaseCount('products', 2);
    }

    /** @test */
    public function it_correctly_filters_featured_products()
    {
        Product::factory()->create([
            'is_featured' => true,
            'is_active' => true
        ]);
        Product::factory()->create([
            'is_featured' => false,
            'is_active' => true
        ]);

        $featuredProducts = Product::where('is_featured', 1)
            ->where('is_active', 1)
            ->get();

        $this->assertEquals(1, $featuredProducts->count());
    }

    /** @test */
    public function it_correctly_filters_sale_products()
    {
        Product::factory()->create([
            'on_sale' => true,
            'is_active' => true
        ]);
        Product::factory()->create([
            'on_sale' => false,
            'is_active' => true
        ]);

        $saleProducts = Product::where('on_sale', 1)
            ->where('is_active', 1)
            ->get();

        $this->assertEquals(1, $saleProducts->count());
    }

    /** @test */
    public function product_belongs_to_category_and_brand()
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id
        ]);

        $this->assertEquals($category->id, $product->category->id);
        $this->assertEquals($brand->id, $product->brand->id);
    }
}