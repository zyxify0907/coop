@extends('layouts.app')

@section('title', 'Dashboard Kehadiran')
@section('page-title', 'Dashboard Kehadiran')
@section('page-subtitle', 'Ringkasan kehadiran pekerja koperasi.')

@section('content')
    <div class="attendance-management-dashboard">
        <section class="attendance-hero">
            <div>
                <span>DASHBOARD KEHADIRAN</span>
                <h1>Dashboard Kehadiran</h1>
                <p>Pantau ringkasan kehadiran pekerja, status check-in, waktu bekerja dan elaun bulanan.</p>
            </div>
            <a href="{{ route('admin.attendance.live') }}">Buka Kehadiran Langsung</a>
        </section>

        <section class="attendance-kpi-grid" aria-label="Ringkasan kehadiran hari ini">
            <article class="attendance-kpi attendance-kpi--blue">
                <span>Jumlah Pekerja</span>
                <strong>{{ number_format($summary['total']) }}</strong>
                <small>Pekerja koperasi aktif</small>
            </article>
            <article class="attendance-kpi attendance-kpi--green">
                <span>Sudah Check-In</span>
                <strong>{{ number_format($summary['checked_in']) }}</strong>
                <small>Hadir hari ini</small>
            </article>
            <article class="attendance-kpi attendance-kpi--orange">
                <span>Belum Check-In</span>
                <strong>{{ number_format($summary['not_checked_in']) }}</strong>
                <small>Belum rekod hari ini</small>
            </article>
            <article class="attendance-kpi attendance-kpi--orange">
                <span>Lewat</span>
                <strong>{{ number_format($summary['late']) }}</strong>
                <small>Check-in selepas masa dibenarkan</small>
            </article>
            <article class="attendance-kpi attendance-kpi--red">
                <span>Tidak Hadir</span>
                <strong>{{ number_format(max($summary['absent'], $summary['explicit_absent'])) }}</strong>
                <small>Tiada rekod / ditanda absent</small>
            </article>
            <article class="attendance-kpi attendance-kpi--blue">
                <span>Sedang Bekerja</span>
                <strong>{{ number_format($summary['working']) }}</strong>
                <small>Belum check-out</small>
            </article>
        </section>

        <section class="attendance-dashboard-grid">
            <article class="attendance-panel attendance-panel--live">
                <header>
                    <div>
                        <span>STATUS LIVE</span>
                        <h2>Status Kehadiran Live</h2>
                        <p>Status semasa pekerja hari ini.</p>
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
                        <div class="attendance-live-card">
                            <div class="attendance-person">
                                <strong>{{ $row['worker']->nama }}</strong>
                                <small>{{ $row['worker']->no_pekerja ?? '-' }}</small>
                            </div>
                            <span class="attendance-status-pill attendance-status-pill--{{ $statusClass }}">{{ $statusLabel }}</span>
                            <div class="attendance-time">
                                <span>Check-In</span>
                                <strong>{{ $record?->check_in_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</strong>
                            </div>
                            <div class="attendance-time">
                                <span>Check-Out</span>
                                <strong>{{ $record?->check_out_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</strong>
                            </div>
                        </div>
                    @empty
                        <div class="attendance-empty-state">Tiada rekod kehadiran hari ini</div>
                    @endforelse
                </div>
            </article>

            <article class="attendance-panel attendance-panel--corrections">
                <header>
                    <div>
                        <span>CORRECTION</span>
                        <h2>Pembetulan Pending</h2>
                        <p>Permohonan pembetulan kehadiran yang perlu disemak.</p>
                    </div>
                    @if ($role === 'admin')
                        <a href="{{ route('admin.attendance.corrections') }}">Semak</a>
                    @endif
                </header>

                <div class="attendance-correction-list">
                    @if ($role === 'admin')
                        @forelse ($pendingCorrections as $correction)
                            <a class="attendance-correction-card" href="{{ route('admin.attendance.corrections') }}">
                                <div>
                                    <strong>{{ $correction->staff?->nama ?? 'Pekerja' }}</strong>
                                    <small>{{ $correction->correction_date?->format('d/m/Y') ?? '-' }}</small>
                                    <span>{{ ucwords(str_replace('_', ' ', $correction->correction_type)) }}</span>
                                </div>
                                <em>{{ ucfirst($correction->status) }}</em>
                            </a>
                        @empty
                            <div class="attendance-empty-state">Tiada pembetulan pending</div>
                        @endforelse
                    @else
                        <div class="attendance-empty-state">Tiada pembetulan pending</div>
                    @endif
                </div>
            </article>
        </section>
    </div>
@endsection

@push('styles')
<style>
    .content:has(.attendance-management-dashboard){background:#F4F7FB}
    .attendance-management-dashboard{width:min(100% - clamp(24px,4vw,64px),1500px);margin:0 auto;display:grid;gap:18px;color:#061A3A}
    .attendance-hero{display:flex;align-items:center;justify-content:space-between;gap:22px;min-height:128px;padding:24px 26px 24px 30px;border:1px solid #D7E2EF;border-left:5px solid #ED1C2E;border-radius:14px;background:#fff;box-shadow:0 8px 18px rgba(8,47,89,.04)}
    .attendance-hero span,.attendance-panel header span{display:inline-flex;color:#0B5ED7;font-size:11px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
    .attendance-hero h1{margin:7px 0 0;color:#061A3A;font-size:clamp(25px,2.2vw,34px);line-height:1.1;font-weight:900;letter-spacing:0}
    .attendance-hero p{margin:8px 0 0;max-width:720px;color:#526987;font-size:14px;font-weight:750;line-height:1.5}
    .attendance-hero a,.attendance-panel header a{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 16px;border:1px solid #0B5ED7;border-radius:7px;background:#0B5ED7;color:#fff;font-size:13px;font-weight:900;text-decoration:none;white-space:nowrap}
    .attendance-panel header a{border-color:#9EC5FF;background:#fff;color:#0B5ED7}
    .attendance-hero a:hover{background:#084FB5;border-color:#084FB5}
    .attendance-panel header a:hover{background:#EAF2FF}
    .attendance-kpi-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
    .attendance-kpi{position:relative;display:grid;gap:6px;min-height:98px;padding:17px 20px 16px 24px;border:1px solid #D7E2EF;border-radius:10px;background:#fff;box-shadow:0 8px 18px rgba(8,47,89,.04);overflow:hidden}
    .attendance-kpi::before{content:"";position:absolute;inset:0 auto 0 0;width:4px;background:#0B5ED7}
    .attendance-kpi--green::before{background:#18B877}
    .attendance-kpi--orange::before{background:#F59E0B}
    .attendance-kpi--red::before{background:#ED1C2E}
    .attendance-kpi span{color:#526987;font-size:11px;font-weight:900;letter-spacing:.06em;text-transform:uppercase}
    .attendance-kpi strong{color:#061A3A;font-size:clamp(20px,1.7vw,26px);font-weight:900;line-height:1.05}
    .attendance-kpi small{color:#526987;font-size:12px;font-weight:800;line-height:1.35}
    .attendance-dashboard-grid{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(360px,.85fr);gap:18px;align-items:start}
    .attendance-panel{min-width:0;padding:22px;border:1px solid #D7E2EF;border-radius:16px;background:#fff;box-shadow:0 8px 18px rgba(8,47,89,.04)}
    .attendance-panel header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:18px;padding-bottom:15px;border-bottom:1px solid #D7E2EF}
    .attendance-panel h2{margin:5px 0 0;color:#061A3A;font-size:20px;line-height:1.2;font-weight:900;letter-spacing:0}
    .attendance-panel p{margin:6px 0 0;color:#526987;font-size:13px;font-weight:750;line-height:1.45}
    .attendance-live-list,.attendance-correction-list{display:grid;gap:12px}
    .attendance-live-list{max-height:610px;overflow:auto;padding-right:2px}
    .attendance-live-card{display:grid;grid-template-columns:minmax(180px,1fr) auto minmax(92px,.42fr) minmax(92px,.42fr);align-items:center;gap:14px;min-height:86px;padding:15px 16px;border:1px solid #D7E2EF;border-radius:14px;background:#FBFDFF}
    .attendance-person strong,.attendance-time strong,.attendance-correction-card strong{display:block;color:#061A3A;font-weight:900;line-height:1.2}
    .attendance-person small,.attendance-time span,.attendance-correction-card small,.attendance-correction-card span{display:block;color:#526987;font-size:12px;font-weight:800;line-height:1.35}
    .attendance-person small{margin-top:4px}
    .attendance-time span{margin-bottom:4px;font-size:11px;font-weight:900;letter-spacing:.05em;text-transform:uppercase}
    .attendance-status-pill,.attendance-correction-card em{display:inline-flex;align-items:center;justify-content:center;min-height:30px;min-width:104px;padding:0 12px;border-radius:999px;background:#EAF2FF;color:#0B5ED7;font-size:12px;font-style:normal;font-weight:900;white-space:nowrap}
    .attendance-status-pill--working,.attendance-status-pill--present,.attendance-status-pill--checked-out{background:#E7F6EF;color:#14875A}
    .attendance-status-pill--late{background:#FFF4DB;color:#A56500}
    .attendance-status-pill--not_checked_in,.attendance-status-pill--absent{background:#FEE2E2;color:#B91C1C}
    .attendance-correction-card{display:flex;align-items:center;justify-content:space-between;gap:16px;min-height:86px;padding:15px 16px;border:1px solid #D7E2EF;border-radius:14px;background:#FBFDFF;color:inherit;text-decoration:none}
    .attendance-correction-card:hover{border-color:#9EC5FF;background:#F6FAFF}
    .attendance-correction-card span{margin-top:2px}
    .attendance-correction-card em{min-width:82px;background:#FFF4DB;color:#A56500}
    .attendance-empty-state{display:grid;min-height:132px;place-items:center;padding:22px;border:1px dashed #BFD0E6;border-radius:14px;background:#FBFDFF;color:#526987;font-size:14px;font-weight:900;text-align:center}
    @media (max-width:1280px){.attendance-dashboard-grid{grid-template-columns:1fr}}
    @media (max-width:820px){.attendance-hero,.attendance-panel header{align-items:stretch;flex-direction:column}.attendance-hero a,.attendance-panel header a{width:100%}.attendance-kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.attendance-live-card{grid-template-columns:1fr;align-items:start}.attendance-status-pill{justify-self:start}}
    @media (max-width:560px){.attendance-management-dashboard{width:min(100% - 24px,1500px)}.attendance-kpi-grid{grid-template-columns:1fr}.attendance-panel{padding:18px}.attendance-correction-card{align-items:flex-start;flex-direction:column}}
</style>
@endpush
