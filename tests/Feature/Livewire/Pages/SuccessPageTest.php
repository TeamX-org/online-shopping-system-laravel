<?php

namespace Tests\Feature\Livewire\Pages;

use App\Livewire\SuccessPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class SuccessPageTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(SuccessPage::class)
            ->assertStatus(200);
    }
}
