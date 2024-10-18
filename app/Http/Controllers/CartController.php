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

            // Return only the message
            return response()->json([
                'message' => 'Item added to cart',
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

        // Find the special offer and load the associated product
        $offer = SpecialOffer::with('product')->find($offerId);
        if (!$offer || !$offer->product) {
            return response()->json(['message' => 'Special offer or related product not found'], 404);
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

            // Add or update the order item based on the associated product
            // Check if the product already exists in the cart
            $orderItem = $order->items()->where('product_id', $offer->product->id)->first();

            if ($orderItem) {
                // If it exists, update the quantity and price
                $orderItem->quantity += $quantity; // Increment the quantity
                $orderItem->price = $offer->new_price * $orderItem->quantity; // Recalculate price
                $orderItem->save();
            } else {
                // If it doesn't exist, create a new order item
                $orderItem = $order->items()->create([
                    'product_id' => $offer->product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                ]);
            }

            // Update the total price of the order
            $order->total_price = $order->items()->sum('price');
            $order->save();

            DB::commit();

            // Return only necessary data
            return response()->json([
                'message' => 'Special offer item added to cart',
                'order_item' => [
                    'id' => $orderItem->id,
                    'product_id' => $offer->product->id,
                    'product_name' => $offer->product->name,
                    'quantity' => $orderItem->quantity,
                    'price' => $orderItem->price,
                    'offer_name' => $offer->name,
                    'offer_description' => $offer->description,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }







    public function viewCart($userId)
    {
        // Validate that the user exists
        $userExists = Users::find($userId);
        if (!$userExists) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Retrieve the pending order for the user
        $order = Order::where('user_id', $userId)
            ->where('status', 'pending')
            ->with('items.product') // Eager load the products through order items
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Cart is empty'], 404);
        }

        // Transform the data to return only specific details
        $cartDetails = $order->items->map(function ($item) {
            return [
                'image' => $item->product->image,
                'name' => $item->product->name,
                'price_per_item' => $item->price / $item->quantity, // Assuming you store price per item in order_items
                'quantity' => $item->quantity,
                'total_price' => $item->price,
            ];
        });

        // Return the transformed cart details
        return response()->json([
            'cart' => $cartDetails,
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