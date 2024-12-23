<?php

namespace App\Http\Livewire;

use App\Models\Category;
use Illuminate\Http\Request;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class CategorySummary extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';

    public $categoryId = null;

    public $type = null;

    public $categoryType = null;

    #[Url( as :'pp')]
    public $perPage = 10;

    protected $listeners = [

        'deleteCategory' => 'deleteCategoryById',
    ];

    public function mount(Request $request)
    {
        $categoryTypes = getCategoryTypes();
        if (!array_key_exists($request->type, $categoryTypes)) {
            return redirect()->back()->with('error', 'Invalid category type.');
        }
        $this->type = $request->type;
        $this->categoryId = $request->categoryId;
        $this->categoryType = $categoryTypes[$this->type];
    }
    public function deleteCategoryById($categoryId)
    {

        $category = Category::find($categoryId);
        $category->update([
            'deleted_by' => getAuthData()->id,
        ]);
        if ($category) {
            $isDeleted = $category->delete();
            if ($isDeleted) {
                session()->flash("success", $this->categoryType . " deleted successfully!.");
                return redirect(route('category', ['type' => $this->type]));
            } else {
                session()->flash("error", "Unable to delete category");
                return;
            }
        }
    }

    public function changePageValue($perPageValue)
    {
        $this->perPage = $perPageValue;
        $this->resetPage();
    }

    public function render()
    {
        $categories = Category::where('type', $this->type)->orderBy('id', 'desc')->paginate($this->perPage);

        $activities = Activity::select('activity_log.*')->where('log_name', 'category_log')
            ->join('categories', 'activity_log.subject_id', '=', 'categories.id')
            ->where('categories.type', $this->type)
            ->orderBy('activity_log.id', 'desc')->paginate(10, pageName: 'activity');

        return view('livewire.category-summary', [
            'categories' => $categories,
            'activities' => $activities,
        ])->layout('layouts.admin');
    }
}
