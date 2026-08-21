@extends('layouts.app')

@section('title', 'Profil Staff')
@section('page-title', 'Profil Staff')
@section('page-subtitle', 'Maklumat akaun, jenis staff dan keanggotaan koperasi.')

@section('content')
    @include('components.coop-page-style')

    @php
        $shareRecord = $share;
        $staffTypeLabel = match($staffType) {
            'lecturer_member' => 'Pensyarah / Staf Akademik',
            'coop_staff' => 'Pekerja Koperasi',
            default => 'Staff Pengurusan Baju',
        };
        $memberStatus = $shareRecord ? 'Anggota Koperasi' : 'Belum Aktif';
        $totalShare = (float) optional($shareRecord)->syer + (float) optional($shareRecord)->tambahan_saham;
    @endphp

    <div class="coop-wrap profile-page">
        <section class="coop-hero profile-hero">
            <div>
                <span class="coop-kicker">Profil Staff</span>
                <h1>{{ $user->nama }}</h1>
                <p>{{ $user->no_pekerja }} · {{ $staffTypeLabel }} · {{ $memberStatus }}</p>
            </div>
        </section>

        <section class="panel profile-panel profile-panel--full">
            <div class="panel-section-head">
                <div>
                    <h2>Maklumat Profil</h2>
                    <p>Maklumat peribadi, akaun sistem dan keanggotaan koperasi staff dalam satu jadual.</p>
                </div>
            </div>
            <table class="detail-table detail-table--full">
                <tr><th>Nama Penuh</th><td>{{ $user->nama }}</td></tr>
                <tr><th>No. KP</th><td>{{ $user->nric ?? '-' }}</td></tr>
                <tr><th>Email</th><td>{{ $user->email ?? '-' }}</td></tr>
                <tr><th>No Telefon</th><td>{{ $user->no_tel ?? '-' }}</td></tr>
                <tr><th>No Pekerja</th><td>{{ $user->no_pekerja }}</td></tr>
                <tr><th>Role</th><td>Staff</td></tr>
                <tr><th>Jenis Staff</th><td>{{ $staffTypeLabel }}</td></tr>
                <tr><th>Status Akaun</th><td>{{ $user->status_aktif ? 'Aktif' : 'Tidak Aktif' }}</td></tr>
                <tr><th>Status Anggota</th><td>{{ $memberStatus }}</td></tr>
                <tr><th>Syer Asas</th><td>RM {{ number_format((float) optional($shareRecord)->syer, 2) }}</td></tr>
                <tr><th>Tambahan Saham</th><td>RM {{ number_format((float) optional($shareRecord)->tambahan_saham, 2) }}</td></tr>
                <tr><th>Jumlah Saham</th><td>RM {{ number_format($totalShare, 2) }}</td></tr>
            </table>
        </section>
    </div>
@endsection

@push('styles')
<style>
    .profile-page { gap: 20px; }
    .profile-hero { min-height: 136px; }

    .profile-panel {
        border-radius: 10px !important;
        overflow: hidden;
    }

    .profile-panel--full .detail-table--full {
        margin: 22px 24px 24px;
        width: calc(100% - 48px);
    }

    @media (max-width: 1080px) {
    }

    @media (max-width: 720px) {
        .profile-hero {
            align-items: flex-start;
            flex-direction: column;
        }

    }
</style>
@endpush
