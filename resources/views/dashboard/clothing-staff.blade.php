@extends('layouts.app')

@section('title', 'Home Pengurusan Baju')
@section('page-title', 'Home Pengurusan Baju')
@section('page-subtitle', 'Ringkasan pengurusan tempahan baju, notifikasi dan pengumuman.')

@section('content')
    @php
        $categoryClass = fn ($category) => match (strtolower((string) $category)) {
            'important' => 'important', 'reminder' => 'reminder', 'update' => 'update', default => 'general',
        };
        $categoryLabel = fn ($category) => match (strtolower((string) $category)) {
            'important' => 'Penting', 'reminder' => 'Peringatan', 'update' => 'Kemaskini', default => 'Umum',
        };
        $lastLogin = $home['last_login_at'] ? \Carbon\Carbon::parse($home['last_login_at'])->format('d/m/Y h:i A') : now()->format('d/m/Y h:i A');
        $announcementsCount = count($home['announcements']);
        $notificationsCount = count($home['messages']);
        $activitiesCount = count($home['recent_activities']);
        $recentActivities = collect($home['recent_activities'])->take(3);
    @endphp

    <div class="admin-home-content staff-home-content clothing-home-content">
        <section class="admin-welcome">
            <div class="admin-welcome__main">
                <img class="admin-welcome__watermark" src="{{ asset('images/koperasi-logo.svg') }}" alt="" aria-hidden="true">
                <span class="admin-home-mark"></span>
                <span class="admin-welcome__kicker">PORTAL PENGURUSAN BAJU</span>
                <h1>{{ $user->nama }}</h1>
                <p>Sistem Pengurusan Koperasi CoopBest</p>
            </div>
            <span class="admin-welcome__divider" aria-hidden="true"></span>
            <div class="admin-welcome__role">
                <span class="admin-welcome__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg></span>
                <div><span class="admin-welcome__role-label">Peranan</span><strong>Staff Pengurus Baju</strong><small>Log masuk terakhir: {{ $lastLogin }}</small></div>
            </div>
        </section>

        <section class="portal-home__summary-grid" aria-label="Ringkasan dashboard">
            <article class="portal-home__summary-card portal-home__summary-card--navy"><span>Pengumuman</span><strong>{{ $announcementsCount }}</strong><small>Makluman untuk semakan anda.</small></article>
            <article class="portal-home__summary-card portal-home__summary-card--blue"><span>Notifikasi</span><strong>{{ $notificationsCount }}</strong><small>Maklumat akaun terkini.</small></article>
            <article class="portal-home__summary-card portal-home__summary-card--red"><span>Aktiviti Tempahan</span><strong>{{ $activitiesCount }}</strong><small>Rekod tempahan terbaru.</small></article>
        </section>

        <section class="portal-home__grid portal-home__grid--overview">
            <article class="portal-home__panel portal-home__panel--announcement">
                <header><div><span>PENGUMUMAN</span><h2>Pengumuman Terkini</h2><p>Makluman rasmi terkini untuk pengurusan baju.</p></div><a class="portal-home__primary" href="{{ route('admin.announcements.create') }}">Tambah</a></header>
                <div class="portal-home__stack">
                    @forelse ($home['announcements'] as $announcement)
                        <div class="portal-home__announcement">
                            <div class="portal-home__announcement-top"><span class="portal-home__pill {{ $categoryClass($announcement->category) }}">{{ $categoryLabel($announcement->category) }}</span></div>
                            <h3>{{ $announcement->title }}</h3><p>{{ $announcement->body }}</p>
                            <div class="portal-home__announcement-meta"><span>{{ $announcement->created_at?->format('d/m/Y') ?? '-' }}</span><span>Oleh {{ $announcement->announcer_name }}</span><span>Tamat: {{ $announcement->ends_at?->format('d/m/Y') ?? '-' }}</span></div>
                            <div class="portal-home__announcement-actions">
                                <a href="{{ route('admin.announcements.edit', $announcement) }}">Kemaskini</a>
                                <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Padam pengumuman ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Padam</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="portal-home__empty"><strong>Tiada pengumuman terkini.</strong><span>Pengumuman baharu akan dipaparkan di sini.</span></div>
                    @endforelse
                </div>
            </article>

            <aside class="portal-home__panel portal-home__panel--compact">
                <header><div><span>NOTIFIKASI</span><h2>Notifikasi</h2><p>Maklumat terkini berkaitan akaun anda.</p></div><a class="portal-home__primary portal-home__primary--soft" href="{{ route('notifications.index') }}">Lihat Semua</a></header>
                <div class="portal-home__notifications">
                    @forelse ($home['messages'] as $notification)
                        <a class="portal-home__notice {{ $notification->read_at ? 'is-read' : 'is-unread' }}" href="{{ $notification->link ?: route('notifications.index') }}"><span class="portal-home__dot"></span><div><strong>{{ $notification->title }}</strong><p>{{ $notification->message ?? 'Tiada makluman tambahan.' }}</p><small>{{ $notification->created_at?->format('d/m/Y H:i') }}</small></div></a>
                    @empty
                        <div class="portal-home__empty"><strong>Tiada notifikasi terkini.</strong><span>Notifikasi baharu akan dipaparkan di sini.</span></div>
                    @endforelse
                </div>
            </aside>
        </section>

        <section class="portal-home__panel portal-home__panel--activity">
            <header><div><span>AKTIVITI TERKINI</span><h2>Aktiviti Tempahan Baju</h2><p>Rekod tempahan dan tindakan terbaru dalam sistem.</p></div></header>
            <div class="portal-home__activity">
                @forelse ($recentActivities as $activity)
                    <div class="portal-home__activity-item"><span class="portal-home__dot"></span><div><strong>{{ $activity['name'] }}</strong><small>{{ $activity['user'] }} - {{ $activity['time'] }}</small></div></div>
                @empty
                    <div class="portal-home__empty"><strong>Tiada aktiviti terkini.</strong><span>Tempahan dan notifikasi akan muncul di sini.</span></div>
                @endforelse
            </div>
        </section>
    </div>
