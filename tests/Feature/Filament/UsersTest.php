<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Livewire\Livewire;

class UsersTest extends TestCase
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
    public function can_view_user_list()
    {
        // Create users after admin
        $users = User::factory()->count(2)->create();

        Livewire::test(UserResource\Pages\ListUsers::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($users);
    }

    /** @test */
    public function can_search_users_by_name()
    {
        $userToFind = User::factory()->create(['name' => 'John Doe']);
        $otherUser = User::factory()->create(['name' => 'Jane Smith']);

        Livewire::test(UserResource\Pages\ListUsers::class)
            ->searchTable('John')
            ->assertCanSeeTableRecords([$userToFind])
            ->assertCanNotSeeTableRecords([$otherUser]);
    }

    /** @test */
    public function can_search_users_by_email()
    {
        $userToFind = User::factory()->create(['email' => 'johndoe@example.com']);
        $otherUser = User::factory()->create(['email' => 'janesmith@example.com']);

        Livewire::test(UserResource\Pages\ListUsers::class)
            ->searchTable('johndoe')
            ->assertCanSeeTableRecords([$userToFind])
            ->assertCanNotSeeTableRecords([$otherUser]);
    }

    /** @test */
    public function can_create_user()
    {
        $newUser = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'email_verified_at' => now(),
        ];

        Livewire::test(UserResource\Pages\CreateUser::class)
            ->fillForm($newUser)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'name' => $newUser['name'],
            'email' => $newUser['email'],
        ]);

        $user = User::where('email', $newUser['email'])->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /** @test */
    public function validates_required_fields_when_creating()
    {
        Livewire::test(UserResource\Pages\CreateUser::class)
            ->fillForm([
                'name' => '',
                'email' => '',
                'password' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'email' => 'required',
                'password' => 'required',
            ]);
    }

    /** @test */
    public function validates_email_format()
    {
        Livewire::test(UserResource\Pages\CreateUser::class)
            ->fillForm([
                'name' => 'Test User',
                'email' => 'invalid-email',
                'password' => 'password123',
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'email']);
    }

    /** @test */
    public function validates_unique_email()
    {
        $existingUser = User::factory()->create();

        Livewire::test(UserResource\Pages\CreateUser::class)
            ->fillForm([
                'name' => 'Test User',
                'email' => $existingUser->email,
                'password' => 'password123',
            ])
            ->call('create')
            ->assertHasFormErrors(['email' => 'unique']);
    }

    /** @test */
    public function can_edit_user()
    {
        $user = User::factory()->create();
        $newData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        Livewire::test(UserResource\Pages\EditUser::class, [
            'record' => $user->id,
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $newData['name'],
            'email' => $newData['email'],
        ]);
    }

    /** @test */
    public function password_is_optional_when_editing()
    {
        $user = User::factory()->create();
        $originalPassword = $user->password;

        Livewire::test(UserResource\Pages\EditUser::class, [
            'record' => $user->id,
        ])
            ->fillForm([
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'password' => '', // Empty password
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();
        $this->assertEquals($originalPassword, $user->password);
    }

    /** @test */
    public function can_delete_user()
    {
        $user = User::factory()->create();

        Livewire::test(UserResource\Pages\ListUsers::class)
            ->assertSuccessful()
            ->callTableAction('delete', $user);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    /** @test */
    public function can_bulk_delete_users()
    {
        // Create users to delete
        $users = User::factory()->count(2)->create();

        // Get IDs for checking later
        $userIds = $users->pluck('id')->toArray();

        Livewire::test(UserResource\Pages\ListUsers::class)
            ->assertSuccessful()
            ->callTableBulkAction('delete', $users);

        // Verify users were deleted
        foreach ($userIds as $id) {
            $this->assertDatabaseMissing('users', ['id' => $id]);
        }
    }

    /** @test */
    public function can_sort_users()
    {
        $userA = User::factory()->create(['name' => 'John Doe']);
        $userB = User::factory()->create(['name' => 'Alice Smith']);

        $component = Livewire::test(UserResource\Pages\ListUsers::class);
        
        // Test initial load
        $component->assertSuccessful();
        
        // Test sorting
        $component->sortTable('name')
            ->assertSuccessful();

        // Verify both users are still visible after sorting
        $component->assertCanSeeTableRecords([$userA, $userB]);
    }
}