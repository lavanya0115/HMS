<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Request;

class MenuHandler extends Component
{
    public $categories;
    public $menu = [
        'name' => '',
        'kannada_name' => '',
        'category_id' => '',
        'qty' => '',
        'unit_type' => '',
        'price' => 0,
        'is_available' => true,
        'custom_status' => '',
        'description' => '',
        'tax' => '',
        'tax_amount' => '',
        'mrp' => '',
    ];

    protected $rules = [
        'menu.name' => 'required|string',
        'menu.kannada_name' => 'required|string',
        'menu.category_id' => 'required',
        'menu.qty' => 'required',
        'menu.price' => 'required',
    ];

    protected $messages = [
        'menu.name.required' => 'The menu name field is required.',
        'menu.kannada_name.required' => 'This field is required.',
        'menu.category.required' => 'The menu category filed is required.',
        'menu.qty.required' => 'The number of item field is required.',
        'menu.price.required' => 'The price field is required.',
    ];

    public function updatedMenuTax($value)
    {
        if (!empty($value)) {
            $taxRate = $value / 100;
            $this->menu['tax_amount'] = $taxRate;
            $price = $this->menu['price'];
            $total = $price + ($price * $taxRate);
            $this->menu['mrp'] = round($total);
            // dd($taxRate, $total);
        }
    }
    public function mount($menuId)
    {
        $this->categories = Category::get();
        if ($menuId) {
            $menu = MenuItem::find($menuId);
            // dd($menu);
            if ($menu) {
                $this->menu = $menu->toArray();
                $this->menu['is_available'] = $menu->is_available ? true : false;
            } else {
                return redirect()->back()->with('warning', 'menu not found');
            }
        }
    }

    public function create()
    {
        $this->validate();

        if ($this->menu['is_available']  == false && empty($this->menu['custom_status'])) {
            return $this->addError('menu.custom_status', 'The custom status field is required.');
        }
        $menuExists = MenuItem::where('name', $this->menu['name'])
            ->where('category_id', $this->menu['category_id'])->first();

        if ($menuExists) {
            $this->addError('menu.name', 'menu already exists.');
            return;
        }
        $authorId = auth()->user()->id;
        $this->menu['created_by'] = $authorId;
        $this->menu['updated_by'] = $authorId;
        try {
            $menu = MenuItem::create($this->menu);

            if ($menu) {
                session()->flash('success', 'menu created successfully.');
                return redirect()->route('menu.items.list');
            }
            session()->flash('info', 'Something went wrong menu not created');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->route('menu.items.list');
        }
    }

    public function update()
    {
        $this->validate();

        if ($this->menu['is_available'] == false && empty($this->menu['custom_status'])) {
            return $this->addError('menu.custom_status', 'The custom status field is required.');
        }

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
            $this->redirect(route('menu.items.list'));
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
