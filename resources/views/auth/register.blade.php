<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akaun - CoopBest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @include('components.coopbest-public-style')
    <style>
        :root { --navy: #082F59; --red: #ED1C2E; --blue: #0B5ED7; --soft: #F3F7FB; --border: #CBD8E8; --muted: #526987; }
        * { box-sizing: border-box; }
        html, body { width: 100%; min-height: 100%; margin: 0; overflow-x: hidden; }
        body { background: #fff; color: var(--navy); font-family: 'Inter', system-ui, sans-serif; }

        .register-page { position: relative; isolation: isolate; display: grid; grid-template-columns: minmax(0, 46%) minmax(0, 54%); width: 100%; min-height: calc(100dvh - 136px); overflow: hidden; background: #fff; }
        .auth-bg { position: absolute; inset: 0 auto 0 0; height: 100%; pointer-events: none; clip-path: polygon(0 0, 100% 0, 74% 100%, 0 100%); }
        .auth-bg--soft { z-index: 0; width: 51%; background: #fff; }
        .auth-bg--red { z-index: 1; width: 46.7%; background: var(--red); }
        .auth-bg--navy { z-index: 2; width: 46%; background: var(--navy); }

        .register-visual, .register-form-panel { position: relative; z-index: 3; min-width: 0; }
        .register-visual { grid-column: 1; display: flex; align-items: flex-start; padding: clamp(120px, 16vh, 150px) clamp(46px, 4.5vw, 74px) clamp(46px, 4.5vw, 74px); color: #fff; overflow: hidden; }
        .register-visual__content { position: relative; z-index: 1; width: 100%; max-width: 560px; padding-right: 30px; }
        .register-watermark { position: absolute; left: clamp(-70px, -4vw, -24px); bottom: clamp(-85px, -8vw, -32px); width: min(62vw, 650px); max-width: 88%; opacity: .08; pointer-events: none; user-select: none; }
        .register-brand { display: flex; align-items: center; gap: clamp(16px, 1.8vw, 24px); width: 100%; max-width: 530px; }
        .register-brand__logo { width: clamp(80px, 6.6vw, 118px); max-width: 26vw; flex: 0 0 auto; }
        .register-brand__divider { width: 2px; height: clamp(76px, 7vw, 112px); background: rgba(255,255,255,.86); }
        .register-brand__copy strong { display: block; color: #fff; font-size: clamp(34px, 3.2vw, 54px); line-height: 1; font-weight: 900; white-space: nowrap; }
        .register-brand__copy strong span { color: var(--red); }
        .register-brand__copy small { display: block; margin-top: 10px; color: #fff; font-size: clamp(17px, 1.55vw, 25px); line-height: 1.1; font-weight: 800; white-space: nowrap; }
        .register-copy { display: grid; gap: 14px; margin-top: clamp(48px, 8vh, 76px); }
        .register-copy__mark { width: 78px; height: 7px; border-radius: 999px; background: var(--red); }
        .register-visual__title { max-width: 540px; margin: 18px 0 0; color: #fff; font-size: clamp(38px, 3vw, 54px); line-height: 1.08; font-weight: 900; white-space: nowrap; }
        .register-visual__subtitle { max-width: 540px; margin: 0; color: #fff; font-size: clamp(28px, 2.25vw, 40px); line-height: 1.15; font-weight: 500; white-space: nowrap; }
        .register-visual__description { max-width: 480px; margin: 0; color: rgba(255,255,255,.9); font-size: clamp(16px, 1.15vw, 20px); line-height: 1.5; font-weight: 500; }

        .register-form-panel { grid-column: 2; display: grid; grid-template-rows: auto minmax(0, 1fr) auto; padding: clamp(26px, 3.5vh, 42px) clamp(48px, 5vw, 82px) clamp(18px, 2.5vh, 28px); }
        .register-back { justify-self: end; display: inline-flex; align-items: center; gap: 12px; color: var(--blue); font-size: clamp(16px, 1.4vw, 22px); font-weight: 500; text-decoration: none; }
        .register-back svg { width: 30px; height: 30px; }
        .register-back:hover { text-decoration: underline; }
        .register-form-content { width: 100%; max-width: 760px; align-self: start; justify-self: center; margin: clamp(28px, 3.5vh, 40px) auto 0; }
        .register-head h1 { position: relative; margin: 0; padding-bottom: 20px; color: #071747; font-size: clamp(38px, 3vw, 52px); line-height: 1.1; font-weight: 900; }
        .register-head h1::after { content: ''; position: absolute; bottom: 0; left: 0; width: 76px; height: 7px; border-radius: 999px; background: var(--red); }
        .register-head p { margin: 14px 0 20px; color: var(--muted); font-size: clamp(16px, 1.15vw, 20px); line-height: 1.5; font-weight: 500; }
        .alert { margin: 0 0 14px; padding: 12px 14px; border: 1px solid #FCA5A5; border-radius: 8px; background: #FEF2F2; color: #B91C1C; font-size: 14px; font-weight: 800; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 14px 18px; }
        .field { display: grid; gap: 6px; min-width: 0; }
        .field label { color: #071747; font-size: clamp(13px, 1vw, 16px); font-weight: 800; }
        .input-control { position: relative; display: grid; grid-template-columns: 46px minmax(0,1fr) 42px; align-items: center; width: 100%; min-width: 0; min-height: 52px; border: 1px solid var(--border); border-radius: 8px; overflow: hidden; color: #526987; background: #fff; }
        .input-control > svg { position: absolute; left: 14px; width: 23px; height: 23px; pointer-events: none; }
        .input-control input, .input-control select { grid-column: 1 / -1; grid-row: 1; width: 100%; min-width: 0; min-height: 50px; border: 0; outline: 0; background: transparent; color: #071747; padding: 0 10px 0 48px; font: inherit; font-size: clamp(14px, 1vw, 17px); font-weight: 600; }
        .input-control select { appearance: auto; padding-right: 34px; }
        .input-control input::placeholder { color: #7889A3; opacity: 1; }
        .input-control:focus-within { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(11,94,215,.13); }
        .password-toggle { position: relative; z-index: 1; grid-column: 3; grid-row: 1; justify-self: center; width: 36px; height: 36px; display: grid; place-items: center; padding: 0; border: 0; border-radius: 6px; color: #526987; background: transparent; cursor: pointer; }
        .password-toggle:hover, .password-toggle:focus-visible { color: var(--blue); background: #EAF2FF; outline: 0; }
        .password-toggle svg { width: 23px; height: 23px; }
        .password-toggle .eye-hide, .password-toggle.is-visible .eye-show { display: none; }
        .password-toggle.is-visible .eye-hide { display: block; }
        .has-toggle input { padding-right: 46px; }
        .agreement { display: inline-flex; align-items: center; gap: 12px; margin-top: 16px; color: var(--muted); font-size: clamp(13px, 1vw, 16px); font-weight: 500; cursor: pointer; }
        .agreement input { width: 22px; height: 22px; margin: 0; accent-color: var(--red); cursor: pointer; }
        .actions { margin-top: 16px; }.button { display: inline-flex; width: 100%; min-height: 56px; align-items: center; justify-content: center; border: 1px solid var(--red); border-radius: 8px; background: var(--red); color: #fff; font: inherit; font-size: clamp(17px, 1.35vw, 22px); font-weight: 900; cursor: pointer; }.button:hover { background: #C91525; border-color: #C91525; }
        .register-login-note { margin: 14px 0 0; color: #294569; font-size: clamp(14px, 1.05vw, 17px); text-align: center; }.register-login-note a { color: var(--blue); font-weight: 900; text-decoration: underline; text-underline-offset: 2px; }
        .register-footer { display: none; }
        .register-auth-page .public-nav { width: min(var(--cb-container), calc(100% - 96px)); }
        .register-auth-page .public-menu { gap: 0; margin-left: auto; }
        .register-auth-page .public-login-link { min-height: 44px; gap: 8px; padding: 0 18px; background: #fff; color: var(--blue) !important; border-color: #BFD7FF; border-radius: 8px; box-shadow: 0 8px 18px rgba(11,94,215,.08); }
        .register-auth-page .public-login-link::before { content: "←"; color: var(--red); font-weight: 900; }
        .register-auth-page .public-login-link:hover,
        .register-auth-page .public-login-link:focus-visible { background: #F7FAFF; color: var(--navy) !important; border-color: var(--navy); }
        @media (max-width: 900px) { .register-auth-page .public-menu { position: static; display: flex; padding: 0; border: 0; box-shadow: none; background: transparent; }.register-auth-page .public-login-link { margin-top: 0; padding: 0 14px; font-size: 13px; white-space: nowrap; } }

        @media (max-width: 1200px) { .register-visual__title, .register-visual__subtitle { white-space: normal; } .register-form-panel { padding-inline: 48px; } }
        @media (max-width: 980px) { .register-page { grid-template-columns: minmax(0,42%) minmax(0,58%); } .auth-bg--soft { width: 48%; }.auth-bg--red { width: 42.7%; }.auth-bg--navy { width: 42%; }.register-visual { padding-inline: 38px; }.register-brand__divider { display: none; }.register-brand { gap: 14px; }.register-brand__copy small { white-space: normal; }.register-form-panel { padding-inline: 32px; }.form-grid { grid-template-columns: 1fr; gap: 12px; } }
        @media (max-width: 768px) { body { overflow-y: auto; }.register-page { display: block; height: auto; min-height: calc(100dvh - 126px); overflow-y: auto; }.auth-bg { display: none; }.register-visual { min-height: auto; padding: 32px 24px; background: var(--navy); border-bottom: 7px solid var(--red); }.register-visual__content { max-width: none; padding-right: 0; }.register-watermark { display: none; }.register-brand__logo { width: 76px; }.register-brand__divider { display: block; height: 74px; }.register-brand__copy strong { font-size: 34px; }.register-brand__copy small { font-size: 17px; white-space: nowrap; }.register-copy { gap: 12px; margin-top: 30px; }.register-copy__mark { width: 56px; height: 5px; }.register-visual__title, .register-visual__subtitle { width: 100%; max-width: 100%; white-space: normal; }.register-form-panel { min-height: auto; padding: 26px 20px 28px; }.register-back { justify-self: start; }.register-form-content { margin-top: 32px; }.form-grid { grid-template-columns: 1fr; } }
        @media (max-height: 820px) and (min-width: 769px) { .register-form-panel { padding-top: 20px; padding-bottom: 16px; }.register-form-content { margin-top: 18px; }.register-head h1 { font-size: 40px; }.register-head p { margin-bottom: 14px; }.form-grid { gap: 10px 16px; }.input-control { min-height: 48px; }.input-control input, .input-control select { min-height: 46px; }.agreement { margin-top: 12px; }.actions { margin-top: 12px; }.button { min-height: 50px; } }
        @media (min-width: 1025px) and (min-height: 760px) { body { overflow-y: auto; }.register-page { min-height: calc(100dvh - 136px); } }
    </style>
</head>
<body class="register-auth-page">
    <header class="public-header">
        <nav class="public-nav" aria-label="Navigasi daftar akaun">
            <a class="public-brand" href="{{ url('/') }}" aria-label="CoopBest utama">
                <img class="public-brand__logo" src="{{ asset('images/koperasi-logo.svg?v=3') }}" alt="Logo CoopBest">
                <span class="public-brand__divider" aria-hidden="true"></span>
                <span class="public-brand__copy">
                    <span class="public-brand__name"><span>Coop</span>Best</span>
                    <span class="public-brand__sub">Koperasi Politeknik Besut</span>
                </span>
            </a>
            <div class="public-menu">
                <a class="public-login-link" href="{{ route('login') }}">Kembali ke Log Masuk</a>
            </div>
        </nav>
    </header>

    <main class="register-page">
        <span class="auth-bg auth-bg--soft" aria-hidden="true"></span>
        <span class="auth-bg auth-bg--red" aria-hidden="true"></span>
        <span class="auth-bg auth-bg--navy" aria-hidden="true"></span>

        <section class="register-visual" aria-label="Identiti CoopBest">
            <img class="register-watermark" src="{{ asset('images/koperasi-logo.svg?v=3') }}" alt="" aria-hidden="true">
            <div class="register-visual__content">
                <div class="register-copy">
                    <span class="register-copy__mark" aria-hidden="true"></span>
                    <h1 class="register-visual__title">Sertai CoopBest</h1>
                    <h2 class="register-visual__subtitle">Daftar Akaun Pengguna</h2>
                    <p class="register-visual__description">Cipta akaun untuk mengakses perkhidmatan Koperasi Politeknik Besut.</p>
                </div>
            </div>
        </section>

        <section class="register-form-panel" aria-label="Borang daftar akaun">
            <div class="register-form-content">
                <div class="register-head">
                    <h1>Daftar Akaun</h1>
                    <p>Lengkapkan maklumat pelajar untuk mencipta akaun CoopBest.</p>
                </div>

                @if ($errors->any())
                    <div class="alert">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('register.submit') }}" id="registerForm">
                    @csrf
                    <input type="hidden" name="role" value="student">

                    <div class="form-grid">
                        <div class="field"><label for="no_matrik">No. Matrik</label><div class="input-control"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg><input id="no_matrik" name="no_matrik" value="{{ old('no_matrik') }}" placeholder="Masukkan no. matrik"></div></div>
                        <div class="field"><label for="nama">Nama Penuh</label><div class="input-control"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg><input id="nama" name="nama" value="{{ old('nama') }}" required placeholder="Masukkan nama penuh"></div></div>
                        <div class="field"><label for="nric">No. Kad Pengenalan</label><div class="input-control"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg><input id="nric" name="nric" value="{{ old('nric') }}" required placeholder="Masukkan no. kad pengenalan"></div></div>
                        <div class="field"><label for="kelas">Kelas</label><div class="input-control"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 10 9-5 9 5-9 5-9-5Z"/><path d="M7 12v4c2.8 2 7.2 2 10 0v-4"/></svg><select id="kelas" name="kelas"><option value="">Pilih kelas</option>@foreach($studentClasses as $class)<option value="{{ $class }}" @selected(old('kelas') === $class)>{{ $class }}</option>@endforeach</select></div></div>
                        <div class="field"><label for="email">Emel</label><div class="input-control"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="14" x="3" y="5" rx="2"/><path d="m3 7 9 6 9-6"/></svg><input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Masukkan emel anda"></div></div>
                        <div class="field"><label for="no_tel">No. Telefon</label><div class="input-control"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .8 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.4 1.8.7 2.8.8a2 2 0 0 1 1.7 2Z"/></svg><input id="no_tel" name="no_tel" value="{{ old('no_tel') }}" placeholder="Masukkan no. telefon"></div></div>
                        <div class="field"><label for="password">Kata Laluan</label><div class="input-control has-toggle"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg><input id="password" type="password" name="password" required placeholder="Masukkan kata laluan"><button class="password-toggle" type="button" aria-label="Papar kata laluan"><svg class="eye-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eye-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2 2 20 20"/><path d="M6.7 6.7C3.8 8.6 2 12 2 12s3.5 7 10 7c1.8 0 3.3-.5 4.7-1.2"/><path d="M9.9 4.2C10.6 4.1 11.3 4 12 4c6.5 0 10 8 10 8s-.8 1.7-2.2 3.4"/></svg></button></div></div>
                        <div class="field"><label for="password_confirmation">Sahkan Kata Laluan</label><div class="input-control has-toggle"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg><input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Sahkan kata laluan"><button class="password-toggle" type="button" aria-label="Papar kata laluan"><svg class="eye-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eye-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2 2 20 20"/><path d="M6.7 6.7C3.8 8.6 2 12 2 12s3.5 7 10 7c1.8 0 3.3-.5 4.7-1.2"/><path d="M9.9 4.2C10.6 4.1 11.3 4 12 4c6.5 0 10 8 10 8s-.8 1.7-2.2 3.4"/></svg></button></div></div>
                    </div>

                    <label class="agreement"><input type="checkbox"><span>Saya mengesahkan bahawa maklumat yang diberikan adalah benar.</span></label>
                    <div class="actions">
                        <button class="button" type="submit">Daftar Akaun</button>
                    </div>
                </form>
                <p class="register-login-note">Sudah mempunyai akaun? <a href="{{ route('login') }}">Log Masuk</a></p>
            </div>

            <footer class="register-footer">&copy; 2026 Koperasi Politeknik Besut. Hak Cipta Terpelihara.</footer>
        </section>
    </main>

    <footer class="public-footer">
        <p>&copy; 2026 Koperasi Politeknik Besut. Hak Cipta Terpelihara.</p>
    </footer>

    <script>
        const form = document.getElementById('registerForm');
        form.querySelectorAll('.password-toggle').forEach((button) => {
            button.addEventListener('click', () => {
                const input = button.closest('.input-control')?.querySelector('input');
                if (!input) return;
                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                button.classList.toggle('is-visible', isHidden);
                button.setAttribute('aria-label', isHidden ? 'Sembunyi kata laluan' : 'Papar kata laluan');
                input.focus();
            });
        });
    </script>
</body>
</html>
