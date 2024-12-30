<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\MenuItem;
use Illuminate\Support\Carbon;

class MenuCard extends Component
{
    public function render()
    {
        $currentHour = Carbon::now()->hour;
        $currentCategory = '';
        if ($currentHour >= 8 && $currentHour < 12) {
            $currentCategory = 'Break fast';
        } elseif ($currentHour >= 12 && $currentHour < 17) {
            $currentCategory = 'Lunch';
        } else {
            $currentCategory = 'Dinner';
        }

        $menuItems = MenuItem::with('category')
            ->whereHas('category', function ($query) use ($currentCategory) {
                $query->where('title', $currentCategory);
            })
            ->orWhereHas('category', function ($query) {
                $query->where('title', 'Refreshment');
            })
            ->orderByDesc('is_available')
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
