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
    $grandTotal = (float) $memberFee + $totalShare;
    $amountFields = ['yuran_anggota', 'modal_saham', 'tambahan_saham', 'jumlah_saham', 'jumlah_bayaran'];
    $initials = collect(explode(' ', $application->nama_pemohon))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'AG';
    $heirFields = ['nama_waris', 'telefon_waris', 'hubungan_waris', 'penama_nama', 'penama_nric', 'penama_hubungan', 'penama_no_tel', 'penama_alamat', 'penama_poskod', 'penama_peratus', 'penama2_nama', 'penama2_nric', 'penama2_hubungan', 'penama2_no_tel', 'penama2_alamat', 'penama2_poskod', 'penama2_peratus'];
    $memberInfo = $isStaffMember ? collect([
        'nama_penuh' => $application->nama_pemohon,
        'email' => $application->email,
        'no_tel' => $application->no_tel,
        'no_anggota' => $memberNumber,
        'no_kp' => optional($member)->nric ?? $data['no_kad_pengenalan'] ?? '-',
        'jenis_staff' => optional($member)->staff_type_label ?? $data['jenis_staff'] ?? '-',
        'status_anggota' => 'Diluluskan',
        'tarikh_daftar' => optional($application->tarikh_keputusan)->format('d/m/Y'),
        'yuran_anggota' => (float) $memberFee,
        'modal_saham' => $baseShare,
        'tambahan_saham' => $additionalShare,
        'jumlah_saham' => $totalShare,
        'jumlah_bayaran' => $grandTotal,
        'alamat' => $data['alamat'] ?? null,
        'agama' => $data['agama'] ?? null,
        'bangsa' => $data['bangsa'] ?? null,
        'jantina' => $data['jantina'] ?? null,
        'tarikh_lahir' => $data['tarikh_lahir'] ?? null,
        'taraf_perkahwinan' => $data['taraf_perkahwinan'] ?? null,
    ])->filter(fn ($value) => filled($value) || is_numeric($value)) : collect([
        'nama_penuh' => $application->nama_pemohon,
        'email' => $application->email,
        'no_tel' => $application->no_tel,
        'no_matrik' => $application->no_matrik,
        'no_anggota' => optional($member)->no_anggota,
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
        'jumlah_bayaran' => $grandTotal,
        'alamat' => $data['alamat'] ?? null,
        'agama' => $data['agama'] ?? null,
        'bangsa' => $data['bangsa'] ?? null,
        'jantina' => $data['jantina'] ?? null,
        'tarikh_lahir' => $data['tarikh_lahir'] ?? null,
        'taraf_perkahwinan' => $data['taraf_perkahwinan'] ?? null,
    ])->filter(fn ($value) => filled($value) || is_numeric($value));
    $heirData = collect($data)->only($heirFields)->filter(fn ($value) => filled($value));
@endphp

@section('page-actions')
    <a class="link-button secondary" href="{{ route($isStaffMember ? 'admin.anggota.staff' : 'admin.anggota.students') }}">Kembali</a>
@endsection

@section('content')
    <section class="panel panel-pad detail-hero">
        <div class="student-cell">
            <span class="student-avatar">{{ $initials }}</span>
            <div>
                <h2>{{ $application->nama_pemohon }}</h2>
                <p>{{ $isStaffMember ? $memberNumber : $application->no_matrik }} - {{ $application->email ?: 'Email tiada' }} - {{ $application->no_tel ?: 'Telefon tiada' }}</p>
            </div>
        </div>

        <div class="hero-meta">
            <span>Anggota Koperasi</span>
            <strong>{{ optional($application->tarikh_keputusan)->format('d/m/Y') ?? '-' }}</strong>
            <span class="status-pill status-pill--diluluskan">Diluluskan</span>
        </div>
    </section>

    <section class="panel panel-pad">
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
        .detail-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 24px;
        }
        .student-cell {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }
        .student-avatar {
            width: 48px;
            height: 48px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: var(--secondary-hover);
            color: #fff;
            font-size: 13px;
            font-weight: 900;
            flex: 0 0 auto;
        }
        .student-cell h2 {
            margin: 0;
            font-size: 20px;
        }
        .student-cell p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 13px;
        }
        .hero-meta {
            display: grid;
            gap: 7px;
            justify-items: end;
        }
        .hero-meta > span:first-child {
            color: var(--muted);
            font-size: 12px;
            font-weight: 900;
        }
        .section-title {
            margin-bottom: 18px;
        }
        .section-title h2 {
            margin: 0;
            font-size: 18px;
        }
        .section-title p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 13px;
        }
        .compact-sections {
            display: grid;
            gap: 12px;
        }
        .detail-group {
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }
        .detail-group summary {
            min-height: 46px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 0 14px;
            cursor: pointer;
            list-style: none;
            background: #f8fafc;
        }
        .detail-group summary::-webkit-details-marker {
            display: none;
        }
        .detail-group summary::after {
            content: '+';
            color: var(--primary);
            font-weight: 900;
        }
        .detail-group[open] summary::after {
            content: '-';
        }
        .detail-group h3 {
            margin: 0;
            color: #16345c;
            font-size: 15px;
        }
        .detail-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            column-gap: 22px;
            padding: 6px 14px;
        }
        .detail-row {
            display: grid;
            grid-template-columns: minmax(130px, .45fr) minmax(0, 1fr);
            gap: 12px;
            align-items: start;
            padding: 10px 0;
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
            font-weight: 800;
            text-transform: capitalize;
        }
        .detail-row strong {
            display: block;
            font-size: 14px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }
        @media (max-width: 1000px) {
            .detail-hero {
                align-items: flex-start;
                flex-direction: column;
            }
            .hero-meta {
                justify-items: start;
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
    </style>
@endpush
