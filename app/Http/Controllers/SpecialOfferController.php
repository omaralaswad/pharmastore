<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SpecialOffer;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;

class SpecialOfferController extends Controller
{

    public function show($id)
    {
        // محاولة العثور على العرض الخاص بناءً على id
        $specialOffer = SpecialOffer::with(['category', 'supplier'])->find($id);

        // في حال عدم وجود العرض الخاص، ارجع برسالة خطأ
        if (!$specialOffer) {
            return response()->json(['message' => 'Special Offer not found'], 404);
        }

        // إرجاع العرض الخاص في حال العثور عليه
        return response()->json($specialOffer);
    }
    // Insert a new special offer
    public function insertOffer(Request $request)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'old_price' => 'required|numeric',
                'new_price' => 'required|numeric',
                'category_id' => 'required|exists:categories,id',
                'supplier_id' => 'required|exists:suppliers,id',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048' // Validate image file with mime types and size
            ]);
    
            // Create a new SpecialOffer instance
            $specialOffer = new SpecialOffer();
            $specialOffer->name = $validatedData['name'];
            $specialOffer->description = $validatedData['description'];
            $specialOffer->old_price = $validatedData['old_price'];
            $specialOffer->new_price = $validatedData['new_price'];
            $specialOffer->category_id = $validatedData['category_id'];
            $specialOffer->supplier_id = $validatedData['supplier_id'];
    
            // Handle the image file upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads'), $imageName);
    
                // Save the image path relative to the public directory
                $specialOffer->image = 'uploads/' . $imageName;
            } else {
                $specialOffer->image = null; // Set to null if no image is provided
            }
    
            // Save the special offer instance to the database
            $specialOffer->save();
    
            // Fetch category name and supplier name
            $categoryName = Category::find($specialOffer->category_id)?->name;
            $supplierName = Supplier::find($specialOffer->supplier_id)?->name;
    
            // Return a JSON response indicating success
            return response()->json([
                'message' => 'Special offer created successfully',
                'special_offer' => $specialOffer,
                'category_name' => $categoryName,
                'supplier_name' => $supplierName
            ], 201);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

    // Delete all offers
    public function deleteAllOffers()
    {
        SpecialOffer::truncate();

        return response()->json(['message' => 'All offers deleted successfully.']);
    }

    public function deleteOffersById($id)
{
    // Find the special offer by ID
    $specialOffer = SpecialOffer::find($id);

    // Check if the special offer exists
    if ($specialOffer) {
        // Delete the special offer
        $specialOffer->delete();

        // Return a JSON response indicating success
        return response()->json(['message' => 'Special offer deleted successfully.']);
    } else {
        // If the special offer doesn't exist, return a JSON response indicating failure
        return response()->json(['message' => 'Special offer not found.'], 404);
    }
}


    // Get all offers
    public function getAllOffers()
    {
        $offers = SpecialOffer::all();

        return response()->json($offers);
    }
}
