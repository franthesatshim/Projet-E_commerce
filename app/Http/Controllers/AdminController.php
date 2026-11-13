<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'products' => Product::count(),
            'orders' => Order::count(),
            'customers' => User::where('role', 'customer')->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'pendingReviews' => Review::where('is_approved', false)->count(),
            'recent' => Order::with('user')->latest()->take(8)->get(),
        ]);
    }

    public function products()
    {
        return view('admin.products', [
            'products' => Product::with('category')->latest()->paginate(15),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeProduct(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:180',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string|max:10000',
            'price_usd' => 'required|numeric|min:0|max:9999999999',
            'price_cdf' => 'required|numeric|min:0|max:999999999999',
            'stock' => 'required|integer|min:0|max:1000000',
            'image' => 'nullable|image|max:2048',
            'showcase_image' => ['nullable', 'string', Rule::in(collect(config('showcase', []))->pluck('path')->all())],
            'condition' => 'required|in:new,used',
        ]);

        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(5));
        $selectedPhoto = $data['showcase_image'] ?? null;
        unset($data['showcase_image']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        } elseif ($selectedPhoto) {
            $source = public_path($selectedPhoto);
            if (! is_file($source)) {
                throw ValidationException::withMessages(['showcase_image' => 'Cette photo est introuvable.']);
            }
            $destination = 'products/'.Str::uuid().'.jpg';
            Storage::disk('public')->put($destination, file_get_contents($source));
            $data['image'] = $destination;
        }

        Product::create($data + ['is_active' => true]);

        return back()->with('success', 'Produit créé.');
    }

    public function deleteProduct(Product $product)
    {
        $product->update(['is_active' => false]);

        return back()->with('success', 'Produit désactivé.');
    }

    public function orders()
    {
        return view('admin.orders', [
            'orders' => Order::with('user')->latest()->paginate(20),
        ]);
    }

    public function updateOrder(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        DB::transaction(function () use ($data, $order) {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            $allowedTransitions = [
                'pending' => ['confirmed', 'cancelled'],
                'confirmed' => ['processing', 'cancelled'],
                'processing' => ['shipped', 'cancelled'],
                'shipped' => ['delivered'],
                'delivered' => [],
                'cancelled' => [],
            ];

            if (
                $data['status'] !== $lockedOrder->status
                && ! in_array($data['status'], $allowedTransitions[$lockedOrder->status] ?? [], true)
            ) {
                throw ValidationException::withMessages([
                    'status' => 'Cette transition de commande n’est pas autorisée.',
                ]);
            }

            if ($lockedOrder->payment_status === 'paid' && $data['status'] === 'cancelled'
                && $data['payment_status'] !== 'refunded') {
                throw ValidationException::withMessages([
                    'payment_status' => 'Remboursez le paiement avant d’annuler cette commande.',
                ]);
            }

            if ($data['payment_status'] === 'paid' && $data['status'] === 'cancelled') {
                throw ValidationException::withMessages([
                    'payment_status' => 'Une commande annulée ne peut pas être marquée comme payée.',
                ]);
            }

            if ($lockedOrder->payment_status === 'refunded' && $data['payment_status'] !== 'refunded') {
                throw ValidationException::withMessages([
                    'payment_status' => 'Un paiement remboursé ne peut pas être réactivé.',
                ]);
            }

            if ($lockedOrder->payment_status === 'failed' && $data['payment_status'] === 'refunded') {
                throw ValidationException::withMessages([
                    'payment_status' => 'Un paiement échoué ne peut pas être remboursé.',
                ]);
            }

            // Restitution unique du stock au premier passage à l'état annulé.
            if ($lockedOrder->status !== 'cancelled' && $data['status'] === 'cancelled') {
                foreach ($lockedOrder->items as $item) {
                    if ($item->product_id !== null) {
                        $product = Product::query()->whereKey($item->product_id)->lockForUpdate()->first();
                        $product?->increment('stock', $item->quantity);
                    }
                }
            }

            $lockedOrder->update($data);

            $payment = $lockedOrder->payment()->lockForUpdate()->first();
            if ($payment) {
                $payment->update([
                    'status' => $data['payment_status'],
                    'paid_at' => $data['payment_status'] === 'paid'
                        ? ($payment->paid_at ?? now())
                        : null,
                ]);
            }
        }, 3);

        return back()->with('success', 'Commande mise à jour.');
    }

    public function reviews()
    {
        return view('admin.reviews', [
            'reviews' => Review::with(['user', 'product'])
                ->where('is_approved', false)
                ->latest()
                ->paginate(20),
        ]);
    }

    public function updateReview(Request $request, Review $review)
    {
        $data = $request->validate(['action' => 'required|in:approve,reject']);

        if ($data['action'] === 'approve') {
            $review->update(['is_approved' => true]);

            return back()->with('success', 'Avis publié.');
        }

        $review->delete();

        return back()->with('success', 'Avis refusé et supprimé.');
    }
}
