@extends('layouts.app')

@section('title', 'Edit Baju')
@section('page-title', 'Edit Baju')
@section('page-subtitle', 'Kemaskini gambar, harga dan stok mengikut size.')

@section('content')
@php
    $money = fn ($value) => 'RM '.number_format((float) $value, 2);
@endphp

@if ($errors->any())
    <div class="alert danger">{{ $errors->first() }}</div>
@endif

<section class="baju-edit-hero">
    <div>
        <span>URUS BAJU</span>
        <h1>Edit Baju</h1>
        <p>Kemaskini maklumat baju dan tambah stok size baharu dalam satu tempat.</p>
    </div>
    <a class="hero-back" href="{{ $backRoute }}">Kembali</a>
</section>

<section class="panel baju-edit-panel">
    <div class="edit-preview">
        <div class="edit-preview__image">
            @if ($item->image_path)
                <img src="{{ asset($item->image_path) }}" alt="{{ $item->nama_item }}">
            @else
                <div>Tiada Gambar</div>
            @endif
        </div>
        <div>
            <h2>{{ $item->nama_item }}</h2>
            <p>{{ $money($item->harga) }}</p>
            <div class="size-pills">
                @foreach ($sizeOptions as $size)
                    @php $sizeRow = $sizes->get($size); @endphp
                    <span class="size-pill {{ $sizeRow ? '' : 'is-empty' }}">{{ $size }} <b>{{ $sizeRow->stok_tertinggal ?? 0 }}</b></span>
                @endforeach
            </div>
        </div>
    </div>

    <form method="POST" action="{{ $updateRoute }}" enctype="multipart/form-data" class="edit-form">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="field">
                <label for="nama_item">Nama Baju</label>
                <input id="nama_item" name="nama_item" value="{{ old('nama_item', $item->nama_item) }}" required>
            </div>
            <div class="field">
                <label for="harga">Harga</label>
                <input id="harga" name="harga" type="number" min="0" step="0.01" value="{{ old('harga', $item->harga) }}" required>
            </div>
            <div class="field">
                <label for="image">Tukar Gambar</label>
                <label class="image-upload-button image-upload-button--form">
                    <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp">
                    <span data-upload-label>Pilih Gambar</span>
                </label>
            </div>
        </div>

        <div class="size-section">
            <div class="size-section__head">
                <div>
                    <h2>Stok Mengikut Size</h2>
                    <p>Isi stok pada size yang belum ada untuk tambah size baharu.</p>
                </div>
            </div>
            <div class="edit-size-grid">
                @foreach ($sizeOptions as $size)
                    @php $sizeRow = $sizes->get($size); @endphp
                    <label class="edit-size-box {{ $sizeRow ? '' : 'is-new' }}">
                        <span>{{ $size }}</span>
                        <input name="stok_saiz[{{ $size }}]" type="number" min="0" value="{{ old('stok_saiz.'.$size, $sizeRow->stok_tertinggal ?? 0) }}">
                        @unless ($sizeRow)
                            <small>Size baharu</small>
                        @endunless
                    </label>
                @endforeach
            </div>
        </div>

        <div class="edit-actions">
            <button class="button" type="submit">Simpan Perubahan</button>
            <a class="link-button secondary" href="{{ $backRoute }}">Batal</a>
        </div>
    </form>
</section>
@endsection

