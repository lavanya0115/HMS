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
        $currentday  = Carbon::now()->format('l');
        $currentCategory = '';
        if ($currentHour >= 5 && $currentHour <= 12) {
            $currentCategory = 'Break fast';
        } elseif ($currentHour >= 12 && $currentHour <= 17) {
            $currentCategory = 'Lunch';
        } else {
            $currentCategory = 'Dinner';
        }

        $menuItems = MenuItem::with('category')
            ->whereHas('category', function ($query) use ($currentCategory, $currentday) {
                $query->where('day', $currentday)
                    ->where('show_time_from', '<=', now()->format('H:i'))
                    ->where('show_time_to', '>=', now()->format('H:i'));
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
