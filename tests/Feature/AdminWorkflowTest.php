<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_access_admin_pages(): void
    {
        $customer = $this->makeUser('customer');

        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_cancelling_an_order_restores_stock_only_once(): void
    {
        $admin = $this->makeUser('admin');
        $customer = $this->makeUser('customer');
        $product = $this->makeProduct(stock: 3);

        $order = Order::create([
            'user_id' => $customer->id,
            'number' => 'CMD-TEST-'.uniqid(),
            'currency' => 'USD',
            'subtotal' => 40,
            'shipping_fee' => 0,
            'total' => 40,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'cash_on_delivery',
            'shipping_name' => 'Client Test',
            'shipping_phone' => '+243900000000',
            'shipping_address' => '123 avenue de test',
            'shipping_city' => 'Lubumbashi',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => 20,
            'quantity' => 2,
            'line_total' => 40,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'method' => 'cash_on_delivery',
            'amount' => 40,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        // Le stock est déjà diminué au moment où la commande est passée.
        $product->update(['stock' => 1]);

        $payload = ['status' => 'cancelled', 'payment_status' => 'pending'];
        $this->actingAs($admin)->patch(route('admin.orders.update', $order), $payload)->assertRedirect();
        $this->assertSame(3, $product->fresh()->stock);

        $this->patch(route('admin.orders.update', $order), $payload)->assertRedirect();
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_admin_can_approve_a_customer_review(): void
    {
        $admin = $this->makeUser('admin');
        $customer = $this->makeUser('customer');
        $product = $this->makeProduct(stock: 4);
        $review = Review::create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Très bon produit',
            'is_approved' => false,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.reviews.update', $review), ['action' => 'approve'])
            ->assertRedirect();

        $this->assertTrue($review->fresh()->is_approved);
    }

    private function makeUser(string $role): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => uniqid($role, true).'@example.test',
            'password' => Hash::make('Password123!'),
            'role' => $role,
        ]);
    }

    private function makeProduct(int $stock): Product
    {
        $category = Category::create([
            'name' => 'Test',
            'slug' => 'test-'.uniqid(),
            'is_active' => true,
        ]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Produit test',
            'slug' => 'produit-'.uniqid(),
            'description' => 'Produit de test',
            'price_usd' => 20,
            'price_cdf' => 55000,
            'stock' => $stock,
            'condition' => 'new',
            'is_active' => true,
        ]);
    }
}
