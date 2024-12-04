<?php

namespace Tests\Feature\Livewire;

use Tests\TestCase;
use App\Models\Category;
use Livewire\Livewire;
use App\Livewire\CategoriesPage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function categories_page_shows_only_active_categories()
    {
        $activeCategory = Category::factory()->create(['is_active' => true]);
        $inactiveCategory = Category::factory()->create(['is_active' => false]);

        Livewire::test(CategoriesPage::class)
            ->assertSee($activeCategory->name)
            ->assertDontSee($inactiveCategory->name);
    }
}