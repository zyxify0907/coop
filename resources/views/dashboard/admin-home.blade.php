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

    <div class="admin-home">
        <section class="home-welcome">
            <div>
                <span class="home-kicker">PUSAT KAWALAN COOPBEST</span>
                <h1>Selamat Datang Kembali, {{ $user->nama ?? 'Admin' }}</h1>
                <p>Sistem Pengurusan Koperasi CoopBest</p>
            </div>
            <div class="home-login-card">
                <span>Peranan</span>
                <strong>Admin / Pentadbir Sistem</strong>
                <small>Log masuk terakhir: {{ $lastLogin }}</small>
            </div>
        </section>

        <section class="home-grid home-grid--main">
            <article class="home-panel announcements-panel">
                <header class="home-panel__head">
                    <div>
                        <span>PENGUMUMAN</span>
                        <h2>Pusat Pengumuman</h2>
                    </div>
                    <a class="home-primary" href="{{ route('admin.announcements.create') }}">Tambah</a>
                </header>
                <div class="announcement-stack">
                    @forelse ($home['announcements'] as $announcement)
                        <div class="home-announcement">
                            <div class="home-announcement__top">
                                <span class="category-pill {{ $categoryClass($announcement->category) }}">{{ $categoryLabel($announcement->category) }}</span>
                                <span class="status-pill {{ $announcement->is_active ? 'active' : 'inactive' }}">{{ $announcement->status_label }}</span>
                            </div>
                            <h3>{{ $announcement->title }}</h3>
                            <p>{{ $announcement->body }}</p>
                            <div class="home-announcement__meta">
                                <span>Dicipta oleh {{ $announcement->created_by ? 'Admin #'.$announcement->created_by : 'Admin' }}</span>
                                <span>{{ $announcement->created_at?->format('d/m/Y') ?? '-' }}</span>
                                <span>Tamat: {{ $announcement->ends_at?->format('d/m/Y') ?? '-' }}</span>
                            </div>
                            <div class="home-announcement__actions">
                                <a href="{{ route('admin.announcements.edit', $announcement) }}">Kemaskini</a>
                                <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Padam pengumuman ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Padam</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="home-empty">
                            <strong>Tiada pengumuman lagi.</strong>
                            <span>Tambah pengumuman untuk paparan rasmi admin dan pengguna.</span>
                        </div>
                    @endforelse
                </div>
            </article>

            <aside class="home-panel notifications-panel">
                <header class="home-panel__head">
                    <div>
                        <span>NOTIFIKASI</span>
                        <h2>Senarai Notifikasi</h2>
                    </div>
                    <a class="mini-action" href="{{ route('notifications.index') }}">Lihat Semua</a>
                </header>
                <div class="notification-list">
                    @forelse ($home['notifications'] as $notification)
                        <a class="notification-item {{ $notification->read_at ? 'is-read' : 'is-unread' }}" href="{{ $notification->link ?: route('notifications.index') }}">
                            <span class="notification-dot"></span>
                            <div>
                                <strong>{{ $notification->title }}</strong>
                                <p>{{ $notification->message ?? 'Tiada makluman tambahan.' }}</p>
                                <small>{{ $notification->created_at?->format('d/m/Y H:i') }}</small>
                            </div>
                        </a>
                    @empty
                        <div class="home-empty compact">
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
    .admin-home{display:grid;gap:20px;color:var(--text)}
    .home-welcome{display:flex;align-items:flex-end;justify-content:space-between;gap:22px;padding:30px;border:1px solid var(--line);border-left:5px solid var(--secondary);border-radius:18px;background:#fff;box-shadow:0 16px 40px rgba(15,23,42,.06)}
    .home-kicker,.home-panel__head span{display:inline-flex;color:var(--secondary);font-size:12px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
    .home-welcome h1{margin:8px 0 0;color:#0F172A;font-size:34px;line-height:1.15;font-weight:900;letter-spacing:0}
    .home-welcome p{margin:8px 0 0;color:#334155;font-size:16px;font-weight:800}
    .home-login-card{display:grid;gap:4px;min-width:260px;padding:16px 18px;border:1px solid #D7E3F5;border-radius:12px;background:#F8FBFF;text-align:right}
    .home-login-card span{color:#64748B;font-size:12px;font-weight:900;text-transform:uppercase}
    .home-login-card strong{color:#14213D;font-size:16px;font-weight:900}
    .home-login-card small{color:#64748B;font-weight:800}
    .home-grid{display:grid;gap:18px}
    .home-grid--main{grid-template-columns:minmax(0,1.35fr) minmax(320px,.65fr)}
    .home-panel{padding:22px;border:1px solid var(--line);border-radius:16px;background:#fff;box-shadow:0 12px 30px rgba(15,23,42,.05)}
    .home-panel__head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid var(--line)}
    .home-panel__head h2{margin:4px 0 0;color:#0F172A;font-size:20px;line-height:1.25;font-weight:900}
    .home-primary,.mini-action{display:inline-flex;align-items:center;justify-content:center;min-height:40px;border-radius:8px;background:var(--secondary);color:#fff;padding:0 16px;text-decoration:none;font-weight:900}
    .mini-action{min-height:36px;background:#fff;color:var(--secondary);border:1px solid #BFD7FF}
    .announcement-stack,.notification-list{display:grid;gap:12px}
    .home-announcement{display:grid;gap:10px;padding:16px;border:1px solid #D7E3F5;border-radius:12px;background:#F8FBFF}
    .home-announcement__top,.home-announcement__meta,.home-announcement__actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
    .home-announcement__top{justify-content:space-between}
    .home-announcement h3{margin:0;color:#0F172A;font-size:17px;font-weight:900}
    .home-announcement p{margin:0;color:#334155;font-size:14px;font-weight:700;line-height:1.55;white-space:pre-line}
    .home-announcement__meta{color:#64748B;font-size:12px;font-weight:800}
    .home-announcement__actions a,.home-announcement__actions button{display:inline-flex;align-items:center;justify-content:center;min-height:36px;border:1px solid #BFD7FF;border-radius:8px;background:#fff;color:var(--secondary);padding:0 14px;text-decoration:none;font-weight:900}
    .home-announcement__actions button{border-color:#FECACA;background:#FEF2F2;color:#B91C1C}
    .category-pill,.status-pill{display:inline-flex;align-items:center;justify-content:center;border-radius:999px;padding:6px 10px;font-size:12px;font-weight:900}
    .category-pill.important{background:#FEE2E2;color:#B91C1C}
    .category-pill.reminder{background:#FEF3C7;color:#B45309}
    .category-pill.update{background:#DCFCE7;color:#15803D}
    .category-pill.general{background:#DBEAFE;color:#1D4ED8}
    .status-pill.active{background:#DCFCE7;color:#15803D}
    .status-pill.inactive{background:#FEE2E2;color:#B91C1C}
    .notification-item,.pending-item{display:grid;grid-template-columns:auto minmax(0,1fr);align-items:flex-start;gap:10px;padding:13px 14px;border:1px solid #D7E3F5;border-radius:8px;background:#fff;color:inherit;text-decoration:none}
    .notification-item:hover{border-color:#BFD7FF;background:#F8FBFF}
    .notification-item strong,.pending-item strong{display:block;color:#0F172A;font-size:14px;font-weight:900;line-height:1.4}
    .notification-item p{margin:3px 0 0;color:#475569;font-size:12px;font-weight:700;line-height:1.45}
    .notification-item small{display:block;margin-top:6px;color:#64748B;font-size:11px;font-weight:700}
    .notification-dot{width:10px;height:10px;border-radius:999px;background:#2563EB}
    .notification-item.is-read .notification-dot{background:#94A3B8}
    .pending-item{grid-template-columns:minmax(0,1fr) auto}
    .pending-item span{color:#334155;font-weight:900}
    .system-status-item{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 14px;border:1px solid var(--line);border-radius:12px;background:#fff}
    .system-status-item span{color:#334155;font-weight:900}
    .system-status-item strong{font-weight:900}
    .system-status-item strong.online{color:#15803D}
    .system-status-item strong.offline{color:#B91C1C}
    .home-empty{display:grid;gap:4px;padding:24px;border:1px dashed #CBD5E1;border-radius:12px;background:#fff;text-align:center}
    .home-empty.compact{padding:18px}
    .home-empty strong{color:#0F172A;font-weight:900}
    .home-empty span{color:#64748B;font-weight:700}
    @media (max-width:1100px){.home-grid--main{grid-template-columns:1fr}}
    @media (max-width:720px){.home-welcome,.home-panel__head{align-items:stretch;flex-direction:column}.home-login-card{text-align:left;min-width:0}.home-welcome h1{font-size:28px}.home-primary{width:100%}}
</style>
@endpush
