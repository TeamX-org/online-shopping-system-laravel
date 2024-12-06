<?php

namespace Tests\Feature\Livewire\Pages;

use App\Livewire\CategoriesPage;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoriesPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function categories_page_component_can_be_rendered()
    {
        $response = $this->get('/categories');

        $response->assertStatus(200);
        $response->assertSeeLivewire('categories-page');
    }

    /** @test */
    public function it_displays_only_active_categories()
    {
        // Create active and inactive categories
        $activeCategory = Category::factory()->create([
            'name' => 'Active Category',
            'is_active' => true
        ]);
        
        $inactiveCategory = Category::factory()->create([
            'name' => 'Inactive Category',
            'is_active' => false
        ]);

        Livewire::test(CategoriesPage::class)
            ->assertSee('Active Category')
            ->assertDontSee('Inactive Category');
    }

    /** @test */
    public function categories_have_correct_link_format()
    {
        $category = Category::factory()->create([
            'id' => 1,
            'is_active' => true
        ]);

        Livewire::test(CategoriesPage::class)
            ->assertSeeHtml('href="/products?selected_categories[0]=1"');
    }

    /** @test */
    public function categories_display_images_from_storage()
    {
        $category = Category::factory()->create([
            'image' => 'categories/test-image.jpg',
            'is_active' => true
        ]);

        Livewire::test(CategoriesPage::class)
            ->assertSeeHtml('src="')  // Testing image presence
            ->assertSeeHtml('alt="' . $category->name . '"');  // Testing alt text
    }
}