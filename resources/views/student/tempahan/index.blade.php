@extends('layouts.app')

@section('title', 'Tempahan')
@section('page-title', 'Tempahan Baju')
@section('page-subtitle', 'Buat dan semak tempahan anda.')

@section('content')
    @php
        $sizeOrder = ['S' => 1, 'M' => 2, 'L' => 3, 'XL' => 4, 'XXL' => 5];
        $groupedItems = collect($items)
            ->groupBy('nama_item')
            ->map(fn ($variants) => $variants->sortBy(fn ($variant) => $sizeOrder[strtoupper((string) $variant->saiz)] ?? 99)->values());
        $cartTotalQuantity = $cartItems->sum('quantity_ordered');
        $cartTotalItems = $cartItems->count();
    @endphp

    <section class="student-hero">
        <div>
            <h2>Tempahan Baju</h2>
            <p>Pilih baju dan size yang tersedia, kemudian semak status ambil tempahan anda di sini.</p>
        </div>
    </section>

    @if ($errors->any())
        <div class="alert" style="border-color:var(--danger-soft);background:var(--primary-soft);color:var(--primary-dark)">{{ $errors->first() }}</div>
    @endif

    @if (session('status'))
        <div class="alert success">{{ session('status') }}</div>
    @endif

    <div class="student-layout">
        <section class="panel panel-pad">
            <div class="booking-head">
                <div>
                    <h2 style="margin:0">Tempahan Baru</h2>
                    <p>Pilih baju dan size, masukkan dalam troli, kemudian hantar tempahan.</p>
                </div>
                <button class="cart-top-button booking-cart-button" type="button" data-cart-dialog-open aria-label="Buka troli tempahan">
                    <span class="cart-top-button__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="20" r="1.5"></circle>
                            <circle cx="18" cy="20" r="1.5"></circle>
                            <path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.4L21 8H6"></path>
                        </svg>
                        <strong>{{ $cartTotalQuantity }}</strong>
                    </span>
                    <span>
                        <b>Troli tempahan</b>
                    </span>
                </button>
            </div>

            <div class="booking-steps">
                <span class="step-chip is-active">1. Pilih Baju</span>
                <span class="step-chip">2. Pilih Size</span>
                <span class="step-chip">3. Masuk Troli</span>
                <span class="step-chip">4. Hantar Troli</span>
            </div>

            <div class="baju-catalog">
                @forelse ($groupedItems as $itemName => $variants)
                    @php
                        $preview = $variants->first();
                        $activeVariant = $variants->firstWhere('item_id', (int) old('item_id')) ?? $preview;
                    @endphp
                    <article class="baju-card {{ (int) old('item_id') && $variants->contains('item_id', (int) old('item_id')) ? 'is-active' : '' }}" data-group-card="{{ $itemName }}">
                        <div class="baju-card__image">
                            @if (!empty($preview->image_path))
                                <img src="{{ asset($preview->image_path) }}" alt="{{ $preview->nama_item }}" loading="lazy" onerror="this.classList.add('is-hidden'); this.nextElementSibling.classList.remove('is-hidden');">
                                <div class="baju-card__placeholder is-hidden">Tiada Gambar</div>
                            @else
                                <div class="baju-card__placeholder">Tiada Gambar</div>
                            @endif
                        </div>
                        <div class="baju-card__body">
                            <div class="baju-card__title">
                                <strong>{{ $itemName }}</strong>
                            </div>
                            <div class="baju-card__sizes">
                                @foreach ($variants as $variant)
                                    <button
                                        type="button"
                                        class="size-chip {{ (int) old('item_id') === (int) $variant->item_id ? 'is-active' : '' }} {{ (int) $variant->quantity < 1 ? 'is-disabled' : '' }}"
                                        data-select-item="{{ $variant->item_id }}"
                                        data-group-name="{{ $itemName }}"
                                        data-size="{{ $variant->saiz ?? '-' }}"
                                        data-stock="{{ $variant->quantity }}"
                                        {{ (int) $variant->quantity < 1 ? 'disabled' : '' }}
                                    >
                                        <span>{{ $variant->saiz ?? '-' }}</span>
                                    </button>
                                @endforeach
                            </div>
                            <form method="POST" action="{{ route('student.tempahan.cart.add') }}" class="card-cart-form">
                                @csrf
                                <input name="item_id" type="hidden" value="{{ (int) old('item_id') && $variants->contains('item_id', (int) old('item_id')) ? old('item_id') : '' }}" required>
                                <div class="card-cart-form__row">
                                    <label>
                                        <span>Kuantiti</span>
                                        <input name="quantity" type="number" min="1" value="{{ old('quantity', 1) }}" required>
                                    </label>
                                    <button class="button card-cart-form__button" type="submit">Tambah ke Troli</button>
                                </div>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">Tiada baju tersedia sekarang.</div>
                @endforelse
            </div>

        </section>

        <dialog class="cart-dialog" data-cart-dialog aria-labelledby="cartDialogTitle">
        <section class="panel cart-panel">
            <div class="cart-shell-head">
                <div class="cart-title">
                    <span class="cart-logo" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="20" r="1.5"></circle>
                            <circle cx="18" cy="20" r="1.5"></circle>
                            <path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.4L21 8H6"></path>
                        </svg>
                    </span>
                    <div>
                        <h2 id="cartDialogTitle">Troli Tempahan</h2>
                        <p>{{ $cartTotalItems }} jenis item, {{ $cartTotalQuantity }} kuantiti dipilih.</p>
                    </div>
                </div>

                <button class="cart-dialog-close" type="button" data-cart-dialog-close aria-label="Tutup cart">&times;</button>
            </div>

            <div class="cart-list">
                @forelse ($cartItems as $cartItem)
                    <article class="cart-item">
                        <div class="cart-product">
                            <div class="cart-product__thumb">
                                @if (!empty($cartItem->image_path))
                                    <img src="{{ asset($cartItem->image_path) }}" alt="{{ $cartItem->nama_item }}" loading="lazy">
                                @else
                                    <span>{{ strtoupper(substr($cartItem->nama_item, 0, 2)) }}</span>
                                @endif
                            </div>
                            <div>
                                <strong>{{ $cartItem->nama_item }}</strong>
                                <span>Size {{ $cartItem->saiz ?? '-' }} · Stok {{ $cartItem->stok_tertinggal }}</span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('student.tempahan.cart.update', $cartItem->id_item) }}" class="cart-quantity-form">
                            @csrf
                            @method('PATCH')
                            <label for="cart_quantity_{{ $cartItem->id_item }}">Kuantiti</label>
                            <div class="cart-quantity-control">
                                <input id="cart_quantity_{{ $cartItem->id_item }}" name="quantity" type="number" min="1" max="{{ $cartItem->stok_tertinggal }}" value="{{ $cartItem->quantity_ordered }}" required>
                                <button class="cart-update-button" type="submit">Update</button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('student.tempahan.cart.remove', $cartItem->id_item) }}" class="cart-remove-form">
                            @csrf
                            @method('DELETE')
                            <button class="cart-remove-button" type="submit" aria-label="Buang {{ $cartItem->nama_item }} dari troli">Buang</button>
                        </form>
                    </article>
                @empty
                    <div class="cart-empty">
                        <span class="cart-logo cart-logo--empty" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="20" r="1.5"></circle>
                                <circle cx="18" cy="20" r="1.5"></circle>
                                <path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.4L21 8H6"></path>
                            </svg>
                        </span>
                        <strong>Troli masih kosong</strong>
                        <p>Pilih baju dan masukkan kuantiti sebelum hantar tempahan.</p>
                    </div>
                @endforelse
            </div>

            @if ($cartItems->isNotEmpty())
                <div class="cart-footer">
                    <div class="cart-footer-total">
                        <span>Jumlah Kuantiti</span>
                        <strong>{{ $cartTotalQuantity }}</strong>
                    </div>
                    <form method="POST" action="{{ route('student.tempahan.cart.checkout') }}">
                        @csrf
                        <button class="button cart-checkout-button" type="submit">Hantar Troli</button>
                    </form>
                </div>
            @endif
        </section>
        </dialog>

        <section class="panel" style="overflow:hidden">
            <div class="panel-pad">
                <h2 style="margin:0">Senarai Tempahan</h2>
            </div>
            <div class="table-wrap student-orders-table-wrap">
                <table class="student-orders-table">
                    <thead><tr><th>ID</th><th>Item</th><th>Size</th><th>Kuantiti</th><th>Status</th><th>Ambil</th><th>Tarikh</th></tr></thead>
                    <tbody>
                        @forelse ($orders as $order)
                            @php
                                $statusKey = filled($order->tarikh_ambil)
                                    ? 'sudah_ambil'
                                    : match (strtolower((string) $order->status)) {
                                        'pending' => 'baru',
                                        'belum_ambil', 'belum ambil', 'diproses', 'dalam proses', 'siap', 'menunggu pengambilan', 'menunggu_ambil' => 'belum_ambil',
                                        'sudah_ambil', 'sudah ambil', 'diambil', 'siap diambil', 'selesai' => 'sudah_ambil',
                                        default => strtolower((string) $order->status),
                                    };
                                $statusLabel = ['baru' => 'Baru', 'belum_ambil' => 'Belum Ambil', 'sudah_ambil' => 'Sudah Ambil'][$statusKey] ?? ucfirst($statusKey);
                            @endphp
                            <tr>
                                <td>#{{ $order->tempahan_id }}</td>
                                <td>{{ $order->item }}</td>
                                <td>{{ $order->saiz ?? '-' }}</td>
                                <td>{{ $order->quantity }}</td>
                                <td><span class="badge {{ $statusKey === 'sudah_ambil' ? 'success' : '' }}">{{ $statusLabel }}</span></td>
                                <td><span class="badge {{ $statusKey === 'sudah_ambil' ? 'success' : '' }}">{{ $statusKey === 'sudah_ambil' ? 'Sudah Ambil' : 'Belum Ambil' }}</span></td>
                                <td>{{ $order->created_at ? \Illuminate\Support\Carbon::parse($order->created_at)->format('d/m/Y') : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="empty-state">Tiada tempahan lagi. Pilih baju dan hantar tempahan pertama anda.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="panel-pad">{{ $orders->links() }}</div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const itemCards = document.querySelectorAll('[data-group-card]');
            const sizeButtons = document.querySelectorAll('[data-select-item]');

            function updateCardSelection(card, button) {
                const input = card.querySelector('[name="item_id"]');
                const quantityInput = card.querySelector('[name="quantity"]');
                const pick = card.querySelector('.card-cart-form__pick');
                const stock = Number(button.dataset.stock || 0);

                input.value = button.dataset.selectItem;
                quantityInput.max = stock;

                if (Number(quantityInput.value || 1) > stock) {
                    quantityInput.value = stock;
                }

                card.querySelectorAll('[data-select-item]').forEach(function (sizeButton) {
                    sizeButton.classList.toggle('is-active', sizeButton === button);
                });

                card.classList.add('is-active');
                if (pick) {
                    pick.textContent = `${button.dataset.groupName} - Size ${button.dataset.size}`;
                    pick.classList.add('is-selected');
                    pick.classList.remove('has-error');
                }
            }

            itemCards.forEach(function (card) {
                const activeButton = card.querySelector('.size-chip.is-active');
                const form = card.querySelector('.card-cart-form');
                const pick = card.querySelector('.card-cart-form__pick');

                if (activeButton) {
                    updateCardSelection(card, activeButton);
                }

                form?.addEventListener('submit', function (event) {
                    if (!form.querySelector('[name="item_id"]').value) {
                        event.preventDefault();
                        if (!pick) {
                            const message = document.createElement('div');
                            message.className = 'card-cart-form__pick has-error';
                            message.textContent = 'Sila pilih size baju dahulu';
                            form.prepend(message);
                            return;
                        }

                        pick.textContent = 'Sila pilih size baju dahulu';
                        pick.classList.add('has-error');
                    }
                });
            });

            sizeButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    updateCardSelection(this.closest('[data-group-card]'), this);
                });
            });

            const cartDialog = document.querySelector('[data-cart-dialog]');
            const cartOpenButton = document.querySelector('[data-cart-dialog-open]');
            const cartCloseButton = document.querySelector('[data-cart-dialog-close]');

            cartOpenButton?.addEventListener('click', function () {
                if (cartDialog && typeof cartDialog.showModal === 'function') {
                    cartDialog.showModal();
                }
            });

            cartCloseButton?.addEventListener('click', function () {
                cartDialog?.close();
            });

            cartDialog?.addEventListener('click', function (event) {
                if (event.target === cartDialog) {
                    cartDialog.close();
                }
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .student-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
        }
        .cart-top-button {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            min-height: 58px;
            padding: 8px 16px 8px 10px;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            background: #fff;
            color: var(--secondary);
            font: inherit;
            text-align: left;
            cursor: pointer;
            box-shadow: 0 14px 30px rgba(15, 23, 42, .07);
            transition: border-color .18s ease, background .18s ease, transform .18s ease;
        }
        .cart-top-button:hover {
            border-color: var(--secondary);
            background: var(--secondary-soft);
            transform: translateY(-1px);
        }
        .cart-top-button__icon {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: var(--secondary);
            color: #fff;
            flex: 0 0 44px;
        }
        .cart-top-button__icon svg {
            width: 23px;
            height: 23px;
        }
        .cart-top-button__icon strong {
            position: absolute;
            top: -8px;
            right: -8px;
            min-width: 23px;
            height: 23px;
            padding: 0 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
            border-radius: 999px;
            background: var(--primary);
            color: #fff;
            font-size: 11px;
            font-weight: 900;
            line-height: 1;
        }
        .cart-top-button small,
        .cart-top-button b {
            display: block;
            line-height: 1.2;
            white-space: nowrap;
        }
        .cart-top-button small {
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .02em;
        }
        .cart-top-button b {
            margin-top: 3px;
            color: var(--text);
            font-size: 15px;
            font-weight: 900;
        }
        .booking-cart-button {
            min-height: 42px;
            padding: 6px 14px 6px 8px;
            border-radius: 999px;
            background: var(--secondary-soft);
            box-shadow: none;
        }
        .booking-cart-button .cart-top-button__icon {
            width: 28px;
            height: 28px;
            flex-basis: 28px;
            border-radius: 999px;
        }
        .booking-cart-button .cart-top-button__icon svg {
            width: 15px;
            height: 15px;
        }
        .booking-cart-button .cart-top-button__icon strong {
            top: -6px;
            right: -6px;
            min-width: 18px;
            height: 18px;
            font-size: 9px;
        }
        .booking-cart-button b {
            margin: 0;
            color: var(--primary);
            font-size: 14px;
        }
        .cart-dialog {
            width: min(1120px, calc(100vw - 40px));
            max-width: 1120px;
            max-height: min(88dvh, 860px);
            padding: 0;
            border: 0;
            border-radius: 14px;
            background: transparent;
            overflow: hidden;
            box-shadow: 0 26px 80px rgba(15, 23, 42, .30);
        }
        .cart-dialog::backdrop {
            background: rgba(15, 23, 42, .44);
        }
        .cart-dialog .cart-panel {
            max-height: min(88dvh, 860px);
            overflow-y: scroll;
            overflow-x: hidden;
            overscroll-behavior: contain;
            scrollbar-gutter: stable;
            border-radius: 14px !important;
        }
        .cart-dialog-close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
            color: var(--text);
            font-size: 26px;
            font-weight: 800;
            line-height: 1;
            cursor: pointer;
        }
        .cart-dialog-close:hover {
            border-color: var(--danger-soft);
            background: var(--danger-soft);
            color: var(--danger);
        }
        .booking-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 16px;
        }
        .booking-head p {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 13px;
        }
        .booking-steps {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .step-chip {
            min-height: 30px;
            padding: 0 12px;
            border-radius: 999px;
            background: #f8fafc;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            font-size: 12px;
            font-weight: 800;
        }
        .step-chip.is-active {
            background: var(--secondary-soft);
            color: var(--primary);
        }
        .booking-tip {
            flex: 0 0 auto;
            min-height: 32px;
            padding: 0 12px;
            border-radius: 999px;
            background: var(--secondary-soft);
            color: var(--primary);
            display: inline-flex;
            align-items: center;
            font-size: 12px;
            font-weight: 800;
        }
        .baju-catalog {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            align-items: stretch;
            margin-bottom: 20px;
        }
        .baju-card {
            border: 1px solid var(--line);
            border-radius: 18px;
            overflow: hidden;
            background: #fff;
            transition: .2s ease;
            display: flex;
            flex-direction: column;
            min-width: 0;
            height: 100%;
            box-shadow: var(--shadow-sm);
        }
        .baju-card.is-active {
            border-color: #bfdbfe;
            box-shadow: 0 14px 34px rgba(30, 64, 175, 0.10);
        }
        .baju-card__image {
            width: 100%;
            aspect-ratio: 4 / 5;
            max-height: 360px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .baju-card__image img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            flex: 0 0 100%;
        }
        .baju-card__image .is-hidden {
            display: none !important;
        }
        .baju-card__placeholder {
            width: 100%;
            height: 100%;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 700;
            display: grid;
            place-items: center;
        }
        .baju-card__body {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 16px;
            flex: 1;
        }
        .baju-card__title {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }
        .baju-card__body strong {
            color: #0f172a;
            font-size: 15px;
        }
        .baju-card__body > span {
            color: #64748b;
            font-size: 12px;
        }
        .baju-card__sizes {
            display: flex;
            flex-wrap: nowrap;
            gap: 8px;
            margin-top: 2px;
            overflow-x: auto;
            padding-bottom: 4px;
        }
        .size-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            min-width: 52px;
            min-height: 38px;
            padding: 0 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
            color: #0f172a;
            text-align: center;
            cursor: pointer;
            transition: .18s ease;
        }
        .size-chip:hover { border-color: #94a3b8; background: #f8fafc; }
        .size-chip.is-disabled,
        .size-chip:disabled {
            opacity: .45;
            cursor: not-allowed;
            background: #f8fafc;
        }
        .size-chip span {
            font-size: 13px;
            font-weight: 800;
        }
        .size-chip.is-active {
            border-color: var(--primary);
            background: var(--secondary-soft);
            color: var(--primary);
        }
        .card-cart-form {
            display: grid;
            gap: 12px;
            margin-top: auto;
            padding-top: 14px;
            border-top: 1px solid var(--line);
        }
        .card-cart-form__pick {
            min-height: 38px;
            display: flex;
            align-items: center;
            padding: 8px 10px;
            border: 1px dashed #bfdbfe;
            border-radius: 8px;
            background: #f8fbff;
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
        }
        .card-cart-form__pick.is-selected {
            border-style: solid;
            border-color: var(--secondary);
            background: var(--secondary-soft);
            color: var(--secondary);
        }
        .card-cart-form__pick.has-error {
            border-style: solid;
            border-color: var(--danger-soft);
            background: var(--danger-soft);
            color: var(--danger);
        }
        .card-cart-form__row {
            display: grid;
            grid-template-columns: 96px minmax(0, 1fr);
            gap: 10px;
            align-items: end;
        }
        .card-cart-form label {
            display: grid;
            gap: 6px;
            margin: 0;
        }
        .card-cart-form label span {
            color: var(--muted);
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .02em;
        }
        .card-cart-form input {
            width: 100%;
            min-height: 40px;
            border: 1px solid var(--line-strong);
            border-radius: 8px;
            padding: 8px 10px;
            background: #fff;
            color: var(--text);
            font: inherit;
            font-weight: 900;
            text-align: center;
        }
        .card-cart-form__button {
            min-height: 40px !important;
            width: 100%;
        }
        .cart-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            border-bottom: 1px solid var(--line);
        }
        .cart-head p {
            margin: 6px 0 0;
            color: var(--muted-2);
            font-weight: 700;
        }
        .cart-panel {
            padding: 0 !important;
            overflow: hidden;
        }
        .cart-shell-head {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto auto;
            gap: 18px;
            align-items: center;
            padding: 22px 24px;
            border-bottom: 1px solid var(--line);
            background: #fff;
        }
        .cart-title {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }
        .cart-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            flex: 0 0 52px;
            border-radius: 14px;
            background: var(--secondary);
            color: #fff;
            box-shadow: 0 14px 28px rgba(36, 83, 166, .16);
        }
        .cart-logo svg {
            width: 26px;
            height: 26px;
        }
        .cart-title h2 {
            margin: 0;
            color: var(--text);
            font-size: 22px;
            line-height: 1.2;
        }
        .cart-title p {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }
        .cart-summary {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }
        .cart-summary > div {
            display: grid;
            gap: 3px;
            min-width: 132px;
            min-height: 58px;
            align-content: center;
            padding: 10px 13px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--surface-soft);
        }
        .cart-summary span {
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .02em;
        }
        .cart-summary strong {
            color: var(--text);
            font-size: 18px;
            line-height: 1.15;
            font-weight: 900;
            white-space: nowrap;
        }
        .cart-checkout-button {
            min-height: 52px !important;
            padding-inline: 18px !important;
            font-size: 14px !important;
            white-space: nowrap;
        }
        .cart-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            padding: 12px 20px;
            border-top: 1px solid var(--line);
            background: #fff;
        }
        .cart-footer form {
            display: flex;
            align-items: stretch;
            margin: 0;
        }
        .cart-footer-total {
            display: grid;
            gap: 4px;
            width: 142px;
            min-height: 52px;
            align-content: center;
            padding: 8px 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--surface-soft);
        }
        .cart-footer-total span {
            color: var(--muted);
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .02em;
        }
        .cart-footer-total strong {
            color: var(--text);
            font-size: 18px;
            line-height: 1;
            font-weight: 900;
        }
        .cart-list {
            display: grid;
            background: #fff;
        }
        .cart-item {
            display: grid;
            grid-template-columns: minmax(260px, 1fr) minmax(210px, auto) auto;
            gap: 16px;
            align-items: end;
            padding: 18px 24px;
            border-bottom: 1px solid var(--line);
        }
        .cart-item:last-child {
            border-bottom: 0;
        }
        .cart-product {
            display: flex;
            align-items: center;
            align-self: center;
            gap: 12px;
            min-width: 0;
        }
        .cart-product__thumb {
            width: 58px;
            height: 58px;
            flex: 0 0 58px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: var(--surface-soft);
            overflow: hidden;
            display: grid;
            place-items: center;
            color: var(--secondary);
            font-weight: 900;
        }
        .cart-product__thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .cart-product strong {
            display: block;
            color: var(--text);
            font-size: 15px;
            line-height: 1.35;
        }
        .cart-product span {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }
        .cart-quantity-form {
            display: grid;
            gap: 7px;
            margin: 0;
        }
        .cart-quantity-form label {
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .02em;
        }
        .cart-quantity-control {
            display: grid;
            grid-template-columns: 82px auto;
            gap: 8px;
            align-items: center;
        }
        .cart-quantity-control input {
            width: 82px;
            min-height: 40px;
            padding: 8px 10px;
            border: 1px solid var(--line-strong);
            border-radius: 8px;
            background: #fff;
            color: var(--text);
            font-weight: 900;
            text-align: center;
        }
        .cart-update-button,
        .cart-remove-button {
            min-height: 40px;
            padding: 0 14px;
            border-radius: 8px;
            font: inherit;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
        }
        .cart-update-button {
            border: 1px solid #bfdbfe;
            background: var(--secondary-soft);
            color: var(--secondary);
        }
        .cart-update-button:hover {
            border-color: var(--secondary);
            background: var(--secondary);
            color: #fff;
        }
        .cart-remove-form {
            align-self: end;
            margin: 0;
        }
        .cart-remove-button {
            border: 1px solid var(--danger-soft);
            background: var(--danger-soft);
            color: var(--danger);
        }
        .cart-remove-button:hover {
            background: var(--danger);
            color: #fff;
        }
        .cart-empty {
            display: grid;
            gap: 8px;
            justify-items: center;
            padding: 44px 24px;
            text-align: center;
            color: var(--muted);
        }
        .cart-logo--empty {
            width: 62px;
            height: 62px;
            flex-basis: 62px;
            background: var(--secondary-soft);
            color: var(--secondary);
            box-shadow: none;
        }
        .cart-empty strong {
            color: var(--text);
            font-size: 18px;
        }
        .cart-empty p {
            margin: 0;
            font-weight: 700;
        }
        .cart-table form {
            margin: 0;
        }
        .danger-button {
            background: var(--danger-soft) !important;
            color: var(--danger) !important;
            border: 1px solid var(--danger-soft) !important;
            box-shadow: none !important;
        }
        .danger-button:hover {
            background: var(--danger) !important;
            color: #fff !important;
        }
        @media (max-width: 720px) {
            .student-layout {
                gap: 18px;
            }
            .booking-head {
                flex-direction: column;
            }
            .cart-head {
                flex-direction: column;
            }
            .baju-card__title {
                flex-direction: column;
                align-items: flex-start;
            }
            .card-cart-form__row {
                grid-template-columns: 1fr;
            }
            .cart-top-button {
                width: 100%;
                justify-content: flex-start;
            }
        }
        @media (max-width: 980px) {
            .cart-shell-head {
                grid-template-columns: 1fr;
            }
            .cart-dialog-close {
                position: absolute;
                top: 14px;
                right: 14px;
            }
            .cart-dialog .cart-title {
                padding-right: 56px;
            }
            .cart-summary {
                justify-content: stretch;
            }
            .cart-summary > div,
            .cart-summary form,
            .cart-checkout-button {
                width: 100%;
            }
            .cart-footer {
                justify-content: stretch;
            }
            .cart-footer-total,
            .cart-footer form,
            .cart-footer .cart-checkout-button {
                width: 100%;
            }
            .cart-item {
                grid-template-columns: 1fr 1fr;
                align-items: start;
            }
            .cart-product {
                grid-column: 1 / -1;
            }
            .cart-remove-form {
                justify-self: end;
            }
        }
        @media (max-width: 620px) {
            .cart-dialog {
                width: calc(100vw - 18px);
                max-height: calc(100dvh - 18px);
                border-radius: 12px;
            }
            .cart-dialog .cart-panel {
                max-height: calc(100dvh - 18px);
            }
            .cart-shell-head {
                padding: 18px;
            }
            .cart-title {
                align-items: flex-start;
            }
            .cart-logo {
                width: 46px;
                height: 46px;
                flex-basis: 46px;
                border-radius: 12px;
            }
            .cart-item {
                grid-template-columns: 1fr;
                gap: 13px;
                padding: 17px 18px;
            }
            .cart-footer {
                flex-direction: column;
                padding: 16px 18px;
            }
            .cart-quantity-control {
                grid-template-columns: 1fr auto;
            }
            .cart-quantity-control input {
                width: 100%;
            }
            .cart-remove-form,
            .cart-remove-button {
                width: 100%;
            }
        }
        @media (max-width: 1180px) {
            .baju-catalog {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 760px) {
            .baju-catalog {
                grid-template-columns: 1fr;
            }
        }
        .student-hero{margin-bottom:28px!important;border:1px solid var(--line)!important;border-left:5px solid var(--secondary)!important;border-radius:18px!important;background:#fff!important;color:var(--text)!important;box-shadow:var(--shadow-sm);padding:28px!important}
        .student-hero h2{color:var(--text)!important;font-size:32px!important;letter-spacing:0!important}
        .student-hero p{color:var(--muted-2)!important;font-weight:800!important}
        .student-hero .link-button{background:var(--secondary-soft)!important;border:1px solid var(--secondary-soft)!important;color:var(--secondary)!important;box-shadow:none!important}
        .student-hero .link-button:hover{background:var(--secondary)!important;color:#fff!important}
        .student-layout{display:grid;gap:28px!important}
        .student-layout > .panel{
            overflow:hidden;
            border-radius:26px!important;
            padding:24px!important;
            background:#fff!important;
            border:1px solid var(--line)!important;
            box-shadow:var(--shadow-sm)!important
        }
        .booking-head{
            padding:0 0 18px!important;
            margin:0 0 22px!important;
            border-bottom:1px solid var(--line);
            background:transparent
        }
        .baju-card{border-radius:18px!important;box-shadow:var(--shadow-sm)}
        .size-chip.is-active{border-color:var(--secondary)!important;background:var(--secondary-soft)!important;color:var(--secondary)!important}
        table{width:100%;border-collapse:separate;border-spacing:0}
        thead th{background:var(--surface-soft);color:var(--muted);font-size:12px;text-transform:uppercase;letter-spacing:.02em}
        th,td{padding:14px 16px;border-bottom:1px solid var(--line);text-align:left}
        .student-orders-table-wrap{border:0;border-radius:0}
        .student-orders-table{min-width:720px}
    </style>
@endpush
