@extends('layouts.app')

@section('title', 'Semak Status & Slip')
@section('page-title', 'Semak Status & Slip')
@section('page-subtitle', 'Semak status permohonan dan dokumen yang telah dihantar.')

@php
    $applicationLabels = [
        'anggota' => 'Permohonan Anggota',
        'saham' => 'Penambahan Saham',
        'berhenti' => 'Pengeluaran / Berhenti',
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
        <section class="coop-hero status-hero">
            <div>
                <span class="coop-kicker">STUDENT PORTAL</span>
                <h1>Semak Status & Slip</h1>
                <p>Rekod permohonan dan dokumen yang telah anda hantar.</p>
            </div>
            <a class="link-button secondary" href="{{ route('student.permohonan.index') }}">Permohonan Baharu</a>
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
                    <h2>Status Permohonan</h2>
                    <p>Semak keputusan dan catatan daripada admin.</p>
                </div>
            </div>
            <div class="status-table-wrap">
                <table class="status-table">
                    <thead>
                        <tr><th>Permohonan</th><th>Tarikh</th><th>Status</th><th>Catatan Admin</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $application)
                            <tr>
                                <td><strong>#{{ $application->id_permohonan }}</strong><span>{{ $applicationLabels[$application->jenis] ?? ucfirst($application->jenis) }}</span></td>
                                <td>{{ optional($application->tarikh_permohonan)->format('d/m/Y') ?? '-' }}</td>
                                <td><span class="status-pill status-pill--{{ $application->status }}">{{ str_replace('_', ' ', ucfirst($application->status)) }}</span></td>
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
    .status-page { display: grid; gap: 20px; }
    .status-hero { display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 26px 28px; border-left: 5px solid var(--primary); }
    .status-hero h1 { margin: 5px 0; font-size: 28px; }
    .status-hero p, .status-panel__head p { margin: 5px 0 0; color: var(--muted); }
    .status-summary { display: grid; grid-template-columns: 190px 190px minmax(0, 1fr); border: 1px solid var(--line); background: #fff; overflow: hidden; }
    .status-summary__item { min-height: 100px; padding: 19px 22px; border-right: 1px solid var(--line); }
    .status-summary__item span, .status-summary__note { display: block; color: var(--muted); font-size: 12px; font-weight: 700; }
    .status-summary__item strong { display: block; margin-top: 8px; color: var(--secondary); font-size: 26px; line-height: 1; }
    .status-summary__note { display: flex; align-items: center; padding: 19px 24px; line-height: 1.55; }
    .status-panel { overflow: hidden; }
    .status-panel__head { padding: 19px 22px; border-bottom: 1px solid var(--line); background: #fff; }
    .status-panel__eyebrow { display: block; margin-bottom: 5px; color: var(--secondary); font-size: 11px; font-weight: 800; }
    .status-panel__head h2 { margin: 0; font-size: 19px; }
    .status-table-wrap { overflow-x: auto; }
    .status-table { width: 100%; min-width: 760px; border-collapse: collapse; }
    .status-table th { padding: 13px 18px; background: var(--surface-soft); color: var(--muted); font-size: 12px; text-align: left; text-transform: uppercase; }
    .status-table td { padding: 16px 18px; border-bottom: 1px solid var(--line); vertical-align: middle; }
    .status-table tbody tr:hover { background: #f8fbff; }
    .status-table td strong, .status-table td span { display: block; }
    .status-table td span { margin-top: 4px; color: var(--muted); font-size: 12px; }
    .status-panel__footer { padding: 14px 20px; background: #fff; }
    .status-empty { padding: 28px !important; color: var(--muted); text-align: center; }
    .status-actions { display: flex; gap: 8px; }
    .status-actions a { min-height: 34px; display: inline-flex; align-items: center; padding: 0 11px; border: 1px solid #c9dcff; border-radius: 6px; background: var(--secondary-soft); color: var(--secondary); font-size: 13px; font-weight: 700; text-decoration: none; }
    .status-actions a:hover { background: var(--secondary); color: #fff; }
    .document-type { width: 42px; height: 34px; display: inline-grid; place-items: center; float: left; margin: 1px 10px 0 0; border: 1px solid #bfdbfe; border-radius: 6px; background: var(--secondary-soft); color: var(--secondary); font-size: 10px; font-weight: 800; }
    .document-name { min-height: 36px; display: grid; align-content: center; }
    .status-pill--baru { background: var(--secondary-soft); color: var(--secondary); }
    .status-pill--semak, .status-pill--pending, .status-pill--dalam_semakan { background: var(--warning-soft); color: var(--warning); }
    .status-pill--diluluskan { background: var(--success-soft); color: var(--success); }
    .status-pill--ditolak { background: var(--danger-soft); color: var(--danger); }
    @media (max-width: 760px) { .status-hero { align-items: flex-start; flex-direction: column; padding: 22px; } .status-summary { grid-template-columns: 1fr 1fr; } .status-summary__note { grid-column: 1 / -1; border-top: 1px solid var(--line); } .status-actions { flex-wrap: wrap; } }
    @media (max-width: 480px) { .status-summary { grid-template-columns: 1fr; } .status-summary__item { border-right: 0; border-bottom: 1px solid var(--line); } }
</style>
@endpush
