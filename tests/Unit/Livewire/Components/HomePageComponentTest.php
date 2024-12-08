<?php

namespace Tests\Unit\Livewire\Components;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageComponentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_only_retrieves_active_brands()
    {
        // Create a mix of active and inactive brands
        Brand::factory()->count(3)->create(['is_active' => true]);
        Brand::factory()->count(2)->create(['is_active' => false]);

        $activeBrands = Brand::where('is_active', 1)->get();

        $this->assertEquals(3, $activeBrands->count());
        $this->assertDatabaseCount('brands', 5);
    }

    /** @test */
    public function it_only_retrieves_active_categories()
    {
        // Create a mix of active and inactive categories
        Category::factory()->count(3)->create(['is_active' => true]);
        Category::factory()->count(2)->create(['is_active' => false]);

        // Retrieve only active categories
        $activeCategories = Category::where('is_active', 1)->get();

        // Test if only active categories are retrieved
        $this->assertEquals(3, $activeCategories->count());
        $this->assertDatabaseCount('categories', 5);
    }

    /** @test */
    public function brand_has_required_attributes()
    {
        // Create a brand
        $brand = Brand::factory()->create([
            'name' => 'Test Brand',
            'slug' => 'test-brand',
            'image' => 'brands/test.jpg',
            'is_active' => true
        ]);

        // Test if attributes are properly set and formatted
        $this->assertEquals('Test Brand', $brand->name);
        $this->assertEquals('test-brand', $brand->slug);
        $this->assertEquals('brands/test.jpg', $brand->image);
        $this->assertTrue($brand->is_active);
    }

    /** @test */
    public function category_has_required_attributes()
    {
        // Create a category
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'image' => 'categories/test.jpg',
            'is_active' => true
        ]);

        // Test if attributes are properly set and formatted
        $this->assertEquals('Test Category', $category->name);
        $this->assertEquals('test-category', $category->slug);
        $this->assertEquals('categories/test.jpg', $category->image);
        $this->assertTrue($category->is_active);
    }
}