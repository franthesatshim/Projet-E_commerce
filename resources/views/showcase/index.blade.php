@extends('layouts.app')
@section('title', 'Collection photos')
@section('content')
<section class="showcase-heading">
  <p class="eyebrow">SHOPNOW · COLLECTION</p>
  <h1>Découvrez notre sélection en images</h1>
  <p class="muted">Des inspirations pour votre prochaine commande. Les articles, prix et disponibilités sont confirmés dans la boutique.</p>
</section>
<nav class="showcase-filters" aria-label="Filtrer les photos">
  <a class="showcase-filter {{ $category === null ? 'is-active' : '' }}" href="{{ route('showcase.index') }}" @if($category === null) aria-current="page" @endif>Tout voir</a>
  @foreach($categories as $name)
    <a class="showcase-filter {{ $category === $name ? 'is-active' : '' }}" href="{{ route('showcase.index', ['categorie' => $name]) }}" @if($category === $name) aria-current="page" @endif>{{ $name }}</a>
  @endforeach
</nav>
<div class="showcase-grid">
  @foreach($items as $photo)
  <figure class="showcase-tile">
    <img src="{{ asset($photo['path']) }}" alt="{{ $photo['title'] }}" loading="lazy" decoding="async" width="400" height="400">
    <figcaption><span>{{ $photo['category'] }}</span><strong>{{ $photo['title'] }}</strong></figcaption>
  </figure>
  @endforeach
</div>
@endsection
