<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CheckoutController extends Controller
{
    public function create()
    {
        if (empty(session('cart', []))) {
            return redirect()->route('cart.index');
        }

        return view('checkout', ['currency' => session('currency', 'USD')]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'shipping_name' => 'required|string|max:120',
            'shipping_phone' => 'required|string|max:30',
            'shipping_address' => 'required|string|max:500',
            'shipping_city' => 'required|string|max:120',
            'payment_method' => 'required|in:cash_on_delivery,bank_transfer,mobile_money',
            'notes' => 'nullable|string|max:1000',
        ]);

        $cart = session('cart', []);
        if (! is_array($cart) || $cart === []) {
            return redirect()->route('cart.index');
        }

        $currency = session('currency', 'USD');
        if (! in_array($currency, ['USD', 'CDF'], true)) {
            $currency = 'USD';
        }

        try {
            $order = DB::transaction(function () use ($cart, $currency, $data, $request) {
                $lockedItems = [];
                $subtotalMinor = 0;

                foreach ($cart as $productId => $line) {
                    $quantity = filter_var($line['quantity'] ?? null, FILTER_VALIDATE_INT);
                    if ($quantity === false || $quantity < 1 || $quantity > 50) {
                        throw new RuntimeException('Une quantité du panier est invalide.');
                    }

                    $product = Product::query()->whereKey($productId)->lockForUpdate()->first();
                    if (! $product || ! $product->is_active || $product->stock < $quantity) {
                        throw new RuntimeException('Stock insuffisant pour '.($product?->name ?? 'un produit du panier'));
                    }

                    // Calcul en centimes pour éviter les erreurs d'arrondi des flottants.
                    $unitMinor = (int) round(((float) $product->price($currency)) * 100);
                    $subtotalMinor += $unitMinor * $quantity;
                    $lockedItems[] = [$product, $quantity, $unitMinor];
                }

                $subtotal = $subtotalMinor / 100;
                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'number' => 'CMD-'.strtoupper(Str::random(10)),
                    'currency' => $currency,
                    'subtotal' => $subtotal,
                    'shipping_fee' => 0,
                    'total' => $subtotal,
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'payment_method' => $data['payment_method'],
                    'shipping_name' => $data['shipping_name'],
                    'shipping_phone' => $data['shipping_phone'],
                    'shipping_address' => $data['shipping_address'],
                    'shipping_city' => $data['shipping_city'],
                    'notes' => $data['notes'] ?? null,
                ]);

                foreach ($lockedItems as [$product, $quantity, $unitMinor]) {
                    $lineTotal = ($unitMinor * $quantity) / 100;
                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'unit_price' => $unitMinor / 100,
                        'quantity' => $quantity,
                        'line_total' => $lineTotal,
                    ]);
                    $product->decrement('stock', $quantity);
                }

                Payment::create([
                    'order_id' => $order->id,
                    'method' => $data['payment_method'],
                    'amount' => $subtotal,
                    'currency' => $currency,
                    'status' => 'pending',
                    'notes' => 'Paiement à confirmer manuellement par un administrateur.',
                ]);

                return $order;
            }, 3);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['stock' => $exception->getMessage()])->withInput();
        }

        session()->forget('cart');

        return redirect()
            ->route('account.order', $order)
            ->with('success', 'Commande enregistrée. Paiement à confirmer.');
    }
}
