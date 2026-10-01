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
        $money = fn ($value) => 'RM '.number_format((float) $value, 2);
    @endphp

    <div class="student-order-page">
        <section class="student-hero">
            <div>
                <h2>Tempahan Baju</h2>
                <p>Pilih baju dan size yang tersedia, kemudian semak status ambil tempahan anda di sini.</p>
            </div>
        </section>

        @if ($errors->any())
            <div class="alert" style="border-color:var(--danger-soft);background:var(--primary-soft);color:var(--primary-dark)">{{ $errors->first() }}</div>
        @endif

        <div class="student-layout">
            <section class="panel panel-pad order-panel">
            <div class="booking-head">
                <div>
                    <h2>Tempahan Baru</h2>
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
                        <b>Troli Saya ({{ $cartTotalQuantity }})</b>
                    </span>
                </button>
            </div>

            <div class="baju-catalog">
                @forelse ($groupedItems as $itemName => $variants)
                    @php
                        $preview = $variants->first();
                        $activeVariant = $variants->firstWhere('item_id', (int) old('item_id')) ?? $preview;
                        $itemCategory = (string) ($preview->nama_kategori ?? '');
                    @endphp
                    <article class="baju-card {{ (int) old('item_id') && $variants->contains('item_id', (int) old('item_id')) ? 'is-active' : '' }}" data-group-card="{{ $itemName }}" data-category="{{ $itemCategory }}">
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
                                <span class="baju-card__price">{{ $money($preview->harga) }}</span>
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
                            @if ($preview->size_chart_path)
                                <button
                                    class="size-chart-button"
                                    type="button"
                                    data-size-chart-open
                                    data-size-chart-src="{{ asset($preview->size_chart_path) }}"
                                    data-size-chart-title="Carta Saiz {{ $itemName }}"
                                >
                                    Lihat Carta Saiz
                                </button>
                            @endif
                            <form method="POST" action="{{ route('student.tempahan.cart.add') }}" class="card-cart-form">
                                @csrf
                                <input name="item_id" type="hidden" value="{{ (int) old('item_id') && $variants->contains('item_id', (int) old('item_id')) ? old('item_id') : '' }}" required>
                                <div class="card-cart-form__row">
                                    <label>
                                        <span>KUANTITI</span>
                                        <input name="quantity" type="number" inputmode="numeric" min="1" value="{{ old('quantity', 1) }}" required>
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

        <dialog class="size-chart-dialog" data-size-chart-dialog aria-labelledby="sizeChartTitle">
            <section class="size-chart-panel">
                <header class="size-chart-panel__head">
                    <div>
                        <h2 id="sizeChartTitle">Carta Saiz</h2>
                        <p>Pilih size yang paling sesuai sebelum menambah baju ke troli.</p>
                    </div>
                    <button class="size-chart-dialog__close" type="button" data-size-chart-close aria-label="Tutup carta saiz">&times;</button>
                </header>
                <div class="size-chart-panel__image">
                    <img data-size-chart-image src="" alt="Carta saiz baju">
                </div>
            </section>
        </dialog>

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
                                <em class="cart-stock-badge">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                    Stok tersedia
                                </em>
                                <span>Size {{ $cartItem->saiz ?? '-' }} · Stok {{ $cartItem->stok_tertinggal }}</span>
                            </div>
                        </div>

                        <div class="cart-actions">
                            <form method="POST" action="{{ route('student.tempahan.cart.update', $cartItem->id_item) }}" class="cart-quantity-form">
                                @csrf
                                @method('PATCH')
                                <label for="cart_quantity_{{ $cartItem->id_item }}">Kuantiti</label>
                                <div class="cart-quantity-control">
                                    <button class="cart-stepper-button" type="button" data-cart-step="-1" aria-label="Kurangkan kuantiti {{ $cartItem->nama_item }}">-</button>
                                    <input id="cart_quantity_{{ $cartItem->id_item }}" name="quantity" type="number" min="1" max="{{ $cartItem->stok_tertinggal }}" value="{{ $cartItem->quantity_ordered }}" required>
                                    <button class="cart-stepper-button" type="button" data-cart-step="1" aria-label="Tambah kuantiti {{ $cartItem->nama_item }}">+</button>
                                    <button class="cart-update-button" type="submit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 0 1-15.4 6.36L3 16"/><path d="M3 21v-5h5"/><path d="M3 12a9 9 0 0 1 15.4-6.36L21 8"/><path d="M21 3v5h-5"/></svg>
                                        Kemaskini
                                    </button>
                                </div>
                            </form>

                            <form method="POST" action="{{ route('student.tempahan.cart.remove', $cartItem->id_item) }}" class="cart-remove-form">
                                @csrf
                                @method('DELETE')
                                <button class="cart-remove-button" type="submit" aria-label="Buang {{ $cartItem->nama_item }} dari troli">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/></svg>
                                    Buang
                                </button>
                            </form>
                        </div>
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
                    <div class="cart-footer-count">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/></svg>
                        <strong>{{ $cartTotalItems }} jenis item</strong>
                    </div>
                    <div class="cart-footer-total">
                        <span>Jumlah Kuantiti</span>
                        <strong>{{ $cartTotalQuantity }}</strong>
                    </div>
                    <form method="POST" action="{{ route('student.tempahan.cart.checkout') }}">
                        @csrf
                        <button class="button cart-checkout-button" type="submit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7Z"/></svg>
                            Hantar Troli
                        </button>
                    </form>
                </div>
            @endif
        </section>
            </dialog>

            <section class="panel order-panel order-list-panel">
            <div class="panel-pad">
                <h2>Senarai Tempahan</h2>
            </div>
            <div class="table-wrap student-orders-table-wrap">
                <table class="student-orders-table">
                    <thead><tr><th>ID</th><th>Item</th><th>Size</th><th>Kuantiti</th><th>Ambil</th><th>Tarikh</th></tr></thead>
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
                                $pickupKey = filled($order->tarikh_ambil) ? 'sudah_ambil' : 'belum_ambil';
                            @endphp
                            <tr>
                                <td>#{{ $order->tempahan_id }}</td>
                                <td>{{ $order->item }}</td>
                                <td>{{ $order->saiz ?? '-' }}</td>
                                <td>{{ $order->quantity }}</td>
                                <td><span class="order-status order-status--{{ $pickupKey }}">{{ $pickupKey === 'sudah_ambil' ? 'Sudah Ambil' : 'Belum Ambil' }}</span></td>
                                <td>{{ $order->created_at ? \Illuminate\Support\Carbon::parse($order->created_at)->format('d/m/Y') : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="empty-state">Tiada rekod tempahan buat masa ini.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="panel-pad">{{ $orders->links() }}</div>
            </section>
        </div>
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

            function clearCardSelection(card, button) {
                const input = card.querySelector('[name="item_id"]');
                const quantityInput = card.querySelector('[name="quantity"]');
                const pick = card.querySelector('.card-cart-form__pick');

                input.value = '';
                quantityInput.removeAttribute('max');
                button.classList.remove('is-active');
                card.classList.remove('is-active');

                if (pick) {
                    pick.textContent = 'Sila pilih size baju dahulu';
                    pick.classList.remove('is-selected');
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
                        return;
                    }

                    const submitButton = form.querySelector('[type="submit"]');
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.dataset.originalText = submitButton.textContent;
                        submitButton.textContent = 'Memproses...';
                    }
                });
            });

            sizeButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const card = this.closest('[data-group-card]');

                    if (this.classList.contains('is-active')) {
                        clearCardSelection(card, this);
                        return;
                    }

                    updateCardSelection(card, this);
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

            const sizeChartDialog = document.querySelector('[data-size-chart-dialog]');
            const sizeChartImage = document.querySelector('[data-size-chart-image]');
            const sizeChartTitle = document.querySelector('#sizeChartTitle');

            document.querySelectorAll('[data-size-chart-open]').forEach(function (button) {
                button.addEventListener('click', function () {
                    if (!sizeChartDialog || !sizeChartImage) {
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

            function syncStepper(input) {
                const min = Number(input.min || 1);
                const max = Number(input.max || 100);
                let value = Number(input.value || min);

                if (value < min) {
                    value = min;
                }

                if (value > max) {
                    value = max;
                }

                input.value = value;

                const control = input.closest('.cart-quantity-control');
                const minus = control?.querySelector('[data-cart-step="-1"]');
                const plus = control?.querySelector('[data-cart-step="1"]');

                if (minus) {
                    minus.disabled = value <= min;
                }

                if (plus) {
                    plus.disabled = value >= max;
                }
            }

            document.querySelectorAll('.cart-quantity-control input[name="quantity"]').forEach(function (input) {
                syncStepper(input);
                input.addEventListener('input', function () {
                    syncStepper(input);
                });
            });

            document.querySelectorAll('[data-cart-step]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const control = button.closest('.cart-quantity-control');
                    const input = control?.querySelector('input[name="quantity"]');

                    if (!input) {
                        return;
                    }

                    const step = Number(button.dataset.cartStep || 0);
                    input.value = Number(input.value || input.min || 1) + step;
                    syncStepper(input);
                });
            });

            document.querySelectorAll('.cart-quantity-form').forEach(function (form) {
                form.addEventListener('submit', function () {
                    const button = form.querySelector('.cart-update-button');

                    if (button) {
                        button.disabled = true;
                        button.dataset.originalText = button.textContent.trim();
                        button.innerHTML = 'Mengemaskini...';
                    }
                });
            });

            document.querySelectorAll('.cart-remove-form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!confirm('Buang item ini dari troli?')) {
                        event.preventDefault();
                    }
                });
            });

            document.querySelectorAll('.cart-footer form').forEach(function (form) {
                form.addEventListener('submit', function () {
                    const button = form.querySelector('.cart-checkout-button');

                    if (button) {
                        button.disabled = true;
                        button.dataset.originalText = button.textContent.trim();
                        button.innerHTML = 'Menghantar...';
                    }
                });
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

        .content:has(.student-order-page){background:#F5F8FC}
        .student-order-page{
            --order-navy:#082F59;
            --order-red:#ED1C2E;
            --order-bg:#F5F8FC;
            --order-border:#D8E2EF;
            --order-muted:#5F7189;
            --order-blue:#1D5FD1;
            --order-green:#14875A;
            --order-amber:#D88400;
            width:min(100% - 48px,1660px);
            margin-inline:auto;
            display:grid;
            gap:18px;
            color:var(--order-navy);
        }
        .student-order-page .alert{margin:0}
        .student-order-page .student-hero{
            position:relative;
            min-height:104px;
            display:flex;
            align-items:center;
            margin:0!important;
            padding:22px 28px!important;
            border:1px solid var(--order-border)!important;
            border-left:16px solid var(--order-navy)!important;
            border-radius:10px!important;
            background:#fff!important;
            box-shadow:0 8px 22px rgba(8,47,89,.035)!important;
        }
        .student-order-page .student-hero::before{
            content:"";
            width:4px;
            height:30px;
            margin-right:18px;
            background:var(--order-red);
            flex:0 0 auto;
        }
        .student-order-page .student-hero h2{
            margin:0!important;
            color:#071A34!important;
            font-size:30px!important;
            line-height:1.1!important;
            font-weight:900!important;
            letter-spacing:0!important;
        }
        .student-order-page .student-hero p{
            margin:6px 0 0!important;
            color:#253B57!important;
            font-size:15px!important;
            font-weight:750!important;
        }
        .student-order-page .student-layout{display:grid;gap:18px!important}
        .student-order-page .student-layout > .panel,
        .student-order-page .order-panel{
            overflow:hidden;
            padding:20px!important;
            border:1px solid var(--order-border)!important;
            border-radius:10px!important;
            background:#fff!important;
            box-shadow:0 8px 22px rgba(8,47,89,.035)!important;
        }
        .student-order-page .booking-head{
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:18px;
            margin:0 0 16px!important;
            padding:0!important;
            border-bottom:0!important;
        }
        .student-order-page .booking-head h2,
        .student-order-page .order-list-panel h2{
            position:relative;
            margin:0!important;
            padding-left:34px;
            color:#071A34;
            font-size:22px;
            line-height:1.2;
            font-weight:900;
            letter-spacing:0;
        }
        .student-order-page .booking-head h2::before,
        .student-order-page .order-list-panel h2::before{
            content:"";
            position:absolute;
            left:0;
            top:.55em;
            width:26px;
            height:4px;
            background:var(--order-red);
        }
        .student-order-page .booking-head p{
            margin:6px 0 0;
            color:#253B57;
            font-size:13px;
            font-weight:750;
        }
        .student-order-page .cart-top-button{
            min-height:46px;
            padding:7px 16px 7px 12px;
            border:1px solid #8DB6F7;
            border-radius:8px;
            background:#fff;
            color:var(--order-navy);
            box-shadow:none;
            transform:none;
        }
        .student-order-page .cart-top-button:hover{
            border-color:var(--order-navy);
            background:#F7FAFF;
            transform:none;
        }
        .student-order-page .cart-top-button__icon{
            width:30px;
            height:30px;
            flex-basis:30px;
            border-radius:0;
            background:transparent;
            color:var(--order-navy);
        }
        .student-order-page .cart-top-button__icon svg{width:28px;height:28px}
        .student-order-page .cart-top-button__icon strong{
            top:-8px;
            right:-7px;
            min-width:18px;
            height:18px;
            border:2px solid #fff;
            background:var(--order-red);
            font-size:10px;
        }
        .student-order-page .cart-top-button b{
            margin:0;
            color:var(--order-navy);
            font-size:15px;
            font-weight:900;
        }
        .student-order-page .baju-catalog{
            display:grid;
            grid-template-columns:repeat(4,minmax(0,1fr));
            gap:18px;
            align-items:stretch;
            margin-top:6px;
        }
        .student-order-page .baju-card{
            display:flex;
            flex-direction:column;
            min-width:0;
            height:100%;
            overflow:hidden;
            border:1px solid var(--order-border);
            border-radius:10px!important;
            background:#fff;
            box-shadow:none!important;
            transition:border-color .16s ease, background .16s ease;
        }
        .student-order-page .baju-card:hover,
        .student-order-page .baju-card.is-active{
            border-color:#BFD7FF;
            background:#FCFDFF;
            box-shadow:none!important;
        }
        .student-order-page .baju-card__image{
            width:100%;
            aspect-ratio:4/3;
            max-height:none;
            background:#F3F6FA;
            border-bottom:1px solid var(--order-border);
        }
        .student-order-page .baju-card__image img{
            width:100%;
            height:100%;
            object-fit:contain;
            display:block;
        }
        .student-order-page .baju-card__body{
            display:flex;
            flex:1;
            flex-direction:column;
            gap:10px;
            padding:12px 14px 14px;
        }
        .student-order-page .baju-card__title{min-height:38px}
        .student-order-page .baju-card__title strong,
        .student-order-page .baju-card__body strong{
            color:var(--order-navy);
            font-size:15px;
            line-height:1.25;
            font-weight:900;
        }
        .student-order-page .baju-card__price{
            flex:0 0 auto;
            color:var(--order-red)!important;
            font-size:14px!important;
            font-weight:900!important;
            white-space:nowrap;
        }
        .student-order-page .size-chart-button{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            align-self:flex-start;
            min-height:34px;
            padding:0 12px;
            border:1px solid #BFD0E6;
            border-radius:6px;
            background:#fff;
            color:var(--order-navy);
            font:inherit;
            font-size:12px;
            font-weight:900;
            cursor:pointer;
        }
        .student-order-page .size-chart-button::before{content:'↗';margin-right:7px;color:var(--order-red);font-size:14px}
        .student-order-page .size-chart-button:hover,.student-order-page .size-chart-button:focus-visible{border-color:var(--order-red);background:#FFF8F8;outline:none}
        .student-order-page .baju-card__sizes{
            display:flex;
            flex-wrap:wrap;
            gap:8px;
            margin:0;
            padding:0 0 10px;
            overflow:visible;
            border-bottom:1px solid var(--order-border);
        }
        .student-order-page .size-chip{
            min-width:48px;
            min-height:34px;
            padding:0 12px;
            border:1px solid #BFD0E6;
            border-radius:6px;
            background:#fff;
            color:var(--order-navy);
        }
        .student-order-page .size-chip:hover,
        .student-order-page .size-chip:focus-visible{
            border-color:var(--order-red);
            background:#fff;
            outline:none;
        }
        .student-order-page .size-chip span{font-size:13px;font-weight:900}
        .student-order-page .size-chip.is-active{
            border-color:var(--order-navy)!important;
            background:var(--order-navy)!important;
            color:#fff!important;
        }
        .student-order-page .size-chip.is-disabled,
        .student-order-page .size-chip:disabled{opacity:.45;background:#F8FAFC;cursor:not-allowed}
        .student-order-page .card-cart-form{
            display:grid;
            gap:8px;
            margin-top:auto;
            padding-top:0;
            border-top:0;
        }
        .student-order-page .card-cart-form__pick{
            min-height:34px;
            padding:8px 10px;
            border-radius:6px;
            font-size:12px;
        }
        .student-order-page .card-cart-form__row{
            display:grid;
            grid-template-columns:82px minmax(0,1fr);
            gap:10px;
            align-items:end;
        }
        .student-order-page .card-cart-form label span{
            color:#344D6D;
            font-size:11px;
            font-weight:900;
            text-transform:uppercase;
            letter-spacing:0;
        }
        .student-order-page .card-cart-form input{
            min-height:42px;
            border:1px solid #BFD0E6;
            border-radius:6px;
            color:#071A34;
            font-size:15px;
            font-weight:900;
            text-align:center;
        }
        .student-order-page .card-cart-form input:focus{
            border-color:var(--order-navy);
            outline:2px solid rgba(29,95,209,.14);
            outline-offset:1px;
        }
        .student-order-page .button,
        .student-order-page .card-cart-form__button{
            min-height:42px!important;
            border:1px solid var(--order-red)!important;
            border-radius:6px!important;
            background:var(--order-red)!important;
            color:#fff!important;
            font-size:14px!important;
            font-weight:900!important;
            box-shadow:none!important;
        }
        .student-order-page .button:hover,
        .student-order-page .card-cart-form__button:hover{
            border-color:#C91525!important;
            background:#C91525!important;
        }
        .student-order-page .button:disabled,
        .student-order-page .card-cart-form__button:disabled{
            opacity:.72;
            cursor:wait;
        }
        .student-order-page .order-list-panel{padding:0!important}
        .student-order-page .order-list-panel .panel-pad{
            padding:18px 20px 10px!important;
        }
        .student-order-page .student-orders-table-wrap{
            margin:0 20px 20px;
            overflow-x:auto;
            border:1px solid var(--order-border);
            border-radius:8px;
        }
        .student-order-page .student-orders-table{
            width:100%;
            min-width:900px;
            border-collapse:collapse;
        }
        .student-order-page .student-orders-table th,
        .student-order-page .student-orders-table td{
            padding:12px 16px;
            border-bottom:1px solid var(--order-border);
            color:#253B57;
            font-size:13px;
            text-align:left;
            vertical-align:middle;
        }
        .student-order-page .student-orders-table thead th{
            background:#F0F6FF;
            color:var(--order-navy);
            font-size:12px;
            font-weight:900;
            text-transform:none;
            letter-spacing:0;
        }
        .student-order-page .student-orders-table tbody tr:last-child td{border-bottom:0}
        .student-order-page .student-orders-table td:first-child,
        .student-order-page .student-orders-table td:nth-child(3),
        .student-order-page .student-orders-table td:nth-child(4){
            color:#31527B;
            font-weight:800;
            white-space:nowrap;
        }
        .student-order-page .student-orders-table td:nth-child(2){min-width:260px}
        .student-order-page .student-orders-table td:last-child{white-space:nowrap}
        .student-order-page .order-status{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            min-height:26px;
            min-width:74px;
            padding:0 11px;
            border-radius:999px;
            font-size:12px;
            font-weight:900;
            white-space:nowrap;
        }
        .student-order-page .order-status--sudah_ambil{background:#E7F6EF;color:var(--order-green)}
        .student-order-page .order-status--belum_ambil{background:#FFF4DB;color:var(--order-amber)}
        .student-order-page .empty-state{
            padding:24px;
            border:1px dashed var(--order-border);
            border-radius:8px;
            background:#fff;
            color:var(--order-muted);
            font-weight:800;
            text-align:center;
        }
        .student-order-page .cart-dialog .cart-panel{border-radius:12px!important}
        .student-order-page .cart-dialog .cart-logo{background:var(--order-navy)}
        .student-order-page .cart-dialog .cart-update-button{color:var(--order-navy)}

        @media (max-width:1400px){
            .student-order-page .baju-catalog{grid-template-columns:repeat(3,minmax(0,1fr))}
        }
        @media (max-width:1000px){
            .student-order-page{width:min(100% - 32px,1660px)}
            .student-order-page .baju-catalog{grid-template-columns:repeat(2,minmax(0,1fr))}
            .student-order-page .booking-head{align-items:stretch}
            .student-order-page .cart-top-button{align-self:flex-start}
        }
        @media (max-width:640px){
            .student-order-page{width:100%;gap:14px}
            .student-order-page .student-hero{
                min-height:0;
                padding:20px!important;
                border-left-width:10px!important;
                border-radius:8px!important;
            }
            .student-order-page .student-hero h2{font-size:26px!important}
            .student-order-page .student-layout > .panel,
            .student-order-page .order-panel{padding:16px!important;border-radius:8px!important}
            .student-order-page .booking-head{flex-direction:column;gap:12px}
            .student-order-page .cart-top-button{width:100%;justify-content:center}
            .student-order-page .baju-catalog{grid-template-columns:1fr;gap:14px}
            .student-order-page .baju-card__image{aspect-ratio:4/3}
            .student-order-page .card-cart-form__row{grid-template-columns:82px minmax(0,1fr)}
            .student-order-page .order-list-panel{padding:0!important}
            .student-order-page .student-orders-table-wrap{margin:0 16px 16px}
            .student-order-page .order-list-panel .panel-pad{padding:16px!important}
        }

        .student-order-page .cart-dialog{
            width:min(780px,calc(100vw - 56px));
            max-width:780px;
            max-height:min(82dvh,720px);
            padding:0;
            border:1px solid #D7E2EF;
            border-radius:12px;
            background:#fff;
            overflow:hidden;
            box-shadow:0 18px 42px rgba(8,47,89,.16);
        }
        .student-order-page .size-chart-dialog{
            width:min(760px,calc(100vw - 40px));
            max-width:760px;
            max-height:min(88dvh,820px);
            padding:0;
            border:1px solid #D7E2EF;
            border-radius:12px;
            background:#fff;
            overflow:hidden;
            box-shadow:0 18px 42px rgba(8,47,89,.20);
        }
        .student-order-page .size-chart-dialog::backdrop{background:rgba(8,47,89,.26)}
        .student-order-page .size-chart-panel{display:grid;grid-template-rows:auto minmax(0,1fr);max-height:min(88dvh,820px);background:#fff}
        .student-order-page .size-chart-panel__head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:20px 22px;border-bottom:4px solid var(--order-red);background:var(--order-navy)}
        .student-order-page .size-chart-panel__head h2{margin:0;color:#fff;font-size:22px;font-weight:900}
        .student-order-page .size-chart-panel__head p{margin:5px 0 0;color:rgba(255,255,255,.8);font-size:13px;font-weight:700}
        .student-order-page .size-chart-dialog__close{display:grid;place-items:center;width:38px;height:38px;flex:0 0 38px;padding:0;border:1px solid rgba(255,255,255,.35);border-radius:8px;background:rgba(255,255,255,.06);color:#fff;font-size:28px;line-height:1;cursor:pointer}
        .student-order-page .size-chart-dialog__close:hover,.student-order-page .size-chart-dialog__close:focus-visible{background:#fff;color:var(--order-navy);outline:none}
        .student-order-page .size-chart-panel__image{min-height:0;overflow:auto;padding:20px;background:#F5F8FC}
        .student-order-page .size-chart-panel__image img{display:block;width:100%;height:auto;max-height:calc(88dvh - 144px);object-fit:contain;margin:auto;border:1px solid #D7E2EF;border-radius:8px;background:#fff}
        .student-order-page .cart-dialog::backdrop{
            background:rgba(8,47,89,.18);
        }
        .student-order-page .cart-dialog .cart-panel{
            display:grid;
            grid-template-rows:auto minmax(0,1fr) auto;
            width:100%;
            max-height:min(82dvh,720px);
            padding:0!important;
            border:0!important;
            border-radius:12px!important;
            background:#fff;
            overflow:hidden;
            overflow-y:hidden!important;
            scrollbar-gutter:auto!important;
            box-shadow:none!important;
        }
        .student-order-page .cart-shell-head{
            position:relative;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:14px;
            padding:18px 22px 20px;
            border-bottom:5px solid var(--order-red);
            border-radius:12px 12px 0 0;
            background:var(--order-navy);
        }
        .student-order-page .cart-title{
            display:flex;
            align-items:center;
            gap:14px;
            min-width:0;
        }
        .student-order-page .cart-title h2{
            margin:0;
            color:#fff;
            font-size:24px;
            line-height:1.12;
            font-weight:900;
            letter-spacing:0;
        }
        .student-order-page .cart-title p{
            margin:5px 0 0;
            color:rgba(255,255,255,.78);
            font-size:14px;
            font-weight:800;
        }
        .student-order-page .cart-logo{
            display:grid;
            place-items:center;
            width:46px;
            height:46px;
            flex:0 0 46px;
            border:1px solid rgba(255,255,255,.28);
            border-radius:10px;
            background:rgba(255,255,255,.08);
            color:#fff;
            box-shadow:none;
        }
        .student-order-page .cart-logo svg{
            width:25px;
            height:25px;
        }
        .student-order-page .cart-dialog-close{
            display:grid;
            place-items:center;
            width:42px;
            height:42px;
            flex:0 0 42px;
            border:1px solid rgba(255,255,255,.32);
            border-radius:10px;
            background:rgba(255,255,255,.06);
            color:#fff;
            font-size:30px;
            font-weight:700;
            line-height:1;
            cursor:pointer;
        }
        .student-order-page .cart-dialog-close:hover,
        .student-order-page .cart-dialog-close:focus-visible{
            background:#fff;
            color:var(--order-navy);
            outline:none;
        }
        .student-order-page .cart-list{
            display:grid;
            min-height:0;
            overflow-y:auto;
            background:#fff;
        }
        .student-order-page .cart-item{
            display:grid;
            grid-template-columns:minmax(240px,1fr) minmax(330px,auto);
            gap:18px;
            align-items:center;
            padding:16px 22px;
            border-bottom:1px solid #D7E2EF;
            background:#fff;
        }
        .student-order-page .cart-product{
            display:grid;
            grid-template-columns:68px minmax(0,1fr);
            gap:16px;
            align-items:center;
            min-width:0;
        }
        .student-order-page .cart-product__thumb{
            width:68px;
            height:68px;
            border:1px solid #D7E2EF;
            border-radius:10px;
            background:#F4F7FB;
            overflow:hidden;
            color:var(--order-navy);
        }
        .student-order-page .cart-product__thumb img{
            width:100%;
            height:100%;
            object-fit:contain;
            display:block;
        }
        .student-order-page .cart-product strong{
            display:block;
            color:var(--order-navy);
            font-size:17px;
            line-height:1.25;
            font-weight:900;
        }
        .student-order-page .cart-product span{
            display:block;
            margin-top:4px;
            color:#3B5374;
            font-size:14px;
            font-weight:750;
        }
        .student-order-page .cart-stock-badge{
            display:inline-flex;
            align-items:center;
            gap:8px;
            min-height:26px;
            margin-top:8px;
            padding:0 10px;
            border-radius:8px;
            background:#DDF7E8;
            color:#0D8A4B;
            font-style:normal;
            font-size:12px;
            font-weight:900;
        }
        .student-order-page .cart-stock-badge svg{
            width:16px;
            height:16px;
            flex:0 0 auto;
        }
        .student-order-page .cart-quantity-form{
            display:grid;
            gap:8px;
            margin:0;
        }
        .student-order-page .cart-actions{
            display:grid;
            grid-template-columns:minmax(0,1fr) 100px;
            gap:10px;
            align-items:end;
            justify-self:end;
        }
        .student-order-page .cart-quantity-form label{
            color:#3B5374;
            font-size:12px;
            font-weight:900;
            letter-spacing:.02em;
            text-transform:uppercase;
        }
        .student-order-page .cart-quantity-control{
            display:grid;
            grid-template-columns:36px 46px 36px 112px;
            gap:0;
            align-items:center;
        }
        .student-order-page .cart-stepper-button,
        .student-order-page .cart-quantity-control input{
            width:36px;
            height:38px;
            border:1px solid #BFD0E6;
            background:#fff;
            color:var(--order-navy);
            font:inherit;
            font-size:20px;
            font-weight:900;
            text-align:center;
        }
        .student-order-page .cart-stepper-button:first-child{
            border-radius:8px 0 0 8px;
        }
        .student-order-page .cart-quantity-control input{
            width:46px;
            border-inline:0;
            border-radius:0;
            font-size:16px;
            -moz-appearance:textfield;
        }
        .student-order-page .cart-quantity-control input::-webkit-outer-spin-button,
        .student-order-page .cart-quantity-control input::-webkit-inner-spin-button{
            margin:0;
            -webkit-appearance:none;
        }
        .student-order-page .cart-stepper-button:nth-of-type(2){
            border-radius:0 8px 8px 0;
        }
        .student-order-page .cart-stepper-button:hover:not(:disabled),
        .student-order-page .cart-stepper-button:focus-visible{
            background:#EAF2FF;
            color:#0B5ED7;
            outline:none;
        }
        .student-order-page .cart-stepper-button:disabled{
            background:#F4F7FB;
            color:#8BA1BC;
            cursor:not-allowed;
        }
        .student-order-page .cart-update-button,
        .student-order-page .cart-remove-button{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            width:100%;
            min-height:38px;
            padding:0 12px;
            border-radius:8px;
            font:inherit;
            font-size:13px;
            font-weight:900;
            cursor:pointer;
            white-space:nowrap;
        }
        .student-order-page .cart-update-button{
            border:1px solid #C7D5E7;
            background:#F8FBFF;
            color:var(--order-navy);
            width:auto;
            margin-left:12px;
        }
        .student-order-page .cart-update-button:hover,
        .student-order-page .cart-update-button:focus-visible{
            border-color:#9DB2CC;
            background:#EEF4FB;
            outline:none;
        }
        .student-order-page .cart-remove-form{
            margin:0;
        }
        .student-order-page .cart-remove-button{
            border:1px solid #F5B9B9;
            background:#FDE8E8;
            color:#E00012;
        }
        .student-order-page .cart-remove-button:hover,
        .student-order-page .cart-remove-button:focus-visible{
            border-color:#ED1C2E;
            background:#FDE8E8;
            outline:none;
        }
        .student-order-page .cart-update-button svg,
        .student-order-page .cart-remove-button svg,
        .student-order-page .cart-checkout-button svg,
        .student-order-page .cart-footer-count svg{
            width:20px;
            height:20px;
            flex:0 0 auto;
        }
        .student-order-page .cart-footer{
            display:grid;
            grid-template-columns:minmax(0,1fr) auto auto;
            align-items:center;
            gap:12px;
            padding:14px 22px;
            border-top:1px solid #D7E2EF;
            background:#fff;
        }
        .student-order-page .cart-footer-count{
            display:inline-flex;
            align-items:center;
            gap:10px;
            color:var(--order-navy);
            font-size:14px;
            font-weight:900;
        }
        .student-order-page .cart-footer-count svg{
            width:22px;
            height:22px;
        }
        .student-order-page .cart-footer-total{
            display:grid;
            gap:4px;
            min-width:156px;
            min-height:48px;
            align-content:center;
            padding:8px 14px;
            border:1px solid #D7E2EF;
            border-radius:10px;
            background:#F8FBFF;
        }
        .student-order-page .cart-footer-total span{
            color:#3B5374;
            font-size:12px;
            font-weight:900;
            text-transform:uppercase;
        }
        .student-order-page .cart-footer-total strong{
            color:var(--order-navy);
            font-size:24px;
            line-height:1;
            font-weight:900;
        }
        .student-order-page .cart-footer form{
            margin:0;
        }
        .student-order-page .cart-checkout-button{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:12px;
            min-height:48px!important;
            padding:0 20px!important;
            border-color:var(--order-red)!important;
            border-radius:10px!important;
            background:var(--order-red)!important;
            color:#fff!important;
            font-size:15px!important;
            font-weight:900!important;
            white-space:nowrap;
        }
        .student-order-page .cart-checkout-button:hover,
        .student-order-page .cart-checkout-button:focus-visible{
            border-color:#C91525!important;
            background:#C91525!important;
            outline:none;
        }
        .student-order-page .cart-checkout-button:disabled,
        .student-order-page .cart-update-button:disabled{
            opacity:.68;
            cursor:wait;
        }
        .student-order-page .cart-empty{
            padding:34px 20px;
        }

        @media (max-width:980px){
            .student-order-page .cart-item{
                grid-template-columns:1fr;
                gap:16px;
            }
            .student-order-page .cart-actions{
                justify-self:stretch;
            }
            .student-order-page .cart-quantity-control{
                grid-template-columns:40px 52px 40px minmax(116px,1fr);
            }
            .student-order-page .cart-remove-form{
                justify-self:stretch;
            }
            .student-order-page .cart-footer{
                grid-template-columns:1fr auto;
            }
            .student-order-page .cart-footer-count{
                grid-column:1 / -1;
            }
        }
        @media (max-width:620px){
            .student-order-page .cart-dialog{
                width:calc(100vw - 18px);
                max-width:calc(100vw - 18px);
                max-height:calc(100dvh - 18px);
                border-radius:12px;
            }
            .student-order-page .cart-dialog .cart-panel{
                max-height:calc(100dvh - 18px);
            }
            .student-order-page .cart-shell-head{
                padding:18px 18px 20px;
            }
            .student-order-page .cart-title{
                align-items:flex-start;
                padding-right:50px;
            }
            .student-order-page .cart-title h2{
                font-size:24px;
            }
            .student-order-page .cart-title p{
                font-size:13px;
            }
            .student-order-page .cart-logo{
                width:46px;
                height:46px;
                flex-basis:46px;
            }
            .student-order-page .cart-dialog-close{
                position:absolute;
                top:16px;
                right:16px;
                width:42px;
                height:42px;
                font-size:30px;
            }
            .student-order-page .cart-item{
                padding:18px;
            }
            .student-order-page .cart-product{
                grid-template-columns:76px minmax(0,1fr);
                gap:14px;
            }
            .student-order-page .cart-product__thumb{
                width:76px;
                height:76px;
            }
            .student-order-page .cart-product strong{
                font-size:17px;
            }
            .student-order-page .cart-quantity-control{
                grid-template-columns:42px minmax(0,1fr) 42px;
                gap:0;
            }
            .student-order-page .cart-actions{
                grid-template-columns:1fr 1fr;
                gap:10px;
            }
            .student-order-page .cart-update-button{
                grid-column:1 / -1;
                margin-left:0;
                width:100%;
            }
            .student-order-page .cart-remove-form,
            .student-order-page .cart-remove-button{
                width:100%;
            }
            .student-order-page .cart-footer{
                grid-template-columns:1fr;
                padding:16px 18px;
            }
            .student-order-page .cart-footer-total,
            .student-order-page .cart-footer form,
            .student-order-page .cart-checkout-button{
                width:100%;
            }
        }
    </style>
@endpush
