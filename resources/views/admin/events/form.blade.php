@extends('layouts.app')

@section('title', $event->exists ? 'Kemaskini Aktiviti' : 'Tambah Aktiviti')
@section('page-title', $event->exists ? 'Kemaskini Aktiviti' : 'Tambah Aktiviti')
@section('page-subtitle', 'Aktiviti ini akan digunakan dalam kalender Laman Utama Admin.')

@section('content')
    <form class="event-form" method="POST" action="{{ $event->exists ? route('admin.events.update', $event) : route('admin.events.store') }}">
        @csrf
        @if ($event->exists)
            @method('PUT')
        @endif

        <section class="event-head">
            <div>
                <span>KALENDAR</span>
                <h1>{{ $event->exists ? 'Kemaskini Aktiviti' : 'Tambah Aktiviti' }}</h1>
                <p>Isi maklumat aktiviti koperasi yang perlu dipaparkan kepada admin.</p>
            </div>
            <div class="event-actions">
                <a href="{{ route('admin.events.index') }}">Batal</a>
                <button type="submit">Simpan</button>
            </div>
        </section>

        <section class="event-panel event-fields">
            <label>Tajuk Aktiviti
                <input name="title" value="{{ old('title', $event->title) }}" required>
            </label>
            <label>Tarikh Aktiviti
                <input type="date" name="event_date" value="{{ old('event_date', $event->event_date?->format('Y-m-d')) }}" required>
            </label>
            <label>Kategori
                <input name="category" value="{{ old('category', $event->category ?? 'Umum') }}" required>
            </label>
            <label>Catatan
                <textarea name="description" rows="5">{{ old('description', $event->description) }}</textarea>
            </label>
            <label class="event-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $event->is_active ?? true))> Aktif</label>
        </section>
    </form>
@endsection

@include('admin.events.styles')
