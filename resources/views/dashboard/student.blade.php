@extends('layouts.app')

@section('title', 'Laman Utama Student')
@section('page-title', 'Laman Utama Student')
@section('page-subtitle', 'Pusat maklumat student untuk pengumuman, status permohonan dan aktiviti koperasi.')

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
        $isMember = $memberApplication !== null;
    @endphp

    <div class="portal-home">
        <section class="portal-home__welcome">
            <div>
                <span class="portal-home__kicker">PORTAL STUDENT COOPBEST</span>
                <h1>Selamat Datang, {{ $user->nama }}</h1>
                <p>{{ $user->program ?? 'Program belum ditetapkan' }} - {{ $user->kelas ?? 'Kelas belum ditetapkan' }}</p>
            </div>
            <div class="portal-home__identity">
                <span>Status</span>
                <strong>{{ $isMember ? 'Student / Ahli Koperasi' : 'Student' }}</strong>
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
                            <span>Pengumuman koperasi akan dipaparkan di sini.</span>
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
                        <h2>Aktiviti Koperasi Anda</h2>
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
                            <span>Aktiviti permohonan dan tempahan akan muncul di sini.</span>
                        </div>
                    @endforelse
                </div>
            </article>

            <aside class="portal-home__panel">
                <header>
                    <div>
                        <span>AKSES PANTAS</span>
                        <h2>Tindakan Student</h2>
                    </div>
                </header>
                <div class="portal-home__actions">
                    <a class="portal-home__action" href="{{ route('student.permohonan.index') }}"><strong>Permohonan</strong><span>Mohon anggota, tambah saham atau pengeluaran.</span></a>
                    <a class="portal-home__action" href="{{ route('student.permohonan.status') }}"><strong>Status & Slip</strong><span>Semak status permohonan dan dokumen.</span></a>
                    <a class="portal-home__action" href="{{ route('student.tempahan.index') }}"><strong>Tempahan Baju</strong><span>Lihat dan buat tempahan baju.</span></a>
                    <a class="portal-home__action" href="{{ route('student.profile') }}"><strong>Profil</strong><span>Semak maklumat akaun student.</span></a>
                </div>
            </aside>
        </section>
    </div>
@endsection

@include('components.portal-home-style')
