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
    // Attempt to find the special offer with related category and supplier
    $specialOffer = SpecialOffer::with(['category', 'supplier'])->find($id);

    // If the special offer is not found, return a 404 error
    if (!$specialOffer) {
        return response()->json(['message' => 'Special Offer not found'], 404);
    }

    // Prepare the response data
    $responseData = [
        'id' => $specialOffer->id,
        'name' => $specialOffer->name,
        'description' => $specialOffer->description,
        'old_price' => $specialOffer->old_price,
        'new_price' => $specialOffer->new_price,
        'category_id' => $specialOffer->category_id,
        'supplier_id' => $specialOffer->supplier_id,
        'image' => $specialOffer->image,
        'created_at' => $specialOffer->created_at,
        'updated_at' => $specialOffer->updated_at,
        'category_name' => $specialOffer->category ? $specialOffer->category->name : 'N/A', // Fetch category name
        'supplier_name' => $specialOffer->supplier ? $specialOffer->supplier->name : 'N/A' // Fetch supplier name
    ];

    // Return the full special offer data along with category and supplier names
    return response()->json($responseData);
}



    // Insert a new special offer
    public function insertOffer(Request $request)
{
    try {
        // Validate the request data
        $validatedData = $request->validate([
            'product_id' => 'required|exists:products,id', // Validate the product ID
            'new_price' => 'required|numeric', // Validate the new price
        ]);

        // Find the product by ID
        $product = Product::find($validatedData['product_id']);

        // Create a new SpecialOffer instance
        $specialOffer = new SpecialOffer();
        $specialOffer->name = $product->name; // Use product name for special offer
        $specialOffer->description = $product->description; // Use product description for special offer
        $specialOffer->old_price = $product->price; // Use product price as old price
        $specialOffer->new_price = $validatedData['new_price']; // Use new price from request
        $specialOffer->category_id = $product->category_id; // Use product category ID
        $specialOffer->supplier_id = $product->supplier_id; // Use product supplier ID
        $specialOffer->product_id = $product->id; // Assign the product ID
        $specialOffer->image = $product->image; // Get the image from the product table

        // Save the special offer instance to the database
        $specialOffer->save();

        // Return a JSON response indicating success
        return response()->json([
            'message' => 'Special offer created successfully',
            'special_offer' => $specialOffer,
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
    // Get all offers with related category and supplier data
    $offers = SpecialOffer::with(['category', 'supplier'])->get();

    // Loop through each offer and add the category and supplier names
    $offersWithDetails = $offers->map(function ($offer) {
        return [
            'id' => $offer->id,
            'name' => $offer->name,
            'description' => $offer->description,
            'old_price' => $offer->old_price,
            'new_price' => $offer->new_price,
            'category_name' => $offer->category ? $offer->category->name : 'N/A',
            'supplier_name' => $offer->supplier ? $offer->supplier->name : 'N/A',
            'image' => $offer->image,
        ];
    });

    // Return the offers with category and supplier names
    return response()->json($offersWithDetails);
}

}
