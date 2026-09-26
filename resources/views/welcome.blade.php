<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="CoopBest - Portal rasmi Koperasi Politeknik Besut.">
    <meta name="theme-color" content="#062B53">
    <title>CoopBest - Koperasi Politeknik Besut</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @include('components.coopbest-public-style')
</head>
<body>
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

            <button class="public-menu-button" type="button" aria-controls="main-menu" aria-expanded="false" data-menu-toggle>
                <span class="public-menu-button__bar"></span>
                <span class="public-menu-button__bar"></span>
                <span class="public-menu-button__bar"></span>
                <span class="sr-only">Buka menu navigasi</span>
            </button>

            <div class="public-menu" id="main-menu" data-public-menu>
                <a class="is-active" href="{{ url('/') }}">Utama</a>
                <a href="#perkhidmatan">Perkhidmatan</a>
                <a href="#tentang">Tentang Kami</a>
                <a href="#hubungi">Hubungi Kami</a>
            </div>
        </nav>
    </header>

    <main>
        <section class="home-hero" aria-labelledby="home-title">
            <div class="home-hero__left">
                <div class="home-hero__content">
                    <span class="public-red-line" aria-hidden="true"></span>
                    <p class="home-hero__kicker">Portal Rasmi Koperasi</p>
                    <h1 id="home-title">Koperasi Politeknik Besut</h1>
                    <p class="home-hero__text">Urus keahlian, semak saham, tempahan dan operasi pekerja koperasi melalui satu portal rasmi yang mudah dan selamat.</p>
                    <div class="home-hero__actions">
                        <a class="public-button public-button--red" href="{{ route('login') }}">Log Masuk</a>
                        <a class="public-button public-button--outline-light" href="{{ route('register') }}">Register</a>
                    </div>
                    <p class="home-hero__note">Untuk pelajar, staf dan pekerja koperasi Politeknik Besut.</p>
                </div>
                <img class="public-wing public-wing--home" src="{{ asset('images/koperasi-logo.svg?v=3') }}" alt="" aria-hidden="true">
            </div>

            <div class="home-hero__right">
                <figure class="home-visual">
                    <img src="{{ asset('images/coopbest-store-hero.png') }}" alt="Visual konsep kedai koperasi kampus dengan buku, alat tulis, beg dan baju polo">
                    <figcaption>
                        <strong>Koperasi Politeknik Besut</strong>
                        <span><i aria-hidden="true"></i>Portal untuk pelajar, staf dan pekerja koperasi</span>
                    </figcaption>
                </figure>
            </div>
        </section>

        <section class="home-services" id="perkhidmatan" aria-labelledby="services-title">
            <div class="home-services__inner">
                <div class="home-services__head">
                    <span class="public-red-line public-red-line--small" aria-hidden="true"></span>
                    <p class="public-kicker">Perkhidmatan Utama</p>
                    <h2 id="services-title">Apa yang ingin anda uruskan?</h2>
                </div>

                <div class="home-services__list">
                    <a class="home-service" href="{{ route('register') }}">
                        <span class="home-service__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/>
                                <path d="M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </span>
                        <span class="home-service__copy"><strong>Keanggotaan</strong><small>Daftar dan semak maklumat ahli.</small></span>
                        <span class="home-service__arrow" aria-hidden="true">&rsaquo;</span>
                    </a>
                    <a class="home-service" href="{{ route('login') }}">
                        <span class="home-service__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19V9"/>
                                <path d="M10 19V5"/>
                                <path d="M16 19v-8"/>
                                <path d="M22 19V3"/>
                                <path d="M2 19h22"/>
                            </svg>
                        </span>
                        <span class="home-service__copy"><strong>Saham Koperasi</strong><small>Semak pegangan dan urusan saham.</small></span>
                        <span class="home-service__arrow" aria-hidden="true">&rsaquo;</span>
                    </a>
                    <a class="home-service" href="{{ route('login') }}">
                        <span class="home-service__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.38 6.3 16 4a4 4 0 0 1-8 0L3.62 6.3a2 2 0 0 0-1.04 2.42l1.14 3.42A2 2 0 0 0 6 13.45V21h12v-7.55a2 2 0 0 0 2.28-1.31l1.14-3.42a2 2 0 0 0-1.04-2.42Z"/>
                            </svg>
                        </span>
                        <span class="home-service__copy"><strong>Tempahan Baju</strong><small>Buat tempahan dan semak pesanan.</small></span>
                        <span class="home-service__arrow" aria-hidden="true">&rsaquo;</span>
                    </a>
                    <a class="home-service" href="{{ route('login') }}">
                        <span class="home-service__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                                <path d="M4 8h16a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2Z"/>
                                <path d="M9 14h6"/>
                            </svg>
                        </span>
                        <span class="home-service__copy"><strong>Pekerja Koperasi</strong><small>Urus kehadiran, tugasan dan operasi harian.</small></span>
                        <span class="home-service__arrow" aria-hidden="true">&rsaquo;</span>
                    </a>
                </div>

            </div>
        </section>

        <section class="home-about" id="tentang" aria-labelledby="about-title">
            <div class="home-about__inner">
                <div class="home-about__copy">
                    <span class="public-red-line public-red-line--small" aria-hidden="true"></span>
                    <p class="public-kicker">Tentang Kami</p>
                    <h2 id="about-title">Kami membantu memudahkan urusan koperasi secara digital.</h2>
                    <p>CoopBest ialah portal rasmi Koperasi Politeknik Besut yang menyatukan urusan keahlian, saham, tempahan, makluman rasmi dan operasi pekerja koperasi dalam satu sistem yang mudah digunakan oleh warga kampus.</p>
                    <p>Sistem ini dibangunkan untuk menjadikan pengurusan koperasi lebih tersusun, cepat dan selamat untuk pelajar, staf, pekerja koperasi serta pentadbir.</p>
                    <a class="public-button public-button--red" href="{{ route('login') }}">Masuk ke Portal</a>
                </div>

                <div class="home-about__points" aria-label="Fokus CoopBest">
                    <article class="home-about__point">
                        <strong>Keahlian</strong>
                        <span>Daftar akaun, semak profil dan urus maklumat ahli koperasi.</span>
                    </article>
                    <article class="home-about__point">
                        <strong>Saham</strong>
                        <span>Semak pegangan saham dan permohonan berkaitan koperasi.</span>
                    </article>
                    <article class="home-about__point">
                        <strong>Tempahan</strong>
                        <span>Buat tempahan baju koperasi dan pantau status pesanan.</span>
                    </article>
                    <article class="home-about__point">
                        <strong>Pekerja Koperasi</strong>
                        <span>Sokong urusan kehadiran, rekod kerja dan operasi koperasi harian.</span>
                    </article>
                </div>
            </div>
        </section>

        <section class="home-contact" id="hubungi" aria-labelledby="contact-title">
            <div class="home-contact__inner">
                <div class="home-contact__head">
                    <span class="public-red-line public-red-line--small" aria-hidden="true"></span>
                    <p class="public-kicker">Hubungi Kami</p>
                    <h2 id="contact-title">Perlu bantuan berkaitan koperasi?</h2>
                    <p>Hubungi pegawai berkaitan untuk urusan keahlian, saham, tempahan atau operasi pekerja koperasi.</p>
                </div>

                <div class="home-contact__list">
                    <article class="home-contact__card">
                        <span class="home-contact__role">Pengerusi Koperasi</span>
                        <strong>Norulmaisura Bt Mohamad</strong>
                        <a href="mailto:maisura.staff@example.com">maisura.staff@example.com</a>
                        <a href="tel:+60123456789">012-3456789</a>
                    </article>
                    <article class="home-contact__card">
                        <span class="home-contact__role">Staff Pengurusan Baju</span>
                        <strong>ADNAN BIN YAZID</strong>
                        <a href="mailto:suriyani.staff@example.com">suriyani.staff@example.com</a>
                        <a href="tel:+60124567890">012-4567890</a>
                    </article>
                    <article class="home-contact__card">
                        <span class="home-contact__role">Staff Pengurusan Saham</span>
                        <strong>ROSHILA BINTI ABDUL MUTALIB</strong>
                        <a href="mailto:azlina.staff@example.com">azlina.staff@example.com</a>
                        <a href="tel:+60122345678">012-2345678</a>
                    </article>
                    <article class="home-contact__card">
                        <span class="home-contact__role">Staff Pengurusan Kedai</span>
                        <strong>Nor Azira Binti Abd Razak</strong>
                        <a href="mailto:azira.staff@example.com">azira.staff@example.com</a>
                        <a href="tel:+60121234567">012-1234567</a>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <footer class="public-footer">
        <p>&copy; 2026 CoopBest. Hak Cipta Terpelihara.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const button = document.querySelector('[data-menu-toggle]');
            const menu = document.querySelector('[data-public-menu]');

            if (! button || ! menu) {
                return;
            }

            button.addEventListener('click', function () {
                const isOpen = button.getAttribute('aria-expanded') === 'true';
                button.setAttribute('aria-expanded', String(! isOpen));
                menu.classList.toggle('is-open', ! isOpen);
            });

            menu.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    button.setAttribute('aria-expanded', 'false');
                    menu.classList.remove('is-open');
                });
            });
        });
    </script>
</body>
</html>
