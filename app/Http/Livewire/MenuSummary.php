<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class MenuSummary extends Component
{
    use WithPagination;
    protected $listeners = [
        'callShowNoticeEvent' => 'showNoticeListener',
        'deleteMenu' => 'deleteMenuById',
        'deleteSelected' => 'deleteSelectedMenuIds',
    ];

    protected $paginationTheme = 'bootstrap';
    #[Url(as: 'pp')]
    public $perPage = 10;

    public $menuId = null;
    // public $menuItemsAll;

    public $selectedItems = [];
    public $selectedAll = false;

    public $menu = [
        'is_available',
        'custom_status',
    ];

    public function toggleSelectAll()
    {
        if ($this->selectedAll) {
            $this->selectedItems = $this->getMenuItems()->pluck('id')->toArray();
        } else {
            $this->selectedItems = [];
        }
    }
    // public function updatedSelectedItems()
    // {
    //     dd('kl',  $this->selectedAll, $this->selectedItems);
    //     $this->selectedAll = count($this->selectedItems) === $this->getMenuItems()->count();
    //     dd($this->selectedItems);
    // }
    public function getSelectedItems()
    {
        $this->selectedItems;
        // dd($this->selectedItems);
        $menus = MenuItem::whereIn('id', $this->selectedItems)->get();
        $this->menu['is_available'] = $menus->pluck('is_available')->first() ? true : false;
        $this->menu['custom_status'] = $menus->pluck('custom_status')->first();
    }

    public function deleteSelectedMenuIds()
    {
        // dd( $this->selectedItems);
        $menuItems = MenuItem::whereIn('id', $this->selectedItems)->get();
        if ($menuItems) {
            foreach ($menuItems as $menu) {
                $menu->update([
                    'deleted_by' => getAuthData()->id,
                ]);
                $menu->delete();
            }
        }
        $this->selectedItems = [];
        $this->selectedAll = false;
        session()->flash('message', 'Selected items deleted successfully.');
    }

    public function updateStatus()
    {
        // dd($this->menu);
        $menuItems = MenuItem::whereIn('id', $this->selectedItems)->get();
        if ($menuItems) {
            foreach ($menuItems as $item) {
                $item->update([
                    'is_available' => $this->menu['is_available'] ?? 0,
                    'custom_status' => $this->menu['custom_status'] ?? null,
                ]);
                $isUpdated = $item->wasChanged('is_available', 'custom_status');
            }

            if ($isUpdated) {
                session()->flash('success', 'Selected items updated successfully.');
                return redirect(route('menu.items.list'));
            } else {
                session()->flash("error", "Unable to update the menus");
                return redirect(route('menu.items.list'));
            }
        }
    }


    public function mount(Request $request)
    {
        reLogin();
        $this->menuId = $request->menuId ?? null;
    }
    public function showNoticeListener($status, $message)
    {
        session()->flash($status, $message);
    }

    public function getMenuItems()
    {
        $menuItems = MenuItem::orderBy('name')
            ->paginate($this->perPage);
        return $menuItems;
    }
    public function render()
    {
        $user = getAuthData();
        $menuItems = $this->getMenuItems();
        return view('livewire.menu-summary', [
            'menuItems' => $menuItems,
        ])->layout('layouts.admin');
    }

    public function changePageValue($perPageValue)
    {
        $this->perPage = $perPageValue;
        $this->resetPage(pageName: 'p');
    }
    public function deleteMenuById($menuId)
    {

        $menu = MenuItem::find($menuId);
        $menu->update([
            'deleted_by' => getAuthData()->id,
        ]);
        if ($menu) {
            $isDeleted = $menu->delete();
            if ($isDeleted) {
                session()->flash("success",  " deleted successfully!.");
                return redirect(route('menu.items.list'));
            } else {
                session()->flash("error", "Unable to delete menu");
                return;
            }
        }
    }

    public function import($data)
    {
        $authId = getAuthData()->id;
        $updatedCount = 0;
        $insertedCount = 0;

        $headings = $data[0];
        $rows = array_slice($data, 1);
        foreach ($rows as $index => $row) {
            $menuData = array_combine($headings, $row);

            if(empty($menuData['Item Name (En)'])){
                session()->flash('warning', "Item Name in English is empty in $index row. Check and Re-upload");
                return redirect()->route('menu.items.list');
            }
            if(empty($menuData['Item Name (Ka)'])){
                session()->flash('warning', "Item Name in Kannada is empty in $index row. Check and Re-upload");
                return redirect()->route('menu.items.list');
            }
            if(empty($menuData['Category Title'])){
                session()->flash('warning', "Category Title is empty in $index row. Check and Re-upload");
                return redirect()->route('menu.items.list');
            }
            if(empty($menuData['Category Type'])){
                session()->flash('warning', "Category Type is empty in $index row. Check and Re-upload");
                return redirect()->route('menu.items.list');
            }
            if(empty($menuData['Qty'])){
                session()->flash('warning', "Qty is empty in $index row. Check and Re-upload");
                return redirect()->route('menu.items.list');
            }
            if(empty($menuData['Unit Type'])){
                session()->flash('warning', "Unit Type is empty in $index row. Check and Re-upload");
                return redirect()->route('menu.items.list');
            }
            if(empty($menuData['Basic Price'])){
                session()->flash('warning', "Basic Price is empty in $index row. Check and Re-upload");
                return redirect()->route('menu.items.list');
            }
            if(empty($menuData['Tax %'])){
                session()->flash('warning', "Tax% is empty in $index row. Check and Re-upload");
                return redirect()->route('menu.items.list');
            }
            // $menu = MenuItem::where('name', 'like', '%' . $menuData['Item Name (En)'] . '%')
            //     ->first();
            $category = Category::where('title', 'like', '%' . $menuData['Category Title'] . '%')->where('type', 'like', '%' . $menuData['Category Type'] . '%')->first();
            $unitTypeExists = Category::where('type', 'unit_type')->where('title', 'like', '%' . $menuData['Unit Type'] . '%')->exists();

            if (!$unitTypeExists) {
                $unitType = Category::create([
                    'title' => $menuData['Unit Type'],
                    'type' => 'unit_type',
                    'is_active' => 1,
                    'created_by' => $authId,
                    'updated_by' => $authId,
                ]);
            }

            if (!$category) {
                $category = Category::create([
                    'title' => $menuData['Category Title'],
                    'type' => lcfirst($menuData['Category Type']),
                    'is_active' => 1,
                    'created_by' => $authId,
                    'updated_by' => $authId,
                ]);
            }


            $menuExists = MenuItem::where('name', 'like', '%' . $menuData['Item Name (En)'] . '%')
                ->where('category_id', $category->id)
                ->first();
            if (!empty($menuData['Tax %'])) {
                $taxRate = $menuData['Tax %'] / 100;
                $menuData['tax_amount'] = $taxRate;
                $price = $menuData['Basic Price'];
                $total = $price + ($price * $taxRate);
                $menuData['mrp'] = round($total);
            }
            // dd($menuExists);
            if ($menuExists) {
                $menuExists->update([
                    'name'         => $menuData['Item Name (En)'],
                    'kannada_name' => $menuData['Item Name (Ka)'],
                    'category_id'  => $category->id,
                    'qty' => $menuData['Qty'],
                    'unit_type' => $menuData['Unit Type'],
                    'price' => $menuData['Basic Price'],
                    'tax' => $menuData['Tax %'],
                    'tax_amount' =>  $menuData['tax_amount'],
                    'mrp' => $menuData['mrp'],
                    'is_available' => 1,
                    'description' => $menuData['Description'],
                    // 'custom_status' => $menu->custom_status,
                    'updated_by'       => $authId,
                ]);
                $isUpdated = $menuExists->wasChanged([
                    'name',
                    'kannada_name',
                    'category_id',
                    'qty',
                    'unit_type',
                    'price',
                    'tax',
                    'tax_amount',
                    'mrp',
                    'is_available',
                    'custom_status',
                    'updated_by',
                ]);
                if ($isUpdated) {
                    $updatedCount++;
                }
            } else {
                $menu = MenuItem::create([
                    'name'         => $menuData['Item Name (En)'],
                    'kannada_name' => $menuData['Item Name (Ka)'],
                    'category_id'  => $category->id,
                    'qty' => $menuData['Qty'],
                    'unit_type' =>  $menuData['Unit Type'],
                    'price' => $menuData['Basic Price'],
                    'tax' => $menuData['Tax %'],
                    'tax_amount' =>  $menuData['tax_amount'],
                    'mrp' => $menuData['mrp'],
                    'is_available' => 1,
                    'description' => $menuData['Description'],
                    // 'custom_status' => $menu->custom_status,
                    'updated_by'       => $authId,
                    'created_by'       => $authId,
                ]);
                // dd($menu);
                if ($menu) {
                    $insertedCount++;
                }
            }
        }

        $messages = [];
        if ($insertedCount > 0) {
            $messages[] = "$insertedCount menus inserted.";
        }
        if ($updatedCount > 0) {
            $messages[] = "$updatedCount menus updated.";
        }

        session()->flash('success', implode(' ', $messages) ?: 'No changes made.');
        return redirect()->route('menu.items.list');
    }
}
