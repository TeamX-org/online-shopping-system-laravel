<?php

namespace Tests\Feature\Livewire\Pages;

use App\Livewire\MyOrderDetailPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class MyOrderDetailPageTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(MyOrderDetailPage::class)
            ->assertStatus(200);
    }
}
