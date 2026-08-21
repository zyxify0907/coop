@extends('layouts.app')

@section('title', 'Settings Koperasi')
@section('page-title', 'Settings Koperasi')
@section('page-subtitle', 'Tetapan yuran, saham minimum dan upload.')

@section('content')
    @include('components.coop-page-style')

    <div class="coop-wrap">
        <section class="coop-header">
            <div>
                <span class="coop-kicker">SYSTEM SETTINGS</span>
                <h1>Tetapan Koperasi</h1>
                <p>Nilai penting koperasi disimpan di database supaya tidak hardcode dalam code.</p>
            </div>
        </section>

        <section class="panel coop-panel">
            <div class="coop-panel-head">
                <div>
                    <h2>Konfigurasi</h2>
                    <p>Kemaskini yuran ahli, minimum saham dan saiz upload.</p>
                </div>
            </div>
            <form class="coop-body coop-grid" method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')
                @foreach ($settings as $setting)
                    <div class="coop-field">
                        <label>{{ $setting->label ?? $setting->key }}</label>
                        <input name="settings[{{ $setting->key }}]" value="{{ $setting->value }}">
                    </div>
                @endforeach
                <div class="coop-field" style="align-self:end">
                    <button class="button" type="submit">Simpan Settings</button>
                </div>
            </form>
        </section>
    </div>
@endsection
