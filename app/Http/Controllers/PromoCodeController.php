<?php

namespace App\Http\Controllers;
use App\Models\PromoCode;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    // Get all promo codes
    public function index()
    {
        return PromoCode::all();
    }

    // Create a new promo code
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:promo_codes,code|max:255',
            'discount' => 'required|numeric|min:0|max:100',
            'expiration_date' => 'required|date|after:today',
        ]);

        $promoCode = PromoCode::create($validated);

        return response()->json($promoCode, 201); // Created
    }

    // Get a single promo code by ID
    public function show($id)
    {
        $promoCode = PromoCode::findOrFail($id);
        return response()->json($promoCode);
    }

    // Update a promo code by ID
    public function update(Request $request, $id)
    {
        $promoCode = PromoCode::findOrFail($id);

        $validated = $request->validate([
            'code' => 'sometimes|required|string|unique:promo_codes,code,' . $id . '|max:255',
            'discount' => 'sometimes|required|numeric|min:0|max:100',
            'expiration_date' => 'sometimes|required|date|after:today',
        ]);

        $promoCode->update($validated);

        return response()->json($promoCode);
    }

    public function getPromoCodeDiscount(Request $request)
    {
        // Validate the request to ensure 'code' is present
        $validatedData = $request->validate([
            'code' => 'required|string|max:255',
        ]);

        // Search for the promo code
        $promoCode = PromoCode::where('code', $validatedData['code'])->first();

        // Check if the promo code was found and is not expired
        if ($promoCode && $promoCode->expiration_date >= now()) {
            return response()->json([
                'code' => $promoCode->code,
                'discount' => $promoCode->discount,
                'message' => 'Promo code is valid.',
            ], 200);
        }

        // If promo code was not found or is expired
        return response()->json([
            'message' => 'Promo code is invalid or expired.',
        ], 404);
    }

    // Delete a promo code by ID
    public function destroy($id)
    {
        $promoCode = PromoCode::findOrFail($id);
        $promoCode->delete();

        return response()->json(null, 204); // No Content
    }
}
