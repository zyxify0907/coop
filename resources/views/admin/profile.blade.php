@extends('layouts.app')

@section('title', 'Profil Pentadbir')
@section('page-title', 'Profil Pentadbir')
@section('page-subtitle', 'Maklumat akaun pentadbir dan tetapan kata laluan.')

@section('content')
    @include('components.coop-page-style')

    @php
        $profileInitial = strtoupper(mb_substr($user->nama ?? 'A', 0, 1));
        $shareRecord = $staffProfile?->sahamStaff;
        $memberNumber = $staffProfile?->no_anggota ?? 'Belum dijana';
        $memberStatus = $staffProfile?->no_anggota || $shareRecord ? 'Anggota Koperasi' : 'Belum menjadi anggota';
        $totalShare = (float) optional($shareRecord)->syer + (float) optional($shareRecord)->tambahan_saham;
        $registeredDate = optional($staffProfile?->tarikh_mula)->format('d/m/Y')
            ?? optional($shareRecord?->tarikh_kemaskini)->format('d/m/Y')
            ?? '-';
    @endphp

    <div class="admin-profile-page">
        <section class="profile-hero-card" aria-label="Profil Pentadbir">
            <div class="profile-hero-main">
                <span class="profile-watermark">KOPERASI</span>
                <div class="profile-identity">
                    <span class="profile-avatar" aria-label="{{ $profileInitial }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="7" r="4"/>
                            <path d="M5 21v-2a7 7 0 0 1 14 0v2"/>
                        </svg>
                    </span>
                    <div>
                        <span class="profile-kicker">Profil Pentadbir</span>
                        <h1>{{ $user->nama }}</h1>
                        <p>{{ $user->peranan ?? 'Admin / Pengurus Sistem' }}</p>
                        <span class="profile-status profile-status--inline">{{ $user->status_aktif ? 'Aktif' : 'Tidak Aktif' }}</span>
                    </div>
                </div>
            </div>
            <div class="profile-hero-side">
                <span>STATUS PENGGUNA</span>
                <strong>Admin / {{ $memberStatus }}</strong>
                <small>No. Anggota: {{ $memberNumber }}</small>
            </div>
        </section>

        <section class="profile-summary-grid" aria-label="Ringkasan profil">
            <article class="profile-summary profile-summary--green">
                <span>Status Akaun</span>
                <strong>{{ $user->status_aktif ? 'Aktif' : 'Tidak Aktif' }}</strong>
                <small>Akaun admin semasa.</small>
            </article>
            <article class="profile-summary profile-summary--blue">
                <span>No Anggota Admin</span>
                <strong>{{ $memberNumber }}</strong>
                <small>Nombor keahlian anda.</small>
            </article>
            <article class="profile-summary profile-summary--amber">
                <span>Jumlah Saham</span>
                <strong>RM {{ number_format($totalShare, 2) }}</strong>
                <small>Jumlah saham terkini anda.</small>
            </article>
            <article class="profile-summary profile-summary--navy">
                <span>Tarikh Daftar</span>
                <strong>{{ $registeredDate }}</strong>
                <small>Tarikh anda berdaftar.</small>
            </article>
        </section>

        <section class="profile-layout">
            <div class="profile-main">
                <section class="panel profile-panel">
                    <div class="panel-section-head">
                        <div>
                            <h2>Maklumat Akaun</h2>
                            <p>Maklumat asas akaun admin semasa.</p>
                        </div>
                    </div>
                    <div class="profile-form">
                        <div class="profile-form-grid">
                            <label>
                                <span>Nama Penuh</span>
                                <input value="{{ $user->nama }}" disabled>
                            </label>
                            <label>
                                <span>No. KP</span>
                                <input value="{{ $user->nric ?? '-' }}" disabled>
                            </label>
                            <label>
                                <span>Peranan</span>
                                <input value="{{ $user->peranan ?? 'Admin' }}" disabled>
                            </label>
                        </div>
                    </div>
                </section>

                <section class="panel profile-panel">
                    <div class="panel-section-head">
                        <div>
                            <h2>Ringkasan Saham</h2>
                            <p>Maklumat syer semasa anda.</p>
                        </div>
                    </div>
                    <div class="profile-share-summary">
                        <table class="profile-detail-table">
                            <tr>
                                <th>Syer Asas</th>
                                <td>RM {{ number_format((float) optional($shareRecord)->syer, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Tambahan Saham</th>
                                <td>RM {{ number_format((float) optional($shareRecord)->tambahan_saham, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Yuran Anggota</th>
                                <td>RM {{ number_format((float) optional($shareRecord)->yuran, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Status Anggota</th>
                                <td>{{ $memberStatus }}</td>
                            </tr>
                        </table>
                    </div>
                </section>

                <section class="panel profile-panel" id="kata-laluan">
                    <div class="panel-section-head">
                        <div>
                            <h2>Tukar Kata Laluan</h2>
                            <p>Kemaskini kata laluan akaun untuk keselamatan login.</p>
                        </div>
                    </div>
                    <form class="profile-form" method="POST" action="{{ route('admin.profile.password') }}">
                        @csrf
                        @method('PATCH')

                        <div class="profile-form-grid">
                            <label>
                                <span>Kata Laluan Semasa</span>
                                <input name="current_password" type="password" autocomplete="current-password">
                                @error('current_password')<small>{{ $message }}</small>@enderror
                            </label>
                            <label>
                                <span>Kata Laluan Baharu</span>
                                <input name="password" type="password" autocomplete="new-password">
                                @error('password')<small>{{ $message }}</small>@enderror
                            </label>
                            <label>
                                <span>Sahkan Kata Laluan Baharu</span>
                                <input name="password_confirmation" type="password" autocomplete="new-password">
                            </label>
                        </div>

                        <div class="profile-actions">
                            <button class="button primary" type="submit">Tukar Kata Laluan</button>
                        </div>
                    </form>
                </section>
            </div>

            <aside class="profile-side">
                <section class="panel profile-panel profile-activity-panel">
                    <div class="panel-section-head">
                        <div>
                            <h2>Aktiviti Terkini</h2>
                            <p>Ringkasan tindakan admin dalam sistem.</p>
                        </div>
                    </div>
                    <div class="admin-list">
                        @forelse ($recentActivities as $activity)
                            <div class="admin-list__item">
                                <strong>{{ ucfirst(str_replace('_', ' ', $activity->module)) }}</strong>
                                <span>{{ ucfirst(str_replace('_', ' ', $activity->action)) }} &middot; {{ $activity->created_at?->format('d/m/Y H:i') }}</span>
                                <small>{{ $activity->description ?? 'Aktiviti sistem direkodkan.' }}</small>
                            </div>
                        @empty
                            <div class="admin-empty">Tiada aktiviti terkini.</div>
                        @endforelse
                    </div>
                </section>

                <section class="panel profile-panel">
                    <div class="panel-section-head">
                        <div>
                            <h2>Senarai Login</h2>
                            <p>Rekod sesi log masuk admin.</p>
                        </div>
                    </div>
                    <div class="admin-list admin-list--login">
                        @foreach ($loginEntries as $login)
                            <div class="admin-list__item">
                                <strong>{{ $login['title'] }}</strong>
                                <span>{{ $login['time'] }}</span>
                                <small>{{ $login['detail'] }} &middot; IP: {{ $login['ip'] }}</small>
                            </div>
                        @endforeach
                    </div>
                </section>

            </aside>
        </section>
    </div>
@endsection

@push('styles')
<style>
    .page-heading{display:none}
    .profile-page--enhanced { gap: 20px; }
    .profile-hero {
        min-height: 150px;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
    }
    .profile-identity {
        display: flex;
        align-items: center;
        gap: 18px;
        min-width: 0;
    }
    .profile-avatar {
        width: 62px;
        height: 62px;
        flex: 0 0 auto;
        display: grid;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .14);
        color: #fff;
        font-size: 24px;
        font-weight: 900;
    }
    .profile-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 0 14px;
        border-radius: 999px;
        background: #DCFCE7;
        color: #166534;
        font-size: 13px;
        font-weight: 900;
        white-space: nowrap;
    }
    .profile-layout--single {
        display: block;
    }
    .profile-main {
        display: grid;
        gap: 20px;
    }
    .profile-panel {
        border-radius: 10px !important;
        overflow: hidden;
    }
    .profile-form {
        padding: 22px 24px 24px;
    }
    .profile-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    .profile-form label { display: grid; gap: 7px; }
    .profile-form label span {
        display: block;
        color: var(--muted);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .02em;
        text-transform: uppercase;
    }
    .profile-form input {
        width: 100%;
        min-height: 44px;
        border: 1px solid var(--line);
        border-radius: 8px;
        padding: 0 13px;
        color: var(--text);
        background: #fff;
        font-weight: 700;
    }
    .profile-form input:disabled {
        background: var(--surface-soft);
        color: var(--muted-2);
    }
    .profile-form input:focus {
        border-color: var(--secondary);
        outline: 3px solid rgba(36, 83, 166, .14);
    }
    .profile-form small {
        color: var(--danger);
        font-size: 12px;
        font-weight: 700;
    }
    .profile-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }
    @media (max-width: 720px) {
        .profile-hero { align-items: flex-start; }
        .profile-identity { align-items: flex-start; }
        .profile-form-grid { grid-template-columns: 1fr; }
        .profile-actions .button { width: 100%; }
    }

    .content:has(> .profile-page--enhanced){
        background:#F4F7FB;
    }
    .content > .profile-page--enhanced{
        max-width:none;
    }
    .content .profile-page--enhanced{
        gap:24px;
    }
    .content .profile-hero{
        position:relative;
        min-height:0;
        align-items:center;
        margin:0;
        padding:22px 28px 22px 48px;
        border:1px solid #D7E2EF;
        border-left:0;
        border-radius:12px;
        background:#082F59;
        box-shadow:0 12px 28px rgba(8,47,89,.08);
        color:#fff;
        overflow:hidden;
    }
    .content .profile-hero::before{
        content:"";
        position:absolute;
        top:0;
        bottom:0;
        left:0;
        width:14px;
        background:#062747;
    }
    .content .profile-hero::after{
        content:"";
        position:absolute;
        top:22px;
        bottom:22px;
        left:28px;
        width:4px;
        border-radius:999px;
        background:#ED1C2E;
    }
    .content .profile-identity{
        gap:18px;
        padding-top:22px;
    }
    .content .profile-avatar{
        width:78px;
        height:78px;
        border:0;
        border-radius:999px;
        background:#DCEBFF;
        color:#082F59;
        font-size:28px;
    }
    .content .profile-hero .coop-kicker{
        display:inline-flex;
        min-height:24px;
        align-items:center;
        border-radius:6px;
        background:rgba(234,242,255,.16);
        color:#DCEBFF;
        padding:0 8px;
        font-size:12px;
        font-weight:900;
        letter-spacing:0;
    }
    .content .profile-hero h1{
        margin:6px 0 0;
        color:#fff;
        font-size:clamp(25px,2.2vw,34px);
        line-height:1.1;
        font-weight:900;
        letter-spacing:0;
    }
    .content .profile-hero p{
        margin:8px 0 0;
        color:rgba(255,255,255,.82);
        font-size:clamp(14px,1.2vw,16px);
        font-weight:800;
    }
    .content .profile-status{
        min-height:40px;
        border-radius:10px;
        background:#fff;
        color:#087A45;
        padding:0 18px;
        font-size:14px;
    }
    .content .profile-main{
        gap:24px;
    }
    .content .profile-panel{
        border:1px solid #D7E2EF!important;
        border-radius:12px!important;
        background:#fff!important;
        box-shadow:0 8px 20px rgba(8,47,89,.035)!important;
    }
    .content .panel-section-head{
        padding:24px 28px;
        border-bottom:1px solid #D7E2EF;
        background:#fff;
    }
    .content .panel-section-head h2{
        margin:0;
        color:#082F59;
        font-size:24px;
        line-height:1.2;
        font-weight:900;
    }
    .content .panel-section-head h2::before{
        content:"";
        display:block;
        width:34px;
        height:4px;
        margin-bottom:10px;
        border-radius:999px;
        background:#ED1C2E;
    }
    .content .panel-section-head p{
        margin:6px 0 0;
        color:#31517D;
        font-size:15px;
        font-weight:650;
    }
    .content .profile-form{
        padding:24px 28px 28px;
    }
    .content .profile-form label span{
        color:#082F59;
        font-size:13px;
        font-weight:800;
        letter-spacing:0;
    }
    .content .profile-form input{
        min-height:42px;
        border:1px solid #C8D6E7;
        border-radius:6px;
        color:#102033;
        font-size:14px;
        font-weight:650;
    }
    .content .profile-form input:disabled{
        background:#F8FBFF;
        color:#526987;
    }
    .content .profile-form input:focus{
        border-color:#0B5ED7;
        outline:0;
        box-shadow:0 0 0 3px rgba(11,94,215,.12);
    }
    .content .profile-actions .button{
        min-height:42px;
        border:1px solid #ED1C2E;
        border-radius:8px;
        background:#ED1C2E;
        color:#fff;
        padding:0 20px;
        font-size:14px;
        font-weight:900;
        box-shadow:0 8px 16px rgba(237,28,46,.14);
    }
    @media (max-width:720px){
        .content .profile-hero{
            align-items:flex-start;
            flex-direction:column;
        }
        .content .profile-identity{
            align-items:flex-start;
            padding-top:22px;
        }
    }

    .content:has(.admin-profile-page) {
        background: #F4F7FB;
    }

    .admin-profile-page {
        --profile-navy: #082F59;
        --profile-red: #ED1C2E;
        --profile-blue: #0B5ED7;
        --profile-green: #087A45;
        --profile-border: #D7E2EF;
        --profile-muted: #526987;
        --profile-card: #FFFFFF;
        --profile-amber: #F59E0B;
        width: 100%;
        max-width: 1180px;
        margin: 0 auto;
        display: grid;
        gap: 16px;
        color: var(--profile-navy);
    }

    .admin-profile-page .profile-hero-card {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1.62fr) minmax(280px, .78fr);
        gap: 0;
        min-height: 148px;
        padding: 0;
        border: 1px solid var(--profile-border);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 10px 28px rgba(8, 47, 89, .08);
        overflow: hidden;
    }

    .admin-profile-page .profile-hero-main {
        position: relative;
        display: flex;
        align-items: center;
        min-width: 0;
        padding: 28px 34px;
        border-radius: 12px 0 0 12px;
        background: var(--profile-navy);
        clip-path: polygon(0 0, calc(100% - 52px) 0, 100% 100%, 0 100%);
        overflow: hidden;
        z-index: 2;
    }

    .admin-profile-page .profile-hero-main::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 18% 20%, rgba(255,255,255,.08), transparent 30%),
            linear-gradient(135deg, rgba(255,255,255,.08), transparent 42%);
        pointer-events: none;
    }

    .admin-profile-page .profile-hero-main::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 1;
        background: var(--profile-red);
        clip-path: polygon(calc(100% - 61px) 0, calc(100% - 52px) 0, 100% 100%, calc(100% - 9px) 100%);
        pointer-events: none;
    }

    .admin-profile-page .profile-watermark {
        position: absolute;
        left: 26px;
        bottom: -4px;
        color: rgba(255, 255, 255, .055);
        font-size: 30px;
        font-weight: 900;
        letter-spacing: .02em;
        pointer-events: none;
    }

    .admin-profile-page .profile-hero-main .profile-identity {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 20px;
        min-width: 0;
        padding-top: 0;
    }

    .admin-profile-page .profile-avatar {
        width: 82px;
        height: 82px;
        flex: 0 0 auto;
        display: grid;
        place-items: center;
        border: 3px solid rgba(255,255,255,.34);
        border-radius: 999px;
        background: #DCEBFF;
        color: var(--profile-navy);
        box-shadow: 0 10px 24px rgba(0,0,0,.16);
    }

    .admin-profile-page .profile-avatar svg {
        width: 46px;
        height: 46px;
    }

    .admin-profile-page .profile-kicker {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        margin-bottom: 8px;
        padding: 0 10px;
        border-radius: 6px;
        background: rgba(234, 242, 255, .18);
        color: #DCEBFF;
        font-size: 12px;
        font-weight: 900;
    }

    .admin-profile-page .profile-identity h1 {
        margin: 0;
        color: #fff;
        font-size: clamp(24px, 2vw, 32px);
        line-height: 1.05;
        font-weight: 900;
        letter-spacing: 0;
    }

    .admin-profile-page .profile-identity p {
        margin: 8px 0 0;
        color: rgba(255,255,255,.82);
        font-size: clamp(14px, 1.1vw, 16px);
        font-weight: 800;
    }

    .admin-profile-page .profile-status--inline {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        width: auto;
        min-height: 26px;
        margin-top: 10px;
        padding: 0 12px;
        border-radius: 999px;
        background: #18B877;
        color: #fff;
        font-size: 13px;
        font-weight: 900;
    }

    .admin-profile-page .profile-status--inline::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: rgba(255,255,255,.72);
    }

    .admin-profile-page .profile-hero-side {
        display: grid;
        align-content: center;
        justify-items: start;
        margin-left: 0;
        padding: 28px 30px 28px 64px;
        border-radius: 0 12px 12px 0;
        background: #fff;
        z-index: 1;
    }

    .admin-profile-page .profile-hero-side span {
        color: var(--profile-navy);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .16em;
    }

    .admin-profile-page .profile-hero-side strong {
        margin-top: 8px;
        color: var(--profile-navy);
        font-size: 18px;
        line-height: 1.2;
        font-weight: 900;
    }

    .admin-profile-page .profile-hero-side small {
        margin-top: 8px;
        color: var(--profile-blue);
        font-size: 14px;
        font-weight: 900;
    }

    .admin-profile-page .profile-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .admin-profile-page .profile-summary {
        min-height: 98px;
        padding: 18px 20px;
        border: 1px solid var(--profile-border);
        border-top: 5px solid var(--summary-color);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 8px 18px rgba(8, 47, 89, .045);
    }

    .admin-profile-page .profile-summary--green { --summary-color: #18B877; }
    .admin-profile-page .profile-summary--blue { --summary-color: var(--profile-blue); }
    .admin-profile-page .profile-summary--amber { --summary-color: var(--profile-amber); }
    .admin-profile-page .profile-summary--navy { --summary-color: var(--profile-navy); }

    .admin-profile-page .profile-summary span,
    .admin-profile-page .profile-form label span {
        display: block;
        color: var(--profile-muted);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .admin-profile-page .profile-summary strong {
        display: block;
        margin-top: 6px;
        color: #061A3A;
        font-size: clamp(20px, 1.65vw, 26px);
        line-height: 1.1;
        font-weight: 900;
        overflow-wrap: anywhere;
    }

    .admin-profile-page .profile-summary small {
        display: block;
        margin-top: 6px;
        color: var(--profile-muted);
        font-size: 13px;
        font-weight: 650;
    }

    .admin-profile-page .profile-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(320px, .82fr);
        gap: 16px;
        align-items: start;
    }

    .admin-profile-page .profile-main,
    .admin-profile-page .profile-side {
        display: grid;
        gap: 16px;
        align-content: start;
    }

    .admin-profile-page .profile-panel {
        border: 1px solid var(--profile-border) !important;
        border-radius: 10px !important;
        background: #fff !important;
        box-shadow: 0 8px 18px rgba(8, 47, 89, .045) !important;
    }

    .admin-profile-page .panel-section-head {
        display: block;
        padding: 20px 22px 0;
        border: 0;
        background: transparent;
    }

    .admin-profile-page .panel-section-head h2 {
        position: relative;
        margin: 0;
        padding: 0 0 0 16px;
        color: var(--profile-navy);
        font-size: 21px;
        line-height: 1.18;
        font-weight: 900;
        letter-spacing: 0;
    }

    .admin-profile-page .panel-section-head h2::before {
        content: none;
    }

    .admin-profile-page .panel-section-head h2::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 28px;
        border-radius: 999px;
        background: var(--profile-red);
    }

    .admin-profile-page .panel-section-head p {
        margin: 7px 0 0 16px;
        color: var(--profile-muted);
        font-size: 14px;
        font-weight: 650;
    }

    .admin-profile-page .profile-form {
        padding: 18px 22px 22px;
    }

    .admin-profile-page .profile-form input {
        min-height: 38px;
        border: 1px solid #C8D6E7;
        border-radius: 5px;
        color: var(--profile-navy);
        font-size: 14px;
        font-weight: 650;
    }

    .admin-profile-page .profile-share-summary {
        padding: 18px 22px 22px;
    }

    .admin-profile-page .profile-detail-table {
        width: 100%;
        border: 1px solid var(--profile-border);
        border-radius: 8px;
        overflow: hidden;
        border-collapse: separate;
        border-spacing: 0;
    }

    .admin-profile-page .profile-detail-table th,
    .admin-profile-page .profile-detail-table td {
        padding: 16px 20px;
        border-bottom: 1px solid var(--profile-border);
        text-align: left;
    }

    .admin-profile-page .profile-detail-table tr:last-child th,
    .admin-profile-page .profile-detail-table tr:last-child td {
        border-bottom: 0;
    }

    .admin-profile-page .profile-detail-table th {
        width: 38%;
        background: #F8FBFF;
        color: var(--profile-navy);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .admin-profile-page .profile-detail-table td {
        color: #061A3A;
        font-size: 16px;
        font-weight: 900;
    }

    .admin-profile-page .profile-actions .button {
        min-width: 160px;
        min-height: 42px;
        border-color: var(--profile-blue);
        border-radius: 6px;
        background: var(--profile-blue);
        color: #fff;
        box-shadow: 0 8px 16px rgba(11, 94, 215, .14);
    }

    .admin-profile-page .admin-list {
        position: relative;
        display: grid;
        gap: 0;
        margin: 18px 22px 22px;
        padding-left: 22px;
    }

    .admin-profile-page .admin-list::before {
        content: "";
        position: absolute;
        top: 10px;
        bottom: 10px;
        left: 4px;
        width: 1px;
        background: #C8D6E7;
    }

    .admin-profile-page .admin-list__item,
    .admin-profile-page .admin-empty {
        position: relative;
        padding: 0 0 16px;
        border-bottom: 1px solid var(--profile-border);
    }

    .admin-profile-page .admin-list__item + .admin-list__item {
        padding-top: 16px;
    }

    .admin-profile-page .admin-list__item:last-child,
    .admin-profile-page .admin-empty:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .admin-profile-page .admin-list__item::before {
        content: "";
        position: absolute;
        top: 6px;
        left: -23px;
        width: 9px;
        height: 9px;
        border-radius: 999px;
        background: var(--profile-navy);
        box-shadow: 0 0 0 4px #fff;
    }

    .admin-profile-page .admin-list__item + .admin-list__item::before {
        top: 22px;
    }

    .admin-profile-page .admin-list__item strong,
    .admin-profile-page .admin-management strong {
        display: block;
        color: var(--profile-navy);
        font-size: 14px;
        line-height: 1.25;
        font-weight: 900;
    }

    .admin-profile-page .admin-list__item span,
    .admin-profile-page .admin-list__item small,
    .admin-profile-page .admin-empty,
    .admin-profile-page .admin-management span {
        display: block;
        margin-top: 5px;
        color: var(--profile-muted);
        font-size: 13px;
        font-weight: 700;
    }

    .admin-profile-page .admin-management {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        padding: 18px 22px 22px;
    }

    .admin-profile-page .admin-management a {
        min-height: 78px;
        padding: 14px;
        border: 1px solid var(--profile-border);
        border-radius: 8px;
        background: #F8FBFF;
        text-decoration: none;
        transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
    }

    .admin-profile-page .admin-management a:hover,
    .admin-profile-page .admin-management a:focus-visible {
        border-color: #AFC4DF;
        box-shadow: 0 8px 16px rgba(8, 47, 89, .08);
        outline: none;
        transform: translateY(-1px);
    }

    @media (max-width: 1180px) {
        .admin-profile-page .profile-hero-card,
        .admin-profile-page .profile-layout {
            grid-template-columns: 1fr;
        }

        .admin-profile-page .profile-hero-main {
            border-radius: 12px 12px 0 0;
            clip-path: none;
        }

        .admin-profile-page .profile-hero-main::after {
            top: auto;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 6px;
            clip-path: none;
        }

        .admin-profile-page .profile-hero-side {
            padding: 24px 30px;
            border-radius: 0 0 12px 12px;
        }

        .admin-profile-page .profile-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .admin-profile-page {
            width: 100%;
        }

        .admin-profile-page .profile-hero-main {
            padding: 24px 22px;
        }

        .admin-profile-page .profile-hero-main .profile-identity {
            flex-direction: column;
            align-items: flex-start;
        }

        .admin-profile-page .profile-hero-side {
            padding: 24px;
        }

        .admin-profile-page .profile-summary-grid,
        .admin-profile-page .profile-form-grid,
        .admin-profile-page .admin-management {
            grid-template-columns: 1fr;
        }

        .admin-profile-page .profile-actions .button {
            width: 100%;
        }
    }
</style>
@endpush
