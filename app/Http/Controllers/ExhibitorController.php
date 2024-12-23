<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Event;
use App\Models\Product;
use App\Models\Category;
use App\Models\Exhibitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ExhibitorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::pluck('title', 'id');
        $categories = Category::where('type', 'exhibitor_business_type')
        ->where('is_active', 1)
        ->get();
        $products = Product::pluck('name', 'id');
        $countries = getCountries();
        
        return view('iframe.exhibitor', compact('events', 'categories', 'products', 'countries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation rules
        $rules = [
            'eventId' => 'required|exists:events,id',
            'salutation' => 'required|in:Dr,Mr,Ms,Mrs',
            'name' => 'required|string|max:255',
            'mobileNumber' => 'required|string|max:20',
            'designation' => 'required|string|max:255',
            'companyName' => 'required|string|max:255',
            'categoryId' => 'required|exists:categories,id',
            'username' => 'required|string|max:255|unique:exhibitors,username',
            'email' => 'required|email|max:255|unique:exhibitors,email',
            'contactNumber' => 'required|string|max:20',
            'products' => 'required|array',
            'products.*' => 'integer|exists:products,id',
            'knownSource' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'pincode' => 'required|string|max:20',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'address' => 'required|string|max:500',
            'newsletter' => 'boolean',
        ];
    
        // Validation messages
        $messages = [
            'products.*.exists' => 'Invalid product selected.',
        ];
    
        // Validate the request
        $validator = Validator::make($request->all(), $rules, $messages);
    
        // Check if validation fails
        if ($validator->fails()) {
            return redirect()
                ->route('exhibitor.show')
                ->withErrors($validator)
                ->withInput($request->all());
        }
    
        try {
            DB::beginTransaction();
    
            // Process the request
    
            $productList = $request->products ?? [];
            $selectedProducts = [];
    
            foreach ($productList as $product) {
                $selectedProducts[] = is_numeric($product) ? $product : Product::create(['name' => $product])->id;
            }
    
            $exhibitor = Exhibitor::create([
                'username' => $request->username,
                'name' => $request->companyName,
                'category_id' => $request->categoryId,
                'email' => $request->email,
                'mobile_number' => $request->mobileNumber,
                'known_source' => $request->knownSource,
                'registration_type' => 'web',
                'newsletter' => $request->newsletter ? true : false,
                'password' => Hash::make('password'),
            ]);
    
            $exhibitor->exhibitorContact()->create([
                'salutation' => $request->salutation,
                'name' => $request->name,
                'contact_number' => $request->contactNumber,
                'designation' => $request->designation,
            ]);
    
            $exhibitor->eventExhibitors()->create([
                'event_id' => $request->eventId,
                'products' => $selectedProducts,
            ]);
    
            $exhibitor->address()->create([
                'address' => $request->address,
                'pincode' => $request->pincode,
                'city' => $request->city ?? null,
                'state' => $request->state ?? null,
                'country' => $request->country,
            ]);
    
            foreach ($selectedProducts as $productId) {
                $exhibitor->exhibitorProducts()->create([
                    'product_id' => $productId,
                ]);
            }
    
            $authData = getAuthData();
            $exhibitor->update(['created_by' => $authData ? $authData->id : null]);
    
            // TODO: Send Welcome Message
            sendWelcomeMessageThroughWhatsappBot($request->mobileNumber, 'exhibitor');
    
            DB::commit();
    
            session()->flash('success', 'Exhibitor Registered Successfully.');
    
            return redirect(route('exhibitor.show'));
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', $e->getMessage());
            return redirect()->back()->withInput();
        }
    }
    
    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
