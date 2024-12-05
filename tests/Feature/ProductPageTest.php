<?php

namespace Tests\Feature\Livewire;

use App\Livewire\ProductsPage;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
   public function it_can_render_component()
   {
       Livewire::test(ProductsPage::class)
           ->assertStatus(200)
           ->assertViewIs('livewire.products-page');
   }

   /** @test */
   public function it_shows_active_products_with_pagination()
   {
       Product::factory()
           ->count(10)
           ->create([
               'is_active' => true,
               'images' => ['test-product.jpg']
           ]);

       $inactiveProduct = Product::factory()->create([
           'is_active' => false,
           'images' => ['test-product.jpg']
       ]);

       Livewire::test(ProductsPage::class)
           ->assertViewHas('products', function($products) {
               return $products->count() == 9; // Default pagination
           })
           ->assertDontSee($inactiveProduct->name);
   }

   /** @test */
   public function it_filters_products_by_category()
   {
       $category = Category::factory()->create(['is_active' => true]);
       
       $productsInCategory = Product::factory()
           ->count(3)
           ->create([
               'category_id' => $category->id,
               'is_active' => true,
               'images' => ['test-product.jpg']
           ]);

       $otherProduct = Product::factory()->create([
           'is_active' => true,
           'images' => ['test-product.jpg']
       ]);

       Livewire::test(ProductsPage::class)
           ->set('selected_categories', [$category->id])
           ->assertSee($productsInCategory[0]->name)
           ->assertDontSee($otherProduct->name);
   }

   /** @test */
   public function it_filters_products_by_brand()
   {
       $brand = Brand::factory()->create(['is_active' => true]);
       
       $brandProducts = Product::factory()
           ->count(3)
           ->create([
               'brand_id' => $brand->id,
               'is_active' => true,
               'images' => ['test-product.jpg']
           ]);

       $otherProduct = Product::factory()->create([
           'is_active' => true,
           'images' => ['test-product.jpg']
       ]);

       Livewire::test(ProductsPage::class)
           ->set('selected_brands', [$brand->id])
           ->assertSee($brandProducts[0]->name)
           ->assertDontSee($otherProduct->name);
   }

   /** @test */
   public function it_filters_featured_products()
   {
       $featuredProduct = Product::factory()->create([
           'is_active' => true,
           'is_featured' => true,
           'images' => ['test-product.jpg']
       ]);

       $nonFeaturedProduct = Product::factory()->create([
           'is_active' => true,
           'is_featured' => false,
           'images' => ['test-product.jpg']
       ]);

       Livewire::test(ProductsPage::class)
           ->set('featured', true)
           ->assertSee($featuredProduct->name)
           ->assertDontSee($nonFeaturedProduct->name);
   }

   /** @test */
   public function it_filters_sale_products()
   {
       $saleProduct = Product::factory()->create([
           'is_active' => true,
           'on_sale' => true,
           'images' => ['test-product.jpg']
       ]);

       $regularProduct = Product::factory()->create([
           'is_active' => true,
           'on_sale' => false,
           'images' => ['test-product.jpg']
       ]);

       Livewire::test(ProductsPage::class)
           ->set('on_sale', true)
           ->assertSee($saleProduct->name)
           ->assertDontSee($regularProduct->name);
   }

   /** @test */
   public function it_filters_by_price_range()
   {
       $cheapProduct = Product::factory()->create([
           'is_active' => true,
           'price' => 100,
           'images' => ['test-product.jpg']
       ]);

       $expensiveProduct = Product::factory()->create([
           'is_active' => true,
           'price' => 25000,
           'images' => ['test-product.jpg']
       ]);

       Livewire::test(ProductsPage::class)
           ->set('price_range', 20000)
           ->assertSee($cheapProduct->name)
           ->assertDontSee($expensiveProduct->name);
   }

   /** @test */
   public function it_sorts_products_by_latest()
   {
       $oldProduct = Product::factory()->create([
           'is_active' => true,
           'images' => ['test-product.jpg'],
           'created_at' => now()->subDays(1)
       ]);

       $newProduct = Product::factory()->create([
           'is_active' => true,
           'images' => ['test-product.jpg'],
           'created_at' => now()
       ]);

       Livewire::test(ProductsPage::class)
           ->set('sort', 'latest')
           ->assertSeeInOrder([$newProduct->name, $oldProduct->name]);
   }

   /** @test */
   public function it_sorts_products_by_price()
   {
       $expensiveProduct = Product::factory()->create([
           'is_active' => true,
           'price' => 1000,
           'images' => ['test-product.jpg']
       ]);

       $cheapProduct = Product::factory()->create([
           'is_active' => true,
           'price' => 500,
           'images' => ['test-product.jpg']
       ]);

       Livewire::test(ProductsPage::class)
           ->set('sort', 'price')
           ->assertSeeInOrder([$cheapProduct->name, $expensiveProduct->name]);
   }

   /** @test */
   public function it_adds_product_to_cart()
   {
       $product = Product::factory()->create([
           'is_active' => true,
           'images' => ['test-product.jpg']
       ]);

       Livewire::test(ProductsPage::class)
           ->call('addToCart', $product->id)
           ->assertDispatched('update-cart-count')
           ->assertEmitted('alert', [
               'type' => 'success',
               'message' => 'Product added to cart successfully!',
           ]);
   }

   /** @test */
   public function it_loads_active_categories_and_brands()
   {
       $activeCategory = Category::factory()->create(['is_active' => true]);
       $inactiveCategory = Category::factory()->create(['is_active' => false]);
       
       $activeBrand = Brand::factory()->create(['is_active' => true]);
       $inactiveBrand = Brand::factory()->create(['is_active' => false]);

       Livewire::test(ProductsPage::class)
           ->assertViewHas('categories', function($categories) use ($activeCategory, $inactiveCategory) {
               return $categories->contains($activeCategory) && 
                      !$categories->contains($inactiveCategory);
           })
           ->assertViewHas('brands', function($brands) use ($activeBrand, $inactiveBrand) {
               return $brands->contains($activeBrand) && 
                      !$brands->contains($inactiveBrand);
           });
   }
}