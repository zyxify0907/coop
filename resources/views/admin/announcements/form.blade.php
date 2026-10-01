@extends('layouts.app')

@section('title', $announcement->exists ? 'Kemaskini Pengumuman' : 'Tambah Pengumuman')
@section('page-title', $announcement->exists ? 'Kemaskini Pengumuman' : 'Tambah Pengumuman')
@section('page-subtitle', 'Pengumuman diurus berasingan daripada ringkasan utama.')

@section('content')
    <form class="announcement-form" method="POST" action="{{ $announcement->exists ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}">
        @csrf
        @if ($announcement->exists)
            @method('PUT')
        @endif

        <section class="announcement-form__head">
            <div>
                <h1>{{ $announcement->exists ? 'Kemaskini Pengumuman' : 'Tambah Pengumuman' }}</h1>
                <p>Isi notis rasmi yang akan dipaparkan dalam halaman pengumuman sahaja.</p>
            </div>
        </section>

        <section class="announcement-form__panel">
            <div class="announcement-form__section-head">
                <h2>Maklumat Pengumuman</h2>
                <p>Lengkapkan tajuk, kandungan, sasaran dan tempoh paparan.</p>
            </div>
            <div class="announcement-form__field">
                <label for="title">Tajuk</label>
                <input id="title" name="title" value="{{ old('title', $announcement->title) }}" required>
                @error('title')<small>{{ $message }}</small>@enderror
            </div>
            <div class="announcement-form__field">
                <label for="body">Kandungan</label>
                <textarea id="body" name="body" rows="7" required>{{ old('body', $announcement->body) }}</textarea>
                @error('body')<small>{{ $message }}</small>@enderror
            </div>
            <div class="announcement-form__grid">
                <div class="announcement-form__field">
                    <label for="category">Kategori</label>
                    <select id="category" name="category" required>
                        @foreach (['Important', 'Reminder', 'Update', 'General'] as $category)
                            <option value="{{ $category }}" @selected(old('category', $announcement->category ?? 'General') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="announcement-form__field">
                    <label for="audience">Sasaran</label>
                    <select id="audience" name="audience" required>
                        <option value="all" @selected(old('audience', $announcement->audience) === 'all')>Semua</option>
                        <option value="ahli" @selected(old('audience', $announcement->audience) === 'ahli')>Student</option>
                        <option value="staff" @selected(old('audience', $announcement->audience) === 'staff')>Staff</option>
                        <option value="admin" @selected(old('audience', $announcement->audience) === 'admin')>Admin</option>
                    </select>
                </div>
                <div class="announcement-form__field">
                    <label for="starts_at">Tarikh Mula</label>
                    <input id="starts_at" type="date" name="starts_at" value="{{ old('starts_at', $announcement->starts_at?->format('Y-m-d')) }}">
                </div>
                <div class="announcement-form__field">
                    <label for="ends_at">Tarikh Tamat</label>
                    <input id="ends_at" type="date" name="ends_at" value="{{ old('ends_at', $announcement->ends_at?->format('Y-m-d')) }}">
                    @error('ends_at')<small>{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="announcement-form__footer">
                <div class="announcement-checks">
                    <label><input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned', $announcement->is_pinned))> <span>Pin di atas</span></label>
                    <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $announcement->is_active ?? true))> <span>Aktif</span></label>
                </div>
                <div class="announcement-form__actions">
                    <a href="{{ route('admin.announcements.index') }}">Batal</a>
                    <button type="submit">Simpan</button>
                </div>
            </div>
        </section>
    </form>
@endsection

@push('styles')
<style>
    .page-heading{display:none}
    .announcement-form{display:grid;gap:24px}
    .announcement-form__head{
        position:relative;
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:18px;
        padding:26px 28px;
        border:1px solid #D7E2EF;
        border-left:6px solid #0b1e36;
        border-radius:12px;
        background:#fff;
        box-shadow:0 12px 28px rgba(11,30,54,.08);
    }
    .announcement-form__head::before{content:"";position:absolute;top:30px;left:28px;width:42px;height:3px;border-radius:999px;background:#ED1C2E}
    .announcement-form__head h1{margin:20px 0 0;color:#071A33;font-size:26px;line-height:1.15;font-weight:700}
    .announcement-form__head p{margin:8px 0 0;color:#263A54;font-size:14px;line-height:1.45;font-weight:600}
    .announcement-form__footer{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;padding-top:4px}
    .announcement-form__actions{display:flex;gap:12px;justify-content:flex-end;flex-wrap:wrap;margin-left:auto}
    .announcement-form__actions a,.announcement-form__actions button{display:inline-flex;min-height:42px;align-items:center;justify-content:center;border:1px solid #C8D6E7;border-radius:6px;background:#fff;color:#102033;padding:0 18px;text-decoration:none;font-size:14px;font-weight:900;box-shadow:none}
    .announcement-form__actions button{border-color:#0B5ED7;background:#0B5ED7;color:#fff;cursor:pointer}
    .announcement-form__panel{display:grid;gap:18px;padding:24px 28px;border:1px solid #D7E2EF;border-radius:12px;background:#fff;box-shadow:0 8px 20px rgba(8,47,89,.035)}
    .announcement-form__section-head{padding-bottom:4px}
    .announcement-form__section-head h2{margin:0;color:#082F59;font-size:24px;line-height:1.2;font-weight:900}
    .announcement-form__section-head h2::before{content:"";display:block;width:34px;height:4px;margin-bottom:10px;border-radius:999px;background:#ED1C2E}
    .announcement-form__section-head p{margin:6px 0 0;color:#31517D;font-size:15px;font-weight:650}
    .announcement-form__field{display:grid;gap:8px}
    .announcement-form__field label{color:#082F59;font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:0}
    .announcement-form input:not([type="checkbox"]),.announcement-form select,.announcement-form textarea{width:100%;border:1px solid #C8D6E7;border-radius:6px;background:#fff;padding:12px 14px;color:#102033;font:inherit;font-weight:650;outline:0}
    .announcement-form input:not([type="checkbox"]):focus,.announcement-form select:focus,.announcement-form textarea:focus{border-color:#0B5ED7;box-shadow:0 0 0 3px rgba(11,94,215,.12)}
    .announcement-form textarea{resize:vertical}
    .announcement-form small{color:var(--danger);font-weight:800}
    .announcement-form__grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
    .announcement-checks{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
    .announcement-checks label{display:inline-flex;align-items:center;gap:10px;min-height:42px;border:1px solid #C8D6E7;border-radius:6px;background:#FBFDFF;padding:0 14px;color:#082F59;font-weight:900}
    .announcement-checks input[type="checkbox"]{appearance:auto;width:18px;height:18px;min-width:18px;margin:0;padding:0;border:1px solid #94A3B8;border-radius:4px;background:#fff;box-shadow:none}
    .content:has(> .announcement-form){background:#F4F7FB}
    .content > .announcement-form{max-width:none}
    @media (max-width:720px){
        .announcement-form__head{align-items:stretch;flex-direction:column}
        .announcement-form__grid{grid-template-columns:1fr}
        .announcement-form__footer{align-items:stretch;flex-direction:column}
        .announcement-form__actions{margin-left:0}
        .announcement-form__actions>*{width:100%}
        .announcement-checks label{width:100%}
    }
</style>
@endpush
