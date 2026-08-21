@extends('layouts.app')

@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')
@section('page-subtitle', 'Makluman sistem koperasi.')

@section('content')
    @include('components.coop-page-style')

    <div class="coop-wrap">
        <section class="coop-header">
            <div>
                <span class="coop-kicker">NOTIFIKASI</span>
                <h1>Notifikasi</h1>
                <p>Semak makluman permohonan, bayaran dan transaksi koperasi.</p>
            </div>
            <form method="POST" action="{{ route('koperasi.notifications.read') }}">
                @csrf
                <button class="button" type="submit">Tanda Semua Dibaca</button>
            </form>
        </section>

        <section class="panel coop-panel">
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
                                    @if ($notification->link)
                                        <a class="coop-button-soft" href="{{ $notification->link }}">Buka</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5">Tiada notifikasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{ $notifications->links() }}
    </div>
@endsection
