<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(int $id)
    {
        $product = Product::find($id);

        if (!$product) {
            abort(404);
        }
        return Inertia::render('Product', [
            'product' => Product::find($id),
        ]);
    }
}