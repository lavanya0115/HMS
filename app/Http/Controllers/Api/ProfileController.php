<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventExhibitor;
use App\Models\Exhibitor;
use App\Models\User;
use App\Models\Visitor;
use App\Models\ExhibitorContact;
use App\Models\ExhibitorProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Support\Facades\File as FileFacade;

class ProfileController extends Controller
{

    public function show(Request $request)
    {
        $exhibitor_id = auth()->user()->id;
        $exhibitor = Exhibitor::find($exhibitor_id);
        $business_types = Category::where('type', 'exhibitor_business_type')->get();
        if (!$exhibitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exhibitor not found...'
            ]);
        }

        $logoExists = false;
        if (!empty($exhibitor->logo)) {
            $logoExists = Storage::disk('public')->exists($exhibitor->logo);
        }


        $exhibitorData = [
            'username' => $exhibitor->username ?? '',
            'name' => $exhibitor->name ?? '',
            'email' => $exhibitor->email ?? '',
            'mobile_number' => $exhibitor->mobile_number ?? '',
            'logo' => $logoExists ? asset('storage/' . $exhibitor->logo ?? '') : '',
            'category_id' => $exhibitor->category_id ?? null,
            'category_name' => $exhibitor->category->name ?? '',
            'product_id' => $exhibitor->exhibitorProducts->pluck('product_id') ?? [],
            'product_name' => $exhibitor->exhibitorProducts->map(function ($exhibitorProduct) {
                return $exhibitorProduct->product->name ?? '';
            })->toArray(),
            'website_url' => $exhibitor->_meta['website_url'] ?? '',
            'description' => $exhibitor->description ?? '',
            'salutation' => $exhibitor->exhibitorContact->salutation ?? '',
            'contact_person' => $exhibitor->exhibitorContact->name ?? '',
            'designation' => $exhibitor->exhibitorContact->designation ?? '',
            'contact_number' => $exhibitor->exhibitorContact->contact_number ?? '',
            'pincode' => $exhibitor->address->pincode ?? '',
            'city' => $exhibitor->address->city ?? '',
            'state' => $exhibitor->address->state ?? '',
            'country' => $exhibitor->address->country ?? '',
            'address' => $exhibitor->address->address ?? '',
            'countries' => getCountries(),
            'business_types' => $business_types->map(function ($business_type) {
                return [
                    'id' => $business_type->id ?? '',
                    'name' => $business_type->name ?? '',
                ];
            }),
            'events' => $exhibitor->eventExhibitors()->orderBy('id', 'desc')->get()->map(function ($eventExhibitor) {
                $products = [];
                if ($eventExhibitor->products !== null) {
                    foreach ($eventExhibitor->products as $productId) {
                        $product = Product::find($productId);

                        if ($product) {
                            $products[] = [
                                'id' => $productId ?? '',
                                'name' => $product->name ?? '',
                            ];
                        }
                    }
                }
                return [
                    'id' => $eventExhibitor->event_id ?? null,
                    'name' => $eventExhibitor->event->title ?? '',
                    'registration_date' => $eventExhibitor->created_at->format('Y-m-d') ?? '',
                    'stall_no' => $eventExhibitor->stall_no ?? '',
                    'products' => $products,
                    'facial_name' => $eventExhibitor->board_name ?? '',
                ];
            }),
            'products' => $exhibitor->exhibitorProducts->map(function ($exhibitorProduct) {
                $images = $exhibitorProduct->_meta['images'] ?? [];

                return [
                    'id' => $exhibitorProduct->product_id ?? '',
                    'name' => $exhibitorProduct->product->name ?? '',
                    'images' => collect($images)->map(function ($productImage) {

                        return [
                            'id' => $productImage['id'] ?? '',
                            'path' => !empty($productImage['filePath']) ? asset('storage/' . $productImage['filePath']) : '',
                        ];
                    }),
                ];
            })->toArray(),

        ];
        return response()->json(['status' => 'success', 'data' => $exhibitorData]);
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'salutation' => 'required',
            'contact_person' => 'required|regex:/^[a-zA-Z ]+$/',
            'designation' => 'required',
            'contact_number' => 'required|regex:/^\+?[\d\s-]{7,15}$/',
            'name' => 'required',
            'category_id' => 'required',
            'email' => 'required|email',
            'product_id' => 'required',
            'country' => 'required',
            'pincode' => 'required',
            'address' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => 'fail',
                'errors' => $validator->errors(),
            ], 422);
        }

        $mobile_number = $request->mobile_number;

        $exhibitor_id = auth()->user()->id;
        $exhibitor = Exhibitor::find($exhibitor_id);

        if (!$exhibitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exhibitor not found...',
            ]);
        }

        $exhibitorEmailExists = Exhibitor::where('email', $request->email)->where('id', '!=', $exhibitor->id)->first();
        if ($exhibitorEmailExists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email already exists',
            ]);
        }

        $exhibitorContactNoExists = ExhibitorContact::where('contact_number', $request->contact_number)
            ->where('exhibitor_id', '!=', $exhibitor->id)->first();

        if ($exhibitorContactNoExists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Contact number already exists',
            ]);
        }
        try {
            DB::beginTransaction();

            // Update Exhibitor details
            $meta = $exhibitor->_meta ?? null;
            $meta['website_url'] = $request->website_url ?? null;

            $exhibitor->update([
                'name' => $request->name,
                'email' => $request->email,
                'category_id' => $request->category_id,
                'description' => $request->description ?? '',
                '_meta' => $meta,
            ]);

            // Update Exhibitor Contact details
            $exhibitor->exhibitorContact()->update([
                'salutation' => $request->salutation,
                'name' => $request->contact_person,
                'designation' => $request->designation,
                'contact_number' => $request->contact_number,
            ]);

            // Update Exhibitor Address details
            $exhibitor->address()->update([
                'pincode' => $request->pincode,
                'city' => $request->city,
                'state' => $request->state,
                'address' => $request->address,
                'country' => $request->country,
            ]);

            $currentProductIds = ExhibitorProduct::where('exhibitor_id', $exhibitor->id)->pluck('product_id')->toArray();

            $removedProductIds = array_diff($currentProductIds, $request->product_id);

            if (count($removedProductIds) > 0) {
                foreach ($removedProductIds as $removedProductId) {
                    $productExists = ExhibitorProduct::where('exhibitor_id', $exhibitor->id)
                        ->where('product_id', $removedProductId)
                        ->first();
                    if ($productExists) {
                        $productExists->delete();
                    }
                }
            }

            foreach ($request->product_id as $productId) {
                $productExists = ExhibitorProduct::where('exhibitor_id', $exhibitor->id)
                    ->where('product_id', $productId)
                    ->first();
                if (!$productExists) {
                    $exhibitor->exhibitorProducts()->create([
                        'product_id' => $productId,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Exhibitor details successfully updated',
                'status' => 'success',
            ], 201);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => 'fail',
            ], 500);
        }
    }

    public function updateEventProducts(Request $request)
    {
        $exhibitor_id = auth()->user()->id;
        $exhibitor = Exhibitor::find($exhibitor_id);
        if (!$exhibitor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exhibitor not found...'
            ]);
        }
        if (!$request->event_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event Id is missing..'
            ]);
        }

        $event = Event::find($request->event_id);
        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found..'
            ]);
        }
        try {
            $products = $request->products ?? [];
            $facialName = $request->facial_name;

            if (empty($facialName)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Facial name is missing',
                ], 500);
            }

            $isFacialNameExists = EventExhibitor::where('event_id', $request->event_id)->where('exhibitor_id', '!=', $exhibitor->id)->where('board_name', $facialName)->first();
            if ($isFacialNameExists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Facial name already exists',
                ], 500);
            }
            $exhibitor->eventExhibitors()->where('event_id', $request->event_id)->update([
                'products' => $products,
                'board_name' => $facialName
            ]);
            foreach ($products as $product) {
                $productExists = ExhibitorProduct::where('exhibitor_id', $exhibitor->id)
                    ->where('product_id', $product)
                    ->first();
                if (!$productExists) {
                    $exhibitor->exhibitorProducts()->create([
                        'product_id' => $product,
                    ]);
                }
            }
            return response()->json([
                'message' => 'Exhibitor products updated',
                'status' => 'success',
            ], 201);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => 'fail',
            ], 500);
        }
    }

    public function storeProductImage(Request $request)
    {
        $productId = $request->product_id;
        $exhibitorId = auth()->user()->id;
        $productImage = $request->product_image;

        if (!$productId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product Id is missing..'
            ]);
        }
        if (!$exhibitorId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exhibitor Id is missing..'
            ]);
        }
        if (!$productImage) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product Image is missing..'
            ]);
        }
        try {

            $exhibitorProduct = ExhibitorProduct::where('product_id', $productId)
                ->where('exhibitor_id', $exhibitorId)->first();


            if ($exhibitorProduct) {

                $existingMeta = $exhibitorProduct->_meta ?? [];

                $images = $existingMeta['images'] ?? [];


                if ($productImage) {

                    $imageFolderPath = 'Exhibitor_Product/' . date('Y/m');
                    foreach ($productImage as $photo) {
                        $imageData = base64_decode($photo);
                        $imageName = str::random(10) . '.png';
                        $filePath = $imageFolderPath . '/' . $imageName;

                        // Store the file using the Storage facade
                        Storage::disk('public')->put($filePath, $imageData);
                        $images[] = [
                            'id' => Str::random(10),
                            'filePath' => $filePath
                        ];
                    }

                    $existingMeta['images'] = $images;
                    $exhibitorProduct->_meta = $existingMeta;
                    $exhibitorProduct->save();

                    return response()->json([
                        'message' => 'Product image stored successfully',
                        'status' => 'success',
                    ], 201);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product not found..'
                ]);
            }
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => 'fail',
            ], 500);
        }
    }

    public function destroyProductImage(Request $request)
    {
        $exhibitorId = auth()->user()->id;
        $productId = $request->product_id;
        $productImageId = $request->image_id;

        if (!$exhibitorId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exhibitor not found...'
            ]);
        }
        if (!$productId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product Id is missing..'
            ]);
        }
        if (!$productImageId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product Image Id is missing..'
            ]);
        }
        try {

            $product = ExhibitorProduct::where('product_id', $productId)
                ->where('exhibitor_id', $exhibitorId)->first();

            if ($product) {
                $existingMeta = $product->_meta ?? [];
                $productImages = $existingMeta['images'];


                if (isset($productImages)) {
                    foreach ($productImages as $index => $productImage) {
                        if ($productImage['id'] === $productImageId) {
                            $filepath = public_path('storage/' . $productImage['filePath']);
                            if (file_exists($filepath)) {
                                unlink($filepath);
                                unset($productImages[$index]);
                            } else {
                                return response()->json([
                                    'status' => 'error',
                                    'message' => 'Product image not found...'
                                ]);
                            }
                        }
                    }
                    $existingMeta['images'] = array_values($productImages);
                    $product->_meta = $existingMeta;
                    $product->save();
                    return response()->json([
                        'message' => 'Product image deleted successfully',
                        'status' => 'success',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product not found...'
                ]);
            }
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => 'fail',
            ], 500);
        }
    }

    public function updateExhibitorLogo(Request $request)
    {
        $exhibitorId = auth()->user()->id;
        $exhibitorLogo = $request->exhibitor_logo;

        if (!$exhibitorId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exhibitor Id is missing..'
            ]);
        }

        if (!$exhibitorLogo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exhibitor Logo is missing..'
            ]);
        }

        try {
            $exhibitor = Exhibitor::find($exhibitorId);

            if ($exhibitor) {
                $imageData = base64_decode($exhibitorLogo);
                // Remove previous logo
                if (!empty($exhibitor->logo)) {
                    // dd($exhibitor->logo);
                    $filepath = public_path('storage/' . $exhibitor->logo);
                    if (file_exists($filepath)) {
                        unlink($filepath);
                    }
                }

                // Convert base64 to file and store using Storage
                $imageFolderPath = 'exhibitor/' . date('Y/m');
                $imageName = str::random(10) . '.png';
                $filePath = $imageFolderPath . '/' . $imageName;

                // Store the file using the Storage facade
                Storage::disk('public')->put($filePath, $imageData);

                $exhibitor->logo = $filePath;
                $exhibitor->save();

                return response()->json([
                    'message' => 'Exhibitor logo stored successfully',
                    'status' => 'success',
                ], 201);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Exhibitor not found...'
                ]);
            }
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status' => 'fail',
            ], 500);
        }
    }

    public function changeUserPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|different:current_password',
            'confirm_password' => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $currentPassword = $request->current_password;
        $newPassword = $request->new_password;
        $confirmPassword = $request->confirm_password;

        $userType = $request->type;
        if (!$userType) {
            return response()->json([
                'status' => 'error',
                'message' => 'User type is missing...'
            ]);
        }

        $userId = auth()->user()->id;
        $user = null;

        if ($userType == 'admin') {
            $user = User::find($userId);
        } else if ($userType == 'exhibitor') {
            $user = Exhibitor::find($userId);
        } else if ($userType == 'visitor') {
            $user = Visitor::find($userId);
        }

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found...'
            ]);
        }

        if (Hash::check($currentPassword, $user->password)) {
            $user->password = Hash::make($newPassword);
            $user->updated_by = $userType == 'admin' ? $userId : null;
            $user->save();
            return response()->json([
                'message' => 'Password changed successfully',
                'status' => 'success',
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect...'
            ]);
        }
    }
}
