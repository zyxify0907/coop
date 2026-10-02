@extends('layouts.app')

@section('title', 'Profil Anggota')
@section('page-title', 'Profil Anggota')
@section('page-subtitle', 'Maklumat penuh anggota koperasi yang telah diluluskan.')

@php
    $application = $memberApplication;
    $data = $application->data_permohonan ?? [];
    $isStaffMember = ($data['pemohon_role'] ?? null) === 'staff';
    $member = $isStaffMember ? $staffMember : $application->ahli;
    $shareRecord = $isStaffMember ? optional($staffMember)->sahamStaff : optional($member)->saham;
    $memberNumber = $isStaffMember ? (optional($member)->no_anggota ?? $data['no_anggota'] ?? '-') : optional($member)->no_anggota;
    $memberFee = $isStaffMember ? ($data['yuran_anggota'] ?? optional($shareRecord)->yuran ?? 10) : (optional($shareRecord)->yuran ?? $data['yuran_anggota'] ?? 0);
    $baseShare = (float) (optional($shareRecord)->syer ?? $data['modal_saham'] ?? 0);
    $additionalShare = (float) (optional($shareRecord)->tambahan_saham ?? 0);
    $totalShare = $baseShare + $additionalShare;
    $amountFields = ['yuran_anggota', 'modal_saham', 'tambahan_saham', 'jumlah_saham'];
    $initials = collect(explode(' ', $application->nama_pemohon))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'AG';
    $heirFields = ['nama_waris', 'telefon_waris', 'hubungan_waris', 'penama_nama', 'penama_nric', 'penama_hubungan', 'penama_no_tel', 'penama_alamat', 'penama_poskod', 'penama_peratus', 'penama2_nama', 'penama2_nric', 'penama2_hubungan', 'penama2_no_tel', 'penama2_alamat', 'penama2_poskod', 'penama2_peratus'];
    $memberInfo = $isStaffMember ? collect([
        'nama_penuh' => $application->nama_pemohon,
        'no_tel' => $application->no_tel,
        'no_anggota_staff' => $memberNumber,
        'no_kp' => optional($member)->nric ?? $data['no_kad_pengenalan'] ?? '-',
        'jenis_staff' => optional($member)->staff_type_label ?? $data['jenis_staff'] ?? '-',
        'status_anggota' => 'Diluluskan',
        'tarikh_daftar' => optional($application->tarikh_keputusan)->format('d/m/Y'),
        'yuran_anggota' => (float) $memberFee,
        'modal_saham' => $baseShare,
        'tambahan_saham' => $additionalShare,
        'jumlah_saham' => $totalShare,
        'alamat' => $data['alamat'] ?? null,
        'agama' => $data['agama'] ?? null,
        'bangsa' => $data['bangsa'] ?? null,
        'jantina' => $data['jantina'] ?? null,
        'tarikh_lahir' => $data['tarikh_lahir'] ?? null,
        'taraf_perkahwinan' => $data['taraf_perkahwinan'] ?? null,
    ])->filter(fn ($value) => filled($value) || is_numeric($value)) : collect([
        'nama_penuh' => $application->nama_pemohon,
        'no_tel' => $application->no_tel,
        'no_matrik' => $application->no_matrik,
        'no_anggota_pelajar' => optional($member)->no_anggota,
        'no_kp' => optional($member)->nric ?? $data['no_kad_pengenalan'] ?? '-',
        'semester' => optional($member)->semester,
        'program' => $data['program_pengajian'] ?? optional($member)->program,
        'kelas' => $data['kelas'] ?? optional($member)->kelas,
        'tarikh_daftar' => optional(optional($member)->tarikh_daftar)->format('d/m/Y') ?? optional($application->tarikh_keputusan)->format('d/m/Y'),
        'status_anggota' => 'Diluluskan',
        'yuran_anggota' => (float) $memberFee,
        'modal_saham' => $baseShare,
        'tambahan_saham' => $additionalShare,
        'jumlah_saham' => $totalShare,
        'alamat' => $data['alamat'] ?? null,
        'agama' => $data['agama'] ?? null,
        'bangsa' => $data['bangsa'] ?? null,
        'jantina' => $data['jantina'] ?? null,
        'tarikh_lahir' => $data['tarikh_lahir'] ?? null,
        'taraf_perkahwinan' => $data['taraf_perkahwinan'] ?? null,
    ])->filter(fn ($value) => filled($value) || is_numeric($value));
    $heirData = collect($data)->only($heirFields)->filter(fn ($value) => filled($value));
