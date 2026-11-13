<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShowcaseController extends Controller
{
    public function index(Request $request)
    {
        $all = collect(config('showcase', []));
        $categories = $all->pluck('category')->unique()->values();
        $category = $request->query('categorie');
        if (!is_string($category) || ! $categories->contains($category)) {
            $category = null;
        }
        $items = $category ? $all->where('category', $category)->values() : $all;
        return view('showcase.index', compact('items', 'categories', 'category'));
    }
}
