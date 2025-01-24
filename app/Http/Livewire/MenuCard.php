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
        if ($currentHour >= 5 && $currentHour <= 12) {
            $currentCategory = 'Break fast';
        } elseif ($currentHour >= 12 && $currentHour <= 17) {
            $currentCategory = 'Lunch';
        } else {
            $currentCategory = 'Dinner';
        }
        // dd($currentCategory, $currentHour);


        $menuItems = MenuItem::with('category')
            ->whereHas('category', function ($query) use ($currentCategory) {

                $query
                ->whereIn('type', ['menu', 'starters', 'drinks'])
                    ->where('title', $currentCategory)
                    ->where('show_time_from', '>=', now())
                    ->orWhere('show_time_to', '<=', now());
            })
            // ->orWhereHas('category', function ($query) {

            //     $query
            //     // ->whereIn('type', ['menu', 'starters', 'drinks'])
            //         ->where('title', 'Refreshment')
            //         ->orWhere('show_time_from', '<=', now())
            //         ->orWhere('show_time_to', '>=', now());
            // })
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
