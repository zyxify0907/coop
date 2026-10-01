@extends('layouts.app')

@section('title', $ownView ? 'Elaun Saya' : 'Elaun Bulanan')
@section('page-title', $ownView ? 'Elaun Saya' : 'Elaun Bulanan')
@section('page-subtitle', 'Pengiraan elaun berdasarkan jumlah minit rekod kehadiran.')

@section('content')
    @include('attendance.partials.styles')
    @php
        $duration = fn ($minutes) => intdiv((int) ($minutes ?? 0), 60).' jam '.((int) ($minutes ?? 0) % 60).' minit';
        $monthName = \Carbon\CarbonImmutable::create((int) $filters['year'], (int) $filters['month'], 1)->locale('ms')->translatedFormat('F Y');
        $statusLabels = [
            'present' => 'Hadir', 'late' => 'Lewat', 'early_leave' => 'Keluar Awal',
            'missing_checkout' => 'Tiada Check Out', 'outside_area' => 'Di Luar Kawasan',
            'absent' => 'Tidak Hadir', 'leave' => 'Cuti', 'holiday' => 'Cuti Umum', 'half_day' => 'Lain',
        ];
        $allowanceStatus = $monthlyAllowance?->status ?? 'not_generated';
        $absentDays = $records->where('status', 'absent')->count();
    @endphp

    <div class="attendance-page allowance-page">
        <header class="attendance-head">
            <div>
                <h1>{{ $ownView ? 'Elaun Saya' : 'Elaun Bulanan' }}</h1>
                <p>{{ $staff->nama }} - kadar harian RM {{ number_format((float) $staff->kadar_elaun, 2) }}. Jumlah jam hanya paparan; kiraan wang menggunakan jumlah minit.</p>
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
            <div class="attendance-metric"><span>Jumlah Tidak Hadir</span><strong>{{ $absentDays }}</strong></div>
            <div class="attendance-metric"><span>Jumlah Lewat</span><strong>{{ $duration($summary['late_minutes']) }}</strong></div>
            <div class="attendance-metric"><span>Jumlah Elaun</span><strong>RM {{ number_format((float) ($monthlyAllowance?->total_allowance ?? $summary['total_allowance']), 2) }}</strong></div>
        </section>

        @unless($ownView)
            <section class="attendance-panel">
                <div class="allowance-actions">
                    <a class="attendance-primary" href="{{ route('admin.allowances.pdf', ['staff_id' => $staff->id_pekerja, 'month' => $filters['month'], 'year' => $filters['year']]) }}">Print / Save PDF</a>
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
                    <p>Butiran check-in, check-out, tempoh bekerja, kelewatan dan elaun harian.</p>
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
                        <th>Lewat</th>
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
                            <td>{{ $record->check_in_time ? $duration($record->late_minutes) : '-' }}</td>
                            <td>RM {{ number_format((float) $record->daily_rate, 2) }}</td>
                            <td><strong>RM {{ number_format((float) $record->allowance_amount, 2) }}</strong></td>
                            <td><span class="attendance-badge attendance-badge--{{ in_array($record->status, ['leave', 'holiday', 'half_day'], true) ? 'working' : ($record->status === 'outside_area' ? 'missing_checkout' : $record->status) }}">{{ $statusLabels[$record->status] ?? $record->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="attendance-empty">Tiada rekod kehadiran untuk pilihan ini.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <section class="allowance-print-report" aria-hidden="true">
        <header class="allowance-print-report__head">
            <div class="allowance-print-report__brand">
                <img src="/images/koperasi-logo.svg" alt="Logo Koperasi Politeknik Besut Berhad">
                <div>
                <h1>Elaun Bulanan Pekerja Koperasi</h1>
                <p>{{ $staff->nama }} ({{ $staff->no_pekerja }})</p>
                <p>Bulan: {{ $monthName }}</p>
                </div>
            </div>
            <div class="allowance-print-report__rate">
                <span>Kadar Harian</span>
                <strong>RM {{ number_format((float) $staff->kadar_elaun, 2) }}</strong>
            </div>
        </header>

        <section class="allowance-print-summary">
            <div><span>Jumlah Hadir</span><strong>{{ $summary['present_days'] }} hari</strong></div>
            <div><span>Jumlah Tidak Hadir</span><strong>{{ $absentDays }} hari</strong></div>
            <div><span>Jumlah Lewat</span><strong>{{ $duration($summary['late_minutes']) }}</strong></div>
            <div><span>Jumlah elaun</span><strong>RM {{ number_format((float) ($monthlyAllowance?->total_allowance ?? $summary['total_allowance']), 2) }}</strong></div>
        </section>
        <p class="allowance-print-note">Kadar harian RM {{ number_format((float) $staff->kadar_elaun, 2) }}. Jumlah elaun dikira daripada rekod kehadiran yang layak.</p>

        <table class="allowance-print-table">
            <thead>
                <tr>
                    <th>Tarikh</th><th>Check In</th><th>Check Out</th><th>Jumlah Jam</th><th>Lewat</th><th>Kadar Harian</th><th>Elaun</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ $record->attendance_date->format('d/m/Y') }}</td>
                        <td>{{ $record->check_in_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</td>
                        <td>{{ $record->check_out_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</td>
                        <td>{{ $record->check_out_time ? $duration($record->total_minutes) : '-' }}</td>
                        <td>{{ $record->check_in_time ? $duration($record->late_minutes) : '-' }}</td>
                        <td>RM {{ number_format((float) $record->daily_rate, 2) }}</td>
                        <td>RM {{ number_format((float) $record->allowance_amount, 2) }}</td>
                        <td>{{ $statusLabels[$record->status] ?? $record->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8">Tiada rekod kehadiran untuk pilihan ini.</td></tr>
                @endforelse
            </tbody>
        </table>

        <footer class="allowance-print-signatures">
            <div>
                <span>Disediakan oleh</span>
                <strong>Pengurusan Kehadiran Koperasi</strong>
            </div>
        </footer>
    </section>
@endsection

@push('styles')
<style>
    .allowance-metrics{grid-template-columns:repeat(4,minmax(0,1fr))}
    .allowance-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:16px 20px}
    .allowance-actions form{display:inline-flex}
    .allowance-table{min-width:1220px}
    .allowance-table td,.allowance-table th{white-space:nowrap}
    .allowance-table .attendance-person{min-width:220px;white-space:normal}
    .allowance-print-report{display:none}
    @media print{
        @page{size:A4 landscape;margin:12mm}
        body *{visibility:hidden!important}
        .allowance-print-report,.allowance-print-report *{visibility:visible!important}
        .allowance-print-report{display:block!important;position:fixed;inset:0;width:100%;color:#111827;background:#fff;font-family:Arial,sans-serif}
        .allowance-print-report__head{display:flex;justify-content:space-between;gap:24px;padding-bottom:12px;margin-bottom:16px;border-bottom:2px solid #111827}
        .allowance-print-report__brand{display:flex;align-items:center;gap:14px}
        .allowance-print-report__brand img{width:70px;height:70px;object-fit:contain}
        .allowance-print-report h1{margin:0 0 6px;font-size:20px}
        .allowance-print-report p{margin:0 0 3px;font-size:11px}
        .allowance-print-report__rate{text-align:right}
        .allowance-print-report__rate span{display:block;font-size:11px}
        .allowance-print-report__rate strong{display:block;margin-top:4px;font-size:20px}
        .allowance-print-summary{display:grid;grid-template-columns:repeat(5,1fr);border:1px solid #cbd5e1;margin-bottom:16px}
        .allowance-print-summary div{padding:9px;border-right:1px solid #cbd5e1}
        .allowance-print-summary div:last-child{border-right:0}
        .allowance-print-summary span{display:block;font-size:9px;font-weight:700;text-transform:uppercase;color:#475569}
        .allowance-print-summary strong{display:block;margin-top:4px;font-size:13px}
        .allowance-print-note{margin:0 0 10px;font-size:10px;color:#475569}
        .allowance-print-table{width:100%;border-collapse:collapse;table-layout:fixed;font-size:9px}
        .allowance-print-table th,.allowance-print-table td{padding:6px 5px;border:1px solid #94a3b8;vertical-align:middle;white-space:nowrap}
        .allowance-print-table th{background:#e2e8f0!important;font-size:8px;text-align:center;text-transform:uppercase;print-color-adjust:exact;-webkit-print-color-adjust:exact}
        .allowance-print-table td:nth-child(5),.allowance-print-table td:nth-child(6),.allowance-print-table td:nth-child(7),.allowance-print-table td:nth-child(8){text-align:right}
        .allowance-print-signatures{display:grid;grid-template-columns:1fr;width:50%;margin:30px auto 0}
        .allowance-print-signatures div{padding-top:12px;border-top:1px solid #111827;text-align:center;font-size:11px}
        .allowance-print-signatures strong,.allowance-print-signatures span{display:block}
        .allowance-print-signatures strong{margin-bottom:3px;font-size:12px}
    }
    @media(max-width:1100px){.allowance-metrics{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @media(max-width:680px){.allowance-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}.allowance-actions{align-items:stretch;flex-direction:column}.allowance-actions form,.allowance-actions button{width:100%}}
</style>
@endpush
