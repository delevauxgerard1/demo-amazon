<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_category_page_with_data()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category' => $category->id]);
        
        $response = $this->get("/categories/{$category->id}");
        $response->assertStatus(200);
        $response->assertViewIs('Category');
        $response->assertViewHas('category_name', $category);
        $response->assertViewHas('category_by_id', Product::where('category', $category->id)->get());
    }
}
