@extends('layouts.app')
@section('title', 'Smart Attendance')
@section('page-title', 'Smart Attendance')
@section('page-subtitle', 'Pantau kehadiran Pekerja Koperasi dan semak isu yang memerlukan tindakan.')
@section('content')
    @include('attendance.partials.styles')
    @php $labels = ['present' => 'Hadir', 'late' => 'Lewat', 'early_leave' => 'Keluar Awal', 'missing_checkout' => 'Tiada Check Out']; @endphp
    <div class="attendance-page">
        <header class="attendance-head"><div><h1>Smart Attendance</h1><p>Ringkasan kehadiran Pekerja Koperasi bagi hari ini.</p></div><div class="attendance-head__meta">Hari ini<strong>{{ now()->translatedFormat('d F Y') }}</strong></div></header>
        <section class="attendance-metrics"><div class="attendance-metric"><span>Jumlah Pekerja</span><strong>{{ $summary['total'] }}</strong></div><div class="attendance-metric"><span>Hadir</span><strong>{{ $summary['present'] }}</strong></div><div class="attendance-metric"><span>Lewat</span><strong>{{ $summary['late'] }}</strong></div><div class="attendance-metric"><span>Tidak Hadir</span><strong>{{ $summary['absent'] }}</strong></div><div class="attendance-metric"><span>Belum Check Out</span><strong>{{ $summary['not_checked_out'] }}</strong></div></section>
        <div class="attendance-section-grid">
            <section class="attendance-panel"><header class="attendance-panel__head"><div><h2>Rekod Hari Ini</h2><p>Kemas kini secara langsung daripada Check In dan Check Out pekerja.</p></div><a class="attendance-secondary" href="{{ route('admin.attendance.live') }}">Lihat Semua</a></header><div class="attendance-list">@forelse($todayRecords as $record)<div class="attendance-list__item"><div><strong>{{ $record->staff?->nama }}</strong><span>{{ $record->staff?->no_pekerja }} &middot; {{ $record->check_in_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }} hingga {{ $record->check_out_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</span></div><span class="attendance-badge attendance-badge--{{ $record->status }}">{{ $labels[$record->status] ?? $record->status }}</span></div>@empty<div class="attendance-empty">Belum ada rekod Check In hari ini.</div>@endforelse</div></section>
            <section class="attendance-panel"><header class="attendance-panel__head"><div><h2>Pembetulan Menunggu Semakan</h2><p>Permohonan pekerja yang memerlukan keputusan admin.</p></div><a class="attendance-secondary" href="{{ route('admin.attendance.corrections') }}">Semak</a></header><div class="attendance-list">@forelse($recentCorrections as $correction)<div class="attendance-list__item"><div><strong>{{ $correction->staff?->nama }}</strong><span>{{ $correction->correction_date->format('d/m/Y') }} &middot; {{ $correction->reason }}</span></div><span class="attendance-badge attendance-badge--pending">Pending</span></div>@empty<div class="attendance-empty">Tiada pembetulan menunggu semakan.</div>@endforelse</div></section>
        </div>
        @if (! $setting->status)<div class="attendance-note">GPS Attendance belum aktif. Lengkapkan lokasi dan aktifkan modul pada <a href="{{ route('admin.attendance.settings') }}">Tetapan Smart Attendance</a>.</div>@endif
    </div>
@endsection
