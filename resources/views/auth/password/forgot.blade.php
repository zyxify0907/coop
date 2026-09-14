<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lupa Kata Laluan - CoopBest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --cb-navy: #082F59;
            --cb-navy-dark: #062747;
            --cb-red: #ED1C2E;
            --cb-blue: #0B5ED7;
            --cb-white: #FFFFFF;
            --cb-soft: #F4F7FB;
            --cb-border: #CBD8E8;
            --cb-muted: #526987;
            --cb-error: #B91C1C;
        }

        * { box-sizing: border-box; }
        html { width: 100%; min-height: 100%; background: var(--cb-white); }
        body {
            margin: 0;
            width: 100%;
            min-height: 100%;
            min-height: 100dvh;
            background: var(--cb-white);
            color: var(--cb-navy);
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }

        .forgot-shell {
            position: relative;
            isolation: isolate;
            height: 100dvh;
            min-height: 680px;
            display: grid;
            grid-template-columns: minmax(0, 46%) minmax(0, 54%);
            width: 100%;
            background: var(--cb-white);
            overflow-x: hidden;
        }

        .forgot-bg {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            height: 100%;
            pointer-events: none;
            clip-path: polygon(0 0, 100% 0, 74% 100%, 0 100%);
        }

        .forgot-bg--soft { z-index: 0; width: 51%; background: var(--cb-soft); }
        .forgot-bg--red { z-index: 1; width: 46.7%; background: var(--cb-red); }
        .forgot-bg--navy { z-index: 2; width: 46%; background: var(--cb-navy); }

        .forgot-brand-panel {
            position: relative;
            z-index: 3;
            display: grid;
            display: flex;
            align-items: flex-start;
            min-width: 0;
            min-height: 100dvh;
            padding: clamp(120px, 16vh, 150px) clamp(46px, 4.5vw, 74px) clamp(46px, 4.5vw, 74px);
            color: var(--cb-white);
            overflow: hidden;
        }

        .forgot-watermark {
            position: absolute;
            left: clamp(-70px, -4vw, -24px);
            bottom: clamp(-85px, -8vw, -32px);
            width: min(62vw, 650px);
            max-width: 88%;
            opacity: .08;
            pointer-events: none;
            user-select: none;
        }

        .forgot-brand-content {
            position: relative;
            z-index: 4;
            display: grid;
            gap: clamp(30px, 4.8vh, 58px);
            width: 100%;
            max-width: 570px;
            padding-right: 30px;
        }

        .forgot-brand {
            display: flex;
            align-items: center;
            gap: clamp(16px, 1.8vw, 24px);
            max-width: 530px;
            min-width: 0;
        }

        .forgot-brand__logo {
            width: clamp(80px, 6.6vw, 118px);
            max-width: 26vw;
            flex: 0 0 auto;
        }

        .forgot-brand__divider {
            width: 2px;
            height: clamp(76px, 7vw, 112px);
            background: rgba(255, 255, 255, .86);
        }

        .forgot-brand__wordmark {
            min-width: 0;
        }

        .forgot-brand__wordmark strong {
            display: block;
            color: var(--cb-white);
            font-size: clamp(34px, 3.2vw, 54px);
            line-height: 1;
            font-weight: 900;
            letter-spacing: 0;
            white-space: nowrap;
        }

        .forgot-brand__wordmark strong span { color: var(--cb-red); }
        .forgot-brand__wordmark small {
            display: block;
            margin-top: 10px;
            color: var(--cb-white);
            font-size: clamp(17px, 1.55vw, 25px);
            line-height: 1.1;
            font-weight: 800;
            white-space: nowrap;
        }

        .forgot-copy {
            display: grid;
            gap: 14px;
        }

        .forgot-copy__mark {
            width: 78px;
            height: 7px;
            border-radius: 999px;
            background: var(--cb-red);
        }

        .forgot-copy h1 {
            margin: 18px 0 0;
            color: var(--cb-white);
            max-width: 560px;
            font-size: clamp(38px, 3vw, 54px);
            line-height: 1.08;
            font-weight: 900;
            letter-spacing: 0;
            overflow-wrap: normal;
            white-space: nowrap;
        }

        .forgot-copy h2 {
            margin: 0;
            color: var(--cb-white);
            max-width: 560px;
            font-size: clamp(28px, 2.25vw, 40px);
            line-height: 1.15;
            font-weight: 500;
            letter-spacing: 0;
            overflow-wrap: normal;
            white-space: nowrap;
        }

        .forgot-copy p {
            max-width: 470px;
            margin: 0;
            color: rgba(255, 255, 255, .90);
            font-size: clamp(16px, 1.25vw, 21px);
            line-height: 1.5;
            font-weight: 500;
        }

        .forgot-divider { display: none; }

        .forgot-form-panel {
            position: relative;
            z-index: 3;
            display: grid;
            grid-template-rows: auto minmax(0, 1fr) auto;
            min-width: 0;
            min-height: 100dvh;
            grid-column: 2;
            padding: clamp(28px, 4vh, 46px) clamp(48px, 3vw, 60px) clamp(20px, 3vh, 32px);
        }

        .forgot-back-link {
            justify-self: end;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: var(--cb-blue);
            font-size: clamp(16px, 1.4vw, 24px);
            font-weight: 500;
            text-decoration: none;
        }

        .forgot-back-link:hover { text-decoration: underline; }
        .forgot-back-link svg { width: 32px; height: 32px; }

        .forgot-form-wrap {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 790px;
            align-self: start;
            justify-self: center;
            margin: clamp(48px, 5vh, 54px) auto 0;
            padding: 0;
        }

        .forgot-form-head h1 {
            position: relative;
            margin: 0;
            padding-bottom: 22px;
            color: #071747;
            font-size: clamp(38px, 3vw, 52px);
            line-height: 1.1;
            font-weight: 900;
            letter-spacing: 0;
        }

        .forgot-form-head h1::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 86px;
            height: 7px;
            border-radius: 999px;
            background: var(--cb-red);
        }

        .forgot-form-head p {
            margin: 18px 0 28px;
            color: var(--cb-muted);
            font-size: clamp(16px, 1.15vw, 20px);
            line-height: 1.5;
            font-weight: 500;
        }

        .forgot-alert {
            margin: 0 0 18px;
            padding: 13px 15px;
            border: 1px solid #FCA5A5;
            border-radius: 8px;
            background: #FEF2F2;
            color: var(--cb-error);
            font-size: 14px;
            font-weight: 800;
        }

        .forgot-alert.success {
            border-color: #86EFAC;
            background: #F0FDF4;
            color: #087A45;
        }

        .forgot-form {
            display: grid;
            gap: 22px;
            margin-top: 40px;
        }

        .forgot-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;
        }

        .forgot-field {
            display: grid;
            gap: 9px;
            min-width: 0;
        }

        .forgot-field label {
            color: #071747;
            font-size: clamp(14px, 1.12vw, 20px);
            line-height: 1.2;
            font-weight: 800;
        }

        .forgot-input {
            position: relative;
            display: grid;
            grid-template-columns: 52px minmax(0, 1fr) 48px;
            align-items: center;
            min-width: 0;
            min-height: 58px;
            width: 100%;
            overflow: hidden;
        }

        .forgot-input svg {
            position: absolute;
            z-index: 1;
            left: 13px;
            width: 26px;
            height: 26px;
            color: #526987;
            pointer-events: none;
        }

        .forgot-input input {
            width: 100%;
            grid-column: 1 / -1;
            grid-row: 1;
            min-width: 0;
            min-height: 58px;
            border: 1px solid var(--cb-border);
            border-radius: 8px;
            background: var(--cb-white);
            color: #071747;
            padding: 0 10px 0 52px;
            font: inherit;
            font-size: clamp(14px, 1vw, 17px);
            font-weight: 600;
            outline: none;
            text-overflow: clip;
            transition: border-color .16s ease, box-shadow .16s ease;
        }

        .forgot-input input::placeholder {
            color: #667A99;
            opacity: 1;
        }

        .forgot-input input:focus {
            border-color: var(--cb-blue);
            box-shadow: 0 0 0 4px rgba(11, 94, 215, .14);
        }

        .forgot-field.has-error .forgot-input input {
            border-color: var(--cb-error);
            box-shadow: 0 0 0 3px rgba(185, 28, 28, .10);
        }

        .forgot-password-button {
            position: static;
            z-index: 2;
            grid-column: 3;
            grid-row: 1;
            justify-self: center;
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #334B70;
            cursor: pointer;
        }

        .forgot-password-button svg {
            position: static;
            width: 27px;
            height: 27px;
            pointer-events: none;
        }

        .forgot-password-button:hover,
        .forgot-password-button:focus-visible {
            background: #EAF2FF;
            color: var(--cb-blue);
            outline: none;
        }

        .forgot-password-button .eye-closed,
        .forgot-password-button.is-visible .eye-open { display: none; }
        .forgot-password-button.is-visible .eye-closed { display: block; }
        .forgot-input.has-toggle input { padding-right: 54px; }

        .forgot-error {
            color: var(--cb-error);
            font-size: 13px;
            font-weight: 800;
        }

        .forgot-password-note {
            margin: -8px 0 0;
            color: var(--cb-muted);
            font-size: clamp(14px, 1.05vw, 18px);
            font-weight: 500;
        }

        .forgot-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            min-height: 58px;
            border: 1px solid var(--cb-red);
            border-radius: 8px;
            background: var(--cb-red);
            color: var(--cb-white);
            font: inherit;
            font-size: clamp(18px, 1.6vw, 25px);
            font-weight: 900;
            cursor: pointer;
            transition: background-color .16s ease, border-color .16s ease, opacity .16s ease;
        }

        .forgot-submit:hover,
        .forgot-submit:focus-visible {
            border-color: #C91525;
            background: #C91525;
            outline: none;
        }

        .forgot-submit:disabled {
            opacity: .72;
            cursor: wait;
        }

        .forgot-submit__spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 3px solid rgba(255, 255, 255, .35);
            border-top-color: #FFFFFF;
            border-radius: 999px;
            animation: forgot-spin .8s linear infinite;
        }

        .forgot-submit.is-loading .forgot-submit__spinner { display: inline-block; }
        @keyframes forgot-spin { to { transform: rotate(360deg); } }

        .forgot-login-note {
            margin: 14px 0 0;
            color: #294569;
            font-size: clamp(15px, 1.2vw, 20px);
            text-align: center;
            font-weight: 500;
        }

        .forgot-login-note a {
            color: var(--cb-blue);
            font-weight: 900;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .forgot-footer {
            position: relative;
            z-index: 2;
            align-self: end;
            justify-self: center;
            color: #294569;
            font-size: clamp(13px, .95vw, 16px);
            font-weight: 500;
            text-align: center;
            padding-top: 20px;
        }

        @media (max-width: 1360px) {
            .forgot-shell { grid-template-columns: minmax(0, 46%) minmax(0, 54%); }
            .forgot-brand-content {
                max-width: 570px;
                padding-right: 30px;
            }
            .forgot-form-panel {
                padding-left: clamp(48px, 3vw, 60px);
                padding-right: clamp(48px, 3vw, 60px);
            }
            .forgot-brand { gap: 20px; }
            .forgot-brand__logo { width: 118px; }
            .forgot-brand__divider { height: 104px; }
        }

        @media (max-width: 1080px) {
            .forgot-shell { grid-template-columns: minmax(0, 40%) minmax(0, 60%); }
            .forgot-form-grid {
                grid-template-columns: 1fr;
                gap: 18px;
            }

            .forgot-form-wrap { max-width: 680px; }
        }

        @media (max-width: 860px) {
            body { overflow-y: auto; }
            .forgot-shell {
                min-height: 100dvh;
                display: block;
            }

            .forgot-bg { display: none; }

            .forgot-brand-panel {
                min-height: auto;
                padding: 30px 24px 34px;
                background: var(--cb-navy);
                border-bottom: 7px solid var(--cb-red);
            }

            .forgot-watermark { display: none; }
            .forgot-brand-content {
                gap: 24px;
                min-width: 0;
                max-width: none;
                padding-right: 0;
            }

            .forgot-copy h1,
            .forgot-copy h2 {
                width: 100%;
                max-width: 100%;
                white-space: normal;
            }

            .forgot-brand__logo { width: 84px; }
            .forgot-brand__divider { height: 78px; }
            .forgot-copy__mark {
                width: 56px;
                height: 5px;
            }

            .forgot-copy h1 { margin-top: 10px; }
            .forgot-form-panel {
                min-height: auto;
                grid-template-rows: auto auto auto;
                padding: 22px 20px 28px;
            }

            .forgot-form-panel::before { display: none; }
            .forgot-back-link { justify-self: start; }
            .forgot-form-wrap {
                width: 100%;
                padding: 32px 0;
            }
        }

        @media (max-width: 520px) {
            .forgot-brand {
                align-items: flex-start;
                gap: 14px;
            }

            .forgot-brand__divider { display: none; }
            .forgot-brand__wordmark strong { font-size: 36px; }
            .forgot-brand__wordmark small { font-size: 18px; }
            .forgot-input input {
                min-height: 56px;
                padding-left: 52px;
            }

            .forgot-input svg {
                left: 14px;
                width: 24px;
                height: 24px;
            }
        }

        @media (max-width: 1200px) {
            .forgot-copy h1,
            .forgot-copy h2 { white-space: normal; }
        }

        @media (min-width: 1025px) and (min-height: 760px) {
            body { overflow-y: hidden; }
            .forgot-shell { height: 100dvh; min-height: 0; }
        }

        @media (max-height: 820px) and (min-width: 1025px) {
            .forgot-form-panel { padding-top: 20px; padding-bottom: 16px; }
            .forgot-form-head h1 { font-size: 42px; }
            .forgot-form-head p { margin-bottom: 22px; }
            .forgot-form-grid { gap: 16px 20px; }
            .forgot-input,
            .forgot-input input { min-height: 52px; }
            .forgot-submit { min-height: 52px; }
        }
    </style>
</head>
<body>
    @include('components.flash-notification')

    <main class="forgot-shell">
        <span class="forgot-bg forgot-bg--soft" aria-hidden="true"></span>
        <span class="forgot-bg forgot-bg--red" aria-hidden="true"></span>
        <span class="forgot-bg forgot-bg--navy" aria-hidden="true"></span>

        <section class="forgot-brand-panel" aria-label="Identiti CoopBest">
            <img class="forgot-watermark" src="{{ asset('images/koperasi-logo.svg?v=3') }}" alt="" aria-hidden="true">

            <div class="forgot-brand-content">
                <div class="forgot-brand">
                    <img class="forgot-brand__logo" src="{{ asset('images/koperasi-logo.svg?v=3') }}" alt="Koperasi Politeknik Besut">
                    <span class="forgot-brand__divider" aria-hidden="true"></span>
                    <div class="forgot-brand__wordmark">
                        <strong><span>Coop</span>Best</strong>
                        <small>Koperasi Politeknik Besut</small>
                    </div>
                </div>

                <div class="forgot-copy">
                    <span class="forgot-copy__mark" aria-hidden="true"></span>
                    <h1>Pulihkan Akses Anda</h1>
                    <h2>Tetapkan Kata Laluan Baharu</h2>
                    <p>Sahkan maklumat akaun anda untuk meneruskan akses ke portal CoopBest.</p>
                </div>
            </div>
        </section>

        <section class="forgot-form-panel" aria-label="Borang lupa kata laluan">
            <a class="forgot-back-link" href="{{ route('login') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                Kembali ke Log Masuk
            </a>

            <div class="forgot-form-wrap">
                <div class="forgot-form-head">
                    <h1>Lupa Kata Laluan</h1>
                    <p>Masukkan maklumat akaun anda untuk menetapkan kata laluan baharu.</p>
                </div>

                @if ($errors->any())
                    <div class="forgot-alert" role="alert">{{ $errors->first() }}</div>
                @endif

                @if (session('status'))
                    <div class="forgot-alert success" role="status">{{ session('status') }}</div>
                @endif

                <form class="forgot-form" method="POST" action="{{ route('password.forgot.submit') }}" data-forgot-form>
                    @csrf
                    <div class="forgot-form-grid">
                        <div class="forgot-field @error('identifier') has-error @enderror">
                            <label for="identifier">No. Matrik / Nama Pengguna</label>
                            <div class="forgot-input">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                                <input id="identifier" name="identifier" type="text" value="{{ old('identifier') }}" required autofocus placeholder="Masukkan ID akaun" autocomplete="username">
                            </div>
                            @error('identifier')<span class="forgot-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="forgot-field @error('nric') has-error @enderror">
                            <label for="nric">No. Kad Pengenalan</label>
                            <div class="forgot-input">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="14" x="3" y="5" rx="2"/><path d="M7 10h4"/><path d="M7 14h3"/><path d="M15 10h2"/><path d="M15 14h2"/></svg>
                                <input id="nric" name="nric" type="text" value="{{ old('nric') }}" required placeholder="Masukkan No. KP" autocomplete="off">
                            </div>
                            @error('nric')<span class="forgot-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="forgot-field @error('password') has-error @enderror">
                            <label for="password">Kata Laluan Baharu</label>
                            <div class="forgot-input has-toggle">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <input id="password" name="password" type="password" required minlength="8" placeholder="Minimum 8 aksara" autocomplete="new-password">
                                <button class="forgot-password-button" type="button" data-password-toggle aria-label="Papar kata laluan">
                                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2 2 20 20"/><path d="M6.7 6.7C3.8 8.6 2 12 2 12s3.5 7 10 7c1.8 0 3.3-.5 4.7-1.2"/><path d="M9.9 4.2C10.6 4.1 11.3 4 12 4c6.5 0 10 8 10 8s-.8 1.7-2.2 3.4"/><path d="M14.1 14.1A3 3 0 0 1 9.9 9.9"/></svg>
                                </button>
                            </div>
                            @error('password')<span class="forgot-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="forgot-field">
                            <label for="password_confirmation">Sahkan Kata Laluan</label>
                            <div class="forgot-input has-toggle">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <input id="password_confirmation" name="password_confirmation" type="password" required placeholder="Ulang kata laluan baharu" autocomplete="new-password">
                                <button class="forgot-password-button" type="button" data-password-toggle aria-label="Papar kata laluan">
                                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2 2 20 20"/><path d="M6.7 6.7C3.8 8.6 2 12 2 12s3.5 7 10 7c1.8 0 3.3-.5 4.7-1.2"/><path d="M9.9 4.2C10.6 4.1 11.3 4 12 4c6.5 0 10 8 10 8s-.8 1.7-2.2 3.4"/><path d="M14.1 14.1A3 3 0 0 1 9.9 9.9"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <p class="forgot-password-note">Gunakan sekurang-kurangnya 8 aksara.</p>

                    <button class="forgot-submit" type="submit" data-submit-button>
                        <span class="forgot-submit__spinner" aria-hidden="true"></span>
                        <span data-submit-text>Tetapkan Kata Laluan</span>
                    </button>
                </form>

                <p class="forgot-login-note">Sudah mengingati kata laluan? <a href="{{ route('login') }}">Log Masuk</a></p>
            </div>

            <footer class="forgot-footer">&copy; 2026 Koperasi Politeknik Besut. Hak Cipta Terpelihara.</footer>
        </section>
    </main>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                const input = button.closest('.forgot-input')?.querySelector('input');

                if (!input) {
                    return;
                }

                const showPassword = input.type === 'password';
                input.type = showPassword ? 'text' : 'password';
                button.classList.toggle('is-visible', showPassword);
                button.setAttribute('aria-label', showPassword ? 'Sembunyi kata laluan' : 'Papar kata laluan');
                input.focus();
            });
        });

        document.querySelector('[data-forgot-form]')?.addEventListener('submit', function () {
            const submitButton = this.querySelector('[data-submit-button]');
            const submitText = this.querySelector('[data-submit-text]');

            if (!submitButton || !submitText) {
                return;
            }

            submitButton.disabled = true;
            submitButton.classList.add('is-loading');
            submitText.textContent = 'Memproses...';
        });
    </script>
</body>
</html>
