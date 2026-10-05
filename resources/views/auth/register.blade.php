<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Daftar akaun CoopBest untuk pelajar koperasi.">
    <meta name="theme-color" content="#062B53">
    <title>Daftar Akaun CoopBest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @include('components.coopbest-public-style')
    <style>
        body.login-page.register-page .login-form-wrap {
            max-width: 610px;
        }

        body.login-page.register-page .login-form-head h2 {
            font-size: clamp(32px, 2.8vw, 44px);
        }

        body.login-page.register-page .login-form-head p {
            margin-bottom: 14px;
        }

        body.login-page.register-page .login-form {
            gap: 12px;
        }

        body.login-page.register-page .register-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px 16px;
        }

        body.login-page.register-page .login-field label {
            margin-bottom: 5px;
            font-size: 14px;
        }

        body.login-page.register-page .login-input-wrap {
            min-height: 48px;
            grid-template-columns: 30px minmax(0, 1fr) auto;
            gap: 12px;
            padding: 0 14px;
        }

        body.login-page.register-page .login-input-wrap svg {
            width: 22px;
            height: 22px;
        }

        body.login-page.register-page .login-input-wrap input {
            height: 46px;
            font-size: 15px;
        }

        body.login-page.register-page .login-input-wrap select {
            width: 100%;
            min-width: 0;
            height: 46px;
            padding: 0;
            border: 0;
            outline: 0;
            color: var(--cb-text);
            background: transparent;
            font-size: 15px;
            font-weight: 500;
        }

        body.login-page.register-page .password-toggle {
            width: 38px;
            height: 46px;
        }

        body.login-page.register-page .login-input-wrap select:invalid {
            color: #7A8798;
        }

        body.login-page.register-page .login-input-wrap input::placeholder {
            font-weight: 600;
        }

        body.login-page.register-page .register-agreement {
            display: inline-flex;
            align-items: flex-start;
            gap: 10px;
            color: var(--cb-muted);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
        }

        body.login-page.register-page .register-agreement input {
            width: 22px;
            height: 22px;
            margin: 1px 0 0;
            accent-color: var(--cb-red);
            cursor: pointer;
            flex: 0 0 auto;
        }

        body.login-page.register-page .register-submit {
            min-height: 50px;
            margin-top: 0;
            font-size: 18px;
        }

        body.login-page.register-page .password-requirements {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 5px 14px;
            margin: -4px 0 0;
            padding: 0;
            color: var(--cb-muted);
            font-size: 12px;
            list-style: none;
        }

        body.login-page.register-page .password-requirements li::before {
            content: '○';
            display: inline-block;
            width: 18px;
            color: #7A8798;
        }

        body.login-page.register-page .password-requirements li[data-valid="true"] {
            color: #16794B;
        }

        body.login-page.register-page .password-requirements li[data-valid="true"]::before {
            content: '✓';
            color: #16794B;
        }

        body.login-page.register-page .password-requirements[data-has-input="true"] li[data-valid="false"] {
            color: #B42318;
        }

        body.login-page.register-page .password-requirements[data-has-input="true"] li[data-valid="false"]::before {
            content: '×';
            color: #B42318;
        }

        body.login-page.register-page .register-row {
            margin-top: 12px;
            font-size: 14px;
        }

        body.login-page.register-page .secure-row {
            display: none;
        }

        @media (max-width: 1180px) {
            body.login-page.register-page .login-form-wrap {
                width: 100%;
                max-width: 610px;
            }
        }

        @media (max-width: 900px) and (min-width: 641px) {
            body.login-page.register-page .login-form-wrap {
                width: min(610px, 100%);
                max-width: 100%;
            }
        }

        @media (max-width: 640px) {
            body.login-page.register-page .register-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            body.login-page.register-page .register-grid {
                gap: 14px;
            }

            body.login-page.register-page .register-agreement {
                font-size: 14px;
            }
        }

        @media (max-width: 900px) {
            body.login-page.register-page {
                height: auto;
                min-height: 100dvh;
                overflow: auto;
            }

            body.login-page.register-page .login-shell {
                height: auto;
                min-height: 0;
                overflow: visible;
            }

            body.login-page.register-page .login-intro {
                min-height: 180px;
                padding-top: 22px;
                padding-bottom: 26px;
            }

            body.login-page.register-page .login-intro h1 {
                font-size: clamp(26px, 7vw, 32px);
            }

            body.login-page.register-page .login-intro__subtitle {
                font-size: clamp(17px, 4.8vw, 22px);
            }

            body.login-page.register-page .login-intro__text {
                display: none;
            }
        }
    </style>
