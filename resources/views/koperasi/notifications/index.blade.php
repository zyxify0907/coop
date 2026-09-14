@extends('layouts.app')

@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')
@section('page-subtitle', 'Makluman sistem koperasi.')

@push('styles')
    <style>
        .notification-page { gap: 20px; }
        .notification-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); border: 1px solid var(--line); border-radius: 8px; overflow: hidden; background: #fff; }
        .notification-summary__item { padding: 16px 20px; border-right: 1px solid var(--line); }
        .notification-summary__item:last-child { border-right: 0; }
        .notification-summary__item span { display: block; color: var(--muted); font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .notification-summary__item strong { display: block; margin-top: 6px; color: var(--text); font-size: 25px; line-height: 1.1; }
        .notification-summary__item--unread strong { color: var(--warning); }
        .notifications-panel__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; padding: 18px 20px; border-bottom: 1px solid var(--line); }
        .notifications-panel__head h2 { margin: 0; font-size: 19px; }
        .notifications-panel__head p { margin: 4px 0 0; color: var(--muted); font-size: 13px; }
        .notifications-filter { display: grid; grid-template-columns: minmax(220px, 1.5fr) minmax(150px, .7fr) minmax(150px, .7fr) auto; gap: 12px; align-items: end; padding: 16px 20px; border-bottom: 1px solid var(--line); background: var(--surface-soft); }
        .notifications-filter label { display: grid; gap: 6px; color: var(--muted); font-size: 12px; font-weight: 700; }
        .notifications-filter input, .notifications-filter select { width: 100%; min-height: 40px; padding: 8px 10px; border: 1px solid var(--line-strong); border-radius: 6px; background: #fff; color: var(--text); font: inherit; font-size: 14px; }
        .notifications-filter input:focus, .notifications-filter select:focus { border-color: var(--secondary); box-shadow: 0 0 0 3px rgba(36, 83, 166, .12); outline: 0; }
        .notifications-filter__actions { display: flex; gap: 8px; }
        .notifications-filter__actions .button, .notifications-filter__actions .link-button { min-height: 40px; white-space: nowrap; }
        .notification-actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .notification-actions form { margin: 0; }
        .notification-actions .coop-button-soft { min-height: 34px; padding: 0 11px; }
        .notification-empty { padding: 34px 20px; color: var(--muted); text-align: center; }
        @media (max-width: 900px) { .notifications-filter { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 640px) {
            .notification-summary { grid-template-columns: 1fr; }
            .notification-summary__item { border-right: 0; border-bottom: 1px solid var(--line); }
            .notification-summary__item:last-child { border-bottom: 0; }
            .notifications-panel__head { align-items: flex-start; flex-direction: column; }
            .notifications-filter { grid-template-columns: 1fr; padding: 16px; }
            .notifications-filter__actions { display: grid; grid-template-columns: 1fr 1fr; }
            .notifications-filter__actions .button, .notifications-filter__actions .link-button { display: inline-flex; align-items: center; justify-content: center; }
        }
    </style>
@endpush

@section('content')
    @include('components.coop-page-style')

    <div class="coop-wrap notification-page">
        <section class="coop-header">
            <div>
                <span class="coop-kicker">NOTIFIKASI</span>
                <h1>Notifikasi</h1>
                <p>Semak makluman permohonan, bayaran dan transaksi koperasi.</p>
            </div>
            @if ($summary['unread'] > 0)
                <form method="POST" action="{{ route('notifications.read') }}">
                    @csrf
                    <button class="button" type="submit">Tandakan Semua Dibaca</button>
                </form>
            @endif
        </section>

        <section class="notification-summary" aria-label="Ringkasan notifikasi">
            <div class="notification-summary__item">
                <span>Jumlah Notifikasi</span>
                <strong>{{ $summary['total'] }}</strong>
            </div>
            <div class="notification-summary__item notification-summary__item--unread">
                <span>Belum Dibaca</span>
                <strong>{{ $summary['unread'] }}</strong>
            </div>
            <div class="notification-summary__item">
                <span>Telah Dibaca</span>
                <strong>{{ $summary['read'] }}</strong>
            </div>
        </section>

        <section class="panel coop-panel">
            <div class="notifications-panel__head">
                <div>
                    <h2>Senarai Notifikasi</h2>
                    <p>{{ $notifications->total() }} rekod ditemui berdasarkan carian dan penapis semasa.</p>
                </div>
            </div>

            <form class="notifications-filter" method="GET" action="{{ route('notifications.index') }}">
                <label for="notification-search">
                    Cari Notifikasi
                    <input id="notification-search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tajuk atau mesej notifikasi">
                </label>
                <label for="notification-status">
                    Status
                    <select id="notification-status" name="status">
                        <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>Semua status</option>
                        <option value="unread" @selected(($filters['status'] ?? '') === 'unread')>Belum dibaca</option>
                        <option value="read" @selected(($filters['status'] ?? '') === 'read')>Telah dibaca</option>
                    </select>
                </label>
                <label for="notification-period">
                    Tempoh
                    <select id="notification-period" name="period">
                        <option value="all" @selected(($filters['period'] ?? 'all') === 'all')>Semua tarikh</option>
                        <option value="today" @selected(($filters['period'] ?? '') === 'today')>Hari ini</option>
                        <option value="week" @selected(($filters['period'] ?? '') === 'week')>Minggu ini</option>
                        <option value="month" @selected(($filters['period'] ?? '') === 'month')>Bulan ini</option>
                    </select>
                </label>
                <div class="notifications-filter__actions">
                    <button class="button" type="submit">Cari</button>
                    <a class="link-button secondary" href="{{ route('notifications.index') }}">Reset</a>
                </div>
            </form>

            <div class="coop-table-wrap">
                <table class="coop-table">
                    <thead>
                        <tr>
                            <th>Tajuk</th>
                            <th>Mesej</th>
                            <th>Status</th>
                            <th>Tarikh</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($notifications as $notification)
                            <tr>
                                <td><strong>{{ $notification->title }}</strong></td>
                                <td>{{ $notification->message ?? '-' }}</td>
                                <td><span class="coop-pill {{ $notification->read_at ? 'success' : 'warning' }}">{{ $notification->read_at ? 'Dibaca' : 'Baru' }}</span></td>
                                <td>{{ $notification->created_at?->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="notification-actions">
                                        @if ($notification->link)
                                            <a class="coop-button-soft" href="{{ $notification->link }}">Lihat</a>
                                        @endif
                                        @if (! $notification->read_at)
                                            <form method="POST" action="{{ route('notifications.read') }}">
                                                @csrf
                                                <input type="hidden" name="notification_id" value="{{ $notification->getKey() }}">
                                                <button class="coop-button-soft" type="submit">Tandakan dibaca</button>
                                            </form>
                                        @endif
                                        @if (! $notification->link && $notification->read_at)
                                            <span>-</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="notification-empty">Tiada notifikasi yang sepadan dengan carian ini.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{ $notifications->links() }}
    </div>
@endsection
