@extends('layouts.app')

@section('title', $announcement->exists ? 'Edit Announcement' : 'Tambah Announcement')
@section('page-title', $announcement->exists ? 'Edit Announcement' : 'Tambah Announcement')
@section('page-subtitle', 'Announcement diurus berasingan daripada dashboard.')

@section('content')
    <form class="announcement-form" method="POST" action="{{ $announcement->exists ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}">
        @csrf
        @if ($announcement->exists)
            @method('PUT')
        @endif

        <section class="announcement-form__head">
            <div>
                <span>ANNOUNCEMENT</span>
                <h1>{{ $announcement->exists ? 'Edit Announcement' : 'Tambah Announcement' }}</h1>
                <p>Isi notis rasmi yang akan dipaparkan dalam page Announcement sahaja.</p>
            </div>
            <div class="announcement-form__actions">
                <a href="{{ route('admin.announcements.index') }}">Batal</a>
                <button type="submit">Simpan</button>
            </div>
        </section>

        <section class="announcement-form__panel">
            <label>
                Tajuk
                <input name="title" value="{{ old('title', $announcement->title) }}" required>
                @error('title')<small>{{ $message }}</small>@enderror
            </label>
            <label>
                Kandungan
                <textarea name="body" rows="7" required>{{ old('body', $announcement->body) }}</textarea>
                @error('body')<small>{{ $message }}</small>@enderror
            </label>
            <div class="announcement-form__grid">
                <label>
                    Kategori
                    <select name="category" required>
                        @foreach (['Important', 'Reminder', 'Update', 'General'] as $category)
                            <option value="{{ $category }}" @selected(old('category', $announcement->category ?? 'General') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Sasaran
                    <select name="audience" required>
                        <option value="all" @selected(old('audience', $announcement->audience) === 'all')>Semua</option>
                        <option value="ahli" @selected(old('audience', $announcement->audience) === 'ahli')>Student</option>
                        <option value="staff" @selected(old('audience', $announcement->audience) === 'staff')>Staff</option>
                        <option value="admin" @selected(old('audience', $announcement->audience) === 'admin')>Admin</option>
                    </select>
                </label>
                <label>
                    Tarikh Mula
                    <input type="date" name="starts_at" value="{{ old('starts_at', $announcement->starts_at?->format('Y-m-d')) }}">
                </label>
                <label>
                    Tarikh Tamat
                    <input type="date" name="ends_at" value="{{ old('ends_at', $announcement->ends_at?->format('Y-m-d')) }}">
                    @error('ends_at')<small>{{ $message }}</small>@enderror
                </label>
            </div>
            <div class="announcement-checks">
                <label><input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned', $announcement->is_pinned))> Pin di atas</label>
                <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $announcement->is_active ?? true))> Aktif</label>
            </div>
        </section>
    </form>
@endsection

@push('styles')
<style>
    .announcement-form{display:grid;gap:18px}
    .announcement-form__head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;padding:28px 30px;border:1px solid var(--line);border-radius:18px;background:#fff;box-shadow:0 14px 34px rgba(15,23,42,.055)}
    .announcement-form__head span{color:var(--secondary);font-size:12px;font-weight:900;letter-spacing:.08em}
    .announcement-form__head h1{margin:6px 0 0;color:var(--text);font-size:30px;line-height:1.15;font-weight:900}
    .announcement-form__head p{margin:8px 0 0;color:var(--muted);font-weight:700}
    .announcement-form__actions{display:flex;gap:10px;flex-wrap:wrap}
    .announcement-form__actions a,.announcement-form__actions button{display:inline-flex;align-items:center;justify-content:center;min-height:44px;border:1px solid #BFD7FF;border-radius:8px;background:#fff;color:var(--secondary);padding:0 18px;text-decoration:none;font-weight:900}
    .announcement-form__actions button{border-color:var(--secondary);background:var(--secondary);color:#fff}
    .announcement-form__panel{display:grid;gap:16px;padding:24px;border:1px solid var(--line);border-radius:16px;background:#fff}
    .announcement-form label{display:grid;gap:8px;color:var(--ink);font-weight:900}
    .announcement-form input,.announcement-form select,.announcement-form textarea{width:100%;border:1px solid var(--line-strong);border-radius:8px;background:#fff;padding:12px 14px;color:var(--text);font-weight:700}
    .announcement-form textarea{resize:vertical}
    .announcement-form small{color:var(--danger);font-weight:800}
    .announcement-form__grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
    .announcement-checks{display:flex;gap:18px;flex-wrap:wrap}
    .announcement-checks label{display:flex;align-items:center;gap:10px}
    .announcement-checks input{width:18px;height:18px}
    @media (max-width:720px){.announcement-form__head{align-items:stretch;flex-direction:column}.announcement-form__grid{grid-template-columns:1fr}.announcement-form__actions>*{width:100%}}
</style>
@endpush
