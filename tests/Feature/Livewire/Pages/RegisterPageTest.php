<?php

namespace Tests\Feature\Livewire\Pages;

use App\Livewire\Auth\RegisterPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class RegisterPageTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(RegisterPage::class)
            ->assertStatus(200);
    }
}
