@extends('layouts.app')
@section('title', 'Pekerja Koperasi')
@section('page-title', 'Pekerja Koperasi')
@section('page-subtitle', 'Senarai staff yang dibenarkan menggunakan Smart Attendance.')
@section('content')
    @include('attendance.partials.styles')
    <div class="attendance-page"><header class="attendance-head"><div><span class="attendance-kicker">SMART ATTENDANCE</span><h1>Pekerja Koperasi</h1><p>Senarai pekerja koperasi yang dibenarkan menggunakan modul kehadiran.</p></div><div class="attendance-head__meta"><span>Jumlah Pekerja</span><strong>{{ $workers->total() }}</strong></div></header><nav class="attendance-nav"><a href="{{ route('admin.attendance.live') }}">Live Attendance</a><a href="{{ route('admin.attendance.records') }}">Rekod Kehadiran</a><a href="{{ route('admin.users.staff') }}">Urus Staff</a></nav>
        <section class="attendance-panel"><form class="attendance-toolbar" method="GET"><label>Cari Pekerja<input name="search" value="{{ $search }}" placeholder="Nama atau No. Pekerja"></label><button class="attendance-primary" type="submit">Cari</button></form><div class="attendance-table-wrap"><table class="attendance-table"><thead><tr><th>Nama</th><th>No. Pekerja</th><th>Jenis Staff</th><th>Status Akaun</th><th>Attendance</th></tr></thead><tbody>@forelse($workers as $worker)<tr><td class="attendance-person"><strong>{{ $worker->nama }}</strong><span>{{ $worker->email ?? '-' }}</span></td><td>{{ $worker->no_pekerja }}</td><td>{{ $worker->staff_type_label }}</td><td><span class="attendance-badge attendance-badge--{{ $worker->status_aktif ? 'present' : 'missing_checkout' }}">{{ $worker->status_aktif ? 'Aktif' : 'Tidak Aktif' }}</span></td><td>{{ $worker->status_aktif ? 'Enabled' : 'Disabled' }}</td></tr>@empty<tr><td colspan="5" class="attendance-empty">Tiada Pekerja Koperasi ditemui.</td></tr>@endforelse</tbody></table></div>@if($workers->hasPages())<div class="pagination">{{ $workers->links() }}</div>@endif</section>
    </div>
@endsection
