@extends('layouts.app')

@section('title', 'Pembayaran')
@section('page-title', 'Pembayaran Vendor')
@section('page-subtitle', 'Kira bayaran akhir vendor selepas komisen.')

@section('content')
<style>
    :root {
        --bg: #f8fafc;
        --card: #ffffff;
        --border: #e2e8f0;
        --border-strong: #cbd5e1;
        --text: #0f172a;
        --muted: #64748b;
        --primary: #1E40AF;
        --primary-hover: #1D4ED8;
        --success: #16a34a;
        --success-hover: #15803d;
        --danger: var(--danger);
        --danger-bg: var(--danger-soft);
        --danger-border: var(--danger-soft);
        --radius: 14px;
        --shadow: 0 8px 30px rgba(15, 23, 42, 0.06);
    }

    * {
        box-sizing: border-box;
    }

    .payment-wrap {
        max-width: 1360px;
        margin: 0 auto;
        padding: 24px 16px 48px;
    }

    .page-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 16px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .page-title h1 {
        margin: 0;
        font-size: 28px;
        line-height: 1.2;
        color: var(--text);
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .page-title p {
        margin: 6px 0 0;
        color: var(--muted);
        font-size: 14px;
    }

    .box {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .box-head {
        padding: 16px 20px;
        background: #ffffff;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .box-head h2 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: var(--text);
    }

    .box-head span {
        font-size: 13px;
        color: var(--muted);
    }

    .box-body {
        padding: 20px;
    }

    .alert {
        padding: 14px 16px;
        border-radius: 12px;
        margin-bottom: 18px;
        border: 1px solid;
        background: var(--danger-bg);
        border-color: var(--danger-border);
        color: var(--primary-dark);
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 14px;
        align-items: end;
    }

    .field {
        grid-column: span 12;
    }

    .field label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .field input,
    .field select {
        width: 100%;
        padding: 11px 12px;
        border: 1px solid var(--border);
        border-radius: 10px;
        font-size: 14px;
        background: #fff;
        transition: border-color .2s, box-shadow .2s, background .2s;
    }

    .field input:focus,
    .field select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .field .help {
        display: block;
        margin-top: 6px;
        font-size: 12px;
        color: var(--muted);
    }

    .field.is-invalid input,
    .field.is-invalid select {
        border-color: var(--danger-soft);
        background: #fffafa;
    }

    .invalid-feedback {
        margin-top: 6px;
        font-size: 12px;
        color: var(--danger);
    }

    .actions {
        display: flex;
        justify-content: flex-end;
        grid-column: span 12;
    }

    .button {
        border: 0;
        border-radius: 10px;
        padding: 11px 16px;
        background: var(--primary);
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: transform .15s ease, background .2s ease;
        width: 100%;
    }

    .button:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
    }

    .table-wrap {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 14px;
    }

    table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        text-align: left;
        padding: 13px 14px;
        font-weight: 700;
        color: #475569;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid var(--border);
        background: #f8fafc;
        white-space: nowrap;
    }

    table tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid #edf2f7;
        vertical-align: middle;
        background: #fff;
    }

    table tbody tr:hover td {
        background: #f8fafc;
    }

    .empty {
        text-align: center;
        padding: 44px 20px;
        color: #94a3b8;
    }

    .empty p {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #64748b;
    }

    .empty small {
        display: block;
        margin-top: 4px;
        font-size: 13px;
    }

    .amount {
        font-weight: 700;
        color: #111827;
    }

    .footer-pad {
        padding: 16px 20px;
        border-top: 1px solid var(--border);
        background: #fff;
        display: flex;
        justify-content: center;
    }

    @media (min-width: 768px) {
        .field.col-4 { grid-column: span 4; }
        .field.col-3 { grid-column: span 3; }
        .field.col-2 { grid-column: span 2; }
        .actions { grid-column: span 1; }
        .button { width: auto; min-width: 150px; }
    }

    @media (max-width: 767px) {
        .payment-wrap {
            padding: 16px 12px 36px;
        }

        .page-title h1 {
            font-size: 24px;
        }
    }
</style>

<div class="payment-wrap">
    <div class="page-head">
        <div class="page-title">
            <h1>Pembayaran Vendor</h1>
            <p>Kira bayaran akhir vendor selepas komisen.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="box">
        <div class="box-head">
            <h2>Rekod Pembayaran</h2>
            <span>Masukkan jualan dan komisen untuk kiraan automatik</span>
        </div>

        <div class="box-body">
            <form method="POST" action="{{ route('admin.pembayaran.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="field col-4 {{ $errors->has('vendor_id') ? 'is-invalid' : '' }}">
                        <label for="vendor_id">Vendor</label>
                        <select id="vendor_id" name="vendor_id" required>
                            <option value="">Pilih vendor</option>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->vendor_id }}" {{ old('vendor_id') == $vendor->vendor_id ? 'selected' : '' }}>
                                    {{ $vendor->nama_vendor }}
                                </option>
                            @endforeach
                        </select>
                        <small class="help">Pilih vendor yang akan dibayar.</small>
                        @error('vendor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field col-3 {{ $errors->has('jumlah_jualan') ? 'is-invalid' : '' }}">
                        <label for="jumlah_jualan">Jumlah Jualan</label>
                        <input
                            id="jumlah_jualan"
                            name="jumlah_jualan"
                            type="number"
                            min="0"
                            step="0.01"
                            value="{{ old('jumlah_jualan') }}"
                            required
                        >
                        <small class="help">Masukkan jumlah jualan kasar.</small>
                        @error('jumlah_jualan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field col-3 {{ $errors->has('komisen') ? 'is-invalid' : '' }}">
                        <label for="komisen">Komisen</label>
                        <input
                            id="komisen"
                            name="komisen"
                            type="number"
                            min="0"
                            step="0.01"
                            value="{{ old('komisen', 0) }}"
                            required
                        >
                        <small class="help">Komisen akan ditolak daripada jualan.</small>
                        @error('komisen')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="actions">
                        <button class="button" type="submit">Kira & Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="box">
        <div class="box-head">
            <h2>Senarai Pembayaran</h2>
            <span>{{ $payments->total() }} rekod</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Jumlah Jualan</th>
                        <th>Komisen</th>
                        <th>Bayaran Akhir</th>
                        <th>Tarikh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>{{ $payment->nama_vendor ?? '-' }}</td>
                            <td>RM {{ number_format((float) $payment->jumlah_jualan, 2) }}</td>
                            <td>RM {{ number_format((float) $payment->komisen, 2) }}</td>
                            <td class="amount">RM {{ number_format((float) $payment->bayaran_akhir, 2) }}</td>
                            <td>{{ $payment->created_at ? \Illuminate\Support\Carbon::parse($payment->created_at)->format('d/m/Y') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty">
                                    <p>Tiada pembayaran.</p>
                                    <small>Rekod pembayaran akan dipaparkan di sini selepas disimpan.</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="footer-pad">
                {{ $payments->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
