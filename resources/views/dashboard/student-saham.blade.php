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
                <h1>Saham Student</h1>
                <p>Pantau nombor anggota, jumlah saham, permohonan saham dan transaksi terkini.</p>
            </div>
            <a href="{{ route('student.permohonan.index', ['jenis' => 'saham']) }}">Tambah Saham</a>
        </section>

        <section class="module-metrics student-share-metrics">
            <div><span>No Anggota</span><strong>{{ $memberNumber }}</strong><small>{{ $isMember ? 'Anggota aktif' : 'Belum diluluskan' }}</small></div>
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
                <div class="student-vertical-chart">
                    @foreach ($chartItems as $item)
                        @php
                            $value = (float) ($item['value'] ?? 0);
                            $height = max(8, ($value / $chartMax) * 180);
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
            </article>

            <article class="module-panel">
                <header>
                    <div><span>PERMOHONAN</span><h2>Status Permohonan Saham</h2></div>
                    <a href="{{ route('student.permohonan.status') }}">Semak</a>
                </header>
                <div class="module-list">
                    @forelse ($latestApplications as $application)
                        <a class="module-row" href="{{ route('student.permohonan.status') }}">
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
    .student-share-metrics{grid-template-columns:repeat(5,minmax(0,1fr))}
    .student-share-metrics strong{font-size:24px}
    .student-vertical-chart{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;align-items:end;min-height:270px;padding:12px 6px 0;border-bottom:1px solid #D7E3F5}
    .student-vertical-chart__bar{display:grid;grid-template-rows:auto 190px auto;gap:10px;align-items:end;justify-items:center;height:100%;text-align:center}
    .student-vertical-chart__bar strong{color:#0F172A;font-size:14px;font-weight:900}
    .student-vertical-chart__track{display:flex;align-items:flex-end;justify-content:center;width:100%;height:190px;border-bottom:1px solid #CBD5E1}
    .student-vertical-chart__track span{display:block;width:min(84px,70%);min-height:8px;border-radius:10px 10px 0 0;background:#2453A6;box-shadow:0 10px 22px rgba(36,83,166,.16)}
    .student-vertical-chart__bar:nth-child(2) .student-vertical-chart__track span{background:#10B981;box-shadow:0 10px 22px rgba(16,185,129,.16)}
    .student-vertical-chart__bar:nth-child(3) .student-vertical-chart__track span{background:#F59E0B;box-shadow:0 10px 22px rgba(245,158,11,.18)}
    .student-vertical-chart__bar p{margin:0;color:#334155;font-size:13px;font-weight:900}
    @media (max-width:1280px){.student-share-metrics{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @media (max-width:900px){.student-share-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width:640px){.student-share-metrics{grid-template-columns:1fr}.student-vertical-chart{gap:10px}.student-vertical-chart__track span{width:min(58px,76%)}}
</style>
@endpush
