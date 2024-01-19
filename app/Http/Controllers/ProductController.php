<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(int $id)
    {
        return Inertia::render('Product', [
            'product' => Product::find($id),
        ]);
    }
}