<?php

namespace Tests\Feature\Livewire\Pages;

use App\Livewire\Auth\ForgotPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class ForgotPageTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ForgotPage::class)
            ->assertStatus(200);
    }
}
