<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lupa Kata Laluan - CoopBest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #C62828;
            --primary-dark: #991B1B;
            --primary-soft: #FEF2F2;
            --secondary: #2453A6;
            --secondary-soft: #EFF6FF;
            --success: #16A34A;
            --success-soft: #DCFCE7;
            --line: #E2E8F0;
            --text: #0F172A;
            --muted: #475569;
            --surface: #FFFFFF;
            --surface-soft: #F8FAFC;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: #F7F8FA;
            color: var(--text);
            font-family: 'Inter', system-ui, sans-serif;
        }

        .reset-wrap {
            width: min(100%, 980px);
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(360px, 420px);
            gap: 24px;
            align-items: stretch;
        }

        .reset-info,
        .reset-card {
            border: 1px solid var(--line);
            border-radius: 18px;
            background: var(--surface);
            box-shadow: 0 18px 42px rgba(15, 23, 42, 0.06);
        }

        .reset-info {
            padding: 34px;
            display: grid;
            align-content: space-between;
            gap: 28px;
            background: linear-gradient(180deg, #FFFFFF 0%, #FBFDFF 100%);
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: var(--text);
            text-decoration: none;
            font-weight: 800;
        }

        .brand-mark {
            width: 52px;
            height: 52px;
            display: grid;
            place-items: center;
        }

        .brand-mark img {
            width: 52px;
            height: 52px;
            object-fit: contain;
            display: block;
        }

        .brand-title {
            display: block;
            font-size: 24px;
            line-height: 1;
            color: var(--primary);
        }

        .brand-title::after {
            content: 'Best';
            color: var(--secondary);
            margin-left: 2px;
        }

        .brand-subtitle {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .reset-copy span {
            display: inline-flex;
            min-height: 28px;
            align-items: center;
            padding: 0 10px;
            border-radius: 999px;
            background: var(--secondary-soft);
            color: var(--secondary);
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .reset-copy h1 {
            margin: 14px 0 10px;
            font-size: 36px;
            line-height: 1.12;
            letter-spacing: 0;
        }

        .reset-copy p {
            margin: 0;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.65;
        }

        .reset-points {
            display: grid;
            gap: 12px;
            padding-top: 4px;
        }

        .reset-point {
            display: grid;
            gap: 5px;
            padding: 14px 16px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--surface-soft);
        }

        .reset-point strong {
            font-size: 14px;
        }

        .reset-point span {
            color: var(--muted);
            font-size: 13px;
            font-weight: 600;
        }

        .reset-card {
            padding: 28px;
        }

        .reset-card h2 {
            margin: 0;
            font-size: 26px;
            line-height: 1.1;
        }

        .reset-card p {
            margin: 10px 0 24px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.55;
        }

        .alert {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #FECACA;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 14px;
            font-weight: 700;
        }

        .alert.success {
            border-color: #A7F3D0;
            background: var(--success-soft);
            color: #047857;
        }

        .form-grid {
            display: grid;
            gap: 16px;
        }

        label {
            display: grid;
            gap: 7px;
            color: #334155;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        input {
            width: 100%;
            min-height: 48px;
            border: 1px solid #CBD5E1;
            border-radius: 10px;
            background: #fff;
            color: var(--text);
            padding: 12px 14px;
            font: inherit;
            font-size: 15px;
            font-weight: 700;
            outline: 0;
        }

        input:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 4px rgba(36, 83, 166, 0.12);
        }

        .button-row {
            display: grid;
            gap: 12px;
            margin-top: 6px;
        }

        .button {
            min-height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 900;
            cursor: pointer;
            transition: .2s ease;
        }

        .button.primary {
            border: 1px solid var(--primary);
            background: var(--primary);
            color: #fff;
        }

        .button.primary:hover {
            background: var(--primary-dark);
        }

        .button.secondary {
            border: 1px solid var(--line);
            background: #fff;
            color: var(--secondary);
        }

        .button.secondary:hover {
            background: var(--secondary-soft);
        }

        .form-note {
            margin-top: 14px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.6;
        }

        @media (max-width: 920px) {
            .reset-wrap {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 560px) {
            body {
                padding: 16px;
            }

            .reset-info,
            .reset-card {
                padding: 22px;
            }

            .reset-copy h1 {
                font-size: 30px;
            }
        }
    </style>
</head>
<body>
    @include('components.flash-notification')
    <div class="reset-wrap">
        <section class="reset-info">
            <a class="brand" href="{{ url('/') }}">
                <span class="brand-mark" aria-hidden="true">
                    <img src="{{ asset('images/koperasi-logo.svg?v=2') }}" alt="">
                </span>
                <span>
                    <span class="brand-title">Coop</span>
                    <span class="brand-subtitle">Koperasi Politeknik</span>
                </span>
            </a>

            <div class="reset-copy">
                <span>Reset Akses Akaun</span>
                <h1>Lupa Kata Laluan</h1>
                <p>Sahkan identiti anda menggunakan ID akaun dan No. KP, kemudian tetapkan kata laluan baharu untuk terus log masuk semula ke CoopBest.</p>
            </div>

            <div class="reset-points">
                <div class="reset-point">
                    <strong>Pelajar</strong>
                    <span>Guna nombor matrik atau No. KP yang didaftarkan.</span>
                </div>
                <div class="reset-point">
                    <strong>Staff</strong>
                    <span>Guna No. Pekerja, email, atau No. KP bersama No. KP pengesahan.</span>
                </div>
                <div class="reset-point">
                    <strong>Admin</strong>
                    <span>Guna username atau No. KP untuk reset kata laluan.</span>
                </div>
            </div>
        </section>

        <section class="reset-card">
            <h2>Tetapkan Kata Laluan Baharu</h2>
            <p>Masukkan maklumat akaun anda dengan tepat untuk kemas kini kata laluan.</p>

            @if ($errors->any())
                <div class="alert" role="alert">{{ $errors->first() }}</div>
            @endif

            @if (session('status'))
                <div class="alert success" role="status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.forgot.submit') }}">
                @csrf
                <div class="form-grid">
                    <label for="identifier">
                        No Matrik / No. KP / Username
                        <input id="identifier" name="identifier" type="text" value="{{ old('identifier') }}" required autofocus placeholder="Contoh: 13DDT22F1001 atau No. KP">
                    </label>

                    <label for="nric">
                        No. KP Pengesahan
                        <input id="nric" name="nric" type="text" value="{{ old('nric') }}" required placeholder="Masukkan No. KP anda">
                    </label>

                    <label for="password">
                        Kata Laluan Baharu
                        <input id="password" name="password" type="password" required placeholder="Minimum 6 aksara">
                    </label>

                    <label for="password_confirmation">
                        Sahkan Kata Laluan Baharu
                        <input id="password_confirmation" name="password_confirmation" type="password" required placeholder="Ulang kata laluan baharu">
                    </label>
                </div>

                <div class="button-row">
                    <button class="button primary" type="submit">Reset Kata Laluan</button>
                    <a class="button secondary" href="{{ route('login') }}">Kembali ke Login</a>
                </div>
            </form>

            <div class="form-note">
                Jika maklumat tidak sepadan, reset tidak akan diproses. Untuk kes akaun lama atau data identiti tidak lengkap, admin sistem masih boleh bantu kemas kini secara manual.
            </div>
        </section>
    </div>
</body>
</html>
