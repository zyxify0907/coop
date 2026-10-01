@extends('layouts.app')

@section('title', 'Tempahan')
@section('page-title', 'Tempahan')
@section('page-subtitle', 'Semak tempahan pelajar dan kemaskini status.')

@section('content')
@php
    $statusLabels = [
        'baru' => 'Baru',
        'belum_ambil' => 'Belum Ambil',
        'sudah_ambil' => 'Sudah Ambil',
    ];

    $statusClass = [
        'baru' => 'status-pill--baru',
        'belum_ambil' => 'status-pill--semak',
        'sudah_ambil' => 'status-pill--diluluskan',
    ];

    $pageOrders = $orders->getCollection();
    $normalizedStatus = fn ($status, $pickupDate = null) => filled($pickupDate)
        ? 'sudah_ambil'
        : match (strtolower((string) $status)) {
            'pending' => 'baru',
            'belum ambil', 'diproses', 'dalam proses', 'siap', 'menunggu pengambilan', 'menunggu_ambil' => 'belum_ambil',
            'sudah ambil', 'diambil', 'siap diambil', 'selesai' => 'sudah_ambil',
            default => strtolower((string) $status),
        };
    $counts = [
        'jumlah' => $orders->total(),
        'baru' => $pageOrders->filter(fn ($order) => $normalizedStatus($order->status, $order->tarikh_ambil) === 'baru')->count(),
        'belum_ambil' => $pageOrders->filter(fn ($order) => $normalizedStatus($order->status, $order->tarikh_ambil) === 'belum_ambil')->count(),
        'sudah_ambil' => $pageOrders->filter(fn ($order) => $normalizedStatus($order->status, $order->tarikh_ambil) === 'sudah_ambil')->count(),
    ];
    $isAdminTempahan = ($role ?? null) === 'admin';
    $tempahanRoutes = [
        'index' => $isAdminTempahan ? 'admin.tempahan.index' : 'clothing-staff.orders.index',
        'update' => $isAdminTempahan ? 'admin.tempahan.update' : 'clothing-staff.orders.update',
        'destroy' => $isAdminTempahan ? 'admin.tempahan.destroy' : 'clothing-staff.orders.destroy',
    ];
    $tempahanDashboardRoute = $isAdminTempahan ? 'admin.dashboard.baju' : 'clothing-staff.dashboard.baju';
@endphp

@if (session('status') || session('success'))
    <div class="alert success">{{ session('status') ?? session('success') }}</div>
@endif

@if ($errors->any() || session('error'))
    <div class="alert danger">{{ $errors->first() ?: session('error') }}</div>
@endif

<section class="staff-hero">
    <div>
        <h2>Tempahan Baju</h2>
        <p>Semak tempahan pelajar, kemaskini status, dan tetapkan tarikh ambil dalam satu paparan rasmi.</p>
    </div>
    <div class="staff-hero__meta">
        <a class="staff-dashboard-backlink" href="{{ route($tempahanDashboardRoute) }}">Dashboard Tempahan</a>
    </div>
</section>

<div class="stats-grid">
    <div class="stat-card">
        <span>Jumlah Tempahan</span>
        <strong>{{ $counts['jumlah'] }}</strong>
        <small>Semua rekod tempahan aktif</small>
    </div>
    <div class="stat-card">
        <span>Baru</span>
        <strong>{{ $counts['baru'] }}</strong>
        <small>Perlu disemak staff</small>
    </div>
    <div class="stat-card">
        <span>Belum Ambil</span>
        <strong>{{ $counts['belum_ambil'] }}</strong>
        <small>Menunggu pelajar ambil</small>
    </div>
    <div class="stat-card">
        <span>Sudah Ambil</span>
        <strong>{{ $counts['sudah_ambil'] }}</strong>
        <small>Telah selesai diurus</small>
    </div>
</div>

