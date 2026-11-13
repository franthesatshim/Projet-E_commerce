<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title','ShopNow') · ShopNow</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script>document.documentElement.classList.add("js")</script>
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
<header class="top">
    <a class="brand" href="{{ route('home') }}">Shop<span>Now</span></a>
    <nav>
        <a href="{{ route('products.index') }}">Boutique</a>
        <a href="{{ route('cart.index') }}">Panier ({{ collect(session('cart',[]))->sum('quantity') }})</a>
        @auth
            <a href="{{ route('account') }}">Mon compte</a>
            <a href="{{ route('account.favorites') }}">Mes favoris</a>
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}">Administration</a>
            @endif
            <form method="post" action="{{ route('logout') }}">@csrf<button class="link">Déconnexion</button></form>
        @endauth
        @guest
            <a href="{{ route('login') }}">Connexion</a>
            <a href="{{ route('register') }}">Créer un compte</a>
        @endguest
    </nav>
    <form method="post" action="{{ route('currency') }}" class="currency">
        @csrf
        <select name="currency" onchange="this.form.submit()">
            <option value="USD" @selected(session('currency','USD') === 'USD')>USD $</option>
            <option value="CDF" @selected(session('currency','USD') === 'CDF')>CDF FC</option>
        </select>
    </form>
</header>
<main class="container">
    @if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    @yield('content')
</main>
<footer>© {{ date('Y') }} ShopNow · Achetez simplement, en USD ou CDF.</footer>
</body>
</html>
