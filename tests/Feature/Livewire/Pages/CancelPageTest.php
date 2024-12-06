<?php

namespace Tests\Feature\Livewire\Pages;

use App\Livewire\CancelPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class CancelPageTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(CancelPage::class)
            ->assertStatus(200);
    }
}