@push('styles')
<style>
    .page-heading{display:none}
    .alert{padding:14px 16px;border-radius:10px;margin-bottom:16px}
    .alert.danger{background:var(--danger-soft);color:var(--primary-dark);border:1px solid var(--danger-soft)}
    .baju-edit-hero{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin-bottom:24px;padding:32px 34px;border:1px solid var(--line);border-left:5px solid var(--secondary);border-radius:22px;background:#fff;box-shadow:0 14px 34px rgba(15,23,42,.055)}
    .baju-edit-hero span{display:inline-flex;min-height:30px;align-items:center;border-radius:999px;background:var(--secondary-soft);color:var(--secondary);padding:0 12px;font-size:12px;font-weight:900;letter-spacing:.04em}
    .baju-edit-hero h1{margin:14px 0 0;font-size:38px;font-weight:900;color:var(--text);letter-spacing:0}
    .baju-edit-hero p{margin:10px 0 0;color:var(--muted-2);font-weight:800}
    .hero-back{display:inline-flex;min-height:42px;align-items:center;justify-content:center;border-radius:12px;background:var(--secondary-soft);color:var(--secondary);padding:0 16px;text-decoration:none;font-weight:900;white-space:nowrap}
    .hero-back:hover{background:var(--secondary);color:#fff}
    .baju-edit-panel{overflow:hidden;border-radius:22px!important;background:#fff;border:1px solid var(--line)!important;box-shadow:0 14px 34px rgba(15,23,42,.055)!important}
    .edit-preview{display:grid;grid-template-columns:190px minmax(0,1fr);gap:22px;align-items:center;padding:24px 28px;border-bottom:1px solid var(--line)}
    .edit-preview__image{height:160px;border:1px solid var(--line);border-radius:16px;background:var(--surface-soft);display:grid;place-items:center;overflow:hidden;color:#94a3b8;font-weight:800}
    .edit-preview__image img{width:100%;height:100%;object-fit:contain;padding:10px;background:#fff}
    .edit-preview h2{margin:0;color:var(--text);font-size:26px;font-weight:900}
    .edit-preview p{margin:8px 0 12px;color:var(--muted-2);font-weight:900}
    .size-pills{display:flex;flex-wrap:wrap;gap:8px}
    .size-pill{display:inline-flex;align-items:center;gap:7px;border:1px solid var(--line);background:#f8fafc;border-radius:10px;padding:7px 10px;color:#334155;font-size:13px;font-weight:900}
    .size-pill b{color:var(--text)}
    .size-pill.is-empty{opacity:.65}
    .edit-form{padding:28px}
    .form-grid{display:grid;grid-template-columns:1.2fr .7fr .9fr;gap:16px}
    .field{display:grid;gap:8px}
    .field label{font-size:12px;font-weight:900;color:#334155;text-transform:uppercase;letter-spacing:.02em}
    .field input{width:100%;height:48px;border:1px solid var(--line);border-radius:10px;padding:0 14px;background:#fff;font-weight:800}
    .field input:focus{outline:0;border-color:var(--secondary);box-shadow:0 0 0 4px rgba(30,64,175,.10)}
    .image-upload-button{position:relative;display:inline-flex;align-items:center;justify-content:center;min-height:48px;border:1px dashed #bfdbfe;border-radius:10px;background:var(--secondary-soft);color:var(--secondary);padding:0 12px;cursor:pointer;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.02em;white-space:nowrap}
    .image-upload-button:hover{background:#dbeafe;border-color:#bfdbfe}
    .image-upload-button.is-selected{background:var(--success-soft);border-color:#bbf7d0;color:var(--success)}
    .image-upload-button input{position:absolute;width:1px;height:1px;opacity:0;pointer-events:none}
    .size-section{margin-top:24px;padding-top:24px;border-top:1px solid var(--line)}
    .size-section__head h2{margin:0;color:var(--text);font-size:20px;font-weight:900}
    .size-section__head p{margin:6px 0 16px;color:var(--muted-2);font-weight:700}
    .edit-size-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px}
    .edit-size-box{display:grid;gap:8px;border:1px solid #dbeafe;background:var(--secondary-soft);border-radius:14px;padding:12px;min-width:0}
    .edit-size-box.is-new{border-style:dashed;background:#f8fbff}
    .edit-size-box span{font-size:13px;font-weight:900;color:var(--secondary);text-align:center}
    .edit-size-box input{width:100%;min-width:0;box-sizing:border-box;min-height:42px!important;padding:6px 6px!important;text-align:center;font-size:14px;font-weight:900;background:#fff;border:1px solid #dbeafe!important;border-radius:10px!important;color:var(--text);appearance:textfield}
    .edit-size-box input::-webkit-outer-spin-button,
    .edit-size-box input::-webkit-inner-spin-button{margin:0}
    .edit-size-box small{text-align:center;color:var(--muted-2);font-size:11px;font-weight:800}
    .edit-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:24px;padding-top:22px;border-top:1px solid var(--line)}
    @media (max-width:900px){
        .form-grid{grid-template-columns:1fr}
        .edit-size-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
        .edit-preview{grid-template-columns:1fr}
    }
    @media (max-width:640px){
        .baju-edit-hero{align-items:flex-start;flex-direction:column;padding:24px}
        .baju-edit-hero h1{font-size:30px}
        .edit-size-grid{grid-template-columns:1fr}
        .edit-actions .button,.edit-actions .link-button{width:100%}
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('change', function (event) {
        const input = event.target;
        if (! input.matches('.image-upload-button input[type="file"]')) return;

        const button = input.closest('.image-upload-button');
        const label = button?.querySelector('[data-upload-label]');
        if (! button || ! label) return;

        const defaultText = label.dataset.defaultLabel || label.textContent;
        label.dataset.defaultLabel = defaultText;

        if (input.files && input.files.length > 0) {
            button.classList.add('is-selected');
            button.title = input.files[0].name;
            label.textContent = 'Gambar Dipilih';
        } else {
            button.classList.remove('is-selected');
            button.removeAttribute('title');
            label.textContent = defaultText;
        }
    });
</script>
@endpush
