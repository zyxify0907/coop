@extends('layouts.app')

@section('title', 'Dashboard Kehadiran')
@section('page-title', 'Dashboard Kehadiran')
@section('page-subtitle', 'Ringkasan Smart Attendance pekerja koperasi.')

@section('content')
    <div class="module-dashboard">
        <section class="module-hero">
            <div>
                <span>DASHBOARD KEHADIRAN</span>
                <h1>Smart Attendance Dashboard</h1>
                <p>Pantau kehadiran hari ini, lewat, missing checkout dan pembetulan pending.</p>
            </div>
            <a href="{{ route('admin.attendance.live') }}">Buka Live Attendance</a>
        </section>

        <section class="module-metrics">
            <div><span>Jumlah Pekerja</span><strong>{{ number_format($summary['total']) }}</strong><small>Pekerja koperasi aktif</small></div>
            <div><span>Hadir Hari Ini</span><strong>{{ number_format($summary['present']) }}</strong><small>{{ number_format($summary['late']) }} lewat</small></div>
            <div><span>Belum Check Out</span><strong>{{ number_format($summary['not_checked_out']) }}</strong><small>{{ number_format($summary['absent']) }} belum hadir</small></div>
            <div><span>Pembetulan Pending</span><strong>{{ number_format($summary['corrections']) }}</strong><small>{{ number_format($summary['early_leave']) }} keluar awal</small></div>
        </section>

        <section class="module-grid module-grid--full">
            <article class="module-panel">
                <header><div><span>CARTA</span><h2>Status Kehadiran Hari Ini</h2></div></header>
                @include('admin.dashboards.partials.bar-chart', ['items' => $charts['today'], 'money' => false])
            </article>
        </section>

        <section class="module-grid">
            <article class="module-panel">
                <header><div><span>ATTENDANCE</span><h2>Rekod Terkini</h2></div><a href="{{ route('admin.attendance.records') }}">Lihat Rekod</a></header>
                <div class="module-list">
                    @forelse ($recentRecords as $record)
                        <a class="module-row" href="{{ route('admin.attendance.show', $record) }}">
                            <div><strong>{{ $record->staff?->nama ?? 'Pekerja' }}</strong><small>{{ $record->attendance_date?->format('d/m/Y') }} · {{ $record->check_in_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</small></div>
                            <span>{{ $labels[$record->status] ?? $record->status }}</span>
                        </a>
                    @empty
                        <div class="module-empty">Tiada rekod kehadiran lagi.</div>
                    @endforelse
                </div>
            </article>

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
        </section>
    </div>
@endsection

@include('admin.dashboards.styles')
