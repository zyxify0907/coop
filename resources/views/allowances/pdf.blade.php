@php
    $duration = fn ($minutes) => intdiv((int) ($minutes ?? 0), 60).' jam '.((int) ($minutes ?? 0) % 60).' minit';
    $monthName = \Carbon\CarbonImmutable::create((int) $filters['year'], (int) $filters['month'], 1)->locale('ms')->translatedFormat('F Y');
    $statusLabels = [
        'present' => 'Hadir',
        'late' => 'Lewat',
        'early_leave' => 'Keluar Awal',
        'missing_checkout' => 'Tiada Check Out',
        'outside_area' => 'Di Luar Kawasan',
        'absent' => 'Tidak Hadir',
        'leave' => 'Cuti',
        'holiday' => 'Cuti Umum',
        'half_day' => 'Lain',
    ];
@endphp
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>Elaun Bulanan {{ $staff->nama }} {{ $monthName }}</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;background:#fff;color:#111827;font-family:Arial,sans-serif;font-size:12px;line-height:1.45}
        .sheet{max-width:1120px;margin:0 auto;padding:28px}
        .head{display:flex;align-items:center;justify-content:space-between;gap:20px;border-bottom:2px solid #111827;padding-bottom:14px;margin-bottom:18px}
        .brand{display:flex;align-items:center;gap:14px}.brand img{width:76px;height:76px;object-fit:contain}
        h1{margin:0 0 6px;font-size:22px}
        p{margin:0;color:#374151}
        .meta{text-align:right}
        .summary{display:grid;grid-template-columns:repeat(5,1fr);border:1px solid #d1d5db;margin-bottom:18px;background:#f8fafc}
        .summary div{padding:10px;border-right:1px solid #d1d5db}
        .summary div:last-child{border-right:0}
        .summary span{display:block;color:#6b7280;font-size:10px;text-transform:uppercase;font-weight:bold}
        .summary strong{display:block;margin-top:4px;font-size:14px}
        table{width:100%;border-collapse:collapse}
        th,td{border:1px solid #d1d5db;padding:7px;text-align:left;vertical-align:top}
        th{background:#f3f4f6;font-size:10px;text-transform:uppercase}tbody tr:nth-child(even){background:#f9fafb}
        .right{text-align:right}
        .foot{margin-top:28px;display:grid;grid-template-columns:1fr 1fr;gap:80px}
        .sign{padding-top:42px;border-top:1px solid #111827;text-align:center}
        @page{size:A4 landscape;margin:12mm}
        @media print{.sheet{max-width:none;padding:0}.head{margin-top:0}}
    </style>
</head>
<body>
    <main class="sheet">
        <section class="head">
            <div class="brand">
                <img src="/images/koperasi-logo.svg" alt="Logo Koperasi Politeknik Besut Berhad">
                <div>
                <h1>Elaun Bulanan Pekerja Koperasi</h1>
                <p>{{ $staff->nama }} ({{ $staff->no_pekerja }})</p>
                <p>Bulan: {{ $monthName }}</p>
                </div>
            </div>
            <div class="meta">
                <p>Kadar Harian</p>
                <h1>RM {{ number_format((float) $staff->kadar_elaun, 2) }}</h1>
            </div>
        </section>

        <section class="summary">
            <div><span>Hari layak</span><strong>{{ $summary['present_days'] }} hari</strong></div>
            <div><span>Hari penuh</span><strong>{{ $summary['full_days'] }} hari</strong></div>
            <div><span>Jumlah masa</span><strong>{{ $duration($summary['total_minutes']) }}</strong></div>
            <div><span>Jumlah lewat</span><strong>{{ $summary['late_minutes'] }} minit</strong></div>
            <div><span>Jumlah elaun</span><strong>RM {{ number_format((float) ($monthlyAllowance?->total_allowance ?? $summary['total_allowance']), 2) }}</strong></div>
        </section>

        <p style="margin:0 0 10px;color:#4b5563">Kadar harian: RM {{ number_format((float) $staff->kadar_elaun, 2) }}. Jumlah elaun dikira daripada rekod kehadiran yang layak.</p>

        <table>
            <thead>
            <tr>
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
                    <td>{{ $record->attendance_date->format('d/m/Y') }}</td>
                    <td>{{ $record->check_in_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</td>
                    <td>{{ $record->check_out_time?->timezone('Asia/Kuala_Lumpur')->format('h:i A') ?? '-' }}</td>
                    <td>{{ $record->check_out_time ? $duration($record->total_minutes) : '-' }}</td>
                    <td class="right">{{ $record->check_out_time ? $record->total_minutes : '-' }}</td>
                    <td class="right">{{ $record->late_minutes }}</td>
                    <td class="right">RM {{ number_format((float) $record->daily_rate, 2) }}</td>
                    <td class="right">RM {{ number_format((float) $record->allowance_amount, 2) }}</td>
                    <td>{{ $statusLabels[$record->status] ?? $record->status }}</td>
                </tr>
            @empty
                <tr><td colspan="9">Tiada rekod kehadiran untuk pilihan ini.</td></tr>
            @endforelse
            </tbody>
        </table>

        <section class="foot">
            <div class="sign">Disediakan oleh</div>
            <div class="sign">Disahkan oleh</div>
        </section>
    </main>
    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
