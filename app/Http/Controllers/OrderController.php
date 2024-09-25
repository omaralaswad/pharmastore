<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        return response()->json(Order::all(), 200);
    }

    public function store(Request $request)
    {
        // Validate the request data including delivery fields
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'total_price' => 'required|numeric',
            'status' => 'required|string|max:255',
            'delivery_name' => 'required|string|max:255',
            'delivery_email' => 'required|email', // Removed the unique constraint
            'delivery_address' => 'required|string',
            'delivery_phone_number' => 'required|string|max:15',
        ]);

        // Create the order with the validated data
        $order = Order::create($validatedData);

        return response()->json($order, 201);
    }

    public function show($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json($order, 200);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Validate the update request data including delivery fields
        $validatedData = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'total_price' => 'sometimes|required|numeric',
            'status' => 'sometimes|required|string|max:255',
            'delivery_name' => 'sometimes|required|string|max:255',
            'delivery_email' => 'sometimes|required|email', // Removed the unique constraint
            'delivery_address' => 'sometimes|required|string',
            'delivery_phone_number' => 'sometimes|required|string|max:15',
        ]);

        // Update the order with the validated data
        $order->update($validatedData);

        return response()->json($order, 200);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(null, 204);
    }

    public function getByOrderId($orderId)
    {
        // Retrieve order items with product details
        $orderItems = OrderItem::with('product')->where('order_id', $orderId)->get();

        return response()->json($orderItems);
    }
}
