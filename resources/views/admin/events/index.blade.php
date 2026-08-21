@extends('layouts.app')

@section('title', 'Kalender Aktiviti')
@section('page-title', 'Kalender Aktiviti')
@section('page-subtitle', 'Urus tarikh penting dan aktiviti koperasi.')

@section('content')
    <div class="event-admin">
        <section class="event-head">
            <div>
                <span>KALENDAR</span>
                <h1>Kalender Aktiviti Koperasi</h1>
                <p>Aktiviti aktif akan dipaparkan terus di Laman Utama Admin.</p>
            </div>
            <a href="{{ route('admin.events.create') }}">Tambah Aktiviti</a>
        </section>

        <section class="event-panel">
            <table class="event-table">
                <thead><tr><th>Tarikh</th><th>Aktiviti</th><th>Kategori</th><th>Status</th><th>Tindakan</th></tr></thead>
                <tbody>
                    @forelse ($events as $event)
                        <tr>
                            <td><strong>{{ $event->event_date?->format('d/m/Y') }}</strong></td>
                            <td><strong>{{ $event->title }}</strong><span>{{ $event->description ?? '-' }}</span></td>
                            <td>{{ $event->category }}</td>
                            <td><span class="event-status {{ $event->is_active ? 'active' : 'inactive' }}">{{ $event->is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                            <td>
                                <div class="event-actions">
                                    <a href="{{ route('admin.events.edit', $event) }}">Kemaskini</a>
                                    <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Padam aktiviti ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Padam</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="event-empty">Tiada aktiviti lagi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        @if ($events->hasPages())
            <div class="pagination">{{ $events->links() }}</div>
        @endif
    </div>
@endsection

@include('admin.events.styles')
