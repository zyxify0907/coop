<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Log masuk CoopBest untuk pelajar, staf dan pentadbir koperasi.">
    <meta name="theme-color" content="#062B53">
    <title>Log Masuk CoopBest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @include('components.coopbest-public-style')
</head>
<body class="login-page">
    @include('components.flash-notification')

    <header class="public-header">
        <nav class="public-nav" aria-label="Navigasi utama">
            <a class="public-brand" href="{{ url('/') }}" aria-label="CoopBest utama">
                <img class="public-brand__logo" src="{{ asset('images/koperasi-logo.svg?v=3') }}" alt="Logo CoopBest">
                <span class="public-brand__divider" aria-hidden="true"></span>
                <span class="public-brand__copy">
                    <span class="public-brand__name"><span>Coop</span>Best</span>
                    <span class="public-brand__sub">Koperasi Politeknik Besut</span>
                </span>
            </a>

        </nav>
    </header>

    <main class="login-shell">
        <section class="login-intro" aria-labelledby="login-welcome">
            <div class="login-intro__content">
                <span class="public-red-line" aria-hidden="true"></span>
                <p class="login-kicker">Portal Koperasi</p>
                <h1 id="login-welcome">Selamat Datang ke CoopBest</h1>
                <p class="login-intro__subtitle">Portal Koperasi Politeknik Besut</p>
                <p class="login-intro__text">Urus keahlian, saham, tempahan dan operasi pekerja koperasi melalui satu portal rasmi yang mudah dan selamat.</p>
            </div>
            <img class="public-wing public-wing--login" src="{{ asset('images/koperasi-logo.svg?v=3') }}" alt="" aria-hidden="true">
        </section>

        <section class="login-panel" aria-labelledby="login-title">
            <div class="login-form-wrap">
                <div class="login-form-head">
                    <p class="login-kicker">Akses Portal</p>
                    <h2 id="login-title">Log Masuk CoopBest</h2>
                    <span class="public-red-line public-red-line--small" aria-hidden="true"></span>
                    <p>Masukkan maklumat akaun anda untuk meneruskan.</p>
                </div>

                <form class="login-form" method="POST" action="{{ route('login.submit') }}" id="login-form" autocomplete="off" novalidate>
                    @csrf

                    <div class="login-field" data-field>
                        <label for="identifier">ID Pengguna</label>
                        <p class="login-field__hint" id="identifier-hint">Pelajar: no. matrik, no. KP atau e-mel. Staf: no. KP, e-mel atau no. pekerja. Admin: no. KP atau nama pengguna.</p>
                        <div class="login-input-wrap">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <input id="identifier" name="identifier" type="text" value="" placeholder="Masukkan ID mengikut akaun anda" autocomplete="off" aria-describedby="identifier-hint identifier-error">
                        </div>
                        <p class="field-error" id="identifier-error" aria-live="polite"></p>
                    </div>

                    <div class="login-field" data-field>
                        <label for="password">Kata Laluan</label>
                        <div class="login-input-wrap">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="11" width="16" height="10" rx="2"/>
                                <path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                            </svg>
                            <input id="password" name="password" type="password" placeholder="Masukkan kata laluan" autocomplete="off" aria-describedby="password-error">
                            <button class="password-toggle" type="button" aria-label="Tunjuk kata laluan" aria-pressed="false" data-password-toggle>
                                <svg class="eye-show" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="eye-hide" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m3 3 18 18"/>
                                    <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                                    <path d="M9.9 5.2A9.5 9.5 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-2.7 3.7"/>
                                    <path d="M6.6 6.6C3.7 8.5 2 12 2 12s3.5 7 10 7a9.8 9.8 0 0 0 4.4-1"/>
                                </svg>
                            </button>
                        </div>
                        <p class="field-error" id="password-error" aria-live="polite"></p>
                    </div>

                    <div class="login-options">
                        <label class="remember-check" for="remember">
                            <input id="remember" name="remember" type="checkbox" @checked(old('remember'))>
                            <span>Ingat Saya</span>
                        </label>
                    </div>

                    <button class="public-button public-button--danger login-submit" type="submit" id="login-submit">
                        Log Masuk
                    </button>
                </form>

                <div class="register-row">
                    <span>Belum mempunyai akaun?</span>
                    <a href="{{ route('register') }}">Daftar Akaun Baharu</a>
                </div>

                <div class="secure-row" aria-label="Maklumat keselamatan">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17 9V7A5 5 0 0 0 7 7v2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-1Zm-8 0V7a3 3 0 0 1 6 0v2H9Zm4 6.73V17a1 1 0 1 1-2 0v-1.27a2 2 0 1 1 2 0Z"/>
                    </svg>
                    <span>Akses anda dilindungi dan sulit.</span>
                </div>
            </div>

        </section>
    </main>

    <footer class="public-footer">
        <p>&copy; 2026 Koperasi Politeknik Besut. Hak Cipta Terpelihara.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuButton = document.querySelector('[data-menu-toggle]');
            const menu = document.querySelector('[data-public-menu]');
            const form = document.getElementById('login-form');
            const submitButton = document.getElementById('login-submit');
            const identifier = document.getElementById('identifier');
            const password = document.getElementById('password');
            const toggle = document.querySelector('[data-password-toggle]');

            if (menuButton && menu) {
                menuButton.addEventListener('click', function () {
                    const isOpen = menuButton.getAttribute('aria-expanded') === 'true';
                    menuButton.setAttribute('aria-expanded', String(! isOpen));
                    menu.classList.toggle('is-open', ! isOpen);
                });

                menu.querySelectorAll('a').forEach(function (link) {
                    link.addEventListener('click', function () {
                        menuButton.setAttribute('aria-expanded', 'false');
                        menu.classList.remove('is-open');
                    });
                });
            }

            function setError(input, message) {
                const field = input.closest('[data-field]');
                const error = field ? field.querySelector('.field-error') : null;
                input.setAttribute('aria-invalid', message ? 'true' : 'false');
                if (field) {
                    field.classList.toggle('has-error', Boolean(message));
                }
                if (error) {
                    error.textContent = message;
                }
            }

            if (toggle && password) {
                toggle.addEventListener('click', function () {
                    const isVisible = password.type === 'text';
                    password.type = isVisible ? 'password' : 'text';
                    toggle.setAttribute('aria-pressed', String(! isVisible));
                    toggle.setAttribute('aria-label', isVisible ? 'Tunjuk kata laluan' : 'Sembunyikan kata laluan');
                    toggle.classList.toggle('is-visible', ! isVisible);
                    password.focus();
                });
            }

            [identifier, password].forEach(function (input) {
                if (! input) {
                    return;
                }
                input.addEventListener('input', function () {
                    if (input.value.trim() !== '') {
                        setError(input, '');
                    }
                });
            });

            if (form && submitButton && identifier && password) {
                form.addEventListener('submit', function (event) {
                    let valid = true;

                    if (identifier.value.trim() === '') {
                        setError(identifier, 'Sila masukkan ID pengguna.');
                        valid = false;
                    }

                    if (password.value.trim() === '') {
                        setError(password, 'Sila masukkan kata laluan.');
                        valid = false;
                    }

                    if (! valid) {
                        event.preventDefault();
                        (identifier.value.trim() === '' ? identifier : password).focus();
                        return;
                    }

                    submitButton.disabled = true;
                    submitButton.textContent = 'Sedang diproses...';
                });
            }
        });
    </script>
</body>
</html>