</head>
<body class="login-page register-page">
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
        <section class="login-intro" aria-labelledby="register-welcome">
            <div class="login-intro__content">
                <span class="public-red-line" aria-hidden="true"></span>
                <p class="login-kicker">Portal Koperasi</p>
                <h1 id="register-welcome">Selamat Datang ke CoopBest</h1>
                <p class="login-intro__subtitle">Portal Koperasi Politeknik Besut</p>
                <p class="login-intro__text">Urus keahlian, saham, tempahan dan operasi pekerja koperasi melalui satu portal rasmi yang mudah dan selamat.</p>
            </div>
            <img class="public-wing public-wing--login" src="{{ asset('images/koperasi-logo.svg?v=3') }}" alt="" aria-hidden="true">
        </section>

        <section class="login-panel" aria-labelledby="register-title">
            <div class="login-form-wrap">
                <div class="login-form-head">
                    <p class="login-kicker">Akaun Baharu</p>
                    <h2 id="register-title">Daftar Akaun CoopBest</h2>
                    <span class="public-red-line public-red-line--small" aria-hidden="true"></span>
                    <p>Lengkapkan maklumat pelajar untuk mencipta akaun CoopBest.</p>
                </div>

                <form class="login-form" method="POST" action="{{ route('register.submit') }}" id="register-form" autocomplete="off">
                    @csrf
                    <input type="hidden" name="role" value="student">

                    <div class="register-grid">
                        <div class="login-field">
                            <label for="no_matrik">No. Matrik</label>
                            <div class="login-input-wrap">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                                <input id="no_matrik" name="no_matrik" value="{{ old('no_matrik') }}" required placeholder="Masukkan no. matrik">
                            </div>
                        </div>

                        <div class="login-field">
                            <label for="nama">Nama Penuh</label>
                            <div class="login-input-wrap">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                                <input id="nama" name="nama" value="{{ old('nama') }}" required placeholder="Masukkan nama penuh">
                            </div>
                        </div>

                        <div class="login-field">
                            <label for="nric">No. Kad Pengenalan</label>
                            <div class="login-input-wrap">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                                <input id="nric" name="nric" value="{{ old('nric') }}" required placeholder="Masukkan no. kad pengenalan">
                            </div>
                        </div>

                        <div class="login-field">
                            <label for="kelas">Kelas</label>
                            <div class="login-input-wrap">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 10 9-5 9 5-9 5-9-5Z"/><path d="M7 12v4c2.8 2 7.2 2 10 0v-4"/></svg>
                                <select id="kelas" name="kelas" required>
                                    <option value="">Pilih kelas</option>
                                    @foreach($studentClasses as $class)
                                        <option value="{{ $class }}" @selected(old('kelas') === $class)>{{ $class }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="login-field">
                            <label for="email">Emel</label>
                            <div class="login-input-wrap">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="14" x="3" y="5" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="Masukkan emel anda">
                            </div>
                        </div>

                        <div class="login-field">
                            <label for="no_tel">No. Telefon</label>
                            <div class="login-input-wrap">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .8 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.4 1.8.7 2.8.8a2 2 0 0 1 1.7 2Z"/></svg>
                                <input id="no_tel" name="no_tel" value="{{ old('no_tel') }}" required placeholder="Masukkan no. telefon">
                            </div>
                        </div>

                        <div class="login-field">
                            <label for="password">Kata Laluan</label>
                            <p class="login-field__hint" id="password-hint">Kata laluan perlu memenuhi semua syarat berikut:</p>
                            <div class="login-input-wrap">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" aria-describedby="password-hint password-requirements" placeholder="Masukkan kata laluan">
                                <button class="password-toggle" type="button" aria-label="Papar kata laluan" aria-pressed="false" data-password-toggle>
                                    <svg class="eye-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="eye-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2 2 20 20"/><path d="M6.7 6.7C3.8 8.6 2 12 2 12s3.5 7 10 7c1.8 0 3.3-.5 4.7-1.2"/><path d="M9.9 4.2C10.6 4.1 11.3 4 12 4c6.5 0 10 8 10 8s-.8 1.7-2.2 3.4"/></svg>
                                </button>
                            </div>
                            <ul class="password-requirements" id="password-requirements" aria-live="polite">
                                <li data-password-rule="length">Sekurang-kurangnya 8 aksara</li>
                                <li data-password-rule="uppercase">Huruf besar (A-Z)</li>
                                <li data-password-rule="lowercase">Huruf kecil (a-z)</li>
                                <li data-password-rule="number">Nombor (0-9)</li>
                                <li data-password-rule="symbol">Simbol khas (@$!%*?&amp;)</li>
                            </ul>
                        </div>

                        <div class="login-field">
                            <label for="password_confirmation">Sahkan Kata Laluan</label>
                            <div class="login-input-wrap">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Sahkan kata laluan">
                                <button class="password-toggle" type="button" aria-label="Papar kata laluan" aria-pressed="false" data-password-toggle>
                                    <svg class="eye-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="eye-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2 2 20 20"/><path d="M6.7 6.7C3.8 8.6 2 12 2 12s3.5 7 10 7c1.8 0 3.3-.5 4.7-1.2"/><path d="M9.9 4.2C10.6 4.1 11.3 4 12 4c6.5 0 10 8 10 8s-.8 1.7-2.2 3.4"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <label class="register-agreement">
                        <input type="checkbox" name="agreement" value="1" required>
                        <span>Saya mengesahkan bahawa maklumat yang diberikan adalah benar.</span>
                    </label>

                    <button class="public-button public-button--danger login-submit register-submit" type="submit">
                        Daftar Akaun
                    </button>
                </form>

                <div class="register-row">
                    <span>Sudah mempunyai akaun?</span>
                    <a href="{{ route('login') }}">Log Masuk</a>
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
        const registerForm = document.getElementById('register-form');
        const passwordInput = document.getElementById('password');
        const passwordConfirmationInput = document.getElementById('password_confirmation');
        const passwordRequirements = document.getElementById('password-requirements');
        const passwordRules = {
            length: (value) => value.length >= 8,
            uppercase: (value) => /[A-Z]/.test(value),
            lowercase: (value) => /[a-z]/.test(value),
            number: (value) => /[0-9]/.test(value),
            symbol: (value) => /[@$!%*?&]/.test(value),
        };

        function updatePasswordRequirements() {
            const value = passwordInput.value;
            let isValid = true;

            passwordRequirements?.setAttribute('data-has-input', String(value.length > 0));

            Object.entries(passwordRules).forEach(([rule, check]) => {
                const passed = check(value);
                const item = document.querySelector(`[data-password-rule="${rule}"]`);
                item?.setAttribute('data-valid', String(passed));
                isValid = isValid && passed;
            });

            passwordInput.setCustomValidity(value && !isValid
                ? 'Sila lengkapkan semua syarat kata laluan yang dipaparkan.'
                : '');

            return isValid;
        }

        function updatePasswordConfirmation() {
            const password = passwordInput.value;
            const confirmation = passwordConfirmationInput.value;

            passwordConfirmationInput.setCustomValidity(
                confirmation && password !== confirmation
                    ? 'Sahkan kata laluan mesti sepadan.'
                    : ''
            );
        }

        passwordInput.addEventListener('input', () => {
            updatePasswordRequirements();
            updatePasswordConfirmation();
        });
        passwordConfirmationInput.addEventListener('input', updatePasswordConfirmation);
        updatePasswordRequirements();
        registerForm.addEventListener('submit', (event) => {
            updatePasswordRequirements();
            updatePasswordConfirmation();
            if (!registerForm.checkValidity()) {
                event.preventDefault();
                registerForm.reportValidity();
            }
        });

        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = button.closest('.login-input-wrap')?.querySelector('input');
                if (!input) return;

                const showPassword = input.type === 'password';
                input.type = showPassword ? 'text' : 'password';
                button.classList.toggle('is-visible', showPassword);
                button.setAttribute('aria-label', showPassword ? 'Sembunyi kata laluan' : 'Papar kata laluan');
                button.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
                input.focus();
            });
        });
    </script>
</body>
</html>
