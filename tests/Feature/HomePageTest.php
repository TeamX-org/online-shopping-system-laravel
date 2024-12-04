<?php

namespace Tests\Feature\Livewire;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use Livewire\Livewire;
use App\Livewire\HomePage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function homepage_shows_active_brands_and_categories()
    {
        // Create test data
        $activeBrands = Brand::factory()->count(3)->create(['is_active' => true]);
        $inactiveBrand = Brand::factory()->create(['is_active' => false]);
        
        $activeCategories = Category::factory()->count(2)->create(['is_active' => true]);
        $inactiveCategory = Category::factory()->create(['is_active' => false]);

        // Test component
        Livewire::test(HomePage::class)
            ->assertViewHas('brands')
            ->assertViewHas('categories')
            ->assertDontSee($inactiveBrand->name)
            ->assertDontSee($inactiveCategory->name);
    }
}