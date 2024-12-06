<?php

namespace Tests\Feature\Livewire\Pages;

use App\Livewire\Auth\ResetPasswordPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class ResetPasswordPageTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ResetPasswordPage::class)
            ->assertStatus(200);
    }
}
