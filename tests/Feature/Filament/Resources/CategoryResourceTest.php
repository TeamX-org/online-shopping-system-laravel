<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Livewire\Livewire;

class CategoryResourceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate admin user
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        // Setup S3 disk for testing
        Storage::fake('s3');
    }

    /** @test */
    public function can_view_category_list()
    {
        // Arrange
        $categories = Category::factory()->count(3)->create();

        // Act & Assert
        Livewire::test(CategoryResource\Pages\ListCategories::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($categories);
    }

    /** @test */
    public function can_search_categories()
    {
        // Arrange
        $categoryToFind = Category::factory()->create(['name' => 'Special Category']);
        $otherCategory = Category::factory()->create(['name' => 'Other Category']);

        // Act & Assert
        Livewire::test(CategoryResource\Pages\ListCategories::class)
            ->searchTable('Special')
            ->assertCanSeeTableRecords([$categoryToFind])
            ->assertCanNotSeeTableRecords([$otherCategory]);
    }

    /** @test */
    public function can_create_category()
    {
        // Arrange
        $newCategory = Category::factory()->make();

        // Act & Assert
        Livewire::test(CategoryResource\Pages\CreateCategory::class)
            ->fillForm([
                'name' => $newCategory->name,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Assert category was created
        $this->assertDatabaseHas('categories', [
            'name' => $newCategory->name,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function slug_is_automatically_generated()
    {
        // Act & Assert
        Livewire::test(CategoryResource\Pages\CreateCategory::class)
            ->fillForm([
                'name' => 'Test Category Name',
            ])
            ->assertSet('data.slug', 'test-category-name');
    }

    /** @test */
    public function validates_required_fields_when_creating()
    {
        // Act & Assert
        Livewire::test(CategoryResource\Pages\CreateCategory::class)
            ->fillForm([
                'name' => '',
                'is_active' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'is_active' => 'required',
            ]);
    }

    /** @test */
    public function validates_unique_slug()
    {
        // Arrange
        $existingCategory = Category::factory()->create();
        $newCategory = Category::factory()->make(['name' => $existingCategory->name]);

        // Act & Assert
        Livewire::test(CategoryResource\Pages\CreateCategory::class)
            ->fillForm([
                'name' => $newCategory->name,
                'slug' => $existingCategory->slug,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    /** @test */
    public function can_edit_category()
    {
        // Arrange
        $category = Category::factory()->create();

        // Act & Assert
        Livewire::test(CategoryResource\Pages\EditCategory::class, [
            'record' => $category->id,
        ])
            ->fillForm([
                'name' => 'Updated Name',
                'is_active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        // Assert category was updated
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
            'is_active' => false,
        ]);
    }

    /** @test */
    public function can_delete_category()
    {
        // Arrange
        $category = Category::factory()->create();

        // Act & Assert
        Livewire::test(CategoryResource\Pages\ListCategories::class)
            ->callTableAction('delete', $category)
            ->assertSuccessful();

        // Assert category was deleted
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    /** @test */
    public function can_bulk_delete_categories()
    {
        // Arrange
        $categories = Category::factory()->count(3)->create();

        // Act & Assert
        Livewire::test(CategoryResource\Pages\ListCategories::class)
            ->assertCanSeeTableRecords($categories)
            ->assertCountTableRecords(3)
            ->callTableBulkAction('delete', $categories)
            ->assertSuccessful();

        // Assert categories were deleted
        foreach ($categories as $category) {
            $this->assertDatabaseMissing('categories', [
                'id' => $category->id,
            ]);
        }
    }

    /** @test */
    public function can_view_category()
    {
        // Arrange
        $category = Category::factory()->create();

        // Act & Assert
        Livewire::test(CategoryResource\Pages\ListCategories::class)
            ->callTableAction('view', $category)
            ->assertSuccessful();
    }

    /** @test */
    public function image_is_optional()
    {
        // Arrange
        $newCategory = Category::factory()->make();

        // Act & Assert
        Livewire::test(CategoryResource\Pages\CreateCategory::class)
            ->fillForm([
                'name' => $newCategory->name,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Assert category was created without image
        $this->assertDatabaseHas('categories', [
            'name' => $newCategory->name,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function can_toggle_is_active()
    {
        // Arrange
        $category = Category::factory()->create(['is_active' => true]);

        // Act & Assert
        Livewire::test(CategoryResource\Pages\EditCategory::class, [
            'record' => $category->id,
        ])
            ->fillForm([
                'is_active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        // Assert is_active was toggled
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'is_active' => false,
        ]);
    }
}