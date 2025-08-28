<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - İlisan E-Ticaret</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 40px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .nav-links a {
            display: inline-block;
            margin-left: 20px;
            padding: 8px 16px;
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
        .welcome-message {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Dashboard</h1>
            <div class="nav-links">
                <a href="{{ url('/') }}">Ana Sayfa</a>
                @if(auth()->check() && str_ends_with(auth()->user()->email, '@ilisan.com'))
                    <a href="/admin" class="admin-link">Admin Panel</a>
                @endif
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Çıkış
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>

        <div class="welcome-message">
            <h2>Hoş geldiniz, {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}!</h2>
            <p>İlisan E-Ticaret kullanıcı paneline hoş geldiniz.</p>
        </div>

        <div class="content">
            <h3>Hesap Bilgileri</h3>
            <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
            <p><strong>Kayıt Tarihi:</strong> {{ auth()->user()->created_at->format('d.m.Y H:i') }}</p>
            @if(auth()->user()->last_login_at)
                <p><strong>Son Giriş:</strong> {{ auth()->user()->last_login_at->format('d.m.Y H:i') }}</p>
            @endif
        </div>
    </div>
</body>
</html>
