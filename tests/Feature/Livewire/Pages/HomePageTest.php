<?php

namespace Tests\Feature\Livewire\Pages;

use App\Livewire\HomePage;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function home_page_component_can_be_rendered()
    {
        // Test if home page component can be rendered
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSeeLivewire('home-page');
    }

    /** @test */
    public function home_page_displays_active_brands()
    {
        // Create active and inactive brands
        $activeBrand = Brand::factory()->create(['is_active' => true]);
        $inactiveBrand = Brand::factory()->create(['is_active' => false]);

        // Test if only active brands are displayed
        Livewire::test(HomePage::class)
            ->assertSee($activeBrand->name)
            ->assertDontSee($inactiveBrand->name);
    }

    /** @test */
    public function home_page_displays_active_categories()
    {
        // Create active and inactive categories
        $activeCategory = Category::factory()->create(['is_active' => true]);
        $inactiveCategory = Category::factory()->create(['is_active' => false]);

        // Test if only active categories are displayed
        Livewire::test(HomePage::class)
            ->assertSee($activeCategory->name)
            ->assertDontSee($inactiveCategory->name);
    }

    /** @test */
    public function brand_links_have_correct_url()
    {
        // Create a brand
        $brand = Brand::factory()->create(['is_active' => true]);
        $expectedUrl = "/products?selected_brands[0]={$brand->id}";

        // Test if brand link has correct URL
        Livewire::test(HomePage::class)
            ->assertSeeHtml($expectedUrl);
    }

    /** @test */
    public function category_links_have_correct_url()
    {
        // Create a category
        $category = Category::factory()->create(['is_active' => true]);
        $expectedUrl = "/products?selected_categories[0]={$category->id}";

        // Test if category link has correct URL
        Livewire::test(HomePage::class)
            ->assertSeeHtml($expectedUrl);
    }
}