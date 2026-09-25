@extends('layouts.app')

@section('title', 'Semak Permohonan')
@section('page-title', 'Semak Permohonan')
@section('page-subtitle', 'Semak status permohonan dan dokumen yang telah dihantar.')

@php
    $portalLabel = $portalLabel ?? ($role === 'staff' ? 'STAFF PORTAL' : 'STUDENT PORTAL');
    $shareDashboardRoute = match (true) {
        ($role ?? null) === 'ahli' => 'student.dashboard.saham',
        ($role ?? null) === 'staff' && ($user->staff_type ?? null) === 'lecturer_member' => 'lecturer-member.dashboard.saham',
        ($role ?? null) === 'staff' && ($user->staff_type ?? null) === 'clothing_staff' => 'clothing-staff.dashboard.saham',
        ($role ?? null) === 'staff' && ($user->staff_type ?? null) === 'share_staff' => 'share-staff.dashboard.saham',
        ($role ?? null) === 'staff' && ($user->staff_type ?? null) === 'coop_manager' => 'coop-manager.dashboard.saham',
        default => null,
    };
    $applicationLabels = [
        'anggota' => 'Permohonan Anggota',
        'saham' => 'Permohonan Penambahan Saham',
        'berhenti' => 'Pengeluaran / Berhenti',
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
        'baru' => 'Permohonan telah dihantar dan menunggu semakan.',
        'semak' => 'Permohonan sedang disemak oleh pihak koperasi.',
        'pending' => 'Permohonan sedang disemak oleh pihak koperasi.',
        'dalam_semakan' => 'Permohonan sedang disemak oleh pihak koperasi.',
        'diluluskan' => 'Permohonan telah diluluskan.',
        'ditolak' => 'Permohonan tidak diluluskan. Sila rujuk catatan admin.',
    ];
    $documentLabels = [
        'salinan_ic' => 'Salinan IC',
        'slip_bayaran' => 'Slip / Bukti Bayaran',
        'surat_pindah_berhenti_persaraan' => 'Surat Sokongan',
    ];
@endphp

