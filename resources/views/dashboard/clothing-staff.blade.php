@extends('layouts.app')

@section('title', 'Laman Utama Staff Baju')
@section('page-title', 'Laman Utama Staff Baju')
@section('page-subtitle', 'Pusat maklumat staff baju untuk pengumuman, notifikasi dan tempahan.')

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
        $memberNumber = $staffMemberNumber ?? $user->no_anggota;
    @endphp

    <div class="portal-home">
        <section class="portal-home__welcome">
            <div>
                <span class="portal-home__kicker">PORTAL STAFF BAJU</span>
                <h1>Selamat Datang, {{ $user->nama }}</h1>
                <p>{{ $user->staff_type_label }} - {{ $memberNumber ?? 'Belum menjadi anggota' }}</p>
            </div>
            <div class="portal-home__identity">
                <span>Peranan</span>
                <strong>Staff Pengurusan Baju</strong>
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
                        <h2>Senarai Notifikasi</h2>
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

        <section class="portal-home__panel">
                <header>
                    <div>
                        <span>AKTIVITI TERKINI</span>
                        <h2>Aktiviti Tempahan Baju</h2>
                    </div>
                    <a class="portal-home__primary" href="{{ route('clothing-staff.orders.index') }}">Lihat Tempahan</a>
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
                            <span>Tempahan dan notifikasi akan muncul di sini.</span>
                        </div>
                    @endforelse
                </div>
        </section>
    </div>
@endsection

@include('components.portal-home-style')
