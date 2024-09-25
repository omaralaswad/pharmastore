<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\SpecialOffer;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Users;
use Illuminate\Support\Facades\DB;


class CartController extends Controller
{

    public function addItem(Request $request)
    {
        // Retrieve request data
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');
        $userId = $request->input('user_id');

        // Find the product
        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $price = $product->price * $quantity;

        // Use a database transaction to ensure atomicity
        DB::beginTransaction();

        try {
            // Find or create a pending order for the user
            $order = Order::firstOrCreate(
                ['user_id' => $userId, 'status' => 'pending'],
                ['total_price' => 0]
            );

            // Add or update the order item
            $orderItem = $order->items()->updateOrCreate(
                ['product_id' => $product->id],
                ['quantity' => $quantity, 'price' => $price]
            );

            // Update the total price of the order
            $order->total_price = $order->items()->sum('price');
            $order->save();

            DB::commit();

            return response()->json([
                'message' => 'Item added to cart',
                'order' => $order->load('items.product'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function addSpecialOfferItem(Request $request)
{
    // Retrieve request data
    $offerId = $request->input('offer_id');
    $quantity = $request->input('quantity');
    $userId = $request->input('user_id');

    // Find the special offer
    $offer = SpecialOffer::find($offerId);
    if (!$offer) {
        return response()->json(['message' => 'Special offer not found'], 404);
    }

    // Calculate price using the new_price from the special offer
    $price = $offer->new_price * $quantity;

    // Use a database transaction to ensure atomicity
    DB::beginTransaction();

    try {
        // Find or create a pending order for the user
        $order = Order::firstOrCreate(
            ['user_id' => $userId, 'status' => 'pending'],
            ['total_price' => 0]
        );

        // Add or update the order item based on the special offer
        $orderItem = $order->items()->updateOrCreate(
            ['product_id' => $offer->id],  // assuming you store offer_id or use a new column
            ['quantity' => $quantity, 'price' => $price]
        );

        // Update the total price of the order
        $order->total_price = $order->items()->sum('price');
        $order->save();

        DB::commit();

        return response()->json([
            'message' => 'Special offer item added to cart',
            'order' => $order->load('items.product'),  // adjust this if needed
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
    }
}




    public function viewCart($userId)
    {
        // Retrieve the user_id from the request
        //$userId = $request->input('user_id');

        // Validate that user_id exists
        $userExists = Users::find($userId);
        if (!$userExists) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Retrieve the pending order for the user
        $order = Order::where('user_id', $userId)
            ->where('status', 'pending')
            ->with('items.product') // Eager load the products
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Cart is empty'], 404);
        }

        return response()->json([
            'order' => $order,
        ]);
    }



    public function checkout(Request $request, $userId)
{
    // Validate that user_id exists
    $userExists = Users::find($userId); // Ensure you are using the correct User model
    if (!$userExists) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // Retrieve the pending order for the user
    $order = Order::where('user_id', $userId)
        ->where('status', 'pending')
        ->first();

    if (!$order) {
        return response()->json(['message' => 'No pending cart found'], 404);
    }

    // Validate delivery information and total price
    $validatedData = $request->validate([
        'delivery_name' => 'required|string|max:255',
        'delivery_email' => 'required|email',
        'delivery_address' => 'required|string',
        'delivery_phone_number' => 'required|string|max:15',
        'total_price' => 'required|numeric|min:0', // Ensure total price is passed and is a positive number
    ]);

    // Update the order status to 'completed', store delivery information and total price
    $order->status = 'completed';
    $order->delivery_name = $validatedData['delivery_name'];
    $order->delivery_email = $validatedData['delivery_email'];
    $order->delivery_address = $validatedData['delivery_address'];
    $order->delivery_phone_number = $validatedData['delivery_phone_number'];
    $order->total_price = $validatedData['total_price']; // Store total price from the frontend

    // Save the order with delivery information and total price
    $order->save();

    return response()->json([
        'message' => 'Order placed successfully',
        'order' => $order->load('items.product'), // Load related items and products if needed
    ]);
}



public function deleteItem($item_id)
{
    // Find the order item by ID
    $orderItem = OrderItem::find($item_id);

    // Check if the order item exists
    if (!$orderItem) {
        return response()->json(['message' => 'Order item not found'], 404);
    }

    // Find the associated order
    $order = $orderItem->order;

    // Delete the order item
    $orderItem->delete();

    // Update the total price of the order
    $order->total_price = $order->items()->sum('price');
    $order->save();

    return response()->json([
        'message' => 'Order item deleted successfully',
        'order' => $order->load('items.product'),
    ]);
}
}