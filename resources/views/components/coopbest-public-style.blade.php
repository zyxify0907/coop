<style>
    :root {
        --cb-navy: #062B53;
        --cb-red: #E32636;
        --cb-white: #FFFFFF;
        --cb-text: #0B1F3A;
        --cb-muted: #4B5D75;
        --cb-border: #D8E1EC;
        --cb-soft: #F5F8FC;
        --cb-focus: rgba(6, 43, 83, .22);
        --cb-container: 1440px;
        --cb-font: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    * { box-sizing: border-box; }
    html {
        min-width: 0;
        scroll-behavior: smooth;
        scroll-padding-top: 96px;
    }
    body {
        min-width: 0;
        margin: 0;
        overflow-x: hidden;
        color: var(--cb-text);
        background: var(--cb-white);
        font-family: var(--cb-font);
        line-height: 1.5;
        text-rendering: geometricPrecision;
        -webkit-font-smoothing: antialiased;
    }
    img, svg { display: block; max-width: 100%; }
    a, button, input { font: inherit; }
    a { color: inherit; }
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
    :focus-visible {
        outline: 3px solid var(--cb-focus);
        outline-offset: 3px;
    }

    .public-header {
        position: sticky;
        top: 0;
        z-index: 30;
        min-height: 88px;
        background: var(--cb-white);
        border-bottom: 1px solid var(--cb-border);
    }
    .public-nav {
        width: min(var(--cb-container), calc(100% - 96px));
        min-height: 88px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 28px;
    }
    .public-brand {
        display: inline-flex;
        align-items: center;
        gap: 18px;
        min-width: 0;
        color: var(--cb-text);
        text-decoration: none;
    }
    .public-brand__logo {
        width: 78px;
        height: 70px;
        object-fit: contain;
        flex: 0 0 auto;
    }
    .public-brand__divider {
        width: 1px;
        height: 54px;
        background: var(--cb-navy);
        opacity: .75;
        flex: 0 0 auto;
    }
    .public-brand__copy { display: grid; gap: 2px; min-width: 0; }
    .public-brand__name {
        color: var(--cb-navy);
        font-size: 35px;
        line-height: .95;
        font-weight: 900;
        letter-spacing: 0;
    }
    .public-brand__name span { color: var(--cb-red); }
    .public-brand__sub {
        color: var(--cb-navy);
        font-size: 19px;
        line-height: 1.2;
        font-weight: 600;
        letter-spacing: 0;
    }
    .public-menu {
        display: flex;
        align-items: center;
        gap: 34px;
        margin-left: auto;
    }
    .public-menu a {
        position: relative;
        color: var(--cb-navy);
        text-decoration: none;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 0;
        transition: color .18s ease, background-color .18s ease, border-color .18s ease;
    }
    .public-menu a:not(.public-login-link)::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: -14px;
        height: 4px;
        background: transparent;
        border-radius: 1px;
        transition: background-color .18s ease;
    }
    .public-menu a:hover,
    .public-menu a:focus-visible { color: var(--cb-red); }
    .public-menu a.is-active::after,
    .public-menu a:not(.public-login-link):hover::after { background: var(--cb-red); }
    .public-login-link,
    .public-button {
        min-height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 0 28px;
        border: 1px solid transparent;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 800;
        line-height: 1;
        cursor: pointer;
        transition: background-color .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease;
    }
    .public-login-link,
    .public-button--danger {
        background: var(--cb-red);
        color: var(--cb-white) !important;
        border-color: var(--cb-red);
    }
    .public-login-link:hover,
    .public-login-link:focus-visible,
    .public-button--danger:hover,
    .public-button--danger:focus-visible {
        background: #B91524;
        border-color: #B91524;
        color: var(--cb-white) !important;
    }
    .public-button--light {
        min-width: 262px;
        min-height: 56px;
        background: var(--cb-white);
        color: var(--cb-navy);
        border-color: var(--cb-white);
    }
    .public-button--light:hover,
    .public-button--light:focus-visible {
        color: var(--cb-navy);
        background: #EDF3FA;
        border-color: #EDF3FA;
    }
    .public-text-link {
        display: inline-flex;
        align-items: center;
        min-height: 44px;
        color: var(--cb-navy);
        font-weight: 600;
        text-decoration-thickness: 1px;
        text-underline-offset: 6px;
        transition: color .18s ease;
    }
    .public-text-link--light { color: var(--cb-white); }
    .public-text-link:hover,
    .public-text-link:focus-visible { color: var(--cb-red); }
    .public-red-line {
        display: block;
        width: 58px;
        height: 5px;
        margin-bottom: 24px;
        background: var(--cb-red);
        border-radius: 1px;
    }
    .public-red-line--small {
        width: 58px;
        height: 4px;
        margin: 8px 0 14px;
    }
    .public-kicker {
        margin: 0;
        color: var(--cb-red);
        font-size: 14px;
        font-weight: 800;
        letter-spacing: .38em;
        text-transform: uppercase;
    }
    .public-wing {
        position: absolute;
        pointer-events: none;
        user-select: none;
        opacity: .08;
        filter: grayscale(1) brightness(2.4);
    }
    .public-footer {
        min-height: 48px;
        display: grid;
        place-items: center;
        padding: 12px 24px;
        color: var(--cb-white);
        background: var(--cb-navy);
        text-align: center;
        font-size: 14px;
        font-weight: 500;
    }
    .public-footer p { margin: 0; }
    .public-menu-button {
        display: none;
        width: 46px;
        height: 46px;
        border: 1px solid var(--cb-border);
        border-radius: 4px;
        background: var(--cb-white);
        color: var(--cb-navy);
        cursor: pointer;
    }
    .public-menu-button__bar {
        display: block;
        width: 22px;
        height: 2px;
        margin: 5px auto;
        background: currentColor;
    }

    .home-hero {
        min-height: calc(100vh - 136px);
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        border-bottom: 1px solid var(--cb-border);
    }
    .home-hero__left {
        position: relative;
        min-width: 0;
        display: flex;
        align-items: center;
        overflow: hidden;
        color: var(--cb-white);
        background: var(--cb-navy);
    }
    .home-hero__left::after,
    .login-intro::after {
        content: '';
        position: absolute;
        top: 0;
        right: -24px;
        width: 48px;
        height: 100%;
        background: var(--cb-red);
        transform: skewX(-11deg);
        transform-origin: top;
        z-index: 2;
    }
    .home-hero__content {
        position: relative;
        z-index: 3;
        width: min(640px, calc(100% - 96px));
        margin: 0 auto;
    }
    .home-hero h1 {
        margin: 0;
        color: var(--cb-white);
        font-size: clamp(36px, 4.2vw, 58px);
        line-height: 1.08;
        font-weight: 900;
        letter-spacing: 0;
    }
    .home-hero__subtitle {
        margin: 12px 0 16px;
        color: var(--cb-white);
        font-size: clamp(25px, 2.6vw, 40px);
        line-height: 1.18;
        font-weight: 400;
        letter-spacing: 0;
    }
    .home-hero__text {
        max-width: 720px;
        margin: 0;
        color: rgba(255, 255, 255, .92);
        font-size: 19px;
    }
    .home-hero__actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 26px 36px;
        margin-top: 34px;
    }
    .public-wing--home {
        left: -30px;
        bottom: 22px;
        width: min(560px, 68vw);
    }
    .home-hero__right {
        min-width: 0;
        display: flex;
        align-items: center;
        background: var(--cb-white);
    }
    .home-hero__right-inner {
        width: min(700px, calc(100% - 96px));
        margin: 0 auto;
    }
    .home-hero__right h2 {
        margin: 0 0 22px;
        color: var(--cb-navy);
        font-size: clamp(34px, 3.6vw, 54px);
        line-height: 1.08;
        font-weight: 900;
        letter-spacing: 0;
    }
    .home-visual { margin: 0; }
    .home-visual img {
        width: 100%;
        aspect-ratio: 16 / 9;
        object-fit: cover;
        border-radius: 4px;
    }
    .home-visual figcaption {
        margin-top: 10px;
        color: var(--cb-muted);
        font-size: 14px;
    }
    .home-services {
        min-height: 116px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        align-items: center;
        width: min(var(--cb-container), calc(100% - 96px));
        margin: 0 auto;
        background: var(--cb-white);
    }
    .home-service {
        min-width: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 28px;
        padding: 28px 34px;
        border-right: 1px solid var(--cb-border);
    }
    .home-service:last-child { border-right: 0; }
    .home-service__icon {
        width: 54px;
        height: 54px;
        flex: 0 0 auto;
        color: var(--cb-navy);
    }
    .home-service strong {
        display: block;
        color: var(--cb-navy);
        font-size: 18px;
        line-height: 1.25;
        font-weight: 800;
    }
    .home-service small {
        display: block;
        margin-top: 4px;
        color: var(--cb-muted);
        font-size: 15px;
    }
    .home-about {
        border-top: 1px solid var(--cb-border);
        background: var(--cb-soft);
    }
    .home-about__inner {
        width: min(900px, calc(100% - 48px));
        margin: 0 auto;
        padding: 64px 0;
        text-align: center;
    }
    .home-about h2 {
        margin: 12px auto;
        color: var(--cb-navy);
        font-size: clamp(26px, 3vw, 38px);
        line-height: 1.18;
        letter-spacing: 0;
    }
    .home-about p:last-child {
        margin: 0 auto;
        max-width: 760px;
        color: var(--cb-muted);
        font-size: 17px;
    }

    .login-page {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        background: var(--cb-soft);
    }
    .login-shell {
        flex: 1 0 auto;
        min-height: calc(100dvh - 136px);
        width: 100%;
        margin: 0;
        padding: 0;
        display: grid;
        grid-template-columns: minmax(0, 60%) minmax(0, 40%);
        align-items: stretch;
        overflow: hidden;
    }
    .login-intro {
        position: relative;
        min-width: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding-block: clamp(28px, 4.8vh, 56px);
        overflow: hidden;
        color: var(--cb-white);
        background: var(--cb-navy);
        border: 0;
        border-radius: 0;
    }
    .login-intro__content {
        position: relative;
        z-index: 3;
        width: min(760px, calc(100% - clamp(72px, 9vw, 150px)));
        max-width: 760px;
        margin: 0 auto;
        padding-right: clamp(64px, 8vw, 130px);
    }
    .login-brand {
        display: inline-flex;
        align-items: center;
        gap: 24px;
        margin-bottom: 62px;
        color: var(--cb-white);
        text-decoration: none;
    }
    .login-brand img {
        width: 128px;
        height: 118px;
        object-fit: contain;
        flex: 0 0 auto;
    }
    .login-brand__divider {
        width: 1px;
        height: 88px;
        background: rgba(255, 255, 255, .85);
    }
    .login-brand strong {
        display: block;
        color: var(--cb-white);
        font-size: clamp(38px, 4.2vw, 62px);
        line-height: .96;
        font-weight: 900;
        letter-spacing: 0;
    }
    .login-brand strong span { color: var(--cb-red); }
    .login-brand small {
        display: block;
        margin-top: 8px;
        color: var(--cb-white);
        font-size: clamp(20px, 2.1vw, 30px);
        line-height: 1.15;
        font-weight: 700;
    }
    .login-intro h1 {
        max-width: 10.8em;
        margin: 0;
        color: var(--cb-white);
        font-size: clamp(30px, 3.2vw, 52px);
        line-height: 1.12;
        font-weight: 900;
        letter-spacing: 0;
    }
    .login-intro__subtitle {
        margin: 10px 0;
        color: var(--cb-white);
        font-size: clamp(20px, 2vw, 32px);
        line-height: 1.16;
        font-weight: 400;
    }
    .login-intro__text {
        max-width: 670px;
        margin: 0;
        color: rgba(255, 255, 255, .92);
        font-size: clamp(15px, 1.15vw, 18px);
    }
    .public-wing--login {
        left: -42px;
        bottom: 34px;
        width: min(430px, 60vw);
    }
    .login-panel {
        min-width: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: clamp(28px, 4.8vh, 56px) clamp(34px, 5vw, 72px);
        background: var(--cb-white);
        border: 0;
        border-radius: 0;
    }
    .login-panel__top {
        display: flex;
        justify-content: flex-end;
    }
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #1473D1;
        text-decoration: none;
        font-size: 17px;
        font-weight: 700;
    }
    .back-link svg { width: 23px; height: 23px; }
    .back-link:hover,
    .back-link:focus-visible {
        color: var(--cb-red);
        text-decoration: underline;
        text-underline-offset: 5px;
    }
    .login-form-wrap {
        width: min(520px, 100%);
        margin: 0 auto;
    }
    .login-form-head h2 {
        margin: 0;
        color: var(--cb-navy);
        font-size: clamp(32px, 3.2vw, 44px);
        line-height: 1.1;
        font-weight: 900;
        letter-spacing: 0;
    }
    .login-form-head p {
        margin: 0 0 34px;
        color: var(--cb-muted);
        font-size: 17px;
    }
    .form-alert {
        margin: 0 0 22px;
        padding: 13px 16px;
        color: #8F1724;
        background: #FFF2F3;
        border: 1px solid #F3B8C0;
        border-left: 4px solid var(--cb-red);
        border-radius: 4px;
        font-size: 14px;
        font-weight: 700;
    }
    .login-form { display: grid; gap: 22px; }
    .login-field label {
        display: block;
        margin-bottom: 8px;
        color: var(--cb-navy);
        font-size: 16px;
        font-weight: 800;
    }
    .login-input-wrap {
        min-height: 58px;
        display: grid;
        grid-template-columns: 34px 1fr auto;
        align-items: center;
        gap: 14px;
        padding: 0 18px;
        color: #6A7688;
        background: var(--cb-white);
        border: 1px solid #B9C8D9;
        border-radius: 4px;
        transition: border-color .18s ease, box-shadow .18s ease;
    }
    .login-input-wrap:focus-within {
        border-color: #1473D1;
        box-shadow: 0 0 0 3px rgba(20, 115, 209, .2);
    }
    .login-input-wrap svg {
        width: 25px;
        height: 25px;
    }
    .login-input-wrap input {
        width: 100%;
        min-width: 0;
        height: 56px;
        padding: 0;
        border: 0;
        outline: 0;
        color: var(--cb-text);
        background: transparent;
        font-size: 17px;
        font-weight: 500;
    }
    .login-input-wrap input::placeholder { color: #7A8798; }
    .password-toggle {
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        padding: 0;
        border: 0;
        border-radius: 4px;
        color: #5E6979;
        background: transparent;
        cursor: pointer;
    }
    .password-toggle:hover,
    .password-toggle:focus-visible {
        color: var(--cb-navy);
        background: #EDF3FA;
    }
    .password-toggle .eye-hide { display: none; }
    .password-toggle.is-visible .eye-show { display: none; }
    .password-toggle.is-visible .eye-hide { display: block; }
    .field-error {
        min-height: 20px;
        margin: 6px 0 0;
        color: #B91524;
        font-size: 13px;
        font-weight: 700;
    }
    .login-field.has-error .login-input-wrap {
        border-color: var(--cb-red);
        box-shadow: 0 0 0 3px rgba(227, 38, 54, .13);
    }
    .login-options {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-top: -2px;
    }
    .remember-check {
        display: inline-flex;
        align-items: center;
        gap: 11px;
        color: var(--cb-navy);
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
    }
    .remember-check input {
        width: 22px;
        height: 22px;
        margin: 0;
        accent-color: var(--cb-red);
        cursor: pointer;
    }
    .login-options a,
    .register-row a {
        color: #1473D1;
        font-weight: 800;
        text-underline-offset: 5px;
    }
    .login-options a:hover,
    .login-options a:focus-visible,
    .register-row a:hover,
    .register-row a:focus-visible { color: var(--cb-red); }
    .login-submit {
        width: 100%;
        min-height: 58px;
        margin-top: 8px;
        font-size: 19px;
    }
    .login-submit:disabled {
        cursor: wait;
        opacity: .78;
    }
    .register-row {
        display: flex;
        justify-content: center;
        margin-top: 28px;
    }
    @media (max-width: 1180px) {
        .public-nav { width: min(var(--cb-container), calc(100% - 48px)); }
        .public-menu { gap: 22px; }
        .public-brand__logo { width: 64px; height: 58px; }
        .public-brand__name { font-size: 29px; }
        .public-brand__sub { font-size: 16px; }
        .home-hero__content,
        .home-hero__right-inner { width: min(650px, calc(100% - 56px)); }
        .home-services { width: calc(100% - 48px); }
        .login-shell {
            width: 100%;
            grid-template-columns: minmax(0, 60%) minmax(0, 40%);
        }
        .login-panel {
            padding-inline: clamp(22px, 3vw, 42px);
        }
        .login-form-wrap {
            width: min(460px, 100%);
        }
    }

    @media (max-height: 780px) and (min-width: 901px) {
        .login-shell {
            min-height: calc(100dvh - 126px);
        }
        .login-intro,
        .login-panel {
            padding-block: 28px;
        }
        .login-intro__content {
            width: min(640px, calc(100% - 96px));
            padding-right: clamp(58px, 7vw, 100px);
        }
        .login-intro h1,
        .login-form-head h2 {
            font-size: clamp(28px, 2.7vw, 38px);
        }
        .login-intro__subtitle {
            font-size: clamp(19px, 1.8vw, 25px);
        }
        .login-form-head p {
            margin-bottom: 18px;
        }
        .login-form {
            gap: 14px;
        }
        .login-submit {
            margin-top: 2px;
        }
        .register-row {
            margin-top: 16px;
        }
    }

    @media (max-width: 900px) {
        .public-header,
        .public-nav { min-height: 78px; }
        .public-menu-button { display: block; }
        .public-menu {
            position: absolute;
            top: 78px;
            left: 24px;
            right: 24px;
            display: none;
            margin: 0;
            padding: 16px;
            border: 1px solid var(--cb-border);
            background: var(--cb-white);
            box-shadow: 0 16px 35px rgba(11, 31, 58, .14);
        }
        .public-menu.is-open {
            display: grid;
            gap: 4px;
        }
        .public-menu a {
            display: flex;
            min-height: 44px;
            align-items: center;
            padding: 0 12px;
        }
        .public-menu a::after { display: none; }
        .public-login-link {
            margin-top: 6px;
            justify-content: center;
        }
        .home-hero {
            min-height: 0;
            grid-template-columns: 1fr;
        }
        .home-hero__left::after,
        .login-intro::after { display: none; }
        .home-hero__left { min-height: 540px; }
        .home-hero__right { padding: 52px 0 48px; }
        .home-services {
            grid-template-columns: 1fr;
            align-items: stretch;
            width: 100%;
        }
        .home-service {
            justify-content: flex-start;
            padding-inline: 32px;
            border-right: 0;
            border-bottom: 1px solid var(--cb-border);
        }
        .home-service:last-child { border-bottom: 0; }
        .login-shell {
            width: min(640px, calc(100% - 36px));
            padding: 34px 0;
            grid-template-columns: 1fr;
            overflow: visible;
            border-bottom: 0;
        }
        .login-intro {
            min-height: 220px;
            padding: 34px 0;
            border-right: 1px solid var(--cb-navy);
            border-bottom: 0;
            border-radius: 8px 8px 0 0;
        }
        .login-intro__content {
            width: min(540px, calc(100% - 48px));
            padding-right: 0;
        }
        .login-intro h1 { font-size: 34px; }
        .login-intro__subtitle { font-size: 24px; }
        .login-panel {
            min-height: auto;
            padding: 34px 30px 38px;
            border-left: 1px solid var(--cb-border);
            border-radius: 0 0 8px 8px;
        }
    }

    @media (max-width: 620px) {
        html { scroll-padding-top: 78px; }
        .public-nav { width: calc(100% - 28px); gap: 14px; }
        .public-brand { gap: 10px; }
        .public-brand__logo { width: 50px; height: 48px; }
        .public-brand__divider { height: 40px; }
        .public-brand__name { font-size: 23px; }
        .public-brand__sub { font-size: 12px; }
        .public-menu { left: 14px; right: 14px; }
        .home-hero__left { min-height: 430px; }
        .home-hero__content,
        .home-hero__right-inner { width: calc(100% - 36px); }
        .home-hero h1 { font-size: 34px; }
        .home-hero__subtitle { font-size: 24px; }
        .home-hero__text { font-size: 16px; }
        .home-hero__actions {
            align-items: stretch;
            gap: 16px;
        }
        .public-button--light { width: 100%; min-width: 0; }
        .public-text-link--light { justify-content: center; }
        .public-kicker { font-size: 12px; letter-spacing: .26em; }
        .home-hero__right h2 { font-size: 31px; }
        .home-visual img { aspect-ratio: 4 / 3; }
        .home-service { gap: 18px; padding: 22px 20px; }
        .home-service__icon { width: 44px; height: 44px; }
        .home-about__inner { width: calc(100% - 36px); padding: 48px 0; }
        .login-shell {
            width: 100%;
            padding: 0;
            background: var(--cb-white);
        }
        .login-intro {
            display: none;
        }
        .login-panel {
            padding: 32px 18px 38px;
            border: 0;
            border-radius: 0;
        }
        .back-link { font-size: 15px; }
        .login-form-head h2 { font-size: 34px; }
        .login-form-head p { margin-bottom: 24px; font-size: 16px; }
        .login-input-wrap {
            min-height: 56px;
            grid-template-columns: 28px 1fr auto;
            gap: 10px;
            padding-inline: 14px;
        }
        .login-input-wrap input { height: 54px; font-size: 16px; }
        .login-options {
            align-items: flex-start;
            flex-direction: column;
            gap: 14px;
        }
        .login-submit {
            min-height: 56px;
            font-size: 19px;
        }
        .public-footer { font-size: 12px; }
    }

    @media (max-width: 620px) {
        body.login-page {
            background: var(--cb-soft);
        }
        body.login-page .public-header,
        body.login-page .public-nav {
            min-height: 70px;
        }
        body.login-page .public-nav {
            width: calc(100% - 24px);
        }
        body.login-page .public-brand {
            gap: 8px;
            flex: 1 1 auto;
            overflow: hidden;
        }
        body.login-page .public-brand__logo {
            width: 42px;
            height: 42px;
        }
        body.login-page .public-brand__divider {
            height: 34px;
        }
        body.login-page .public-brand__name {
            font-size: 20px;
        }
        body.login-page .public-brand__sub {
            max-width: 180px;
            overflow: hidden;
            color: var(--cb-muted);
            font-size: 11px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        body.login-page .public-menu-button {
            width: 42px;
            height: 42px;
            flex: 0 0 auto;
        }
        body.login-page .public-menu {
            top: 70px;
            left: 12px;
            right: 12px;
        }
        body.login-page .login-shell {
            width: min(100%, 430px);
            min-height: calc(100svh - 118px);
            margin: 0 auto;
            padding: 18px 14px 24px;
            background: var(--cb-soft);
        }
        body.login-page .login-panel {
            align-items: flex-start;
            padding: 24px 18px 26px;
            background: var(--cb-white);
            border: 1px solid var(--cb-border);
            border-radius: 8px;
        }
        body.login-page .login-form-wrap {
            width: 100%;
        }
        body.login-page .login-form-head h2 {
            font-size: 28px;
            line-height: 1.15;
        }
        body.login-page .public-red-line--small {
            width: 46px;
            height: 4px;
            margin: 10px 0 12px;
        }
        body.login-page .login-form-head p {
            margin-bottom: 18px;
            font-size: 14px;
        }
        body.login-page .login-form {
            gap: 14px;
        }
        body.login-page .login-field label {
            font-size: 13px;
        }
        body.login-page .login-input-wrap {
            min-height: 50px;
            grid-template-columns: 24px 1fr auto;
            padding-inline: 12px;
        }
        body.login-page .login-input-wrap svg {
            width: 21px;
            height: 21px;
        }
        body.login-page .login-input-wrap input {
            height: 48px;
            font-size: 15px;
        }
        body.login-page .password-toggle {
            width: 38px;
            height: 38px;
        }
        body.login-page .login-options {
            gap: 10px;
        }
        body.login-page .remember-check {
            font-size: 14px;
        }
        body.login-page .remember-check input {
            width: 19px;
            height: 19px;
        }
        body.login-page .login-options a,
        body.login-page .register-row a {
            font-size: 14px;
        }
        body.login-page .login-submit {
            min-height: 50px;
            font-size: 16px;
        }
        body.login-page .register-row {
            margin-top: 18px;
        }
        body.login-page .public-footer {
            min-height: 48px;
            padding-inline: 14px;
            font-size: 11px;
            line-height: 1.4;
        }
    }
</style>
