@extends('layouts.app')
@section('title','Modération des avis')
@section('content')
<h1>Modération des avis</h1>
@forelse($reviews as $review)
    <article class="panel review-admin">
        <div>
            <strong>{{ $review->product?->name ?? 'Produit supprimé' }}</strong>
            <p>{{ $review->user->name }} · {{ $review->rating }}/5</p>
            <p>{{ $review->comment }}</p>
        </div>
        <div class="inline">
            <form method="post" action="{{ route('admin.reviews.update', $review) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="action" value="approve">
                <button class="btn small">Publier</button>
            </form>
            <form method="post" action="{{ route('admin.reviews.update', $review) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="action" value="reject">
                <button class="btn small secondary">Refuser</button>
            </form>
        </div>
    </article>
@empty
    <p>Aucun avis en attente de modération.</p>
@endforelse
{{ $reviews->links() }}
@endsection
