@extends('layouts.app')

@section('title', 'Laman Utama Admin')
@section('page-title', 'Laman Utama Admin')
@section('page-subtitle', 'Pusat kawalan admin untuk pengumuman, notifikasi, tugasan dan status sistem.')

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
    @endphp

    <div class="admin-home-content">
        <section class="admin-welcome">
            <div class="admin-welcome__main">
                <img class="admin-welcome__watermark" src="{{ asset('images/koperasi-logo.svg') }}" alt="" aria-hidden="true">
                <span class="admin-home-mark"></span>
                <span class="admin-welcome__kicker">PORTAL PENTADBIR COOPBEST</span>
                <h1>{{ $user->nama ?? 'Admin' }}</h1>
                <p>Sistem Pengurusan Koperasi CoopBest</p>
            </div>
            <span class="admin-welcome__divider" aria-hidden="true"></span>
            <div class="admin-welcome__role">
                <span class="admin-welcome__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <circle cx="12" cy="10" r="3"/>
                        <path d="M7.5 17a5.4 5.4 0 0 1 9 0"/>
                    </svg>
                </span>
                <div>
                    <span class="admin-welcome__role-label">Peranan</span>
                    <strong>Admin / Pentadbir Sistem</strong>
                    <small>Log masuk terakhir: {{ $lastLogin }}</small>
                </div>
            </div>
        </section>

        <section class="admin-home-grid">
            <article class="admin-home-panel announcement-card">
                <header class="admin-home-panel__header">
                    <div>
                        <span class="admin-home-mark"></span>
                        <h2>Pusat Pengumuman</h2>
                        <p>Maklumat dan pengumuman penting daripada pihak CoopBest.</p>
                    </div>
                    <a class="admin-home-primary" href="{{ route('admin.announcements.create') }}" aria-label="Tambah pengumuman">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 5v14"/>
                            <path d="M5 12h14"/>
                        </svg>
                        <span>Tambah</span>
                    </a>
                </header>
                <div class="admin-announcement-list">
                    @forelse ($home['announcements'] as $announcement)
                        <article class="admin-announcement-item">
                            <div class="admin-announcement-item__top">
                                <span class="admin-pill admin-pill--{{ $categoryClass($announcement->category) }}">{{ $categoryLabel($announcement->category) }}</span>
                                <span class="admin-pill admin-pill--{{ $announcement->is_active ? 'active' : 'inactive' }}">{{ $announcement->status_label }}</span>
                            </div>
                            <h3>{{ $announcement->title }}</h3>
                            <p>{{ $announcement->body }}</p>
                            <div class="admin-announcement-item__meta">
                                <span>Dicipta oleh {{ $announcement->created_by ? 'Admin #'.$announcement->created_by : 'Admin' }}</span>
                                <span>{{ $announcement->created_at?->format('d/m/Y') ?? '-' }}</span>
                                <span>Tamat: {{ $announcement->ends_at?->format('d/m/Y') ?? '-' }}</span>
                            </div>
                            <div class="admin-announcement-item__actions">
                                <a href="{{ route('admin.announcements.edit', $announcement) }}">Kemaskini</a>
                                <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Padam pengumuman ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Padam</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <div class="admin-home-empty">
                            <strong>Tiada pengumuman lagi.</strong>
                            <span>Tambah pengumuman untuk paparan rasmi admin dan pengguna.</span>
                        </div>
                    @endforelse
                </div>
            </article>

            <aside class="admin-home-panel admin-notifications-card">
                <header class="admin-home-panel__header admin-home-panel__header--row">
                    <div>
                        <span class="admin-home-mark"></span>
                        <h2>Notifikasi</h2>
                        <p>Maklumat terkini berkaitan pengurusan sistem.</p>
                    </div>
                    <a class="admin-home-view-all" href="{{ route('notifications.index') }}">Lihat Semua</a>
                </header>
                <div class="admin-notification-list">
                    @forelse ($home['notifications'] as $notification)
                        <a class="admin-notification-item {{ $notification->read_at ? 'is-read' : 'is-unread' }}" href="{{ $notification->link ?: route('notifications.index') }}">
                            <span class="admin-notification-item__dot"></span>
                            <div>
                                <strong>{{ $notification->title }}</strong>
                                <p>{{ $notification->message ?? 'Tiada makluman tambahan.' }}</p>
                            </div>
                            <time>{{ $notification->created_at?->format('d/m/Y, h:i A') }}</time>
                        </a>
                    @empty
                        <div class="admin-home-empty admin-home-empty--compact">
                            <strong>Tiada notifikasi terkini.</strong>
                            <span>Notifikasi baharu akan dipaparkan di sini.</span>
                        </div>
                    @endforelse
                </div>
            </aside>
        </section>

    </div>
@endsection

