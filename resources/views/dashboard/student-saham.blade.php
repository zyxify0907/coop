@extends('layouts.app')

@section('title', 'Dashboard Saham Student')
@section('page-title', 'Dashboard Saham')
@section('page-subtitle', 'Ringkasan saham dan transaksi koperasi student.')

@section('content')
    @php
        $share = $user->saham;
        $isMember = $memberApplication !== null;
        $memberNumber = $isMember ? ($user->no_anggota ?? $memberApplication->data_permohonan['no_anggota'] ?? '-') : 'Belum dijana';
        $memberFee = (float) optional($share)->yuran;
        $currentShare = (float) optional($share)->syer;
        $additionalShare = (float) optional($share)->tambahan_saham;
        $totalShare = $currentShare + $additionalShare;
        $chartItems = [
            ['label' => 'Yuran Ahli', 'value' => $memberFee],
            ['label' => 'Syer Semasa', 'value' => $currentShare],
            ['label' => 'Tambahan Saham', 'value' => $additionalShare],
        ];
        $chartMax = max(1, collect($chartItems)->max('value') ?? 1);
        $chartAxisMax = max(10, (int) ceil($chartMax / 5) * 5);
        $chartAxisMid = $chartAxisMax / 2;
        $typeLabels = [
            'OPENING_BALANCE' => 'Baki Awal',
            'SHARE_ADDITION' => 'Tambah Saham',
            'SHARE_WITHDRAWAL' => 'Pengeluaran Saham',
            'MANUAL_CREATE' => 'Rekod Manual Baru',
            'MANUAL_UPDATE' => 'Kemaskini Manual',
            'MANUAL_ADJUSTMENT' => 'Pelarasan Manual',
        ];
        $applicationTypeLabels = [
            'anggota' => 'Permohonan Anggota',
            'saham' => 'Penambahan Saham',
            'berhenti' => 'Pengeluaran / Berhenti',
            'pengeluaran' => 'Pengeluaran Saham',
            'pindah' => 'Pindah Keahlian',
            'bersara' => 'Persaraan',
        ];
        $statusLabels = [
            'baru' => 'Baharu',
            'semak' => 'Sedang Disemak',
            'pending' => 'Sedang Disemak',
            'dalam_semakan' => 'Sedang Disemak',
            'diluluskan' => 'Diluluskan',
            'ditolak' => 'Ditolak',
        ];
        $statusDescriptions = [
            'baru' => 'Menunggu semakan koperasi',
            'semak' => 'Sedang disemak admin',
            'pending' => 'Sedang disemak admin',
            'dalam_semakan' => 'Sedang disemak admin',
            'diluluskan' => 'Telah diluluskan',
            'ditolak' => 'Tidak diluluskan',
        ];
    @endphp

    <div class="module-dashboard student-share-dashboard">
        <section class="module-hero">
            <div>
                <span>DASHBOARD SAHAM</span>
                <h1>Saham Student</h1>
                <p>Pantau nombor anggota, jumlah saham, permohonan saham dan transaksi terkini.</p>
            </div>
        </section>

        <section class="student-share-actions" aria-label="Tindakan saham">
            <a href="{{ route('student.permohonan.index', ['jenis' => 'saham']) }}">
                <span class="student-share-action__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 15h6"/><path d="M9 11h2"/></svg>
                </span>
                <span class="student-share-action__text"><strong>Permohonan Saham</strong><span>Mohon tambah atau urus saham koperasi.</span></span>
                <span class="student-share-action__chevron" aria-hidden="true">›</span>
            </a>
            <a href="{{ route('student.permohonan.status') }}">
                <span class="student-share-action__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </span>
                <span class="student-share-action__text"><strong>Semak Permohonan</strong><span>Lihat status permohonan yang telah dihantar.</span></span>
                <span class="student-share-action__chevron" aria-hidden="true">›</span>
            </a>
            <a href="{{ route('koperasi.transactions.index') }}">
                <span class="student-share-action__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-4 4 4 5-7"/></svg>
                </span>
                <span class="student-share-action__text"><strong>Transaksi Saham</strong><span>Semak sejarah transaksi saham anda.</span></span>
                <span class="student-share-action__chevron" aria-hidden="true">›</span>
            </a>
        </section>

        <section class="module-metrics student-share-metrics">
            <div><span>No Anggota Pelajar</span><strong>{{ $memberNumber }}</strong><small>{{ $isMember ? 'Anggota aktif' : 'Belum diluluskan' }}</small></div>
            <div><span>Yuran Ahli</span><strong>RM {{ number_format($memberFee, 2) }}</strong><small>Yuran menjadi anggota</small></div>
            <div><span>Syer Semasa</span><strong>RM {{ number_format($currentShare, 2) }}</strong><small>Modal saham asas</small></div>
            <div><span>Tambahan Saham</span><strong>RM {{ number_format($additionalShare, 2) }}</strong><small>Jumlah tambahan diluluskan</small></div>
            <div><span>Jumlah Saham</span><strong>RM {{ number_format($totalShare, 2) }}</strong><small>Keseluruhan saham</small></div>
        </section>

        <section class="module-grid student-share-main">
            <article class="module-panel">
                <header>
                    <div><span>CARTA</span><h2>Ringkasan Saham</h2></div>
                </header>
                <div class="student-vertical-chart">
                    <div class="student-vertical-chart__axis" aria-hidden="true">
                        <span>RM {{ number_format($chartAxisMax, 0) }}</span>
                        <span>RM {{ number_format($chartAxisMid, 0) }}</span>
                        <span>RM 0</span>
                    </div>
                    <div class="student-vertical-chart__plot">
                        @foreach ($chartItems as $item)
                            @php
                                $value = (float) ($item['value'] ?? 0);
                                $height = max(8, ($value / $chartAxisMax) * 190);
                            @endphp
                            <div class="student-vertical-chart__bar">
                                <strong>RM {{ number_format($value, 2) }}</strong>
                                <div class="student-vertical-chart__track">
                                    <span style="height: {{ $height }}px"></span>
                                </div>
                                <p>{{ $item['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>

            <article class="module-panel">
                <header>
                    <div><span>PERMOHONAN</span><h2>Status Permohonan Saham</h2></div>
                    <a href="{{ route('student.permohonan.status') }}">Semak</a>
                </header>
                <div class="module-list">
                    @forelse ($latestApplications as $application)
                        <a class="module-row" href="{{ route('student.permohonan.status') }}">
                            <div>
                                <strong>{{ $applicationTypeLabels[$application->jenis] ?? ucwords(str_replace('_', ' ', $application->jenis)) }}</strong>
                                <small>{{ $application->tarikh_permohonan?->format('d/m/Y') ?? '-' }} - {{ $statusDescriptions[$application->status] ?? 'Sila semak butiran' }}</small>
                            </div>
                            <span class="share-status-label share-status-label--{{ $application->status }}">{{ $statusLabels[$application->status] ?? ucwords(str_replace('_', ' ', $application->status)) }}</span>
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
                    <a href="{{ route('koperasi.transactions.index') }}">Lihat Semua</a>
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
    .content:has(> .student-share-dashboard){background:#F5F8FC}
    .content > .student-share-dashboard{width:min(100% - 48px,1400px);max-width:1400px;margin-inline:auto}
    .student-share-dashboard{display:grid;gap:20px;color:#082F59}
    .student-share-dashboard .module-hero{position:relative;padding:24px 28px;border:1px solid #D8E2EF;border-left:5px solid #082F59;border-radius:12px;background:#fff;box-shadow:0 8px 22px rgba(8,47,89,.035)}
    .student-share-dashboard .module-hero span,.student-share-dashboard .module-panel header span{position:relative;display:inline-flex;align-items:center;color:#1D5FD1;font-size:12px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
    .student-share-dashboard .module-hero span::before{content:"";width:34px;height:4px;margin-right:12px;background:#ED1C2E}
    .student-share-dashboard .module-hero h1{margin:8px 0 0;color:#071A34;font-size:32px;line-height:1.12;font-weight:900;letter-spacing:0}
    .student-share-dashboard .module-hero p{margin:8px 0 0;color:#253B57;font-size:15px;font-weight:800}
    .student-share-actions{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
    .student-share-actions a{display:grid;grid-template-columns:auto minmax(0,1fr) auto;align-items:center;gap:14px;min-height:86px;padding:18px 20px;border:1px solid #D8E2EF;border-radius:12px;background:#fff;color:#082F59;text-decoration:none;box-shadow:0 8px 20px rgba(8,47,89,.025)}
    .student-share-actions a:hover{border-color:#082F59;background:#F7FAFF}
    .student-share-action__icon{display:grid;place-items:center;width:34px;height:34px;color:#082F59}
    .student-share-action__icon svg{width:28px;height:28px}
    .student-share-action__text{display:grid;gap:4px;min-width:0}
    .student-share-action__text strong{color:#071A34;font-size:16px;font-weight:900;line-height:1.25}
    .student-share-action__text span{color:#5F7189;font-size:13px;font-weight:750;line-height:1.35}
    .student-share-action__chevron{display:inline-flex;align-items:center;justify-content:center;color:#082F59;font-size:24px;font-weight:700;line-height:1}
    .student-share-metrics{grid-template-columns:repeat(5,minmax(0,1fr));gap:14px}
    .student-share-metrics div{position:relative;min-height:112px;padding:18px 18px 16px;border:1px solid #D8E2EF;border-radius:12px;background:#fff;box-shadow:0 8px 20px rgba(8,47,89,.025);overflow:hidden}
    .student-share-metrics div::before{content:"";position:absolute;top:0;left:0;right:0;height:3px;background:#082F59}
    .student-share-metrics div:nth-child(4)::before{background:#E89A00}
    .student-share-metrics span{color:#344D6D;font-size:11px;font-weight:900;letter-spacing:.06em;text-transform:uppercase}
    .student-share-metrics strong{color:#071A34;font-size:clamp(22px,1.5vw,26px);font-weight:900;line-height:1.1;letter-spacing:0}
    .student-share-metrics small{color:#5F7189;font-size:12px;font-weight:750;line-height:1.35}
    .student-share-main{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;align-items:stretch}
    .student-share-dashboard .module-panel{display:flex;flex-direction:column;min-height:360px;padding:22px;border:1px solid #D8E2EF;border-radius:12px;background:#fff;box-shadow:0 8px 22px rgba(8,47,89,.035)}
    .student-share-dashboard .module-panel header{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:16px;padding-bottom:13px;border-bottom:1px solid #D8E2EF}
    .student-share-dashboard .module-panel h2{margin:4px 0 0;color:#071A34;font-size:20px;line-height:1.2;font-weight:900;letter-spacing:0}
    .student-share-dashboard .module-panel header a{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 14px;border:1px solid #BFD7FF;border-radius:8px;background:#fff;color:#1D5FD1;text-decoration:none;font-size:13px;font-weight:900}
    .student-share-dashboard .module-panel header a:hover{border-color:#082F59;background:#F7FAFF;color:#082F59}
    .student-vertical-chart{display:grid;grid-template-columns:58px minmax(0,1fr);gap:10px;flex:1;min-height:275px;padding:8px 0 0}
    .student-vertical-chart__axis{display:grid;grid-template-rows:1fr 1fr 1fr;align-items:start;padding-top:22px;color:#5F7189;font-size:12px;font-weight:800;text-align:right}
    .student-vertical-chart__axis span:nth-child(2){align-self:center}
    .student-vertical-chart__axis span:nth-child(3){align-self:end;padding-bottom:28px}
    .student-vertical-chart__plot{position:relative;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;align-items:end;padding:0 6px 0;border-bottom:1px solid #BFCFE3;background:repeating-linear-gradient(to bottom,transparent 0,transparent calc(50% - 1px),#D8E2EF calc(50% - 1px),#D8E2EF 50%,transparent calc(50% + 1px),transparent 100%)}
    .student-vertical-chart__plot::before,.student-vertical-chart__plot::after{content:"";position:absolute;left:0;right:0;border-top:1px dashed #D8E2EF;pointer-events:none}
    .student-vertical-chart__plot::before{top:22px}
    .student-vertical-chart__plot::after{top:calc(50% + 9px)}
    .student-vertical-chart__bar{position:relative;z-index:1;display:grid;grid-template-rows:auto 190px auto;gap:9px;align-items:end;justify-items:center;height:100%;text-align:center}
    .student-vertical-chart__bar strong{color:#071A34;font-size:13px;font-weight:900}
    .student-vertical-chart__track{display:flex;align-items:flex-end;justify-content:center;width:100%;height:190px}
    .student-vertical-chart__track span{display:block;width:58px;min-height:8px;border-radius:7px 7px 0 0;background:#082F59}
    .student-vertical-chart__bar:nth-child(2) .student-vertical-chart__track span{background:#ED1C2E}
    .student-vertical-chart__bar:nth-child(3) .student-vertical-chart__track span{background:#E89A00}
    .student-vertical-chart__bar p{margin:0;color:#253B57;font-size:12px;font-weight:900}
    .student-share-dashboard .module-list{display:grid;gap:8px;flex:1}
    .student-share-dashboard .module-row{display:flex;align-items:center;justify-content:space-between;gap:14px;min-height:58px;padding:13px 14px;border:1px solid #D8E2EF;border-radius:10px;background:#F8FBFF;color:#082F59;text-decoration:none}
    .student-share-dashboard .module-row:hover{border-color:#BFD7FF;background:#fff}
    .student-share-dashboard .module-row strong{display:block;color:#071A34;font-size:14px;font-weight:900}
    .student-share-dashboard .module-row small{display:block;margin-top:4px;color:#5F7189;font-size:12px;font-weight:800}
    .student-share-dashboard .module-grid--full{grid-template-columns:1fr}
    .student-share-dashboard .module-grid--full .module-panel{min-height:auto}
    .student-share-dashboard .module-grid--full .module-row span:not(.share-status-label){display:inline-flex;align-items:center;justify-content:center;min-height:30px;min-width:88px;padding:0 12px;border-radius:999px;background:#EAF2FF;color:#1D5FD1;font-size:12px;font-weight:900}
    .share-status-label{display:inline-flex;align-items:center;justify-content:center;min-height:30px;min-width:86px;padding:0 12px;border-radius:999px;font-size:12px;font-weight:900;white-space:nowrap}
    .share-status-label--baru{background:#EAF2FF;color:#1D5FD1}
    .share-status-label--semak,.share-status-label--pending,.share-status-label--dalam_semakan{background:#FFF4DB;color:#A56500}
    .share-status-label--diluluskan{background:#E7F6EF;color:#14875A}
    .share-status-label--ditolak{background:#FEE2E2;color:#B91C1C}
    .student-share-dashboard .module-empty{padding:22px;border:1px dashed #CBD5E1;border-radius:10px;background:#fff;color:#5F7189;text-align:center;font-weight:800}
    @media (max-width:1280px){.student-share-metrics{grid-template-columns:repeat(3,minmax(0,1fr))}.student-share-dashboard .module-hero h1{font-size:30px}}
    @media (max-width:1100px){.content > .student-share-dashboard{width:100%}.student-share-main{grid-template-columns:1fr}.student-share-dashboard .module-panel{min-height:auto}}
    @media (max-width:900px){.student-share-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}.student-share-actions{grid-template-columns:1fr}.student-share-actions a{min-height:78px}.student-vertical-chart{grid-template-columns:48px minmax(0,1fr)}}
    @media (max-width:768px){.student-share-metrics{grid-template-columns:1fr}.student-share-dashboard .module-hero{padding:22px}.student-share-actions a,.student-share-dashboard .module-row{align-items:flex-start}.student-share-dashboard .module-row{flex-direction:column}.share-status-label{min-width:0}.student-vertical-chart__track span{width:42px}}
    @media (max-width:520px){.content > .student-share-dashboard{width:100%}.student-vertical-chart{grid-template-columns:1fr}.student-vertical-chart__axis{display:none}.student-vertical-chart__plot{gap:10px}.student-vertical-chart__bar p{font-size:11px}}
</style>
@endpush
