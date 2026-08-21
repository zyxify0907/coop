@extends('layouts.app')

@section('title', 'Profil Student')
@section('page-title', 'Profil Student')
@section('page-subtitle', 'Maklumat akaun, akademik dan keanggotaan koperasi.')

@section('content')
    @include('components.coop-page-style')

    @php
        $share = $user->saham;
        $memberStatus = $user->no_anggota ? 'Anggota Koperasi' : 'Belum Aktif';
        $totalShare = (float) optional($share)->syer + (float) optional($share)->tambahan_saham;
        $memberNumber = $user->no_anggota ?? 'Belum dijana';
    @endphp

    <div class="coop-wrap profile-page">
        <section class="coop-hero profile-hero">
            <div>
                <span class="coop-kicker">Profil Pelajar</span>
                <h1>{{ $user->nama }}</h1>
                <p>{{ $user->no_matrik }} · {{ $user->program ?? 'Program belum ditetapkan' }} · {{ $user->kelas ?? 'Kelas belum ditetapkan' }} · {{ $memberStatus }}</p>
            </div>
        </section>

        <section class="panel profile-panel profile-panel--full">
            <div class="panel-section-head">
                <div>
                    <h2>Maklumat Profil</h2>
                    <p>Maklumat peribadi, akademik, akaun sistem dan keanggotaan koperasi pelajar dalam satu jadual.</p>
                </div>
            </div>
            <table class="detail-table detail-table--full">
                <tr><th>Nama Penuh</th><td>{{ $user->nama }}</td></tr>
                <tr><th>No. KP</th><td>{{ $user->nric ?? '-' }}</td></tr>
                <tr><th>Email</th><td>{{ $user->email ?? '-' }}</td></tr>
                <tr><th>No Telefon</th><td>{{ $user->no_tel ?? '-' }}</td></tr>
                <tr><th>No Matrik</th><td>{{ $user->no_matrik }}</td></tr>
                <tr><th>Program</th><td>{{ $user->program ?? '-' }}</td></tr>
                <tr><th>Kelas</th><td>{{ $user->kelas ?? '-' }}</td></tr>
                <tr><th>Semester</th><td>{{ $user->semester ?? '-' }}</td></tr>
                <tr><th>Role</th><td>Student / Ahli</td></tr>
                <tr><th>Status Akaun</th><td>{{ $user->status_aktif ? 'Aktif' : 'Tidak Aktif' }}</td></tr>
                <tr><th>Status Anggota</th><td>{{ $memberStatus }}</td></tr>
                <tr><th>No Anggota</th><td>{{ $memberNumber }}</td></tr>
                <tr><th>Baki E-Wallet</th><td>RM {{ number_format((float) $user->baki_ewallet, 2) }}</td></tr>
                <tr><th>Syer Asas</th><td>RM {{ number_format((float) optional($share)->syer, 2) }}</td></tr>
                <tr><th>Tambahan Saham</th><td>RM {{ number_format((float) optional($share)->tambahan_saham, 2) }}</td></tr>
                <tr><th>Jumlah Saham</th><td>RM {{ number_format($totalShare, 2) }}</td></tr>
                <tr><th>Yuran Anggota</th><td>RM {{ number_format((float) optional($share)->yuran, 2) }}</td></tr>
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

    @media (max-width: 720px) {
        .profile-hero {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endpush
