@extends('layouts.app')

@section('title', 'Staff Dashboard')
@section('page-title', 'Staff Dashboard')
@section('page-subtitle', 'Operasi tempahan, stok dan jualan koperasi.')

@section('content')
    <section class="template-hero staff-template-hero">
        <div>
            <span class="template-kicker">Koperasi Politeknik Besut</span>
            <h2>Selamat bertugas, {{ $user->nama }}</h2>
            <p>{{ $user->staff_type_label }} - {{ $user->no_pekerja }}</p>
        </div>
        <div class="template-hero__meta">
            <span class="badge {{ $user->status_aktif ? 'success' : 'danger' }}">{{ $user->status_aktif ? 'Aktif' : 'Tidak Aktif' }}</span>
            <strong>{{ now()->format('d M Y') }}</strong>
        </div>
    </section>

    <div class="card-grid">
        <section class="panel metric">
            <div>
                <span class="metric-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="3"/><path d="M8 7h8"/><path d="M8 11h4"/></svg>
                </span>
                <span>No Pekerja</span>
            </div>
            <div class="metric-row">
                <strong style="font-size:26px">{{ $user->no_pekerja }}</strong>
                <span class="badge success">Staff</span>
            </div>
        </section>

        <section class="panel metric">
            <div>
                <span class="metric-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h16"/><path d="M8 8l-4 4 4 4"/><path d="M16 8l4 4-4 4"/></svg>
                </span>
                <span>Jenis Staff</span>
            </div>
            <div class="metric-row">
                <strong style="font-size:22px">{{ $user->staff_type_label }}</strong>
            </div>
        </section>

        <section class="panel metric">
            <div>
                <span class="metric-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m8.5 12 2.5 2.5 4.5-5"/></svg>
                </span>
                <span>Status</span>
            </div>
            <div class="metric-row">
                <strong>{{ $user->status_aktif ? 'Aktif' : 'Tidak Aktif' }}</strong>
                <span class="badge {{ $user->status_aktif ? 'success' : 'danger' }}">{{ $user->status_aktif ? 'Online' : 'Off' }}</span>
            </div>
        </section>
    </div>

    <div class="staff-dashboard-grid">
        <section class="panel">
            <div class="panel-section-head">
                <div>
                    <h2>Kerja Harian</h2>
                    <p>Akses rekod koperasi yang perlu disemak dan dikemas kini.</p>
                </div>
            </div>

            <nav class="staff-work-list" aria-label="Kerja harian staff">
                <a class="staff-work-item" href="{{ route('student.permohonan.index') }}">
                    <span>Permohonan</span>
                    <strong>Semak permohonan koperasi</strong>
                    <small>Semak status permohonan saham dan pengeluaran.</small>
                </a>
                <a class="staff-work-item" href="{{ route('koperasi.transactions.index') }}">
                    <span>Transaksi</span>
                    <strong>Rekod transaksi saham</strong>
                    <small>Lihat rekod kredit dan debit anggota koperasi.</small>
                </a>
                <a class="staff-work-item" href="{{ route('koperasi.documents.index') }}">
                    <span>Dokumen</span>
                    <strong>Semak dokumen sokongan</strong>
                    <small>Lihat slip bayaran dan dokumen milik akaun staff.</small>
                </a>
                <a class="staff-work-item" href="{{ route('koperasi.notifications.index') }}">
                    <span>Notifikasi</span>
                    <strong>Makluman terkini</strong>
                    <small>Semak keputusan dan perubahan rekod koperasi.</small>
                </a>
            </nav>
        </section>

        <aside class="panel staff-profile-panel">
            <div class="panel-section-head">
                <div>
                    <h2>Profil Staff</h2>
                    <p>Maklumat akaun operasi.</p>
                </div>
            </div>
            <div class="staff-profile-card">
                <div class="staff-profile-main">
                    <span class="staff-avatar">{{ strtoupper(substr($user->nama ?? 'S', 0, 1)) }}</span>
                    <div>
                        <strong>{{ $user->nama }}</strong>
                        <span>{{ $user->staff_type_label }}</span>
                    </div>
                </div>
                <div class="staff-profile-list">
                    <div>
                        <span>No. KP</span>
                        <strong>{{ $user->nric }}</strong>
                    </div>
                    <div>
                        <span>Email</span>
                        <strong>{{ $user->email ?? '-' }}</strong>
                    </div>
                    <div>
                        <span>No Telefon</span>
                        <strong>{{ $user->no_tel ?? '-' }}</strong>
                    </div>
                    <div>
                        <span>Status</span>
                        <strong>{{ $user->status_aktif ? 'Aktif' : 'Tidak Aktif' }}</strong>
                    </div>
                </div>
            </div>
        </aside>
    </div>
