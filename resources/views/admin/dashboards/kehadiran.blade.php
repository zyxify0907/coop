@extends('layouts.app')

@section('title', 'Dashboard Kehadiran')
@section('page-title', 'Dashboard Kehadiran')
@section('page-subtitle', 'Ringkasan Smart Attendance pekerja koperasi.')

@section('content')
    @php
        $formatDuration = function (?int $minutes): string {
            $minutes = max(0, (int) $minutes);

            return intdiv($minutes, 60).' jam '.($minutes % 60).' minit';
        };
    @endphp

    <div class="module-dashboard attendance-dashboard">
        <section class="module-hero">
            <div>
                <span>DASHBOARD KEHADIRAN</span>
                <h1>Dashboard Kehadiran</h1>
                <p>Pantau ringkasan hari ini, status live pekerja, jam kerja dan anggaran elaun bulan semasa.</p>
            </div>
            <a href="{{ route('admin.attendance.live') }}">Buka Live Attendance</a>
        </section>

        <section class="module-panel attendance-summary-panel">
            <header>
                <div>
                    <span>RINGKASAN HARI INI</span>
                    <h2>Ringkasan Hari Ini</h2>
                </div>
                <a href="{{ route('admin.attendance.records') }}">Lihat Rekod</a>
            </header>
            <div class="attendance-summary-grid">
                <div><span>Jumlah Pekerja</span><strong>{{ number_format($summary['total']) }}</strong><small>Pekerja koperasi aktif</small></div>
                <div><span>Sudah Check-In</span><strong>{{ number_format($summary['checked_in']) }}</strong><small>{{ number_format($summary['present']) }} hadir</small></div>
                <div><span>Belum Check-In</span><strong>{{ number_format($summary['not_checked_in']) }}</strong><small>Belum ada rekod hari ini</small></div>
                <div><span>Lewat</span><strong>{{ number_format($summary['late']) }}</strong><small>Check-in selepas masa dibenarkan</small></div>
                <div><span>Tidak Hadir</span><strong>{{ number_format(max($summary['absent'], $summary['explicit_absent'])) }}</strong><small>Tiada rekod / ditanda absent</small></div>
                <div><span>Sedang Bekerja</span><strong>{{ number_format($summary['working']) }}</strong><small>{{ number_format($summary['checked_out']) }} sudah check-out</small></div>
            </div>
        </section>

        <section class="module-grid {{ $role === 'admin' ? 'attendance-live-correction-grid' : 'module-grid--full' }}">
            <article class="module-panel attendance-live-panel">
                <header>
                    <div>
                        <span>STATUS LIVE</span>
                        <h2>Status Kehadiran Live</h2>
                    </div>
                    <a href="{{ route('admin.attendance.live') }}">Lihat Semua</a>
                </header>
                <div class="attendance-live-list">
                    @forelse ($liveAttendance as $row)
                        @php
                            $record = $row['record'];
                            $isCheckedOut = filled($record?->check_out_time);
                            $statusLabel = $isCheckedOut ? 'Balik' : $row['label'];
                            $statusClass = $isCheckedOut ? 'checked-out' : $row['status'];
                        @endphp
                        <div class="attendance-live-row">
                            <div class="attendance-live-row__person">
                                <strong>{{ $row['worker']->nama }}</strong>
                                <small>{{ $row['worker']->no_pekerja ?? '-' }}</small>
                            </div>
                            <div><span>Check-In</span><strong>{{ $record?->check_in_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</strong></div>
                            <div><span>Check-Out</span><strong>{{ $record?->check_out_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</strong></div>
                            <em class="attendance-live-status attendance-live-status--{{ $statusClass }}">{{ $statusLabel }}</em>
                        </div>
                    @empty
                        <div class="module-empty">Belum ada data pekerja untuk hari ini.</div>
                    @endforelse
                </div>
            </article>

            @if ($role === 'admin')
                <article class="module-panel">
                    <header><div><span>CORRECTION</span><h2>Pembetulan Pending</h2></div><a href="{{ route('admin.attendance.corrections') }}">Semak</a></header>
                    <div class="module-list">
                        @forelse ($pendingCorrections as $correction)
                            <a class="module-row" href="{{ route('admin.attendance.corrections') }}">
                                <div><strong>{{ $correction->staff?->nama ?? 'Pekerja' }}</strong><small>{{ $correction->correction_date?->format('d/m/Y') }} · {{ ucwords(str_replace('_', ' ', $correction->correction_type)) }}</small></div>
                                <span>{{ ucfirst($correction->status) }}</span>
                            </a>
                        @empty
                            <div class="module-empty">Tiada pembetulan pending.</div>
                        @endforelse
                    </div>
                </article>
            @endif
        </section>
    </div>
@endsection

@include('admin.dashboards.styles')

@push('styles')
<style>
    .attendance-dashboard .module-hero{margin-bottom:0}
    .attendance-live-correction-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    .attendance-summary-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px}
    .attendance-summary-grid div{display:grid;gap:7px;min-height:118px;padding:17px;border:1px solid #D7E3F5;border-radius:12px;background:#F8FBFF}
    .attendance-summary-grid span,.attendance-live-row span{color:#526987;font-size:11px;font-weight:900;letter-spacing:.05em;text-transform:uppercase}
    .attendance-summary-grid strong{color:#061B34;font-size:28px;font-weight:900;line-height:1}
    .attendance-summary-grid small{color:#64748B;font-weight:800}
    .attendance-live-list{display:grid;gap:10px;max-height:560px;overflow:auto;padding-right:2px}
    .attendance-live-row{display:grid;grid-template-columns:minmax(150px,1fr) repeat(2,minmax(78px,.5fr));align-items:center;gap:12px;padding:13px 14px;border:1px solid #D7E3F5;border-radius:12px;background:#F8FBFF}
    .attendance-live-row__person strong,.attendance-live-row div strong{display:block;color:#061B34;font-weight:900}
    .attendance-live-row__person small{display:block;margin-top:3px;color:#64748B;font-weight:800}
    .attendance-live-status{display:inline-flex;align-items:center;justify-content:center;min-height:32px;padding:0 12px;border-radius:999px;background:#E8F0FF;color:#1D4ED8;font-size:12px;font-style:normal;font-weight:900;white-space:nowrap}
    .attendance-live-status--working{background:#DDF7E8;color:#0D8A4B}
    .attendance-live-status--late{background:#FFF4DB;color:#B45309}
    .attendance-live-status--not_checked_in,.attendance-live-status--absent{background:#FDE8E8;color:#E00012}
    .attendance-live-status--checked-out,.attendance-live-status--present{background:#E8F0FF;color:#1D4ED8}
    .attendance-live-status{grid-column:1 / -1;justify-self:start}
    @media (max-width:1280px){.attendance-summary-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @media (max-width:1000px){.attendance-live-correction-grid{grid-template-columns:1fr}}
    @media (max-width:760px){.attendance-summary-grid{grid-template-columns:1fr}.attendance-live-row{grid-template-columns:1fr}.attendance-live-status{grid-column:auto}}
</style>
@endpush
