<?php

namespace Tests\Feature\Livewire\Pages;

use App\Livewire\ProductsPage;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
    }

    /** @test */
    public function products_page_component_can_be_rendered()
    {
        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertSeeLivewire('products-page');
    }

    /** @test */
    public function it_filters_products_by_category()
    {
        $category1 = Category::factory()->create(['is_active' => true]);
        $category2 = Category::factory()->create(['is_active' => true]);

        $product1 = Product::factory()->create([
            'category_id' => $category1->id,
            'is_active' => true
        ]);
        $product2 = Product::factory()->create([
            'category_id' => $category2->id,
            'is_active' => true
        ]);

        Livewire::test(ProductsPage::class)
            ->set('selected_categories', [$category1->id])
            ->assertSee($product1->name)
            ->assertDontSee($product2->name);
    }

    /** @test */
    public function it_filters_products_by_brand()
    {
        $brand1 = Brand::factory()->create(['is_active' => true]);
        $brand2 = Brand::factory()->create(['is_active' => true]);

        $product1 = Product::factory()->create([
            'brand_id' => $brand1->id,
            'is_active' => true
        ]);
        $product2 = Product::factory()->create([
            'brand_id' => $brand2->id,
            'is_active' => true
        ]);

        Livewire::test(ProductsPage::class)
            ->set('selected_brands', [$brand1->id])
            ->assertSee($product1->name)
            ->assertDontSee($product2->name);
    }

    /** @test */
    public function it_filters_products_by_price_range()
    {
        $cheapProduct = Product::factory()->create([
            'price' => 1000,
            'is_active' => true
        ]);
        $expensiveProduct = Product::factory()->create([
            'price' => 25000,
            'is_active' => true
        ]);

        Livewire::test(ProductsPage::class)
            ->set('price_range', 15000)
            ->assertSee($cheapProduct->name)
            ->assertDontSee($expensiveProduct->name);
    }

    /** @test */
    public function it_sorts_products_by_price()
    {
        $expensiveProduct = Product::factory()->create([
            'price' => 2000,
            'is_active' => true
        ]);
        $cheapProduct = Product::factory()->create([
            'price' => 1000,
            'is_active' => true
        ]);

        Livewire::test(ProductsPage::class)
            ->set('sort', 'price')
            ->assertSeeInOrder([$cheapProduct->name, $expensiveProduct->name]);
    }

    /** @test */
    public function it_adds_product_to_cart()
    {
        $product = Product::factory()->create(['is_active' => true]);

        Livewire::test(ProductsPage::class)
            ->call('addToCart', $product->id)
            ->assertDispatched('update-cart-count');
    }
}