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
        // Create orders with specific attributes
        $orders = Order::factory()->count(2)->create([
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex',
            'status' => 'new'
        ]);

        // Test viewing the order list page
        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($orders);
    }

    /** @test */
    public function can_search_orders_by_customer_name()
    {
        // Create a customer and orders
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

        // Test searching orders by customer name
        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->searchTable('John')
            ->assertCanSeeTableRecords([$orderToFind])
            ->assertCanNotSeeTableRecords([$otherOrder]);
    }

    /** @test */
    public function can_filter_orders_by_payment_status()
    {
        // Create orders with different payment statuses
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

        // Test filtering orders by payment status
        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->searchTable('paid')
            ->assertCanSeeTableRecords([$paidOrder])
            ->assertCanNotSeeTableRecords([$pendingOrder]);
    }

    /** @test */
    public function can_create_order_with_items()
    {
        // Create a customer and product
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
        // Test validation for required fields when creating an order
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
        // Create an order and a new customer for updating
        $order = Order::factory()->create([
            'payment_method' => 'stripe',
            'status' => 'new',
            'shipping_method' => 'fedex'
        ]);
        $newCustomer = User::factory()->create();

        // Define new data for the order
        $newData = [
            'user_id' => $newCustomer->id,
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'status' => 'processing',
            'currency' => 'lkr',
            'shipping_method' => 'dhl',
            'notes' => 'Updated notes',
        ];

        // Test editing the order
        Livewire::test(OrderResource\Pages\EditOrder::class, [
            'record' => $order->id,
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        // Assert the order is updated in the database
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
        // Create an order to delete
        $order = Order::factory()->create([
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex',
            'status' => 'new'
        ]);

        // Test deleting the order
        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->assertSuccessful()
            ->callTableAction('delete', $order);

        // Assert the order is deleted from the database
        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
        ]);
    }

    /** @test */
    public function can_bulk_delete_orders()
    {
        // Create multiple orders to delete
        $orders = Order::factory()
            ->count(2)
            ->create([
                'payment_method' => 'stripe',
                'shipping_method' => 'fedex',
                'status' => 'new'
            ]);
        
        $orderIds = $orders->pluck('id')->toArray();

        // Test bulk deleting the orders
        Livewire::test(OrderResource\Pages\ListOrders::class)
            ->assertSuccessful()
            ->callTableBulkAction('delete', $orders);

        // Assert the orders are deleted from the database
        foreach ($orderIds as $id) {
            $this->assertDatabaseMissing('orders', ['id' => $id]);
        }
    }

    /** @test */
    public function can_view_order()
    {
        // Create an order to view
        $order = Order::factory()->create([
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex',
            'status' => 'new'
        ]);

        // Test viewing the order
        Livewire::test(OrderResource\Pages\ViewOrder::class, [
            'record' => $order->id,
        ])->assertSuccessful();
    }

    /** @test */
    public function can_update_order_status()
    {
        // Create an order to update
        $order = Order::factory()->create([
            'payment_method' => 'stripe',
            'shipping_method' => 'fedex',
            'status' => 'new'
        ]);

        // Test updating the order status
        Livewire::test(OrderResource\Pages\EditOrder::class, [
            'record' => $order->id,
        ])
            ->fillForm([
                'status' => 'processing'
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        // Assert the order status is updated in the database
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);
    }
}