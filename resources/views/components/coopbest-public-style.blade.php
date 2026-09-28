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
    .home-hero__left::after {
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
        min-height: clamp(500px, 58dvh, 620px);
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
    .login-input-wrap input[type="password"]::-ms-reveal,
    .login-input-wrap input[type="password"]::-ms-clear {
        display: none;
    }
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

    body.login-page {
        background: var(--cb-white);
    }
    body.login-page .login-shell {
        min-height: clamp(500px, 58dvh, 620px);
        width: 100%;
        max-width: 100%;
        grid-template-columns: minmax(0, 49%) minmax(0, 51%);
        overflow: hidden;
        background: var(--cb-white);
    }
    body.login-page .login-intro {
        --login-seam-run: clamp(118px, 7.6vw, 148px);
        --login-seam-red: clamp(32px, 2.1vw, 40px);
        isolation: isolate;
        overflow: hidden;
        background: transparent;
        align-items: flex-start;
        padding-top: clamp(172px, 19.2vh, 202px);
        padding-bottom: clamp(32px, 5vh, 52px);
    }
    body.login-page .login-intro::before {
        content: '';
        position: absolute;
        inset: 0;
        z-index: 0;
        background: #082F59;
        clip-path: polygon(
            0 0,
            calc(100% - var(--login-seam-red)) 0,
            calc(100% - var(--login-seam-run) - var(--login-seam-red)) 100%,
            0 100%
        );
    }
    body.login-page .login-intro::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100%;
        height: 100%;
        background: #ED1C2E;
        transform: none;
        z-index: 2;
        clip-path: polygon(
            calc(100% - var(--login-seam-red)) 0,
            100% 0,
            calc(100% - var(--login-seam-run)) 100%,
            calc(100% - var(--login-seam-run) - var(--login-seam-red)) 100%
        );
        pointer-events: none;
    }
    body.login-page .login-intro__content {
        width: min(650px, calc(100% - clamp(150px, 14vw, 260px)));
        max-width: 650px;
        margin-left: clamp(70px, 6.7vw, 128px);
        margin-right: auto;
        padding-right: clamp(28px, 4vw, 68px);
    }
    body.login-page .login-intro .public-red-line,
    body.login-page .login-form-head .public-red-line {
        background: #ED1C2E;
    }
    body.login-page .login-kicker {
        margin: 0 0 clamp(16px, 2vh, 24px);
        color: inherit;
        font-size: clamp(12px, .82vw, 14px);
        line-height: 1.2;
        font-weight: 900;
        letter-spacing: .34em;
        text-transform: uppercase;
    }
    body.login-page .login-intro h1 {
        max-width: 9.8em;
        margin: 0 0 clamp(10px, 1.5vh, 14px);
        color: var(--cb-white);
        font-size: clamp(32px, 3vw, 48px);
    }
    body.login-page .login-intro__subtitle {
        margin: 0 0 clamp(10px, 1.4vh, 16px);
        font-size: clamp(20px, 1.7vw, 28px);
        line-height: 1.2;
    }
    body.login-page .login-intro__text {
        max-width: 650px;
        font-size: clamp(15px, 1.05vw, 18px);
        line-height: 1.55;
    }
    body.login-page .public-wing--login {
        left: clamp(-44px, -2vw, -22px);
        bottom: clamp(18px, 4vh, 38px);
        width: min(430px, 42vw);
        opacity: .04;
        filter: grayscale(1) brightness(2.7);
        z-index: 1;
    }
    body.login-page .login-panel {
        width: 100%;
        align-items: flex-start;
        justify-content: flex-start;
        padding: clamp(54px, 7.2vh, 68px) clamp(24px, 4vw, 64px) clamp(20px, 3.4vh, 36px);
        padding-left: clamp(76px, 4.8vw, 96px);
    }
    body.login-page .login-form-wrap {
        width: 100%;
        max-width: 610px;
        margin: 0;
    }
    body.login-page .login-form,
    body.login-page .login-form-head,
    body.login-page .register-row,
    body.login-page .secure-row {
        max-width: 610px;
    }
    body.login-page .login-form-head .login-kicker {
        margin-bottom: clamp(12px, 1.5vh, 16px);
        color: #082F59;
    }
    body.login-page .login-form-head h2 {
        color: #082F59;
        font-size: clamp(32px, 2.8vw, 44px);
    }
    body.login-page .login-form-head p {
        margin: 0 0 clamp(18px, 2.4vh, 26px);
        font-size: clamp(15px, 1.1vw, 18px);
    }
    body.login-page .login-form {
        gap: clamp(12px, 1.65vh, 18px);
    }
    body.login-page .login-field label {
        color: #082F59;
        font-size: clamp(14px, .98vw, 16px);
    }
    body.login-page .login-input-wrap {
        min-height: 56px;
        border: 1px solid #B8C6D8;
        border-radius: 6px;
    }
    body.login-page .login-input-wrap:focus-within {
        border-color: #1473D1;
        box-shadow: 0 0 0 3px rgba(20, 115, 209, .22);
    }
    body.login-page .login-input-wrap input {
        height: 56px;
    }
    body.login-page .password-toggle {
        width: 44px;
        height: 54px;
        border-left: 1px solid #D8E1EC;
        border-radius: 0;
    }
    body.login-page .login-options {
        margin-top: -4px;
    }
    body.login-page .remember-check {
        font-size: clamp(14px, .98vw, 16px);
    }
    body.login-page .login-submit {
        min-height: 56px;
        margin-top: 0;
        border-radius: 6px;
        background: #ED1C2E;
        border-color: #ED1C2E;
        font-size: clamp(17px, 1.25vw, 20px);
    }
    body.login-page .login-submit:hover,
    body.login-page .login-submit:focus-visible {
        background: #C91525;
        border-color: #C91525;
    }
    body.login-page .register-row {
        flex-wrap: wrap;
        align-items: center;
        gap: 4px;
        margin-top: clamp(14px, 2.2vh, 22px);
        color: #082F59;
        font-size: clamp(14px, 1vw, 16px);
    }
    body.login-page .secure-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: clamp(14px, 2.4vh, 22px);
        color: #637083;
        font-size: clamp(13px, .95vw, 15px);
    }
    body.login-page .secure-row svg {
        width: 17px;
        height: 17px;
        flex: 0 0 auto;
    }
    @media (max-height: 780px) and (min-width: 901px) {
        body.login-page .login-shell {
            min-height: calc(100dvh - 126px);
        }
        body.login-page .login-intro__content {
            width: min(620px, calc(100% - 120px));
            max-width: 620px;
            margin-left: clamp(56px, 6vw, 82px);
            padding-right: clamp(42px, 6vw, 82px);
        }
        body.login-page .login-intro {
            padding-top: clamp(134px, 16vh, 160px);
        }
        body.login-page .login-panel {
            padding-left: clamp(64px, 5vw, 80px);
            padding-top: clamp(40px, 5.4vh, 52px);
        }
        body.login-page .login-intro h1,
        body.login-page .login-form-head h2 {
            font-size: clamp(28px, 2.45vw, 38px);
        }
        body.login-page .login-form-head p {
            margin-bottom: 18px;
        }
        body.login-page .field-error {
            min-height: 16px;
            margin-top: 4px;
        }
    }
    @media (max-width: 900px) {
        body.login-page .login-shell {
            width: 100vw;
            max-width: 100vw;
            min-height: calc(100dvh - 126px);
            padding: clamp(28px, 5vh, 48px) 18px;
            grid-template-columns: 1fr;
            background: var(--cb-white);
        }
        body.login-page .login-intro,
        body.login-page .login-intro::before,
        body.login-page .login-intro::after {
            display: none;
        }
        body.login-page .login-panel {
            padding: 0;
            border: 0;
            border-radius: 0;
        }
        body.login-page .login-form-wrap {
            width: min(520px, calc(100vw - 36px));
            max-width: calc(100vw - 36px);
        }
    }
    @media (max-width: 620px) {
        body.login-page .login-shell {
            padding: 30px 16px 38px;
            background: var(--cb-white);
        }
        body.login-page .login-form-wrap {
            max-width: calc(100vw - 32px);
        }
        body.login-page .login-form-head h2 {
            font-size: 28px;
        }
        body.login-page .login-form-head p {
            font-size: 14px;
        }
        body.login-page .login-input-wrap {
            min-height: 56px;
        }
        body.login-page .login-input-wrap input {
            height: 54px;
        }
        body.login-page .password-toggle {
            width: 40px;
            height: 54px;
        }
        body.login-page .login-options {
            align-items: center;
            flex-flow: row wrap;
            gap: 10px 14px;
            justify-content: space-between;
        }
        body.login-page .login-options a {
            margin-left: auto;
        }
        body.login-page .login-submit {
            min-height: 56px;
        }
    }
    @media (max-width: 900px), (max-aspect-ratio: 4 / 5) {
        body.login-page .login-shell {
            width: 100vw;
            max-width: 100vw;
            grid-template-columns: 1fr;
            overflow: hidden;
        }
        body.login-page .login-intro,
        body.login-page .login-intro::before,
        body.login-page .login-intro::after {
            display: none !important;
        }
        body.login-page .login-panel {
            width: 100vw;
            max-width: 100vw;
            min-width: 0;
            padding: clamp(28px, 5vh, 48px) 18px;
            overflow: hidden;
        }
    }

    .home-hero {
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        overflow: hidden;
    }
    .home-hero__left {
        overflow: visible;
        z-index: 1;
    }
    .home-hero__left::after {
        right: -20px;
        width: clamp(40px, 2.7vw, 48px);
        height: 100%;
        background: var(--cb-red);
        transform: skewX(-11deg);
        transform-origin: top center;
        z-index: 2;
    }
    .home-hero__content {
        max-width: 640px;
        padding-right: clamp(40px, 6vw, 96px);
    }
    .home-hero__right {
        position: relative;
        z-index: 0;
        width: 100%;
        min-width: 0;
    }
    .home-hero__right-inner {
        width: min(700px, calc(100% - 96px));
        max-width: 700px;
    }
    @media (max-width: 900px) {
        .home-hero {
            min-height: 0;
            width: 100vw;
            max-width: 100vw;
            grid-template-columns: 1fr;
            overflow: hidden;
        }
        .home-hero__left,
        .home-hero__right {
            width: 100vw;
            max-width: 100vw;
            min-width: 0;
            overflow: hidden;
        }
        .home-hero__left {
            min-height: 0;
            overflow: hidden;
            padding: clamp(48px, 8vw, 72px) 0;
        }
        .home-hero__left::after {
            display: none;
        }
        .home-hero__content,
        .home-hero__right-inner {
            width: calc(100vw - 40px);
            max-width: min(680px, calc(100vw - 40px));
            min-width: 0;
            padding-right: 0;
            overflow: hidden;
        }
        .home-hero h1 {
            font-size: clamp(32px, 7vw, 46px);
            overflow-wrap: anywhere;
        }
        .home-hero__subtitle {
            font-size: clamp(22px, 5vw, 32px);
            overflow-wrap: anywhere;
        }
        .home-hero__text,
        .home-hero__right h2 {
            overflow-wrap: anywhere;
        }
        .home-hero__right {
            padding: clamp(40px, 7vw, 60px) 0;
        }
        .home-visual img {
            width: 100%;
            max-width: 100%;
            height: auto;
        }
    }
    @media (max-width: 600px) {
        .home-hero__left {
            padding: 44px 0;
        }
        .home-hero__content,
        .home-hero__right-inner {
            width: calc(100vw - 32px);
            max-width: calc(100vw - 32px);
        }
        .home-hero h1 {
            font-size: clamp(30px, 9vw, 38px);
        }
        .home-hero__subtitle {
            font-size: clamp(20px, 5.6vw, 26px);
        }
        .home-hero__right h2 {
            font-size: clamp(28px, 8vw, 36px);
        }
        .home-hero__actions {
            flex-direction: column;
            align-items: stretch;
            gap: 14px;
        }
        .home-hero__actions .public-button,
        .home-hero__actions .public-text-link {
            width: 100%;
            justify-content: center;
        }
        .home-visual img {
            width: 100%;
            max-width: 100%;
            height: auto;
            aspect-ratio: auto;
        }
    }

    body.login-page .login-shell {
        min-height: calc(100dvh - 136px);
        width: 100%;
        max-width: 100%;
        display: grid;
        grid-template-columns: minmax(0, 51%) minmax(0, 49%);
        align-items: stretch;
        overflow: hidden;
        background: #FFFFFF;
    }
    body.login-page .login-intro {
        --login-diagonal-run: clamp(118px, 7.8vw, 152px);
        --login-red-band: 10px;
        position: relative;
        min-width: 0;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        padding: clamp(34px, 4vh, 48px) 0 clamp(34px, 4vh, 48px) clamp(72px, 7vw, 132px);
        overflow: hidden;
        isolation: isolate;
        color: #FFFFFF;
        background: transparent;
        border: 0;
        border-radius: 0;
    }
    body.login-page .login-intro::before {
        content: '';
        position: absolute;
        inset: 0;
        z-index: 0;
        background: #082F59;
        clip-path: polygon(
            0 0,
            calc(100% - var(--login-red-band)) 0,
            calc(100% - var(--login-diagonal-run) - var(--login-red-band)) 100%,
            0 100%
        );
    }
    body.login-page .login-intro::after {
        content: '';
        position: absolute;
        inset: 0;
        z-index: 2;
        width: auto;
        height: auto;
        background: #ED1C2E;
        transform: none;
        transform-origin: initial;
        clip-path: polygon(
            calc(100% - 10px) 0,
            100% 0,
            calc(100% - var(--login-diagonal-run)) 100%,
            calc(100% - var(--login-diagonal-run) - 10px) 100%
        );
        pointer-events: none;
    }
    body.login-page .login-intro__content {
        position: relative;
        z-index: 3;
        width: min(620px, calc(100% - clamp(138px, 12vw, 220px)));
        max-width: 620px;
        margin: 0;
        padding: 0 clamp(28px, 3.5vw, 64px) 0 0;
    }
    body.login-page .login-intro .public-red-line,
    body.login-page .login-form-head .public-red-line {
        width: clamp(58px, 4vw, 76px);
        height: 5px;
        margin-bottom: clamp(18px, 2.2vh, 24px);
        background: #ED1C2E;
    }
    body.login-page .login-form-head .public-red-line {
        width: 58px;
        height: 4px;
        margin: 8px 0 clamp(14px, 1.8vh, 18px);
    }
    body.login-page .login-kicker {
        margin: 0 0 clamp(18px, 2.2vh, 24px);
        font-size: clamp(12px, .78vw, 15px);
        line-height: 1.2;
        font-weight: 900;
        letter-spacing: .34em;
        text-transform: uppercase;
    }
    body.login-page .login-intro h1 {
        max-width: 10.4em;
        margin: 0 0 clamp(10px, 1.4vh, 14px);
        color: #FFFFFF;
        font-size: clamp(38px, 3.1vw, 52px);
        line-height: 1.12;
        font-weight: 900;
        letter-spacing: 0;
    }
    body.login-page .login-intro__subtitle {
        margin: 0 0 clamp(10px, 1.4vh, 16px);
        color: #FFFFFF;
        font-size: clamp(23px, 1.8vw, 32px);
        line-height: 1.2;
        font-weight: 400;
    }
    body.login-page .login-intro__text {
        max-width: 610px;
        margin: 0;
        color: rgba(255, 255, 255, .94);
        font-size: clamp(16px, 1.08vw, 19px);
        line-height: 1.5;
    }
    body.login-page .public-wing--login {
        left: clamp(-42px, -1.8vw, -18px);
        bottom: clamp(18px, 3vh, 34px);
        width: min(430px, 40vw);
        opacity: .04;
        filter: grayscale(1) brightness(2.7);
        z-index: 1;
    }
    body.login-page .login-panel {
        width: 100%;
        min-width: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: clamp(30px, 4vh, 48px) clamp(54px, 5vw, 94px) clamp(30px, 4vh, 48px) clamp(66px, 5vw, 98px);
        overflow: hidden;
        background: #FFFFFF;
        border: 0;
        border-radius: 0;
    }
    body.login-page .login-form-wrap {
        width: 100%;
        max-width: 610px;
        margin: 0;
    }
    body.login-page .login-form-head,
    body.login-page .login-form,
    body.login-page .register-row,
    body.login-page .secure-row {
        width: 100%;
        max-width: 610px;
    }
    body.login-page .login-form-head .login-kicker {
        margin-bottom: clamp(12px, 1.5vh, 16px);
        color: #082F59;
    }
    body.login-page .login-form-head h2 {
        margin: 0;
        color: #082F59;
        font-size: clamp(38px, 3vw, 52px);
        line-height: 1.1;
        font-weight: 900;
        letter-spacing: 0;
    }
    body.login-page .login-form-head p {
        margin: 0 0 clamp(22px, 2.8vh, 32px);
        color: var(--cb-muted);
        font-size: clamp(16px, 1.12vw, 20px);
    }
    body.login-page .login-form {
        display: grid;
        gap: clamp(16px, 1.9vh, 22px);
    }
    body.login-page .login-field label {
        margin-bottom: 8px;
        color: #082F59;
        font-size: clamp(14px, .94vw, 16px);
        font-weight: 800;
    }
    body.login-page .login-input-wrap {
        min-height: 56px;
        grid-template-columns: 34px 1fr auto;
        gap: 14px;
        padding: 0 18px;
        border: 1px solid #B8C6D8;
        border-radius: 6px;
        background: #FFFFFF;
    }
    body.login-page .login-input-wrap:focus-within {
        border-color: #1473D1;
        box-shadow: 0 0 0 3px rgba(20, 115, 209, .22);
    }
    body.login-page .login-input-wrap input {
        height: 56px;
        font-size: clamp(16px, 1vw, 18px);
    }
    body.login-page .password-toggle {
        width: 44px;
        height: 54px;
        border-left: 1px solid #D8E1EC;
        border-radius: 0;
    }
    body.login-page .login-options {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-top: -2px;
    }
    body.login-page .remember-check {
        font-size: clamp(14px, .94vw, 16px);
    }
    body.login-page .login-submit {
        width: 100%;
        min-height: 56px;
        margin-top: 0;
        border-color: #ED1C2E;
        border-radius: 6px;
        background: #ED1C2E;
        font-size: clamp(17px, 1.15vw, 20px);
    }
    body.login-page .login-submit:hover,
    body.login-page .login-submit:focus-visible {
        border-color: #C91525;
        background: #C91525;
    }
    body.login-page .register-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 4px;
        margin-top: clamp(18px, 2.4vh, 28px);
        color: #082F59;
        font-size: clamp(14px, .98vw, 16px);
    }
    body.login-page .secure-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: clamp(16px, 2.2vh, 24px);
        color: #637083;
        font-size: clamp(13px, .9vw, 15px);
    }
    body.login-page .secure-row svg {
        width: 17px;
        height: 17px;
        flex: 0 0 auto;
    }
    @media (max-height: 780px) and (min-width: 901px) {
        body.login-page .login-intro {
            padding-top: clamp(28px, 3.4vh, 40px);
            padding-bottom: clamp(28px, 3.4vh, 40px);
        }
        body.login-page .login-intro__content {
            width: min(620px, calc(100% - clamp(118px, 11vw, 190px)));
        }
        body.login-page .login-intro h1,
        body.login-page .login-form-head h2 {
            font-size: clamp(32px, 2.7vw, 44px);
        }
        body.login-page .login-intro__subtitle {
            font-size: clamp(20px, 1.6vw, 27px);
        }
        body.login-page .login-panel {
            padding-top: clamp(28px, 3.6vh, 42px);
            padding-bottom: clamp(20px, 3vh, 32px);
        }
        body.login-page .login-form {
            gap: 14px;
        }
        body.login-page .login-form-head p {
            margin-bottom: 18px;
        }
        body.login-page .field-error {
            min-height: 16px;
            margin-top: 4px;
        }
        body.login-page .register-row {
            margin-top: 14px;
        }
        body.login-page .secure-row {
            margin-top: 14px;
        }
    }
    @media (max-width: 900px) {
        body.login-page .login-shell {
            width: 100vw;
            max-width: 100vw;
            min-height: calc(100dvh - 126px);
            grid-template-columns: 1fr;
            overflow: hidden;
            background: #FFFFFF;
        }
        body.login-page .login-intro,
        body.login-page .login-intro::before,
        body.login-page .login-intro::after {
            display: none !important;
        }
        body.login-page .login-panel {
            width: 100vw;
            max-width: 100vw;
            padding: clamp(28px, 5vh, 48px) 18px;
            overflow: hidden;
        }
        body.login-page .login-form-wrap {
            max-width: min(610px, calc(100vw - 36px));
        }
    }
    @media (max-width: 600px) {
        body.login-page .login-panel {
            padding: 30px 16px 38px;
        }
        body.login-page .login-form-wrap {
            max-width: calc(100vw - 32px);
        }
        body.login-page .login-form-head h2 {
            font-size: clamp(28px, 8vw, 34px);
        }
        body.login-page .login-form-head p {
            font-size: 14px;
        }
        body.login-page .login-options {
            flex-flow: row wrap;
            gap: 10px 14px;
        }
        body.login-page .login-options a {
            margin-left: auto;
        }
    }

    .home-hero {
        --home-diagonal-run: clamp(92px, 6.4vw, 126px);
        --home-red-band: 10px;
        min-height: calc(100dvh - 136px);
        display: grid;
        grid-template-columns: minmax(0, 44%) minmax(0, 56%);
        align-items: stretch;
        overflow: hidden;
        background: #FFFFFF;
        border-bottom: 1px solid var(--cb-border);
    }
    .home-hero__left {
        position: relative;
        min-width: 0;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        padding: clamp(44px, 6vh, 72px) 0 clamp(44px, 6vh, 72px) clamp(54px, 4vw, 76px);
        overflow: visible;
        isolation: isolate;
        color: #FFFFFF;
        background: transparent;
        z-index: 2;
    }
    .home-hero__left::before {
        content: '';
        position: absolute;
        inset: 0;
        z-index: 0;
        background: #082F59;
        clip-path: polygon(
            0 0,
            calc(100% - var(--home-red-band)) 0,
            calc(100% - var(--home-diagonal-run) - var(--home-red-band)) 100%,
            0 100%
        );
    }
    .home-hero__left::after {
        content: '';
        position: absolute;
        inset: 0;
        width: auto;
        height: auto;
        z-index: 2;
        background: #ED1C2E;
        transform: none;
        transform-origin: initial;
        clip-path: polygon(
            calc(100% - 10px) 0,
            100% 0,
            calc(100% - var(--home-diagonal-run)) 100%,
            calc(100% - var(--home-diagonal-run) - 10px) 100%
        );
        pointer-events: none;
    }
    .home-hero__content {
        position: relative;
        z-index: 3;
        width: min(640px, calc(100% - clamp(112px, 12vw, 190px)));
        max-width: 640px;
        margin: 0;
        padding: 0 clamp(24px, 3vw, 52px) 0 0;
    }
    .home-hero .public-red-line {
        width: clamp(58px, 4vw, 72px);
        height: 5px;
        margin-bottom: clamp(18px, 2.3vh, 24px);
        background: #ED1C2E;
    }
    .home-hero__kicker {
        margin: 0 0 clamp(12px, 1.7vh, 18px);
        color: #FFFFFF;
        font-size: clamp(13px, .85vw, 16px);
        line-height: 1.2;
        font-weight: 900;
        letter-spacing: .22em;
        text-transform: uppercase;
    }
    .home-hero h1 {
        max-width: 11.5em;
        margin: 0 0 clamp(12px, 1.8vh, 18px);
        color: #FFFFFF;
        font-size: clamp(38px, 3.1vw, 56px);
        line-height: 1.08;
        font-weight: 900;
        letter-spacing: 0;
    }
    .home-hero__subtitle {
        display: none;
    }
    .home-hero__text {
        max-width: 620px;
        margin: 0;
        color: rgba(255, 255, 255, .94);
        font-size: clamp(16px, 1.08vw, 20px);
        line-height: 1.45;
    }
    .home-hero__actions {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        gap: clamp(12px, 1.4vw, 18px);
        margin-top: clamp(24px, 3.2vh, 34px);
    }
    .home-hero__actions .public-button {
        min-width: clamp(220px, 13vw, 274px);
        min-height: 56px;
        justify-content: center;
        border-radius: 4px;
        font-size: clamp(15px, .92vw, 17px);
        white-space: nowrap;
        font-weight: 800;
    }
    .public-button--red {
        color: #FFFFFF;
        border-color: #ED1C2E;
        background: #ED1C2E;
    }
    .public-button--red:hover,
    .public-button--red:focus-visible {
        color: #FFFFFF;
        border-color: #C91525;
        background: #C91525;
    }
    .public-button--outline-light {
        color: #FFFFFF;
        border-color: rgba(255, 255, 255, .9);
        background: transparent;
    }
    .public-button--outline-light:hover,
    .public-button--outline-light:focus-visible {
        color: #082F59;
        border-color: #FFFFFF;
        background: #FFFFFF;
    }
    .home-hero__note {
        margin: clamp(16px, 2.1vh, 22px) 0 0;
        color: rgba(255, 255, 255, .9);
        font-size: clamp(14px, .9vw, 16px);
        line-height: 1.5;
    }
    .public-wing--home {
        left: clamp(-32px, -1.6vw, -14px);
        bottom: clamp(18px, 3vh, 34px);
        width: min(430px, 37vw);
        opacity: .035;
        filter: grayscale(1) brightness(2.6);
        z-index: 1;
    }
    .home-hero__right {
        position: relative;
        z-index: 1;
        min-width: 0;
        margin-left: calc(-1 * var(--home-diagonal-run));
        display: block;
        overflow: hidden;
        background: #FFFFFF;
    }
    .home-visual {
        position: relative;
        width: calc(100% + var(--home-diagonal-run));
        height: 100%;
        margin: 0;
    }
    .home-visual img {
        display: block;
        width: 100%;
        height: 100%;
        min-height: inherit;
        object-fit: cover;
        object-position: center;
        border-radius: 0;
    }
    .home-visual figcaption {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        min-height: 52px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin: 0;
        padding: 12px clamp(28px, 3.2vw, 58px);
        color: #FFFFFF;
        background: #082F59;
        font-size: clamp(14px, 1vw, 18px);
        line-height: 1.35;
    }
    .home-visual figcaption strong {
        font-weight: 800;
    }
    .home-visual figcaption span {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
    }
    .home-visual figcaption i {
        width: 14px;
        height: 14px;
        border-radius: 999px;
        background: #ED1C2E;
        flex: 0 0 auto;
    }
    .home-services {
        position: relative;
        display: block;
        width: 100%;
        min-height: 0;
        margin: 0;
        padding: clamp(34px, 5vh, 54px) clamp(32px, 4vw, 76px) clamp(38px, 5.4vh, 62px);
        background: #FFFFFF;
        border-bottom: 1px solid var(--cb-border);
    }
    .home-anchor {
        position: absolute;
        top: 0;
    }
    .home-services__inner {
        width: min(1680px, 100%);
        margin: 0 auto;
    }
    .home-services__head {
        margin-bottom: clamp(18px, 2.6vh, 28px);
    }
    .home-services__head .public-red-line {
        display: inline-block;
        width: 42px;
        height: 5px;
        margin: 0 14px 4px 0;
        vertical-align: middle;
        background: #ED1C2E;
    }
    .home-services__head .public-kicker {
        display: inline-block;
        margin: 0;
        color: #52647B;
        vertical-align: middle;
        font-size: clamp(12px, .8vw, 15px);
        font-weight: 900;
        letter-spacing: .18em;
        text-transform: uppercase;
    }
    .home-services__head h2 {
        margin: clamp(8px, 1.2vh, 12px) 0 0;
        color: #082F59;
        font-size: clamp(30px, 2.6vw, 44px);
        line-height: 1.08;
        font-weight: 900;
        letter-spacing: 0;
    }
    .home-services__list {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        align-items: stretch;
    }
    .home-service {
        min-width: 0;
        display: grid;
        grid-template-columns: clamp(44px, 4vw, 62px) minmax(0, 1fr) 24px;
        align-items: center;
        gap: clamp(18px, 2.1vw, 30px);
        padding: clamp(20px, 2.7vh, 30px) clamp(26px, 3.6vw, 54px);
        color: inherit;
        border-right: 1px solid #D6E0EC;
        text-decoration: none;
        background: transparent;
        box-shadow: none;
    }
    .home-service:first-child {
        padding-left: 0;
    }
    .home-service:last-child {
        padding-right: 0;
        border-right: 0;
    }
    .home-service__icon {
        width: clamp(44px, 4vw, 62px);
        height: clamp(44px, 4vw, 62px);
        color: #082F59;
    }
    .home-service__icon svg {
        width: 100%;
        height: 100%;
    }
    .home-service__copy {
        min-width: 0;
    }
    .home-service strong {
        display: block;
        color: #082F59;
        font-size: clamp(16px, 1.08vw, 20px);
        line-height: 1.22;
        font-weight: 900;
    }
    .home-service small {
        display: block;
        margin-top: 6px;
        color: #4F6075;
        font-size: clamp(13px, .92vw, 16px);
        line-height: 1.35;
    }
    .home-service__arrow {
        color: #082F59;
        font-size: clamp(34px, 2.5vw, 42px);
        line-height: 1;
        font-weight: 500;
        justify-self: end;
    }
    .home-service:hover strong,
    .home-service:focus-visible strong,
    .home-service:hover .home-service__arrow,
    .home-service:focus-visible .home-service__arrow {
        color: #ED1C2E;
    }
    .home-services__note {
        margin: clamp(22px, 3.4vh, 34px) 0 0;
        color: #3D5572;
        font-size: clamp(14px, .98vw, 17px);
        line-height: 1.5;
        text-align: center;
    }
    .home-about {
        position: relative;
        padding: clamp(46px, 6vh, 76px) clamp(32px, 4vw, 76px);
        background: #F5F8FC;
        border-bottom: 1px solid var(--cb-border);
        scroll-margin-top: 92px;
    }
    .home-about__inner {
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(320px, .95fr);
        gap: clamp(32px, 4vw, 70px);
        align-items: center;
        width: min(1680px, 100%);
        margin: 0 auto;
        padding: 0;
        text-align: left;
    }
    .home-about__copy {
        max-width: 760px;
    }
    .home-about .public-red-line {
        display: inline-block;
        width: 42px;
        height: 5px;
        margin: 0 14px 4px 0;
        vertical-align: middle;
        background: #ED1C2E;
    }
    .home-about .public-kicker {
        display: inline-block;
        margin: 0;
        color: #52647B;
        vertical-align: middle;
        font-size: clamp(12px, .8vw, 15px);
        font-weight: 900;
        letter-spacing: .18em;
        text-transform: uppercase;
    }
    .home-about h2 {
        margin: clamp(10px, 1.4vh, 14px) 0 0;
        color: #082F59;
        font-size: clamp(30px, 2.5vw, 44px);
        line-height: 1.1;
        font-weight: 900;
        letter-spacing: 0;
    }
    .home-about__copy p {
        margin: 16px 0 0;
        color: #314864;
        font-size: clamp(15px, 1vw, 18px);
        line-height: 1.65;
        font-weight: 650;
    }
    .home-about__copy .public-button {
        margin-top: 26px;
    }
    .home-about__points {
        display: grid;
        gap: 14px;
    }
    .home-about__point {
        position: relative;
        min-height: 112px;
        padding: 20px 22px 20px 28px;
        border: 1px solid #D7E2EF;
        border-left: 5px solid #082F59;
        border-radius: 8px;
        background: #FFFFFF;
        box-shadow: 0 8px 20px rgba(8, 47, 89, .045);
    }
    .home-about__point:nth-child(2) {
        border-left-color: #0B5ED7;
    }
    .home-about__point:nth-child(3) {
        border-left-color: #ED1C2E;
    }
    .home-about__point strong {
        display: block;
        color: #082F59;
        font-size: clamp(17px, 1.15vw, 21px);
        line-height: 1.2;
        font-weight: 900;
    }
    .home-about__point span {
        display: block;
        margin-top: 8px;
        color: #4F6075;
        font-size: clamp(13px, .92vw, 16px);
        line-height: 1.45;
        font-weight: 650;
    }
    .home-contact {
        padding: clamp(46px, 6vh, 76px) clamp(32px, 4vw, 76px);
        background: #FFFFFF;
        border-bottom: 1px solid var(--cb-border);
        scroll-margin-top: 92px;
    }
    .home-contact__inner {
        width: min(1680px, 100%);
        margin: 0 auto;
    }
    .home-contact__head {
        max-width: 780px;
        margin-bottom: clamp(22px, 3vh, 34px);
    }
    .home-contact .public-red-line {
        display: inline-block;
        width: 42px;
        height: 5px;
        margin: 0 14px 4px 0;
        vertical-align: middle;
        background: #ED1C2E;
    }
    .home-contact .public-kicker {
        display: inline-block;
        margin: 0;
        color: #52647B;
        vertical-align: middle;
        font-size: clamp(12px, .8vw, 15px);
        font-weight: 900;
        letter-spacing: .18em;
        text-transform: uppercase;
    }
    .home-contact h2 {
        margin: clamp(10px, 1.4vh, 14px) 0 0;
        color: #082F59;
        font-size: clamp(30px, 2.5vw, 44px);
        line-height: 1.1;
        font-weight: 900;
        letter-spacing: 0;
    }
    .home-contact__head > p:last-child {
        margin: 12px 0 0;
        color: #314864;
        font-size: clamp(15px, 1vw, 18px);
        line-height: 1.55;
        font-weight: 650;
    }
    .home-contact__list {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }
    .home-contact__card {
        display: grid;
        gap: 8px;
        min-height: 190px;
        padding: 22px;
        border: 1px solid #D7E2EF;
        border-top: 5px solid #082F59;
        border-radius: 8px;
        background: #F8FBFF;
        box-shadow: 0 8px 20px rgba(8, 47, 89, .045);
    }
    .home-contact__card:nth-child(2) {
        border-top-color: #0B5ED7;
    }
    .home-contact__card:nth-child(3) {
        border-top-color: #ED1C2E;
    }
    .home-contact__role {
        color: #0B5ED7;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .home-contact__card strong {
        color: #082F59;
        font-size: clamp(17px, 1.15vw, 21px);
        line-height: 1.25;
        font-weight: 900;
    }
    .home-contact__card a {
        width: fit-content;
        max-width: 100%;
        color: #314864;
        font-size: clamp(13px, .92vw, 16px);
        line-height: 1.35;
        font-weight: 750;
        overflow-wrap: anywhere;
        text-decoration: none;
    }
    .home-contact__card a:hover,
    .home-contact__card a:focus-visible {
        color: #ED1C2E;
        text-decoration: underline;
        text-underline-offset: 3px;
    }
    @media (max-height: 780px) and (min-width: 901px) {
        .home-hero {
            min-height: clamp(470px, 58dvh, 560px);
        }
        .home-hero__left {
            padding-top: clamp(30px, 4vh, 44px);
            padding-bottom: clamp(30px, 4vh, 44px);
        }
        .home-hero__content {
            width: min(610px, calc(100% - clamp(104px, 11vw, 170px)));
        }
        .home-hero h1 {
            font-size: clamp(34px, 2.7vw, 46px);
        }
        .home-hero__text {
            font-size: clamp(15px, 1vw, 18px);
        }
        .home-hero__actions {
            margin-top: 22px;
        }
        .home-hero__actions .public-button {
            min-height: 50px;
            min-width: clamp(202px, 13vw, 240px);
        }
        .home-hero__note {
            margin-top: 14px;
        }
        .home-services {
            padding-top: 28px;
            padding-bottom: 32px;
        }
        .home-services__head {
            margin-bottom: 14px;
        }
        .home-service {
            padding-top: 16px;
            padding-bottom: 18px;
        }
        .home-services__note {
            margin-top: 18px;
        }
    }
    @media (max-width: 1200px) {
        .home-services__list {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .home-contact__list {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .home-service:nth-child(2n) {
            padding-right: 0;
            border-right: 0;
        }
        .home-service:nth-child(2n + 1) {
            padding-left: 0;
        }
    }
    @media (max-width: 900px) {
        .home-hero {
            width: 100%;
            max-width: 100%;
            min-height: 0;
            grid-template-columns: 1fr;
            overflow: hidden;
        }
        .home-hero__left {
            width: 100%;
            max-width: 100%;
            min-height: auto;
            padding: clamp(50px, 8vw, 78px) 20px;
            overflow: hidden;
        }
        .home-hero__left::before {
            clip-path: none;
        }
        .home-hero__left::after {
            display: none;
        }
        .home-hero__content {
            width: min(640px, 100%);
            max-width: 640px;
            padding-right: 0;
        }
        .home-hero h1 {
            font-size: clamp(34px, 7vw, 48px);
        }
        .home-hero__right {
            width: 100%;
            max-width: 100%;
            margin-left: 0;
            overflow: hidden;
        }
        .home-visual {
            width: 100%;
            height: auto;
        }
        .home-visual img {
            width: 100%;
            height: auto;
            aspect-ratio: 16 / 9;
        }
        .home-visual figcaption {
            position: static;
            flex-wrap: wrap;
            padding: 12px 20px;
        }
        .home-services {
            padding: 34px 20px 42px;
            overflow: hidden;
        }
        .home-about {
            padding: 42px 20px;
        }
        .home-contact {
            padding: 42px 20px;
        }
        .home-about__inner {
            grid-template-columns: 1fr;
            gap: 28px;
        }
        .home-contact__list {
            grid-template-columns: 1fr;
        }
        .home-services__list {
            grid-template-columns: 1fr;
        }
        .home-service,
        .home-service:first-child,
        .home-service:last-child {
            grid-template-columns: 48px minmax(0, 1fr) 22px;
            padding: 22px 0;
            border-right: 0;
            border-bottom: 1px solid #D6E0EC;
        }
        .home-service:last-child {
            border-bottom: 0;
        }
    }
    @media (max-width: 600px) {
        .home-hero__left {
            padding: 44px 16px;
        }
        .home-hero__actions {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }
        .home-hero__actions .public-button {
            width: 100%;
            min-width: 0;
        }
        .home-visual img {
            width: 100%;
            height: auto;
        }
        .home-visual figcaption {
            align-items: flex-start;
            flex-direction: column;
            gap: 8px;
        }
        .home-services {
            padding-inline: 16px;
        }
        .home-about {
            padding-inline: 16px;
        }
        .home-contact {
            padding-inline: 16px;
        }
        .home-services__head h2 {
            font-size: clamp(28px, 8vw, 36px);
        }
        .home-about h2 {
            font-size: clamp(28px, 8vw, 36px);
        }
        .home-contact h2 {
            font-size: clamp(28px, 8vw, 36px);
        }
        .home-about__copy .public-button {
            width: 100%;
        }
    }

    /* Final homepage composition: stable image panel, diagonal overlap only at the split. */
    body:not(.login-page) .home-hero {
        --home-diagonal-run: clamp(84px, 5.8vw, 112px);
        --home-red-band: 10px;
        position: relative;
        min-height: clamp(500px, 58dvh, 610px);
        display: block;
        overflow: hidden;
        background: #FFFFFF;
    }
    body:not(.login-page) .home-hero__left {
        position: relative;
        z-index: 2;
        width: 44%;
        height: 100%;
        min-height: inherit;
        display: flex;
        align-items: center;
        padding: clamp(38px, 5vh, 60px) 0 clamp(38px, 5vh, 60px) clamp(54px, 4vw, 76px);
        overflow: visible;
        background: transparent;
    }
    body:not(.login-page) .home-hero__left::before {
        content: '';
        position: absolute;
        inset: 0;
        z-index: 0;
        background: #082F59;
        clip-path: polygon(
            0 0,
            calc(100% - var(--home-red-band)) 0,
            calc(100% - var(--home-diagonal-run) - var(--home-red-band)) 100%,
            0 100%
        );
    }
    body:not(.login-page) .home-hero__left::after {
        content: '';
        position: absolute;
        inset: 0;
        z-index: 2;
        width: auto;
        height: auto;
        background: #ED1C2E;
        transform: none;
        clip-path: polygon(
            calc(100% - 10px) 0,
            100% 0,
            calc(100% - var(--home-diagonal-run)) 100%,
            calc(100% - var(--home-diagonal-run) - 10px) 100%
        );
        pointer-events: none;
    }
    body:not(.login-page) .home-hero__content {
        width: min(610px, calc(100% - clamp(96px, 10vw, 164px)));
        max-width: 610px;
        margin: 0;
        padding-right: clamp(20px, 3vw, 46px);
    }
    body:not(.login-page) .home-hero__right {
        position: absolute;
        z-index: 1;
        top: 0;
        right: 0;
        bottom: 0;
        left: calc(44% - var(--home-diagonal-run));
        width: auto;
        max-width: none;
        margin-left: 0;
        overflow: hidden;
    }
    body:not(.login-page) .home-visual {
        width: 100%;
        height: 100%;
    }
    body:not(.login-page) .home-visual img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }
    body:not(.login-page) .home-visual figcaption {
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        min-width: 0;
        padding-left: calc(var(--home-diagonal-run) + clamp(22px, 2.4vw, 44px));
        padding-right: clamp(22px, 3vw, 56px);
        overflow: hidden;
    }
    body:not(.login-page) .home-visual figcaption span {
        min-width: 0;
    }
    @media (max-height: 780px) and (min-width: 901px) {
        body:not(.login-page) .home-hero {
            min-height: clamp(470px, 58dvh, 550px);
        }
        body:not(.login-page) .home-hero h1 {
            font-size: clamp(34px, 2.6vw, 44px);
        }
        body:not(.login-page) .home-hero__actions .public-button {
            min-width: clamp(206px, 13vw, 244px);
        }
    }
    @media (max-width: 900px) {
        body:not(.login-page) .home-hero {
            display: grid;
            grid-template-columns: 1fr;
            min-height: 0;
        }
        body:not(.login-page) .home-hero__left,
        body:not(.login-page) .home-hero__right {
            position: relative;
            inset: auto;
            width: 100%;
            max-width: 100%;
            min-height: auto;
        }
        body:not(.login-page) .home-hero__left {
            padding: clamp(50px, 8vw, 78px) 20px;
            overflow: hidden;
        }
        body:not(.login-page) .home-hero__left::before {
            clip-path: none;
        }
        body:not(.login-page) .home-hero__left::after {
            display: none;
        }
        body:not(.login-page) .home-hero__content {
            width: min(640px, 100%);
            padding-right: 0;
        }
        body:not(.login-page) .home-visual,
        body:not(.login-page) .home-visual img {
            height: auto;
        }
        body:not(.login-page) .home-visual img {
            aspect-ratio: 16 / 9;
        }
        body:not(.login-page) .home-visual figcaption {
            position: static;
            padding: 12px 20px;
        }
    }
    @media (max-width: 600px) {
        body:not(.login-page) .home-hero__left {
            padding: 44px 16px;
        }
    }

    @media (max-width: 900px) {
        body.login-page {
            min-height: 100dvh;
            background: #FFFFFF;
        }

        body.login-page .public-header,
        body.login-page .public-nav {
            min-height: 72px;
        }

        body.login-page .public-nav {
            width: 100%;
            padding: 0 18px;
        }

        body.login-page .public-brand {
            gap: 12px;
        }

        body.login-page .public-brand__logo {
            width: 54px;
            height: 48px;
        }

        body.login-page .public-brand__divider {
            height: 42px;
        }

        body.login-page .public-brand__name {
            font-size: 24px;
        }

        body.login-page .public-brand__sub {
            font-size: 12px;
            line-height: 1.15;
        }

        body.login-page .login-shell {
            flex: 1 0 auto;
            width: 100%;
            max-width: none;
            min-height: auto;
            padding: 0;
            display: grid;
            grid-template-columns: 1fr;
            overflow: visible;
            background: #FFFFFF;
        }

        body.login-page .login-intro {
            --login-diagonal-run: 62px;
            --login-red-band: 7px;
            width: 100%;
            min-height: 240px;
            display: flex !important;
            align-items: center;
            padding: 34px 20px 38px;
            overflow: hidden;
            background: transparent;
        }

        body.login-page .login-intro {
            background: #082F59;
        }

        body.login-page .login-intro::before,
        body.login-page .login-intro::after {
            display: block !important;
        }

        body.login-page .login-intro::before {
            clip-path: none;
        }

        body.login-page .login-intro::after {
            display: none !important;
        }

        body.login-page .login-intro__content {
            width: min(520px, calc(100% - 70px));
            max-width: 520px;
            margin: 0;
            padding: 0 18px 0 0;
        }

        body.login-page .login-intro .public-red-line {
            width: 58px;
            height: 5px;
            margin-bottom: 14px;
        }

        body.login-page .login-intro .login-kicker {
            margin-bottom: 12px;
            font-size: 12px;
            letter-spacing: .22em;
        }

        body.login-page .login-intro h1 {
            max-width: 9.8em;
            margin-bottom: 8px;
            font-size: clamp(30px, 8vw, 40px);
            line-height: 1.12;
        }

        body.login-page .login-intro__subtitle {
            margin-bottom: 8px;
            font-size: clamp(20px, 5.8vw, 28px);
        }

        body.login-page .login-intro__text {
            max-width: 430px;
            font-size: 15px;
            line-height: 1.45;
        }

        body.login-page .public-wing--login {
            left: -24px;
            bottom: 14px;
            width: min(310px, 72vw);
        }

        body.login-page .login-panel {
            width: 100%;
            max-width: none;
            min-height: auto;
            align-items: flex-start;
            justify-content: center;
            padding: 28px 18px 34px;
            overflow: visible;
        }

        body.login-page .login-form-wrap {
            width: min(610px, 100%);
            max-width: 100%;
        }

        body.login-page .login-form-head p {
            margin-bottom: 22px;
        }

        body.login-page .public-footer {
            min-height: 44px;
            padding: 10px 16px;
        }
    }

    @media (max-width: 600px) {
        body.login-page .login-intro {
            min-height: 220px;
            padding: 28px 16px 32px;
        }

        body.login-page .login-intro__content {
            width: min(420px, calc(100% - 54px));
            padding-right: 10px;
        }

        body.login-page .login-intro h1 {
            font-size: clamp(28px, 7.5vw, 34px);
        }

        body.login-page .login-intro__subtitle {
            font-size: clamp(18px, 5.3vw, 24px);
        }

        body.login-page .login-intro__text {
            font-size: 14px;
        }

        body.login-page .login-panel {
            padding: 24px 16px 32px;
        }

        body.login-page .login-form {
            gap: 16px;
        }

        body.login-page .login-input-wrap {
            min-height: 52px;
            grid-template-columns: 30px minmax(0, 1fr) auto;
            gap: 12px;
            padding: 0 14px;
        }

        body.login-page .login-input-wrap input,
        body.login-page .login-input-wrap select {
            height: 52px;
            font-size: 15px;
        }

        body.login-page .password-toggle {
            width: 40px;
            height: 50px;
        }

        body.login-page .login-submit {
            min-height: 52px;
            font-size: 17px;
        }

        body.login-page .register-row,
        body.login-page .secure-row {
            margin-top: 14px;
        }
    }
</style>