@endphp

@section('content')
    <section class="detail-hero">
        <div class="student-cell">
            <span class="student-avatar">{{ $initials }}</span>
            <div>
                <h2>{{ $application->nama_pemohon }}</h2>
                <p>
                    <span>{{ $isStaffMember ? $memberNumber : $application->no_matrik }}</span>
                    <span>{{ $application->no_tel ?: 'Telefon tiada' }}</span>
                </p>
            </div>
        </div>

        <div class="hero-meta">
            <span class="hero-meta__label">Anggota Koperasi</span>
            <strong>{{ optional($application->tarikh_keputusan)->format('d/m/Y') ?? '-' }}</strong>
            <span class="status-pill status-pill--diluluskan">Diluluskan</span>
        </div>
    </section>

    <div class="detail-metrics">
        <section>
            <span>{{ $isStaffMember ? 'No Anggota Staff' : 'No Anggota Pelajar' }}</span>
            <strong>{{ $memberNumber ?: '-' }}</strong>
        </section>
        <section>
            <span>Yuran Anggota</span>
            <strong>{{ 'RM '.number_format((float) $memberFee, 2) }}</strong>
        </section>
        <section>
            <span>Jumlah Tambahan</span>
            <strong>{{ 'RM '.number_format($additionalShare, 2) }}</strong>
        </section>
        <section>
            <span>Jumlah Saham</span>
            <strong>{{ 'RM '.number_format($totalShare, 2) }}</strong>
        </section>
    </div>

    <section class="profile-shell">
        <div class="section-title">
            <h2>Maklumat Profil Anggota</h2>
            <p>Semua maklumat {{ $isStaffMember ? 'staff' : 'pelajar' }}, maklumat anggota dan ringkasan saham.</p>
        </div>

        <div class="compact-sections">
            @include('admin.permohonan.partials.detail-list', ['title' => 'Maklumat Anggota', 'items' => $memberInfo, 'amountFields' => $amountFields, 'open' => true])

            @if ($heirData->isNotEmpty())
                @include('admin.permohonan.partials.detail-list', ['title' => 'Maklumat Penama / Wasi', 'items' => $heirData, 'amountFields' => $amountFields, 'open' => false])
            @endif
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .page-heading{display:none}
        .detail-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 24px;
            padding: 28px 30px;
            border: 1px solid var(--line);
            border-left: 5px solid var(--secondary);
            border-radius: 8px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 14px 34px rgba(15, 23, 42, .055);
        }
        .student-cell {
            display: flex;
            align-items: center;
            gap: 18px;
            min-width: 0;
        }
        .student-avatar {
            width: 64px;
            height: 64px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: var(--secondary);
            color: #fff;
            font-size: 16px;
            font-weight: 900;
            flex: 0 0 auto;
            box-shadow: 0 12px 22px rgba(30, 64, 175, .18);
        }
        .student-cell h2 {
            margin: 0;
            color: var(--text);
            font-size: 26px;
            line-height: 1.18;
            font-weight: 900;
            letter-spacing: 0;
        }
        .student-cell p {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin: 10px 0 0;
            color: var(--muted-2);
            font-size: 14px;
            font-weight: 800;
        }
        .student-cell p span {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 0 10px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
        }
        .hero-meta {
            display: grid;
            gap: 8px;
            justify-items: end;
            flex: 0 0 auto;
            text-align: right;
        }
        .hero-meta__label {
            color: var(--muted-2);
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .hero-meta strong {
            color: var(--text);
            font-size: 22px;
            line-height: 1;
            font-weight: 900;
        }
        .detail-metrics {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }
        .detail-metrics section {
            display: grid;
            gap: 8px;
            min-height: 104px;
            align-content: center;
            padding: 18px 20px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .045);
        }
        .detail-metrics span {
            color: var(--muted-2);
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .detail-metrics strong {
            color: var(--text);
            font-size: 24px;
            line-height: 1.1;
            font-weight: 900;
            overflow-wrap: anywhere;
        }
        .profile-shell {
            padding: 26px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .055);
        }
        .section-title {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 20px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--line);
        }
        .section-title h2 {
            margin: 0;
            color: var(--text);
            font-size: 24px;
            line-height: 1.2;
            font-weight: 900;
        }
        .section-title p {
            margin: 6px 0 0;
            color: var(--muted-2);
            font-size: 14px;
            font-weight: 700;
        }
        .compact-sections {
            display: grid;
            gap: 14px;
        }
        .page-delete-form {
            margin: 0;
        }
        .link-button.danger {
            border-color: var(--danger-soft);
            background: var(--danger-soft);
            color: var(--danger);
        }
        .link-button.danger:hover {
            background: var(--danger);
            color: #fff;
        }
        .detail-group {
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }
        .detail-group summary {
            min-height: 58px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 0 18px;
            cursor: pointer;
            list-style: none;
            background: var(--surface-soft);
        }
        .detail-group summary::-webkit-details-marker {
            display: none;
        }
        .detail-group summary::after {
            content: '+';
            display: grid;
            width: 28px;
            height: 28px;
            place-items: center;
            border-radius: 8px;
            background: #fff;
            color: var(--primary);
            font-weight: 900;
            border: 1px solid var(--line);
        }
        .detail-group[open] summary::after {
            content: '-';
        }
        .detail-group h3 {
            margin: 0;
            color: #16345c;
            font-size: 17px;
            font-weight: 900;
        }
        .detail-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0 24px;
            padding: 10px 18px 16px;
        }
        .detail-row {
            display: grid;
            grid-template-columns: minmax(150px, .42fr) minmax(0, 1fr);
            gap: 12px;
            align-items: start;
            min-height: 56px;
            padding: 14px 0;
            border-bottom: 1px solid var(--line);
        }
        .detail-row:last-child,
        .detail-row:nth-last-child(2):nth-child(odd) {
            border-bottom: 0;
        }
        .detail-row span {
            display: block;
            color: var(--muted);
            font-size: 12px;
            font-weight: 900;
            text-transform: capitalize;
        }
        .detail-row strong {
            display: block;
            color: var(--text);
            font-size: 15px;
            line-height: 1.45;
            font-weight: 900;
            overflow-wrap: anywhere;
        }
        .status-pill {
            border-radius: 8px;
            min-height: 30px;
        }
        @media (max-width: 1000px) {
            .detail-hero {
                align-items: flex-start;
                flex-direction: column;
                padding: 24px;
            }
            .hero-meta {
                justify-items: start;
                text-align: left;
            }
            .detail-metrics {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .section-title {
                align-items: flex-start;
                flex-direction: column;
            }
            .detail-list,
            .detail-row {
                grid-template-columns: 1fr;
            }
            .detail-list {
                padding: 6px 14px 10px;
            }
            .detail-row {
                gap: 6px;
            }
        }
        @media (max-width: 640px) {
            .student-cell {
                align-items: flex-start;
                flex-direction: column;
            }
            .student-cell h2 {
                font-size: 22px;
            }
            .student-cell p span {
                width: 100%;
            }
            .detail-metrics {
                grid-template-columns: 1fr;
            }
            .profile-shell {
                padding: 20px;
            }
        }
    </style>
@endpush
