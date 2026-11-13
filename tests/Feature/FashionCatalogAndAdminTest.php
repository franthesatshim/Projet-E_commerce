<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FashionCatalogAndAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_fashion_categories_are_seeded_without_creating_fictitious_products(): void
    {
        $this->seed(DatabaseSeeder::class);
        foreach (['vetements', 'perruques', 'tissus-wax', 'sacs', 'chaussures'] as $slug) {
            $this->assertDatabaseHas('categories', ['slug' => $slug, 'is_active' => 1]);
        }
        $this->assertDatabaseCount('products', 0);
    }

    public function test_old_demo_products_are_hidden_but_not_deleted(): void
    {
        $category = Category::create(['name' => 'Électronique', 'slug' => 'electronique', 'is_active' => true]);
        $demo = Product::create([
            'category_id' => $category->id, 'name' => 'Smartphone Android',
            'slug' => 'smartphone-android',
            'description' => 'Produit de démonstration à remplacer par votre catalogue réel.',
            'price_usd' => 199, 'price_cdf' => 550000, 'stock' => 12,
            'condition' => 'new', 'is_active' => true,
        ]);
        $this->seed(DatabaseSeeder::class);
        $this->assertFalse($demo->fresh()->is_active);
        $this->assertFalse($category->fresh()->is_active);
        $this->get(route('products.index'))->assertOk()->assertDontSee('Smartphone Android');
    }

    public function test_admin_login_redirects_to_dashboard_and_displays_navigation_link(): void
    {
        $admin = User::create([
            'name' => 'Administratrice', 'email' => 'admin-test@example.test',
            'password' => Hash::make('Password123!'), 'role' => 'admin',
        ]);
        $this->post(route('login.store'), [
            'email' => $admin->email, 'password' => 'Password123!',
        ])->assertRedirect(route('admin.dashboard'));
        $this->actingAs($admin)->get(route('home'))->assertOk()->assertSee('Administration');
    }
}
