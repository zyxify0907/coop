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

<div class="baju-edit-wrap">
    <section class="baju-edit-hero">
        <div>
            <span>URUS BAJU</span>
            <h1>Edit Baju</h1>
            <p>Kemaskini maklumat baju dan tambah stok size baharu dalam satu tempat.</p>
        </div>
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
                <div class="field field--visibility">
                    <label class="visibility-control" for="is_visible">
                        <input type="hidden" name="is_visible" value="0">
                        <input id="is_visible" name="is_visible" type="checkbox" value="1" @checked(old('is_visible', (bool) $item->is_visible))>
                        <span class="visibility-copy">
                            <strong>Paparkan kepada pelajar</strong>
                            <small>Baju ini akan muncul dalam halaman tempahan student.</small>
                        </span>
                        <span class="visibility-switch" aria-hidden="true"></span>
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
</div>
@endsection

@push('styles')
<style>
    .page-heading{display:none}
    .alert{padding:14px 16px;border-radius:10px;margin-bottom:16px}
    .alert.danger{background:var(--danger-soft);color:var(--primary-dark);border:1px solid var(--danger-soft)}
    .baju-edit-wrap{max-width:none;margin:0 auto;padding:0 0 48px}
    .baju-edit-hero{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:24px;padding:34px 36px;border:1px solid var(--line);border-left:5px solid var(--secondary);border-radius:22px;background:#fff;box-shadow:0 14px 34px rgba(15,23,42,.055);flex-wrap:wrap}
    .baju-edit-hero span{display:inline-flex;min-height:24px;align-items:center;border-radius:4px;background:var(--secondary-soft);color:var(--secondary);padding:0 10px;font-size:11px;font-weight:900;letter-spacing:.04em}
    .baju-edit-hero h1{margin:14px 0 0;font-size:38px;font-weight:900;color:var(--text);letter-spacing:0}
    .baju-edit-hero p{margin:10px 0 0;color:var(--muted-2);font-size:14px;font-weight:800}
    .hero-back{display:inline-flex;min-height:36px;align-items:center;justify-content:center;border:1px solid var(--line);border-radius:8px;background:#fff;color:var(--secondary);padding:0 16px;text-decoration:none;font-size:14px;font-weight:800;white-space:nowrap}
    .hero-back:hover{background:var(--secondary);color:#fff}
    .baju-edit-panel{overflow:hidden;margin-bottom:24px;border-radius:22px!important;background:#fff;border:1px solid var(--line)!important;box-shadow:0 14px 34px rgba(15,23,42,.055)!important}
    .edit-preview{display:grid;grid-template-columns:190px minmax(0,1fr);gap:22px;align-items:center;padding:24px 28px;border-bottom:1px solid var(--line);background:#fff}
    .edit-preview__image{height:160px;border:1px solid var(--line);border-radius:16px;background:var(--surface-soft);display:grid;place-items:center;overflow:hidden;color:#94a3b8;font-weight:800}
    .edit-preview__image img{width:100%;height:100%;object-fit:contain;padding:10px;background:#fff}
    .edit-preview h2{margin:0;color:var(--text);font-size:26px;font-weight:900}
    .edit-preview p{margin:8px 0 12px;color:var(--muted-2);font-weight:900}
    .size-pills{display:flex;flex-wrap:wrap;gap:8px}
    .size-pill{display:inline-flex;align-items:center;gap:7px;border:1px solid var(--line);background:#f8fafc;border-radius:10px;padding:7px 10px;color:#334155;font-size:13px;font-weight:900}
    .size-pill b{color:var(--text)}
    .size-pill.is-empty{opacity:.65}
    .edit-form{padding:28px}
    .form-grid{display:grid;grid-template-columns:minmax(260px,1.2fr) minmax(160px,.7fr) minmax(220px,.9fr) minmax(360px,1.55fr);gap:16px;align-items:end}
    .field{display:grid;gap:8px}
    .field--visibility{align-self:end}
    .visibility-control{position:relative;display:flex;align-items:center;justify-content:space-between;gap:18px;min-height:48px;padding:10px 12px 10px 14px;border:1px solid #dbe7fb;border-radius:12px;background:#fff;box-shadow:0 8px 18px rgba(15,23,42,.045);cursor:pointer;transition:border-color .2s,box-shadow .2s,background .2s}
    .visibility-control:hover{border-color:#bfdbfe;box-shadow:0 10px 24px rgba(30,64,175,.08)}
    .field .visibility-control input{position:absolute;width:1px;height:1px;opacity:0;pointer-events:none}
    .visibility-copy{display:grid;gap:5px;min-width:0}
    .visibility-copy strong{display:flex;align-items:center;gap:8px;font-size:12px;color:#061b34;font-weight:900;text-transform:uppercase;letter-spacing:.01em;line-height:1.2}
    .visibility-copy strong::before{content:"";width:8px;height:8px;flex:0 0 8px;border-radius:999px;background:#cbd5e1;box-shadow:0 0 0 3px rgba(148,163,184,.14)}
    .visibility-control:has(input:checked){border-color:#bfdbfe;background:#fbfdff}
    .visibility-control:has(input:checked) .visibility-copy strong::before{background:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.13)}
    .visibility-copy small{color:#64748b;font-size:11px;line-height:1.35;font-weight:800;text-transform:uppercase;letter-spacing:0}
    .visibility-switch{position:relative;width:42px;height:24px;flex:0 0 42px;border:1px solid #cbd5e1;border-radius:999px;background:#e2e8f0;transition:background .2s,border-color .2s}
    .visibility-switch::after{content:"";position:absolute;top:3px;left:3px;width:16px;height:16px;border-radius:50%;background:#fff;box-shadow:0 2px 5px rgba(15,23,42,.22);transition:left .2s}
    .visibility-control input:checked + .visibility-copy + .visibility-switch{background:#21457f;border-color:#21457f}
    .visibility-control input:checked + .visibility-copy + .visibility-switch::after{left:21px}
    .visibility-control input:focus-visible + .visibility-copy + .visibility-switch{outline:3px solid rgba(33,69,127,.22);outline-offset:3px}
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
    .edit-size-grid{display:grid;grid-template-columns:repeat(5,154px);gap:12px;justify-content:start;align-items:stretch}
    .edit-size-box{display:grid;grid-template-rows:auto 34px;gap:8px;width:154px;min-height:86px;border:1px solid #dbeafe;background:#f5f9ff;border-radius:12px;padding:11px 12px;min-width:0;box-shadow:0 8px 18px rgba(30,64,175,.045)}
    .edit-size-box.is-new{border-style:dashed;background:#f8fbff}
    .edit-size-box span{font-size:12px;font-weight:900;color:var(--secondary);text-align:center}
    .edit-size-box input{width:100%;min-width:0;box-sizing:border-box;min-height:34px!important;padding:5px 6px!important;text-align:center;font-size:13px;font-weight:900;background:#fff;border:1px solid #dbeafe!important;border-radius:8px!important;color:var(--text);appearance:textfield}
    .edit-size-box input::-webkit-outer-spin-button,
    .edit-size-box input::-webkit-inner-spin-button{margin:0}
    .edit-size-box small{text-align:center;color:var(--muted-2);font-size:11px;font-weight:800}
    .edit-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:24px;padding-top:22px;border-top:1px solid var(--line)}
    .edit-actions .button,.edit-actions .link-button{min-height:40px;border-radius:8px;font-size:14px;font-weight:900}
    @media (max-width:900px){
        .form-grid{grid-template-columns:1fr}
        .edit-size-grid{grid-template-columns:repeat(2,154px);justify-content:start}
        .edit-preview{grid-template-columns:1fr}
    }
    @media (max-width:640px){
        .baju-edit-hero{align-items:flex-start;flex-direction:column;padding:24px}
        .baju-edit-hero h1{font-size:30px}
        .edit-size-grid{grid-template-columns:minmax(0,1fr)}
        .edit-size-box{width:100%}
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
