@extends('layouts.app')

@section('title', 'Profil Pelajar')
@section('page-title', 'Profil Pelajar')
@section('page-subtitle', 'Maklumat akaun, keanggotaan, saham dan aktiviti terkini.')

@section('content')
    @include('components.coop-page-style')

    @php
        $share = $user->saham;
        $memberStatus = $user->no_anggota ? 'Anggota Koperasi' : 'Belum Aktif';
        $totalShare = (float) optional($share)->syer + (float) optional($share)->tambahan_saham;
        $memberNumber = $user->no_anggota ?? 'Belum dijana';
        $profileInitial = collect(explode(' ', trim((string) $user->nama)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_substr($part, 0, 1))
            ->join('');
        $profileInitial = strtoupper($profileInitial ?: 'S');

        $activityStatusClass = function (?string $status): string {
            $status = strtolower(str_replace('_', ' ', (string) $status));

            return match (true) {
                str_contains($status, 'lulus') => 'is-approved',
                str_contains($status, 'belum') => 'is-muted',
                str_contains($status, 'credit'), str_contains($status, 'debit') => 'is-transaction',
                default => 'is-new',
            };
        };
    @endphp

    <div class="student-profile-page">
        <section class="profile-hero-card" aria-label="Profil Pelajar">
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
                        <span class="profile-kicker">Profil Pelajar</span>
                        <h1>{{ $user->nama }}</h1>
                        <p>{{ $user->no_matrik }} &middot; {{ $user->program ?? 'Program belum ditetapkan' }}</p>
                        <span class="profile-status profile-status--inline">
                            {{ $user->status_aktif ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="profile-hero-divider" aria-hidden="true"></div>
            <div class="profile-hero-side">
                <span>STATUS PENGGUNA</span>
                <strong>Pelajar / {{ $memberStatus }}</strong>
                <small>No. Anggota: {{ $memberNumber }}</small>
            </div>
        </section>

        <section class="profile-summary-grid" aria-label="Ringkasan profil">
            <article class="profile-summary profile-summary--green">
                <div>
                    <span>Status Akaun</span>
                    <strong>{{ $user->status_aktif ? 'Aktif' : 'Tidak Aktif' }}</strong>
                    <small>Akaun anda dalam keadaan aktif.</small>
                </div>
            </article>
            <article class="profile-summary profile-summary--blue">
                <div>
                    <span>No Anggota Pelajar</span>
                    <strong>{{ $memberNumber }}</strong>
                    <small>Nombor keahlian anda.</small>
                </div>
            </article>
            <article class="profile-summary profile-summary--amber">
                <div>
                    <span>Jumlah Saham</span>
                    <strong>RM {{ number_format($totalShare, 2) }}</strong>
                    <small>Jumlah saham terkini anda.</small>
                </div>
            </article>
            <article class="profile-summary profile-summary--purple">
                <div>
                    <span>Tarikh Daftar</span>
                    <strong>{{ optional($user->tarikh_daftar)->format('d/m/Y') ?? '-' }}</strong>
                    <small>Tarikh anda berdaftar.</small>
                </div>
            </article>
        </section>

        <section class="profile-layout">
            <div class="profile-main">
                <section class="profile-panel">
                    <div class="panel-section-head">
                        <div>
                            <h2>Kemaskini Maklumat</h2>
                            <p>Kemaskini maklumat hubungan yang digunakan untuk urusan koperasi.</p>
                        </div>
                    </div>
                    <form class="profile-form" method="POST" action="{{ route('student.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="profile-form-grid">
                            <label>
                                <span>Nama Penuh</span>
                                <input value="{{ $user->nama }}" disabled aria-readonly="true">
                            </label>
                            <label>
                                <span>No. KP</span>
                                <input value="{{ $user->nric ?? '-' }}" disabled aria-readonly="true">
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
                            <label>
                                <span>No Matrik</span>
                                <input value="{{ $user->no_matrik }}" disabled aria-readonly="true">
                            </label>
                            <label>
                                <span>Kelas</span>
                                <input value="{{ $user->kelas ?? '-' }}" disabled aria-readonly="true">
                            </label>
                        </div>

                        <div class="profile-actions">
                            <button class="profile-button" type="submit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
                                Simpan Profil
                            </button>
                        </div>
                    </form>
                </section>

                <section class="profile-panel">
                    <div class="panel-section-head">
                        <div>
                            <h2>Ringkasan Saham</h2>
                            <p>Maklumat syer semasa anda.</p>
                        </div>
                    </div>
                    <div class="profile-share-summary">
                        <table class="profile-detail-table">
                            <tr>
                                <th>Syer Asas</th>
                                <td>RM {{ number_format((float) optional($share)->syer, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Tambahan Saham</th>
                                <td>RM {{ number_format((float) optional($share)->tambahan_saham, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Yuran Anggota</th>
                                <td>RM {{ number_format((float) optional($share)->yuran, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Status Anggota</th>
                                <td>{{ $memberStatus }}</td>
                            </tr>
                        </table>
                    </div>
                </section>

                <section class="profile-panel" id="kata-laluan">
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
                                <span class="password-field">
                                    <input name="current_password" type="password" autocomplete="current-password">
                                    <button type="button" data-password-toggle aria-label="Papar kata laluan">
                                        <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2 2 20 20"/><path d="M6.7 6.7C3.8 8.6 2 12 2 12s3.5 7 10 7c1.8 0 3.3-.5 4.7-1.2"/><path d="M9.9 4.2C10.6 4.1 11.3 4 12 4c6.5 0 10 8 10 8s-.8 1.7-2.2 3.4"/><path d="M14.1 14.1A3 3 0 0 1 9.9 9.9"/></svg>
                                    </button>
                                </span>
                                @error('current_password')<small>{{ $message }}</small>@enderror
                            </label>
                            <label>
                                <span>Kata Laluan Baharu</span>
                                <span class="password-field">
                                    <input name="password" type="password" autocomplete="new-password">
                                    <button type="button" data-password-toggle aria-label="Papar kata laluan">
                                        <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2 2 20 20"/><path d="M6.7 6.7C3.8 8.6 2 12 2 12s3.5 7 10 7c1.8 0 3.3-.5 4.7-1.2"/><path d="M9.9 4.2C10.6 4.1 11.3 4 12 4c6.5 0 10 8 10 8s-.8 1.7-2.2 3.4"/><path d="M14.1 14.1A3 3 0 0 1 9.9 9.9"/></svg>
                                    </button>
                                </span>
                                @error('password')<small>{{ $message }}</small>@enderror
                            </label>
                            <label>
                                <span>Sahkan Kata Laluan Baharu</span>
                                <span class="password-field">
                                    <input name="password_confirmation" type="password" autocomplete="new-password">
                                    <button type="button" data-password-toggle aria-label="Papar kata laluan">
                                        <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2 2 20 20"/><path d="M6.7 6.7C3.8 8.6 2 12 2 12s3.5 7 10 7c1.8 0 3.3-.5 4.7-1.2"/><path d="M9.9 4.2C10.6 4.1 11.3 4 12 4c6.5 0 10 8 10 8s-.8 1.7-2.2 3.4"/><path d="M14.1 14.1A3 3 0 0 1 9.9 9.9"/></svg>
                                    </button>
                                </span>
                            </label>
                        </div>

                        <div class="profile-actions">
                            <button class="profile-button" type="submit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Tukar Kata Laluan
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <aside class="profile-side">
                <section class="profile-panel profile-activity-panel">
                    <div class="panel-section-head">
                        <div>
                            <h2>Aktiviti Terkini</h2>
                            <p>Ringkasan permohonan, transaksi dan tempahan.</p>
                        </div>
                    </div>
                    <div class="profile-activity">
                        @forelse ($latestApplications as $application)
                            @php
                                $applicationStatus = ucfirst(str_replace('_', ' ', $application->status));
                                $applicationClass = $activityStatusClass($application->status);
                            @endphp
                            <div class="profile-activity__item {{ $applicationClass }}">
                                <div>
                                    <strong>Permohonan {{ ucwords(str_replace('_', ' ', $application->jenis)) }}</strong>
                                    <span>{{ $applicationStatus }} &middot; {{ optional($application->tarikh_permohonan)->format('d/m/Y') ?? '-' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="profile-empty">Tiada permohonan terkini.</div>
                        @endforelse

                        @foreach ($recentTransactions as $transaction)
                            <div class="profile-activity__item is-transaction">
                                <div>
                                    <strong>Transaksi saham RM {{ number_format((float) $transaction->amount, 2) }}</strong>
                                    <span>{{ strtoupper($transaction->direction ?? '-') }} &middot; {{ optional($transaction->transacted_at)->format('d/m/Y') ?? '-' }}</span>
                                </div>
                            </div>
                        @endforeach

                        @foreach ($recentOrders as $order)
                            @php
                                $orderStatus = ucfirst(str_replace('_', ' ', (string) ($order->status ?? 'baru')));
                                $orderClass = $activityStatusClass($order->status ?? 'baru');
                            @endphp
                            <div class="profile-activity__item {{ $orderClass }}">
                                <div>
                                    <strong>Tempahan baju</strong>
                                    <span>{{ $orderStatus }} &middot; {{ isset($order->tarikh_tempahan) ? \Carbon\Carbon::parse($order->tarikh_tempahan)->format('d/m/Y') : '-' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </aside>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        function initStudentProfilePasswordToggles() {
            document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const input = button.closest('.password-field')?.querySelector('input');

                    if (!input) {
                        return;
                    }

                    const showPassword = input.type === 'password';
                    input.type = showPassword ? 'text' : 'password';
                    button.classList.toggle('is-visible', showPassword);
                    button.setAttribute('aria-label', showPassword ? 'Sembunyi kata laluan' : 'Papar kata laluan');
                    input.focus();
                });
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initStudentProfilePasswordToggles);
        } else {
            initStudentProfilePasswordToggles();
        }
    </script>
@endpush

@push('styles')
<style>
    .content:has(.student-profile-page) {
        background: #F4F7FB;
    }

    .student-profile-page {
        --profile-navy: #082F59;
        --profile-red: #ED1C2E;
        --profile-blue: #0B5ED7;
        --profile-blue-soft: #EAF2FF;
        --profile-green-soft: #DDF7E8;
        --profile-green: #087A45;
        --profile-amber: #F59E0B;
        --profile-purple: #7C3AED;
        --profile-card: #FFFFFF;
        --profile-border: #D7E2EF;
        --profile-muted: #526987;
        width: min(100% - clamp(24px, 4vw, 64px), 1500px);
        margin: 0 auto;
        display: grid;
        gap: 16px;
        color: var(--profile-navy);
    }

    .profile-hero-card {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        min-height: 116px;
        padding: 22px 28px 22px 48px;
        border: 1px solid var(--profile-border);
        border-radius: 12px;
        background: var(--profile-navy);
        box-shadow: 0 10px 28px rgba(8, 47, 89, .10);
        overflow: hidden;
    }

    .profile-hero-card::before,
    .profile-hero-card::after {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
    }

    .profile-hero-card::before {
        left: 0;
        width: 14px;
        background: #062747;
    }

    .profile-hero-card::after {
        left: 28px;
        width: 4px;
        top: 22px;
        bottom: 22px;
        border-radius: 999px;
        background: var(--profile-red);
    }

    .profile-identity {
        display: flex;
        align-items: center;
        gap: 24px;
        min-width: 0;
    }

    .profile-avatar {
        width: 78px;
        height: 78px;
        flex: 0 0 auto;
        display: grid;
        place-items: center;
        border-radius: 999px;
        background: #DCEBFF;
        color: var(--profile-navy);
        font-size: 28px;
        font-weight: 900;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        display: block;
        border-radius: inherit;
        object-fit: cover;
    }

    .profile-kicker {
        display: inline-flex;
        align-items: center;
        min-height: 24px;
        margin-bottom: 6px;
        padding: 0 8px;
        border-radius: 6px;
        background: rgba(234, 242, 255, .16);
        color: #DCEBFF;
        font-size: 12px;
        font-weight: 900;
    }

    .profile-identity h1 {
        margin: 0;
        color: #FFFFFF;
        font-size: clamp(25px, 2.2vw, 34px);
        line-height: 1.1;
        font-weight: 900;
        letter-spacing: 0;
    }

    .profile-identity p {
        margin: 8px 0 0;
        color: rgba(255, 255, 255, .82);
        font-size: clamp(14px, 1.2vw, 16px);
        font-weight: 800;
    }

    .profile-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 40px;
        padding: 0 18px;
        border-radius: 10px;
        background: #FFFFFF;
        color: var(--profile-green);
        font-size: 14px;
        font-weight: 900;
        white-space: nowrap;
    }

    .profile-status svg {
        width: 21px;
        height: 21px;
    }

    .profile-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .profile-summary {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        align-items: center;
        gap: 0;
        min-height: 88px;
        padding: 18px 24px;
        border: 1px solid var(--profile-border);
        border-left: 6px solid var(--summary-color);
        border-radius: 10px;
        background: var(--profile-card);
        box-shadow: 0 8px 22px rgba(8, 47, 89, .05);
        overflow: hidden;
    }

    .profile-summary--green { --summary-color: #18B877; --summary-bg: #E7F8EF; }
    .profile-summary--blue { --summary-color: var(--profile-blue); --summary-bg: var(--profile-blue-soft); }
    .profile-summary--amber { --summary-color: var(--profile-amber); --summary-bg: #FFF3D9; }
    .profile-summary--purple { --summary-color: var(--profile-purple); --summary-bg: #F0E9FF; }

    .summary-icon {
        display: none;
        place-items: center;
        width: 60px;
        height: 60px;
        border-radius: 12px;
        background: var(--summary-bg);
        color: var(--summary-color);
    }

    .summary-icon svg {
        width: 31px;
        height: 31px;
    }

    .profile-summary span,
    .profile-form label span {
        display: block;
        color: var(--profile-muted);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .profile-summary strong {
        display: block;
        margin-top: 6px;
        color: var(--profile-navy);
        font-size: clamp(20px, 1.7vw, 24px);
        line-height: 1.1;
        font-weight: 900;
        overflow-wrap: anywhere;
    }

    .profile-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.94fr) minmax(340px, 1fr);
        gap: 16px;
        align-items: stretch;
    }

    .profile-main,
    .profile-side {
        display: grid;
        gap: 16px;
        align-content: start;
    }

    .profile-panel {
        border: 1px solid var(--profile-border);
        border-radius: 12px;
        background: var(--profile-card);
        box-shadow: 0 8px 22px rgba(8, 47, 89, .05);
        overflow: hidden;
    }

    .profile-activity-panel {
        height: 100%;
    }

    .panel-section-head {
        display: grid;
        grid-template-columns: 30px minmax(0, 1fr);
        gap: 12px;
        align-items: start;
        padding: 20px 24px 0;
        border: 0;
        background: transparent;
    }

    .section-icon {
        display: grid;
        place-items: center;
        width: 28px;
        height: 28px;
        margin-top: 2px;
        color: var(--profile-navy);
    }

    .section-icon svg {
        width: 24px;
        height: 24px;
    }

    .panel-section-head h2 {
        position: relative;
        margin: 0;
        padding-bottom: 13px;
        color: var(--profile-navy);
        font-size: clamp(20px, 1.55vw, 24px);
        line-height: 1.18;
        font-weight: 900;
        letter-spacing: 0;
    }

    .panel-section-head h2::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: 0;
        width: 34px;
        height: 4px;
        border-radius: 999px;
        background: var(--profile-red);
    }

    .panel-section-head p {
        margin: 10px 0 0;
        color: var(--profile-muted);
        font-size: 14px;
        font-weight: 650;
    }

    .profile-form {
        padding: 24px;
    }

    .profile-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .profile-form label {
        display: grid;
        gap: 7px;
    }

    .profile-form input {
        width: 100%;
        min-height: 46px;
        border: 1px solid #C8D6E7;
        border-radius: 8px;
        padding: 0 13px;
        color: var(--profile-navy);
        background: #fff;
        font-size: 15px;
        font-weight: 800;
        transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .profile-form input:disabled {
        background: #F1F5FA;
        color: #46617F;
        opacity: 1;
        cursor: not-allowed;
    }

    .profile-form input:focus {
        outline: none;
        border-color: var(--profile-blue);
        box-shadow: 0 0 0 3px rgba(11, 94, 215, .14);
    }

    .profile-share-summary {
        padding: 20px 24px 24px;
    }

    .profile-detail-table {
        width: 100%;
        border: 1px solid var(--profile-border);
        border-radius: 8px;
        overflow: hidden;
        border-collapse: separate;
        border-spacing: 0;
    }

    .profile-detail-table th,
    .profile-detail-table td {
        padding: 16px 20px;
        border-bottom: 1px solid var(--profile-border);
        text-align: left;
    }

    .profile-detail-table tr:last-child th,
    .profile-detail-table tr:last-child td {
        border-bottom: 0;
    }

    .profile-detail-table th {
        width: 38%;
        background: #F8FBFF;
        color: var(--profile-navy);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .profile-detail-table td {
        color: #061A3A;
        font-size: 16px;
        font-weight: 900;
    }

    .profile-form small {
        color: var(--profile-red);
        font-weight: 800;
    }

    .password-field {
        position: relative;
        display: block;
    }

    .password-field input {
        padding-right: 46px;
    }

    .password-field button {
        position: absolute;
        top: 50%;
        right: 8px;
        display: grid;
        place-items: center;
        width: 32px;
        height: 32px;
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: var(--profile-muted);
        transform: translateY(-50%);
        cursor: pointer;
    }

    .password-field button:hover,
    .password-field button:focus-visible {
        background: var(--profile-blue-soft);
        color: var(--profile-blue);
        outline: none;
    }

    .password-field svg {
        width: 19px;
        height: 19px;
    }

    .password-field .eye-closed,
    .password-field button.is-visible .eye-open {
        display: none;
    }

    .password-field button.is-visible .eye-closed {
        display: block;
    }

    .profile-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 16px;
    }

    .profile-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 42px;
        min-width: 178px;
        padding: 0 20px;
        border: 1px solid var(--profile-red);
        border-radius: 8px;
        background: var(--profile-red);
        color: #fff;
        font: inherit;
        font-size: 14px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 8px 16px rgba(237, 28, 46, .14);
    }

    .profile-button:hover,
    .profile-button:focus-visible {
        border-color: #C91525;
        background: #C91525;
        outline: none;
    }

    .profile-button svg {
        width: 19px;
        height: 19px;
    }

    .profile-activity {
        position: relative;
        display: grid;
        gap: 0;
        margin: 18px 24px 24px;
        padding-left: 50px;
    }

    .profile-activity::before {
        content: "";
        position: absolute;
        top: 22px;
        bottom: 22px;
        left: 24px;
        width: 2px;
        background: var(--profile-border);
    }

    .profile-activity__item,
    .profile-empty {
        position: relative;
        min-height: 64px;
        padding: 12px 0 14px;
        border-bottom: 1px solid var(--profile-border);
    }

    .profile-activity__item:last-child,
    .profile-empty:last-child {
        border-bottom: 0;
    }

    .activity-icon {
        position: absolute;
        left: -50px;
        top: 12px;
        z-index: 1;
        display: grid;
        place-items: center;
        width: 42px;
        height: 42px;
        border: 5px solid var(--profile-card);
        border-radius: 999px;
        background: var(--activity-bg);
        color: var(--activity-color);
    }

    .activity-icon svg {
        width: 21px;
        height: 21px;
    }

    .profile-activity__item.is-new {
        --activity-bg: var(--profile-blue-soft);
        --activity-color: var(--profile-blue);
    }

    .profile-activity__item.is-approved {
        --activity-bg: var(--profile-green-soft);
        --activity-color: var(--profile-green);
    }

    .profile-activity__item.is-transaction {
        --activity-bg: #FFE3E7;
        --activity-color: var(--profile-red);
    }

    .profile-activity__item.is-muted {
        --activity-bg: #EEF2F6;
        --activity-color: #526987;
    }

    .profile-activity__item strong {
        display: block;
        color: var(--profile-navy);
        font-size: 15px;
        line-height: 1.25;
        font-weight: 900;
    }

    .profile-activity__item span,
    .profile-empty {
        display: block;
        margin-top: 5px;
        color: var(--profile-muted);
        font-size: 14px;
        font-weight: 700;
    }

    @media (max-width: 1180px) {
        .profile-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .profile-layout {
            grid-template-columns: 1fr;
        }

        .profile-activity-panel {
            height: auto;
        }
    }

    @media (max-width: 720px) {
        .student-profile-page {
            width: min(100% - 24px, 1500px);
        }

        .profile-hero-card {
            flex-direction: column;
            align-items: flex-start;
            padding: 22px 18px 20px 38px;
        }

        .profile-hero-card::after {
            left: 22px;
            top: 20px;
            bottom: 20px;
        }

        .profile-identity {
            align-items: flex-start;
            gap: 16px;
            flex-direction: column;
        }

        .profile-avatar {
            width: 68px;
            height: 68px;
            font-size: 24px;
        }

        .profile-status {
            width: 100%;
        }

        .profile-summary-grid,
        .profile-form-grid {
            grid-template-columns: 1fr;
        }

        .profile-summary {
            grid-template-columns: minmax(0, 1fr);
            min-height: 84px;
            padding: 16px;
        }

        .summary-icon {
            width: 54px;
            height: 54px;
        }

        .panel-section-head {
            padding: 18px 18px 0;
        }

        .profile-form {
            padding: 20px 18px;
        }

        .profile-actions .profile-button {
            width: 100%;
        }

        .profile-activity {
            margin: 18px;
        }
    }

    @media (max-width: 420px) {
        .profile-summary {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .profile-activity {
            padding-left: 42px;
        }

        .profile-activity::before {
            left: 20px;
        }

        .activity-icon {
            left: -42px;
            width: 38px;
            height: 38px;
        }
    }

    .student-profile-page {
        gap: 18px;
    }

    .student-profile-page .profile-hero-card {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(320px, .9fr);
        gap: 0;
        min-height: 178px;
        padding: 0;
        border: 1px solid var(--profile-border);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 10px 28px rgba(8, 47, 89, .08);
    }

    .student-profile-page .profile-hero-card::before,
    .student-profile-page .profile-hero-card::after {
        content: none;
    }

    .profile-hero-main {
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

    .profile-hero-main::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 18% 20%, rgba(255,255,255,.08), transparent 30%),
            linear-gradient(135deg, rgba(255,255,255,.08), transparent 42%);
        pointer-events: none;
    }

    .profile-hero-main::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 1;
        background: var(--profile-red);
        clip-path: polygon(calc(100% - 74px) 0, calc(100% - 64px) 0, 100% 100%, calc(100% - 10px) 100%);
        pointer-events: none;
    }

    .profile-watermark {
        position: absolute;
        left: 26px;
        bottom: -4px;
        color: rgba(255, 255, 255, .055);
        font-size: 34px;
        font-weight: 900;
        letter-spacing: .02em;
        pointer-events: none;
    }

    .profile-hero-main .profile-identity {
        position: relative;
        z-index: 2;
        gap: 26px;
    }

    .profile-hero-main .profile-avatar {
        width: 94px;
        height: 94px;
        border: 3px solid rgba(255,255,255,.34);
        background: #DCEBFF;
        color: var(--profile-navy);
        font-size: 34px;
        box-shadow: 0 10px 24px rgba(0,0,0,.16);
    }

    .profile-hero-main .profile-avatar svg {
        width: 54px;
        height: 54px;
    }

    .profile-hero-main .profile-kicker {
        min-height: 30px;
        margin-bottom: 10px;
        padding: 0 12px;
        border-radius: 6px;
        background: rgba(234, 242, 255, .18);
        color: #DCEBFF;
        font-size: 13px;
    }

    .profile-hero-main .profile-identity h1 {
        font-size: clamp(27px, 2.35vw, 38px);
        line-height: 1.05;
    }

    .profile-hero-main .profile-identity p {
        margin-top: 10px;
        font-size: clamp(15px, 1.22vw, 18px);
    }

    .profile-status--inline {
        width: auto;
        min-height: 28px;
        margin-top: 12px;
        gap: 7px;
        border-radius: 999px;
        background: #18B877;
        color: #fff;
        padding: 0 12px;
        font-size: 13px;
    }

    .profile-status--inline::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: rgba(255,255,255,.72);
    }

    .profile-hero-divider {
        display: none;
    }

    .profile-hero-divider::before {
        content: none;
    }

    .profile-hero-side {
        display: grid;
        align-content: center;
        justify-items: start;
        margin-left: 0;
        padding: 34px 38px 34px 76px;
        border-radius: 0 12px 12px 0;
        background: #fff;
        z-index: 1;
    }

    .profile-hero-side span {
        color: var(--profile-navy);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .16em;
    }

    .profile-hero-side strong {
        margin-top: 8px;
        color: var(--profile-navy);
        font-size: 20px;
        line-height: 1.2;
        font-weight: 900;
    }

    .profile-hero-side small {
        margin-top: 8px;
        color: var(--profile-blue);
        font-size: 14px;
        font-weight: 900;
    }

    .student-profile-page .profile-summary {
        min-height: 114px;
        padding: 22px 26px;
        border-left: 1px solid var(--profile-border);
        border-top: 5px solid var(--summary-color);
        border-radius: 8px;
        box-shadow: 0 8px 18px rgba(8, 47, 89, .045);
    }

    .profile-summary--purple {
        --summary-color: var(--profile-navy);
    }

    .student-profile-page .profile-summary strong {
        margin-top: 7px;
        font-size: clamp(26px, 2.25vw, 32px);
        color: #061A3A;
    }

    .student-profile-page .profile-summary small {
        display: block;
        margin-top: 7px;
        color: var(--profile-muted);
        font-size: 14px;
        font-weight: 650;
    }

    .student-profile-page .profile-layout {
        grid-template-columns: minmax(0, 1.9fr) minmax(380px, 1fr);
        gap: 18px;
        align-items: stretch;
    }

    .student-profile-page .profile-panel {
        border-radius: 10px;
        box-shadow: 0 8px 18px rgba(8, 47, 89, .045);
    }

    .student-profile-page .panel-section-head {
        display: block;
        padding: 22px 24px 0;
    }

    .student-profile-page .panel-section-head h2 {
        padding: 0 0 0 16px;
        font-size: 22px;
    }

    .student-profile-page .panel-section-head h2::after {
        top: 0;
        bottom: auto;
        left: 0;
        width: 4px;
        height: 28px;
        border-radius: 999px;
    }

    .student-profile-page .panel-section-head p {
        margin: 7px 0 0 16px;
    }

    .student-profile-page .profile-form {
        padding: 20px 24px 24px;
    }

    .student-profile-page .profile-form input {
        min-height: 38px;
        border-radius: 5px;
        font-size: 14px;
        font-weight: 650;
    }

    .student-profile-page .profile-button {
        min-width: 160px;
        border-color: var(--profile-blue);
        border-radius: 6px;
        background: var(--profile-blue);
        box-shadow: 0 8px 16px rgba(11, 94, 215, .14);
    }

    .student-profile-page .profile-button:hover,
    .student-profile-page .profile-button:focus-visible {
        border-color: #084FB5;
        background: #084FB5;
    }

    .student-profile-page .profile-button > svg {
        display: none;
    }

    .student-profile-page .profile-activity {
        margin: 20px 24px 24px;
        padding-left: 22px;
    }

    .student-profile-page .profile-activity::before {
        top: 14px;
        bottom: 14px;
        left: 4px;
        width: 1px;
        background: #C8D6E7;
    }

    .student-profile-page .profile-activity__item {
        min-height: auto;
        padding: 0 0 18px;
        border-bottom: 1px solid var(--profile-border);
    }

    .student-profile-page .profile-activity__item + .profile-activity__item {
        padding-top: 18px;
    }

    .student-profile-page .profile-activity__item::before {
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

    .student-profile-page .profile-activity__item + .profile-activity__item::before {
        top: 24px;
    }

    .student-profile-page .profile-activity__item strong {
        font-size: 14px;
    }

    .student-profile-page .profile-activity__item span,
    .student-profile-page .profile-empty {
        font-size: 13px;
    }

    @media (max-width: 1180px) {
        .student-profile-page .profile-hero-card {
            grid-template-columns: 1fr;
        }

        .profile-hero-main {
            border-radius: 12px 12px 0 0;
            clip-path: none;
        }

        .profile-hero-main::after {
            top: auto;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 6px;
            transform: none;
        }

        .profile-hero-side {
            margin-left: 0;
            padding: 28px 34px;
            border-radius: 0 0 12px 12px;
        }
    }

    @media (max-width: 720px) {
        .profile-hero-main {
            padding: 26px 24px;
        }

        .profile-hero-main .profile-identity {
            flex-direction: column;
        }

        .profile-hero-side {
            padding: 24px;
        }

        .student-profile-page .profile-summary-grid,
        .student-profile-page .profile-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
