@extends('layouts.app')

@section('title', 'Jualan')
@section('page-title', 'Jualan')
@section('page-subtitle', 'Rekod jualan dan kurangkan stok secara automatik.')

@section('content')
    {{-- Success Message --}}
    @if (session('success'))
        <div class="alert alert-success" role="alert" style="border-color:#86efac;background:#f0fdf4;color:#166534;padding:12px 16px;border-radius:8px;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m9 12 2 2 4-4"/>
                <circle cx="12" cy="12" r="10"/>
            </svg>
            <span>{{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.style.display='none'" style="margin-left:auto;background:none;border:none;font-size:20px;cursor:pointer;color:#166534;">&times;</button>
        </div>
    @endif

    {{-- Error Messages --}}
    @if ($errors->any())
        <div class="alert alert-danger" role="alert" style="border-color:var(--danger-soft);background:var(--primary-soft);color:var(--primary-dark);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M10.3 3.9 2.5 17.2A2 2 0 0 0 4.2 20h15.6a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0Z"/>
                    <path d="M12 9v4"/>
                    <path d="M12 17h.01"/>
                </svg>
                <strong>Ralat:</strong>
            </div>
            <ul style="margin:4px 0 0 32px;padding:0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Sales Form --}}
    <section class="panel panel-pad" style="margin-bottom:24px;background:var(--surface);border-radius:12px;border:1px solid var(--line);box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
            <div>
                <h2 style="margin:0;font-size:20px;font-weight:600;color:#0F172A;">Rekod Jualan</h2>
                <p style="margin:4px 0 0;font-size:14px;color:#64748B;">Isi maklumat jualan untuk mengurangkan stok secara automatik</p>
            </div>
            <span style="font-size:13px;background:#E2E8F0;padding:4px 12px;border-radius:20px;color:#475569;">Stok akan dikurangkan</span>
        </div>

        <form method="POST" action="{{ route('staff.jualan.store') }}" class="form-grid" id="salesForm" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;align-items:end;">
            @csrf
            
            {{-- Item Selection --}}
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label for="item_id" style="font-weight:500;font-size:14px;color:#334155;">Item <span style="color:var(--danger);">*</span></label>
                <select name="item_id" id="item_id" required style="padding:10px 12px;border:2px solid #E2E8F0;border-radius:8px;font-size:14px;background:white;transition:border-color 0.2s;width:100%;" onfocus="this.style.borderColor='var(--secondary)'" onblur="this.style.borderColor='#E2E8F0'">
                    <option value="">Pilih item</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->item_id }}" data-price="{{ $item->harga }}" data-stock="{{ $item->stok ?? 'N/A' }}">
                            {{ $item->nama_item }} - RM {{ number_format((float) $item->harga, 2) }}
                            @if(isset($item->stok))
                                (Stok: {{ $item->stok }})
                            @endif
                        </option>
                    @endforeach
                </select>
                <small style="color:#64748B;font-size:12px;" id="stockDisplay">Stok semasa: -</small>
            </div>

            {{-- Quantity --}}
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label for="quantity" style="font-weight:500;font-size:14px;color:#334155;">Kuantiti <span style="color:var(--danger);">*</span></label>
                <input name="quantity" id="quantity" type="number" min="1" required style="padding:10px 12px;border:2px solid #E2E8F0;border-radius:8px;font-size:14px;transition:border-color 0.2s;width:100%;" onfocus="this.style.borderColor='var(--secondary)'" onblur="this.style.borderColor='#E2E8F0'" placeholder="1">
                <small style="color:#64748B;font-size:12px;" id="totalDisplay">Jumlah: RM 0.00</small>
            </div>

            {{-- Date --}}
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label for="tarikh" style="font-weight:500;font-size:14px;color:#334155;">Tarikh <span style="color:var(--danger);">*</span></label>
                <input name="tarikh" id="tarikh" type="date" value="{{ now()->toDateString() }}" required style="padding:10px 12px;border:2px solid #E2E8F0;border-radius:8px;font-size:14px;transition:border-color 0.2s;width:100%;" onfocus="this.style.borderColor='var(--secondary)'" onblur="this.style.borderColor='#E2E8F0'">
            </div>

            {{-- Submit Button --}}
            <div style="display:flex;align-items:end;">
                <button class="button" type="submit" style="background:var(--secondary);color:white;padding:10px 24px;border:none;border-radius:8px;font-weight:600;font-size:14px;cursor:pointer;transition:all 0.2s;width:100%;display:flex;align-items:center;justify-content:center;gap:8px;" onmouseover="this.style.background='var(--secondary-hover)'" onmouseout="this.style.background='var(--secondary)'" onmousedown="this.style.transform='scale(0.97)'" onmouseup="this.style.transform='scale(1)'">
                    Simpan Jualan
                </button>
            </div>
        </form>
    </section>

    {{-- Sales List --}}
    <section class="panel" style="overflow:hidden;border-radius:12px;border:1px solid #E2E8F0;background:white;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        {{-- Header with Stats --}}
        <div class="panel-pad" style="padding:16px 20px;border-bottom:1px solid #E2E8F0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;background:#F8FAFC;">
            <div>
                <h2 style="margin:0;font-size:18px;font-weight:600;color:#0F172A;">Senarai Jualan</h2>
                <p style="margin:2px 0 0;font-size:13px;color:#64748B;">Jumlah rekod: <strong>{{ $sales->total() }}</strong></p>
            </div>
            <div style="display:flex;gap:16px;font-size:13px;color:#475569;flex-wrap:wrap;">
                <span>Jualan hari ini: <strong>{{ $todaySales ?? 0 }}</strong></span>
                <span>Jumlah hari ini: <strong>RM {{ number_format($todayTotal ?? 0, 2) }}</strong></span>
            </div>
        </div>

        {{-- Table --}}
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead>
                    <tr style="background:#F1F5F9;border-bottom:2px solid #E2E8F0;">
                        <th style="padding:12px 16px;text-align:left;font-weight:600;color:#475569;white-space:nowrap;">Tarikh</th>
                        <th style="padding:12px 16px;text-align:left;font-weight:600;color:#475569;">Item</th>
                        <th style="padding:12px 16px;text-align:center;font-weight:600;color:#475569;">Kuantiti</th>
                        <th style="padding:12px 16px;text-align:right;font-weight:600;color:#475569;">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr style="border-bottom:1px solid #F1F5F9;transition:background 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                            <td style="padding:12px 16px;white-space:nowrap;">
                                {{ $sale->tarikh ? \Illuminate\Support\Carbon::parse($sale->tarikh)->format('d/m/Y') : '-' }}
                            </td>
                            <td style="padding:12px 16px;font-weight:500;color:#0F172A;">
                                {{ $sale->nama_item ?? '-' }}
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                <span style="background:#E2E8F0;padding:2px 12px;border-radius:12px;font-weight:500;font-size:13px;">{{ $sale->quantity }}</span>
                            </td>
                            <td style="padding:12px 16px;text-align:right;font-weight:600;color:#0F172A;">
                                RM {{ number_format((float) $sale->jumlah, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding:40px 20px;text-align:center;color:#94a3b8;">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="margin-bottom:8px;">
                                    <path d="M4 4h16v16H4z"/>
                                    <path d="M4 9h16"/>
                                    <path d="M8 14h8"/>
                                </svg>
                                <p style="margin:0;font-size:15px;font-weight:500;">Tiada jualan direkodkan</p>
                                <p style="margin:4px 0 0;font-size:13px;">Mula merekod jualan menggunakan borang di atas</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($sales->hasPages())
            <div class="panel-pad" style="padding:12px 20px;border-top:1px solid #E2E8F0;background:#F8FAFC;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                <div style="font-size:13px;color:#64748B;">
                    Menunjukkan <strong>{{ $sales->firstItem() ?? 0 }}</strong> - <strong>{{ $sales->lastItem() ?? 0 }}</strong> daripada <strong>{{ $sales->total() }}</strong> rekod
                </div>
                <div style="display:flex;gap:4px;flex-wrap:wrap;">
                    {{ $sales->links() }}
                </div>
            </div>
        @endif
    </section>
@endsection

{{-- Custom CSS for pagination styling (optional) --}}
@push('styles')
<style>
    /* Pagination styling */
    .pagination {
        display: flex;
        gap: 4px;
        margin: 0;
        padding: 0;
        list-style: none;
        flex-wrap: wrap;
    }
    .pagination li {
        display: inline;
    }
    .pagination li a, 
    .pagination li span {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        text-decoration: none;
        color: var(--secondary);
        background: white;
        border: 1px solid #E2E8F0;
        transition: all 0.15s;
    }
    .pagination li a:hover {
        background: #eff6ff;
        border-color: var(--secondary);
    }
    .pagination li.active span {
        background: var(--secondary);
        color: white;
        border-color: var(--secondary);
    }
    .pagination li.disabled span {
        color: #94a3b8;
        background: #F1F5F9;
    }
</style>
@endpush

{{-- JavaScript for real-time calculations --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const itemSelect = document.getElementById('item_id');
        const quantityInput = document.getElementById('quantity');
        const totalDisplay = document.getElementById('totalDisplay');
        const stockDisplay = document.getElementById('stockDisplay');

        function updateTotals() {
            const selectedOption = itemSelect.options[itemSelect.selectedIndex];
            const price = parseFloat(selectedOption?.dataset?.price) || 0;
            const stock = selectedOption?.dataset?.stock || 'N/A';
            const quantity = parseInt(quantityInput.value) || 0;
            const total = price * quantity;

            // Update stock display
            stockDisplay.textContent = `Stok semasa: ${stock}`;

            // Update total display
            totalDisplay.textContent = `Jumlah: RM ${total.toFixed(2)}`;
        }

        itemSelect.addEventListener('change', updateTotals);
        quantityInput.addEventListener('input', updateTotals);

        // Initial calculation
        updateTotals();
    });
</script>
@endpush
