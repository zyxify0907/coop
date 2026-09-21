@extends('layouts.app')

@section('title', $ownView ? 'Elaun Saya' : 'Elaun Bulanan')
@section('page-title', $ownView ? 'Elaun Saya' : 'Elaun Bulanan')
@section('page-subtitle', 'Pengiraan elaun berdasarkan jumlah minit rekod kehadiran.')

@section('content')
    @include('attendance.partials.styles')
    @php
        $duration = fn ($minutes) => intdiv((int) ($minutes ?? 0), 60).' jam '.((int) ($minutes ?? 0) % 60).' minit';
        $statusLabels = [
            'present' => 'Hadir', 'late' => 'Lewat', 'early_leave' => 'Keluar Awal',
            'missing_checkout' => 'Tiada Check Out', 'outside_area' => 'Di Luar Kawasan',
            'absent' => 'Tidak Hadir', 'leave' => 'Cuti', 'holiday' => 'Cuti Umum', 'half_day' => 'Lain',
        ];
        $allowanceStatus = $monthlyAllowance?->status ?? 'not_generated';
        $allowanceLabels = ['not_generated' => 'Belum Selesai', 'pending' => 'Belum Selesai', 'approved' => 'Diluluskan', 'paid' => 'Dibayar', 'completed' => 'Selesai'];
    @endphp

    <div class="attendance-page allowance-page">
        <header class="attendance-head">
            <div>
                <span class="attendance-kicker">ELAUN</span>
                <h1>{{ $ownView ? 'Elaun Saya' : 'Elaun Bulanan' }}</h1>
                <p>{{ $staff->nama }} - kadar harian RM {{ number_format((float) $staff->kadar_elaun, 2) }}. Jumlah jam hanya paparan; kiraan wang menggunakan jumlah minit.</p>
            </div>
            <div class="attendance-head__meta">
                <span>Status</span>
                <strong>{{ $allowanceLabels[$allowanceStatus] ?? $allowanceStatus }}</strong>
            </div>
        </header>

        <section class="attendance-panel">
            <form class="attendance-toolbar" method="GET" action="{{ $ownView ? route('coop-staff.allowances.index') : route('admin.allowances.index') }}">
                @unless($ownView)
                    <label>Pekerja
                        <select name="staff_id">
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id_pekerja }}" @selected($filters['staff_id'] == $worker->id_pekerja)>{{ $worker->nama }} ({{ $worker->no_pekerja }})</option>
                            @endforeach
                        </select>
                    </label>
                @endunless
                <label>Bulan<input type="number" name="month" min="1" max="12" value="{{ $filters['month'] }}"></label>
                <label>Tahun<input type="number" name="year" min="2020" max="2100" value="{{ $filters['year'] }}"></label>
                <button class="attendance-primary" type="submit">Tapis</button>
            </form>
        </section>

        <section class="attendance-metrics allowance-metrics">
            <div class="attendance-metric"><span>Jumlah Hadir</span><strong>{{ $summary['present_days'] }}</strong></div>
            <div class="attendance-metric"><span>Hari Penuh</span><strong>{{ $summary['full_days'] }}</strong></div>
            <div class="attendance-metric"><span>Jumlah Lewat</span><strong>{{ $summary['late_minutes'] }}m</strong></div>
            <div class="attendance-metric"><span>Jumlah Minit</span><strong>{{ $summary['total_minutes'] }}</strong></div>
            <div class="attendance-metric"><span>Jumlah Elaun</span><strong>RM {{ number_format((float) ($monthlyAllowance?->total_allowance ?? $summary['total_allowance']), 2) }}</strong></div>
        </section>

        @unless($ownView)
            <section class="attendance-panel">
                <div class="allowance-actions">
                    <a class="attendance-primary" target="_blank" href="{{ route('admin.allowances.pdf', ['staff_id' => $staff->id_pekerja, 'month' => $filters['month'], 'year' => $filters['year']]) }}">Export PDF</a>
                    @if($allowanceStatus !== 'completed')
                        <form method="POST" action="{{ route('admin.allowances.complete') }}">
                            @csrf
                            <input type="hidden" name="staff_id" value="{{ $staff->id_pekerja }}">
                            <input type="hidden" name="month" value="{{ $filters['month'] }}">
                            <input type="hidden" name="year" value="{{ $filters['year'] }}">
                            <button class="attendance-secondary" type="submit">Selesai</button>
                        </form>
                    @else
                        <span class="attendance-badge attendance-badge--approved">Selesai</span>
                    @endif
                </div>
            </section>
        @endunless

        <section class="attendance-panel">
            <div class="attendance-panel__head">
                <div>
                    <h2>Rekod Harian</h2>
                    <p>Butiran check-in, check-out, jumlah minit dan elaun harian.</p>
                </div>
            </div>
            <div class="attendance-table-wrap">
                <table class="attendance-table allowance-table">
                    <thead>
                    <tr>
                        <th>Nama Pekerja</th>
                        <th>Tarikh</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Jumlah Jam</th>
                        <th>Jumlah Minit</th>
                        <th>Minit Lewat</th>
                        <th>Kadar Harian</th>
                        <th>Elaun</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td class="attendance-person"><strong>{{ $record->staff?->nama ?? $staff->nama }}</strong><span>{{ $record->staff?->no_pekerja ?? $staff->no_pekerja }}</span></td>
                            <td>{{ $record->attendance_date->format('d/m/Y') }}</td>
                            <td>{{ $record->check_in_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</td>
                            <td>{{ $record->check_out_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</td>
                            <td>{{ $record->check_out_time ? $duration($record->total_minutes) : '-' }}</td>
                            <td>{{ $record->check_out_time ? $record->total_minutes.' minit' : '-' }}</td>
                            <td>{{ $record->late_minutes }} minit</td>
                            <td>RM {{ number_format((float) $record->daily_rate, 2) }}</td>
                            <td><strong>RM {{ number_format((float) $record->allowance_amount, 2) }}</strong></td>
                            <td><span class="attendance-badge attendance-badge--{{ in_array($record->status, ['leave', 'holiday', 'half_day'], true) ? 'working' : ($record->status === 'outside_area' ? 'missing_checkout' : $record->status) }}">{{ $statusLabels[$record->status] ?? $record->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="attendance-empty">Tiada rekod kehadiran untuk pilihan ini.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push('styles')
<style>
    .allowance-metrics{grid-template-columns:repeat(5,minmax(0,1fr))}
    .allowance-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:16px 20px}
    .allowance-actions form{display:inline-flex}
    .allowance-table{min-width:1220px}
    .allowance-table td,.allowance-table th{white-space:nowrap}
    .allowance-table .attendance-person{min-width:220px;white-space:normal}
    @media(max-width:1100px){.allowance-metrics{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @media(max-width:680px){.allowance-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}.allowance-actions{align-items:stretch;flex-direction:column}.allowance-actions form,.allowance-actions button{width:100%}}
</style>
@endpush
