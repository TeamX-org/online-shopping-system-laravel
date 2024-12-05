<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Livewire\ProductsPage;
use App\Helpers\CartManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductPageTest extends TestCase
{
    use RefreshDatabase;

    private ProductsPage $component;

    protected function setUp(): void
    {
        parent::setUp();
        $this->component = new ProductsPage();
    }

    /** @test */
    public function it_applies_price_range_filter()
    {
        Product::factory()->create(['price' => 15000, 'is_active' => true]);
        Product::factory()->create(['price' => 25000, 'is_active' => true]);

        $this->component->price_range = 20000;
        $view = $this->component->render();
        
        $this->assertCount(1, $view->getData()['products']);
    }

    /** @test */
    public function it_sorts_products_by_price()
    {
        $expensive = Product::factory()->create(['price' => 2000, 'is_active' => true]);
        $cheap = Product::factory()->create(['price' => 1000, 'is_active' => true]);

        $this->component->sort = 'price';
        $view = $this->component->render();
        
        $products = $view->getData()['products'];
        $this->assertEquals($cheap->id, $products->first()->id);
    }

    /** @test */
    public function it_sorts_products_by_latest()
    {
        $older = Product::factory()->create(['is_active' => true]);
        sleep(1);
        $newer = Product::factory()->create(['is_active' => true]);

        $this->component->sort = 'latest';
        $view = $this->component->render();
        
        $products = $view->getData()['products'];
        $this->assertEquals($newer->id, $products->first()->id);
    }

    /** @test */
    public function it_filters_sale_products()
    {
        $saleProduct = Product::factory()->create([
            'is_active' => true,
            'on_sale' => true
        ]);
        $regularProduct = Product::factory()->create(['is_active' => true]);

        $this->component->on_sale = true;
        $view = $this->component->render();
        
        $products = $view->getData()['products'];
        $this->assertCount(1, $products);
        $this->assertEquals($saleProduct->id, $products->first()->id);
    }

    /** @test */
    public function it_loads_active_categories_and_brands()
    {
        Category::factory()->count(2)->create(['is_active' => true]);
        Category::factory()->create(['is_active' => false]);
        Brand::factory()->count(3)->create(['is_active' => true]);
        Brand::factory()->create(['is_active' => false]);

        $view = $this->component->render();
        $data = $view->getData();
        
        $this->assertCount(2, $data['categories']);
        $this->assertCount(3, $data['brands']);
    }
}