<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\MenuItem;

class MenuCard extends Component
{
    public function render()
    {
        $menuItems = MenuItem::with('category')
            ->where('is_available', true)
            ->get()
            ->groupBy('category.title');

        return view(
            'livewire.menu-card',
            [
                'menuItems' => $menuItems
            ]
        )->layout('layouts.guest');;
    }
}
