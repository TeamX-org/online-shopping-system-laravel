<?php

namespace Tests\Feature\Filament\Widgets;

use App\Filament\Resources\OrderResource\Widgets\OrderStats;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Number;
use Livewire\Livewire;
use Tests\TestCase;

class OrderStatsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate admin user
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
    }

    /** @test */
    public function can_render_widget()
    {
        Livewire::test(OrderStats::class)
            ->assertSuccessful();
    }

    /** @test */
    public function shows_correct_number_of_new_orders()
    {
        // Create orders with different statuses
        Order::factory()->create([
            'status' => 'new',
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex'
        ]);
        Order::factory()->create([
            'status' => 'processing',
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex'
        ]);

        Livewire::test(OrderStats::class)
            ->assertSee('1') // Should show 1 new order
            ->assertSee('New Orders');
    }

    /** @test */
    public function shows_correct_number_of_processing_orders()
    {
        // Create orders with different statuses
        Order::factory()->count(2)->create([
            'status' => 'processing',
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex'
        ]);
        Order::factory()->create([
            'status' => 'new',
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex'
        ]);

        Livewire::test(OrderStats::class)
            ->assertSee('2') // Should show 2 processing orders
            ->assertSee('Order Processing');
    }

    /** @test */
    public function shows_correct_number_of_shipped_orders()
    {
        // Create orders with different statuses
        Order::factory()->count(3)->create([
            'status' => 'shipped',
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex'
        ]);
        Order::factory()->create([
            'status' => 'new',
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex'
        ]);

        Livewire::test(OrderStats::class)
            ->assertSee('3') // Should show 3 shipped orders
            ->assertSee('Order Shipped');
    }

    /** @test */
    public function shows_correct_average_price()
    {
        // Create orders with different prices
        Order::factory()->create([
            'grand_total' => 100,
            'status' => 'new',
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex'
        ]);
        Order::factory()->create([
            'grand_total' => 200,
            'status' => 'new',
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex'
        ]);

        $expectedAverage = Number::currency(150, 'LKR');

        Livewire::test(OrderStats::class)
            ->assertSee($expectedAverage)
            ->assertSee('Average Price');
    }

    /** @test */
    public function shows_zero_when_no_orders()
    {
        Livewire::test(OrderStats::class)
            ->assertSee('0')
            ->assertSee('New Orders')
            ->assertSee('Order Processing')
            ->assertSee('Order Shipped')
            ->assertSee(Number::currency(0, 'LKR'));
    }

    /** @test */
    public function updates_stats_when_new_order_is_created()
    {
        $component = Livewire::test(OrderStats::class);
        
        // Initial assertion
        $component->assertSee('0');

        // Create new order
        Order::factory()->create([
            'status' => 'new',
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex'
        ]);

        // Refresh component
        $component->call('$refresh')
            ->assertSee('1')
            ->assertSee('New Orders');
    }

    /** @test */
    public function calculates_average_price_correctly_with_multiple_orders()
    {
        // Create orders with different prices
        Order::factory()->create([
            'grand_total' => 100,
            'status' => 'new',
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex'
        ]);
        Order::factory()->create([
            'grand_total' => 200,
            'status' => 'new',
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex'
        ]);
        Order::factory()->create([
            'grand_total' => 300,
            'status' => 'new',
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex'
        ]);

        $expectedAverage = Number::currency(200, 'LKR');

        Livewire::test(OrderStats::class)
            ->assertSee($expectedAverage)
            ->assertSee('Average Price');
    }
}