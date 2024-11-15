<?php

namespace Tests\Feature\Livewire\Pages;

use App\Livewire\Auth\ForgotPasswordPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class ForgotPasswordPageTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        // Test if the ForgotPasswordPage component renders successfully
        Livewire::test(ForgotPasswordPage::class)
            ->assertStatus(200);
    }
}
