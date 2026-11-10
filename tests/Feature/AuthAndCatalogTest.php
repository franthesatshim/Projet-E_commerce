<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_customer_not_an_admin(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Nouvelle cliente',
            'email' => 'new-customer@example.test',
            'phone' => '+243900000000',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'admin',
        ])->assertRedirect(route('account'));

        $this->assertDatabaseHas('users', [
            'email' => 'new-customer@example.test',
            'role' => 'customer',
        ]);
    }

    public function test_inactive_products_are_not_visible_in_catalog_or_details(): void
    {
        $category = Category::create([
            'name' => 'Test',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Produit caché',
            'slug' => 'produit-cache',
            'price_usd' => 10,
            'price_cdf' => 27000,
            'stock' => 3,
            'condition' => 'new',
            'is_active' => false,
        ]);

        $this->get(route('products.index'))->assertOk()->assertDontSee('Produit caché');
        $this->get(route('products.show', $product))->assertNotFound();
    }

    public function test_invalid_catalog_price_range_is_rejected(): void
    {
        $this->from(route('products.index'))
            ->get(route('products.index', ['min' => 100, 'max' => 20]))
            ->assertSessionHasErrors('max');
    }
}
