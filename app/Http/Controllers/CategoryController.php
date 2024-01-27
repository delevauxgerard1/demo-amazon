<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Inertia\Inertia;

class CategoryController extends Controller
{

    public function index(int $id)
    {
        $category = Category::find($id);

        if (!$category) {
            abort(404);
        }
        
        $products = Product::where('category', $id)->get();

        return Inertia::render('Category', [
            'category_name' => $category,
            'category_by_id' => $products,
        ]);
    }
}
