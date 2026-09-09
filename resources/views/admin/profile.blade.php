@extends('layouts.app')

@section('title', 'Profil Admin')
@section('page-title', 'Profil Admin')
@section('page-subtitle', 'Maklumat akaun admin dan tetapan kata laluan.')

@section('content')
    @include('components.coop-page-style')

    <div class="coop-wrap profile-page profile-page--enhanced">
        <section class="coop-hero profile-hero">
            <div class="profile-identity">
                <span class="profile-avatar">{{ strtoupper(mb_substr($user->nama ?? 'A', 0, 1)) }}</span>
                <div>
                    <span class="coop-kicker">Profil Admin</span>
                    <h1>{{ $user->nama }}</h1>
                    <p>{{ $user->username }} &middot; {{ $user->peranan ?? 'Admin' }}</p>
                </div>
            </div>
            <span class="profile-status">{{ $user->status_aktif ? 'Aktif' : 'Tidak Aktif' }}</span>
        </section>

        <section class="profile-layout profile-layout--single">
            <div class="profile-main">
                <section class="panel profile-panel">
                    <div class="panel-section-head">
                        <div>
                            <h2>Maklumat Akaun</h2>
                            <p>Maklumat asas akaun admin semasa.</p>
                        </div>
                    </div>
                    <div class="profile-form">
                        <div class="profile-form-grid">
                            <label>
                                <span>Nama Penuh</span>
                                <input value="{{ $user->nama }}" disabled>
                            </label>
                            <label>
                                <span>Username</span>
                                <input value="{{ $user->username }}" disabled>
                            </label>
                            <label>
                                <span>No. KP</span>
                                <input value="{{ $user->nric ?? '-' }}" disabled>
                            </label>
                            <label>
                                <span>Peranan</span>
                                <input value="{{ $user->peranan ?? 'Admin' }}" disabled>
                            </label>
                        </div>
                    </div>
                </section>

                <section class="panel profile-panel" id="kata-laluan">
                    <div class="panel-section-head">
                        <div>
                            <h2>Tukar Kata Laluan</h2>
                            <p>Kemaskini kata laluan akaun untuk keselamatan login.</p>
                        </div>
                    </div>
                    <form class="profile-form" method="POST" action="{{ route('admin.profile.password') }}">
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
    .profile-layout--single {
        display: block;
    }
    .profile-main {
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
    .profile-form label span {
        display: block;
        color: var(--muted);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .02em;
        text-transform: uppercase;
    }
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
        border-color: var(--secondary);
        outline: 3px solid rgba(36, 83, 166, .14);
    }
    .profile-form small {
        color: var(--danger);
        font-size: 12px;
        font-weight: 700;
    }
    .profile-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }
    @media (max-width: 720px) {
        .profile-hero { align-items: flex-start; }
        .profile-identity { align-items: flex-start; }
        .profile-form-grid { grid-template-columns: 1fr; }
        .profile-actions .button { width: 100%; }
    }
</style>
@endpush
