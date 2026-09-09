@extends('layouts.app')

@section('title', 'Profil Student')
@section('page-title', 'Profil Student')
@section('page-subtitle', 'Maklumat akaun, keanggotaan, saham dan aktiviti terkini.')

@section('content')
    @include('components.coop-page-style')

    @php
        $share = $user->saham;
        $memberStatus = $user->no_anggota ? 'Anggota Koperasi' : 'Belum Aktif';
        $totalShare = (float) optional($share)->syer + (float) optional($share)->tambahan_saham;
        $memberNumber = $user->no_anggota ?? 'Belum dijana';
        $profileInitial = strtoupper(mb_substr($user->nama ?? 'S', 0, 1));
    @endphp

    <div class="coop-wrap profile-page profile-page--enhanced">
        <section class="coop-hero profile-hero">
            <div class="profile-identity">
                <span class="profile-avatar">{{ $profileInitial }}</span>
                <div>
                    <span class="coop-kicker">Profil Pelajar</span>
                    <h1>{{ $user->nama }}</h1>
                    <p>{{ $user->no_matrik }} &middot; {{ $user->program ?? 'Program belum ditetapkan' }} &middot; {{ $user->kelas ?? 'Kelas belum ditetapkan' }}</p>
                </div>
            </div>
            <span class="profile-status">{{ $memberStatus }}</span>
        </section>

        <section class="profile-summary-grid" aria-label="Ringkasan profil">
            <article class="profile-summary">
                <span>Status Akaun</span>
                <strong>{{ $user->status_aktif ? 'Aktif' : 'Tidak Aktif' }}</strong>
            </article>
            <article class="profile-summary">
                <span>No Anggota Pelajar</span>
                <strong>{{ $memberNumber }}</strong>
            </article>
            <article class="profile-summary">
                <span>Jumlah Saham</span>
                <strong>RM {{ number_format($totalShare, 2) }}</strong>
            </article>
            <article class="profile-summary">
                <span>Tarikh Daftar</span>
                <strong>{{ optional($user->tarikh_daftar)->format('d/m/Y') ?? '-' }}</strong>
            </article>
        </section>

        <section class="profile-layout">
            <div class="profile-main">
                <section class="panel profile-panel">
                    <div class="panel-section-head">
                        <div>
                            <h2>Kemaskini Maklumat</h2>
                            <p>Kemaskini maklumat hubungan yang digunakan untuk urusan koperasi.</p>
                        </div>
                    </div>
                    <form class="profile-form" method="POST" action="{{ route('student.profile.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="profile-form-grid">
                            <label>
                                <span>Nama Penuh</span>
                                <input value="{{ $user->nama }}" disabled>
                            </label>
                            <label>
                                <span>No. KP</span>
                                <input value="{{ $user->nric ?? '-' }}" disabled>
                            </label>
                            <label>
                                <span>No Telefon</span>
                                <input name="no_tel" value="{{ old('no_tel', $user->no_tel) }}" placeholder="Contoh: 0123456789">
                                @error('no_tel')<small>{{ $message }}</small>@enderror
                            </label>
                            <label>
                                <span>Email</span>
                                <input name="email" type="email" value="{{ old('email', $user->email) }}" placeholder="nama@email.com">
                                @error('email')<small>{{ $message }}</small>@enderror
                            </label>
                            <label>
                                <span>No Matrik</span>
                                <input value="{{ $user->no_matrik }}" disabled>
                            </label>
                            <label>
                                <span>Kelas</span>
                                <input value="{{ $user->kelas ?? '-' }}" disabled>
                            </label>
                        </div>

                        <div class="profile-actions">
                            <button class="button primary" type="submit">Simpan Profil</button>
                        </div>
                    </form>
                </section>

                <section class="panel profile-panel" id="kata-laluan">
                    <div class="panel-section-head">
                        <div>
                            <h2>Tukar Kata Laluan</h2>
                            <p>Kemaskini kata laluan akaun untuk keselamatan login.</p>
                        </div>
                    </div>
                    <form class="profile-form" method="POST" action="{{ route('student.profile.password') }}">
                        @csrf
                        @method('PATCH')

                        <div class="profile-form-grid">
                            <label>
                                <span>Kata Laluan Semasa</span>
                                <input name="current_password" type="password" autocomplete="current-password">
                                @error('current_password')<small>{{ $message }}</small>@enderror
                            </label>
                            <label>
                                <span>Kata Laluan Baharu</span>
                                <input name="password" type="password" autocomplete="new-password">
                                @error('password')<small>{{ $message }}</small>@enderror
                            </label>
                            <label>
                                <span>Sahkan Kata Laluan Baharu</span>
                                <input name="password_confirmation" type="password" autocomplete="new-password">
                            </label>
                        </div>

                        <div class="profile-actions">
                            <button class="button primary" type="submit">Tukar Kata Laluan</button>
                        </div>
                    </form>
                </section>
            </div>

            <aside class="profile-side">
                <section class="panel profile-panel">
                    <div class="panel-section-head">
                        <div>
                            <h2>Aktiviti Terkini</h2>
                            <p>Ringkasan permohonan, transaksi dan tempahan.</p>
                        </div>
                    </div>
                    <div class="profile-activity">
                        @forelse ($latestApplications as $application)
                            <div class="profile-activity__item">
                                <strong>Permohonan {{ ucwords(str_replace('_', ' ', $application->jenis)) }}</strong>
                                <span>{{ ucfirst(str_replace('_', ' ', $application->status)) }} &middot; {{ optional($application->tarikh_permohonan)->format('d/m/Y') ?? '-' }}</span>
                            </div>
                        @empty
                            <div class="profile-empty">Tiada permohonan terkini.</div>
                        @endforelse

                        @foreach ($recentTransactions as $transaction)
                            <div class="profile-activity__item">
                                <strong>Transaksi saham RM {{ number_format((float) $transaction->amount, 2) }}</strong>
                                <span>{{ ucfirst($transaction->direction ?? '-') }} &middot; {{ optional($transaction->transacted_at)->format('d/m/Y') ?? '-' }}</span>
                            </div>
                        @endforeach

                        @foreach ($recentOrders as $order)
                            <div class="profile-activity__item">
                                <strong>Tempahan baju</strong>
                                <span>{{ ucfirst(str_replace('_', ' ', (string) ($order->status ?? 'baru'))) }} &middot; {{ isset($order->tarikh_tempahan) ? \Carbon\Carbon::parse($order->tarikh_tempahan)->format('d/m/Y') : '-' }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            </aside>
        </section>
    </div>
@endsection

@push('styles')
<style>
    .profile-page--enhanced { gap: 20px; }
    .profile-hero {
        min-height: 150px;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
    }
    .profile-identity {
        display: flex;
        align-items: center;
        gap: 18px;
        min-width: 0;
    }
    .profile-avatar {
        width: 62px;
        height: 62px;
        flex: 0 0 auto;
        display: grid;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .14);
        color: #fff;
        font-size: 24px;
        font-weight: 900;
    }
    .profile-status {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 0 14px;
        border-radius: 999px;
        background: #DCFCE7;
        color: #166534;
        font-size: 13px;
        font-weight: 900;
        white-space: nowrap;
    }
    .profile-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }
    .profile-summary {
        padding: 18px;
        border: 1px solid var(--line);
        border-radius: 10px;
        background: #fff;
    }
    .profile-summary span,
    .profile-form label span {
        display: block;
        color: var(--muted);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .02em;
        text-transform: uppercase;
    }
    .profile-summary strong {
        display: block;
        margin-top: 8px;
        color: var(--text);
        font-size: 20px;
        overflow-wrap: anywhere;
    }
    .profile-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(320px, .75fr);
        gap: 20px;
        align-items: start;
    }
    .profile-main,
    .profile-side {
        display: grid;
        gap: 20px;
    }
    .profile-panel {
        border-radius: 10px !important;
        overflow: hidden;
    }
    .profile-form {
        padding: 22px 24px 24px;
    }
    .profile-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    .profile-form label { display: grid; gap: 7px; }
    .profile-form input {
        width: 100%;
        min-height: 44px;
        border: 1px solid var(--line);
        border-radius: 8px;
        padding: 0 13px;
        color: var(--text);
        background: #fff;
        font-weight: 700;
    }
    .profile-form input:disabled {
        background: var(--surface-soft);
        color: var(--muted-2);
    }
    .profile-form input:focus {
        outline: 3px solid rgba(37, 99, 235, .14);
        border-color: var(--secondary);
    }
    .profile-form small {
        color: var(--danger);
        font-weight: 800;
    }
    .profile-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }
    .profile-detail-table {
        margin: 18px;
        width: calc(100% - 36px);
    }
    .profile-activity {
        display: grid;
        gap: 0;
        padding: 12px 18px 18px;
    }
    .profile-activity__item,
    .profile-empty {
        padding: 13px 0;
        border-bottom: 1px solid var(--line);
    }
    .profile-activity__item:last-child,
    .profile-empty:last-child { border-bottom: 0; }
    .profile-activity__item strong {
        display: block;
        color: var(--text);
        font-size: 14px;
    }
    .profile-activity__item span,
    .profile-empty {
        display: block;
        margin-top: 3px;
        color: var(--muted-2);
        font-size: 13px;
        font-weight: 700;
    }

    @media (max-width: 1080px) {
        .profile-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .profile-layout { grid-template-columns: 1fr; }
    }

    @media (max-width: 720px) {
        .profile-hero {
            align-items: flex-start;
            flex-direction: column;
        }
        .profile-identity { align-items: flex-start; }
        .profile-summary-grid,
        .profile-form-grid { grid-template-columns: 1fr; }
        .profile-form { padding: 18px; }
        .profile-actions .button { width: 100%; }
    }
</style>
@endpush
