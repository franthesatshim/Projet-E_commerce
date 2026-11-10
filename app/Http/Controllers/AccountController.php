<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        return view('account.index', [
            'orders' => $request->user()->orders()->latest()->take(5)->get(),
        ]);
    }

    public function orders(Request $request)
    {
        return view('account.orders', [
            'orders' => $request->user()->orders()->latest()->paginate(10),
        ]);
    }

    public function order(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 404);
        $order->load('items', 'payment');

        return view('account.order', compact('order'));
    }

    public function favorites(Request $request)
    {
        $products = $request->user()->favorites()
            ->where('is_active', true)
            ->with('category')
            ->latest('products.created_at')
            ->paginate(12);

        return view('account.favorites', compact('products'));
    }

    public function favorite(Request $request, Product $product)
    {
        abort_unless($product->is_active, 404);
        $request->user()->favorites()->toggle($product->id);

        return back()->with('success', 'Favoris mis à jour.');
    }

    public function review(Request $request, Product $product)
    {
        abort_unless($product->is_active, 404);

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        Review::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $product->id],
            $data + ['is_approved' => false]
        );

        return back()->with('success', 'Avis envoyé pour validation.');
    }
}
