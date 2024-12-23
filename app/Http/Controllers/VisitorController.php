<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Product;
use App\Models\Visitor;
use App\Models\Address;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VisitorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::where('start_date', '>=', now()->format('Y-m-d'))
        ->orWhere('end_date', '>', now()->format('Y-m-d'))
        ->pluck('title', 'id');
        $categories = Category::where('type', 'visitor_business_type')->where('is_active', 1)->get();
        $products = Product::all();
        $countries = getCountries();

        return view('iframe.visitor',compact('events','categories','products','countries'));
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
        // dd($request->all());

        $request->validate([
            'eventId' => 'required',
            'username' => 'required|string|unique:visitors,username',
            'name' => 'required|regex:/^[a-zA-Z ]+$/',
            'email' => 'required|string|unique:visitors,email',
            'mobileNumber' => 'required|digits:10|unique:visitors,mobile_number',
            'designation' => 'required',
            'organization' => 'required',
            'pincode' => 'required',
            'categoryId' => 'required',
        ]);

        try {

            DB::beginTransaction();

            $visitor = Visitor::create([
                'username' => $request->username,
                'salutation' => preg_replace('/[^a-zA-Z0-9]/', '', $request->salutation),
                'name' => $request->name,
                'mobile_number' => $request->mobileNumber,
                'email' => $request->email,
                'category_id' => $request->categoryId,
                'organization' => $request->organization,
                'designation' => $request->designation,
                'known_source' => $request->knownSource,
                'reason_for_visit' => $request->visitReason,
                'newsletter' => $request->newsletter ? true : false,
                'proof_type' => null,
                'proof_id' => null,
                'registration_type' => 'web',
                'event_id' => $request->eventId,
                'password' => Hash::make(config('app.default_user_password')),
            ]);

            if($visitor) {

                $address = new Address([
                    'pincode' => $request->pincode,
                    'city' => $request->city,
                    'state' => $request->state,
                    'country' => $request->country,
                    'address' => $request->address,
                ]);

                $visitor->address()->save($address);

                $selectedProducts = $request->products;

                $visitor->eventVisitors()->create(['product_looking' => $selectedProducts, 'event_id' => $request->eventId]);

                //  $authData = isset(getAuthData()->id) ? getAuthData()->id :null;
                $authData = getAuthData();
                if($authData && isset($authData->user) && $authData->user->id) {
                    $authId = $authData->user->id;
                    $visitor->update(['created_by' => $authId]);
                }

                DB::commit();

                // TODO: Use job & queue to send welcome message
                sendWelcomeMessageThroughWhatsappBot($request->mobileNumber, 'visitor');

                session()->flash('success', 'Visitor created successfully');

                return redirect()->to(route('visitor.show'));
            }
        } catch (\Exception $e) {
            DB::rollBack();

            session()->flash('error', $e->getMessage());
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
