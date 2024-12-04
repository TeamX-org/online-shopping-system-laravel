<?php

namespace Tests\Feature\Livewire;

use Tests\TestCase;
use App\Models\Category;
use Livewire\Livewire;
use App\Livewire\CategoriesPage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoriesPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_render_component()
    {
        Livewire::test(CategoriesPage::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.categories-page');
    }

    /** @test */
    public function it_displays_only_active_categories()
    {
        // Create active categories
        $activeCategories = Category::factory()->count(3)->create([
            'is_active' => true
        ]);

        // Create inactive category
        $inactiveCategory = Category::factory()->create([
            'is_active' => false
        ]);

        // Test the component
        Livewire::test(CategoriesPage::class)
            ->assertViewHas('categories', function ($categories) use ($activeCategories, $inactiveCategory) {
                // Should only have active categories
                $this->assertCount(3, $categories);
                
                // Check if all active categories are present
                foreach ($activeCategories as $category) {
                    $this->assertTrue($categories->contains($category));
                }

                // Check that inactive category is not present
                $this->assertFalse($categories->contains($inactiveCategory));

                return true;
            });
    }

    /** @test */
    public function it_shows_correct_page_title()
    {
        $response = $this->get('/categories'); // Adjust route as needed

        $response->assertSee('Categories - Cosmetics');
    }

    /** @test */
    public function it_handles_empty_categories()
    {
        // Delete any existing categories
        Category::query()->delete();

        // Test the component
        Livewire::test(CategoriesPage::class)
            ->assertViewHas('categories', function ($categories) {
                return $categories->isEmpty();
            });
    }
}