@extends('layouts.app')

@section('title', 'Laman Utama Staff')
@section('page-title', 'Laman Utama Staff')
@section('page-subtitle', 'Pusat maklumat staff untuk pengumuman, notifikasi dan tindakan koperasi.')

@section('content')
    @php
        $categoryClass = fn ($category) => match (strtolower((string) $category)) {
            'important' => 'important',
            'reminder' => 'reminder',
            'update' => 'update',
            default => 'general',
        };
        $categoryLabel = fn ($category) => match (strtolower((string) $category)) {
            'important' => 'Penting',
            'reminder' => 'Peringatan',
            'update' => 'Kemaskini',
            default => 'Umum',
        };
        $lastLogin = $home['last_login_at'] ? \Carbon\Carbon::parse($home['last_login_at'])->format('d/m/Y h:i A') : now()->format('d/m/Y h:i A');
        $isCoopStaff = $staffType === 'coop_staff';
        $memberNumber = $staffMemberNumber ?? $user->no_anggota;
        $announcementsCount = count($home['announcements']);
        $notificationsCount = count($home['messages']);
        $activitiesCount = count($home['recent_activities']);
    @endphp

    <div class="admin-home-content staff-home-content">
        <section class="admin-welcome">
            <div class="admin-welcome__main">
                <img class="admin-welcome__watermark" src="{{ asset('images/koperasi-logo.svg') }}" alt="" aria-hidden="true">
                <span class="admin-home-mark"></span>
                <span class="admin-welcome__kicker">{{ $isCoopStaff ? 'PORTAL PEKERJA KOPERASI' : 'PORTAL STAFF COOPBEST' }}</span>
                <h1>{{ $user->nama }}</h1>
                <p>Sistem Pengurusan Koperasi CoopBest</p>
            </div>
            <span class="admin-welcome__divider" aria-hidden="true"></span>
            <div class="admin-welcome__role">
                <span class="admin-welcome__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21a8 8 0 0 0-16 0"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </span>
                <div>
                    <span class="admin-welcome__role-label">Peranan</span>
                    <strong>{{ $user->staff_type_label }}</strong>
                    <small>Log masuk terakhir: {{ $lastLogin }}</small>
                </div>
            </div>
        </section>

        <section class="portal-home__summary-grid" aria-label="Ringkasan dashboard">
            <article class="portal-home__summary-card portal-home__summary-card--navy">
                <span>Pengumuman</span>
                <strong>{{ $announcementsCount }}</strong>
                <small>Makluman untuk semakan anda.</small>
            </article>
            <article class="portal-home__summary-card portal-home__summary-card--blue">
                <span>Notifikasi</span>
                <strong>{{ $notificationsCount }}</strong>
                <small>Maklumat akaun terkini.</small>
            </article>
            <article class="portal-home__summary-card portal-home__summary-card--red">
                <span>Aktiviti</span>
                <strong>{{ $activitiesCount }}</strong>
                <small>Rekod tindakan terbaru.</small>
            </article>
        </section>

        <section class="portal-home__grid portal-home__grid--overview">
            <article class="portal-home__panel portal-home__panel--announcement">
                <header>
                    <div>
                        <span>PENGUMUMAN</span>
                        <h2>Pengumuman Terkini</h2>
                        <p>Makluman rasmi terkini untuk staff.</p>
                    </div>
                </header>
                <div class="portal-home__stack">
                    @forelse ($home['announcements'] as $announcement)
                        <div class="portal-home__announcement">
                            <div class="portal-home__announcement-top">
                                <span class="portal-home__pill {{ $categoryClass($announcement->category) }}">{{ $categoryLabel($announcement->category) }}</span>
                                <span class="portal-home__pill general">{{ $announcement->status_label }}</span>
                            </div>
                            <h3>{{ $announcement->title }}</h3>
                            <p>{{ $announcement->body }}</p>
                            <div class="portal-home__announcement-meta">
                                <span>{{ $announcement->created_at?->format('d/m/Y') ?? '-' }}</span>
                                <span>Tamat: {{ $announcement->ends_at?->format('d/m/Y') ?? '-' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="portal-home__empty">
                            <strong>Tiada pengumuman terkini.</strong>
                            <span>Pengumuman staff akan dipaparkan di sini.</span>
                        </div>
                    @endforelse
                </div>
            </article>

            <aside class="portal-home__panel portal-home__panel--compact">
                <header>
                    <div>
                        <span>NOTIFIKASI</span>
                        <h2>Notifikasi</h2>
                        <p>Maklumat terkini berkaitan akaun anda.</p>
                    </div>
                    <a class="portal-home__primary portal-home__primary--soft" href="{{ route('notifications.index') }}">Lihat Semua</a>
                </header>
                <div class="portal-home__notifications">
                    @forelse ($home['messages'] as $notification)
                        <a class="portal-home__notice {{ $notification->read_at ? 'is-read' : 'is-unread' }}" href="{{ $notification->link ?: route('notifications.index') }}">
                            <span class="portal-home__dot"></span>
                            <div>
                                <strong>{{ $notification->title }}</strong>
                                <p>{{ $notification->message ?? 'Tiada makluman tambahan.' }}</p>
                                <small>{{ $notification->created_at?->format('d/m/Y H:i') }}</small>
                            </div>
                        </a>
                    @empty
                        <div class="portal-home__empty">
                            <strong>Tiada notifikasi terkini.</strong>
                            <span>Notifikasi baharu akan dipaparkan di sini.</span>
                        </div>
                    @endforelse
                </div>
            </aside>
        </section>

        <section class="portal-home__panel portal-home__panel--activity">
            <header>
                <div>
                    <span>AKTIVITI TERKINI</span>
                    <h2>Aktiviti Terkini</h2>
                    <p>Rekod aktiviti terbaru anda dalam sistem.</p>
                </div>
            </header>
            <div class="portal-home__activity">
                @forelse ($home['recent_activities'] as $activity)
                    <div class="portal-home__activity-item">
                        <span class="portal-home__dot"></span>
                        <div>
                            <strong>{{ $activity['name'] }}</strong>
                            <small>{{ $activity['user'] }} - {{ $activity['time'] }}</small>
                        </div>
                    </div>
                @empty
                    <div class="portal-home__empty">
                        <strong>Tiada aktiviti terkini.</strong>
                        <span>Aktiviti staff akan muncul di sini.</span>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection

@include('components.portal-home-style')

@push('styles')
<style>
    .content:has(> .admin-home-content){background:#F4F7FB;padding:0}
    .admin-home-content{width:100%;max-width:1600px;margin:0 auto;padding:clamp(20px,2vw,32px);color:#082F59}
    .staff-home-content{display:grid;gap:20px}
    .admin-welcome{position:relative;display:grid;grid-template-columns:minmax(0,68%) minmax(270px,32%);height:clamp(154px,13.5vw,190px);max-height:190px;border:1px solid #D7E2EF;border-radius:12px;background:#fff;box-shadow:0 8px 20px rgba(8,47,89,.035);overflow:hidden}
    .admin-welcome__main{position:relative;display:flex;flex-direction:column;justify-content:center;min-width:0;padding:32px 40px;background:#082F59;color:#fff;clip-path:polygon(0 0,calc(100% - 30px) 0,100% 100%,0 100%);overflow:hidden}
    .admin-welcome__watermark{position:absolute;left:-18px;bottom:-50px;width:min(270px,28vw);opacity:.045;pointer-events:none;filter:grayscale(1) brightness(3)}
    .admin-home-mark{display:block;width:42px;height:4px;margin-bottom:12px;background:#ED1C2E}
    .admin-welcome__kicker,.admin-welcome__role-label{position:relative;z-index:1;color:inherit;font-size:12px;font-weight:900;letter-spacing:.1em;text-transform:uppercase}
    .admin-welcome h1{position:relative;z-index:1;max-width:calc(100% - 34px);margin:8px 0 0;color:#fff;font-size:clamp(30px,2.3vw,42px);line-height:1.1;font-weight:900;letter-spacing:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .admin-welcome p{position:relative;z-index:1;margin:8px 0 0;color:rgba(255,255,255,.92);font-size:clamp(15px,1.1vw,18px);font-weight:800}
    .admin-welcome__divider{position:absolute;top:0;bottom:0;left:calc(68% - 38px);width:40px;background:#ED1C2E;clip-path:polygon(0 0,10px 0,40px 100%,30px 100%);pointer-events:none;z-index:2}
    .admin-welcome__role{display:flex;align-items:center;justify-content:center;gap:18px;padding:clamp(24px,2vw,36px);background:#fff;color:#082F59}
    .admin-welcome__icon{flex:0 0 auto;width:clamp(48px,4vw,64px);height:clamp(48px,4vw,64px);color:#061E5C}
    .admin-welcome__icon svg{width:100%;height:100%}
    .admin-welcome__role strong{display:block;margin-top:5px;color:#061E5C;font-size:clamp(18px,1.35vw,24px);line-height:1.16;font-weight:900}
    .admin-welcome__role small{display:block;margin-top:7px;color:#2453A6;font-size:13px;font-weight:900}
    .portal-home__welcome{position:relative;display:grid;grid-template-columns:minmax(0,68%) minmax(270px,32%);height:clamp(154px,13.5vw,190px);max-height:190px;padding:0;border:1px solid #D7E2EF;border-radius:12px;background:#fff;box-shadow:0 8px 20px rgba(8,47,89,.035);overflow:hidden}
    .portal-home__welcome-main{position:relative;display:flex;flex-direction:column;justify-content:center;min-width:0;padding:32px 40px;background:#082F59;color:#fff;clip-path:polygon(0 0,calc(100% - 30px) 0,100% 100%,0 100%);overflow:hidden}
    .portal-home__watermark{position:absolute;left:-18px;bottom:-50px;width:min(270px,28vw);opacity:.045;pointer-events:none;filter:grayscale(1) brightness(3)}
    .portal-home__mark{position:relative;z-index:1;display:block;width:42px;height:4px;margin-bottom:12px;background:#ED1C2E}
    .portal-home__kicker{position:relative;z-index:1;color:#fff;font-size:12px;font-weight:900;letter-spacing:.1em;text-transform:uppercase}
    .portal-home__welcome h1{position:relative;z-index:1;max-width:calc(100% - 34px);margin:8px 0 0;color:#fff;font-size:clamp(30px,2.3vw,42px);line-height:1.1;font-weight:900;letter-spacing:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .portal-home__welcome p{position:relative;z-index:1;margin:8px 0 0;color:rgba(255,255,255,.92);font-size:clamp(15px,1.1vw,18px);font-weight:800}
    .portal-home__divider{position:absolute;top:0;bottom:0;left:calc(68% - 38px);width:40px;background:#ED1C2E;clip-path:polygon(0 0,10px 0,40px 100%,30px 100%);pointer-events:none;z-index:2}
    .portal-home__identity{display:flex;align-items:center;justify-content:center;gap:16px;min-width:0;padding:24px 30px;border:0;border-radius:0;background:#fff;color:#082F59;text-align:left}
    .portal-home__identity-icon{flex:0 0 auto;width:clamp(42px,3.5vw,54px);height:clamp(42px,3.5vw,54px);color:#061E5C}
    .portal-home__identity-icon svg{width:100%;height:100%}
    .portal-home__identity span:not(.portal-home__identity-icon){display:block;color:#082F59;font-size:12px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
    .portal-home__identity strong{display:block;margin-top:5px;color:#061E5C;font-size:clamp(18px,1.35vw,24px);line-height:1.16;font-weight:900}
    .portal-home__identity small{display:block;margin-top:7px;color:#2453A6;font-size:13px;font-weight:900}
    .portal-home__summary-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
    .portal-home__summary-card{position:relative;display:grid;gap:4px;min-height:116px;padding:18px 20px;border:1px solid #D7E2EF;border-radius:10px;background:#fff;box-shadow:0 6px 16px rgba(8,47,89,.03);overflow:hidden}
    .portal-home__summary-card::before{content:"";position:absolute;top:18px;bottom:18px;left:0;width:4px;background:#082F59}
    .portal-home__summary-card--blue::before{background:#0B5ED7}
    .portal-home__summary-card--red::before{background:#ED1C2E}
    .portal-home__summary-card span{padding-left:4px;color:#526987;font-size:11px;font-weight:900;letter-spacing:.1em;text-transform:uppercase}
    .portal-home__summary-card strong{padding-left:4px;color:#082F59;font-size:30px;line-height:1;font-weight:900}
    .portal-home__summary-card small{padding-left:4px;color:#526987;font-size:12px;font-weight:700}
    .portal-home__grid{gap:20px;align-items:start}
    .portal-home__grid--overview{grid-template-columns:minmax(0,1.2fr) minmax(360px,.8fr);align-items:stretch}
    .portal-home__grid--overview > .portal-home__panel{height:100%!important}
    .portal-home__panel{height:auto!important;min-height:0!important;border:1px solid #D7E2EF;border-radius:12px;background:#fff;box-shadow:0 6px 16px rgba(8,47,89,.035);padding:20px 22px}
    .portal-home__panel--compact{padding:18px 20px}
    .portal-home__panel header{align-items:flex-start;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid #D7E2EF}
    .portal-home__panel header span{color:#0B5ED7;font-size:12px;font-weight:900;letter-spacing:.1em;text-transform:uppercase}
    .portal-home__panel header div::before{content:"";display:block;width:32px;height:4px;margin-bottom:8px;background:#ED1C2E}
    .portal-home__panel h2{margin:0;color:#082F59;font-size:clamp(24px,1.7vw,30px);line-height:1.12;font-weight:900;letter-spacing:0}
    .portal-home__panel header p{margin:5px 0 0;color:#526987;font-size:13px;font-weight:700;line-height:1.4}
    .portal-home__primary{min-height:42px;border:1px solid #9EC5FF;border-radius:6px;background:#fff;color:#0B5ED7;padding:0 14px;font-size:13px;font-weight:900}
    .portal-home__announcement{padding:16px 18px;border:1px solid #C8DBF3;border-radius:10px;background:#FBFDFF}
    .portal-home__announcement h3{color:#061E5C;font-size:clamp(18px,1.25vw,21px);line-height:1.2}
    .portal-home__announcement p{color:#31517D;font-size:14px;font-weight:650}
    .portal-home__announcement-meta{color:#2453A6;font-weight:800}
    .portal-home__pill{min-height:28px;padding:0 13px}
    .portal-home__notice{padding:14px 16px;border:1px solid #C8DBF3;border-radius:10px;background:#fff}
    .portal-home__notice:hover{border-color:#9EC5FF;background:#FBFDFF;color:#082F59}
    .portal-home__dot{width:11px;height:11px;margin-top:5px;background:#0B5ED7}
    .portal-home__notice strong,.portal-home__activity-item strong{color:#061E5C}
    .portal-home__notice p{color:#31517D;font-size:13px;font-weight:700}
    .portal-home__notice small,.portal-home__activity-item small{color:#2453A6;font-weight:800}
    .portal-home__activity{gap:0}
    .portal-home__panel--activity{padding-bottom:18px}
    .portal-home__panel--activity .portal-home__activity{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:0}
    .portal-home__panel--activity .portal-home__empty{grid-column:1 / -1}
    .portal-home__activity-item{padding:14px 0;border:0;border-bottom:1px solid #D7E2EF;border-radius:0;background:#fff}
    .portal-home__activity-item:last-child{border-bottom:0}
    .portal-home__panel--activity .portal-home__activity-item{padding:4px 18px;border-right:1px solid #D7E2EF;border-bottom:0}
    .portal-home__panel--activity .portal-home__activity-item:first-child{padding-left:0}
    .portal-home__panel--activity .portal-home__activity-item:last-child{border-right:0}
    .portal-home__empty{display:grid;place-content:center;min-height:92px;padding:14px 18px;border:1px dashed #C8DBF3;border-radius:8px;background:#F9FCFF;text-align:center}
    .portal-home__empty strong{color:#082F59}
    .portal-home__empty span{color:#526987}
    .portal-home__primary:hover{border-color:#0B5ED7;background:#EAF2FF;color:#0B5ED7}
    @media (max-width:1100px){
        .admin-welcome{grid-template-columns:minmax(0,64%) minmax(260px,36%);height:auto;max-height:none}
        .admin-welcome__main{padding:26px 32px}
        .admin-welcome h1{font-size:clamp(27px,3vw,36px)}
        .admin-welcome__divider{left:calc(64% - 38px)}
        .portal-home__welcome{grid-template-columns:minmax(0,64%) minmax(260px,36%);height:auto;max-height:none}
        .portal-home__welcome-main{padding:26px 32px}
        .portal-home__welcome h1{font-size:clamp(27px,3vw,36px)}
        .portal-home__divider{left:calc(64% - 38px)}
        .portal-home__grid--overview{grid-template-columns:1fr}
        .portal-home__grid--overview > .portal-home__panel{height:auto!important}
        .portal-home__panel--activity .portal-home__activity{grid-template-columns:repeat(2,minmax(0,1fr))}
        .portal-home__identity{justify-content:flex-start}
    }
    @media (max-width:900px){
        .admin-welcome{grid-template-columns:1fr}
        .admin-welcome__main{clip-path:none}
        .admin-welcome__divider{display:none}
        .portal-home__welcome{grid-template-columns:1fr}
        .portal-home__welcome-main{clip-path:none}
        .portal-home__divider{display:none}
        .portal-home__summary-grid{grid-template-columns:1fr}
        .portal-home__panel--activity .portal-home__activity{grid-template-columns:1fr}
        .portal-home__panel--activity .portal-home__activity-item,.portal-home__panel--activity .portal-home__activity-item:first-child{padding:12px 0;border-right:0;border-bottom:1px solid #D7E2EF}
        .portal-home__panel--activity .portal-home__activity-item:last-child{border-bottom:0}
    }
    @media (max-width:680px){
        .admin-home-content{padding:18px}
        .admin-welcome__main,.admin-welcome__role{padding:20px}
        .admin-welcome h1{max-width:100%;font-size:28px;white-space:normal}
        .portal-home__welcome-main,.portal-home__identity,.portal-home__panel{padding:20px}
        .portal-home__welcome h1{max-width:100%;font-size:28px;white-space:normal}
        .portal-home__panel header{align-items:stretch;flex-direction:column}
        .portal-home__primary{width:100%}
    }
</style>
@endpush
