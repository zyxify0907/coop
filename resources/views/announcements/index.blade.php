@extends('layouts.app')

@section('title', 'Announcement')
@section('page-title', 'Announcement')
@section('page-subtitle', 'Semua pengumuman rasmi koperasi dipaparkan di sini sahaja.')

@section('content')
    <div class="announcement-wrap">
        <section class="announcement-hero">
            <div>
                <span class="announcement-kicker">ANNOUNCEMENT</span>
                <h1>Pengumuman Koperasi</h1>
                <p>Rujuk notis rasmi yang aktif tanpa mengganggu paparan dashboard utama.</p>
            </div>
            <span class="announcement-count">{{ $announcements->total() }} rekod</span>
        </section>

        <section class="announcement-list">
            @forelse ($announcements as $announcement)
                <article class="announcement-card {{ $announcement->is_pinned ? 'is-pinned' : '' }}">
                    <div class="announcement-card__head">
                        <div>
                            <span class="announcement-category">{{ $announcement->category }}</span>
                            <h2>{{ $announcement->title }}</h2>
                        </div>
                        <div class="announcement-badges">
                            @if ($announcement->is_pinned)
                                <span class="announcement-badge pinned">Pinned</span>
                            @endif
                            <span class="announcement-badge">{{ $announcement->audience_label }}</span>
                        </div>
                    </div>
                    <p>{{ $announcement->body }}</p>
                    <footer>
                        <span>Diumumkan oleh: {{ $announcement->announcer_name }}</span>
                        <span>Mula: {{ $announcement->starts_at?->format('d/m/Y') ?? '-' }}</span>
                        <span>Tamat: {{ $announcement->ends_at?->format('d/m/Y') ?? '-' }}</span>
                    </footer>
                </article>
            @empty
                <div class="announcement-empty">
                    <strong>Tiada announcement aktif.</strong>
                    <span>Pengumuman baru akan dipaparkan di sini bila admin aktifkan.</span>
                </div>
            @endforelse
        </section>

        @if ($announcements->hasPages())
            <div class="pagination">{{ $announcements->links() }}</div>
        @endif
    </div>
@endsection

@push('styles')
<style>
    .announcement-wrap{display:grid;gap:18px}
    .announcement-hero{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;padding:28px 30px;border:1px solid var(--line);border-radius:18px;background:#fff;box-shadow:0 14px 34px rgba(15,23,42,.055)}
    .announcement-kicker{display:inline-flex;margin-bottom:10px;color:var(--secondary);font-size:12px;font-weight:900;letter-spacing:.08em}
    .announcement-hero h1{margin:0;color:var(--text);font-size:30px;line-height:1.15;font-weight:900}
    .announcement-hero p{margin:8px 0 0;color:var(--muted);font-weight:700}
    .announcement-count{display:inline-flex;align-items:center;justify-content:center;min-height:34px;padding:0 14px;border-radius:999px;background:var(--secondary-soft);color:var(--secondary);font-weight:900}
    .announcement-list{display:grid;gap:14px}
    .announcement-card{padding:22px 24px;border:1px solid var(--line);border-radius:16px;background:#fff;box-shadow:0 10px 26px rgba(15,23,42,.045)}
    .announcement-card.is-pinned{border-color:#BFDBFE;background:#F8FBFF}
    .announcement-card__head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
    .announcement-category{color:var(--primary);font-size:12px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
    .announcement-card h2{margin:4px 0 0;color:var(--text);font-size:20px;line-height:1.25;font-weight:900}
    .announcement-card p{margin:14px 0 0;color:var(--ink);font-size:15px;font-weight:650;white-space:pre-line}
    .announcement-card footer{display:flex;gap:14px;flex-wrap:wrap;margin-top:18px;padding-top:14px;border-top:1px solid var(--line);color:var(--muted-2);font-size:13px;font-weight:800}
    .announcement-badges{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}
    .announcement-badge{display:inline-flex;align-items:center;justify-content:center;border-radius:999px;background:var(--secondary-soft);color:var(--secondary);padding:6px 10px;font-size:12px;font-weight:900}
    .announcement-badge.pinned{background:var(--warning-soft);color:var(--warning)}
    .announcement-empty{display:grid;gap:4px;padding:34px;border:1px dashed var(--line-strong);border-radius:16px;background:#fff;color:var(--muted);text-align:center}
    .announcement-empty strong{color:var(--text);font-size:18px}
    @media (max-width:720px){.announcement-hero,.announcement-card__head{align-items:stretch;flex-direction:column}.announcement-badges{justify-content:flex-start}}
</style>
@endpush
