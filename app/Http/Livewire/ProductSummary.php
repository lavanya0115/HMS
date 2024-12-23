<?php

namespace App\Http\Livewire;

use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use Illuminate\Http\Request;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class ProductSummary extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';

    public $productId = null;

    #[Url(as: 'pp')]
    public $perPage = 10;

    #[Url(as: 's')]
    public $search;


    protected $listeners = [
        'deleteProduct' => 'deleteProductById',
    ];

    public function deleteProductById($productId)
    {

        $product = Product::find($productId);
        if (auth()->guard('exhibitor')->check()) {
            $product->update([
                "deleted_by" => null,
            ]);
        } else {

            $product->update([
                "deleted_by" => getAuthData()->id,
            ]);
        }
        if ($product) {

            $isDeleted = $product->delete();
            if ($isDeleted) {
                session()->flash("success", "Product deleted successfully");
                if (auth()->guard('exhibitor')->check()) {
                    return redirect(route("myproducts", ['pp' => $this->perPage, 'page' => $this->paginators['page']]));
                } else {
                    return redirect(route("products", ['pp' => $this->perPage, 'page' => $this->paginators['page']]));
                }
            } else {
                session()->flash("error", "Unable to delete Product");
                return;
            }
        }
    }

    public function changePageValue($perPageValue)
    {
        $this->perPage = $perPageValue;
        $this->resetPage();
    }

    public function mount(Request $request)
    {
        $this->productId = $request->productId;
    }

    public function render()
    {
        $products = Product::when($this->search !== null, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })->orderBy('id', 'desc')->paginate($this->perPage);

        $productTags = [];

        foreach ($products as $index => $product) {
            if (empty($product->tags) || count($product->tags) == 0) {
                continue;
            }
            $tagNames = Category::where('type', 'product_tags')->whereIn('id', $product->tags)->pluck('name');
            if (isset($tagNames) && count($tagNames) > 0) {
                $tagNames = $tagNames->toArray();
                $productTags[$product->id] = $tagNames;
            }
        }


        // $activities = Activity::where('log_name', 'product_log')->orderBy('id', 'desc')->get();


        return view('livewire.product-summary', [
            'products' => $products,
            'productTags' => $productTags,
            // 'activities' => $activities,
        ])->layout("layouts.admin");
    }
}
