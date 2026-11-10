<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_place_an_order_and_stock_is_decremented(): void
    {
        $customer = $this->makeUser();
        $product = $this->makeProduct(stock: 5);

        $response = $this->actingAs($customer)
            ->withSession([
                'cart' => [$product->id => ['quantity' => 2]],
                'currency' => 'USD',
            ])
            ->post(route('checkout.store'), $this->shippingData());

        $order = Order::query()->firstOrFail();

        $response->assertRedirect(route('account.order', $order));
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame('USD', $order->currency);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'pending',
            'amount' => '40.00',
        ]);
    }

    public function test_checkout_rejects_insufficient_stock_without_creating_an_order(): void
    {
        $customer = $this->makeUser();
        $product = $this->makeProduct(stock: 1);

        $this->actingAs($customer)
            ->withSession(['cart' => [$product->id => ['quantity' => 2]], 'currency' => 'USD'])
            ->from(route('checkout'))
            ->post(route('checkout.store'), $this->shippingData())
            ->assertSessionHasErrors('stock');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(1, $product->fresh()->stock);
    }

    private function makeUser(string $role = 'customer'): User
    {
        return User::create([
            'name' => 'Client Test',
            'email' => uniqid('client', true).'@example.test',
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

    private function shippingData(): array
    {
        return [
            'shipping_name' => 'Client Test',
            'shipping_phone' => '+243900000000',
            'shipping_address' => '123 avenue de test',
            'shipping_city' => 'Lubumbashi',
            'payment_method' => 'cash_on_delivery',
        ];
    }
}
