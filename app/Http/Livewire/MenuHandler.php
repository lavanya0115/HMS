<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Category;

class MenuHandler extends Component
{
    public $categories;

    public $menu = [
        'name' => '',
        'category_id' => '',
        'nos' => '',
        'price' => 0,
        'is_available' => 1,
        'description' => '',
    ];

    protected $rules = [
        'menu.name' => 'required|string',
        'menu.category_id' => 'required',
        'menu.nos' => 'required|digits:10',
        'menu.price' => 'required|digits:10',
    ];

    protected $messages = [
        'menu.name.required' => 'The menu name field is required.',
        'menu.category_id.required' => 'The menu category filed is required.',
        'menu.nos.required' => 'The number of item field is required.',
        'menu.nos.digits' => 'Please give the valid no',
        'menu.price.required' => 'The price field is required.',
    ];

    public function mount($menuId)
    {
        $user = getAuthData();

        $this->categories = Category::get();

        if ($menuId) {

            $menu = MenuItem::find($menuId);
            if ($menu) {
                $this->menu = $menu->toArray();
            } else {
                return redirect()->back()->with('warning', 'menu not found');
            }
        }
    }

    public function create()
    {
        $this->validate();

        $menuExists = MenuItem::where('name', $this->menu['name'])
            ->where('category_id', $this->menu['category_id'])->first();

        if ($menuExists) {
            $this->addError('menu.name', 'menu already exists.');
            return;
        }


        $authorId = auth()->user()->id;
        $this->menu['created_by'] = $authorId;
        $this->menu['updated_by'] = $authorId;


        // try {
        $menu = MenuItem::create($this->menu);

        if ($menu) {
            session()->flash('success', 'menu created successfully.');
            // $this->dispatch('callShowNoticeEvent', 'Success', 'menu created successfully.');
            return redirect()->route('menu.items.create');
        }
        session()->flash('info', 'Something went wrong menu not created');
        // $this->dispatch('callShowNoticeEvent', 'info', 'Something went wrong menu not created');
        // } catch (\Exception $e) {
        //     session()->flash('error', $e->getMessage());

        // }
    }

    public function update()
    {
        $this->validate();

        $menuExists = MenuItem::where('name', $this->menu['name'])
            ->where('id', '!=', $this->menu['id'])->first();
        if ($menuExists) {
            $this->addError('menu.name', 'menu Name already exists.');
            return;
        }


        $authorId = auth()->user()->id;
        $this->menu['updated_by'] = $authorId;



        try {
            $menu = MenuItem::find($this->menu['id']);

            if (!$menu) {
                session()->flash('warning', 'menu not found');
                $this->dispatch('callShowNoticeEvent', 'warning', 'menu not found');
                return;
            }
            $menu->update($this->menu);

            session()->flash('success', 'menu updated successfully.');
            $this->dispatch('callShowNoticeEvent', 'success', 'menu updated successfully.');
            $this->redirect(route('menus.index'));
        } catch (\Exception $e) {
            $this->dispatch('callShowNoticeEvent', 'error', $e->getMessage());
            return;
        }
    }
    public function render()
    {
        return view('livewire.menu-handler')->layout('layouts.admin');;
    }
}