@push('styles')
<style>
    .content:has(> .admin-home-content){background:#F4F7FB;padding:0}
    .admin-home-content{width:100%;max-width:1600px;margin:0 auto;padding:clamp(20px,2vw,32px);color:#082F59}
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
    .admin-home-grid{display:grid;grid-template-columns:minmax(0,2fr) minmax(320px,1fr);gap:20px;align-items:stretch;margin-top:20px}
    .admin-home-panel{height:auto;min-height:0;border:1px solid #D7E2EF;border-radius:12px;background:#fff;box-shadow:0 8px 20px rgba(8,47,89,.035);padding:22px}
    .admin-home-panel__header{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid #D7E2EF}
    .admin-home-panel__header .admin-home-mark{width:32px;height:4px;margin-bottom:8px}
    .admin-home-panel h2{margin:0;color:#082F59;font-size:clamp(24px,1.7vw,30px);line-height:1.12;font-weight:900;letter-spacing:0}
    .admin-home-panel p{margin:4px 0 0;color:#31517D;font-size:15px;line-height:1.35;font-weight:650}
    .admin-home-primary,.admin-home-view-all{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px;border-radius:6px;text-decoration:none;font-weight:900;white-space:nowrap}
    .admin-home-primary{padding:0 18px;border:1px solid #0B5ED7;background:#0B5ED7;color:#fff;box-shadow:0 10px 18px rgba(11,94,215,.18)}
    .admin-home-primary svg{width:18px;height:18px}
    .admin-home-view-all{min-width:108px;padding:0 14px;border:1px solid #9EC5FF;background:#fff;color:#0B5ED7;font-size:13px}
    .admin-announcement-list{display:grid;gap:12px}
    .admin-notification-list{display:grid;gap:0}
    .admin-announcement-item{height:auto;min-height:0;display:grid;gap:11px;padding:16px 18px;border:1px solid #C8DBF3;border-radius:10px;background:#FBFDFF}
    .admin-announcement-item__top,.admin-announcement-item__meta,.admin-announcement-item__actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
    .admin-announcement-item__top{justify-content:space-between}
    .admin-announcement-item h3{margin:0;color:#061E5C;font-size:clamp(18px,1.25vw,21px);line-height:1.2;font-weight:900;letter-spacing:0}
    .admin-announcement-item p{margin:0;color:#061E5C;font-size:14px;font-weight:700;line-height:1.5;white-space:pre-line}
    .admin-announcement-item__meta{color:#2453A6;font-size:13px;font-weight:800}
    .admin-announcement-item__actions a,.admin-announcement-item__actions button{display:inline-flex;align-items:center;justify-content:center;min-height:38px;border:1px solid #9EC5FF;border-radius:6px;background:#fff;color:#0B5ED7;padding:0 15px;text-decoration:none;font-weight:900}
    .admin-announcement-item__actions button{border-color:#FECACA;background:#FEF2F2;color:#B91C1C}
    .admin-pill{display:inline-flex;align-items:center;justify-content:center;min-height:28px;width:max-content;padding:0 13px;border-radius:999px;font-size:12px;font-weight:900}
    .admin-pill--important,.admin-pill--reminder{background:#FEF3C7;color:#B45309}
    .admin-pill--update{background:#DCFCE7;color:#15803D}
    .admin-pill--general{background:#DBEAFE;color:#1D4ED8}
    .admin-pill--active{background:#DCFCE7;color:#15803D}
    .admin-pill--inactive{background:#FEE2E2;color:#B91C1C}
    .admin-notification-item{display:grid;grid-template-columns:12px minmax(0,1fr) auto;gap:12px;align-items:start;padding:11px 0;border:0;border-bottom:1px solid #D7E2EF;border-radius:0;background:#fff;color:#082F59;text-decoration:none}
    .admin-notification-item:last-child{border-bottom:0}
    .admin-notification-item:hover{background:#fff;color:#082F59}
    .admin-notification-item__dot{width:11px;height:11px;margin-top:5px;border-radius:999px;background:#0B5ED7}
    .admin-notification-item.is-read .admin-notification-item__dot{background:#94A3B8}
    .admin-notification-item strong{display:block;color:#061E5C;font-size:15px;line-height:1.28;font-weight:900}
    .admin-notification-item p{margin:4px 0 0;color:#31517D;font-size:13px;font-weight:700;line-height:1.45}
    .admin-notification-item time{color:#082F59;font-size:13px;font-weight:900;text-align:right;white-space:nowrap}
    .admin-home-empty{display:grid;gap:4px;padding:22px;border:1px dashed #CBD5E1;border-radius:10px;background:#fff;text-align:center}
    .admin-home-empty--compact{padding:18px}
    .admin-home-empty strong{color:#082F59;font-weight:900}
    .admin-home-empty span{color:#526987;font-weight:700}
    .admin-home-content a:focus-visible,.admin-home-content button:focus-visible{outline:3px solid rgba(11,94,215,.2);outline-offset:3px}
    .admin-home-view-all:hover,.admin-home-primary:hover,.admin-announcement-item__actions a:hover{border-color:#0B5ED7;color:#0B5ED7;background:#EAF2FF}
    .admin-home-primary:hover{color:#fff;background:#084BAE}
    .admin-announcement-item__actions button:hover{background:#FEE2E2}
    @media (max-width:1100px){.admin-welcome{grid-template-columns:minmax(0,64%) minmax(260px,36%);height:auto;max-height:none}.admin-welcome__main{padding:26px 32px}.admin-welcome h1{font-size:clamp(27px,3vw,36px)}.admin-welcome__divider{left:calc(64% - 38px)}.admin-home-grid{grid-template-columns:1fr;align-items:start}.admin-welcome__role{justify-content:flex-start}}
    @media (max-width:900px){.admin-welcome{grid-template-columns:1fr}.admin-welcome__main{clip-path:none}.admin-welcome__divider{display:none}}
    @media (max-width:680px){.admin-home-content{padding:18px}.admin-welcome__main,.admin-welcome__role,.admin-home-panel{padding:20px}.admin-welcome h1{max-width:100%;font-size:28px;white-space:normal}.admin-home-panel__header,.admin-home-panel__header--row{align-items:stretch;flex-direction:column}.admin-home-primary,.admin-home-view-all{width:100%}.admin-announcement-item__top{align-items:flex-start;flex-direction:column}.admin-announcement-item__actions,.admin-announcement-item__actions form{width:100%}.admin-announcement-item__actions a,.admin-announcement-item__actions button{width:100%}}
</style>
@endpush
