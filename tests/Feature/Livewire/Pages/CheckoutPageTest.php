<?php

namespace Tests\Feature\Livewire\Pages;

use App\Livewire\CheckoutPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutPageTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(CheckoutPage::class)
            ->assertStatus(200);
    }
}
