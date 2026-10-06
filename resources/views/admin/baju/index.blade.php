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
    $bajuDashboardRoute = $isAdminBaju ? 'admin.dashboard.baju' : 'clothing-staff.dashboard.baju';
@endphp

@if (session('status'))
    <div class="alert success">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="alert danger">{{ $errors->first() }}</div>
@endif

<div class="admin-order-page">
<section class="admin-baju-hero">
    <div>
        <h2>Tempahan Baju</h2>
        <p>Staff boleh urus senarai baju, stok, harga, gambar dan status tempahan.</p>
    </div>
    <div class="admin-baju-hero__meta">
        <a class="clothing-dashboard-backlink" href="{{ route($bajuDashboardRoute) }}">Dashboard Tempahan</a>
    </div>
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
        <div class="field">
            <label for="size_chart">Carta Saiz</label>
            <label class="image-upload-button image-upload-button--form">
                <input id="size_chart" name="size_chart" type="file" accept=".jpg,.jpeg,.png,.webp">
                <span data-upload-label>Muat Naik Carta Saiz</span>
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
                    <div class="baju-card__title">
                        <strong>{{ $item->nama_item }}</strong>
                        <span class="baju-card__price">{{ $money($item->harga) }}</span>
                    </div>
                    <div class="size-pills">
                        @foreach ($item->sizes as $size)
                            <span class="size-pill">
                                <span class="size-pill__label">{{ $size->saiz ?? '-' }}</span>
                                <b aria-label="Stok {{ $size->saiz ?? '-' }}: {{ $size->stok_tertinggal }}">{{ $size->stok_tertinggal }}</b>
                            </span>
                        @endforeach
                    </div>
                    <div class="baju-meta-row">
                        <div class="baju-meta-text">
                            <span>Jumlah stok: {{ $item->total_stock }}</span>
                            <span>{{ $item->is_visible ? 'Dipaparkan kepada pelajar' : 'Disorok daripada pelajar' }}</span>
                            @if ($item->size_chart_path)
                                <button
                                    class="size-chart-view"
                                    type="button"
                                    data-size-chart-open
                                    data-size-chart-src="{{ asset($item->size_chart_path) }}"
                                    data-size-chart-title="Carta Saiz {{ $item->nama_item }}"
                                >
                                    Lihat gambar carta saiz
                                </button>
                            @else
                                <span>Carta saiz belum dimuat naik</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="baju-card__buttons">
                    <a class="button" href="{{ route($bajuRoutes['edit'], $item->id_item) }}">Kemaskini</a>
                    <form method="POST" action="{{ route($bajuRoutes['destroy'], $item->id_item) }}" onsubmit="return confirm('Padam baju ini?');">
                        @csrf
                        @method('DELETE')
                        <button class="link-button danger" type="submit">Padam</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="empty-state">Tiada baju direkodkan lagi.</div>
        @endforelse
    </div>

</section>

<dialog class="size-chart-dialog" data-size-chart-dialog aria-labelledby="sizeChartTitle">
    <section class="size-chart-panel">
        <header class="size-chart-panel__head">
            <div>
                <h2 id="sizeChartTitle">Carta Saiz</h2>
                <p>Semak gambar carta saiz baju.</p>
            </div>
            <button class="size-chart-dialog__close" type="button" data-size-chart-close aria-label="Tutup carta saiz">&times;</button>
        </header>
        <div class="size-chart-panel__image">
            <img data-size-chart-image src="" alt="Carta saiz baju">
        </div>
    </section>
</dialog>
</div>

@endsection

