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
        <span class="badge success">Live</span>
        <strong>{{ now()->format('d M Y') }}</strong>
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
        <div>
            <h2>Senarai Tempahan</h2>
            <span>Kemaskini status tempahan baju pelajar.</span>
        </div>
    </div>

    <div class="filter-wrap">
        <form method="GET" action="{{ route($tempahanRoutes['index']) }}" class="filter-bar">
            <div class="filter-field search-field">
                <label for="search">Cari Pelajar</label>
                <input id="search" name="search" type="search" value="{{ request('search') }}" placeholder="Nama atau no matrik">
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
            @if (filled(request('search')) || filled(request('status')))
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
                    <th>Status</th>
                    <th>Tarikh Tempah</th>
                    <th>Tarikh Ambil</th>
                    <th style="text-align:right">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    @php
                        $updateFormId = 'tempahan-update-'.$order->tempahan_id;
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
                            <select class="status-control" name="status" form="{{ $updateFormId }}" aria-label="Status tempahan #{{ $order->tempahan_id }}">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" @selected($currentStatus === $status)>{{ $statusLabels[$status] ?? ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            {{ $order->created_at ? \Illuminate\Support\Carbon::parse($order->created_at)->format('d/m/Y') : '-' }}
                        </td>
                        <td>
                            <div class="date-field">
                                <input class="date-control" name="tarikh_ambil" type="date" form="{{ $updateFormId }}" value="{{ $order->tarikh_ambil ? \Illuminate\Support\Carbon::parse($order->tarikh_ambil)->format('Y-m-d') : '' }}" aria-label="Tarikh ambil tempahan #{{ $order->tempahan_id }}">
                            </div>
                        </td>
                        <td>
                            <div class="list-actions">
                                <form id="{{ $updateFormId }}" method="POST" action="{{ route($tempahanRoutes['update'], $order->tempahan_id) }}">
                                    @csrf
                                    @method('PUT')
                                    <button class="button action-save" type="submit">Simpan</button>
                                </form>
                                <form method="POST" action="{{ route($tempahanRoutes['destroy'], $order->tempahan_id) }}" onsubmit="return confirm('Padam tempahan ini?');" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button class="action-delete" type="submit">Delete</button>
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
    .staff-hero{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:22px;padding:26px 30px;border:1px solid var(--line);border-left:5px solid var(--secondary);border-radius:22px;background:var(--surface);box-shadow:0 14px 34px rgba(15,23,42,.06);color:#0f172a}
    .staff-hero h2{margin:0;font-size:30px;letter-spacing:0;color:#0f172a}
    .staff-hero p{margin:10px 0 0;color:#475569;font-weight:700;max-width:760px}
    .staff-hero__meta{display:grid;gap:8px;justify-items:end}
    .staff-hero__meta strong{font-size:14px;color:#475569}
    .stats-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px;margin-bottom:26px}
    .stat-card{position:relative;border:1px solid rgba(226,232,240,.95);border-radius:18px;background:#fff;padding:18px 20px;box-shadow:0 10px 24px rgba(15,23,42,.045);overflow:hidden}
    .stat-card::before{content:'';position:absolute;inset:auto 20px 0 20px;height:3px;border-radius:999px;background:var(--secondary-soft)}
    .stat-card span{display:block;font-size:12px;font-weight:900;color:#64748b;text-transform:uppercase;letter-spacing:.03em}
    .stat-card strong{display:block;margin-top:10px;font-size:34px;color:#0f172a;letter-spacing:-.03em}
    .stat-card small{display:block;margin-top:10px;color:#64748b;font-weight:600}
    .staff-orders-panel{overflow:hidden;border-radius:22px!important;background:#fff;border:1px solid var(--line)!important;box-shadow:0 14px 34px rgba(15,23,42,.055)!important}
    .staff-orders-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;padding:24px 26px 16px;border-bottom:0;background:#fff}
    .staff-orders-head h2{margin:0;font-size:28px;letter-spacing:0}
    .staff-orders-head span{display:block;margin-top:6px;color:#64748b;font-size:14px}
    .filter-wrap{padding:0 26px 22px;border-bottom:1px solid var(--line);background:#fff}
    .filter-bar{display:flex;gap:12px;align-items:end}
    .filter-field{display:grid;gap:7px;min-width:240px}
    .filter-field label{font-size:12px;font-weight:900;color:#64748b;text-transform:uppercase;letter-spacing:.03em}
    .filter-bar input,
    .filter-bar select{width:100%;max-width:100%;border:1px solid var(--line);border-radius:10px;padding:11px 14px;background:#fff}
    .search-field{min-width:280px;flex:1 1 280px}
    .filter-bar .button{
        background:var(--secondary);
        box-shadow:0 10px 22px rgba(30,64,175,.16);
    }
    .filter-bar .button:hover{
        background:var(--secondary-hover);
        box-shadow:0 12px 26px rgba(30,64,175,.22);
    }
    .table-wrap{overflow-x:auto;overflow-y:visible;padding:0}
    .orders-table{width:100%;min-width:1120px;border-collapse:separate;border-spacing:0;table-layout:auto}
    .orders-table thead th{background:#f8fafc;color:#334155;font-size:12px;text-transform:uppercase;letter-spacing:.03em;padding:14px 16px;border-bottom:1px solid var(--line);text-align:left}
    .orders-table tbody td{padding:20px 16px;border-bottom:1px solid var(--line);vertical-align:middle}
    .orders-table tbody tr{transition:background var(--transition-fast)}
    .orders-table tbody tr:hover{background:#f8fafc}
    .orders-table th:nth-child(1),.orders-table td:nth-child(1){width:18%}
    .orders-table th:nth-child(2),.orders-table td:nth-child(2){width:15%}
    .orders-table th:nth-child(3),.orders-table td:nth-child(3){width:6%;text-align:center}
    .orders-table th:nth-child(4),.orders-table td:nth-child(4){width:7%;text-align:center}
    .orders-table th:nth-child(5),.orders-table td:nth-child(5){width:11%}
    .orders-table th:nth-child(6),.orders-table td:nth-child(6){width:10%}
    .orders-table th:nth-child(7),.orders-table td:nth-child(7){width:13%}
    .orders-table th:nth-child(8),.orders-table td:nth-child(8){width:18%;text-align:right}
    .orders-table td:nth-child(8){padding-right:18px}
    .student-cell{display:flex;align-items:center;gap:12px;min-width:0}
    .student-avatar{width:50px;height:50px;border-radius:999px;display:grid;place-items:center;background:var(--secondary-soft);color:var(--secondary);border:1px solid #dbeafe;font-weight:900;box-shadow:none;flex:0 0 auto}
    .student-cell strong{display:block;font-size:16px}
    .student-cell small{display:block;margin-top:3px;color:#64748b}
    .order-detail{display:grid;gap:8px;min-width:0}
    .order-detail strong{display:block;color:#0f172a;line-height:1.5}
    .table-pill{display:inline-flex;align-items:center;justify-content:center;min-height:34px;padding:0 12px;border-radius:8px;border:1px solid var(--line);background:var(--surface-soft);font-weight:800;color:#0f172a}
    .table-pill--count{min-width:38px}
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
    .list-actions{display:flex;gap:10px;justify-content:flex-end;align-items:center;flex-wrap:nowrap;min-width:206px}
    .list-actions form{margin:0;flex:0 0 auto}
    .action-save,
    .action-delete{
        appearance:none;
        -webkit-appearance:none;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        width:auto;
        min-width:96px;
        padding:0 16px;
        min-height:40px;
        border-radius:12px;
        box-shadow:none;
        text-decoration:none;
        font-weight:900;
        font-size:14px;
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
    @media (max-width: 900px){
        .staff-hero{align-items:flex-start;flex-direction:column}
        .staff-hero__meta{justify-items:start}
        .stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
        .orders-table{table-layout:auto}
        .orders-table th,.orders-table td{width:auto!important}
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
</style>
@endpush
