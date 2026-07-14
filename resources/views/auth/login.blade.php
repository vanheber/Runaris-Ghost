<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Entrar - Runaris Ghost') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --brand-primary: #6366f1;
            --brand-secondary: #a855f7;
            --bg-dark: #0f172a;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background: url("{{ Vite::asset('resources/assets/images/bg-app.jpg') }}") no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        .brand-logo {
            font-weight: 800;
            font-size: 2rem;
            letter-spacing: -0.05em;
            background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 2rem;
            display: block;
        }
        .form-control {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 12px;
            padding: 0.8rem 1rem;
            text-align: center;
        }
        .btn-brand {
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            border: none;
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }
        .btn-brand:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4);
            color: white;
        }
    </style>
</head>
<body>

    <div class="glass-card">
        <div class="text-center mb-4">
            <img src="{{ Vite::asset('resources/assets/images/ghost-icon-transparent.png') }}" alt="Runaris Ghost" class="mb-3" style="width: 70px; filter: drop-shadow(0 0 15px rgba(111, 66, 193, 0.3));">
            <span class="brand-logo mb-0">Runaris <span class="opacity-50">Ghost</span></span>
        </div>
        
        <form method="POST" action="/login">
            @csrf
            
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="{{ __('E-mail') }}" value="{{ old('email') }}" required autofocus>
            </div>
            
            <div class="mb-4">
                <input type="password" name="password" class="form-control" placeholder="{{ __('Senha') }}" required>
            </div>

            @if ($errors->any())
                <div class="text-danger small mb-3">
                    {{ __('Credenciais incorretas.') }}
                </div>
            @endif

            <button type="submit" class="btn btn-brand">{{ __('Entrar no Santuário') }}</button>
            
            <div class="mt-4">
                <p class="text-secondary small">{{ __('Esqueceu sua senha?') }} <a href="#" class="text-primary text-decoration-none">{{ __('Suporte') }}</a></p>
            </div>
        </form>
    </div>

</body>
</html>
