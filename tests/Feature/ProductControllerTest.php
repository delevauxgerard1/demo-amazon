<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_product_page()
    {
        $product = Product::factory()->create();

        $response = $this->get("/product/{$product->id}");
        $response->assertStatus(200);
    }

    public function test_it_returns_404_for_nonexistent_product()
    {
        $nonexistentProductId = 999;

        $response = $this->get("/product/{$nonexistentProductId}");

        $response->assertStatus(404);
    }

    public function test_it_renders_product_page_with_product_data()
    {
        $product = Product::factory()->create();

        $response = $this->get("/product/{$product->id}");

        $response->assertInertia(
            fn (Assert $page) => $page->component('Product')
                ->url("/product/{$product->id}")
                ->has('product')
                ->where('product.id', $product->id)
                ->where('product.title', $product->title)
                ->where('product.description', $product->description)
                ->where('product.image', $product->image)
                ->where('product.price', $product->price)
                ->where('product.category', $product->category)
        );
    }
}
