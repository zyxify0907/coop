@extends('layouts.app')

@section('title', 'Smart Attendance')
@section('page-title', 'Smart Attendance')
@section('page-subtitle', 'Rekod kehadiran masuk dan keluar kerja di koperasi.')

@section('content')
    @include('attendance.partials.styles')
    @php
        $labels = ['not_checked_in' => 'Belum Check In', 'working' => 'Sedang Bekerja', 'present' => 'Hadir', 'late' => 'Lewat', 'early_leave' => 'Keluar Awal', 'missing_checkout' => 'Tiada Check Out', 'outside_area' => 'Di Luar Kawasan'];
        $statusClass = $todayStatus === 'not_checked_in' ? 'missing_checkout' : $todayStatus;
        $duration = fn ($minutes) => intdiv((int) ($minutes ?? 0), 60).' jam '.((int) ($minutes ?? 0) % 60).' minit';
    @endphp
    <div class="attendance-page">
        <header class="attendance-head">
            <div><h1>Kehadiran Hari Ini</h1><p>Check In perlu dibuat dalam kawasan Politeknik. Check Out masih boleh direkodkan, tetapi GPS akan ditanda jika di luar kawasan.</p></div>
            <div class="attendance-head__meta">Tarikh<strong>{{ now()->timezone('Asia/Kuala_Lumpur')->translatedFormat('d F Y') }}</strong></div>
        </header>
        <div class="attendance-overview">
            <section class="attendance-panel">
                <header class="attendance-panel__head"><div><h2>Rekod Kehadiran</h2><p>Jadual kerja {{ \Carbon\Carbon::parse($setting->work_start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($setting->work_end_time)->format('h:i A') }}</p></div><span class="attendance-badge attendance-badge--{{ $statusClass }}">{{ $labels[$todayStatus] }}</span></header>
                <div class="attendance-detail">
                    <div><span>Check In</span><strong>{{ $record?->check_in_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '--' }}</strong></div>
                    <div><span>Check Out</span><strong>{{ $record?->check_out_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '--' }}</strong></div>
                    <div><span>Lewat</span><strong>{{ $record?->check_in_time ? $duration($displayLateMinutes) : '--' }}</strong></div>
                    <div><span>Waktu Bekerja</span><strong>{{ $record?->check_in_time ? $duration($displayWorkingMinutes) : '--' }}</strong></div>
                    <div><span>Lokasi</span><strong>{{ $record?->location_name ?? $setting->location_name ?? '--' }}</strong></div>
                    <div><span>GPS</span><strong>{{ $record ? ($record->check_in_gps_verified ? 'Disahkan' : 'Luar kawasan') : '--' }}</strong></div>
                </div>
            </section>
            <section class="attendance-panel">
                <header class="attendance-panel__head"><div><h2>Tindakan Kehadiran</h2><p>Check In perlu berada dalam radius {{ $setting->allowed_radius_meter }} meter dari {{ $setting->location_name }}.</p></div></header>
                <div class="attendance-action">
                    @if (! $setting->status)
                        <div class="attendance-note">GPS belum diaktifkan oleh admin. Sila minta admin melengkapkan tetapan lokasi attendance.</div>
                    @elseif (! $isWorkingDay)
                        <div class="attendance-note">Hari ini bukan hari bekerja yang ditetapkan dalam sistem.</div>
                    @elseif (! $record)
                        <div class="attendance-action__status">
                            <span>Masa Semasa</span>
                            <strong data-current-time>{{ now()->timezone('Asia/Kuala_Lumpur')->format('h:i A') }}</strong>
                        </div>
                        <form method="POST" action="{{ route('coop-staff.attendance.check-in') }}" data-attendance-form>
                            @csrf
                            <input type="hidden" name="latitude"><input type="hidden" name="longitude">
                            <button class="attendance-primary" type="button" data-attendance-action="check-in">Check In Sekarang</button>
                        </form>
                    @elseif (! $record->check_out_time)
                        <div class="attendance-action__status">
                            <span>Masa Semasa</span>
                            <strong data-current-time>{{ now()->timezone('Asia/Kuala_Lumpur')->format('h:i A') }}</strong>
                        </div>
                        <form method="POST" action="{{ route('coop-staff.attendance.check-out') }}" data-attendance-form>
                            @csrf
                            <input type="hidden" name="latitude"><input type="hidden" name="longitude">
                            <button class="attendance-primary" type="button" data-attendance-action="check-out">Check Out Sekarang</button>
                        </form>
                    @else
                        <div class="attendance-action__status"><span>Rekod hari ini</span><strong>Selesai direkodkan</strong></div>
                        <a class="attendance-secondary" href="{{ route('coop-staff.attendance.history') }}">Lihat Sejarah Kehadiran</a>
                    @endif
                    <p class="attendance-help">Sistem meminta kebenaran lokasi daripada pelayar anda. Check In akan ditolak jika di luar kawasan Politeknik, manakala Check Out akan direkodkan dengan status GPS tidak disahkan.</p>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function updateAttendanceCurrentTime() {
    const now = new Date();
    const text = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });

    document.querySelectorAll('[data-current-time]').forEach(function (node) {
        node.textContent = text;
    });
}

updateAttendanceCurrentTime();
setInterval(updateAttendanceCurrentTime, 1000);

document.querySelectorAll('[data-attendance-form]').forEach(function (form) {
    form.querySelector('[data-attendance-action]').addEventListener('click', function () {
        if (!navigator.geolocation) { alert('Pelayar ini tidak menyokong GPS.'); return; }
        this.disabled = true; this.textContent = 'Mengesahkan lokasi...';
        navigator.geolocation.getCurrentPosition(function (position) {
            form.querySelector('[name="latitude"]').value = position.coords.latitude;
            form.querySelector('[name="longitude"]').value = position.coords.longitude;
            form.submit();
        }, function () { alert('Sila benarkan akses lokasi untuk meneruskan attendance.'); form.querySelector('[data-attendance-action]').disabled = false; form.querySelector('[data-attendance-action]').textContent = form.querySelector('[data-attendance-action]').dataset.attendanceAction === 'check-in' ? 'Check In Sekarang' : 'Check Out Sekarang'; }, {enableHighAccuracy: true, timeout: 15000, maximumAge: 0});
    });
});
</script>
@endpush
