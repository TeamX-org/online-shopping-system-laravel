<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Brand;
use App\Models\Category;
use App\Livewire\HomePage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_retrieves_only_active_brands()
    {
        Brand::factory()->count(3)->create(['is_active' => true]);
        Brand::factory()->create(['is_active' => false]);

        $component = new HomePage();
        $view = $component->render();

        $this->assertCount(3, $view->getData()['brands']);
    }

    /** @test */
    public function it_retrieves_only_active_categories()
    {
        Category::factory()->count(2)->create(['is_active' => true]);
        Category::factory()->create(['is_active' => false]);

        $component = new HomePage();
        $view = $component->render();

        $this->assertCount(2, $view->getData()['categories']);
    }
}