<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Product Detail - Cosmetics')]

class ProductDetailPage extends Component
{

    public $slug;

    public function mount($slug) {
        $this->slug = $slug;
    }

    public function render()
    {
        $productQuery = Product::query()->where('slug', $this->slug)->firstOrFail();
        return view('livewire.product-detail-page', [
            'product' => $productQuery,
        ]);
    }
}
