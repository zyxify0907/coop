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
    @endphp

    <div class="portal-home">
        <section class="portal-home__welcome">
            <div>
                <span class="portal-home__kicker">{{ $isCoopStaff ? 'PORTAL PEKERJA KOPERASI' : 'PORTAL STAFF COOPBEST' }}</span>
                <h1>Selamat Datang, {{ $user->nama }}</h1>
                <p>{{ $user->staff_type_label }} - {{ $user->no_pekerja }}</p>
            </div>
            <div class="portal-home__identity">
                <span>Peranan</span>
                <strong>{{ $user->staff_type_label }}</strong>
                <small>Log masuk terakhir: {{ $lastLogin }}</small>
            </div>
        </section>

        <section class="portal-home__grid portal-home__grid--main">
            <article class="portal-home__panel">
                <header>
                    <div>
                        <span>PENGUMUMAN</span>
                        <h2>Pengumuman Terkini</h2>
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

            <aside class="portal-home__panel">
                <header>
                    <div>
                        <span>NOTIFIKASI</span>
                        <h2>Pusat Notifikasi</h2>
                    </div>
                </header>
                <div class="portal-home__notifications">
                    @foreach ($home['notifications'] as $notification)
                        <a class="portal-home__notice {{ $notification['tone'] }}" href="{{ $notification['route'] }}">
                            <span class="portal-home__dot"></span>
                            <strong>{{ number_format($notification['count']) }}</strong>
                            <p>{{ $notification['label'] }}</p>
                        </a>
                    @endforeach
                </div>
            </aside>
        </section>

        <section class="portal-home__grid portal-home__grid--support">
            <article class="portal-home__panel">
                <header>
                    <div>
                        <span>AKTIVITI TERKINI</span>
                        <h2>Aktiviti Staff</h2>
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
            </article>

            <aside class="portal-home__panel">
                <header>
                    <div>
                        <span>AKSES PANTAS</span>
                        <h2>Tindakan Staff</h2>
                    </div>
                </header>
                <div class="portal-home__actions">
                    @if ($isCoopStaff)
                        <a class="portal-home__action" href="{{ route('coop-staff.attendance.index') }}"><strong>Smart Attendance</strong><span>Check in, check out dan status hari ini.</span></a>
                        <a class="portal-home__action" href="{{ route('coop-staff.attendance.history') }}"><strong>Sejarah Kehadiran</strong><span>Lihat rekod kehadiran terdahulu.</span></a>
                        <a class="portal-home__action" href="{{ route('coop-staff.attendance.corrections') }}"><strong>Pembetulan</strong><span>Hantar pembetulan kehadiran.</span></a>
                        <a class="portal-home__action" href="{{ route('coop-staff.profile') }}"><strong>Profil</strong><span>Semak maklumat akaun pekerja.</span></a>
                    @else
                        <a class="portal-home__action" href="{{ route($portalPrefix.'.permohonan.index') }}"><strong>Permohonan</strong><span>Mohon tambah saham atau pengeluaran.</span></a>
                        <a class="portal-home__action" href="{{ route($portalPrefix.'.shares') }}"><strong>Saham Saya</strong><span>Lihat rekod saham staff.</span></a>
                        <a class="portal-home__action" href="{{ route($portalPrefix.'.profile') }}"><strong>Profil</strong><span>Semak maklumat akaun staff.</span></a>
                        <a class="portal-home__action" href="{{ route('koperasi.notifications.index') }}"><strong>Notifikasi</strong><span>Lihat semua makluman koperasi.</span></a>
                    @endif
                </div>
            </aside>
        </section>
    </div>
@endsection

@include('components.portal-home-style')
