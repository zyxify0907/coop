@extends('layouts.app')
@section('title', 'Sejarah Kehadiran')
@section('page-title', 'Sejarah Kehadiran')
@section('page-subtitle', 'Rekod kehadiran peribadi anda sahaja.')
@section('content')
    @include('attendance.partials.styles')
    @php
        $labels = $statuses + ['missing_checkout' => 'Tiada Check Out'];
        $duration = fn ($minutes) => intdiv((int) ($minutes ?? 0), 60).' jam '.((int) ($minutes ?? 0) % 60).' minit';
    @endphp
    <div class="attendance-page">
        <header class="attendance-head"><div><h1>Sejarah Kehadiran</h1><p>Semak rekod Check In, Check Out dan jam bekerja anda.</p></div></header>
        <section class="attendance-panel">
            <form class="attendance-toolbar" method="GET"><label>Bulan<input type="number" name="month" min="1" max="12" value="{{ $filters['month'] }}"></label><label>Tahun<input type="number" name="year" min="2020" max="2100" value="{{ $filters['year'] }}"></label><label>Status<select name="status"><option value="">Semua status</option>@foreach($statuses as $key => $label)<option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>@endforeach</select></label><button class="attendance-primary" type="submit">Tapis</button></form>
            <div class="attendance-table-wrap"><table class="attendance-table"><thead><tr><th>Tarikh</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Lewat</th><th>Keluar Awal</th><th>Waktu Bekerja</th><th>GPS</th></tr></thead><tbody>
                @forelse($records as $record)<tr><td><strong>{{ $record->attendance_date->format('d/m/Y') }}</strong></td><td>{{ $record->check_in_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</td><td>{{ $record->check_out_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</td><td><span class="attendance-badge attendance-badge--{{ $record->status }}">{{ $labels[$record->status] ?? $record->status }}</span></td><td>{{ $record->check_in_time ? $duration($record->late_minutes) : '-' }}</td><td>{{ $record->check_out_time ? $duration($record->early_leave_minutes) : '-' }}</td><td>{{ $record->check_in_time ? $duration($record->working_minutes) : '-' }}</td><td>{{ $record->check_in_gps_verified && (!$record->check_out_time || $record->check_out_gps_verified) ? 'Disahkan' : 'Tidak disahkan' }}</td></tr>
                @empty<tr><td colspan="8" class="attendance-empty">Tiada rekod kehadiran untuk pilihan ini.</td></tr>@endforelse
            </tbody></table></div>
            @if($records->hasPages())<div class="pagination">{{ $records->links() }}</div>@endif
        </section>
    </div>
@endsection
