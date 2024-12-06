<?php

namespace Tests\Feature\Livewire\Pages;

use App\Livewire\MyOrdersPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class MyOrdersPageTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(MyOrdersPage::class)
            ->assertStatus(200);
    }
}
