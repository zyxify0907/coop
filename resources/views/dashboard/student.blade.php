@extends('layouts.app')

@section('title', 'Laman Utama Pelajar')
@section('page-title', 'Laman Utama Pelajar')
@section('page-subtitle', 'Ringkasan dan tindakan utama untuk pelajar CoopBest.')

@section('content')
    @php
        $categoryClass = fn ($category) => match (strtolower((string) $category)) {
            'important' => 'important',
            'reminder' => 'reminder',
            'update' => 'update',
            default => 'general',
        };
        $categoryLabel = fn ($category) => match (strtolower((string) $category)) {
            'important' => 'Peringatan',
            'reminder' => 'Peringatan',
            'update' => 'Makluman',
            default => 'Makluman',
        };
        $isMember = $memberApplication !== null;
        $studentDepartment = $user->jabatan ?? $user->program ?? 'Jabatan belum ditetapkan';
        $studentClass = $user->kelas ?? 'Kelas belum ditetapkan';
        $studentStatus = $isMember ? 'Pelajar / Anggota Koperasi' : 'Pelajar';
        $memberNo = $user->no_anggota ?? ($memberApplication ? ($memberApplication->data_permohonan['no_anggota'] ?? null) : null);
        $studentMessages = collect($home['messages'] ?? [])->take(3);
        $recentActivities = collect($home['recent_activities'] ?? [])->take(3);
    @endphp

    <div class="student-home-redesign">
        <section class="student-welcome-banner">
            <div class="student-welcome-banner__left">
                <img class="student-welcome-banner__watermark" src="{{ asset('images/koperasi-logo.svg') }}" alt="" aria-hidden="true">
                <span class="student-home-redesign__mark"></span>
                <span class="student-welcome-banner__kicker">PORTAL PELAJAR COOPBEST</span>
                <h1>Selamat Datang, {{ $user->nama }}</h1>
                <p>{{ $studentDepartment }} - {{ $studentClass }}</p>
            </div>
            <span class="student-welcome-banner__divider" aria-hidden="true"></span>
            <div class="student-welcome-banner__right">
                <span class="student-welcome-banner__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21a8 8 0 0 0-16 0"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </span>
                <div>
                    <span class="student-welcome-banner__status-label">Status Pengguna</span>
                    <strong>{{ $studentStatus }}</strong>
                    <small>No. Anggota: {{ $memberNo ?: 'Belum Aktif' }}</small>
                </div>
            </div>
        </section>

        <section class="student-content-grid">
            <article class="student-home-panel student-home-announcements">
                <header class="student-home-panel__header">
                    <div>
                        <span class="student-home-redesign__mark"></span>
                        <h2>Pengumuman Terkini</h2>
                        <p>Maklumat dan pengumuman penting daripada pihak CoopBest.</p>
                    </div>
                </header>

                <div class="student-announcement-list">
                    @forelse ($home['announcements'] as $announcement)
                        @php
                            $announcementTitle = trim((string) $announcement->title);
                            if (in_array(strtolower($announcementTitle), ['student baru', 'student baharu'], true)) {
                                $announcementTitle = 'Pendaftaran Anggota Baharu Dibuka';
                            }
                        @endphp
                        <article class="student-announcement-item">
                            <div class="student-announcement-item__body">
                                <span class="student-home-pill {{ $categoryClass($announcement->category) }}">{{ $categoryLabel($announcement->category) }}</span>
                                <h3>{{ $announcementTitle ?: 'Pengumuman CoopBest' }}</h3>
                                <p>{{ \Illuminate\Support\Str::limit(strip_tags((string) $announcement->body), 135) }}</p>
                                <div class="student-announcement-item__meta">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M8 2v4"/>
                                        <path d="M16 2v4"/>
                                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                                        <path d="M3 10h18"/>
                                    </svg>
                                    <time datetime="{{ $announcement->created_at?->toDateString() }}">{{ $announcement->created_at?->format('d/m/Y') ?? '-' }}</time>
                                    <span>Oleh {{ $announcement->announcer_name }}</span>
                                </div>
                            </div>
                            <a class="student-announcement-item__link" href="{{ route('announcements.index') }}">
                                <span>Baca Selanjutnya</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 12h14"/>
                                    <path d="m12 5 7 7-7 7"/>
                                </svg>
                            </a>
                        </article>
                    @empty
                        <div class="student-home-empty">
                            <strong>Tiada pengumuman terkini.</strong>
                            <span>Pengumuman koperasi akan dipaparkan di sini.</span>
                        </div>
                    @endforelse
                </div>
            </article>

            <div class="student-home-side">
                <aside class="student-home-panel student-home-panel--compact">
                    <header class="student-home-panel__header student-home-panel__header--row">
                        <div>
                            <span class="student-home-redesign__mark"></span>
                            <h2>Notifikasi</h2>
                            <p>Maklumat terkini berkaitan akaun anda.</p>
                        </div>
                        <a class="student-home-view-all" href="{{ route('notifications.index') }}">Lihat Semua</a>
                    </header>
                    <div class="student-notice-list">
                        @forelse ($studentMessages as $notification)
                            <a class="student-notice-item {{ $notification->read_at ? 'is-read' : 'is-unread' }}" href="{{ $notification->link ?: route('notifications.index') }}">
                                <span class="student-notice-item__dot"></span>
                                <div>
                                    <strong>{{ $notification->title }}</strong>
                                    <p>{{ \Illuminate\Support\Str::limit((string) ($notification->message ?? 'Tiada makluman tambahan.'), 85) }}</p>
                                </div>
                                <time datetime="{{ $notification->created_at?->toDateString() }}">{{ $notification->created_at?->format('d/m/Y, h:i A') ?? '-' }}</time>
                            </a>
                        @empty
                            <div class="student-home-empty">
                                <strong>Tiada notifikasi terkini.</strong>
                                <span>Notifikasi baharu akan dipaparkan di sini.</span>
                            </div>
                        @endforelse
                    </div>
                </aside>

                <aside class="student-home-panel student-home-panel--compact">
                    <header class="student-home-panel__header">
                        <div>
                            <span class="student-home-redesign__mark"></span>
                            <h2>Aktiviti Terkini</h2>
                            <p>Rekod aktiviti terbaru anda dalam sistem.</p>
                        </div>
                    </header>
                    <div class="student-activity-list">
                        @forelse ($recentActivities as $activity)
                            <a class="student-activity-item" href="{{ $activity['route'] ?? route('auth.dashboard') }}">
                                <span class="student-activity-item__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <path d="M14 2v6h6"/>
                                        <path d="M9 15h6"/>
                                        <path d="M9 11h2"/>
                                    </svg>
                                </span>
                                <div>
                                    <strong>{{ $activity['name'] }}</strong>
                                    <p>{{ $activity['detail'] ?? 'Aktiviti' }}</p>
                                </div>
                                <time>{{ $activity['time'] ?? '-' }}</time>
                            </a>
                        @empty
                            <div class="student-home-empty">
                                <strong>Tiada aktiviti terkini.</strong>
                                <span>Permohonan, tempahan dan notifikasi pelajar akan muncul di sini.</span>
                            </div>
                        @endforelse
                    </div>
                </aside>
            </div>
        </section>
    </div>
