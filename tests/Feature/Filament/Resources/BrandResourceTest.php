<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\BrandResource;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Livewire\Livewire;

class BrandResourceTest extends TestCase
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
    public function can_view_brand_list()
    {
        // Create brands
        $brands = Brand::factory()->count(2)->create();

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($brands);
    }

    /** @test */
    public function can_search_brands_by_name()
    {
        $brandToFind = Brand::factory()->create(['name' => 'Specific Brand']);
        $otherBrand = Brand::factory()->create(['name' => 'Other Brand']);

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->searchTable('Specific')
            ->assertCanSeeTableRecords([$brandToFind])
            ->assertCanNotSeeTableRecords([$otherBrand]);
    }

    /** @test */
    public function can_search_brands_by_slug()
    {
        $brandToFind = Brand::factory()->create(['name' => 'Test Brand', 'slug' => 'test-brand']);
        $otherBrand = Brand::factory()->create(['name' => 'Other Brand', 'slug' => 'other-brand']);

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->searchTable('test-brand')
            ->assertCanSeeTableRecords([$brandToFind])
            ->assertCanNotSeeTableRecords([$otherBrand]);
    }

    /** @test */
    public function can_create_brand()
    {
        $newBrand = [
            'name' => 'New Test Brand',
            'is_active' => true,
        ];

        Livewire::test(BrandResource\Pages\CreateBrand::class)
            ->fillForm($newBrand)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('brands', [
            'name' => $newBrand['name'],
            'slug' => 'new-test-brand',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function slug_is_automatically_generated()
    {
        Livewire::test(BrandResource\Pages\CreateBrand::class)
            ->fillForm([
                'name' => 'Test Brand Name',
            ])
            ->assertSet('data.slug', 'test-brand-name');
    }

    /** @test */
    public function validates_required_fields_when_creating()
    {
        Livewire::test(BrandResource\Pages\CreateBrand::class)
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
        $existingBrand = Brand::factory()->create();
        
        Livewire::test(BrandResource\Pages\CreateBrand::class)
            ->fillForm([
                'name' => $existingBrand->name,
                'slug' => $existingBrand->slug,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    /** @test */
    public function can_edit_brand()
    {
        $brand = Brand::factory()->create();
        
        $newData = [
            'name' => 'Updated Brand Name',
            'is_active' => false,
        ];

        Livewire::test(BrandResource\Pages\EditBrand::class, [
            'record' => $brand->id,
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'name' => $newData['name'],
            'is_active' => false,
        ]);
    }

    /** @test */
    public function can_delete_brand()
    {
        $brand = Brand::factory()->create();

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->assertSuccessful()
            ->callTableAction('delete', $brand);

        $this->assertDatabaseMissing('brands', [
            'id' => $brand->id,
        ]);
    }

    /** @test */
    public function can_bulk_delete_brands()
    {
        // Create brands to delete
        $brands = Brand::factory()->count(2)->create();
        
        // Get IDs for checking later
        $brandIds = $brands->pluck('id')->toArray();

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->assertSuccessful()
            ->callTableBulkAction('delete', $brands);

        // Verify brands were deleted
        foreach ($brandIds as $id) {
            $this->assertDatabaseMissing('brands', ['id' => $id]);
        }
    }

    /** @test */
    public function can_toggle_brand_active_status()
    {
        $brand = Brand::factory()->create(['is_active' => true]);

        Livewire::test(BrandResource\Pages\EditBrand::class, [
            'record' => $brand->id,
        ])
            ->fillForm([
                'is_active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function can_sort_brands()
    {
        $brands = Brand::factory()->count(2)->create();

        $component = Livewire::test(BrandResource\Pages\ListBrands::class);
        
        // Test initial load
        $component->assertSuccessful();
        
        // Test sorting
        $component->sortTable('name')
            ->assertSuccessful();

        // Verify records are still visible after sorting
        $component->assertCanSeeTableRecords($brands);
    }

    /** @test */
    public function image_is_optional()
    {
        $newBrand = [
            'name' => 'Brand Without Image',
            'is_active' => true,
        ];

        Livewire::test(BrandResource\Pages\CreateBrand::class)
            ->fillForm($newBrand)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('brands', [
            'name' => $newBrand['name'],
            'is_active' => true,
        ]);
    }
}