@endsection

@push('styles')
<style>
    .template-hero{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:30px;padding:28px;border:1px solid var(--line);border-left:5px solid var(--secondary);border-radius:18px;background:#fff;box-shadow:var(--shadow-sm);color:var(--text)}
    .template-kicker{display:inline-flex;margin-bottom:12px;padding:5px 12px;border-radius:999px;background:var(--secondary-soft);color:var(--secondary);font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.08em}
    .template-hero h2{margin:0;font-size:32px;letter-spacing:0;color:var(--text)}
    .template-hero p{margin:10px 0 0;color:var(--muted-2);font-weight:800}
    .template-hero__meta{display:grid;gap:8px;justify-items:end}
    .template-hero__meta strong{font-size:14px;color:var(--muted-2)}
    .card-grid{gap:26px!important;margin-bottom:30px!important}
    .staff-dashboard-grid{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(300px,.75fr);gap:16px;margin-top:18px}
    .staff-work-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0}
    .staff-work-item{display:grid;gap:6px;padding:18px 20px;border-right:1px solid var(--line);border-bottom:1px solid var(--line);text-decoration:none;color:var(--text)}
    .staff-work-item:nth-child(2n){border-right:0}
    .staff-work-item:nth-last-child(-n+2){border-bottom:0}
    .staff-work-item:hover{background:var(--surface-soft);color:var(--text)}
    .staff-work-item span{font-size:11px;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:.04em}
    .staff-work-item strong{font-size:15px}
    .staff-work-item small{color:var(--muted-2);font-weight:500;line-height:1.5}
    .card-grid .metric::before{display:none!important}
    .card-grid .metric{padding:30px;border-radius:18px;box-shadow:0 10px 26px rgba(15,23,42,.045)}
    .card-grid .metric-icon{background:var(--surface-soft)!important;color:var(--secondary)!important;border:1px solid var(--line)!important;box-shadow:none!important}
    .dashboard-grid > .panel:hover,
    .card-grid > .panel:hover,
    .template-hero:hover{border-color:var(--line)!important;box-shadow:var(--shadow-sm)!important}
    .staff-profile-panel{overflow:hidden!important;border-radius:16px!important}
    .staff-profile-panel:hover{border-color:var(--line)!important;box-shadow:var(--shadow-sm)!important}
    .staff-profile-panel .panel-section-head{border-radius:16px 16px 0 0;background:#fff;padding:20px 22px}
    .staff-profile-card{padding:22px;display:grid;gap:18px}
    .staff-profile-main{display:flex;align-items:center;gap:14px;padding-bottom:18px;border-bottom:1px solid var(--line)}
    .staff-avatar{width:52px;height:52px;border-radius:999px;background:var(--secondary-soft);color:var(--secondary);display:grid;place-items:center;font-weight:900;border:1px solid #dbeafe}
    .staff-profile-main strong{display:block;font-size:17px;color:var(--text)}
    .staff-profile-main span{display:block;margin-top:3px;color:var(--muted-2);font-weight:700}
    .staff-profile-list{display:grid;gap:10px}
    .staff-profile-list div{display:grid;gap:3px;padding:12px 14px;border:1px solid var(--line);border-radius:12px;background:var(--surface-soft)}
    .staff-profile-list span{font-size:11px;font-weight:900;color:var(--muted-2);text-transform:uppercase;letter-spacing:.03em}
    .staff-profile-list strong{font-size:14px;color:var(--text);overflow-wrap:anywhere}
    @media (max-width: 900px){.template-hero{align-items:flex-start;flex-direction:column}.template-hero__meta{justify-items:start}.staff-dashboard-grid{grid-template-columns:1fr}.staff-work-list{grid-template-columns:1fr}.staff-work-item,.staff-work-item:nth-child(2n),.staff-work-item:nth-last-child(-n+2){border-right:0;border-bottom:1px solid var(--line)}.staff-work-item:last-child{border-bottom:0}}
</style>
@endpush