@endsection

@push('styles')
<style>
    .content > .student-home-redesign{max-width:1800px}
    .content:has(> .student-home-redesign){background:#F5F8FC;padding:clamp(18px,2.2vw,28px) clamp(24px,3vw,56px) 28px}
    .student-home-redesign{display:grid;gap:18px;color:#082F59}
    .student-welcome-banner{position:relative;display:grid;grid-template-columns:minmax(0,68%) minmax(270px,32%);height:clamp(154px,13.5vw,190px);max-height:190px;border:1px solid #D8E2EF;border-radius:12px;background:#fff;overflow:hidden}
    .student-welcome-banner__left{position:relative;display:flex;flex-direction:column;justify-content:center;padding:32px 40px;background:#082F59;color:#fff;clip-path:polygon(0 0,calc(100% - 30px) 0,100% 100%,0 100%);overflow:hidden}
    .student-welcome-banner__watermark{position:absolute;left:-18px;bottom:-46px;width:min(260px,28vw);opacity:.04;pointer-events:none;filter:grayscale(1) brightness(3)}
    .student-home-redesign__mark{display:block;width:42px;height:4px;margin-bottom:12px;background:#ED1C2E}
    .student-welcome-banner__kicker,.student-welcome-banner__status-label{color:inherit;font-size:12px;font-weight:900;letter-spacing:.1em;text-transform:uppercase}
    .student-welcome-banner h1{position:relative;z-index:1;max-width:calc(100% - 34px);margin:6px 0 0;color:#fff;font-size:clamp(30px,2.3vw,42px);line-height:1.1;font-weight:900;letter-spacing:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .student-welcome-banner p{position:relative;z-index:1;margin:6px 0 0;color:rgba(255,255,255,.92);font-size:clamp(15px,1.1vw,18px);font-weight:800}
    .student-welcome-banner__divider{position:absolute;top:0;bottom:0;left:calc(68% - 38px);width:40px;background:#ED1C2E;clip-path:polygon(0 0,10px 0,40px 100%,30px 100%);pointer-events:none;z-index:2}
    .student-welcome-banner__right{display:flex;align-items:center;justify-content:center;gap:16px;padding:24px 30px;background:#fff;color:#082F59}
    .student-welcome-banner__icon{flex:0 0 auto;width:clamp(42px,3.5vw,54px);height:clamp(42px,3.5vw,54px);color:#061E5C}
    .student-welcome-banner__icon svg{width:100%;height:100%}
    .student-welcome-banner__right strong{display:block;margin-top:4px;color:#061E5C;font-size:clamp(17px,1.25vw,22px);line-height:1.18;font-weight:900}
    .student-welcome-banner__right small{display:block;margin-top:5px;color:#2453A6;font-size:13px;font-weight:900}
    .student-content-grid{display:grid;grid-template-columns:minmax(0,2fr) minmax(340px,1fr);gap:24px;align-items:stretch}
    .student-home-side{display:grid;gap:20px;align-content:start}
    .student-home-panel{border:1px solid #D8E2EF;border-radius:12px;background:#fff;box-shadow:0 8px 20px rgba(8,47,89,.035);padding:24px 28px}
    .student-home-panel--compact{padding:20px 22px}
    .student-home-panel__header{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:12px}
    .student-home-panel__header .student-home-redesign__mark{width:30px;height:4px;margin-bottom:7px}
    .student-home-panel h2{margin:0;color:#082F59;font-size:clamp(22px,1.65vw,28px);line-height:1.16;font-weight:900;letter-spacing:0}
    .student-home-panel p{margin:3px 0 0;color:#31517D;font-size:14px;line-height:1.35;font-weight:600}
    .student-announcement-list,.student-notice-list,.student-activity-list{display:grid;gap:10px}
    .student-announcement-item{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:18px;align-items:center;min-height:112px;padding:14px 18px;border:1px solid #D8E2EF;border-radius:10px;background:#fbfdff}
    .student-announcement-item h3{margin:7px 0 0;color:#061E5C;font-size:clamp(17px,1.25vw,21px);line-height:1.2;font-weight:900;letter-spacing:0}
    .student-home-pill{display:inline-flex;align-items:center;justify-content:center;min-height:26px;width:max-content;padding:0 12px;border-radius:999px;font-size:12px;font-weight:900}
    .student-home-pill.important,.student-home-pill.reminder{background:#fee2e2;color:#b91c1c}
    .student-home-pill.update{background:#dcfce7;color:#15803d}
    .student-home-pill.general{background:#dbeafe;color:#1d4ed8}
    .student-announcement-item__meta{display:flex;align-items:center;gap:10px;margin-top:8px;color:#2453A6;font-size:13px;font-weight:700}
    .student-announcement-item__meta svg{width:18px;height:18px;flex:0 0 auto}
    .student-announcement-item__link,.student-home-view-all{display:inline-flex;align-items:center;justify-content:center;gap:9px;min-height:40px;border:1px solid #9ec5ff;border-radius:6px;background:#fff;color:#0b63f6;text-decoration:none;font-weight:900;white-space:nowrap}
    .student-announcement-item__link{min-width:172px;padding:0 16px}
    .student-announcement-item__link svg{width:18px;height:18px}
    .student-home-view-all{min-width:106px;padding:0 12px;font-size:13px}
    .student-notice-item,.student-activity-item{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:10px;align-items:start;padding:9px 0;border-bottom:1px solid #D8E2EF;color:#082F59;text-decoration:none}
    .student-notice-item:last-child,.student-activity-item:last-child{border-bottom:0}
    .student-notice-item__dot{width:12px;height:12px;margin-top:4px;border-radius:999px;background:#0b63f6}
    .student-notice-item.is-read .student-notice-item__dot{background:#94a3b8}
    .student-notice-item.is-unread:nth-child(3n + 1) .student-notice-item__dot{background:#f59e0b}
    .student-notice-item.is-unread:nth-child(3n) .student-notice-item__dot{background:#0f9f5f}
    .student-notice-item strong,.student-activity-item strong{display:block;color:#061E5C;font-size:14px;line-height:1.25;font-weight:900}
    .student-notice-item p,.student-activity-item p{margin-top:2px;font-size:12px;color:#31517D;font-weight:600}
    .student-notice-item time,.student-activity-item time{color:#31517D;font-size:12px;font-weight:700;text-align:right;white-space:nowrap}
    .student-activity-item{grid-template-columns:30px minmax(0,1fr) auto}
    .student-activity-item__icon{display:grid;place-items:center;width:26px;height:26px;color:#061E5C}
    .student-activity-item__icon svg{width:24px;height:24px}
    .student-home-empty{display:grid;gap:4px;padding:18px;border:1px dashed #cbd5e1;border-radius:10px;background:#fff;text-align:center}
    .student-home-empty strong{color:#082F59;font-weight:900}
    .student-home-empty span{color:#64748B;font-weight:700}
    .student-home-redesign a:focus-visible{outline:3px solid rgba(37,99,235,.2);outline-offset:3px}
    .student-announcement-item__link:hover,.student-home-view-all:hover,.student-notice-item:hover,.student-activity-item:hover{border-color:#8cb9f5;color:#0b63f6}
    @media (max-width:1100px){.student-welcome-banner{grid-template-columns:minmax(0,64%) minmax(260px,36%);height:auto;max-height:none}.student-welcome-banner__left{padding:26px 32px}.student-welcome-banner h1{font-size:clamp(27px,3vw,36px)}.student-welcome-banner__divider{left:calc(64% - 38px)}.student-content-grid{grid-template-columns:1fr}.student-home-side{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width:900px){.student-welcome-banner{grid-template-columns:1fr}.student-welcome-banner__left{clip-path:none}.student-welcome-banner__divider{display:none}.student-welcome-banner__right{justify-content:flex-start}.student-home-side{grid-template-columns:1fr}}
    @media (max-width:680px){.student-home-redesign{gap:14px}.student-welcome-banner__left,.student-welcome-banner__right,.student-home-panel{padding:20px}.student-welcome-banner__right,.student-home-panel__header,.student-home-panel__header--row{align-items:flex-start;flex-direction:column}.student-announcement-item,.student-notice-item,.student-activity-item{grid-template-columns:1fr}.student-announcement-item__link,.student-home-view-all{width:100%}.student-notice-item time,.student-activity-item time{text-align:left}}
</style>
@endpush
