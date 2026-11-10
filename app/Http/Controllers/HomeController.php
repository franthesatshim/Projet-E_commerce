<?php
namespace App\Http\Controllers;use App\Models\{Product,Category};use Illuminate\Http\Request;class HomeController extends Controller{public function index(Request $r){return view('home',['products'=>Product::with('category')->where('is_active',true)->latest()->take(8)->get(),'categories'=>Category::where('is_active',true)->orderBy('name')->get(),'currency'=>session('currency','USD')]);}}