@endsection

@include('components.portal-home-style')

@push('styles')
<style>
    .content:has(>.clothing-home-content){background:#F4F7FB;padding:0}.admin-home-content{width:100%;max-width:1600px;margin:0 auto;padding:32px;color:#082F59}.staff-home-content{display:grid;gap:20px}.admin-welcome{position:relative;display:grid;grid-template-columns:minmax(0,68%) minmax(270px,32%);height:180px;border:1px solid #D7E2EF;border-radius:12px;background:#fff;box-shadow:0 8px 20px rgba(8,47,89,.035);overflow:hidden}.admin-welcome__main{position:relative;display:flex;flex-direction:column;justify-content:center;min-width:0;padding:32px 40px;background:#082F59;color:#fff;clip-path:polygon(0 0,calc(100% - 30px) 0,100% 100%,0 100%);overflow:hidden}.admin-welcome__watermark{position:absolute;left:-18px;bottom:-50px;width:270px;opacity:.045;filter:grayscale(1) brightness(3)}.admin-home-mark{width:42px;height:4px;margin-bottom:12px;background:#ED1C2E}.admin-welcome__kicker,.admin-welcome__role-label{font-size:12px;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.admin-welcome h1{margin:8px 0 0;color:#fff;font-size:38px;line-height:1.1;font-weight:900;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.admin-welcome p{margin:8px 0 0;color:rgba(255,255,255,.92);font-size:16px;font-weight:800}.admin-welcome__divider{position:absolute;top:0;bottom:0;left:calc(68% - 38px);width:40px;background:#ED1C2E;clip-path:polygon(0 0,10px 0,40px 100%,30px 100%);z-index:2}.admin-welcome__role{display:flex;align-items:center;justify-content:center;gap:18px;padding:28px;background:#fff}.admin-welcome__icon{width:58px;height:58px;color:#061E5C}.admin-welcome__icon svg{width:100%;height:100%}.admin-welcome__role strong{display:block;margin-top:5px;color:#061E5C;font-size:21px;line-height:1.16;font-weight:900}.admin-welcome__role small{display:block;margin-top:7px;color:#2453A6;font-size:13px;font-weight:900}.portal-home__summary-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.portal-home__summary-card{position:relative;display:grid;gap:4px;min-height:116px;padding:18px 20px;border:1px solid #D7E2EF;border-radius:10px;background:#fff;box-shadow:0 6px 16px rgba(8,47,89,.03);overflow:hidden}.portal-home__summary-card::before{content:"";position:absolute;top:18px;bottom:18px;left:0;width:4px;background:#082F59}.portal-home__summary-card--blue::before{background:#0B5ED7}.portal-home__summary-card--red::before{background:#ED1C2E}.portal-home__summary-card span{padding-left:4px;color:#526987;font-size:11px;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.portal-home__summary-card strong{padding-left:4px;color:#082F59;font-size:30px;line-height:1;font-weight:900}.portal-home__summary-card small{padding-left:4px;color:#526987;font-size:12px;font-weight:700}.portal-home__grid{gap:20px;align-items:start}.portal-home__grid--overview{grid-template-columns:minmax(0,1.2fr) minmax(360px,.8fr);align-items:stretch}.portal-home__panel{height:auto!important;min-height:0!important;padding:20px 22px;border:1px solid #D7E2EF;border-radius:12px;background:#fff;box-shadow:0 6px 16px rgba(8,47,89,.035)}.portal-home__panel--compact{padding:18px 20px}.portal-home__panel header{align-items:flex-start;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid #D7E2EF}.portal-home__panel header span{color:#0B5ED7;font-size:12px;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.portal-home__panel header div::before{content:"";display:block;width:32px;height:4px;margin-bottom:8px;background:#ED1C2E}.portal-home__panel h2{margin:0;color:#082F59;font-size:28px;line-height:1.12;font-weight:900}.portal-home__panel header p{margin:5px 0 0;color:#526987;font-size:13px;font-weight:700}.portal-home__primary{min-height:42px;border:1px solid #9EC5FF;border-radius:6px;background:#fff;color:#0B5ED7;padding:0 14px;font-size:13px;font-weight:900}.portal-home__announcement{padding:16px 18px;border:1px solid #C8DBF3;border-radius:10px;background:#FBFDFF}.portal-home__announcement h3,.portal-home__notice strong,.portal-home__activity-item strong{color:#061E5C}.portal-home__announcement p,.portal-home__notice p{color:#31517D;font-weight:650}.portal-home__announcement-meta,.portal-home__notice small,.portal-home__activity-item small{color:#2453A6;font-weight:800}.portal-home__notice{padding:14px 16px;border:1px solid #C8DBF3;border-radius:10px;background:#fff}.portal-home__dot{width:11px;height:11px;margin-top:5px;background:#0B5ED7}.portal-home__panel--activity .portal-home__activity{display:grid;grid-template-columns:repeat(3,minmax(0,1fr))}.portal-home__panel--activity .portal-home__activity-item{padding:4px 18px;border-right:1px solid #D7E2EF;border-bottom:0;border-radius:0;background:#fff}.portal-home__panel--activity .portal-home__activity-item:last-child{border-right:0}.portal-home__empty{display:grid;place-content:center;min-height:92px;padding:14px 18px;border:1px dashed #C8DBF3;border-radius:8px;background:#F9FCFF;text-align:center}.portal-home__empty strong{color:#082F59}.portal-home__empty span{color:#526987}@media(max-width:900px){.admin-home-content{padding:20px}.admin-welcome,.portal-home__grid--overview{grid-template-columns:1fr;height:auto}.admin-welcome__main{clip-path:none}.admin-welcome__divider{display:none}.portal-home__summary-grid,.portal-home__panel--activity .portal-home__activity{grid-template-columns:1fr}.portal-home__panel--activity .portal-home__activity-item{padding:12px 0;border-right:0;border-bottom:1px solid #D7E2EF}.admin-welcome h1{white-space:normal}}@media(max-width:680px){.admin-home-content{padding:18px}.admin-welcome__main,.admin-welcome__role,.portal-home__panel{padding:20px}.portal-home__panel header{align-items:stretch;flex-direction:column}.portal-home__primary{width:100%}}
    .portal-home__announcement-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:12px}
    .portal-home__announcement-actions form{margin:0}
    .portal-home__announcement-actions a,.portal-home__announcement-actions button{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 15px;border:1px solid #9EC5FF;border-radius:6px;background:#fff;color:#0B5ED7;text-decoration:none;font:inherit;font-size:13px;font-weight:900;cursor:pointer}
    .portal-home__announcement-actions button{border-color:#FECACA;background:#FEF2F2;color:#B91C1C}
    .portal-home__announcement-actions a:hover{border-color:#0B5ED7;background:#EAF2FF}
    .portal-home__announcement-actions button:hover{background:#FEE2E2}
    @media(max-width:680px){.portal-home__announcement-actions,.portal-home__announcement-actions form,.portal-home__announcement-actions a,.portal-home__announcement-actions button{width:100%}}
</style>
@endpush
