@extends('layouts.app')
@section('title','Mes favoris')
@section('content')
<h1>Mes favoris</h1>
<div class="grid">
    @forelse($products as $product)
        <article class="product">
            <a href="{{ route('products.show', $product) }}">
                <div class="photo">
                    @if($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
                    @else
                        <span>SN</span>
                    @endif
                </div>
                <small>{{ $product->category?->name }}</small>
                <h3>{{ $product->name }}</h3>
            </a>
            <form method="post" action="{{ route('favorites.toggle', $product) }}">
                @csrf
                <button class="btn secondary small">Retirer des favoris</button>
            </form>
        </article>
    @empty
        <p>Vous n’avez pas encore ajouté de favoris.</p>
    @endforelse
</div>
{{ $products->links() }}
@endsection
