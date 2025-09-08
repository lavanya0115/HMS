<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Category;
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

        $categoryMenuTimings = Category::where('type', 'menu')
            ->where('is_active', 1)
            ->where('day', $currentday)
            ->get(['title', 'show_time_from', 'show_time_to'])
            ->mapWithKeys(function ($item) {
                return [
                    $item->title => [
                        'show_time_from' => $item->show_time_from,
                        'show_time_to'   => $item->show_time_to,
                    ]
                ];
            })
            ->toArray();

        // dd($categoryMenuTimings);

        $menuItemsKannada = MenuItem::with('category')
            ->whereHas('category', function ($query) use ($currentCategory, $currentday) {
                $query->where('is_active', 1)
                    ->where('day', $currentday)
                    ->where('show_time_from', '<=', now()->format('H:i'))
                    ->where('show_time_to', '>=', now()->format('H:i'));
            })
            ->orderByDesc('is_available')
            ->paginate(10)
            ->groupBy('category.title');

       $menuItemsEnglish = MenuItem::with('category')
        ->whereHas('category', function ($query) use ($currentday) {
            $query->where('day', $currentday)
                ->where('is_active', 1)
                ->where('show_time_from', '<=', now()->format('H:i'))
                ->where('show_time_to', '>=', now()->format('H:i'));
        })
        ->orderByDesc('is_available')
        ->take(10)
        ->get()
        ->groupBy(fn($item) => $item->category->title) // group by category (Lunch/Dinner)
        ->map(function ($items) {
            return $items->groupBy(function ($item) {
                $meta = json_decode($item->meta, true);
                return $meta['variety'] ?? 'Others'; // group by variety
            });
        });
        //  if($meta != null){
                    // dd($menuItemsEnglish);
                // }

        return view(
            'livewire.menu-card',
            [
                'menuItemsKannada' => $menuItemsKannada,
                'menuItemsEnglish' => $menuItemsEnglish,
                'timings'   => $categoryMenuTimings
            ]
        )->layout('layouts.guest');;
    }
}
