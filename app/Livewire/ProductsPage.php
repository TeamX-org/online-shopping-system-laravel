<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Products - Cosmetics')]

class ProductsPage extends Component
{
    use WithPagination;

    public function render()
    {   
        $productQuery = Product::query()->where('is_active', 1);
        $categories = Category::where('is_active', 1)->get(['id', 'name', 'slug']);
        $brands = Brand::where('is_active', 1)->get(['id', 'name', 'slug']);

        return view('livewire.products-page', [
            'products' => $productQuery->paginate(9),
            'categories' => $categories,
            'brands' => $brands
        ]);
    }
}
