<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_category_page()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category' => $category->id]);

        $response = $this->get("/category/{$category->id}");

        $response->assertStatus(200);
    }

    public function test_it_returns_404_for_nonexistent_category()
    {
        $nonexistentCategoryId = 999;

        $response = $this->get("/category/{$nonexistentCategoryId}");

        $response->assertStatus(404);
    }

    public function test_it_renders_category_page_with_no_products()
    {
        $category = Category::factory()->create();

        $response = $this->get("/category/{$category->id}");

        $response->assertInertia(
            fn (Assert $page) => $page->component('Category')
                ->url("/category/{$category->id}")
                ->has('categories')
                ->where('category_name.name', $category->name)
                ->where('category_by_id', [])
        );
    }

    public function test_it_renders_category_page_with_products()
    {
        $category = Category::factory()->create();
        $product1 = Product::factory()->create(['category' => $category->id]);
        $product2 = Product::factory()->create(['category' => $category->id]);

        $response = $this->get("/category/{$category->id}");
        
        $response->assertInertia(
            fn (Assert $page) => $page->component('Category')
                ->url("/category/{$category->id}")
                ->has('categories')
                ->where('category_name.name', $category->name)
                ->where('category_by_id', function ($data) use ($product1, $product2) {
                    return $data->contains('name', $product1->name) &&
                        $data->contains('name', $product2->name);
                })
        );
    }
}
