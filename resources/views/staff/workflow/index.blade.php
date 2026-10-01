@extends('layouts.app')

@section('title', 'Aliran Kerja Staf')
@section('page-title', 'Aliran Kerja Staf')
@section('page-subtitle', 'Semakan harian staf koperasi.')

@section('content')
    @include('components.coop-page-style')

    <div class="coop-wrap">
        <section class="coop-header">
            <div>
                <span class="coop-kicker">STAF KOPERASI</span>
                <h1>Aliran Kerja Staf</h1>
                <p>Ruang kerja staf untuk menyemak permohonan, dokumen, bayaran dan tempahan sebelum tindakan pentadbir akhir.</p>
            </div>
        </section>

        <section class="panel coop-panel">
            <div class="coop-panel-head">
                <div>
                    <h2>Checklist Kerja Harian</h2>
                    <p>Gunakan modul ini untuk kawal kerja semakan sebelum rekod diluluskan admin.</p>
                </div>
            </div>
            <div class="coop-body coop-grid">
                <div class="panel" style="padding:18px">
                    <span class="coop-pill">Semak</span>
                    <h3>Permohonan Baru</h3>
                    <p>Semak maklumat pemohon, penama/wasi dan bukti bayaran.</p>
                </div>
                <div class="panel" style="padding:18px">
                    <span class="coop-pill">Verify</span>
                    <h3>Dokumen</h3>
                    <p>Pastikan slip, IC dan dokumen sokongan lengkap.</p>
                </div>
                <div class="panel" style="padding:18px">
                    <span class="coop-pill">Proses</span>
                    <h3>Tempahan Baju</h3>
                    <p>Kemaskini status tempahan, tarikh siap dan tarikh ambil.</p>
                </div>
            </div>
        </section>

        <section class="panel coop-panel">
            <div class="coop-panel-head">
                <div>
                    <h2>Permohonan Terkini</h2>
                    <p>10 permohonan terbaru untuk semakan staff.</p>
                </div>
            </div>
            <div class="coop-table-wrap">
                <table class="coop-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>No Matrik / Pekerja</th>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th>Tarikh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $application)
                            <tr>
                                <td>{{ $application->nama_pemohon }}</td>
                                <td>{{ $application->no_matrik }}</td>
                                <td>{{ ucfirst($application->jenis) }}</td>
                                <td><span class="coop-pill">{{ ucfirst($application->status) }}</span></td>
                                <td>{{ optional($application->tarikh_permohonan)->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">Tiada permohonan terbaru.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
