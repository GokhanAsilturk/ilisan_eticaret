<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>İlisan E-Ticaret</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 40px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .nav-links {
            margin-top: 30px;
        }
        .nav-links a {
            display: inline-block;
            margin-right: 20px;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .nav-links a:hover {
            background: #0056b3;
        }
        .admin-link {
            background: #dc3545 !important;
        }
        .admin-link:hover {
            background: #c82333 !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>İlisan E-Ticaret</h1>
        <p>Çelik yelek ve askeri malzeme mağazamıza hoş geldiniz.</p>
        <p>Bu platform güvenlik kuvvetleri ve sivil güvenlik personeli için özel tasarlanmış ürünlerin satışını yapmaktadır.</p>

        <div class="nav-links">
            @auth
                <a href="{{ url('/dashboard') }}">Dashboard</a>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Çıkış
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            @else
                <a href="{{ route('login') }}">Giriş</a>
                <a href="{{ route('register') }}">Kayıt</a>
            @endauth

            @if(auth()->check() && str_ends_with(auth()->user()->email, '@ilisan.com'))
                <a href="/admin" class="admin-link">Admin Panel</a>
            @endif
        </div>
    </div>
</body>
</html>