<section class="panel staff-orders-panel">
    <div class="panel-head staff-orders-head">
        <img class="print-report__logo" src="{{ asset('images/koperasi-logo.svg') }}" alt="Logo Koperasi Politeknik Besut">
        <div>
            <h2>Senarai Tempahan</h2>
            <span>Kemaskini status tempahan baju pelajar.</span>
        </div>
        <button class="print-button" type="button" onclick="window.print()">Print / PDF</button>
    </div>

    <div class="filter-wrap">
        <form method="GET" action="{{ route($tempahanRoutes['index']) }}" class="filter-bar">
            <div class="filter-field search-field">
                <label for="search">Cari Pelajar</label>
                <input id="search" name="search" type="search" value="{{ request('search') }}" placeholder="Nama atau no matrik">
            </div>
            <div class="filter-field">
                <label for="item">Cari Baju</label>
                <input id="item" name="item" type="search" value="{{ request('item') }}" placeholder="Nama baju">
            </div>
            <div class="filter-field size-filter">
                <label for="size">Size</label>
                <select id="size" name="size" aria-label="Tapis size baju">
                    <option value="">Semua Size</option>
                    @foreach (['S', 'M', 'L', 'XL', 'XXL'] as $size)
                        <option value="{{ $size }}" @selected(strtoupper((string) request('size')) === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="status">Status</label>
                <select id="status" name="status" aria-label="Tapis status">
                    <option value="">Semua Status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $statusLabels[$status] ?? ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <button class="button" type="submit">Tapis</button>
            @if (filled(request('search')) || filled(request('item')) || filled(request('size')) || filled(request('status')))
                <a class="link-button" href="{{ route($tempahanRoutes['index']) }}">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-wrap">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Pelajar</th>
                    <th>Butiran Tempahan</th>
                    <th>Size</th>
                    <th>Kuantiti</th>
                    <th>Tarikh Tempah</th>
                    <th>Tarikh Ambil</th>
                    <th>Status</th>
                    <th style="text-align:right">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    @php
                        $updateId = $order->order_item_id ?? $order->tempahan_id;
                        $updateFormId = 'tempahan-update-'.$updateId;
                        $currentStatus = $normalizedStatus($order->status, $order->tarikh_ambil);
                    @endphp
                    <tr>
                        <td>
                            <div class="student-cell">
                                <span class="student-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($order->nama ?? 'P', 0, 1)) }}</span>
                                <div>
                                    <strong>{{ $order->nama ?? 'Pelajar' }}</strong>
                                    <small>{{ $order->no_matrik ?? 'Tiada No Matrik' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="order-detail">
                                <strong>{{ ($order->item ?? '-') === '-' ? 'Baju' : $order->item }}</strong>
                            </div>
                        </td>
                        <td><span class="table-pill">{{ $order->saiz ?? '-' }}</span></td>
                        <td><span class="table-pill table-pill--count">{{ $order->quantity ?? 1 }}</span></td>
                        <td>
                            {{ $order->created_at ? \Illuminate\Support\Carbon::parse($order->created_at)->format('d/m/Y') : '-' }}
                        </td>
                        <td>
                            <span class="pickup-date" data-pickup-date-output>{{ $order->tarikh_ambil ? \Illuminate\Support\Carbon::parse($order->tarikh_ambil)->format('d/m/Y') : '-' }}</span>
                        </td>
                        <td>
                            <form id="{{ $updateFormId }}" class="pickup-update-form" method="POST" action="{{ route($tempahanRoutes['update'], $updateId) }}">
                                @csrf
                                @method('PUT')
                                <input data-pickup-status-input type="hidden" name="pickup_status" value="{{ $currentStatus === 'sudah_ambil' ? 'sudah_ambil' : 'belum_ambil' }}">
                                <input data-pickup-date-input type="hidden" name="tarikh_ambil" value="{{ $order->tarikh_ambil ? \Illuminate\Support\Carbon::parse($order->tarikh_ambil)->format('Y-m-d') : '' }}">
                                <label class="pickup-check">
                                    <input type="checkbox" @checked($currentStatus === 'sudah_ambil')>
                                    <span></span>
                                    <strong>Sudah Ambil</strong>
                                </label>
                                <small class="pickup-note">{{ $currentStatus === 'sudah_ambil' ? 'Telah disahkan' : 'Belum diambil' }}</small>
                            </form>
                        </td>
                        <td>
                            <div class="list-actions">
                                <button class="button action-save" type="submit" form="{{ $updateFormId }}">Kemaskini</button>
                                <form method="POST" action="{{ route($tempahanRoutes['destroy'], $updateId) }}" onsubmit="return confirm('Padam tempahan ini?');" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button class="action-delete" type="submit">Padam</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">Tiada tempahan ditemui.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($orders->hasPages())
        <div class="pagination-wrap">{{ $orders->links() }}</div>
    @endif
</section>
@endsection

@push('styles')
<style>
    .alert{padding:14px 16px;border-radius:12px;margin-bottom:18px}
    .alert.success{background:#ecfdf5;color:#166534;border:1px solid #bbf7d0}
    .alert.danger{background:var(--danger-soft);color:var(--primary-dark);border:1px solid var(--danger-soft)}
    .staff-hero{display:flex;justify-content:space-between;align-items:center;gap:20px;margin-bottom:16px;padding:20px 26px;border:1px solid var(--line);border-left:5px solid var(--secondary);border-radius:16px;background:var(--surface);box-shadow:0 10px 24px rgba(15,23,42,.05);color:#0f172a}
    .staff-hero h2{margin:0;font-size:26px;letter-spacing:0;color:#0f172a}
    .staff-hero p{margin:6px 0 0;color:#475569;font-size:14px;font-weight:600;max-width:760px}
    .staff-hero__meta{display:grid;gap:6px;justify-items:end}
    .staff-hero__meta strong{font-size:14px;color:#475569}
    .staff-dashboard-backlink{display:inline-flex;align-items:center;justify-content:center;min-height:46px;padding:0 20px;border:1px solid #D7E3F5;border-radius:10px;background:#fff;color:var(--secondary);font-size:15px;font-weight:900;text-decoration:none;white-space:nowrap}
    .staff-dashboard-backlink::before{content:'\2190';margin-right:8px;color:var(--primary);font-weight:900}
    .staff-dashboard-backlink:hover{border-color:#AFC7EA;background:#F8FBFF}
    .stats-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}
    .stat-card{position:relative;border:1px solid rgba(226,232,240,.95);border-radius:14px;background:#fff;padding:14px 18px;box-shadow:0 8px 18px rgba(15,23,42,.04);overflow:hidden}
    .stat-card::before{content:'';position:absolute;inset:auto 20px 0 20px;height:3px;border-radius:999px;background:var(--secondary-soft)}
    .stat-card span{display:block;font-size:12px;font-weight:900;color:#64748b;text-transform:uppercase;letter-spacing:.03em}
    .stat-card strong{display:block;margin-top:6px;font-size:28px;color:#0f172a;letter-spacing:0}
    .stat-card small{display:block;margin-top:6px;color:#64748b;font-size:13px;font-weight:600}
    .staff-orders-panel{overflow:visible;border-radius:16px!important;background:#fff;border:1px solid var(--line)!important;box-shadow:0 10px 24px rgba(15,23,42,.045)!important}
    .staff-orders-head{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:20px 26px 12px;border-bottom:0;background:#fff}
    .staff-orders-head h2{margin:0;font-size:24px;letter-spacing:0}
    .staff-orders-head span{display:block;margin-top:4px;color:#64748b;font-size:13px}
    .print-button{
        appearance:none;
        border:1px solid #bfdbfe;
        border-radius:12px;
        background:var(--secondary-soft);
        color:var(--secondary);
        min-height:38px;
        padding:0 14px;
        font:inherit;
        font-size:14px;
        font-weight:900;
        cursor:pointer;
        white-space:nowrap;
    }
    .print-button:hover{border-color:var(--secondary);background:var(--secondary);color:#fff}
    .print-report__logo{display:none}
    .filter-wrap{padding:0 26px 16px;border-bottom:1px solid var(--line);background:#fff}
    .filter-bar{display:flex;gap:12px;align-items:end}
    .filter-field{display:grid;gap:6px;min-width:220px}
    .filter-field.size-filter{min-width:145px}
    .filter-field label{font-size:12px;font-weight:900;color:#64748b;text-transform:uppercase;letter-spacing:.03em}
    .filter-bar input,
    .filter-bar select{width:100%;max-width:100%;min-height:40px;border:1px solid var(--line);border-radius:10px;padding:8px 12px;background:#fff}
    .search-field{min-width:260px;flex:1 1 260px}
    .filter-bar .button{
        background:var(--secondary);
        box-shadow:0 10px 22px rgba(30,64,175,.16);
    }
    .filter-bar .button:hover{
        background:var(--secondary-hover);
        box-shadow:0 12px 26px rgba(30,64,175,.22);
    }
    .staff-orders-panel .table-wrap{
        overflow-x:hidden;
        overflow-y:hidden;
        padding:0;
        scrollbar-gutter:auto;
    }
    .orders-table{width:100%;min-width:0;border-collapse:separate;border-spacing:0;table-layout:fixed}
    .orders-table thead th{background:#f8fafc;color:#334155;font-size:11px;text-transform:uppercase;letter-spacing:.03em;padding:11px 10px;border-bottom:1px solid var(--line);text-align:left;white-space:nowrap}
    .orders-table tbody td{padding:11px 10px;border-bottom:1px solid var(--line);vertical-align:middle}
    .orders-table tbody tr{transition:background var(--transition-fast)}
    .orders-table tbody tr:hover{background:#f8fafc}
    .orders-table th:nth-child(1),.orders-table td:nth-child(1){width:21%}
    .orders-table th:nth-child(2),.orders-table td:nth-child(2){width:17%}
    .orders-table th:nth-child(3),.orders-table td:nth-child(3){width:6%;text-align:center}
    .orders-table th:nth-child(4),.orders-table td:nth-child(4){width:7%;text-align:center}
    .orders-table th:nth-child(5),.orders-table td:nth-child(5){width:10%}
    .orders-table th:nth-child(6),.orders-table td:nth-child(6){width:10%}
    .orders-table th:nth-child(7),.orders-table td:nth-child(7){width:15%}
    .orders-table th:nth-child(8),.orders-table td:nth-child(8){width:14%;text-align:right}
    .orders-table td:nth-child(8){padding-right:12px}
    .student-cell{display:flex;align-items:center;gap:12px;min-width:0}
    .student-avatar{width:42px;height:42px;border-radius:999px;display:grid;place-items:center;background:var(--secondary-soft);color:var(--secondary);border:1px solid #dbeafe;font-weight:900;box-shadow:none;flex:0 0 auto}
    .student-cell strong{display:block;font-size:14px;line-height:1.25;max-width:none}
    .student-cell small{display:block;margin-top:3px;color:#64748b;white-space:nowrap}
    .order-detail{display:grid;gap:8px;min-width:0}
    .order-detail strong{display:block;color:#0f172a;font-size:14px;line-height:1.35;max-width:none}
    .table-pill{display:inline-flex;align-items:center;justify-content:center;min-height:32px;padding:0 11px;border-radius:8px;border:1px solid var(--line);background:var(--surface-soft);font-weight:800;color:#0f172a;white-space:nowrap}
    .table-pill--count{min-width:38px}
    .pickup-check{
        position:relative;
        display:inline-flex;
        align-items:center;
        gap:7px;
        min-height:32px;
        padding:4px 9px 4px 6px;
        border:1px solid var(--line);
        border-radius:999px;
        background:#fff;
        cursor:pointer;
        user-select:none;
        white-space:nowrap;
    }
    .pickup-check input{
        position:absolute;
        inset:0;
        z-index:1;
        width:100%;
        height:100%;
        margin:0;
        opacity:0;
        cursor:pointer;
    }
    .pickup-check span{
        width:18px;
        height:18px;
        border:2px solid #cbd5e1;
        border-radius:7px;
        background:#fff;
        display:inline-grid;
        place-items:center;
        transition:all .16s ease;
    }
    .pickup-check span::after{
        content:'';
        width:9px;
        height:5px;
        border-left:2px solid #fff;
        border-bottom:2px solid #fff;
        transform:rotate(-45deg) translate(1px,-1px);
        opacity:0;
    }
    .pickup-check strong{font-size:12px;color:#334155}
    .pickup-check:has(input:checked){
        border-color:#bbf7d0;
        background:#ecfdf5;
    }
    .pickup-check:has(input:checked) span{
        border-color:#16a34a;
        background:#16a34a;
    }
    .pickup-check:has(input:checked) span::after{opacity:1}
    .pickup-check:has(input:checked) strong{color:#166534}
    .pickup-note{
        display:block;
        margin-top:5px;
        color:#64748b;
        font-size:11px;
        font-weight:700;
    }
    .pickup-date{
        display:inline-flex;
        align-items:center;
        min-height:32px;
        padding:0 10px;
        border-radius:10px;
        background:var(--surface-soft);
        color:#0f172a;
        font-weight:800;
        white-space:nowrap;
    }
    .status-control{
        width:100%;
        max-width:148px;
        min-height:40px;
        border:1px solid var(--line);
        border-radius:10px;
        background:#fff;
        padding:8px 12px;
        font-weight:700;
    }
    .date-stack{display:grid;gap:8px;color:#334155;min-width:0}
    .date-stack span{line-height:1.45}
    .date-stack b{color:#64748b;font-weight:900}
    .date-stack label{display:grid;grid-template-columns:auto minmax(0,1fr);gap:8px;align-items:center}
    .date-field{
        display:flex;
        justify-content:flex-start;
        min-width:132px;
    }
    .date-control{
        width:100%;
        max-width:138px;
        min-height:42px;
        border:1px solid var(--line);
        border-radius:12px;
        background:#fff;
        padding:8px 12px;
        font-weight:700;
    }
    .sub{margin-top:4px;font-size:12px;color:#64748b}
    .list-actions{display:flex;gap:7px;justify-content:flex-end;align-items:center;flex-wrap:nowrap;min-width:0}
    .list-actions form{margin:0;flex:0 0 auto}
    .action-save,
    .action-delete{
        appearance:none;
        -webkit-appearance:none;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        width:auto;
        min-width:72px;
        padding:0 10px;
        min-height:36px;
        border-radius:10px;
        box-shadow:none;
        text-decoration:none;
        font-weight:900;
        font-size:12px;
        line-height:1;
        white-space:nowrap;
        flex:0 0 auto;
        overflow:visible;
    }
    .action-save{
        border:1px solid var(--secondary);
        background:var(--secondary);
        color:#fff;
        box-shadow:0 6px 16px rgba(36, 83, 166, 0.18);
    }
    .action-save:hover{
        background:var(--secondary-hover);
        border-color:var(--secondary-hover);
        color:#fff;
        transform:none;
        box-shadow:0 8px 18px rgba(36, 83, 166, 0.24);
    }
    .action-delete{
        border:1px solid var(--danger-soft);
        background:var(--danger-soft);
        color:var(--danger);
    }
    .action-delete:hover{background:var(--danger);color:#fff;transform:none}
    .empty-state{padding:28px;text-align:center;color:#64748b;font-weight:700}
    .pagination-wrap{padding:18px 24px 24px}
    @media (max-width: 1180px){
        .staff-orders-panel .table-wrap{overflow-x:auto}
        .orders-table{min-width:1120px}
    }
    @media (max-width: 900px){
        .staff-hero{align-items:flex-start;flex-direction:column}
        .staff-hero__meta{justify-items:start}
        .stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
        .status-control,.date-control{max-width:none}
        .list-actions{justify-content:flex-start}
    }
    @media (max-width: 640px){
        .stats-grid{grid-template-columns:1fr}
        .filter-bar{align-items:stretch;flex-direction:column}
        .filter-field{min-width:0}
        .filter-bar select{width:100%}
        .staff-orders-head h2{font-size:24px}
        .date-stack label{grid-template-columns:1fr}
        .list-actions{display:grid;grid-template-columns:1fr 1fr;justify-items:stretch;min-width:0}
        .date-field{min-width:0}
        .action-save,.action-delete{width:100%}
    }

    .content:has(> .staff-hero){
        background:#F4F7FB;
    }
    .content > .staff-hero,
    .content > .stats-grid,
    .content > .staff-orders-panel{
        max-width:none;
    }
    .content > .staff-hero{
        position:relative;
        align-items:flex-start;
        margin-bottom:24px;
        padding:26px 28px!important;
        border:1px solid #D7E2EF!important;
        border-left:6px solid #082F59!important;
        border-radius:12px!important;
        background:#fff!important;
        box-shadow:0 12px 28px rgba(8,47,89,.08)!important;
        color:#082F59!important;
    }
    .content > .staff-hero::before{
        content:"";
        position:absolute;
        top:30px;
        left:28px;
        width:42px;
        height:3px;
        border-radius:999px;
        background:#ED1C2E;
    }
    .content > .staff-hero h2{
        margin:20px 0 0!important;
        color:#082F59!important;
        font-size:30px!important;
        line-height:1.15!important;
        font-weight:900!important;
        letter-spacing:0!important;
    }
    .content > .staff-hero p{
        margin:8px 0 0!important;
        max-width:780px;
        color:#31517D!important;
        font-size:15px!important;
        line-height:1.45!important;
        font-weight:650!important;
    }
    .content > .staff-hero .staff-hero__meta{
        padding-top:32px;
    }
    .content .staff-dashboard-backlink{
        min-height:42px;
        border:1px solid #9EC5FE;
        border-radius:6px;
        background:#fff;
        color:#0B5ED7;
        padding:0 18px;
        font-size:14px;
        font-weight:900;
        box-shadow:none;
    }
    .content .staff-dashboard-backlink::before{
        color:#0B5ED7;
    }
    .content .staff-dashboard-backlink:hover{
        border-color:#0B5ED7;
        background:#F8FBFF;
        color:#0B5ED7;
    }
    .content > .stats-grid{
        gap:16px;
        margin-bottom:24px;
    }
    .content .stat-card{
        min-height:116px;
        padding:20px 22px;
        border:1px solid #D7E2EF;
        border-radius:12px;
        background:#fff;
        box-shadow:0 8px 20px rgba(8,47,89,.045);
    }
    .content .stat-card::before{
        inset:0 auto 0 0;
        width:6px;
        height:auto;
        border-radius:12px 0 0 12px;
        background:#082F59;
    }
    .content .stat-card:nth-child(2)::before{
        background:#0B5ED7;
    }
    .content .stat-card:nth-child(3)::before{
        background:#F59E0B;
    }
    .content .stat-card:nth-child(4)::before{
        background:#10B981;
    }
    .content .stat-card span{
        color:#526987;
        font-size:13px;
        font-weight:900;
        letter-spacing:.02em;
    }
    .content .stat-card strong{
        margin-top:8px;
        color:#061A3A;
        font-size:32px;
        line-height:1;
        font-weight:900;
    }
    .content .stat-card small{
        margin-top:10px;
        color:#31517D;
        font-size:14px;
        font-weight:650;
    }
    .content .staff-orders-panel{
        overflow:hidden;
        border:1px solid #D7E2EF!important;
        border-radius:12px!important;
        background:#fff!important;
        box-shadow:0 8px 20px rgba(8,47,89,.035)!important;
    }
    .content .staff-orders-head{
        align-items:flex-start;
        padding:24px 28px 18px;
        border-bottom:0;
        background:#fff;
    }
    .content .staff-orders-head h2{
        margin:0;
        color:#082F59;
        font-size:26px;
        line-height:1.2;
        font-weight:900;
        letter-spacing:0;
    }
    .content .staff-orders-head h2::before{
        content:"";
        display:block;
        width:42px;
        height:4px;
        margin-bottom:12px;
        border-radius:999px;
        background:#ED1C2E;
    }
    .content .staff-orders-head span{
        margin-top:8px;
        color:#7A8AA1;
        font-size:15px;
        font-weight:650;
    }
    .content .print-button{
        min-height:42px;
        border:1px solid #9EC5FE;
        border-radius:6px;
        background:#fff;
        color:#0B5ED7;
        padding:0 18px;
        font-size:14px;
        font-weight:900;
        box-shadow:none;
    }
    .content .print-button:hover{
        border-color:#0B5ED7;
        background:#F8FBFF;
        color:#0B5ED7;
    }
    .content .filter-wrap{
        margin:0 28px 12px;
        padding:10px 12px;
        border:1px solid #D7E2EF;
        border-radius:6px;
        background:#F8FBFF;
    }
    .content .filter-bar{
        display:grid;
        grid-template-columns:minmax(260px,1.35fr) minmax(220px,.9fr) minmax(140px,.5fr) minmax(160px,.6fr) auto auto;
        gap:10px;
        align-items:end;
    }
    .content .filter-field{
        min-width:0;
    }
    .content .filter-field label{
        color:#31517D;
        font-size:11px;
        font-weight:900;
        letter-spacing:.03em;
    }
    .content .filter-bar input,
    .content .filter-bar select{
        min-height:36px;
        border:1px solid #C8D6E7;
        border-radius:6px;
        background:#fff;
        color:#102033;
        padding:6px 10px;
        font-size:13px;
        font-weight:650;
    }
    .content .filter-bar input:focus,
    .content .filter-bar select:focus{
        outline:0;
        border-color:#0B5ED7;
        box-shadow:0 0 0 3px rgba(11,94,215,.12);
    }
    .content .filter-bar .button{
        min-height:36px;
        border:1px solid #0B5ED7;
        border-radius:6px;
        background:#0B5ED7;
        color:#fff;
        padding:0 16px;
        font-weight:900;
        box-shadow:none;
    }
    .content .filter-bar .link-button{
        display:inline-flex;
        min-height:36px;
        align-items:center;
        justify-content:center;
        border:1px solid #C8D6E7;
        border-radius:6px;
        background:#fff;
        color:#102033;
        padding:0 14px;
        text-decoration:none;
        font-weight:900;
    }
    .content .staff-orders-panel .table-wrap{
        overflow-x:auto;
        border-top:1px solid #D7E2EF;
    }
    .content .orders-table{
        min-width:1120px;
        border-collapse:collapse;
        table-layout:fixed;
    }
    .content .orders-table thead th{
        padding:13px 14px;
        border-bottom:1px solid #D7E2EF;
        background:#F8F9FA;
        color:#31517D;
        font-size:12px;
        font-weight:900;
        letter-spacing:.03em;
    }
    .content .orders-table tbody td{
        padding:14px;
        border-bottom:1px solid #DCE5F0;
        color:#082F59;
        font-size:13px;
    }
    .content .orders-table tbody tr:hover{
        background:#F8FBFF;
    }
    .content .student-avatar{
        width:36px;
        height:36px;
        border:1px solid #CFE0F5;
        background:#EAF3FF;
        color:#0B5ED7;
        font-size:12px;
    }
    .content .student-cell strong,
    .content .order-detail strong{
        color:#061A3A;
        font-size:13px;
        font-weight:900;
    }
    .content .student-cell small{
        color:#526987;
        font-size:12px;
    }
    .content .table-pill,
    .content .pickup-date{
        min-height:30px;
        border:1px solid #D7E2EF;
        border-radius:6px;
        background:#F4F7FB;
        color:#061A3A;
        font-size:13px;
        font-weight:900;
    }
    .content .pickup-check{
        min-height:30px;
        border:1px solid #D7E2EF;
        border-radius:999px;
        background:#fff;
        padding:4px 10px 4px 6px;
    }
    .content .pickup-check span{
        width:18px;
        height:18px;
        border-radius:999px;
    }
    .content .pickup-check:has(input:checked){
        border-color:#BBF7D0;
        background:#DCFCE7;
    }
    .content .pickup-check:has(input:checked) span{
        border-color:#16A34A;
        background:#16A34A;
    }
    .content .pickup-check:has(input:checked) strong{
        color:#15803D;
    }
    .content .pickup-note{
        color:#526987;
        font-size:11px;
        font-weight:750;
    }
    .content .list-actions{
        gap:8px;
    }
    .content .action-save,
    .content .action-delete{
        min-width:76px;
        min-height:34px;
        border-radius:6px;
        font-size:13px;
        box-shadow:none;
    }
    .content .action-save{
        border-color:#0B5ED7;
        background:#0B5ED7;
        color:#fff;
    }
    .content .action-save:hover{
        border-color:#084FB5;
        background:#084FB5;
        box-shadow:none;
    }
    .content .action-delete{
        border-color:#FAD5D8;
        background:#FEF2F2;
        color:#B91C1C;
    }
    .content .action-delete:hover{
        border-color:#ED1C2E;
        background:#ED1C2E;
        color:#fff;
    }
    .content .pagination-wrap{
        padding:16px 24px;
        border-top:1px solid #D7E2EF;
        background:#F8F9FA;
    }
    @media (max-width: 1180px){
        .content .filter-bar{
            grid-template-columns:repeat(2,minmax(0,1fr));
        }
    }
    @media (max-width: 900px){
        .content > .staff-hero .staff-hero__meta{
            padding-top:0;
        }
    }
    @media (max-width: 640px){
        .content .filter-wrap{
            margin:0 22px 16px;
        }
        .content .filter-bar{
            grid-template-columns:1fr;
        }
        .content .staff-orders-head{
            align-items:stretch;
            flex-direction:column;
            padding:22px;
        }
        .content .print-button,
        .content .staff-dashboard-backlink{
            width:100%;
        }
    }
    @media print{
        @page{size:A4 landscape;margin:10mm}
        *{
            box-shadow:none!important;
            text-shadow:none!important;
            overflow:visible!important;
            scrollbar-width:none!important;
        }
        *::-webkit-scrollbar{display:none!important}
        html,
        body{
            width:auto!important;
            height:auto!important;
            background:#fff!important;
            overflow:visible!important;
        }
        .sidebar,
        .topbar,
        .staff-hero,
        .stats-grid,
        .filter-wrap,
        .print-button,
        .list-actions,
        .alert,
        .pagination-wrap{display:none!important}
        .main-shell,
        .content,
        .app-content{
            display:block!important;
            width:100%!important;
            height:auto!important;
            margin:0!important;
            padding:0!important;
            max-width:none!important;
            overflow:visible!important;
        }
        .staff-orders-panel{border:0!important;box-shadow:none!important;border-radius:0!important}
        .staff-orders-head{display:block!important;padding:0 0 14px!important;border-bottom:2px solid #0f172a!important}
        .print-report__logo{display:block!important;width:48px!important;height:48px!important;object-fit:contain!important;margin:0 auto 8px!important}
        .staff-orders-head h2{font-size:22px!important}
        .staff-orders-head span{font-size:12px!important;color:#334155!important}
        .staff-orders-panel .table-wrap,
        .table-wrap{
            display:block!important;
            width:100%!important;
            max-width:100%!important;
            height:auto!important;
            max-height:none!important;
            overflow:visible!important;
            border:0!important;
        }
        .orders-table{
            min-width:0!important;
            width:100%!important;
            max-width:100%!important;
            border-collapse:collapse!important;
            table-layout:fixed!important;
            font-size:10px!important;
        }
        .orders-table thead th,
        .orders-table tbody td{
            border:1px solid #cbd5e1!important;
            vertical-align:middle!important;
            box-sizing:border-box!important;
            overflow-wrap:break-word!important;
            word-break:normal!important;
        }
        .orders-table thead th{
            background:#f1f5f9!important;
            color:#0f172a!important;
            padding:7px 8px!important;
            font-size:9px!important;
            line-height:1.25!important;
            white-space:normal!important;
            letter-spacing:0!important;
        }
        .orders-table tbody td{
            padding:8px!important;
            font-size:10px!important;
            line-height:1.25!important;
        }
        .orders-table th:nth-child(1),.orders-table td:nth-child(1){width:25%!important}
        .orders-table th:nth-child(2),.orders-table td:nth-child(2){width:19%!important}
        .orders-table th:nth-child(3),.orders-table td:nth-child(3){width:7%!important;text-align:center!important}
        .orders-table th:nth-child(4),.orders-table td:nth-child(4){width:8%!important;text-align:center!important}
        .orders-table th:nth-child(5),.orders-table td:nth-child(5){width:12%!important;text-align:center!important}
        .orders-table th:nth-child(6),.orders-table td:nth-child(6){width:12%!important;text-align:center!important}
        .orders-table th:nth-child(7),.orders-table td:nth-child(7){width:17%!important}
        .orders-table th:nth-child(8),
        .orders-table td:nth-child(8){display:none!important}
        .student-avatar{display:none!important}
        .student-cell{display:block!important;gap:0!important;padding-right:6px!important}
        .student-cell strong{
            max-width:none!important;
            white-space:normal!important;
            font-size:10px!important;
            line-height:1.2!important;
        }
        .student-cell small{
            font-size:9px!important;
            white-space:normal!important;
        }
        .order-detail{display:block!important;gap:0!important}
        .order-detail strong{
            font-size:10px!important;
            line-height:1.2!important;
            color:#0f172a!important;
            white-space:normal!important;
        }
        .table-pill,
        .pickup-date{border:0!important;background:transparent!important;padding:0!important;min-height:0!important}
        .pickup-check{
            border:0!important;
            background:transparent!important;
            padding:0!important;
            min-height:0!important;
            white-space:normal!important;
        }
        .pickup-check strong{font-size:10px!important;line-height:1.2!important}
        .pickup-check span{width:14px!important;height:14px!important;border-radius:3px!important}
        .pickup-note{display:none!important}
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const isoDate = `${yyyy}-${mm}-${dd}`;
        const displayDate = `${dd}/${mm}/${yyyy}`;

        document.querySelectorAll('.pickup-check input[type="checkbox"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const row = checkbox.closest('tr');
                const dateInput = row?.querySelector('[data-pickup-date-input]');
                const dateOutput = row?.querySelector('[data-pickup-date-output]');
                const note = row?.querySelector('.pickup-note');
                const statusInput = row?.querySelector('[data-pickup-status-input]');

                if (checkbox.checked) {
                    if (statusInput) {
                        statusInput.value = 'sudah_ambil';
                    }
                    if (dateInput && !dateInput.value) {
                        dateInput.value = isoDate;
                    }
                    if (dateOutput) {
                        dateOutput.textContent = dateInput?.value ? dateInput.value.split('-').reverse().join('/') : displayDate;
                    }
                    if (note) {
                        note.textContent = 'Telah disahkan';
                    }
                    return;
                }

                if (dateInput) {
                    dateInput.value = '';
                }
                if (statusInput) {
                    statusInput.value = 'belum_ambil';
                }
                if (dateOutput) {
                    dateOutput.textContent = '-';
                }
                if (note) {
                    note.textContent = 'Belum diambil';
                }
            });
        });

        document.querySelectorAll('form[id^="tempahan-update-"]').forEach(function (form) {
            form.addEventListener('submit', function () {
                const row = form.closest('tr');
                const checkbox = row?.querySelector('.pickup-check input[type="checkbox"]');
                const statusInput = row?.querySelector('[data-pickup-status-input]');

                if (statusInput) {
                    statusInput.value = checkbox?.checked ? 'sudah_ambil' : 'belum_ambil';
                }

                if (!checkbox?.checked) {
                    const dateInput = row?.querySelector('[data-pickup-date-input]');
                    if (dateInput) {
                        dateInput.value = '';
                    }
                }
            });
        });
    });
</script>
@endpush
