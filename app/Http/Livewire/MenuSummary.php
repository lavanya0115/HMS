<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\MenuItem;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Request;

class MenuSummary extends Component
{
    use WithPagination;
    protected $listeners = [
        'callShowNoticeEvent' => 'showNoticeListener',
    ];

    protected $paginationTheme = 'bootstrap';
    #[Url(as: 'pp')]
    public $perPage = 10;

    public $menuId = null;

    public function mount(Request $request)
    {
        $this->menuId = $request->menuId ?? null;
    }
    public function showNoticeListener($status, $message)
    {
        session()->flash($status, $message);
    }
    public function render()
    {
        $user = getAuthData();

        $menuItems = MenuItem::orderBy('name')
            ->paginate($this->perPage, pageName: 'p');

        return view('livewire.menu-summary', [
            'menuItems' => $menuItems,
        ])->layout('layouts.admin');
    }

    public function changePageValue($perPageValue)
    {
        $this->perPage = $perPageValue;
        $this->resetPage(pageName: 'p');
    }
}
