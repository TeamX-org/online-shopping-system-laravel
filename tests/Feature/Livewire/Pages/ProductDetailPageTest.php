<?php

namespace Tests\Feature\Livewire\Pages;

use App\Livewire\ProductDetailPage;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductDetailPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
    }

    /** @test */
    public function product_detail_page_can_be_rendered()
    {
        $product = Product::factory()->create([
            'is_active' => true
        ]);

        $response = $this->get('/products/' . $product->slug);
        $response->assertStatus(200);
        $response->assertSeeLivewire('product-detail-page');
    }

    /** @test */
    public function it_shows_correct_product_details()
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 1000,
            'description' => 'Test Description',
            'is_active' => true
        ]);

        Livewire::test(ProductDetailPage::class, ['slug' => $product->slug])
            ->assertSee('Test Product')
            ->assertSee('1,000')
            ->assertSee('Test Description');
    }

    /** @test */
    public function quantity_can_be_increased()
    {
        $product = Product::factory()->create(['is_active' => true]);

        Livewire::test(ProductDetailPage::class, ['slug' => $product->slug])
            ->assertSet('quantity', 1)
            ->call('increaseQty')
            ->assertSet('quantity', 2);
    }

    /** @test */
    public function quantity_can_be_decreased_but_not_below_one()
    {
        $product = Product::factory()->create(['is_active' => true]);

        Livewire::test(ProductDetailPage::class, ['slug' => $product->slug])
            ->set('quantity', 2)
            ->call('decreaseQty')
            ->assertSet('quantity', 1)
            ->call('decreaseQty')
            ->assertSet('quantity', 1); // Should not go below 1
    }

    /** @test */
    public function it_can_add_product_to_cart()
    {
        $product = Product::factory()->create(['is_active' => true]);

        Livewire::test(ProductDetailPage::class, ['slug' => $product->slug])
            ->set('quantity', 2)
            ->call('addToCart', $product->id)
            ->assertDispatched('update-cart-count');
    }

    /** @test */
    public function it_shows_404_for_invalid_product()
    {
        $response = $this->get('/products/invalid-product-slug');
        $response->assertStatus(404);
    }
}