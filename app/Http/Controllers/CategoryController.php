<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::all();
    }

    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Create a new category
        $category = Category::create($request->all());

        // Return the created category with a 201 status code
        return response()->json($category, 201);
    }

    public function show($id)
    {
        // Find the category by ID
        $category = Category::find($id);

        // Return 404 if the category is not found
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        // Return the category with a 200 status code
        return response()->json($category, 200);
    }

    public function update(Request $request, $id)
    {
        // Find the category or fail if not found
        $category = Category::findOrFail($id);

        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Update the category with the validated data
        $category->update($request->all());

        // Return the updated category with a 200 status code
        return response()->json($category, 200);
    }

    public function delete($id)
    {
        // Find the category or fail if not found
        $category = Category::findOrFail($id);

        // Delete the category
        $category->delete();

        // Return a 204 status code for successful deletion
        return response()->json(null, 204);
    }
}
