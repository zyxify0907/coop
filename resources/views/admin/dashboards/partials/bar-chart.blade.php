@php
    $maxValue = max(1, collect($items)->max('value') ?? 1);
@endphp

<div class="module-chart">
    @foreach ($items as $item)
        @php
            $value = (float) ($item['value'] ?? 0);
            $width = max(6, round(($value / $maxValue) * 100));
        @endphp
        <div class="chart-row">
            <div class="chart-row__head">
                <span>{{ $item['label'] }}</span>
                <strong>{{ $money ? 'RM '.number_format($value, 2) : number_format($value) }}</strong>
            </div>
            <div class="chart-track">
                <div class="chart-fill" style="width: {{ $width }}%;"></div>
            </div>
        </div>
    @endforeach
</div>
