<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function getLastProducts($x)
{
    // Eager load the category and supplier relationships
    $products = Product::with(['category', 'supplier'])
                ->orderBy('id', 'desc')
                ->limit($x)
                ->get();

    // Format the response to include category and supplier names
    return $products->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'category_name' => $product->category->name ?? null,
            'supplier_name' => $product->supplier->name ?? null,
            'image' => $product->image,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
        ];
    });
}


    public function index()
{
    // Eager load the category and supplier relationships
    $products = Product::with(['category', 'supplier'])->get();

    return $products->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'category_name' => $product->category->name ?? null,
            'supplier_name' => $product->supplier->name ?? null,
            'image' => $product->image,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
        ];
    });
}

public function store(Request $request)
{
    try {
        // Validate the request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Create a new Product instance
        $product = new Product();
        $product->name = $validatedData['name'];
        $product->description = $validatedData['description'];
        $product->price = $validatedData['price'];
        $product->category_id = $validatedData['category_id'];
        $product->supplier_id = $validatedData['supplier_id'];

        // Handle the image file upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads'), $imageName);

            // Save the image path relative to the public directory
            $product->image = 'uploads/' . $imageName;
        } else {
            $product->image = null; // Set to null if no image is provided
        }

        // Save the product instance to the database
        $product->save();

        // Fetch category name and supplier name
        $categoryName = Category::find($product->category_id)?->name;
        $supplierName = Supplier::find($product->supplier_id)?->name;

        // Return a JSON response indicating success
        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
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




public function show($id)
{
    // Find the product with category and supplier relationships
    $product = Product::with(['category', 'supplier'])->find($id);

    if (!$product) {
        return response()->json(['message' => 'Product not found'], 404);
    }

    // Extract the image filename from the image path
    //$imageName = $product->image ? basename($product->image) : null;

    // Format the response to include category, supplier, and image name
    return response()->json([
        'id' => $product->id,
        'name' => $product->name,
        'description' => $product->description,
        'price' => $product->price,
        'category_name' => $product->category->name ?? null,
        'supplier_name' => $product->supplier->name ?? null,
        'image_name' => $imageName, // Return only the image filename
        'created_at' => $product->created_at,
        'updated_at' => $product->updated_at,
    ]);
}


    public function update(Request $request, $id)
{
    // Find the product or return a 404 error if not found
    $product = Product::findOrFail($id);

    // Validate only the fields that are sent in the request
    $validatedData = $request->validate([
        'name' => 'sometimes|string|max:255',
        'description' => 'sometimes|nullable|string',
        'price' => 'sometimes|numeric',
        'category_id' => 'sometimes|exists:categories,id',
        'supplier_id' => 'sometimes|exists:suppliers,id',
        'image' => 'sometimes|nullable|string'
    ]);

    // Update only the fields that are provided
    if ($request->has('name')) {
        $product->name = $validatedData['name'];
    }
    if ($request->has('description')) {
        $product->description = $validatedData['description'];
    }
    if ($request->has('price')) {
        $product->price = $validatedData['price'];
    }
    if ($request->has('category_id')) {
        $product->category_id = $validatedData['category_id'];
    }
    if ($request->has('supplier_id')) {
        $product->supplier_id = $validatedData['supplier_id'];
    }
    if ($request->has('image')) {
        $product->image = $validatedData['image'];
    }

    // Save the changes to the database
    $product->save();

    // Return the updated product in the response
    return response()->json($product, 200);
}


    public function delete($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(null, 204);
    }

    public function getByCategoryId($categoryId)
{
    // Join products with categories
    $products = Product::join('categories', 'products.category_id', '=', 'categories.id')
        ->where('products.category_id', $categoryId)
        ->select('products.*', 'categories.name as category_name') // Select columns from both tables
        ->get();

    if ($products->isEmpty()) {
        return response()->json(['message' => 'No products found for this category'], 404);
    }

    return response()->json($products);
}


    public function getLast20Products()
    {
        $products = Product::orderBy('created_at', 'desc')->limit(20)->get();
        return response()->json($products);
    }

    public function getAllProductsSorted()
    {
        $products = Product::orderBy('created_at', 'desc')->get();
        return response()->json($products);
    }
}
