<?php

namespace App\Http\Livewire;

use App\Models\Category;
use Livewire\Component;

class CategoryHandler extends Component
{
    public $categoryId = null;
    public $categoryType = null;
    public $types;
    public $category = [
        'title',
        'type',
        'show_time_from',
        'show_time_to',
        'description',
        'is_active' => true,
    ];

    protected $rules = [
        'category.title' => 'required|string',
        'category.type' => 'required|string',
    ];

    protected $messages = [
        'category.title.required' => 'Name is required',
        'category.type.required' => 'Name is required',
    ];

    public function mount($categoryId = null)
    {
        $this->categoryId = $categoryId;
        $this->types = Category::whereNotNull('type')->pluck('type', 'id');
        if ($this->categoryId) {
            // $this->authorize('Update Category');
            $category = Category::find($this->categoryId);
            $this->category = $category ? $category->toArray() : [];
            $this->category['is_active'] = $category->is_active ? true : false;
            $this->category['type'] = $category->type;
        }
    }
    public function create()
    {
        $this->validate();
        $categoryExists = Category::where('title', $this->category['title'])
            ->first();
        if ($categoryExists) {
            $this->addError("category.title", "Name already exists");
            return;
        }
        $this->category['created_by'] = getAuthData()->id;
        $this->category['updated_by'] = getAuthData()->id;

        try {
            $category = Category::create($this->category);
            if ($category) {
                session()->flash('success', 'Category created successfully!.');
                return redirect(route('category'));
            }
            session()->flash('error', 'Error while creating category');
            return;
        } catch (\Exception $e) {
            session()->flash("error", $e->getMessage());
            return;
        }
    }

    public function update()
    {
        $this->validate();

        $categoryNameExists = Category::where('title', $this->category['title'])
            ->where('id', '!=', $this->category['id'])->first();

        if ($categoryNameExists) {
            $this->addError("category.title", "Name already exists");
            return;
        }

        try {
            $category = Category::find($this->categoryId);

            if ($category) {
                $this->category['updated_by'] = getAuthData()->id;
                $category->update($this->category);
                $isCategoryUpdated = $category->wasChanged('title', 'description');
                if ($isCategoryUpdated) {
                    return redirect(route('category',))->with('success', 'Category updated successfully!.');
                }
                return session()->flash('info', 'Do Some Changes to be Updated');
            }
            session()->flash('error', 'Error while creating category');
            return;
        } catch (\Exception $e) {
            session()->flash("error", $e->getMessage());
            return;
        }
    }

    public function resetFields()
    {
        $this->categoryId = null;
        $this->category = [

            'name' => null,
            'description' => null,

        ];
    }

    public function render()
    {
        return view('livewire.category-handler')->layout('layouts.admin');
    }
}
