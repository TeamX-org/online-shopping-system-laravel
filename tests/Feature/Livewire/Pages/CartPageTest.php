<?php

namespace Tests\Feature\Livewire\Pages;

use App\Livewire\CartPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class CartPageTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        // Test if the CartPage component renders successfully
        Livewire::test(CartPage::class)
            ->assertStatus(200);
    }
}
