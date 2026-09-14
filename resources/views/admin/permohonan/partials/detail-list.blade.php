<details class="detail-group" @if($open ?? false) open @endif>
    <summary><h3>{{ $title }}</h3></summary>
    <div class="detail-list">
        @foreach ($items as $label => $value)
            @php
                $hiddenFields = ['nama_bank', 'no_akaun_bank', 'bank', 'baki_ewallet'];
                $labelMap = [
                    'no_pekerja' => 'No Pekerja',
                    'no_matrik' => 'No Matrik',
                    'no_anggota_staff' => 'No Anggota Staff',
                    'no_anggota_pelajar' => 'No Anggota Pelajar',
                    'no_kp' => 'No KP',
                ];
                $displayValue = is_array($value) ? implode(', ', $value) : $value;
            @endphp
            @continue(in_array($label, $hiddenFields, true))
            <div class="detail-row">
                <span>{{ $labelMap[$label] ?? str_replace('_', ' ', ucfirst($label)) }}</span>
                <strong>{{ in_array($label, $amountFields, true) ? 'RM '.number_format((float) $value, 2) : ($displayValue ?: '-') }}</strong>
            </div>
        @endforeach
    </div>
</details>
