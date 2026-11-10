<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:120',
            'category' => 'nullable|integer|exists:categories,id',
            'min' => 'nullable|numeric|min:0|max:999999999999',
            'max' => 'nullable|numeric|min:0|max:999999999999|gte:min',
            'sort' => 'nullable|in:price_asc,price_desc',
        ]);

        $query = Product::with('category')->where('is_active', true);

        if (! empty($filters['q'])) {
            $term = str_replace(['%', '_'], ['\\%', '\\_'], $filters['q']);
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', '%'.$term.'%')
                ->orWhere('description', 'like', '%'.$term.'%'));
        }

        if (! empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        $column = session('currency', 'USD') === 'CDF' ? 'price_cdf' : 'price_usd';
        if (isset($filters['min'])) {
            $query->where($column, '>=', $filters['min']);
        }
        if (isset($filters['max'])) {
            $query->where($column, '<=', $filters['max']);
        }

        if (($filters['sort'] ?? null) === 'price_asc') {
            $query->orderBy($column);
        } elseif (($filters['sort'] ?? null) === 'price_desc') {
            $query->orderByDesc($column);
        } else {
            $query->latest();
        }

        return view('products.index', [
            'products' => $query->paginate(12)->withQueryString(),
            'categories' => Category::where('is_active', true)->get(),
            'currency' => session('currency', 'USD'),
        ]);
    }

    public function category(Category $category)
    {
        abort_unless($category->is_active, 404);

        return view('products.index', [
            'products' => $category->products()->with('category')->where('is_active', true)->paginate(12),
            'categories' => Category::where('is_active', true)->get(),
            'currency' => session('currency', 'USD'),
        ]);
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);
        $product->load([
            'category',
            'reviews' => fn ($query) => $query->where('is_approved', true)->with('user'),
        ]);

        return view('products.show', [
            'product' => $product,
            'currency' => session('currency', 'USD'),
        ]);
    }
}
