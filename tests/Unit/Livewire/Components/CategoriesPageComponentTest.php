<?php

namespace Tests\Unit\Livewire\Components;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriesPageComponentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_retrieves_only_active_categories()
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
    public function category_attributes_are_properly_formatted()
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
        $this->assertTrue(str_starts_with($category->image, 'categories/'));
        $this->assertTrue($category->is_active);
    }

    /** @test */
    public function inactive_categories_are_excluded()
    {
        // Create one active and one inactive category
        $activeCategory = Category::factory()->create(['is_active' => true]);
        $inactiveCategory = Category::factory()->create(['is_active' => false]);

        // Retrieve displayed categories
        $displayedCategories = Category::where('is_active', 1)->get();

        // Test if only active categories are retrieved
        $this->assertTrue($displayedCategories->contains($activeCategory));
        $this->assertFalse($displayedCategories->contains($inactiveCategory));
    }
}