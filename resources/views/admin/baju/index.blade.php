@extends('layouts.app')

@section('title', 'Tempahan Baju')
@section('page-title', 'Tempahan Baju')
@section('page-subtitle', 'Staff boleh urus senarai baju, stok, harga, gambar dan status tempahan.')

@section('content')
@php
    $money = fn ($value) => 'RM '.number_format((float) $value, 2);
    $isAdminBaju = ($role ?? null) === 'admin';
    $isClothingStaff = request()->routeIs('clothing-staff.baju.*');
    $bajuRoutes = [
        'store' => $isAdminBaju ? 'admin.baju.store' : 'clothing-staff.baju.store',
        'edit' => $isAdminBaju ? 'admin.baju.edit' : 'clothing-staff.baju.edit',
        'update' => $isAdminBaju ? 'admin.baju.update' : 'clothing-staff.baju.update',
        'destroy' => $isAdminBaju ? 'admin.baju.destroy' : 'clothing-staff.baju.destroy',
    ];
@endphp

@if (session('status'))
    <div class="alert success">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="alert danger">{{ $errors->first() }}</div>
@endif

<section class="baju-hero">
    <div>
        <h1>Tempahan Baju</h1>
        <p>Staff boleh urus senarai baju, stok, harga, gambar dan status tempahan.</p>
    </div>
    @if ($isClothingStaff)
        <a class="clothing-dashboard-backlink" href="{{ route('clothing-staff.dashboard.baju') }}">Dashboard Tempahan</a>
    @endif
</section>

<section class="panel baju-panel" style="margin-bottom:24px">
    <div class="panel-head">
        <div>
            <h2>Tambah Baju</h2>
            <span>Isi satu baju, satu gambar, kemudian tetapkan stok ikut size.</span>
        </div>
    </div>

    <form method="POST" action="{{ route($bajuRoutes['store']) }}" enctype="multipart/form-data" class="form-grid form-grid--baju">
        @csrf
        <div class="field">
            <label for="nama_item">Nama Baju</label>
            <input id="nama_item" name="nama_item" value="{{ old('nama_item') }}" required>
        </div>
        <div class="field">
            <label for="harga">Harga</label>
            <input id="harga" name="harga" type="number" min="0" step="0.01" value="{{ old('harga', 0) }}" required>
        </div>
        <div class="field">
            <label for="image">Gambar Baju</label>
            <label class="image-upload-button image-upload-button--form">
                <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp">
                <span data-upload-label>Pilih Gambar</span>
            </label>
        </div>
        <div class="field field--sizes">
            <label>Stok Mengikut Size</label>
            <div class="size-stock-grid">
                @foreach ($sizeOptions as $size)
                    <label class="size-stock-input">
                        <span>{{ $size }}</span>
                        <input name="stok_saiz[{{ $size }}]" type="number" min="0" value="{{ old('stok_saiz.'.$size, 0) }}">
                    </label>
                @endforeach
            </div>
        </div>
        <div class="field field-actions">
            <button class="button" type="submit">Tambah Baju</button>
        </div>
    </form>
</section>

<section class="panel baju-panel" style="margin-bottom:24px">
    <div class="panel-head">
        <div>
            <h2>Senarai Baju</h2>
            <span>{{ $items->count() }} jenis baju dalam rekod staff.</span>
        </div>
    </div>

    <div class="baju-grid">
        @forelse ($items as $item)
            <article class="baju-card">
                <div class="baju-card__image">
                    @if ($item->image_path)
                        <img src="{{ asset($item->image_path) }}" alt="{{ $item->nama_item }}" loading="lazy" onerror="this.classList.add('is-hidden'); this.nextElementSibling.classList.remove('is-hidden');">
                        <div class="baju-card__placeholder is-hidden">Tiada Gambar</div>
                    @else
                        <div class="baju-card__placeholder">Tiada Gambar</div>
                    @endif
                </div>
                <div class="baju-card__body">
                    <strong>{{ $item->nama_item }}</strong>
                    <div class="size-pills">
                        @foreach ($item->sizes as $size)
                            <span class="size-pill">{{ $size->saiz ?? '-' }} <b>{{ $size->stok_tertinggal }}</b></span>
                        @endforeach
                    </div>
                    <div class="baju-meta-row">
                        <div class="baju-meta-text">
                            <span>Jumlah stok: {{ $item->total_stock }}</span>
                            <span>Harga: {{ $money($item->harga) }}</span>
                        </div>
                    </div>
                </div>
                <div class="baju-card__buttons">
                    <a class="button" href="{{ route($bajuRoutes['edit'], $item->id_item) }}">Edit</a>
                    <form method="POST" action="{{ route($bajuRoutes['destroy'], $item->id_item) }}" onsubmit="return confirm('Padam baju ini?');">
                        @csrf
                        @method('DELETE')
                        <button class="link-button danger" type="submit">Delete</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="empty-state">Tiada baju direkodkan lagi.</div>
        @endforelse
    </div>

</section>

@endsection

