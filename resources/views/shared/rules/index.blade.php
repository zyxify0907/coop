@extends('layouts.app')

@section('title', 'Rules')
@section('page-title', 'Rules')
@section('page-subtitle', 'Panduan operasi sistem CoopBest.')

@section('content')
    <section class="panel panel-pad">
        <h2 style="margin:0 0 16px">Peraturan Operasi</h2>
        <table>
            <tr><th>Import Ahli</th><td>CSV/XLSX perlu mengandungi Nama, No Matrik dan Semester. No Matrik duplikat akan dilangkau.</td></tr>
            <tr><th>Tempahan</th><td>Student membuat tempahan, staff mengurus baju dan mengemaskini status sehingga selesai.</td></tr>
            <tr><th>Stok</th><td>Kuantiti stok dikemas kini melalui pengurusan stok dan tempahan baju.</td></tr>
            <tr><th>Saham</th><td>Admin mengurus SYER dan YURAN untuk setiap ahli.</td></tr>
        </table>
    </section>
@endsection
