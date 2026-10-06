@extends('layouts.app')

@section('title', 'Profil Staf')
@section('page-title', 'Profil Staf')
@section('page-subtitle', 'Maklumat akaun, jenis staf, saham dan aktiviti terkini.')

@section('content')
    @include('components.coop-page-style')

    @php
        $shareRecord = $share;
        $staffTypeLabel = match($staffType) {
            'lecturer_member' => 'Pensyarah / Staf Akademik',
            'coop_staff' => 'Pekerja Koperasi',
            'clothing_staff' => 'Staf Pengurus Baju',
            'share_staff' => 'Staf Pengurus Saham',
            'coop_manager' => 'Staf Pengurus Pekerja Koperasi',
            default => 'Staf',
        };
        $isShareholderStaff = $user->isEligibleForShares();
        $memberNumber = $staffMemberNumber ?? $user->no_anggota;
        $memberStatus = $isShareholderStaff
            ? ($shareRecord || $memberNumber ? 'Anggota Koperasi' : 'Belum menjadi anggota')
            : 'Pekerja Koperasi';
        $totalShare = (float) optional($shareRecord)->syer + (float) optional($shareRecord)->tambahan_saham;
        $memberApplication = $latestApplications->firstWhere('jenis', 'anggota');
        $registeredDate = optional($memberApplication?->tarikh_keputusan)->format('d/m/Y') ?? optional($user->tarikh_mula)->format('d/m/Y') ?? '-';
        $profileInitial = strtoupper(mb_substr($user->nama ?? 'S', 0, 1));
    @endphp

    <div class="staff-profile-page">
        <section class="profile-hero-card" aria-label="Profil Staf">
            <div class="profile-hero-main">
                <span class="profile-watermark">KOPERASI</span>
                <div class="profile-identity">
                    <span class="profile-avatar" aria-label="Gambar profil {{ $user->nama }}">
                        @if ($user->profile_image_path)
                            <img src="{{ asset($user->profile_image_path) }}" alt="Gambar profil {{ $user->nama }}">
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="7" r="4"/>
                                <path d="M5 21v-2a7 7 0 0 1 14 0v2"/>
                            </svg>
                        @endif
                    </span>
                    <div>
                        <span class="profile-kicker">Profil Staf</span>
                        <h1>{{ $user->nama }}</h1>
                        <p>{{ $isShareholderStaff ? ($memberNumber ?? 'Belum menjadi anggota') : $user->no_pekerja }} &middot; {{ $staffTypeLabel }} &middot; {{ $memberStatus }}</p>
                        <span class="profile-status profile-status--inline">{{ $user->status_aktif ? 'Aktif' : 'Tidak Aktif' }}</span>
                    </div>
                </div>
            </div>
            <div class="profile-hero-side">
                <span>STATUS PENGGUNA</span>
                <strong>{{ $staffTypeLabel }}</strong>
                <small>{{ $isShareholderStaff ? 'No. Anggota: '.($memberNumber ?? 'Belum menjadi anggota') : 'No. Pekerja: '.$user->no_pekerja }}</small>
            </div>
        </section>

        <section class="profile-summary-grid" aria-label="Ringkasan profil">
            <article class="profile-summary profile-summary--green">
                <span>Jenis Staff</span>
                <strong>{{ $staffTypeLabel }}</strong>
                <small>Kategori akaun staff semasa.</small>
            </article>
            @if ($isShareholderStaff)
                <article class="profile-summary profile-summary--blue">
                    <span>No Anggota Staff</span>
                    <strong>{{ $memberNumber ?? 'Belum menjadi anggota' }}</strong>
                    <small>Nombor keahlian anda.</small>
                </article>
                <article class="profile-summary profile-summary--amber">
                    <span>Jumlah Saham</span>
                    <strong>RM {{ number_format($totalShare, 2) }}</strong>
                    <small>Jumlah saham terkini anda.</small>
                </article>
                <article class="profile-summary profile-summary--navy">
                    <span>Tarikh Daftar</span>
                    <strong>{{ $registeredDate }}</strong>
                    <small>Tarikh anda berdaftar.</small>
                </article>
            @else
                <article class="profile-summary profile-summary--blue">
                    <span>No Pekerja</span>
                    <strong>{{ $user->no_pekerja ?? '-' }}</strong>
                    <small>Nombor pekerja anda.</small>
                </article>
                <article class="profile-summary profile-summary--amber">
                    <span>Status Pekerja</span>
                    <strong>{{ $user->status_aktif ? 'Aktif' : 'Tidak Aktif' }}</strong>
                    <small>Status akaun pekerja.</small>
                </article>
                <article class="profile-summary profile-summary--navy">
                    <span>Tarikh Mula</span>
                    <strong>{{ optional($user->tarikh_mula)->format('d/m/Y') ?? '-' }}</strong>
                    <small>Tarikh mula bekerja.</small>
                </article>
            @endif
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
                    <form id="staff-profile-form" class="profile-form" method="POST" action="{{ route($portalPrefix.'.profile.update') }}" enctype="multipart/form-data">
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
                            <label class="profile-photo-upload">
                                <span>Gambar Profil</span>
                                <input name="profile_image" type="file" accept="image/jpeg,image/png,image/webp">
                                <small>JPG, PNG atau WebP. Maksimum 4 MB.</small>
                                @error('profile_image')<small>{{ $message }}</small>@enderror
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
                            @if (! $isShareholderStaff)
                                <label>
                                    <span>No Pekerja</span>
                                    <input value="{{ $user->no_pekerja }}" disabled>
                                </label>
                            @endif
                            <label>
                                <span>Role</span>
                                <input value="{{ $staffTypeLabel }}" disabled>
                            </label>
                        </div>

                    </form>
                    <div class="profile-image-actions">
                        <button class="button primary" type="submit" form="staff-profile-form">Simpan Profil</button>
                        <x-remove-profile-image :image-path="$user->profile_image_path" :name="$user->nama" :profile="true" />
                    </div>
                </section>

                @if ($isShareholderStaff)
                    <section class="panel profile-panel">
                        <div class="panel-section-head">
                            <div>
                                <h2>Ringkasan Saham</h2>
                                <p>Maklumat syer semasa staff.</p>
                            </div>
                        </div>
                        <div class="profile-share-summary">
                            <table class="profile-detail-table">
                                <tr>
                                    <th>Syer Asas</th>
                                    <td>RM {{ number_format((float) optional($shareRecord)->syer, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Tambahan Saham</th>
                                    <td>RM {{ number_format((float) optional($shareRecord)->tambahan_saham, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Yuran Anggota</th>
                                    <td>RM {{ number_format((float) optional($shareRecord)->yuran, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Status Anggota</th>
                                    <td>{{ $memberStatus }}</td>
                                </tr>
                            </table>
                        </div>
                    </section>
                @endif

                <section class="panel profile-panel" id="kata-laluan">
                    <div class="panel-section-head">
                        <div>
                            <h2>Tukar Kata Laluan</h2>
                            <p>Kemaskini kata laluan akaun untuk keselamatan login.</p>
                        </div>
                    </div>
                    <form class="profile-form" method="POST" action="{{ route($portalPrefix.'.profile.password') }}">
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
                            <p>Ringkasan permohonan, transaksi dan kehadiran.</p>
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

                        @foreach ($attendanceRecords as $record)
                            <div class="profile-activity__item">
                                <strong>Kehadiran {{ optional($record->attendance_date)->format('d/m/Y') ?? '-' }}</strong>
                                <span>{{ $record->check_out_time ? 'Check out selesai' : 'Check in direkodkan' }}</span>
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
        width: 48px;
        height: 48px;
        flex: 0 0 auto;
        display: grid;
        place-items: center;
        border-radius: 999px;
        overflow: hidden;
        background: rgba(255, 255, 255, .14);
        color: #fff;
        font-size: 24px;
        font-weight: 900;
    }
    .profile-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
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
        border: 1px solid var(--line);
        border-radius: 8px;
        overflow: hidden;
        border-collapse: separate;
        border-spacing: 0;
    }
    .profile-detail-table th,
    .profile-detail-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--line);
        text-align: left;
    }
    .profile-detail-table tr:last-child th,
    .profile-detail-table tr:last-child td {
        border-bottom: 0;
    }
    .profile-detail-table th {
        width: 38%;
        background: var(--surface-soft);
        color: var(--muted);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .02em;
        text-transform: uppercase;
    }
    .profile-detail-table td {
        color: var(--text);
        font-weight: 800;
    }
    .profile-share-summary {
        padding: 0 6px 6px;
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

    .content:has(.staff-profile-page) {
        background: #F4F7FB;
    }

    .staff-profile-page {
        --profile-navy: #082F59;
        --profile-red: #ED1C2E;
        --profile-blue: #0B5ED7;
        --profile-green: #087A45;
        --profile-border: #D7E2EF;
        --profile-muted: #526987;
        --profile-card: #FFFFFF;
        --profile-amber: #F59E0B;
        width: min(100% - clamp(24px, 4vw, 64px), 1500px);
        margin: 0 auto;
        display: grid;
        gap: 18px;
        color: var(--profile-navy);
    }

    .staff-profile-page .profile-hero-card {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(320px, .9fr);
        gap: 0;
        min-height: 178px;
        padding: 0;
        border: 1px solid var(--profile-border);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 10px 28px rgba(8, 47, 89, .08);
        overflow: hidden;
    }

    .staff-profile-page .profile-hero-main {
        position: relative;
        display: flex;
        align-items: center;
        min-width: 0;
        padding: 34px 42px;
        border-radius: 12px 0 0 12px;
        background: var(--profile-navy);
        clip-path: polygon(0 0, calc(100% - 64px) 0, 100% 100%, 0 100%);
        overflow: hidden;
        z-index: 2;
    }

    .staff-profile-page .profile-hero-main::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 18% 20%, rgba(255,255,255,.08), transparent 30%),
            linear-gradient(135deg, rgba(255,255,255,.08), transparent 42%);
        pointer-events: none;
    }

    .staff-profile-page .profile-hero-main::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 1;
        background: var(--profile-red);
        clip-path: polygon(calc(100% - 74px) 0, calc(100% - 64px) 0, 100% 100%, calc(100% - 10px) 100%);
        pointer-events: none;
    }

    .staff-profile-page .profile-watermark {
        position: absolute;
        left: 26px;
        bottom: -4px;
        color: rgba(255, 255, 255, .055);
        font-size: 34px;
        font-weight: 900;
        letter-spacing: .02em;
        pointer-events: none;
    }

    .staff-profile-page .profile-hero-main .profile-identity {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 26px;
        min-width: 0;
    }

    .staff-profile-page .profile-avatar {
        width: 48px;
        height: 48px;
        flex: 0 0 auto;
        display: grid;
        place-items: center;
        border: 3px solid rgba(255,255,255,.34);
        border-radius: 999px;
        background: #DCEBFF;
        color: var(--profile-navy);
        box-shadow: 0 10px 24px rgba(0,0,0,.16);
    }

    .staff-profile-page .profile-avatar svg {
        width: 54px;
        height: 54px;
    }

    .staff-profile-page .profile-kicker {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        margin-bottom: 10px;
        padding: 0 12px;
        border-radius: 6px;
        background: rgba(234, 242, 255, .18);
        color: #DCEBFF;
        font-size: 13px;
        font-weight: 900;
    }

    .staff-profile-page .profile-identity h1 {
        margin: 0;
        color: #fff;
        font-size: clamp(27px, 2.35vw, 38px);
        line-height: 1.05;
        font-weight: 900;
        letter-spacing: 0;
    }

    .staff-profile-page .profile-identity p {
        margin: 10px 0 0;
        color: rgba(255,255,255,.82);
        font-size: clamp(15px, 1.22vw, 18px);
        font-weight: 800;
    }

    .staff-profile-page .profile-status--inline {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        width: auto;
        min-height: 28px;
        margin-top: 12px;
        padding: 0 12px;
        border-radius: 999px;
        background: #18B877;
        color: #fff;
        font-size: 13px;
        font-weight: 900;
    }

    .staff-profile-page .profile-status--inline::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: rgba(255,255,255,.72);
    }

    .staff-profile-page .profile-hero-side {
        display: grid;
        align-content: center;
        justify-items: start;
        margin-left: 0;
        padding: 34px 38px 34px 76px;
        border-radius: 0 12px 12px 0;
        background: #fff;
        z-index: 1;
    }

    .staff-profile-page .profile-hero-side span {
        color: var(--profile-navy);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .16em;
    }

    .staff-profile-page .profile-hero-side strong {
        margin-top: 8px;
        color: var(--profile-navy);
        font-size: 20px;
        line-height: 1.2;
        font-weight: 900;
    }

    .staff-profile-page .profile-hero-side small {
        margin-top: 8px;
        color: var(--profile-blue);
        font-size: 14px;
        font-weight: 900;
    }

    .staff-profile-page .profile-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .staff-profile-page .profile-summary {
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 88px;
        padding: 16px 20px;
        border: 1px solid var(--profile-border);
        border-top: 4px solid var(--summary-color);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 5px 12px rgba(8, 47, 89, .035);
    }

    .staff-profile-page .profile-summary--green { --summary-color: #18B877; }
    .staff-profile-page .profile-summary--blue { --summary-color: var(--profile-blue); }
    .staff-profile-page .profile-summary--amber { --summary-color: var(--profile-amber); }
    .staff-profile-page .profile-summary--navy { --summary-color: var(--profile-navy); }

    .staff-profile-page .profile-summary span,
    .staff-profile-page .profile-form label span {
        display: block;
        color: var(--profile-muted);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .staff-profile-page .profile-summary strong {
        display: block;
        margin-top: 6px;
        color: #061A3A;
        font-size: clamp(20px, 1.8vw, 26px);
        line-height: 1.1;
        font-weight: 900;
        overflow-wrap: anywhere;
    }

    .staff-profile-page .profile-summary small {
        display: block;
        margin-top: 6px;
        color: var(--profile-muted);
        font-size: 12px;
        font-weight: 650;
    }

    .staff-profile-page .profile-layout {
        grid-template-columns: minmax(0, 1.9fr) minmax(380px, 1fr);
        gap: 18px;
        align-items: stretch;
    }

    .staff-profile-page .profile-main,
    .staff-profile-page .profile-side {
        gap: 18px;
    }

    .staff-profile-page .profile-panel {
        border: 1px solid var(--profile-border) !important;
        border-radius: 10px !important;
        background: #fff !important;
        box-shadow: 0 8px 18px rgba(8, 47, 89, .045) !important;
    }

    .staff-profile-page .panel-section-head {
        display: block;
        padding: 22px 24px 0;
        border: 0;
        background: transparent;
    }

    .staff-profile-page .panel-section-head h2 {
        position: relative;
        margin: 0;
        padding: 0 0 0 16px;
        color: var(--profile-navy);
        font-size: 22px;
        line-height: 1.18;
        font-weight: 900;
        letter-spacing: 0;
    }

    .staff-profile-page .panel-section-head h2::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 28px;
        border-radius: 999px;
        background: var(--profile-red);
    }

    .staff-profile-page .panel-section-head p {
        margin: 7px 0 0 16px;
        color: var(--profile-muted);
        font-size: 14px;
        font-weight: 650;
    }

    .staff-profile-page .profile-form {
        padding: 20px 24px 24px;
    }

    .staff-profile-page .profile-form input {
        min-height: 38px;
        border: 1px solid #C8D6E7;
        border-radius: 5px;
        color: var(--profile-navy);
        font-size: 14px;
        font-weight: 650;
    }

    .staff-profile-page .profile-actions .button {
        min-width: 160px;
        min-height: 42px;
        border-color: var(--profile-blue);
        border-radius: 6px;
        background: var(--profile-blue);
        color: #fff;
        box-shadow: 0 8px 16px rgba(11, 94, 215, .14);
    }

    .staff-profile-page .profile-detail-table {
        border-color: var(--profile-border);
    }

    .staff-profile-page .profile-activity {
        position: relative;
        margin: 20px 24px 24px;
        padding: 0 0 0 22px;
    }

    .staff-profile-page .profile-activity::before {
        content: "";
        position: absolute;
        top: 14px;
        bottom: 14px;
        left: 4px;
        width: 1px;
        background: #C8D6E7;
    }

    .staff-profile-page .profile-activity__item {
        position: relative;
        min-height: auto;
        padding: 0 0 18px;
        border-bottom: 1px solid var(--profile-border);
    }

    .staff-profile-page .profile-activity__item + .profile-activity__item {
        padding-top: 18px;
    }

    .staff-profile-page .profile-activity__item::before {
        content: "";
        position: absolute;
        top: 6px;
        left: -23px;
        width: 9px;
        height: 9px;
        border-radius: 999px;
        background: var(--profile-navy);
        box-shadow: 0 0 0 4px #fff;
    }

    .staff-profile-page .profile-activity__item + .profile-activity__item::before {
        top: 24px;
    }

    .staff-profile-page .profile-activity__item strong {
        color: var(--profile-navy);
        font-size: 14px;
        font-weight: 900;
    }

    .staff-profile-page .profile-activity__item span,
    .staff-profile-page .profile-empty {
        color: var(--profile-muted);
        font-size: 13px;
        font-weight: 700;
    }

    @media (max-width: 1180px) {
        .staff-profile-page .profile-hero-card,
        .staff-profile-page .profile-layout {
            grid-template-columns: 1fr;
        }

        .staff-profile-page .profile-hero-main {
            border-radius: 12px 12px 0 0;
            clip-path: none;
        }

        .staff-profile-page .profile-hero-main::after {
            top: auto;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 6px;
            clip-path: none;
        }

        .staff-profile-page .profile-hero-side {
            padding: 28px 34px;
            border-radius: 0 0 12px 12px;
        }

        .staff-profile-page .profile-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .staff-profile-page {
            width: min(100% - 24px, 1500px);
        }

        .staff-profile-page .profile-hero-main {
            padding: 26px 24px;
        }

        .staff-profile-page .profile-hero-main .profile-identity {
            flex-direction: column;
            align-items: flex-start;
        }

        .staff-profile-page .profile-hero-side {
            padding: 24px;
        }

        .staff-profile-page .profile-summary-grid,
        .staff-profile-page .profile-form-grid {
            grid-template-columns: 1fr;
        }

        .staff-profile-page .profile-actions .button {
            width: 100%;
        }
    }
</style>
@endpush