@section('content')
    @include('components.coop-page-style')

    <div class="coop-wrap status-page">
        <section class="status-hero">
            <div>
                <span class="coop-kicker">{{ $portalLabel }}</span>
                <h1>Semak Permohonan</h1>
                <p>Rekod permohonan dan dokumen yang telah anda hantar.</p>
            </div>
            <div class="student-actions">
                @if ($shareDashboardRoute)
                    <a class="student-share-backlink" href="{{ route($shareDashboardRoute) }}">Dashboard Saham</a>
                @endif
            </div>
        </section>

        <section class="status-summary" aria-label="Ringkasan permohonan">
            <div class="status-summary__item">
                <span>Jumlah Permohonan</span>
                <strong>{{ $applications->total() }}</strong>
            </div>
            <div class="status-summary__item">
                <span>Slip & Dokumen</span>
                <strong>{{ $documents->total() }}</strong>
            </div>
            <div class="status-summary__note">Setiap fail yang dihantar melalui borang permohonan akan dipaparkan di sini.</div>
        </section>

        <section class="panel status-panel">
            <div class="status-panel__head">
                <div>
                    <span class="status-panel__eyebrow">PERMOHONAN</span>
                    <h2>Status Permohonan Saham</h2>
                    <p>Semak tahap permohonan, maksud status dan catatan daripada admin.</p>
                </div>
            </div>
            <div class="status-table-wrap">
                <table class="status-table">
                    <thead>
                        <tr><th>Permohonan</th><th>Tarikh Hantar</th><th>Status Semasa</th><th>Catatan Admin</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $application)
                            <tr>
                                <td><strong>#{{ $application->id_permohonan }}</strong><span>{{ $applicationLabels[$application->jenis] ?? ucfirst($application->jenis) }}</span></td>
                                <td>{{ optional($application->tarikh_permohonan)->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    <span class="status-pill status-pill--{{ $application->status }}">{{ $statusLabels[$application->status] ?? ucwords(str_replace('_', ' ', $application->status)) }}</span>
                                    <small class="status-explain">{{ $statusDescriptions[$application->status] ?? 'Sila semak catatan admin untuk maklumat lanjut.' }}</small>
                                </td>
                                <td>{{ $application->catatan_admin ?: 'Belum ada catatan admin.' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="status-empty">Belum ada permohonan dihantar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="status-panel__footer">{{ $applications->links() }}</div>
        </section>

        <section class="panel status-panel">
            <div class="status-panel__head">
                <div>
                    <span class="status-panel__eyebrow">DOKUMEN</span>
                    <h2>Slip & Dokumen Dimuat Naik</h2>
                    <p>Lihat atau muat turun fail yang telah dilampirkan pada permohonan anda.</p>
                </div>
            </div>
            <div class="status-table-wrap">
                <table class="status-table">
                    <thead>
                        <tr><th>Dokumen</th><th>Tarikh Muat Naik</th><th>Saiz</th><th>Tindakan</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $document)
                            <tr>
                                <td>
                                    <div class="document-type">{{ strtoupper(pathinfo($document->original_name, PATHINFO_EXTENSION) ?: 'FILE') }}</div>
                                    <div class="document-name"><strong>{{ $documentLabels[$document->category] ?? $document->category }}</strong><span>{{ $document->original_name }}</span></div>
                                </td>
                                <td>{{ $document->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>{{ number_format($document->size / 1024, 1) }} KB</td>
                                <td>
                                    <div class="status-actions">
                                        <a href="{{ route('koperasi.documents.view', $document) }}" target="_blank" rel="noopener">Lihat</a>
                                        <a href="{{ route('koperasi.documents.download', $document) }}">Muat Turun</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="status-empty">Belum ada dokumen dimuat naik.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="status-panel__footer">{{ $documents->links() }}</div>
        </section>
    </div>
@endsection

@push('styles')
<style>
    .content:has(.status-page){background:#F4F7FB}
    .status-page{
        --status-navy:#082F59;
        --status-red:#ED1C2E;
        --status-blue:#0B5ED7;
        --status-border:#D7E2EF;
        --status-muted:#526987;
        width:min(100% - clamp(24px,4vw,64px),1500px);
        display:grid;
        gap:18px;
        color:var(--status-navy)
    }
    .content .status-page .status-hero.coop-hero{background:#fff!important;background-image:none!important}
    .student-share-backlink{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 18px;border:1px solid #BFD7FF;border-radius:8px;background:#fff;color:var(--status-blue);font-size:14px;font-weight:900;text-decoration:none;white-space:nowrap}
    .student-share-backlink::before{content:'\2190';margin-right:8px;color:var(--status-red);font-weight:900}
    .student-share-backlink:hover{border-color:#8FB8F8;background:#F8FBFF}
    .status-hero{position:relative;display:flex;align-items:center;justify-content:space-between;gap:22px;min-height:118px;padding:24px 28px 24px 40px!important;border:1px solid var(--status-border)!important;border-left:6px solid var(--status-navy)!important;border-radius:10px!important;background:#fff!important;background-image:none!important;box-shadow:0 8px 22px rgba(8,47,89,.045)!important;overflow:hidden}
    .status-hero::before{content:none}
    .status-hero .coop-kicker{display:inline-flex;min-height:24px;align-items:center;gap:14px;margin:0 0 10px;padding:0;border-radius:0;background:transparent;color:var(--status-blue);font-size:12px;font-weight:900;letter-spacing:.12em;text-transform:uppercase}
    .status-hero .coop-kicker::before{content:"";width:34px;height:4px;border-radius:999px;background:var(--status-red)}
    .status-hero h1{margin:0;color:#061A3A;font-size:clamp(30px,2.8vw,42px);line-height:1.05;font-weight:900;letter-spacing:0}
    .status-hero p,.status-panel__head p{margin:8px 0 0;color:#31517D;font-size:15px;font-weight:650}
    .status-summary{display:grid;grid-template-columns:repeat(2,minmax(170px,220px)) minmax(0,1fr);gap:14px;border:0;background:transparent;overflow:visible}
    .status-summary__item,.status-summary__note{border:1px solid var(--status-border);border-radius:8px;background:#fff;box-shadow:0 8px 18px rgba(8,47,89,.04)}
    .status-summary__item{display:grid;align-content:center;min-height:104px;padding:20px 22px;border-top:5px solid var(--status-blue)}
    .status-summary__item span,.status-summary__note{display:block;color:var(--status-muted);font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:0}
    .status-summary__item strong{display:block;margin-top:8px;color:#061A3A;font-size:30px;line-height:1;font-weight:900}
    .status-summary__note{display:flex;align-items:center;min-height:104px;padding:20px 24px;line-height:1.55;text-transform:none;letter-spacing:0}
    .status-panel{overflow:hidden;border:1px solid var(--status-border)!important;border-radius:10px!important;background:#fff!important;box-shadow:0 8px 18px rgba(8,47,89,.045)!important}
    .status-panel__head{padding:22px 24px 18px;border-bottom:1px solid var(--status-border);background:#fff}
    .status-panel__eyebrow{display:block;margin:0 0 9px;padding-left:15px;color:var(--status-blue);font-size:11px;font-weight:900;letter-spacing:.04em;text-transform:uppercase}
    .status-panel__head h2{position:relative;margin:0;padding-left:15px;color:var(--status-navy);font-size:22px;line-height:1.18;font-weight:900}
    .status-panel__head h2::before{content:"";position:absolute;left:0;top:0;width:4px;height:28px;border-radius:999px;background:var(--status-red)}
    .status-panel__head p{padding-left:15px}
    .status-table-wrap{overflow-x:auto}
    .status-table{width:100%;min-width:760px;border-collapse:collapse}
    .status-table th{padding:13px 18px;background:#F5F8FC;color:#31517D;font-size:12px;font-weight:900;text-align:left;text-transform:uppercase}
    .status-table td{padding:18px;border-bottom:1px solid var(--status-border);vertical-align:middle;color:#061A3A;font-weight:650}
    .status-table tbody tr:hover{background:#F8FBFF}
    .status-table td strong,.status-table td span{display:block}
    .status-table td span{margin-top:5px;color:var(--status-muted);font-size:12px;font-weight:700}
    .status-table td .status-pill{display:inline-flex;margin-top:0;align-items:center;min-height:28px;border-radius:999px;padding:0 10px;color:inherit;font-size:12px;font-weight:900}
    .status-explain{display:block;max-width:310px;margin-top:8px;color:var(--status-muted);font-size:12px;font-weight:750;line-height:1.45}
    .status-panel__footer{padding:14px 20px;background:#fff}
    .status-empty{padding:28px!important;color:var(--status-muted);text-align:center}
    .status-actions{display:flex;gap:8px;flex-wrap:wrap}
    .status-actions a{min-height:34px;display:inline-flex;align-items:center;padding:0 12px;border:1px solid #BFD7FF;border-radius:7px;background:#EAF2FF;color:var(--status-blue);font-size:13px;font-weight:900;text-decoration:none}
    .status-actions a:hover{background:var(--status-blue);color:#fff}
    .document-type{width:42px;height:34px;display:inline-grid;place-items:center;float:left;margin:1px 12px 0 0;border:1px solid #BFD7FF;border-radius:7px;background:#EAF2FF;color:var(--status-blue);font-size:10px;font-weight:900}
    .document-name{min-height:36px;display:grid;align-content:center}
    .status-pill--baru{background:#EAF2FF;color:var(--status-blue)}
    .status-pill--semak,.status-pill--pending,.status-pill--dalam_semakan{background:#FFF3D9;color:#B45309}
    .status-pill--diluluskan{background:#DDF7E8;color:#087A45}
    .status-pill--ditolak{background:#FFE3E7;color:#B4232D}
    @media (max-width:760px){.status-page{width:min(100% - 24px,1500px)}.status-hero{align-items:flex-start;flex-direction:column;padding:22px 20px 22px 32px!important}.status-summary{grid-template-columns:1fr 1fr}.status-summary__note{grid-column:1 / -1}.student-share-backlink{width:100%}}
    @media (max-width:480px){.status-summary{grid-template-columns:1fr}.status-actions a{width:100%;justify-content:center}}
</style>
@endpush