@push('styles')
<style>
    .page-heading{display:none}
    .alert{padding:14px 16px;border-radius:10px;margin-bottom:16px}
    .alert.success{background:#ecfdf5;color:#166534;border:1px solid #bbf7d0}
    .alert.danger{background:var(--danger-soft);color:var(--primary-dark);border:1px solid var(--danger-soft)}
    .baju-hero{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin-bottom:24px;padding:34px 36px;border:1px solid var(--line);border-left:5px solid var(--secondary);border-radius:22px;background:#fff;box-shadow:0 14px 34px rgba(15,23,42,.055)}
    .baju-hero h1{margin:0;color:var(--text);font-size:38px;line-height:1.15;font-weight:900;letter-spacing:0}
    .baju-hero p{margin:14px 0 0;color:var(--muted-2);font-size:16px;font-weight:800}
    .clothing-dashboard-backlink{display:inline-flex;align-items:center;justify-content:center;min-height:46px;padding:0 20px;border:1px solid #D7E3F5;border-radius:10px;background:#fff;color:var(--secondary);font-size:15px;font-weight:900;text-decoration:none;white-space:nowrap}
    .clothing-dashboard-backlink::before{content:'\2190';margin-right:8px;color:var(--primary);font-weight:900}
    .clothing-dashboard-backlink:hover{border-color:#AFC7EA;background:#F8FBFF}
    .baju-panel{border-radius:10px!important;overflow:hidden}
    .baju-panel:hover,
    .baju-card:hover{border-color:var(--line)!important;box-shadow:var(--shadow-sm)!important;transform:none!important}
    .panel-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;padding:18px 20px;border-bottom:1px solid var(--line);background:#fff}
    .panel-head h2{margin:0;font-size:18px}
    .panel-head span{margin-top:6px;display:block;color:#64748b;font-size:13px}
    .form-grid--baju{display:grid;grid-template-columns:minmax(180px,1.05fr) minmax(96px,.45fr) minmax(140px,.6fr) minmax(430px,1.6fr) 132px;gap:14px;padding:18px;align-items:end}
    .field{display:grid;gap:8px}
    .field label{min-height:16px;font-size:12px;font-weight:900;color:#334155;text-transform:uppercase;letter-spacing:.02em}
    .field input,.field select{width:100%;height:48px;border:1px solid var(--line);border-radius:10px;padding:0 14px;background:#fff;font-weight:800}
    .field input[type="file"]{height:48px;padding:7px 10px;background:#f8fbff;border-style:dashed;border-color:#bfdbfe;color:#475569;font-weight:700}
    .field input[type="file"]::file-selector-button{height:32px;margin-right:10px;border:1px solid var(--secondary-soft);border-radius:9px;background:var(--secondary-soft);color:var(--secondary);padding:0 12px;font-weight:900;cursor:pointer}
    .field input[type="file"]::file-selector-button:hover{background:#dbeafe}
    .field--sizes{align-self:stretch}
    .field-actions{align-self:end}
    .field-actions .button{width:100%;height:48px;min-height:48px;border-radius:10px;white-space:nowrap;padding:0 12px}
    .size-stock-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:8px}
    .size-stock-input{display:grid;grid-template-columns:22px minmax(56px,1fr);align-items:center;gap:6px;border:1px solid #dbeafe;border-radius:12px;padding:6px;background:var(--secondary-soft);min-width:0;height:48px}
    .size-stock-input span{font-size:11px;font-weight:900;color:var(--secondary);text-align:center}
    .size-stock-input input{width:100%;min-width:56px;box-sizing:border-box;padding:0 5px!important;height:34px!important;min-height:34px!important;text-align:center;font-size:13px;font-weight:900;border-color:#dbeafe!important;border-radius:10px!important;appearance:textfield}
    .size-stock-input input::-webkit-outer-spin-button,
    .size-stock-input input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
    .size-stock-input input:focus{outline:0;border-color:var(--secondary)!important;box-shadow:0 0 0 3px rgba(30,64,175,.10)}
    .baju-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;padding:20px;align-items:stretch}
    .baju-card{border:1px solid var(--line);border-radius:10px;overflow:hidden;background:#fff;display:flex;flex-direction:column;min-width:0;height:100%;box-shadow:var(--shadow-sm)}
    .baju-card__image{width:100%;aspect-ratio:4 / 5;max-height:360px;background:var(--surface-soft);display:flex;align-items:center;justify-content:center;overflow:hidden}
    .baju-card__image img{display:block;width:100%;height:100%;object-fit:cover;background:#fff;flex:0 0 100%}
    .baju-card__image .is-hidden{display:none!important}
    .baju-card__placeholder{width:100%;height:100%;display:grid;place-items:center;color:#94a3b8;font-weight:700;font-size:12px}
    .baju-card__body{display:flex;flex-direction:column;gap:7px;padding:16px 16px 8px;flex:1}
    .baju-card__body strong{font-size:14px;color:#0f172a}
    .baju-card__body span{font-size:12px;color:#475569}
    .baju-meta-row{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-top:auto;padding-top:6px}
    .baju-meta-text{display:grid;gap:5px;min-width:0}
    .size-pills{display:flex;gap:6px;flex-wrap:wrap;margin:2px 0;min-width:0}
    .size-pill{display:inline-flex;align-items:center;gap:6px;border:1px solid var(--line);background:#f8fafc;border-radius:8px;padding:5px 8px;color:#334155!important;font-weight:800}
    .size-pill b{color:#0f172a}
    .baju-card__edit{display:grid;gap:9px;padding:10px 14px;min-width:0}
    .baju-card__edit input,.baju-card__edit select{width:100%;border:1px solid var(--line);border-radius:8px;padding:7px 10px;font-size:13px;min-height:36px}
    .edit-size-grid{display:flex;gap:8px;flex-wrap:wrap}
    .edit-size-box{display:grid;gap:7px;border:1px solid #dbeafe;background:var(--secondary-soft);border-radius:12px;padding:9px 8px;min-width:82px;flex:0 0 auto}
    .edit-size-box span{font-size:11px;font-weight:900;color:var(--secondary);text-align:center;letter-spacing:.02em}
    .edit-size-box input{width:100%;min-width:54px;box-sizing:border-box;min-height:38px!important;padding:6px 5px!important;text-align:center;font-size:13px;font-weight:900;background:#fff;border-color:#dbeafe!important;border-radius:10px!important;color:var(--text);box-shadow:inset 0 1px 2px rgba(15,23,42,.04);appearance:textfield}
    .edit-size-box input:focus{outline:0;border-color:var(--secondary)!important;box-shadow:0 0 0 3px rgba(30,64,175,.10)}
    .edit-size-box input::-webkit-outer-spin-button,
    .edit-size-box input::-webkit-inner-spin-button{margin:0}
    .file-field{display:grid;grid-template-columns:1fr;gap:8px;border:1px dashed #bfdbfe;border-radius:12px;padding:11px 12px;background:#f8fbff}
    .file-field--compact{gap:6px;padding:9px 10px;min-width:0}
    .file-field span{font-size:11px;font-weight:900;color:#475569;text-transform:uppercase;letter-spacing:.02em}
    .file-field input{border:0!important;background:transparent!important;padding:0!important;min-height:auto!important;font-size:11px;color:#475569;max-width:100%}
    .file-field input::file-selector-button{margin-right:7px;border:1px solid var(--secondary-soft);border-radius:9px;background:var(--secondary-soft);color:var(--secondary);padding:7px 9px;font-weight:900;cursor:pointer}
    .file-field input::file-selector-button:hover{background:#dbeafe}
    .image-upload-button{
        position:relative;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:36px;
        flex:0 0 auto;
        border:1px solid #dbeafe;
        border-radius:10px;
        background:var(--secondary-soft);
        color:var(--secondary);
        padding:0 12px;
        cursor:pointer;
        font-size:11px;
        font-weight:900;
        text-transform:uppercase;
        letter-spacing:.02em;
        white-space:nowrap;
    }
    .image-upload-button:hover{background:#dbeafe;border-color:#bfdbfe}
    .image-upload-button.is-selected{background:var(--success-soft);border-color:#bbf7d0;color:var(--success)}
    .image-upload-button input{position:absolute;width:1px;height:1px;opacity:0;pointer-events:none}
    .image-upload-button--form{height:48px;width:100%;border-style:dashed;font-size:12px}
    .baju-card__buttons{display:flex;gap:8px;align-items:center;flex-wrap:wrap;padding:8px 16px 16px}
    .baju-card__buttons .button,
    .baju-card__buttons .link-button{min-height:34px;padding:0 12px;font-size:12px}
    .table-wrap{overflow:auto}
    .sub{margin-top:4px;font-size:12px;color:#64748b}
    .order-actions{display:grid;gap:8px}
    .order-actions select,.order-actions input{width:100%;border:1px solid var(--line);border-radius:8px;padding:8px 10px}
    .pagination-wrap{padding:0 18px 18px}
    .empty-state{padding:28px;text-align:center;color:#64748b;font-weight:700}
    @media (max-width: 1120px){
        .form-grid--baju{grid-template-columns:repeat(2,minmax(0,1fr))}
        .field--sizes{grid-column:1 / -1}
        .field-actions{grid-column:1 / -1}
        .field-actions .button{width:100%}
        .size-stock-grid{grid-template-columns:repeat(5,minmax(0,1fr))}
    }
    @media (max-width: 1100px){
        .baju-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    }
    @media (max-width: 640px){
        .baju-hero{align-items:flex-start;flex-direction:column;padding:24px}
        .baju-hero h1{font-size:30px}
        .form-grid--baju{grid-template-columns:1fr}
        .baju-grid{grid-template-columns:1fr}
        .baju-meta-row{align-items:flex-start;flex-direction:column}
        .size-stock-grid{grid-template-columns:repeat(5,minmax(72px,1fr));overflow-x:auto;padding-bottom:4px}
        .size-stock-input{min-width:72px}
        .baju-card__image{max-height:none}
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('change', function (event) {
        const input = event.target;

        if (! input.matches('.image-upload-button input[type="file"]')) {
            return;
        }

        const button = input.closest('.image-upload-button');
        const label = button?.querySelector('[data-upload-label]');

        if (! button || ! label) {
            return;
        }

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
