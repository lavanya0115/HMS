<?php

namespace App\Http\Livewire;

use App\Models\Category;
use Livewire\Component;

class CategoryHandler extends Component
{
    public $categoryId = null;
    public $categoryType = null;

    public $category = [
        'type',
        'name',
        'description',
        'is_active' => 0,
        'is_default' => 0,

    ];

    protected $rules = [
        'category.name' => 'required|string',
        'category.type' => 'required',
    ];

    protected $messages = [
        'category.name.required' => 'Name is required',
        'category.type.required' => 'Type is required',
    ];

    public function mount($categoryId = null, $type = null)
    {
        $categoryTypes = getCategoryTypes();
        if (!array_key_exists($type, $categoryTypes)) {
            return redirect()->back()->with('error', 'Invalid category type.');
        }
        $this->categoryId = $categoryId;
        $this->category['type'] = $type;
        $this->categoryType = $categoryTypes[$type];
        if ($this->categoryId) {
            $this->authorize('Update Category');
            $category = Category::find($this->categoryId);
            $this->category = $category ? $category->toArray() : [];
            $this->category['is_active'] = $this->category['is_active'] == 1 ? true : false;
        }
    }
    public function create()
    {
        $this->authorize('Create Category');
        $this->validate();
        $categoryExists = Category::where('name', $this->category['name'])->where('type', $this->category['type'])->first();
        if ($categoryExists) {
            $this->addError("category.name", "Name already exists");
            return;
        }
        $this->category['created_by'] = getAuthData()->id;
        $this->category['updated_by'] = getAuthData()->id;

        try {
            $category = Category::create($this->category);
            if ($category) {
                session()->flash('success', $this->categoryType . ' created successfully!.');
                return redirect(route('category', ['type' => $this->category['type']]));
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

        $categoryNameExists = Category::where('name', $this->category['name'])->where('type', $this->category['type'])
            ->where('id', '!=', $this->category['id'])->first();

        if ($categoryNameExists) {
            $this->addError("category.name", "Name already exists");
            return;
        }

        try {
            $category = Category::find($this->categoryId);

            if ($category) {
                $this->category['updated_by'] = getAuthData()->id;
                $category->update($this->category);
                $isCategoryUpdated = $category->wasChanged('name', 'type', 'description', 'is_active');
                if ($isCategoryUpdated) {
                    return redirect(route('category', ['type' => $this->category['type']]))->with('success', $this->categoryType . ' updated successfully!.');
                }
                session()->flash('info', 'Do Some Changes to be Updated');
                return;
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
            'type' => null,
            'name' => null,
            'description' => null,
            'is_active' => 1,
            'is_default' => 0,
        ];
    }

    public function render()
    {
        return view('livewire.category-handler')->layout('layouts.admin');
    }

}
