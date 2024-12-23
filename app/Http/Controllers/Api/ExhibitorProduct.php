<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventExhibitor;
use App\Models\Exhibitor;
use App\Models\Product;

class ExhibitorProduct extends Controller
{
    public function getExhibitorProduct($eventId, $search)
    {
        $products = Product::where('name', 'like', '%' . $search . '%')->get();
        $eventExhibitorIds = [];
        if (count($products) > 0) {
            $eventExhibitorIds = EventExhibitor::where('event_id', $eventId)
                ->where(function ($subquery) use ($products) {
                    foreach ($products as $product) {
                        $subquery->orWhereJsonContains('products', strval($product->id));
                    }
                })
                ->pluck('exhibitor_id')
                ->toArray();
        }
        $exhibitors = Exhibitor::whereIn('id', $eventExhibitorIds)->get();
        return response()->json([
            'exhibitors' => $exhibitors,
        ]);
    }
}
