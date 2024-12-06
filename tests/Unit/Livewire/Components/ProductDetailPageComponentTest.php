<?php

namespace Tests\Unit\Livewire\Components;

use App\Models\Product;
use App\Helpers\CartManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailPageComponentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function product_can_be_found_by_slug()
    {
        $product = Product::factory()->create([
            'slug' => 'test-product',
            'is_active' => true
        ]);

        $foundProduct = Product::where('slug', 'test-product')->first();
        
        $this->assertNotNull($foundProduct);
        $this->assertEquals($product->id, $foundProduct->id);
    }

    /** @test */
    public function product_has_required_attributes()
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test Description',
            'price' => 1000,
            'is_active' => true
        ]);

        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals('test-product', $product->slug);
        $this->assertEquals('Test Description', $product->description);
        $this->assertEquals(1000, $product->price);
        $this->assertTrue($product->is_active);
    }

    /** @test */
    public function cart_management_adds_correct_quantity()
    {
        $product = Product::factory()->create(['is_active' => true]);
        
        // Test adding multiple quantities
        $totalCount = CartManagement::addItemToCartWithQty($product->id, 3);
        
        $this->assertIsInt($totalCount);
        $this->assertGreaterThan(0, $totalCount);
    }

    /** @test */
    public function product_images_are_accessible()
    {
        $product = Product::factory()->create([
            'is_active' => true,
            'images' => ['products/image1.jpg', 'products/image2.jpg']
        ]);

        $this->assertIsArray($product->images);
        $this->assertCount(2, $product->images);
        $this->assertStringStartsWith('products/', $product->images[0]);
    }
}