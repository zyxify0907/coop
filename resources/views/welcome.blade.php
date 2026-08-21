<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="CoopBest - Platform pengurusan digital terpadu untuk Koperasi Politeknik.">
    <meta name="theme-color" content="#2453A6">
    <title>CoopBest - Koperasi Politeknik Digital</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* Warna Koperasi Politeknik */
            --primary: #C62828;          /* Merah CoopBest */
            --primary-dark: #991B1B;     /* Merah Gelap */
            --secondary: #2453A6;        /* Biru CoopBest */
            --secondary-hover: #1D4ED8;  
            --dark: #0F172A;             
            --body: #475569;             
            --line: #E2E8F0;             
            --light: #F8FAFC;            
            --white: #FFFFFF;
            --radius: 10px;
            --shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
            --container: 1200px;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--dark);
            background: #FAFAFA;
            line-height: 1.7;
            font-size: 18px;
            -webkit-font-smoothing: antialiased;
        }

        a { color: var(--secondary); text-decoration: underline; text-underline-offset: 4px; font-weight: 700; }
        a:hover { color: var(--primary); }
        img, svg { display: block; max-width: 100%; }

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
        .nav-links {
            display: flex;
            align-items: center;
            gap: 28px;
            font-size: 16px;
            font-weight: 700;
        }
        .nav-links a { 
            color: var(--dark); 
            text-decoration: none; 
            transition: color 0.2s ease;
            padding: 8px 0;
        }
        .nav-links a:hover { color: var(--primary); text-decoration: none; }
        .nav-actions { display: flex; align-items: center; gap: 14px; }
        
        /* Interactive Buttons */
        .button {
            min-height: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0 26px;
            border: 2px solid transparent;
            border-radius: 10px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.25s ease;
        }
        .button.primary { 
            background: var(--primary); 
            color: var(--white); 
            box-shadow: 0 4px 14px rgba(185, 28, 28, 0.25);
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
        }

        /* Hero Section */
        .hero {
            position: relative;
            padding: 160px 0 90px;
            background: #FFFFFF;
            border-bottom: 1px solid var(--line);
        }
        .hero-inner {
            width: min(var(--container), calc(100% - 32px));
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(340px, 0.85fr);
            gap: 60px;
            align-items: center;
        }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 100px;
            background: #DBEAFE;
            color: var(--secondary);
            font-size: 14px;
            font-weight: 800;
            border: 1px solid #93C5FD;
            margin-bottom: 12px;
        }
        h1 {
            margin: 16px 0 20px;
            max-width: 680px;
            font-size: clamp(34px, 4.2vw, 48px);
            line-height: 1.2;
            letter-spacing: -0.02em;
            color: var(--dark);
            font-weight: 800;
        }
        .lead {
            max-width: 620px;
            margin: 0;
            color: var(--body);
            font-size: 19px;
            line-height: 1.6;
        }
        .hero-actions { display: flex; gap: 16px; flex-wrap: wrap; margin-top: 36px; }
        
        /* Hero Preview Mockup */
        .hero-preview {
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 14px;
            background: var(--white);
            box-shadow: var(--shadow);
        }
        .mock-browser {
            overflow: hidden;
            border-radius: 10px;
            background: var(--white);
            border: 1px solid var(--line);
        }
        .mock-topbar {
            height: 44px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 16px;
            border-bottom: 1px solid var(--line);
            background: #F8FAFC;
        }
        .mock-dot { width: 12px; height: 12px; border-radius: 50%; background: #CBD5E1; }
        .mock-body { padding: 20px; display: grid; gap: 16px; }
        .mock-cards { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .mock-card { min-height: 84px; padding: 14px; border: 1.5px solid #E2E8F0; border-radius: 10px; background: #F8FAFC; }
        .mock-card span { display: block; width: 50%; height: 10px; margin-bottom: 16px; border-radius: 999px; background: #CBD5E1; }
        .mock-card strong { display: block; height: 16px; border-radius: 999px; background: var(--primary); }
        .mock-chart {
            height: 170px;
            border: 1.5px solid #E2E8F0;
            border-radius: 10px;
            background: #F8FAFC;
            position: relative;
            overflow: hidden;
        }
        .mock-chart::before, .mock-chart::after {
            content: ''; position: absolute; left: 20px; right: 20px; height: 45px; border-radius: 999px; border-top: 4px solid var(--primary);
        }
        .mock-chart::before { top: 60px; }
        .mock-chart::after { top: 85px; border-color: var(--secondary); }

        /* Section Layouts */
        .section {
            width: min(var(--container), calc(100% - 32px));
            margin: 0 auto;
            padding: 90px 0;
        }
        .section-head { max-width: 680px; margin: 0 auto 52px; text-align: center; }
        .section-head span { color: var(--secondary); font-weight: 800; font-size: 14px; text-transform: uppercase; letter-spacing: 0.08em; }
        .section-head h2 { margin: 10px 0 16px; font-size: clamp(28px, 4vw, 38px); line-height: 1.25; color: var(--dark); font-weight: 800; }
        .section-head p { margin: 0; color: var(--body); font-size: 19px; }
        
        /* Features Grid */
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 32px;
        }
        .feature {
            padding: 38px 30px;
            border: 2px solid var(--line);
            border-radius: var(--radius);
            background: var(--white);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
        }
        .feature:hover { 
            border-color: var(--secondary); 
            transform: translateY(-4px);
            box-shadow: var(--shadow);
        }
        .feature-icon {
            width: 60px; height: 60px; display: grid; place-items: center; border-radius: 12px; margin-bottom: 24px; color: var(--white); background: var(--secondary); box-shadow: 0 4px 10px rgba(30, 64, 175, 0.25);
        }
        .feature h3 { margin: 0 0 12px; font-size: 22px; color: var(--dark); font-weight: 700; }
        .feature p { margin: 0; color: var(--body); font-size: 16px; }

        /* About Section */
        .about-section {
            background: var(--white);
            border-top: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
            padding: 90px 0;
        }
        .about-grid {
            width: min(var(--container), calc(100% - 32px));
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }
        .about-box {
            background: #F8FAFC;
            border: 2px solid var(--line);
            border-radius: var(--radius);
            padding: 36px;
        }
        .about-box h3 { margin-top: 0; color: var(--primary); font-size: 24px; }
        .about-list { list-style: none; padding: 0; margin: 20px 0 0; }
        .about-list li { padding-left: 28px; position: relative; margin-bottom: 12px; font-weight: 600; color: var(--dark); }
        .about-list li::before { content: '✓'; position: absolute; left: 0; color: var(--primary); font-weight: 800; }

        /* Contact Section */
        .contact-section {
            width: min(var(--container), calc(100% - 32px));
            margin: 0 auto;
            padding: 90px 0;
        }
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        .contact-info {
            background: var(--white);
            border: 2px solid var(--line);
            border-radius: var(--radius);
            padding: 40px;
        }
        .contact-item {
            display: flex;
            gap: 18px;
            margin-bottom: 24px;
            align-items: flex-start;
        }
        .contact-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #DBEAFE;
            color: var(--secondary);
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .contact-form {
            background: var(--white);
            border: 2px solid var(--line);
            border-radius: var(--radius);
            padding: 40px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 700; font-size: 15px; color: var(--dark); }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--line);
            border-radius: 8px;
            font-family: inherit;
            font-size: 16px;
            transition: border-color 0.2s ease;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--secondary);
        }

        /* Footer */
        .footer {
            padding: 36px 16px;
            border-top: 1px solid var(--line);
            color: var(--body);
            text-align: center;
            font-size: 16px;
            font-weight: 600;
            background: #F1F5F9;
        }

        /* Responsive Breakpoints */
        @media (max-width: 980px) {
            .nav-links { display: none; }
            .hero-inner, .about-grid, .contact-grid { grid-template-columns: 1fr; }
            .hero-preview { max-width: 620px; }
            .feature-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 620px) {
            .nav { height: 72px; }
            .brand-subtitle { display: none; }
            .brand-title { font-size: 22px; }
            .button { width: 100%; min-height: 48px; }
            .hero { padding: 110px 0 60px; }
            .section, .about-section, .contact-section { padding: 60px 0; }
        }
        /* Institutional public-site refinement */
        body { background: #F7F8FA; font-size: 16px; line-height: 1.6; }
        .site-header { background: #fff; backdrop-filter: none; border-bottom: 1px solid var(--line); box-shadow: none; }
        .nav { height: 78px; }
        .brand-mark { width: 52px; height: 52px; border-radius: 0; background: transparent; box-shadow: none; }
        .brand-mark img { width: 52px; height: 52px; }
        .brand-title { font-size: 24px; letter-spacing: -.04em; }
        .brand-subtitle { margin-top: 3px; color: var(--body); font-size: 11px; font-weight: 600; }

        @media (max-width: 560px) {
            .nav { height: 72px; }
            .brand-mark, .brand-mark img { width: 46px; height: 46px; }
        }
        .nav-links { gap: 22px; font-size: 14px; }
        .nav-links a { color: #475569; font-weight: 600; }
        .nav-links a:hover { color: var(--secondary); }
        .button { min-height: 42px; padding: 0 16px; border-radius: 7px; font-size: 14px; font-weight: 600; transition: background .18s ease, border-color .18s ease; }
        .button.primary { box-shadow: none; }
        .button.primary:hover { transform: none; box-shadow: none; }
        .button.soft { border: 1px solid #CBD5E1; }
        .button.soft:hover { transform: none; }
        .hero { padding: 132px 0 64px; background: #fff; border-bottom: 1px solid var(--line); }
        .hero-inner { grid-template-columns: minmax(0, 1fr) minmax(320px, .72fr); gap: 48px; }
        .eyebrow { margin-bottom: 10px; padding: 4px 8px; border: 0; border-radius: 4px; background: #FEF2F2; color: var(--primary); font-size: 11px; letter-spacing: .05em; }
        h1 { margin: 10px 0 16px; max-width: 680px; font-size: clamp(32px, 4.2vw, 46px); line-height: 1.14; font-weight: 700; }
        .lead { font-size: 16px; line-height: 1.7; }
        .hero-actions { margin-top: 28px; gap: 10px; }
        .hero-preview { padding: 10px; border-radius: 10px; box-shadow: none; background: #F8FAFC; }
        .mock-browser { border-radius: 7px; }
        .mock-topbar { height: 36px; }
        .mock-dot { width: 8px; height: 8px; }
        .mock-body { padding: 16px; gap: 12px; }
        .mock-card { min-height: 70px; padding: 12px; border-radius: 6px; }
        .mock-card strong { background: var(--secondary); }
        .mock-chart { height: 145px; border-radius: 6px; background: #fff; }
        .mock-chart::before, .mock-chart::after { border-radius: 0; }
        .mock-chart::before { border-color: var(--secondary); }
        .section { padding: 64px 0; }
        .section-head { max-width: 670px; margin: 0 0 32px; text-align: left; }
        .section-head span { color: var(--primary); font-size: 11px; font-weight: 600; }
        .section-head h2 { margin: 8px 0 10px; font-size: clamp(25px, 3vw, 32px); line-height: 1.25; font-weight: 700; }
        .section-head p { font-size: 15px; }
        .feature-grid { gap: 0; border: 1px solid var(--line); border-radius: 10px; background: #fff; overflow: hidden; }
        .feature { padding: 24px; border: 0; border-radius: 0; border-right: 1px solid var(--line); box-shadow: none; }
        .feature:last-child { border-right: 0; }
        .feature:hover { border-color: var(--line); transform: none; box-shadow: none; background: #F8FAFC; }
        .feature-icon { width: 36px; height: 36px; margin-bottom: 16px; border-radius: 6px; color: var(--secondary); background: #DBEAFE; box-shadow: none; }
        .feature h3 { margin-bottom: 8px; font-size: 17px; }
        .feature p { font-size: 14px; line-height: 1.65; }
        .about-section { padding: 64px 0; background: #F1F4F8; }
        .about-grid { gap: 48px; }
        .about-box { padding: 24px; border: 1px solid var(--line); border-radius: 8px; background: #fff; }
        .about-box h3 { font-size: 19px; }
        .about-list li { font-size: 14px; }
        .contact-section { padding: 64px 0; }
        .contact-grid { gap: 24px; }
        .contact-info, .contact-form { padding: 26px; border: 1px solid var(--line); border-radius: 10px; box-shadow: none; }
        .contact-icon { width: 36px; height: 36px; border-radius: 6px; background: #DBEAFE; }
        .footer { padding: 24px 16px; background: #F1F4F8; font-size: 13px; }
        @media (max-width: 980px) {
            .hero-inner,
            .about-grid,
            .contact-grid { grid-template-columns: minmax(0, 1fr); gap: 32px; }
            .hero-preview { max-width: 620px; }
            .feature-grid { grid-template-columns: 1fr; }
            .feature { border-right: 0; border-bottom: 1px solid var(--line); }
            .feature:last-child { border-bottom: 0; }
        }
        html, body { max-width: 100%; overflow-x: hidden; }
        img, svg { max-width: 100%; }
        @media (max-width: 620px) {
            .nav { gap: 12px; }
            .brand { gap: 8px; min-width: 0; }
            .brand-mark { width: 44px; height: 44px; }
            .brand-mark img { width: 44px; height: 44px; }
            .brand-title { font-size: 21px; }
            .nav-actions { display: flex; margin-left: auto; flex: 0 0 auto; }
            .nav-actions .button { width: auto; min-height: 38px; padding: 0 12px; font-size: 12px; white-space: nowrap; }
            .hero { padding: 108px 0 48px; }
            .hero-preview { display: none; }
            .section, .about-section, .contact-section { padding: 48px 0; }
            .hero-actions { flex-direction: column; align-items: stretch; }
            .hero-actions .button { width: 100%; }
        }

        /* Responsive safety net for common laptop, tablet and phone widths. */
        html, body { width: 100%; max-width: 100%; overflow-x: hidden; }
        .nav, .hero-inner, .section, .about-grid, .contact-grid,
        .hero-copy, .hero-preview, .feature, .about-box, .contact-info, .contact-form { min-width: 0; }
        .hero-preview, .mock-browser, .mock-body, .mock-chart { max-width: 100%; }
        .contact-form input, .contact-form textarea { max-width: 100%; }

        @media (min-width: 1200px) and (max-width: 1439px) {
            :root { --container: 1160px; }
            .hero-inner { gap: 40px; }
        }

        @media (max-width: 767px) {
            .nav, .hero-inner, .section, .about-grid, .contact-section { width: min(var(--container), calc(100% - 28px)); }
            .hero-inner, .about-grid, .contact-grid { gap: 24px; }
            .feature-grid { grid-template-columns: minmax(0, 1fr); }
            .contact-info, .contact-form, .about-box { padding: 20px; }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <nav class="nav" aria-label="Main navigation">
            <a class="brand" href="{{ url('/') }}">
                <span class="brand-mark" aria-hidden="true">
                    <img src="{{ asset('images/koperasi-logo.svg?v=2') }}" alt="">
                </span>
                <span>
                    <span class="brand-title">Coop</span>
                    <span class="brand-subtitle">Koperasi Politeknik</span>
                </span>
            </a>
            <div class="nav-links">
                <a href="#perkhidmatan">Perkhidmatan</a>
                <a href="#about">Mengenai Kami</a>
                <a href="#contact">Hubungi Kami</a>
                <a href="{{ route('login') }}">Log Masuk</a>
            </div>
            <div class="nav-actions">
                <a class="button primary" href="{{ route('login') }}">Log Masuk Portal</a>
            </div>
        </nav>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-inner">
                <div>
                    <span class="eyebrow">Platform Koperasi Politeknik Terpadu</span>
                    <h1>Platform Digital Koperasi CoopBest</h1>
                    <p class="lead">
                        Portal rasmi integrasi komuniti Politeknik. Memudahkan pensyarah, staf, dan pelajar menyemak syer ahli, belian kelengkapan pengajian, serta akaun perkhidmatan kedai koperasi.
                    </p>
                    <div class="hero-actions">
                        <a class="button primary" href="{{ route('login') }}">Log Masuk Ahli / Staf</a>
                        <a class="button soft" href="#about">Tentang Koperasi</a>
                    </div>
                </div>
                <div class="hero-preview" aria-label="CoopBest Dashboard Preview">
                    <div class="mock-browser">
                        <div class="mock-topbar">
                            <span class="mock-dot"></span>
                            <span class="mock-dot"></span>
                            <span class="mock-dot"></span>
                        </div>
                        <div class="mock-body">
                            <div class="mock-cards">
                                <div class="mock-card"><span></span><strong style="width:68%"></strong></div>
                                <div class="mock-card"><span></span><strong style="width:82%"></strong></div>
                                <div class="mock-card"><span></span><strong style="width:55%"></strong></div>
                            </div>
                            <div class="mock-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Perkhidmatan Section -->
        <section class="section" id="perkhidmatan">
            <div class="section-head">
                <span>Perkhidmatan & Sistem</span>
                <h2>Kemudahan Digital Warga Politeknik</h2>
                <p>Ekosistem pengurusan koperasi terpadu khas untuk kemudahan pelajar, pensyarah, dan pentadbir kedai koperasi.</p>
            </div>
            <div class="feature-grid">
                <article class="feature">
                    <div class="feature-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/>
                            <circle cx="9.5" cy="7" r="3.5"/>
                            <path d="M21 21v-2a3.5 3.5 0 0 0-3-3.46"/>
                            <path d="M16.5 3.4a3.5 3.5 0 0 1 0 6.8"/>
                        </svg>
                    </div>
                    <h3>Portal Pelajar & Staf</h3>
                    <p>Semakan jumlah syer terkumpul serta urusan borang keahlian Koperasi Politeknik secara dalam talian.</p>
                </article>
                <article class="feature">
                    <div class="feature-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m21 8-9-5-9 5 9 5 9-5Z"/>
                            <path d="M3 8v8l9 5 9-5V8"/>
                            <path d="M12 13v8"/>
                        </svg>
                    </div>
                    <h3>Buku Teks & Kelengkapan Kampus</h3>
                    <p>Tempahan awal dan pembelian barangan akademik, alat tulis, baju korporat politeknik, serta kit latihan amali pelajar.</p>
                </article>
                <article class="feature">
                    <div class="feature-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="6.5" width="18" height="11" rx="2"/>
                            <circle cx="12" cy="12" r="2"/>
                            <path d="M7 12h.01"/>
                            <path d="M17 12h.01"/>
                        </svg>
                    </div>
                    <h3>Jualan & Transaksi POS</h3>
                    <p>Sistem kaunter kedai Koperasi Politeknik yang membolehkan pembayaran tunai atau cashless dilakukan dengan lancar.</p>
                </article>
            </div>
        </section>

        <!-- About Us Section -->
        <section class="about-section" id="about">
            <div class="about-grid">
                <div>
                    <span style="color: var(--secondary); font-weight: 800; text-transform: uppercase; font-size: 14px;">Mengenai Kami</span>
                    <h2 style="font-size: 34px; margin: 10px 0 16px; color: var(--dark);">Koperasi Politeknik Berhad (CoopBest)</h2>
                    <p style="color: var(--body);">
                        CoopBest ditubuhkan untuk menjaga kebajikan ekonomi dan memberikan kemudahan perkhidmatan terbaik kepada seluruh warga akademik, pensyarah, staf pentadbiran, serta para pelajar Politeknik.
                    </p>
                    <p style="color: var(--body);">
                        Melalui platform digital CoopBest, kami komited menyokong aktiviti keusahawanan kampus dan menawarkan perkhidmatan retail barangan keperluan harian secara efisien.
                    </p>
                </div>
                <div class="about-box">
                    <h3>Fokus & Visi Utama</h3>
                    <ul class="about-list">
                        <li>Memperkasakan Sosio-Ekonomi Warga Politeknik</li>
                        <li>Ketelusan Pengurusan Syer Ahli</li>
                        <li>Menyediakan Barang Keperluan Akademik yang Lengkap</li>
                        <li>Mendukung Pembangunan Usahawan Siswa Kampus</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Contact Us Section -->
        <section class="contact-section" id="contact">
            <div class="section-head">
                <span>Hubungi Kami</span>
                <h2>Ada Sebarang Pertanyaan?</h2>
                <p>Sila berhubung dengan jawatankuasa Koperasi Politeknik untuk sebarang urusan keahlian, semakan syer, atau maklum balas.</p>
            </div>
            <div class="contact-grid">
                <div class="contact-info">
                    <h3 style="margin-top: 0; color: var(--dark); font-size: 24px;">Maklumat Pejabat Koperasi</h3>
                    <div style="margin-top: 24px;">
                        <div class="contact-item">
                            <div class="contact-icon">📍</div>
                            <div>
                                <strong>Lokasi Pejabat</strong>
                                <p style="margin: 4px 0 0; font-size: 15px; color: var(--body);">
                                    Kedai Koperasi CoopBest, Bangunan Pentadbiran Politeknik.
                                </p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">✉️</div>
                            <div>
                                <strong>Emel Rasmi</strong>
                                <p style="margin: 4px 0 0; font-size: 15px; color: var(--body);">koperasi@politeknik.edu.my</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">📞</div>
                            <div>
                                <strong>Telefon / WhatsApp</strong>
                                <p style="margin: 4px 0 0; font-size: 15px; color: var(--body);">+60 3-XXXX XXXX / +60 19-XXX XXXX</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">⏰</div>
                            <div>
                                <strong>Waktu Operasi Kedai</strong>
                                <p style="margin: 4px 0 0; font-size: 15px; color: var(--body);">Isnin - Jumaat (8:00 AM - 5:00 PM)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="contact-form">
                    <h3 style="margin-top: 0; color: var(--dark); font-size: 24px;">Hantar Mesej</h3>
                    <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Mesej anda telah dihantar!');">
                        <div class="form-group">
                            <label for="name">Nama Penuh</label>
                            <input type="text" id="name" placeholder="cth: Ahmad Bin Razali" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Emel / No. Kad Pengenalan / No. Matrik</label>
                            <input type="text" id="email" placeholder="cth: 04010103XXXX" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Mesej / Pertanyaan</label>
                            <textarea id="message" rows="4" placeholder="Tulis pertanyaan anda di sini..." required></textarea>
                        </div>
                        <button type="submit" class="button primary" style="width: 100%;">Hantar Pertanyaan</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        &copy; {{ date('Y') }} CoopBest - Koperasi Politeknik. Hak Cipta Terpelihara.
    </footer>
</body>
</html>
