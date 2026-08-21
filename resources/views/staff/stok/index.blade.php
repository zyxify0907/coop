@extends('layouts.app')

@section('title', 'Stok')
@section('page-title', 'Inventori Stok')
@section('page-subtitle', 'Urus dan kemaskini inventori koperasi dengan mudah.')

@section('content')
    {{-- Error Alert with Dismiss --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <div class="alert-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <span>{{ $errors->first() }}</span>
            <button class="alert-close" type="button" aria-label="Close notification">&times;</button>
        </div>
    @endif

    {{-- Success Message (if any) --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <div class="alert-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <span>{{ session('success') }}</span>
            <button class="alert-close" type="button" aria-label="Close notification">&times;</button>
        </div>
    @endif

    {{-- Add Item Form --}}
    <section class="panel" style="border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04); margin-bottom: 24px; overflow: hidden;">
        <div style="padding: 20px 24px; background: var(--surface); border-bottom: 1px solid var(--line);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: var(--secondary-soft); display: grid; place-items: center; color: var(--secondary);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="16"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                </div>
                <div>
                    <h2 style="margin: 0; font-size: 18px; font-weight: 600; color: #0F172A;">Tambah Item Stok</h2>
                    <p style="margin: 4px 0 0; font-size: 14px; color: #64748B;">Masukkan maklumat item baru ke dalam inventori.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('staff.stok.store') }}" style="padding: 24px;">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 16px; align-items: end;">
                <div>
                    <label style="display: block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Nama Item</label>
                    <input name="nama_item" required placeholder="Contoh: Buku Latihan" 
                           style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 14px; transition: all 0.2s;"
                           onfocus="this.style.borderColor='var(--secondary)'; this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.12)'"
                           onblur="this.style.borderColor='#CBD5E1'; this.style.boxShadow='none'">
                </div>
                <div>
                    <label style="display: block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Kuantiti</label>
                    <input name="quantity" type="number" min="0" required placeholder="0" 
                           style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 14px; transition: all 0.2s;"
                           onfocus="this.style.borderColor='var(--secondary)'; this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.12)'"
                           onblur="this.style.borderColor='#CBD5E1'; this.style.boxShadow='none'">
                </div>
                <div>
                    <label style="display: block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px;">Harga (RM)</label>
                    <input name="harga" type="number" min="0" step="0.01" required placeholder="0.00" 
                           style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 14px; transition: all 0.2s;"
                           onfocus="this.style.borderColor='var(--secondary)'; this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.12)'"
                           onblur="this.style.borderColor='#CBD5E1'; this.style.boxShadow='none'">
                </div>
                <div>
                    <button class="button" type="submit" 
                            style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 24px; background: var(--secondary); color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s; white-space: nowrap;"
                            onmouseover="this.style.background='var(--secondary-hover)'; this.style.boxShadow='0 4px 12px rgba(37,99,235,.24)'"
                            onmouseout="this.style.background='var(--secondary)'; this.style.boxShadow='none'">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Tambah Stok
                    </button>
                </div>
            </div>
        </form>
    </section>

    {{-- Inventory List --}}
    <section class="panel" style="border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04); overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid #E2E8F0; background: #F8FAFC; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <h2 style="margin: 0; font-size: 18px; font-weight: 600; color: #0F172A;">Senarai Stok</h2>
                <p style="margin: 4px 0 0; font-size: 14px; color: #64748B;">Jumlah item dalam inventori: <strong style="color: #0F172A;">{{ $items->count() }}</strong></p>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <span class="badge" style="background: var(--success-soft); color: var(--success); padding: 4px 12px; border-radius: 100px; font-size: 13px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px;">
                    <span style="width: 8px; height: 8px; background: var(--success); border-radius: 50%; display: inline-block;"></span>
                    Aktif
                </span>
                <span class="badge" style="background: #eff8ff; color: #175cd3; padding: 4px 12px; border-radius: 100px; font-size: 13px; font-weight: 500;">
                    {{ $items->total() }} rekod
                </span>
            </div>
        </div>

        <div style="overflow-x: auto; padding: 0 4px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px; min-width: 700px;">
                <thead style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0;">
                    <tr>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 500; color: #475569; letter-spacing: 0.02em; text-transform: uppercase; font-size: 12px;">#</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 500; color: #475569; letter-spacing: 0.02em; text-transform: uppercase; font-size: 12px;">Nama Item</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 500; color: #475569; letter-spacing: 0.02em; text-transform: uppercase; font-size: 12px;">Kuantiti</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 500; color: #475569; letter-spacing: 0.02em; text-transform: uppercase; font-size: 12px;">Harga (RM)</th>
                        <th style="padding: 12px 16px; text-align: right; font-weight: 500; color: #475569; letter-spacing: 0.02em; text-transform: uppercase; font-size: 12px; width: 240px;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr style="border-bottom: 1px solid #E2E8F0; transition: background 0.15s; background: white;" 
                            onmouseover="this.style.background='#F8FAFC'" 
                            onmouseout="this.style.background='white'">
                            <form id="stok-update-{{ $item->item_id }}" method="POST" action="{{ route('staff.stok.update', $item->item_id) }}" style="display: contents;">
                                @csrf
                                @method('PUT')
                            </form>
                            <td style="padding: 12px 16px; color: #94A3B8; font-weight: 500; width: 40px;">
                                {{ $loop->iteration }}
                            </td>
                            <td style="padding: 12px 16px;">
                                <input form="stok-update-{{ $item->item_id }}" name="nama_item" value="{{ $item->nama_item }}" required
                                       style="width: 100%; padding: 6px 12px; border: 1px solid transparent; border-radius: 6px; font-size: 14px; background: transparent; transition: all 0.2s;"
                                       onfocus="this.style.borderColor='var(--secondary)'; this.style.background='white'; this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.12)'"
                                       onblur="this.style.borderColor='transparent'; this.style.background='transparent'; this.style.boxShadow='none'">
                            </td>
                            <td style="padding: 12px 16px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <input form="stok-update-{{ $item->item_id }}" name="quantity" type="number" min="0" value="{{ $item->quantity }}" required
                                           style="width: 80px; padding: 6px 12px; border: 1px solid transparent; border-radius: 6px; font-size: 14px; background: transparent; transition: all 0.2s;"
                                           onfocus="this.style.borderColor='var(--secondary)'; this.style.background='white'; this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.12)'"
                                           onblur="this.style.borderColor='transparent'; this.style.background='transparent'; this.style.boxShadow='none'">
                                    @if($item->quantity <= 5 && $item->quantity > 0)
                                        <span style="background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 100px; font-size: 11px; font-weight: 600; white-space: nowrap;">Low Stock</span>
                                    @elseif($item->quantity <= 0)
                                        <span style="background: var(--primary-soft); color: var(--primary-dark); padding: 2px 8px; border-radius: 100px; font-size: 11px; font-weight: 600; white-space: nowrap;">Out of Stock</span>
                                    @else
                                        <span style="background: var(--success-soft); color: var(--success); padding: 2px 8px; border-radius: 100px; font-size: 11px; font-weight: 600; white-space: nowrap;">In Stock</span>
                                    @endif
                                </div>
                            </td>
                            <td style="padding: 12px 16px;">
                                <span style="font-weight: 600; color: #0F172A;">RM</span>
                                <input form="stok-update-{{ $item->item_id }}" name="harga" type="number" min="0" step="0.01" value="{{ $item->harga }}" required
                                       style="width: 100px; padding: 6px 12px; border: 1px solid transparent; border-radius: 6px; font-size: 14px; background: transparent; transition: all 0.2s;"
                                       onfocus="this.style.borderColor='var(--secondary)'; this.style.background='white'; this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.12)'"
                                       onblur="this.style.borderColor='transparent'; this.style.background='transparent'; this.style.boxShadow='none'">
                            </td>
                            <td style="padding: 12px 16px; text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end; flex-wrap: wrap;">
                                    <button class="button" form="stok-update-{{ $item->item_id }}" type="submit" 
                                            style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; background: var(--secondary); color: white; border: none; border-radius: 6px; font-weight: 500; font-size: 13px; cursor: pointer; transition: all 0.2s;"
                                            onmouseover="this.style.background='var(--secondary-hover)'; this.style.boxShadow='0 2px 8px rgba(37,99,235,.20)'"
                                            onmouseout="this.style.background='var(--secondary)'; this.style.boxShadow='none'">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                                            <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                                        </svg>
                                        Kemaskini
                                    </button>
                                    <form method="POST" action="{{ route('staff.stok.destroy', $item->item_id) }}" 
                                          onsubmit="return confirm('Padam item stok ini?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; background: var(--primary-soft); color: var(--danger); border: 1px solid var(--danger-soft); border-radius: 6px; font-weight: 500; font-size: 13px; cursor: pointer; transition: all 0.2s;"
                                                onmouseover="this.style.background='var(--danger-soft)'; this.style.borderColor='var(--danger-soft)'"
                                                onmouseout="this.style.background='var(--primary-soft)'; this.style.borderColor='var(--danger-soft)'">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            </svg>
                                            Padam
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 48px 16px; text-align: center; color: #64748B; background: white;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                                    </svg>
                                    <span style="font-size: 15px; font-weight: 500;">Tiada stok dalam inventori.</span>
                                    <span style="font-size: 13px; color: #94A3B8;">Gunakan borang di atas untuk menambah item baru.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div style="padding: 16px 24px; border-top: 1px solid #E2E8F0; background: #F8FAFC; border-radius: 0 0 16px 16px;">
                {{ $items->links() }}
            </div>
        @endif
    </section>

    {{-- Inline Styles for Alert Dismissible --}}
    <style>
        .alert-dismissible {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            animation: slideDown 0.3s ease-out;
            position: relative;
        }
        .alert-dismissible .alert-icon {
            flex-shrink: 0;
        }
        .alert-dismissible .alert-close {
            background: transparent;
            border: none;
            font-size: 22px;
            line-height: 1;
            cursor: pointer;
            margin-left: auto;
            padding: 0 4px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        .alert-dismissible .alert-close:hover {
            opacity: 1;
        }
        .alert-success {
            border: 1px solid #abf0d5;
            background: var(--success-soft);
            color: var(--success);
        }
        .alert-success .alert-close {
            color: var(--success);
        }
        .alert-danger {
            border: 1px solid var(--danger-soft);
            background: var(--primary-soft);
            color: var(--primary-dark);
        }
        .alert-danger .alert-close {
            color: var(--primary-dark);
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Focus styles for inputs */
        input:focus {
            outline: none;
        }

        /* Responsive adjustments */
        @media (max-width: 900px) {
            .panel form > div {
                grid-template-columns: 1fr 1fr !important;
            }
            .panel form > div > div:last-child {
                grid-column: span 2;
            }
            table {
                font-size: 13px;
            }
            th, td {
                padding: 10px 12px !important;
            }
            td input[type="number"] {
                width: 60px !important;
            }
        }

        @media (max-width: 600px) {
            .panel form > div {
                grid-template-columns: 1fr !important;
            }
            .panel form > div > div:last-child {
                grid-column: span 1;
            }
            .panel form button {
                width: 100%;
                justify-content: center;
            }
            table {
                font-size: 12px;
            }
            th, td {
                padding: 8px 10px !important;
            }
            td input {
                font-size: 12px !important;
                padding: 4px 8px !important;
            }
            td input[type="number"] {
                width: 50px !important;
            }
            td .button, td form button {
                padding: 4px 10px !important;
                font-size: 11px !important;
            }
        }
    </style>

    {{-- Auto-dismiss alerts after 5 seconds --}}
    @if ($errors->any() || session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const alerts = document.querySelectorAll('.alert-dismissible');
                alerts.forEach(alert => {
                    setTimeout(() => {
                        alert.style.transition = 'opacity 0.5s, transform 0.5s';
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-10px)';
                        setTimeout(() => alert.remove(), 500);
                    }, 5000);

                    const closeBtn = alert.querySelector('.alert-close');
                    if (closeBtn) {
                        closeBtn.addEventListener('click', () => {
                            alert.style.opacity = '0';
                            alert.style.transform = 'translateY(-10px)';
                            setTimeout(() => alert.remove(), 500);
                        });
                    }
                });
            });
        </script>
    @endif
@endsection
