<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\MenuItem;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Illuminate\Http\Request;

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
}
