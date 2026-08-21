@extends('layouts.app')
@section('title', 'Live Attendance')
@section('page-title', 'Live Attendance')
@section('page-subtitle', 'Pantau kehadiran Pekerja Koperasi untuk hari ini.')
@section('content')
    @include('attendance.partials.styles')
    @php
        $labels = ['working' => 'Sedang Bekerja', 'present' => 'Hadir', 'late' => 'Lewat', 'early_leave' => 'Keluar Awal', 'missing_checkout' => 'Tiada Check Out', 'not_checked_in' => 'Belum Check In'];
        $duration = fn ($minutes) => intdiv((int) ($minutes ?? 0), 60).' jam '.((int) ($minutes ?? 0) % 60).' minit';
    @endphp
    <div class="attendance-page"><header class="attendance-head"><div><span class="attendance-kicker">SMART ATTENDANCE</span><h1>Live Attendance</h1><p>Status Check In dan Check Out pekerja koperasi untuk hari ini.</p></div><div class="attendance-head__meta"><span>Tarikh Semasa</span><strong>{{ now()->format('d/m/Y') }}</strong></div></header><nav class="attendance-nav"><a href="{{ route('admin.attendance.records') }}">Rekod Kehadiran</a><a href="{{ route('admin.attendance.corrections') }}">Pembetulan</a><a href="{{ route('admin.attendance.settings') }}">Tetapan</a></nav>
        <section class="attendance-metrics"><div class="attendance-metric"><span>Jumlah Pekerja</span><strong>{{ $summary['total'] }}</strong></div><div class="attendance-metric"><span>Hadir</span><strong>{{ $summary['present'] }}</strong></div><div class="attendance-metric"><span>Lewat</span><strong>{{ $summary['late'] }}</strong></div><div class="attendance-metric"><span>Tidak Hadir</span><strong>{{ $summary['absent'] }}</strong></div><div class="attendance-metric"><span>Belum Check Out</span><strong>{{ $summary['not_checked_out'] }}</strong></div></section>
        <section class="attendance-panel"><div class="attendance-table-wrap"><table class="attendance-table"><thead><tr><th>Nama</th><th>ID Pekerja</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Waktu Bekerja</th><th>GPS</th></tr></thead><tbody>@forelse($workers as $worker)@php($record = $records->get($worker->id_pekerja))@php($state = $record ? app(\App\Services\AttendanceService::class)->displayStatus($record, $setting) : 'not_checked_in')<tr><td class="attendance-person"><strong>{{ $worker->nama }}</strong><span>{{ $worker->staff_type_label }}</span></td><td>{{ $worker->no_pekerja }}</td><td>{{ $record?->check_in_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</td><td>{{ $record?->check_out_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</td><td><span class="attendance-badge attendance-badge--{{ $state === 'not_checked_in' ? 'missing_checkout' : $state }}">{{ $labels[$state] }}</span></td><td>{{ $record?->working_minutes ? $duration($record->working_minutes) : '-' }}</td><td>{{ $record?->check_in_gps_verified ? 'Disahkan' : '-' }}</td></tr>@empty<tr><td colspan="7" class="attendance-empty">Belum ada Pekerja Koperasi aktif.</td></tr>@endforelse</tbody></table></div></section>
    </div>
@endsection
