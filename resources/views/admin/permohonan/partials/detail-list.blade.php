<details class="detail-group" @if($open ?? false) open @endif>
    <summary><h3>{{ $title }}</h3></summary>
    <div class="detail-list">
        @foreach ($items as $label => $value)
            @php
                $displayValue = is_array($value) ? implode(', ', $value) : $value;
            @endphp
            <div class="detail-row">
                <span>{{ str_replace('_', ' ', ucfirst($label)) }}</span>
                <strong>{{ in_array($label, $amountFields, true) ? 'RM '.number_format((float) $value, 2) : ($displayValue ?: '-') }}</strong>
            </div>
        @endforeach
    </div>
</details>
