@extends('layouts.app')

@section('title', 'Tempahan')
@section('page-title', 'Tempahan Baju')
@section('page-subtitle', 'Buat dan semak tempahan anda.')

@section('content')
    @php
        $groupedItems = collect($items)->groupBy('nama_item');
    @endphp

    <section class="student-hero">
        <div>
            <h2>Tempahan Baju</h2>
            <p>Pilih baju dan size yang tersedia, kemudian semak status ambil tempahan anda di sini.</p>
        </div>
        <!-- <div class="student-actions">
            <a class="link-button secondary" href="{{ route('auth.dashboard') }}">Dashboard</a>
            <a class="link-button secondary" href="{{ route('student.profile') }}">Profil</a>
        </div> -->
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
                <div class="booking-tip">Troli tempahan</div>
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
                    <article class="baju-card {{ old('item_id') ? 'is-active' : '' }}" data-group-card="{{ $itemName }}">
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
                        </div>
                    </article>
                @empty
                    <div class="empty-state">Tiada baju tersedia sekarang.</div>
                @endforelse
            </div>

            <form method="POST" action="{{ route('student.tempahan.cart.add') }}" class="booking-form">
                @csrf
                <div class="booking-bar">
                    <input id="item_id" name="item_id" type="hidden" value="{{ old('item_id') }}" required>
                    <div class="booking-pick" id="booking-pick">
                        Belum pilih size
                    </div>
                    <div class="booking-field booking-field--small">
                        <label for="quantity">Kuantiti</label>
                        <input id="quantity" name="quantity" type="number" min="1" value="{{ old('quantity', 1) }}" required>
                    </div>
                    <button class="button booking-submit" type="submit">Tambah ke Troli</button>
                </div>
            </form>
        </section>

        <section class="panel" style="overflow:hidden">
            <div class="panel-pad cart-head">
                <div>
                    <h2 style="margin:0">Troli Tempahan</h2>
                    <p>Semak pilihan baju sebelum dihantar ke senarai tempahan.</p>
                </div>
                @if ($cartItems->isNotEmpty())
                    <form method="POST" action="{{ route('student.tempahan.cart.checkout') }}">
                        @csrf
                        <button class="button" type="submit">Hantar Troli</button>
                    </form>
                @endif
            </div>
            <div class="table-wrap student-orders-table-wrap">
                <table class="student-orders-table cart-table">
                    <thead><tr><th>Nama Baju</th><th>Size</th><th>Kuantiti</th><th>Tindakan</th></tr></thead>
                    <tbody>
                        @forelse ($cartItems as $cartItem)
                            <tr>
                                <td>{{ $cartItem->nama_item }}</td>
                                <td>{{ $cartItem->saiz ?? '-' }}</td>
                                <td>{{ $cartItem->quantity_ordered }}</td>
                                <td>
                                    <form method="POST" action="{{ route('student.tempahan.cart.remove', $cartItem->id_item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button danger-button" type="submit">Buang</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="empty-state">Troli masih kosong. Pilih baju dan tambah ke troli dahulu.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

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
            const itemInput = document.getElementById('item_id');
            const quantityInput = document.getElementById('quantity');
            const bookingPick = document.getElementById('booking-pick');
            const itemCards = document.querySelectorAll('[data-group-card]');
            const sizeButtons = document.querySelectorAll('[data-select-item]');

            function getActiveButton() {
                if (!itemInput) {
                    return null;
                }

                return Array.from(sizeButtons).find(function (button) {
                    return button.dataset.selectItem === itemInput.value;
                }) || null;
            }

            function updateOrderSummary() {
                if (!itemInput || !quantityInput) {
                    return;
                }

                const activeButton = getActiveButton();
                const quantity = Math.max(1, Number(quantityInput.value || 1));

                if (!activeButton) {
                    if (bookingPick) {
                        bookingPick.textContent = 'Belum pilih size';
                        bookingPick.classList.remove('is-selected');
                    }
                    quantityInput.removeAttribute('max');
                    return;
                }

                const stock = Number(activeButton.dataset.stock || 0);
                quantityInput.max = stock;

                if (bookingPick) {
                    bookingPick.textContent = `${activeButton.dataset.groupName} - Size ${activeButton.dataset.size}`;
                    bookingPick.classList.add('is-selected');
                }

                sizeButtons.forEach(function (button) {
                    button.classList.toggle('is-active', activeButton && button.dataset.selectItem === activeButton.dataset.selectItem);
                });

                itemCards.forEach(function (card) {
                    const hasActiveSize = card.querySelector('.size-chip.is-active');
                    card.classList.toggle('is-active', !!hasActiveSize);
                });
            }

            quantityInput?.addEventListener('input', updateOrderSummary);
            sizeButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    itemInput.value = this.dataset.selectItem;

                    updateOrderSummary();
                    bookingPick?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            });
            updateOrderSummary();
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
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 2px;
        }
        .size-chip {
            display: grid;
            gap: 2px;
            min-width: 78px;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: #fff;
            color: #0f172a;
            text-align: left;
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
        .booking-form {
            border-top: 1px solid #e2e8f0;
            padding-top: 18px;
        }
        .booking-bar {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 120px 180px;
            gap: 14px;
            align-items: end;
        }
        .booking-pick {
            min-height: 44px;
            padding: 10px 12px;
            border: 1px dashed #bfdbfe;
            border-radius: 10px;
            background: #f8fbff;
            color: var(--secondary);
            display: flex;
            align-items: center;
            font-size: 13px;
            font-weight: 700;
        }
        .booking-pick.is-selected {
            border-style: solid;
            border-color: var(--primary);
            background: var(--secondary-soft);
            color: var(--secondary);
            box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.08);
        }
        .booking-field {
            display: grid;
            gap: 8px;
        }
        .booking-field label {
            margin: 0;
            color: #334155;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .booking-field select,
        .booking-field input {
            width: 100%;
            min-height: 44px;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 10px 12px;
            background: #fff;
            font: inherit;
        }
        .booking-submit {
            width: 100%;
            min-height: 44px;
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
            .booking-bar {
                grid-template-columns: 1fr;
            }
            .baju-card__title {
                flex-direction: column;
                align-items: flex-start;
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
        .booking-form{margin-top:24px!important;border-top:1px solid var(--line)!important;padding-top:22px!important}
        .booking-pick{border-color:var(--line)!important;background:var(--surface-soft)!important;color:var(--muted)!important}
        .booking-pick.is-selected{border-color:var(--secondary)!important;background:var(--secondary-soft)!important;color:var(--secondary)!important}
        table{width:100%;border-collapse:separate;border-spacing:0}
        thead th{background:var(--surface-soft);color:var(--muted);font-size:12px;text-transform:uppercase;letter-spacing:.02em}
        th,td{padding:14px 16px;border-bottom:1px solid var(--line);text-align:left}
        .student-orders-table-wrap{border:0;border-radius:0}
        .student-orders-table{min-width:720px}
    </style>
@endpush
