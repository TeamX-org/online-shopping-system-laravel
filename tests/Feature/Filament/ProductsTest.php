<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Livewire\Livewire;

class ProductsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate admin user
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        // Setup S3 disk for testing
        Storage::fake('s3');
    }

    /** @test */
    public function can_view_product_list()
    {
        // Create products with relationships
        $products = Product::factory()
            ->withExisting()
            ->count(2)
            ->create();

        Livewire::test(ProductResource\Pages\ListProducts::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($products);
    }

    /** @test */
    public function can_search_products_by_name()
    {
        $productToFind = Product::factory()->withExisting()->create(['name' => 'Special Product']);
        $otherProduct = Product::factory()->withExisting()->create(['name' => 'Other Product']);

        Livewire::test(ProductResource\Pages\ListProducts::class)
            ->searchTable('Special')
            ->assertCanSeeTableRecords([$productToFind])
            ->assertCanNotSeeTableRecords([$otherProduct]);
    }

    /** @test */
    public function can_search_products_by_category()
    {
        $category = Category::factory()->create(['name' => 'Electronics']);
        $productInCategory = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => Brand::factory(),
        ]);
        $otherProduct = Product::factory()->withExisting()->create();

        Livewire::test(ProductResource\Pages\ListProducts::class)
            ->searchTable('Electronics')
            ->assertCanSeeTableRecords([$productInCategory])
            ->assertCanNotSeeTableRecords([$otherProduct]);
    }

    /** @test */
    public function can_search_products_by_brand()
    {
        $brand = Brand::factory()->create(['name' => 'Apple']);
        $productWithBrand = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => Category::factory(),
        ]);
        $otherProduct = Product::factory()->withExisting()->create();

        Livewire::test(ProductResource\Pages\ListProducts::class)
            ->searchTable('Apple')
            ->assertCanSeeTableRecords([$productWithBrand])
            ->assertCanNotSeeTableRecords([$otherProduct]);
    }

    /** @test */
    public function can_filter_products_by_category()
    {
        $category = Category::factory()->create();
        $productInCategory = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => Brand::factory(),
        ]);
        $otherProduct = Product::factory()->withExisting()->create();

        Livewire::test(ProductResource\Pages\ListProducts::class)
            ->filterTable('category', $category->id)
            ->assertCanSeeTableRecords([$productInCategory])
            ->assertCanNotSeeTableRecords([$otherProduct]);
    }

    /** @test */
    public function can_filter_products_by_brand()
    {
        $brand = Brand::factory()->create();
        $productWithBrand = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => Category::factory(),
        ]);
        $otherProduct = Product::factory()->withExisting()->create();

        Livewire::test(ProductResource\Pages\ListProducts::class)
            ->filterTable('brand', $brand->id)
            ->assertCanSeeTableRecords([$productWithBrand])
            ->assertCanNotSeeTableRecords([$otherProduct]);
    }

    /** @test */
    public function can_create_product()
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $newProduct = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'images' => ['products/test-image.jpg'],
            'is_active' => true,
            'is_featured' => false,
            'in_stock' => true,
            'on_sale' => false,
        ];

        Livewire::test(ProductResource\Pages\CreateProduct::class)
            ->fillForm($newProduct)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'name' => $newProduct['name'],
            'slug' => 'test-product',
            'price' => $newProduct['price'],
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'is_active' => true,
            'is_featured' => false,
            'in_stock' => true,
            'on_sale' => false,
        ]);
    }

    /** @test */
    public function validates_required_fields_when_creating()
    {
        Livewire::test(ProductResource\Pages\CreateProduct::class)
            ->fillForm([
                'name' => '',
                'description' => '',
                'price' => null,
                'category_id' => null,
                'brand_id' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'description' => 'required',
                'price' => 'required',
                'category_id' => 'required',
                'brand_id' => 'required',
                'images' => 'required',
            ]);
    }

    /** @test */
    public function validates_price_minimum_value()
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        Livewire::test(ProductResource\Pages\CreateProduct::class)
            ->fillForm([
                'name' => 'Test Product',
                'description' => 'Test Description',
                'price' => -10,
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'images' => ['products/test-image.jpg'],
            ])
            ->call('create')
            ->assertHasFormErrors(['price' => 'min']);
    }

    /** @test */
    public function can_edit_product()
    {
        $product = Product::factory()->withExisting()->create();
        $newCategory = Category::factory()->create();
        $newBrand = Brand::factory()->create();

        $newData = [
            'name' => 'Updated Product Name',
            'description' => 'Updated Description',
            'price' => 199.99,
            'category_id' => $newCategory->id,
            'brand_id' => $newBrand->id,
            'images' => ['products/test-image.jpg'], // Added required images field
            'is_active' => false,
            'is_featured' => true,
            'in_stock' => false,
            'on_sale' => true,
        ];

        Livewire::test(ProductResource\Pages\EditProduct::class, [
            'record' => $product->id,
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $newData['name'],
            'price' => $newData['price'],
            'category_id' => $newCategory->id,
            'brand_id' => $newBrand->id,
            'is_active' => false,
            'is_featured' => true,
            'in_stock' => false,
            'on_sale' => true,
        ]);
    }

    /** @test */
    public function can_delete_product()
    {
        $product = Product::factory()->withExisting()->create();

        Livewire::test(ProductResource\Pages\ListProducts::class)
            ->assertSuccessful()
            ->callTableAction('delete', $product);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /** @test */
    public function can_bulk_delete_products()
    {
        $products = Product::factory()
            ->withExisting()
            ->count(2)
            ->create();

        $productIds = $products->pluck('id')->toArray();

        Livewire::test(ProductResource\Pages\ListProducts::class)
            ->assertSuccessful()
            ->callTableBulkAction('delete', $products);

        foreach ($productIds as $id) {
            $this->assertDatabaseMissing('products', ['id' => $id]);
        }
    }

    /** @test */
    public function can_sort_products_by_price()
    {
        $products = Product::factory()
            ->withExisting()
            ->count(2)
            ->create();

        $component = Livewire::test(ProductResource\Pages\ListProducts::class);
        
        // Test initial load
        $component->assertSuccessful();
        
        // Test sorting
        $component->sortTable('price')
            ->assertSuccessful()
            ->assertCanSeeTableRecords($products);
    }
}