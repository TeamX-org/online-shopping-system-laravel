<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Livewire\Livewire;

class OrderResourceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate admin user
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
    }

    /** @test */
    public function can_view_order_list()
    {
        $orders = Order::factory()->count(2)->create([
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex',
            'status' => 'new'
        ]);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($orders);
    }

    /** @test */
    public function can_search_orders_by_customer_name()
    {
        $customer = User::factory()->create(['name' => 'John Doe']);
        $orderToFind = Order::factory()->create([
            'user_id' => $customer->id,
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex',
            'status' => 'new'
        ]);
        $otherOrder = Order::factory()->create([
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex',
            'status' => 'new'
        ]);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->searchTable('John')
            ->assertCanSeeTableRecords([$orderToFind])
            ->assertCanNotSeeTableRecords([$otherOrder]);
    }

    /** @test */
    public function can_filter_orders_by_payment_status()
    {
        $paidOrder = Order::factory()->create([
            'payment_method' => 'stripe',
            'payment_status' => 'paid',
            'shipping_method' => 'fedex',
            'status' => 'new'
        ]);
        $pendingOrder = Order::factory()->create([
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
            'shipping_method' => 'fedex',
            'status' => 'new'
        ]);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->searchTable('paid')
            ->assertCanSeeTableRecords([$paidOrder])
            ->assertCanNotSeeTableRecords([$pendingOrder]);
    }

    /** @test */
    public function can_create_order_with_items()
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create(['price' => 100]);

        // First fill the basic order data
        $component = Livewire::test(OrderResource\Pages\CreateOrder::class)
            ->fillForm([
                'user_id' => $customer->id,
                'payment_method' => 'stripe',
                'payment_status' => 'pending',
                'status' => 'new',
                'currency' => 'lkr',
                'shipping_method' => 'fedex',
                'notes' => 'Test order notes',
            ]);

        // Then fill the repeater data in a separate step
        $component->set('data.items', [
            '0' => [
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_amount' => $product->price,
                'total_amount' => $product->price * 2,
            ],
        ])->call('create')
            ->assertHasNoFormErrors();

        // Assert order was created
        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
            'status' => 'new',
            'currency' => 'lkr',
            'shipping_method' => 'fedex',
            'notes' => 'Test order notes'
        ]);

        // Assert order item was created
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_amount' => $product->price,
            'total_amount' => $product->price * 2
        ]);
    }

    /** @test */
    public function validates_required_fields_when_creating()
    {
        Livewire::test(OrderResource\Pages\CreateOrder::class)
            ->fillForm([
                'user_id' => null,
                'payment_method' => null,
                'payment_status' => null,
                'currency' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'user_id' => 'required',
                'payment_method' => 'required',
                'payment_status' => 'required',
                'currency' => 'required',
            ]);
    }

    /** @test */
    public function can_edit_order()
    {
        $order = Order::factory()->create([
            'payment_method' => 'stripe',
            'status' => 'new',
            'shipping_method' => 'fedex'
        ]);
        $newCustomer = User::factory()->create();

        $newData = [
            'user_id' => $newCustomer->id,
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'status' => 'processing',
            'currency' => 'lkr',
            'shipping_method' => 'dhl',
            'notes' => 'Updated notes',
        ];

        Livewire::test(OrderResource\Pages\EditOrder::class, [
            'record' => $order->id,
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $newCustomer->id,
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'status' => 'processing',
            'currency' => 'lkr',
            'shipping_method' => 'dhl',
            'notes' => 'Updated notes',
        ]);
    }

    /** @test */
    public function can_delete_order()
    {
        $order = Order::factory()->create([
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex',
            'status' => 'new'
        ]);

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->assertSuccessful()
            ->callTableAction('delete', $order);

        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
        ]);
    }

    /** @test */
    public function can_bulk_delete_orders()
    {
        $orders = Order::factory()
            ->count(2)
            ->create([
                'payment_method' => 'stripe',
                'shipping_method' => 'fedex',
                'status' => 'new'
            ]);
        
        $orderIds = $orders->pluck('id')->toArray();

        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->assertSuccessful()
            ->callTableBulkAction('delete', $orders);

        foreach ($orderIds as $id) {
            $this->assertDatabaseMissing('orders', ['id' => $id]);
        }
    }

    /** @test */
    public function can_view_order()
    {
        $order = Order::factory()->create([
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex',
            'status' => 'new'
        ]);

        Livewire::test(OrderResource\Pages\ViewOrder::class, [
            'record' => $order->id,
        ])->assertSuccessful();
    }

    /** @test */
    public function can_update_order_status()
    {
        $order = Order::factory()->create([
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex',
            'status' => 'new'
        ]);

        Livewire::test(OrderResource\Pages\EditOrder::class, [
            'record' => $order->id,
        ])
            ->fillForm([
                'status' => 'processing'
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);
    }
}