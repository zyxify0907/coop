@extends('layouts.app')

@section('title', 'Dashboard Saham Staff')
@section('page-title', 'Dashboard Saham')
@section('page-subtitle', 'Ringkasan saham dan transaksi koperasi staff.')

@section('content')
    @php
        $memberNumber = $staffMemberNumber ?? $user->no_anggota;
        $memberFee = (float) optional($share)->yuran;
        $currentShare = (float) optional($share)->syer;
        $additionalShare = (float) optional($share)->tambahan_saham;
        $totalShare = $currentShare + $additionalShare;
        $chartItems = [
            ['label' => 'Yuran Ahli', 'value' => $memberFee],
            ['label' => 'Syer Semasa', 'value' => $currentShare],
            ['label' => 'Tambahan Saham', 'value' => $additionalShare],
        ];
        $chartAxisMax = 10;
        $chartAxisMid = 5;
        $typeLabels = [
            'OPENING_BALANCE' => 'Baki Awal',
            'SHARE_ADDITION' => 'Tambah Saham',
            'SHARE_WITHDRAWAL' => 'Pengeluaran Saham',
            'MANUAL_CREATE' => 'Rekod Manual Baru',
            'MANUAL_UPDATE' => 'Kemaskini Manual',
            'MANUAL_ADJUSTMENT' => 'Pelarasan Manual',
        ];
    @endphp

    <div class="module-dashboard">
        <section class="module-hero">
            <div>
                <span>DASHBOARD SAHAM</span>
                <h1>Saham Staff</h1>
                <p>Pantau nombor anggota, jumlah saham, permohonan saham dan transaksi terkini.</p>
            </div>
        </section>

        <section class="staff-share-actions" aria-label="Tindakan saham">
            <a href="{{ route($portalPrefix.'.permohonan.index', ['jenis' => 'saham']) }}">
                <strong>Permohonan Saham</strong>
                <span>Mohon tambah atau urus saham koperasi.</span>
            </a>
            <a href="{{ route('student.permohonan.status') }}">
                <strong>Semak Permohonan</strong>
                <span>Lihat status permohonan yang telah dihantar.</span>
            </a>
            <a href="{{ route('koperasi.transactions.index', ['scope' => 'mine']) }}">
                <strong>Transaksi Saham</strong>
                <span>Semak sejarah transaksi saham anda.</span>
            </a>
        </section>

        <section class="module-metrics staff-share-metrics">
            <div><span>No Anggota Staff</span><strong>{{ $memberNumber ?? 'Belum menjadi anggota' }}</strong><small>{{ $user->staff_type_label }}</small></div>
            <div><span>Yuran Ahli</span><strong>RM {{ number_format($memberFee, 2) }}</strong><small>Yuran menjadi anggota</small></div>
            <div><span>Syer Semasa</span><strong>RM {{ number_format($currentShare, 2) }}</strong><small>Modal saham asas</small></div>
            <div><span>Tambahan Saham</span><strong>RM {{ number_format($additionalShare, 2) }}</strong><small>Jumlah tambahan diluluskan</small></div>
            <div><span>Jumlah Saham</span><strong>RM {{ number_format($totalShare, 2) }}</strong><small>Keseluruhan saham</small></div>
        </section>

        <section class="module-grid">
            <article class="module-panel">
                <header>
                    <div><span>CARTA</span><h2>Ringkasan Saham</h2></div>
                </header>
                <div class="staff-vertical-chart">
                    <div class="staff-vertical-chart__axis" aria-hidden="true">
                        <span>RM {{ number_format($chartAxisMax, 0) }}</span>
                        <span>RM {{ number_format($chartAxisMid, 0) }}</span>
                        <span>RM 0</span>
                    </div>
                    <div class="staff-vertical-chart__plot">
                        @foreach ($chartItems as $item)
                            @php
                                $value = (float) ($item['value'] ?? 0);
                                $height = $value > 0 ? min(176, max(10, ($value / $chartAxisMax) * 176)) : 0;
                            @endphp
                            <div class="staff-vertical-chart__bar">
                                <div class="staff-vertical-chart__track" style="--bar-height: {{ $height }}px">
                                    <strong>RM {{ number_format($value, 2) }}</strong>
                                    <span @class(['is-empty' => $value <= 0])></span>
                                </div>
                                <p>{{ $item['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>

            <article class="module-panel module-panel--applications">
                <header>
                    <div><span>PERMOHONAN</span><h2>Status Permohonan Saham</h2></div>
                    <a href="{{ route($portalPrefix.'.permohonan.index') }}">Semak</a>
                </header>
                <div class="module-list">
                    @forelse ($latestApplications as $application)
                        <a class="module-row" href="{{ route($portalPrefix.'.permohonan.index') }}">
                            <div><strong>{{ ucwords(str_replace('_', ' ', $application->jenis)) }}</strong><small>{{ $application->tarikh_permohonan?->format('d/m/Y') ?? '-' }}</small></div>
                            <span>{{ ucfirst(str_replace('_', ' ', $application->status)) }}</span>
                        </a>
                    @empty
                        <div class="module-empty">Tiada permohonan saham direkodkan.</div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="module-grid module-grid--full">
            <article class="module-panel">
                <header>
                    <div><span>TRANSAKSI</span><h2>Transaksi Saham Terkini</h2></div>
                    <a href="{{ route('koperasi.transactions.index', ['scope' => 'mine']) }}">Lihat Semua</a>
                </header>
                <div class="module-list">
                    @forelse ($recentTransactions as $transaction)
                        <div class="module-row">
                            <div><strong>{{ $typeLabels[$transaction->transaction_type] ?? ucwords(strtolower(str_replace('_', ' ', $transaction->transaction_type))) }}</strong><small>{{ $transaction->transacted_at?->format('d/m/Y') ?? '-' }}</small></div>
                            <span>RM {{ number_format((float) $transaction->amount, 2) }}</span>
                        </div>
                    @empty
                        <div class="module-empty">Tiada transaksi saham terkini.</div>
                    @endforelse
                </div>
            </article>
        </section>
    </div>
@endsection

@include('admin.dashboards.styles')

@push('styles')
<style>
    .staff-share-metrics{grid-template-columns:repeat(5,minmax(0,1fr))}
    .staff-share-metrics strong{font-size:24px}
    .staff-share-actions{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
    .staff-share-actions a{display:grid;gap:6px;min-height:94px;padding:18px;border:1px solid #D7E3F5;border-radius:12px;background:#fff;color:inherit;text-decoration:none}
    .staff-share-actions a:hover{border-color:#AFC7EA;background:#F8FBFF}
    .staff-share-actions strong{color:#0F172A;font-size:16px;font-weight:900}
    .staff-share-actions span{color:#64748B;font-size:13px;font-weight:750;line-height:1.45}
    .staff-vertical-chart{display:grid;grid-template-columns:58px minmax(0,1fr);gap:10px;flex:1;min-height:260px;padding:8px 0 0}
    .staff-vertical-chart__axis{display:grid;grid-template-rows:1fr 1fr 1fr;align-items:start;padding-top:30px;color:#64748B;font-size:12px;font-weight:800;text-align:right}
    .staff-vertical-chart__axis span:nth-child(2){align-self:center}
    .staff-vertical-chart__axis span:nth-child(3){align-self:end;padding-bottom:58px}
    .staff-vertical-chart__plot{position:relative;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;align-items:end;padding:28px 6px 0;background:linear-gradient(to bottom,transparent 0,transparent 27px,#D7E3F5 27px,#D7E3F5 28px,transparent 28px,transparent calc(50% + 13px),#D7E3F5 calc(50% + 13px),#D7E3F5 calc(50% + 14px),transparent calc(50% + 14px),transparent 203px,#CBD5E1 203px,#CBD5E1 204px,transparent 204px)}
    .staff-vertical-chart__plot::before,.staff-vertical-chart__plot::after{content:"";position:absolute;left:0;right:0;border-top:1px dashed #D7E3F5;pointer-events:none}
    .staff-vertical-chart__plot::before{top:28px}
    .staff-vertical-chart__plot::after{top:calc(50% + 14px)}
    .staff-vertical-chart__bar{position:relative;z-index:1;display:grid;grid-template-rows:176px auto;gap:8px;align-items:end;justify-items:center;height:100%;text-align:center}
    .staff-vertical-chart__bar strong{position:absolute;left:50%;bottom:calc(var(--bar-height) + 8px);transform:translateX(-50%);color:#0F172A;font-size:14px;font-weight:900;white-space:nowrap}
    .staff-vertical-chart__track{position:relative;display:flex;align-items:flex-end;justify-content:center;width:100%;height:176px}
    .staff-vertical-chart__track span{display:block;width:58px;height:var(--bar-height);min-height:10px;border-radius:7px 7px 0 0;background:#082F59;color:#082F59;box-shadow:0 10px 22px rgba(8,47,89,.16)}
    .staff-vertical-chart__track span.is-empty{height:0;min-height:0;border-radius:999px;border-top:8px solid currentColor;background:transparent;box-shadow:none}
    .staff-vertical-chart__bar:nth-child(2) .staff-vertical-chart__track span{background:#ED1C2E;color:#ED1C2E;box-shadow:0 10px 22px rgba(237,28,46,.16)}
    .staff-vertical-chart__bar:nth-child(3) .staff-vertical-chart__track span{background:#E89A00;color:#E89A00;box-shadow:0 10px 22px rgba(232,154,0,.18)}
    .staff-vertical-chart__bar .staff-vertical-chart__track span.is-empty{background:transparent;box-shadow:none}
    .staff-vertical-chart__bar p{align-self:start;min-height:30px;margin:0;color:#334155;font-size:13px;font-weight:900;line-height:1.25}
    .module-panel--applications{min-height:360px;align-self:stretch}
    .module-panel--applications .module-list{flex:none}
    .module-panel--applications .module-row{min-height:74px;padding:16px 18px;background:#fff;border-left:4px solid #082F59;box-shadow:0 6px 16px rgba(8,47,89,.035)}
    @media (max-width:1280px){.staff-share-metrics{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @media (max-width:900px){.staff-share-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}.staff-share-actions{grid-template-columns:1fr}.staff-vertical-chart{grid-template-columns:48px minmax(0,1fr)}}
    @media (max-width:640px){.staff-share-metrics{grid-template-columns:1fr}.staff-vertical-chart{grid-template-columns:42px minmax(0,1fr);gap:8px}.staff-vertical-chart__plot{gap:8px;padding-inline:2px}.staff-vertical-chart__axis{font-size:11px}.staff-vertical-chart__bar p{font-size:11px;line-height:1.2}.staff-vertical-chart__bar strong{font-size:11px}.staff-vertical-chart__track span{width:34px}}
</style>
@endpush
