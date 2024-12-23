<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MasterController extends Controller
{
    public function showProducts(Request $request)
    {
        $search = $request->search;
        if (!$search) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Please enter search key'
            ], 404);
        }
        try {
            $products = Product::where('name', 'like', '%' . $search . '%')->get();
            if ($products->count() == 0) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'No product found'
                ], 404);
            }
            $data = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                ];
            });
            return response()->json([
                'status' => 'success',
                'data' => $data,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage()
            ], 404);

        }
    }

    public function addProducts(Request $request)
    {
        $newProduct = $request->product_name;
        if (!$newProduct) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Please enter new product'
            ], 404);
        }
        $checkProduct = Product::where('name', $newProduct)->first();
        if ($checkProduct) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Product already exists'
            ], 404);
        }
        try {
            $product = new Product();
            $product->name = $newProduct;
            $product->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Product added successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function getMasterData(Request $request)
    {
        $currentEvent = getCurrentEvent();
        if (!$currentEvent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current event not found',
            ], 404);
        }
        $events = Event::where('start_date', '>=', $currentEvent->start_date)->orderBy('start_date', 'asc')->get();
        $formattedEvents = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'thumbnail' => !empty($event->_meta['thumbnail']) ? asset('storage/' . ($event->_meta['thumbnail'])) : '',
                'exhibitorList' => !empty($event->_meta['exhibitorList']) ? asset('storage/' . ($event->_meta['exhibitorList'])) : '',
                'latitude' => $event->_meta['latitude'] ?? '',
                'longitude' => $event->_meta['longitude'] ?? '',
            ];
        });
        $businessTypes = Category::where('type', 'exhibitor_business_type')->get();
        $formattedBusinessTypes = $businessTypes->map(function ($type) {
            return [
                'id' => $type->id,
                'name' => $type->name,
            ];
        });

        $knownSources = getKnownSourceData();
        $knownSourcesCollection = collect($knownSources);
        $formattedKnownSources = $knownSourcesCollection->map(function ($name, $id) {
            return [
                'value' => $id,
                'label' => $name,
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'events' => $formattedEvents,
                'businessTypes' => $formattedBusinessTypes,
                'countries' => getCountries(),
                'knownSources' => $formattedKnownSources,
            ],
        ], 201);

    }
}