@push('styles')
<style>
    .page-heading{display:none}
    .alert{padding:14px 16px;border-radius:10px;margin-bottom:16px}
    .alert.success{background:#ecfdf5;color:#166534;border:1px solid #bbf7d0}
    .alert.danger{background:var(--danger-soft);color:var(--primary-dark);border:1px solid var(--danger-soft)}
    .admin-order-page{
        --order-navy:#003B75;
        --order-red:#ED1C2E;
        --order-border:#C9D8EA;
        width:min(100%,1660px);
        margin:0 auto;
        display:grid;
        gap:18px;
    }
    .admin-order-page .admin-baju-hero{
        display:flex;
        justify-content:space-between;
        align-items:flex-end;
        gap:18px;
        min-height:150px;
        padding:28px 32px;
        border:1px solid var(--order-border);
        border-left:8px solid #082F59;
        border-radius:14px;
        background:#fff;
        box-shadow:0 18px 42px rgba(15,23,42,.06);
    }
    .admin-order-page .admin-baju-hero__eyebrow{
        display:inline-flex;
        align-items:center;
        gap:12px;
        min-height:28px;
        margin-bottom:6px;
        padding:0 12px;
        border-radius:5px;
        background:#EFF6FF;
        color:#0B4DA2;
        font-size:11px;
        font-weight:900;
        letter-spacing:.08em;
        text-transform:uppercase;
    }
    .admin-order-page .admin-baju-hero__eyebrow i{
        display:block;
        width:30px;
        height:4px;
        border-radius:999px;
        background:var(--order-red);
    }
    .admin-order-page .admin-baju-hero h2{
        margin:0;
        color:#061B34;
        font-size:32px;
        line-height:1.12;
        font-weight:900;
        letter-spacing:0;
    }
    .admin-order-page .admin-baju-hero p{
        margin:8px 0 0;
        color:var(--order-navy);
        font-size:13px;
        font-weight:800;
    }
    .admin-order-page .admin-baju-hero__meta{
        display:grid;
        justify-items:end;
        gap:10px;
        color:#284A75;
        font-size:16px;
        font-weight:900;
        text-align:right;
        white-space:nowrap;
    }
    .admin-order-page .admin-baju-hero__meta strong{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:38px;
        padding:0 17px;
        border-radius:999px;
        background:#DDF7E8;
        color:#061B34;
        font-size:15px;
        font-weight:900;
    }
    .admin-order-page .clothing-dashboard-backlink{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:38px;
        padding:0 17px;
        border:1px solid #8DB4F7;
        border-radius:7px;
        background:#fff;
        color:var(--order-navy);
        font-size:12px;
        font-weight:900;
        text-decoration:none;
        white-space:nowrap;
    }
    .admin-order-page .clothing-dashboard-backlink::before{content:'\2190';margin-right:8px;color:var(--order-red);font-weight:900}
    .admin-order-page .clothing-dashboard-backlink:hover{border-color:#5D93F1;background:#F5F9FF}
    .admin-order-page .baju-panel{
        overflow:hidden;
        margin-bottom:0!important;
        padding:22px!important;
        border:1px solid var(--order-border)!important;
        border-radius:8px!important;
        background:#fff!important;
        box-shadow:none!important;
    }
    .admin-order-page .baju-panel:hover,
    .admin-order-page .baju-card:hover{border-color:var(--order-border)!important;box-shadow:none!important;transform:none!important}
    .admin-order-page .panel-head{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:12px;
        padding:0 0 16px;
        margin:0 0 18px;
        border-bottom:0;
        background:transparent;
    }
    .admin-order-page .panel-head h2{
        position:relative;
        margin:0;
        padding-left:26px;
        color:#061B34;
        font-size:22px;
        line-height:1.2;
        font-weight:900;
        letter-spacing:0;
    }
    .admin-order-page .panel-head h2::before{
        content:'';
        position:absolute;
        left:0;
        top:.62em;
        width:18px;
        height:3px;
        border-radius:999px;
        background:var(--order-red);
    }
    .admin-order-page .panel-head span{margin-top:6px;display:block;color:#4D6484;font-size:12px;font-weight:800}
    .admin-order-page .form-grid--baju{
        display:grid;
        grid-template-columns:minmax(180px,1.1fr) minmax(110px,.45fr) minmax(160px,.65fr) minmax(160px,.8fr) minmax(420px,1.6fr) 142px;
        gap:14px;
        padding:0;
        align-items:end;
    }
    .admin-order-page .form-grid--baju > *{min-width:0}
    .admin-order-page .field{display:grid;gap:7px}
    .admin-order-page .field label{min-height:16px;color:var(--order-navy);font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.02em}
    .admin-order-page .field input,
    .admin-order-page .field select{
        width:100%;
        height:40px;
        border:1px solid #BFD0E6;
        border-radius:6px;
        padding:0 12px;
        background:#fff;
        color:#061B34;
        font-weight:800;
    }
    .admin-order-page .field--sizes{align-self:stretch}
    .admin-order-page .field-actions{align-self:end}
    .admin-order-page .field-actions .button{
        width:100%;
        height:40px;
        min-height:40px!important;
        border-color:var(--order-navy)!important;
        border-radius:6px!important;
        background:var(--order-navy)!important;
        color:#fff!important;
        font-size:12px!important;
        font-weight:900!important;
        white-space:nowrap;
        padding:0 12px!important;
    }
    .admin-order-page .size-stock-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:8px}
    .admin-order-page .size-stock-input{
        display:grid;
        grid-template-columns:22px minmax(42px,1fr);
        align-items:center;
        gap:6px;
        min-width:0;
        height:40px;
        padding:4px 6px;
        border:1px solid #BFD0E6;
        border-radius:6px;
        background:#F5F9FF;
    }
    .admin-order-page .size-stock-input span{font-size:11px;font-weight:900;color:var(--order-navy);text-align:center}
    .admin-order-page .size-stock-input input{width:100%;min-width:42px;box-sizing:border-box;padding:0 5px!important;height:30px!important;min-height:30px!important;text-align:center;font-size:12px;font-weight:900;border-color:#BFD0E6!important;border-radius:5px!important;appearance:textfield}
    .size-stock-input input::-webkit-outer-spin-button,
    .size-stock-input input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
    .admin-order-page .size-stock-input input:focus{outline:0;border-color:#5D93F1!important;box-shadow:0 0 0 3px rgba(36,83,166,.10)}
    .admin-order-page .baju-grid{
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        gap:18px;
        padding:0;
        align-items:stretch;
    }
    .admin-order-page .baju-card{
        display:flex;
        flex-direction:column;
        min-width:0;
        height:100%;
        overflow:hidden;
        border:1px solid var(--order-border);
        border-radius:8px;
        background:#fff;
        box-shadow:none;
    }
    .admin-order-page .baju-card__image{
        width:100%;
        aspect-ratio:4 / 3;
        min-height:230px;
        max-height:270px;
        background:#F4F7FB;
        display:flex;
        align-items:center;
        justify-content:center;
        overflow:hidden;
    }
    .admin-order-page .baju-card__image img{display:block;width:100%;height:100%;object-fit:contain;background:#fff;flex:0 0 100%}
    .admin-order-page .baju-card__image .is-hidden{display:none!important}
    .admin-order-page .baju-card__placeholder{width:100%;height:100%;display:grid;place-items:center;color:#7589A8;font-weight:700;font-size:13px}
    .admin-order-page .baju-card__body{display:flex;flex-direction:column;gap:10px;padding:12px 14px 14px;flex:1}
    .admin-order-page .baju-card__title{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;min-height:38px}
    .admin-order-page .baju-card__body strong{color:var(--order-navy);font-size:14px;line-height:1.35;font-weight:900}
    .admin-order-page .baju-card__price{flex:0 0 auto;color:var(--order-red)!important;font-size:14px!important;font-weight:900!important;white-space:nowrap}
    .admin-order-page .baju-card__body span{font-size:12px;color:#3B5374}
    .admin-order-page .baju-meta-row{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;margin-top:0;padding-top:0}
    .admin-order-page .baju-meta-text{display:grid;gap:4px;min-width:0}
    .admin-order-page .baju-meta-text span{color:#4D6484;font-size:12px;font-weight:800}
    .admin-order-page .size-chart-view{justify-self:start;padding:0;border:0;background:transparent;color:#0B5ED7;font:inherit;font-size:12px;font-weight:900;line-height:1.35;text-align:left;cursor:pointer}
    .admin-order-page .size-chart-view::before{content:'↗';margin-right:6px;color:var(--order-red);font-size:14px}
    .admin-order-page .size-chart-view:hover,.admin-order-page .size-chart-view:focus-visible{color:var(--order-red);text-decoration:underline;outline:0}
    .admin-order-page .size-pills{
        display:grid;
        grid-template-columns:repeat(5,minmax(0,1fr));
        gap:6px;
        min-width:0;
        padding-bottom:12px;
        border-bottom:1px solid var(--order-border);
    }
    .admin-order-page .size-pill{
        display:grid;
        grid-template-columns:minmax(0,1fr) auto;
        align-items:center;
        gap:4px;
        width:100%;
        min-width:0;
        min-height:32px;
        padding:0 5px;
        border:1px solid #BFD0E6;
        border-radius:6px;
        background:#fff;
        color:var(--order-navy)!important;
        font-size:12px;
        font-weight:900;
    }
    .admin-order-page .size-pill__label{overflow:hidden;text-align:center;text-overflow:ellipsis;white-space:nowrap}
    .admin-order-page .baju-meta-row{transform:none}
    .admin-order-page .size-pill b{display:inline-grid;place-items:center;min-width:18px;height:20px;padding:0 3px;border-radius:4px;background:#EAF2FF;color:#0B4DA2;font-size:11px;font-weight:900;line-height:1;white-space:nowrap}
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
    .admin-order-page .image-upload-button{
        position:relative;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:40px;
        flex:0 0 auto;
        border:1px dashed #BFD0E6;
        border-radius:6px;
        background:#F5F9FF;
        color:var(--order-navy);
        padding:0 12px;
        cursor:pointer;
        font-size:11px;
        font-weight:900;
        text-transform:uppercase;
        letter-spacing:.02em;
        white-space:nowrap;
    }
    .admin-order-page .image-upload-button:hover{background:#EAF2FF;border-color:#8DB4F7}
    .admin-order-page .image-upload-button.is-selected{background:#E7F6EF;border-color:#9FDEC0;color:#0D8A4B}
    .admin-order-page .image-upload-button input{position:absolute;width:1px;height:1px;opacity:0;pointer-events:none}
    .admin-order-page .image-upload-button--form{height:40px;width:100%;font-size:11px}
    .admin-order-page .baju-card__buttons{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;align-items:center;padding:0 14px 14px}
    .admin-order-page .baju-card__buttons form{margin:0}
    .admin-order-page .baju-card__buttons .button,
    .admin-order-page .baju-card__buttons .link-button{
        width:100%;
        min-height:32px!important;
        padding:0 12px!important;
        border-radius:6px!important;
        font-size:11px!important;
        font-weight:900!important;
    }
    .admin-order-page .baju-card__buttons .button{
        border-color:var(--order-navy)!important;
        background:var(--order-navy)!important;
        color:#fff!important;
    }
    .admin-order-page .baju-card__buttons .link-button.danger{
        border-color:#F5B9B9!important;
        background:#FDE8E8!important;
        color:#E00012!important;
    }
    .table-wrap{overflow:auto}
    .sub{margin-top:4px;font-size:12px;color:#64748b}
    .order-actions{display:grid;gap:8px}
    .order-actions select,.order-actions input{width:100%;border:1px solid var(--line);border-radius:8px;padding:8px 10px}
    .pagination-wrap{padding:0 18px 18px}
    .admin-order-page .empty-state{grid-column:1 / -1;padding:28px;text-align:center;color:#64748b;font-weight:700;border:1px dashed var(--order-border);border-radius:8px;background:#F8FBFF}
    .size-chart-dialog{width:min(900px,calc(100vw - 32px));max-width:900px;max-height:88dvh;padding:0;border:0;border-radius:12px;background:transparent;overflow:hidden;box-shadow:0 28px 80px rgba(15,23,42,.28)}
    .size-chart-dialog::backdrop{background:rgba(8,47,89,.30)}
    .size-chart-panel{display:grid;grid-template-rows:auto minmax(0,1fr);max-height:88dvh;background:#fff}
    .size-chart-panel__head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:20px 22px;border-bottom:4px solid var(--order-red);background:var(--order-navy)}
    .size-chart-panel__head h2{margin:0;color:#fff;font-size:22px;font-weight:900}
    .size-chart-panel__head p{margin:5px 0 0;color:rgba(255,255,255,.8);font-size:13px;font-weight:700}
    .size-chart-dialog__close{display:grid;place-items:center;width:38px;height:38px;flex:0 0 38px;padding:0;border:1px solid rgba(255,255,255,.35);border-radius:8px;background:rgba(255,255,255,.06);color:#fff;font-size:28px;line-height:1;cursor:pointer}
    .size-chart-dialog__close:hover,.size-chart-dialog__close:focus-visible{background:#fff;color:var(--order-navy);outline:none}
    .size-chart-panel__image{min-height:0;overflow:auto;padding:20px;background:#F5F8FC}
    .size-chart-panel__image img{display:block;width:100%;height:auto;max-height:calc(88dvh - 144px);object-fit:contain;margin:auto;border:1px solid #D7E2EF;border-radius:8px;background:#fff}
    @media (max-width: 1580px){
        .admin-order-page .form-grid--baju{grid-template-columns:minmax(160px,1.3fr) minmax(100px,.55fr) minmax(150px,1fr) minmax(150px,1fr)}
        .admin-order-page .field--sizes{grid-column:1 / span 3}
        .admin-order-page .field-actions{grid-column:4}
    }
    @media (max-width: 1120px){
        .admin-order-page .form-grid--baju{grid-template-columns:repeat(2,minmax(0,1fr))}
        .admin-order-page .field--sizes{grid-column:1 / -1}
        .admin-order-page .field-actions{grid-column:1 / -1}
        .admin-order-page .field-actions .button{width:100%}
        .admin-order-page .size-stock-grid{grid-template-columns:repeat(5,minmax(0,1fr))}
    }
    @media (max-width: 1400px){
        .admin-order-page .baju-grid{grid-template-columns:repeat(3,minmax(0,1fr))}
    }
    @media (max-width: 1000px){
        .admin-order-page .admin-baju-hero{align-items:flex-start;flex-direction:column;min-height:0}
        .admin-order-page .admin-baju-hero__meta{display:flex;align-items:center;justify-content:space-between;width:100%;text-align:left;white-space:normal}
        .admin-order-page .baju-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    }
    @media (max-width: 640px){
        .admin-order-page{gap:14px}
        .admin-order-page .admin-baju-hero{align-items:flex-start;flex-direction:column;min-height:0;padding:22px}
        .admin-order-page .admin-baju-hero h2{font-size:26px}
        .admin-order-page .admin-baju-hero__meta{justify-items:start;text-align:left;white-space:normal}
        .admin-order-page .baju-panel{padding:16px!important}
        .admin-order-page .form-grid--baju{grid-template-columns:1fr}
        .admin-order-page .baju-grid{grid-template-columns:1fr}
        .admin-order-page .baju-meta-row{align-items:flex-start;flex-direction:column}
        .admin-order-page .size-stock-grid{grid-template-columns:repeat(5,minmax(72px,1fr));overflow-x:auto;padding-bottom:4px}
        .admin-order-page .size-stock-input{min-width:72px}
        .admin-order-page .baju-card__image{max-height:none}
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

    const sizeChartDialog = document.querySelector('[data-size-chart-dialog]');
    const sizeChartImage = document.querySelector('[data-size-chart-image]');
    const sizeChartTitle = document.querySelector('#sizeChartTitle');

    document.querySelectorAll('[data-size-chart-open]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (! sizeChartDialog || ! sizeChartImage) {
                return;
            }

            const title = button.dataset.sizeChartTitle || 'Carta Saiz';
            sizeChartImage.src = button.dataset.sizeChartSrc || '';
            sizeChartImage.alt = title;
            if (sizeChartTitle) {
                sizeChartTitle.textContent = title;
            }
            sizeChartDialog.showModal();
        });
    });

    document.querySelector('[data-size-chart-close]')?.addEventListener('click', function () {
        sizeChartDialog?.close();
    });

    sizeChartDialog?.addEventListener('click', function (event) {
        if (event.target === sizeChartDialog) {
            sizeChartDialog.close();
        }
    });
</script>
@endpush
