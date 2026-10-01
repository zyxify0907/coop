@extends('layouts.app')

@section('title', 'Ringkasan Pentadbir')
@section('page-title', 'Ringkasan Pentadbir')
@section('page-subtitle', 'Pantau angka utama, permohonan yang menunggu tindakan, saham dan tempahan koperasi dalam satu paparan rasmi.')

@section('content')
    @include('components.coop-page-style')

    @php
        $statusChart = [
            ['label' => 'Baru', 'value' => $stats['applications_new'] ?? 0, 'class' => 'info'],
            ['label' => 'Semakan', 'value' => $stats['applications_review'] ?? 0, 'class' => 'warning'],
            ['label' => 'Diluluskan', 'value' => $stats['applications_approved'] ?? 0, 'class' => 'success'],
            ['label' => 'Ditolak', 'value' => $stats['applications_rejected'] ?? 0, 'class' => 'danger'],
        ];
        $typeChart = [
            ['label' => 'Anggota', 'value' => $stats['applications_membership_total'] ?? 0],
            ['label' => 'Tambah Saham', 'value' => $stats['applications_share_total'] ?? 0],
            ['label' => 'Pengeluaran / Berhenti', 'value' => $stats['applications_withdrawal_total'] ?? 0],
        ];
        $shareChart = [
            ['label' => 'Pelajar', 'value' => (float) ($stats['student_shares'] ?? 0)],
            ['label' => 'Staff', 'value' => (float) ($stats['staff_shares'] ?? 0)],
        ];
        $orderChart = [
            ['label' => 'Baru', 'value' => $stats['orders_new'] ?? 0],
            ['label' => 'Diproses', 'value' => $stats['orders_processing'] ?? 0],
            ['label' => 'Belum Ambil', 'value' => $stats['orders_waiting_pickup'] ?? 0],
            ['label' => 'Diambil', 'value' => $stats['orders_picked'] ?? 0],
        ];
        $statusMax = max(1, collect($statusChart)->max('value'));
        $typeMax = max(1, collect($typeChart)->max('value'));
        $shareMax = max(1, collect($shareChart)->max('value'));
        $orderMax = max(1, collect($orderChart)->max('value'));
        $typeTotal = max(1, collect($typeChart)->sum('value'));
        $typeSegments = [];
        $typeColors = ['#2563EB', '#F59E0B', '#22C55E'];
        $typeOffset = 0;
        foreach ($typeChart as $index => $item) {
            $slice = ($item['value'] / $typeTotal) * 360;
            $typeSegments[] = ($typeColors[$index] ?? '#CBD5E1').' '.round($typeOffset, 2).'deg '.round($typeOffset + $slice, 2).'deg';
            $typeOffset += $slice;
        }
        $typeDonut = empty($typeSegments) ? '#E2E8F0 0deg 360deg' : implode(', ', $typeSegments);
    @endphp

    <div class="coop-wrap admin-dashboard">
        <section class="coop-hero admin-hero">
            <div>
                <span class="coop-kicker">PUSAT PANTAUAN PENTADBIR</span>
                <h1>Ringkasan Pentadbir</h1>
                <p>Fokuskan semakan harian kepada anggota, permohonan, jumlah saham, tempahan baju dan rekod yang masih menunggu tindakan.</p>
            </div>
            <div class="admin-hero__meta">
                <span>Tarikh</span>
                <strong>{{ now()->format('d M Y') }}</strong>
            </div>
        </section>

        <section class="metric-section">
            <div class="metric-grid metric-grid--primary">
                <a class="metric-card" href="{{ route('admin.anggota.index') }}">
                    <span class="metric-card__label">Jumlah Anggota Aktif</span>
                    <strong>{{ number_format($stats['active_students']) }}</strong>
                    <small>Rekod anggota koperasi aktif</small>
                </a>
                <a class="metric-card metric-card--attention" href="{{ route('admin.permohonan.index') }}">
                    <span class="metric-card__label">Jumlah Permohonan Keseluruhan</span>
                    <strong>{{ number_format($stats['total_applications']) }}</strong>
                    <small>Semua permohonan yang direkodkan</small>
                </a>
                <a class="metric-card" href="{{ route('admin.saham.index') }}">
                    <span class="metric-card__label">Jumlah Saham Keseluruhan</span>
                    <strong>RM {{ number_format($stats['total_shares'], 2) }}</strong>
                    <small>Pelajar dan staff</small>
                </a>
                <a class="metric-card" href="{{ route('admin.tempahan.index') }}">
                    <span class="metric-card__label">Jumlah Tempahan Baju</span>
                    <strong>{{ number_format($stats['orders_total']) }}</strong>
                    <small>Semua rekod tempahan</small>
                </a>
            </div>
        </section>

        <section class="metric-section">
            <div class="metric-grid metric-grid--secondary">
                <a class="metric-card metric-card--soft" href="{{ route('admin.users.students') }}">
                    <span class="metric-card__label">Jumlah Pelajar</span>
                    <strong>{{ number_format($stats['students']) }}</strong>
                    <small>Semua akaun student</small>
                </a>
                <a class="metric-card metric-card--soft" href="{{ route('admin.users.staff') }}">
                    <span class="metric-card__label">Jumlah Staff</span>
                    <strong>{{ number_format($stats['staff']) }}</strong>
                    <small>Semua kategori staff</small>
                </a>
                <a class="metric-card metric-card--soft" href="{{ route('admin.users.staff', ['staff_type' => 'coop_staff']) }}">
                    <span class="metric-card__label">Jumlah Pekerja Koperasi</span>
                    <strong>{{ number_format($stats['coop_workers']) }}</strong>
                    <small>Staff jenis coop staff</small>
                </a>
                <div class="metric-card metric-card--soft">
                    <span class="metric-card__label">Jumlah Tidak Aktif / Berhenti</span>
                    <strong>{{ number_format($stats['inactive_total']) }}</strong>
                    <small>Pelajar dan staff tidak aktif</small>
                </div>
            </div>
        </section>

        <section class="chart-grid">
            <article class="coop-panel chart-panel">
                <header class="coop-panel-head">
                    <div>
                        <h2>Carta Status Permohonan</h2>
                    </div>
                </header>
                <div class="coop-body">
                    <div class="chart-bars chart-bars--four">
                        @foreach ($statusChart as $item)
                            <div class="chart-bar">
                                <span class="chart-value">{{ number_format($item['value']) }}</span>
                                <div class="chart-rail">
                                    <div class="chart-fill {{ $item['class'] }}" style="height: {{ max(16, round(($item['value'] / $statusMax) * 140)) }}px;"></div>
                                </div>
                                <span class="chart-label">{{ $item['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>

            <article class="coop-panel chart-panel">
                <header class="coop-panel-head">
                    <div>
                        <h2>Carta Permohonan Mengikut Jenis</h2>
                    </div>
                </header>
                <div class="coop-body">
                    <div class="donut-layout">
                        <div class="donut-chart-wrap">
                            <div class="donut-chart" style="background: conic-gradient({{ $typeDonut }});">
                                <div class="donut-chart__inner">
                                    <span>Jumlah</span>
                                    <strong>{{ number_format(collect($typeChart)->sum('value')) }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="donut-legend">
                            @foreach ($typeChart as $index => $item)
                                <div class="donut-legend__item">
                                    <span class="donut-legend__dot" style="background: {{ $typeColors[$index] ?? '#CBD5E1' }}"></span>
                                    <div class="donut-legend__copy">
                                        <strong>{{ $item['label'] }}</strong>
                                        <small>{{ number_format($item['value']) }} permohonan</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </article>

            <article class="coop-panel chart-panel">
                <header class="coop-panel-head">
                    <div>
                        <h2>Carta Saham Pelajar vs Staff</h2>
                    </div>
                </header>
                <div class="coop-body">
                    <div class="compare-list">
                        @foreach ($shareChart as $item)
                            <div class="compare-item">
                                <div class="compare-item__head">
                                    <span>{{ $item['label'] }}</span>
                                    <strong>RM {{ number_format($item['value'], 2) }}</strong>
                                </div>
                                <div class="compare-track">
                                    <div class="compare-fill" style="width: {{ max(8, round(($item['value'] / $shareMax) * 100)) }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>

            <article class="coop-panel chart-panel">
                <header class="coop-panel-head">
                    <div>
                        <h2>Carta Tempahan Baju Mengikut Status</h2>
                    </div>
                </header>
                <div class="coop-body">
                    <div class="compare-list compare-list--status">
                        @foreach ($orderChart as $item)
                            <div class="compare-item">
                                <div class="compare-item__head">
                                    <span>{{ $item['label'] }}</span>
                                    <strong>{{ number_format($item['value']) }}</strong>
                                </div>
                                <div class="compare-track">
                                    <div class="compare-fill compare-fill--orders" style="width: {{ max(8, round(($item['value'] / $orderMax) * 100)) }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>
        </section>

    </div>
@endsection

@push('styles')
    <style>
        .admin-dashboard { gap: 24px; }
        .admin-hero { align-items: center; }
        .admin-dashboard .coop-hero.admin-hero {
            background: #FFFFFF !important;
            border-left-color: #2453A6 !important;
        }
        .admin-hero__meta {
            min-width: 160px;
            padding: 16px 18px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            text-align: right;
        }
        .admin-hero__meta span,
        .admin-hero__meta strong { display: block; }
        .admin-hero__meta span {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .admin-hero__meta strong {
            margin-top: 5px;
            color: var(--ink);
            font-size: 20px;
        }
        .metric-section,
        .chart-grid { display: grid; gap: 16px; }
        .section-copy h2 {
            margin: 0;
            font-size: 20px;
        }
        .section-copy p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 14px;
        }
        .metric-grid {
            display: grid;
            gap: 16px;
        }
        .metric-grid--primary,
        .metric-grid--secondary {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .metric-card {
            display: grid;
            gap: 8px;
            min-height: 158px;
            padding: 22px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            color: inherit;
            text-decoration: none;
        }
        .metric-card:hover {
            border-color: #BFDBFE;
            background: #F8FAFC;
        }
        .metric-card--soft {
            min-height: 138px;
        }
        .metric-card--attention {
            border-color: #FDE68A;
            background: #FFFBEB;
        }
        .metric-card__label {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }
        .metric-card strong {
            color: var(--ink);
            font-size: clamp(28px, 2.8vw, 36px);
            line-height: 1.1;
        }
        .metric-card small {
            color: var(--muted);
            font-size: 13px;
            font-weight: 600;
        }
        .chart-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .chart-panel { overflow: hidden; }
        .chart-bars {
            display: grid;
            align-items: end;
            gap: 18px;
            min-height: 240px;
        }
        .donut-layout {
            display: grid;
            grid-template-columns: 220px 1fr;
            gap: 24px;
            align-items: center;
        }
        .donut-chart-wrap {
            display: flex;
            justify-content: center;
        }
        .donut-chart {
            width: 190px;
            height: 190px;
            border-radius: 50%;
            display: grid;
            place-items: center;
        }
        .donut-chart__inner {
            width: 118px;
            height: 118px;
            border-radius: 50%;
            background: #fff;
            display: grid;
            place-items: center;
            text-align: center;
            box-shadow: inset 0 0 0 1px var(--line);
        }
        .donut-chart__inner span {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .donut-chart__inner strong {
            color: var(--ink);
            font-size: 28px;
            line-height: 1;
        }
        .donut-legend {
            display: grid;
            gap: 12px;
        }
        .donut-legend__item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
        }
        .donut-legend__dot {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            flex: 0 0 12px;
        }
        .donut-legend__copy {
            display: grid;
            gap: 2px;
        }
        .donut-legend__copy strong {
            color: var(--ink);
            font-size: 14px;
        }
        .donut-legend__copy small {
            color: var(--muted);
            font-size: 13px;
        }
        .chart-bars--four { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .chart-bars--three { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .chart-bar {
            display: grid;
            gap: 10px;
            justify-items: center;
        }
        .chart-value {
            color: var(--ink);
            font-size: 13px;
            font-weight: 700;
        }
        .chart-rail {
            width: 100%;
            min-height: 150px;
            display: flex;
            align-items: end;
            justify-content: center;
            border-bottom: 1px solid var(--line);
        }
        .chart-fill {
            width: 58px;
            max-width: 100%;
            border-radius: 10px 10px 0 0;
            background: #93C5FD;
        }
        .chart-fill.info { background: #60A5FA; }
        .chart-fill.warning { background: #F59E0B; }
        .chart-fill.success { background: #22C55E; }
        .chart-fill.danger { background: #F87171; }
        .chart-label {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
            text-align: center;
        }
        .compare-list {
            display: grid;
            gap: 18px;
        }
        .compare-item {
            display: grid;
            gap: 10px;
        }
        .compare-item__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .compare-item__head span {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }
        .compare-item__head strong {
            color: var(--ink);
            font-size: 16px;
        }
        .compare-track {
            height: 12px;
            overflow: hidden;
            border-radius: 999px;
            background: #E2E8F0;
        }
        .compare-fill {
            height: 100%;
            border-radius: inherit;
            background: #2453A6;
        }
        .compare-fill--orders { background: #3B82F6; }
        @media (max-width: 1280px) {
            .metric-grid--primary,
            .metric-grid--secondary,
            .chart-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 860px) {
            .admin-hero {
                flex-direction: column;
                align-items: flex-start;
            }
            .admin-hero__meta {
                width: 100%;
                text-align: left;
            }
            .metric-grid--primary,
            .metric-grid--secondary,
            .chart-grid {
                grid-template-columns: 1fr;
            }
            .donut-layout {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 640px) {
            .chart-bars--four {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .chart-bars--three {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush
