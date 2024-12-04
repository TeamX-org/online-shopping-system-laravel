<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Category;
use App\Livewire\CategoriesPage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_retrieves_only_active_categories()
    {
        Category::factory()->count(2)->create(['is_active' => true]);
        Category::factory()->create(['is_active' => false]);

        $component = new CategoriesPage();
        $view = $component->render();

        $this->assertCount(2, $view->getData()['categories']);
    }

    /** @test */
    public function it_returns_empty_collection_when_no_active_categories()
    {
        Category::factory()->create(['is_active' => false]);

        $component = new CategoriesPage();
        $view = $component->render();

        $this->assertCount(0, $view->getData()['categories']);
    }
}