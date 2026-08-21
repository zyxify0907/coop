<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Login CoopBest untuk ahli, staff dan admin koperasi dalam satu borang.">
    <meta name="theme-color" content="#2453A6">
    <title>Login - CoopBest Koperasi Politeknik</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Warna Koperasi Politeknik (Diselaraskan dengan Homepage) */
            --primary: #C62828;          /* Merah CoopBest */
            --primary-dark: #991B1B;     /* Merah Gelap */
            --primary-soft: #FEF2F2;
            --secondary: #2453A6;        /* Biru CoopBest */
            --secondary-hover: #1D4ED8;
            --secondary-soft: #EFF6FF;
            --success: #16A34A;
            --success-soft: #DCFCE7;
            --danger: #B91C1C;
            --danger-soft: #FEE2E2;
            --warning: #D97706;
            --warning-soft: #FEF3C7;
            --dark: #0F172A;             
            --body: #475569;             
            --line: #E2E8F0;             
            --light: #F8FAFC;            
            --white: #FFFFFF;
            --radius: 10px;
            --shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
            --container: 1180px;
        }

        * { box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--dark);
            background: #FAFAFA;
            line-height: 1.7;
            font-size: 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            -webkit-font-smoothing: antialiased;
        }

        a { color: var(--secondary); text-decoration: underline; text-underline-offset: 4px; font-weight: 700; }
        a:hover { color: var(--primary); }

        /* Site Header */
        .site-header {
            position: fixed;
            inset: 0 0 auto;
            z-index: 100;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            border-bottom: 4px solid var(--primary);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        .nav {
            width: min(var(--container), calc(100% - 32px));
            height: 88px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 800;
            text-decoration: none;
            color: var(--dark);
        }
        .brand-mark {
            width: 58px;
            height: 58px;
            border-radius: 0;
            display: grid;
            place-items: center;
            background: transparent;
            box-shadow: none;
        }
        .brand-mark img { width: 58px; height: 58px; object-fit: contain; display: block; }
        .brand-title { 
            display: block; 
            font-size: 26px; 
            line-height: 1; 
            letter-spacing: -0.5px;
            color: var(--primary);
        }
        .brand-title::after {
            content: 'Best';
            color: var(--secondary);
            margin-left: 2px;
        }
        .brand-subtitle { display: block; margin-top: 4px; color: var(--body); font-size: 13px; font-weight: 700; }

        /* Interactive Button */
        .button {
            min-height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0 24px;
            border: 2px solid transparent;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.25s ease;
        }
        .button.primary { 
            background: var(--primary); 
            color: var(--white); 
            box-shadow: 0 4px 14px rgba(185, 28, 28, 0.25);
            width: 100%;
        }
        .button.primary:hover { 
            background: var(--primary-dark); 
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(185, 28, 28, 0.35);
        }
        .button.soft { 
            background: var(--white); 
            color: var(--secondary); 
            border: 2px solid var(--secondary); 
        }
        .button.soft:hover { 
            background: var(--secondary-soft); 
            color: var(--secondary-hover);
            transform: translateY(-2px);
            text-decoration: none;
        }

        /* Login Main Section */
        .login-section {
            padding: 140px 0 60px;
            background: #F7F8FA;
            flex-grow: 1;
            display: flex;
            align-items: center;
        }
        .login-container {
            width: min(var(--container), calc(100% - 32px));
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(380px, 0.9fr);
            gap: 50px;
            align-items: center;
        }

        /* Left Side: Info & Visual Feature */
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 100px;
            background: #DBEAFE;
            color: var(--secondary);
            font-size: 13px;
            font-weight: 800;
            border: 1px solid #93C5FD;
            margin-bottom: 12px;
        }
        .login-info h1 {
            margin: 12px 0 16px;
            font-size: clamp(32px, 3.8vw, 44px);
            line-height: 1.2;
            letter-spacing: -0.02em;
            color: var(--dark);
            font-weight: 800;
        }
        .login-info p {
            color: var(--body);
            font-size: 18px;
            margin-bottom: 28px;
        }

        .cooperative-features {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin: 0 0 26px;
        }
        .cooperative-feature {
            min-width: 0;
            min-height: 108px;
            padding: 15px 16px;
            border: 1px solid #D7DEE8;
            border-radius: 8px;
            background: #F1F4F8;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
        }
        .cooperative-feature span {
            display: block;
            margin-bottom: 4px;
            color: var(--secondary);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .cooperative-feature strong {
            display: block;
            color: var(--dark);
            font-size: 15px;
            line-height: 1.35;
        }
        .cooperative-feature small {
            display: none;
        }

        /* Feature Card Checklist */
        .feature-box {
            background: transparent;
            border: 0;
            border-top: 1px solid var(--line);
            border-radius: 0;
            padding: 22px 0 0;
            box-shadow: none;
        }
        .feature-box h3 { margin: 0 0 12px; font-size: 18px; color: var(--dark); font-weight: 700; }
        .feature-list { list-style: none; padding: 0; margin: 0; }
        .feature-list li { 
            padding-left: 28px; 
            position: relative; 
            margin-bottom: 10px; 
            font-weight: 600; 
            color: var(--body);
            font-size: 15px;
        }
        .feature-list li:last-child { margin-bottom: 0; }
        .feature-list li::before { 
            content: '✓'; 
            position: absolute; 
            left: 0; 
            color: var(--primary); 
            font-weight: 800; 
        }

        /* Right Side: Login Card */
        .login-card {
            background: var(--white);
            border: 2px solid var(--line);
            border-radius: var(--radius);
            padding: 40px;
            box-shadow: var(--shadow);
        }
        .login-card-head { margin-bottom: 24px; }
        .login-card-head h2 { margin: 0 0 6px; font-size: 26px; font-weight: 800; color: var(--dark); }
        .login-card-head p { margin: 0; color: var(--body); font-size: 14px; }

        /* Alerts */
        .alert {
            margin-bottom: 20px;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #FECACA;
            background: var(--primary-soft);
            color: #DC2626;
            font-weight: 700;
            font-size: 14px;
        }
        .alert.success {
            border-color: #A7F3D0;
            background: var(--success-soft);
            color: #047857;
        }

        /* Form Controls */
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 800;
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            height: 48px;
            border: 2px solid var(--line);
            border-radius: 8px;
            background: #F8FAFC;
            color: var(--dark);
            padding: 0 16px;
            outline: none;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.2s ease;
        }
        .form-group input:focus {
            background: var(--white);
            border-color: var(--secondary);
            box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.1);
        }

        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 14px;
        }
        .checkbox-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--body);
            font-weight: 600;
            cursor: pointer;
        }
        .checkbox-label input {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        /* Quick Help Section */
        .role-guide {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--line);
            display: grid;
            gap: 8px;
            font-size: 13px;
            color: var(--body);
        }
        .role-row { display: flex; align-items: center; gap: 10px; }
        .badge {
            min-width: 60px;
            padding: 2px 8px;
            text-align: center;
            border-radius: 100px;
            background: #DBEAFE;
            color: var(--secondary);
            font-size: 11px;
            font-weight: 800;
        }
        .badge.staff { background: var(--danger-soft); color: var(--primary-dark); }
        .badge.admin { background: var(--warning-soft); color: #92400E; }

        /* Site Footer */
        .site-footer {
            background: #111827;
            border-top: 1px solid #111827;
            padding: 24px 0;
            text-align: center;
            color: #F8FAFC;
            font-size: 14px;
            font-weight: 600;
        }

        /* Responsive Layout */
        @media (max-width: 900px) {
            .login-container { grid-template-columns: 1fr; gap: 32px; }
            .login-section { padding: 120px 0 40px; }
        }
        @media (max-width: 560px) {
            .nav { height: 72px; }
            .brand-subtitle { display: none; }
            .login-card { padding: 24px; }
            .form-row { flex-direction: column; align-items: flex-start; gap: 12px; }
        }
        /* Calm institutional login layout */
        body { background: #F7F8FA; font-size: 15px; line-height: 1.55; }
        .site-header { background: #fff; backdrop-filter: none; border-bottom: 1px solid var(--line); box-shadow: none; }
        .nav { height: 78px; }
        .brand-mark { width: 52px; height: 52px; border-radius: 0; background: transparent; box-shadow: none; }
        .brand-mark img { width: 52px; height: 52px; }
        .brand-title { font-size: 24px; letter-spacing: -.04em; }
        .brand-subtitle { font-size: 11px; font-weight: 600; }
        .button { min-height: 42px; padding: 0 16px; border-radius: 7px; font-size: 14px; box-shadow: none; }
        .button.primary { box-shadow: none; }
        .button.primary:hover { transform: none; box-shadow: none; }
        .button.soft { border: 1px solid #CBD5E1; }
        .button.soft:hover { transform: none; }
        .login-section { padding: 112px 0 48px; background: #F7F8FA; align-items: center; }
        .login-container { grid-template-columns: minmax(0, 1.25fr) minmax(390px, .8fr); gap: 28px; max-width: 1280px; align-items: center; }
        .login-info {
            min-height: 0;
            padding: 0;
            border: 0;
            border-radius: 0;
            background: transparent;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .eyebrow { margin-bottom: 10px; padding: 4px 8px; border: 0; border-radius: 4px; background: #FEF2F2; color: var(--primary); font-size: 11px; letter-spacing: .06em; }
        .login-info h1 { margin: 8px 0 12px; max-width: 520px; font-size: clamp(29px, 3.7vw, 38px); line-height: 1.2; font-weight: 700; }
        .login-info p { max-width: 540px; margin: 0 0 24px; color: var(--body); font-size: 15px; }
        .feature-box { padding: 20px 0 0; border: 0; border-top: 1px solid var(--line); border-radius: 0; background: transparent; box-shadow: none; }
        .feature-box h3 { font-size: 15px; }
        .feature-list li { margin-bottom: 8px; padding-left: 20px; font-size: 13px; font-weight: 500; }
        .feature-list li::before { content: '\2022'; color: var(--secondary); }
        .login-card {
            height: auto;
            min-height: 0;
            padding: 28px;
            border: 1px solid var(--line);
            border-radius: 10px;
            box-shadow: none;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-card-head { margin-bottom: 22px; }
        .login-card-head h2 { font-size: 22px; font-weight: 700; }
        .form-group { margin-bottom: 18px; }
        .form-group label { margin-bottom: 6px; color: var(--body); font-size: 12px; font-weight: 600; letter-spacing: .02em; }
        .form-group input[type="text"], .form-group input[type="password"] { height: 42px; border: 1px solid #CBD5E1; border-radius: 7px; background: #fff; font-weight: 500; }
        .form-group input:focus { border-color: var(--secondary); box-shadow: 0 0 0 3px rgba(36,83,166,.14); }
        .form-row { margin-bottom: 20px; }
        .checkbox-label input { accent-color: var(--secondary); }
        .role-guide { margin-top: 20px; padding-top: 16px; gap: 7px; font-size: 12px; }
        .badge { min-width: 56px; border-radius: 4px; font-weight: 600; }
        .badge.staff { background: #F1F5F9; color: #475569; }
        .badge.admin { background: #FEF2F2; color: var(--primary); }
        .site-footer { padding: 20px 0; background: #111827; border-color: #111827; color: #F8FAFC; font-size: 12px; }
        html, body { max-width: 100%; overflow-x: hidden; }
        img, svg { max-width: 100%; }
        @media (max-width: 900px) { .login-section { padding: 104px 0 36px; } .login-container { grid-template-columns: 1fr; gap: 28px; max-width: 620px; } .login-info { display: none; } }
        @media (max-width: 560px) {
            .nav { width: min(var(--container), calc(100% - 24px)); }
            .nav > .button { width: auto; min-height: 38px; padding: 0 12px; white-space: nowrap; }
            .login-section { padding: 92px 0 28px; }
            .login-card { padding: 20px; }
        }

        /* Responsive safety net: the form always follows the available viewport,
           including smaller laptop panels and browser zoom. */
        html, body { width: 100%; max-width: 100%; overflow-x: hidden; }
        .nav, .login-section, .login-container, .login-info, .login-card,
        .form-group, .form-row { min-width: 0; }
        .login-card, .login-container { max-width: 100%; }
        .form-group input[type="text"], .form-group input[type="password"], .button { max-width: 100%; }

        @media (min-width: 901px) and (max-width: 1100px) {
            .login-container { width: min(100% - 40px, 1020px); grid-template-columns: minmax(0, 1.15fr) minmax(340px, .85fr); gap: 20px; }
            .login-info { min-height: 0; padding: 0; }
            .login-card { min-height: 0; padding: 24px; }
            .cooperative-features { gap: 10px; }
            .cooperative-feature { min-height: 100px; padding: 13px; }
        }

        @media (max-width: 420px) {
            .nav { width: calc(100% - 20px); }
            .brand-mark { width: 44px; height: 44px; }
            .brand-mark img { width: 44px; height: 44px; }
            .brand-title { font-size: 20px; }
            .nav > .button { padding-inline: 10px; }
            .login-card { padding: 18px; }
        }
    </style>
</head>
<body>

    <!-- Site Header -->
    <header class="site-header">
        <nav class="nav">
            <a class="brand" href="{{ url('/') }}">
                <span class="brand-mark" aria-hidden="true">
                    <img src="{{ asset('images/koperasi-logo.svg?v=2') }}" alt="">
                </span>
                <span>
                    <span class="brand-title">Coop</span>
                    <span class="brand-subtitle">Koperasi Politeknik</span>
                </span>
            </a>
            <a class="button soft" href="{{ url('/') }}">Kembali ke Utama</a>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="login-section">
        <div class="login-container">
            
            <!-- Left Info Panel -->
            <div class="login-info">
                <span class="eyebrow">Akses Portal CoopBest</span>
                <h1>CoopBest Sistem Pengurusan Koperasi </h1>
                <p>Portal digital CoopBest Politeknik Besut untuk mengurus keanggotaan, saham koperasi, tempahan baju dan rekod kehadiran pekerja dalam satu sistem bersepadu.</p>

                <div class="cooperative-features" aria-label="Ciri utama koperasi">
                    <div class="cooperative-feature">
                        <span>👥 Keanggotaan</span>
                        <strong>Pengurusan permohonan dan rekod ahli koperasi</strong>
                    </div>
                    <div class="cooperative-feature">
                        <span>💰 Saham Koperasi</span>
                        <strong>Semakan saham, transaksi dan permohonan anggota</strong>
                    </div>
                    <div class="cooperative-feature">
                        <span>👕 Tempahan Baju</span>
                        <strong>Tempahan, pembayaran dan status pesanan</strong>
                    </div>
                    <div class="cooperative-feature">
                        <span>⏰ Smart Attendance</span>
                        <strong>Rekod kehadiran pekerja secara digital</strong>
                    </div>
                </div>

            </div>

            <!-- Right Login Form -->
            <div class="login-card">
                <div class="login-card-head">
                    <h2>Log Masuk</h2>
                    <p>Sila masukkan maklumat akaun anda untuk meneruskan.</p>
                </div>

                @if ($errors->any())
                    <div class="alert" role="alert">{{ $errors->first() }}</div>
                @endif

                @if (session('status'))
                    <div class="alert success" role="status">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}" id="login-form" autocomplete="off">
                    @csrf

                    <div class="form-group">
                        <label for="identifier">No Matrik / No. KP</label>
                        <input id="identifier" name="identifier" type="text" value="{{ old('identifier') }}" required autofocus placeholder="Cth: 13DDT22F1001 atau No. KP" autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label for="password">Kata Laluan</label>
                        <input id="password" name="password" type="password" required placeholder="Masukkan kata laluan" autocomplete="current-password">
                    </div>

                    <div class="form-row">
                        <label class="checkbox-label" for="remember">
                            <input id="remember" name="remember" type="checkbox" @checked(old('remember'))>
                            Ingat Saya
                        </label>
                        <a href="#">Lupa Kata Laluan?</a>
                    </div>

                    <button class="button primary" type="submit" id="login-btn">Log Masuk</button>
                </form>

                <div class="role-guide">
                    <div class="role-row"><span class="badge">Pelajar</span><span>Log masuk menggunakan nombor matrik dan kata laluan berdaftar.</span></div>
                    <div class="role-row"><span class="badge staff">Staff</span><span>Log masuk menggunakan No. KP dan akaun staff.</span></div>
                    <div class="role-row"><span class="badge admin">Admin</span><span>Log masuk menggunakan No. KP pentadbir sistem.</span></div>
                </div>
            </div>

        </div>
    </main>

    <!-- Site Footer -->
    <footer class="site-footer">
        &copy; {{ date('Y') }} CoopBest. Hak Cipta Terpelihara. Koperasi Politeknik Malaysia.
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('login-form');
            const submitBtn = document.getElementById('login-btn');
            if (form && submitBtn) {
                form.addEventListener('submit', function () {
                    if (form.checkValidity()) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Memproses...';
                    }
                });
            }
        });
    </script>
</body>
</html>
