@extends('layouts.app')

@section('title', 'Vendor Management')
@section('page-title', 'Vendors')
@section('page-subtitle', 'Manage vendor information and payments')

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
        --danger-hover: #b91c1c;
        --warning-bg: #fff7ed;
        --warning-border: #fb923c;
        --warning-text: #9a3412;
        --success-bg: #f0fdf4;
        --success-border: #22c55e;
        --success-text: #166534;
        --radius: 14px;
        --shadow: 0 8px 30px rgba(15, 23, 42, 0.06);
    }

    * {
        box-sizing: border-box;
    }

    .vendor-wrap {
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

    .page-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-box {
        position: relative;
        min-width: 280px;
        flex: 1;
    }

    .search-box input {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: #fff;
        font-size: 14px;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }

    .search-box input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .btn {
        appearance: none;
        border: 0;
        border-radius: 10px;
        padding: 10px 16px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: transform .15s ease, background .2s ease, box-shadow .2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        line-height: 1;
        white-space: nowrap;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn:active {
        transform: translateY(0);
    }

    .btn-blue {
        background: var(--primary);
        color: #fff;
    }

    .btn-blue:hover {
        background: var(--primary-hover);
    }

    .btn-green {
        background: var(--success);
        color: #fff;
    }

    .btn-green:hover {
        background: var(--success-hover);
    }

    .btn-red {
        background: var(--danger);
        color: #fff;
    }

    .btn-red:hover {
        background: var(--danger-hover);
    }

    .btn-ghost {
        background: #fff;
        color: var(--text);
        border: 1px solid var(--border);
    }

    .btn-ghost:hover {
        background: #f8fafc;
    }

    .grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .box {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
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

    .box-head h3 {
        margin: 0;
        font-size: 15px;
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

    .alert-box {
        padding: 14px 16px;
        border-radius: 12px;
        margin-bottom: 18px;
        border: 1px solid;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }

    .alert-box.error {
        background: #fff5f5;
        border-color: var(--danger-soft);
        color: var(--primary-dark);
    }

    .alert-box.success {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #166534;
    }

    .alert-box .close {
        background: none;
        border: none;
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
        color: inherit;
        opacity: 0.6;
        padding: 0;
    }

    .alert-box .close:hover {
        opacity: 1;
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

    .field .help {
        display: block;
        margin-top: 6px;
        font-size: 12px;
        color: var(--muted);
    }

    .field input {
        width: 100%;
        padding: 11px 12px;
        border: 1px solid var(--border);
        border-radius: 10px;
        font-size: 14px;
        background: #fff;
        transition: border-color .2s, box-shadow .2s, background .2s;
    }

    .field input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .field.is-invalid input {
        border-color: var(--danger-soft);
        background: #fffafa;
    }

    .invalid-feedback {
        margin-top: 6px;
        font-size: 12px;
        color: var(--danger);
    }

    .field.actions {
        display: flex;
        justify-content: flex-end;
    }

    .field.actions .btn {
        width: 100%;
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

    .inline-input {
        width: 100%;
        padding: 9px 10px;
        border: 1px solid transparent;
        border-radius: 8px;
        font-size: 14px;
        background: transparent;
        color: #1f2937;
        transition: all .15s ease;
    }

    .inline-input:hover {
        background: #fff;
        border-color: #e2e8f0;
    }

    .inline-input:focus {
        outline: none;
        background: #fff;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: #dbeafe;
        color: var(--primary-hover);
    }

    .actions-cell {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .actions-cell .btn {
        padding: 8px 12px;
        font-size: 13px;
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

    .pagination {
        padding: 16px 20px;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: center;
        background: #fff;
    }

    .pagination nav {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .pagination a,
    .pagination span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        color: #475569;
        text-decoration: none;
        font-size: 14px;
        background: #fff;
        transition: all .15s ease;
    }

    .pagination a:hover {
        background: #f8fafc;
        border-color: var(--border-strong);
    }

    .pagination .active span {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
    }

    @media (min-width: 768px) {
        .field.col-4 { grid-column: span 4; }
        .field.col-3 { grid-column: span 3; }
        .field.col-2 { grid-column: span 2; }
        .field.col-1 { grid-column: span 1; }
        .field.actions { grid-column: span 1; }
    }

    @media (max-width: 767px) {
        .vendor-wrap {
            padding: 16px 12px 36px;
        }

        .page-title h1 {
            font-size: 24px;
        }

        .search-box {
            min-width: 100%;
        }

        .box-head,
        .page-head {
            align-items: stretch;
        }

        .actions-cell {
            justify-content: flex-start;
        }

        table thead th {
            font-size: 11px;
        }
    }
</style>

<div class="vendor-wrap">
    <div class="page-head">
        <div class="page-title">
            <h1>Vendors</h1>
            <p>Manage vendor information and payments</p>
        </div>

        <div class="page-actions">
            <div class="search-box">
                <input type="search" id="vendorSearch" placeholder="Search vendor, account, or bank...">
            </div>
            <a href="#new-vendor" class="btn btn-blue">+ New Vendor</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert-box error">
            <span>{{ $errors->first() }}</span>
            <button class="close" type="button" onclick="this.parentElement.remove()">×</button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert-box success">
            <span>{{ session('success') }}</span>
            <button class="close" type="button" onclick="this.parentElement.remove()">×</button>
        </div>
    @endif

    <div class="grid">
        <div class="box" id="new-vendor">
            <div class="box-head">
                <h3>New Vendor</h3>
                <span>Add vendor profile</span>
            </div>
            <div class="box-body">
                <form method="POST" action="{{ route('admin.vendors.store') }}">
                    @csrf
                    <div class="form-grid">
                        <div class="field col-4 {{ $errors->has('nama_vendor') ? 'is-invalid' : '' }}">
                            <label for="nama_vendor">Vendor</label>
                            <input
                                id="nama_vendor"
                                type="text"
                                name="nama_vendor"
                                placeholder="Company name"
                                value="{{ old('nama_vendor') }}"
                                required
                            >
                            <small class="help">Enter the vendor’s legal or trading name.</small>
                            @error('nama_vendor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field col-4 {{ $errors->has('no_akaun') ? 'is-invalid' : '' }}">
                            <label for="no_akaun">Account</label>
                            <input
                                id="no_akaun"
                                type="text"
                                name="no_akaun"
                                placeholder="Account number"
                                value="{{ old('no_akaun') }}"
                                required
                            >
                            <small class="help">Use the payment account number.</small>
                            @error('no_akaun')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field col-3 {{ $errors->has('bank') ? 'is-invalid' : '' }}">
                            <label for="bank">Bank</label>
                            <input
                                id="bank"
                                type="text"
                                name="bank"
                                placeholder="Bank name"
                                value="{{ old('bank') }}"
                                required
                            >
                            <small class="help">Example: Maybank, CIMB, RHB.</small>
                            @error('bank')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field col-1 actions">
                            <button class="btn btn-blue" type="submit">Add</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="box">
            <div class="box-head">
                <h3>All Vendors</h3>
                <span>{{ $vendors->total() }} total</span>
            </div>

            <div class="table-wrap">
                <table id="vendorTable">
                    <thead>
                        <tr>
                            <th style="width:60px;">#</th>
                            <th>Name</th>
                            <th>Account</th>
                            <th>Bank</th>
                            <th style="text-align:center;">Payments</th>
                            <th style="text-align:right;width:220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vendors as $vendor)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <form id="frm-{{ $vendor->vendor_id }}" method="POST" action="{{ route('admin.vendors.update', $vendor->vendor_id) }}">
                                        @csrf
                                        @method('PUT')
                                        <input
                                            type="text"
                                            class="inline-input"
                                            name="nama_vendor"
                                            value="{{ $vendor->nama_vendor }}"
                                            required
                                        >
                                    </form>
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        class="inline-input"
                                        form="frm-{{ $vendor->vendor_id }}"
                                        name="no_akaun"
                                        value="{{ $vendor->no_akaun }}"
                                        required
                                    >
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        class="inline-input"
                                        form="frm-{{ $vendor->vendor_id }}"
                                        name="bank"
                                        value="{{ $vendor->bank }}"
                                        required
                                    >
                                </td>
                                <td style="text-align:center;">
                                    <span class="badge">{{ $vendor->pembayaran_count }}</span>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <button class="btn btn-green" form="frm-{{ $vendor->vendor_id }}" type="submit">Save</button>
                                        <form method="POST" action="{{ route('admin.vendors.destroy', $vendor->vendor_id) }}" onsubmit="return confirm('Delete vendor?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-red" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty">
                                        <p>No vendors yet</p>
                                        <small>Add one using the form above.</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($vendors->hasPages())
                <div class="pagination">
                    {{ $vendors->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.alert-box .close').forEach(btn => {
        btn.addEventListener('click', function () {
            this.parentElement.remove();
        });
    });

    document.querySelectorAll('.alert-box').forEach(el => {
        setTimeout(() => {
            el.style.opacity = '0';
            el.style.transition = 'opacity 0.3s ease';
            setTimeout(() => el.remove(), 300);
        }, 4000);
    });

    const searchInput = document.getElementById('vendorSearch');
    const table = document.getElementById('vendorTable');
    if (searchInput && table) {
        searchInput.addEventListener('input', function () {
            const term = this.value.toLowerCase().trim();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }
</script>
@endsection
