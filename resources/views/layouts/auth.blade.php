{{-- Guardar como: resources/views/layouts/auth.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'Acesso') - Plataforma Nacional de Rastreio e Declaração de Saúde do Viajante</title>

    <style>
        :root {
            --primary: #1E1611;
            --primary-dark: #075c50;
            --primary-soft: #e8f5f1;
            --background: #f3f7f7;
            --surface: #ffffff;
            --text: #18312e;
            --muted: #687c79;
            --border: #dce7e4;
            --danger: #b42318;
            --danger-bg: #fff0ef;
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body { min-height: 100%; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: var(--background);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .auth-wrapper { width: 100%; max-width: 430px; }

        .brand {
            margin: 0 0 22px;
            text-align: center;
        }

        .brand-mark {
            width: 42px;
            height: 4px;
            margin: 0 auto 20px;
            border-radius: 4px;
            background: var(--primary);
        }

        .brand h1 {
            margin: 0 auto;
            max-width: 390px;
            font-size: clamp(1.25rem, 3.8vw, 1.65rem);
            font-weight: 700;
            line-height: 1.35;
            letter-spacing: -0.035em;
        }

        .auth-card {
            padding: 32px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 12px 35px rgba(22, 65, 57, 0.06);
        }

        .auth-card h2 {
            margin: 0 0 8px;
            color: var(--text);
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .intro {
            margin: 0 0 24px;
            color: var(--muted);
            font-size: .9rem;
            line-height: 1.5;
        }

        .field { margin-bottom: 18px; }

        .field label {
            display: block;
            margin-bottom: 7px;
            color: #354b47;
            font-size: .83rem;
            font-weight: 600;
        }

        .field input[type="email"],
        .field input[type="password"],
        .field input[type="text"] {
            display: block;
            width: 100%;
            min-height: 46px;
            padding: 11px 13px;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: #fff;
            color: var(--text);
            font: inherit;
            font-size: .95rem;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .field input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(8, 124, 104, .12);
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 2px 0 20px;
            color: var(--muted);
            font-size: .87rem;
        }

        .remember input { width: 16px; height: 16px; accent-color: var(--primary); }

        .btn {
            display: block;
            width: 100%;
            min-height: 46px;
            padding: 12px 16px;
            border: 0;
            border-radius: 7px;
            background: var(--primary);
            color: #fff;
            font: inherit;
            font-size: .92rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s ease, transform .15s ease;
        }

        .btn:hover { background: var(--primary-dark); }
        .btn:active { transform: translateY(1px); }
        .btn:focus-visible { outline: 3px solid #a9ded3; outline-offset: 2px; }

        .card-footer {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px 16px;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: .85rem;
        }

        .card-footer a { color: var(--primary-dark); text-decoration: underline; text-underline-offset: 3px; }
        .card-footer a:hover { color: var(--primary); }

        .notice {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 7px;
            font-size: .88rem;
            line-height: 1.5;
        }

        .notice-info { border: 1px solid #b9e1d7; background: var(--primary-soft); color: var(--primary-dark); }
        .notice-error { border: 1px solid #f4c7c3; background: var(--danger-bg); color: var(--danger); }
        .notice ul { margin: 6px 0 0; padding-left: 20px; }

        @media (max-width: 480px) {
            body { padding: 18px; }
            .brand { margin-bottom: 18px; }
            .auth-card { padding: 25px 21px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition: none !important; }
        }
    </style>
</head>
<body>
    <main class="auth-wrapper">
        <header class="brand">
            <div class="brand-mark" aria-hidden="true"></div>
            <h1>Plataforma Nacional de Rastreio e Declaração de Saúde do Viajante</h1>
        </header>

        <section class="auth-card">
            @hasSection('card_title')
                <h2>@yield('card_title')</h2>
            @endif

            @hasSection('card_intro')
                <p class="intro">@yield('card_intro')</p>
            @endif

            @if (session('status'))
                <div class="notice notice-info" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="notice notice-error" role="alert">
                    <strong>{{ __('Verifique os dados introduzidos.') }}</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

            @hasSection('card_footer')
                <div class="card-footer">@yield('card_footer')</div>
            @endif
        </section>
    </main>
</body>
</html>
