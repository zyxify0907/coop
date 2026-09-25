@php
    $values = collect($items)->map(fn ($item) => (float) ($item['value'] ?? 0));
    $maxValue = max(1, $values->max() ?? 1);
    $itemCount = max(1, count($items));
    $width = max(720, 180 + ($itemCount * 92));
    $height = 260;
    $paddingLeft = 92;
    $paddingRight = 28;
    $paddingTop = 42;
    $paddingBottom = 42;
    $plotLeft = $paddingLeft;
    $plotRight = $width - $paddingRight;
    $plotTop = $paddingTop;
    $plotBottom = $height - $paddingBottom;
    $plotWidth = $plotRight - $plotLeft;
    $plotHeight = $plotBottom - $plotTop;
    $count = max(1, $itemCount - 1);
    $moneyTick = fn (float $value) => 'RM '.number_format($value, $value >= 1000 ? 0 : 2);
    $ticks = [
        ['value' => $maxValue, 'y' => $plotTop],
        ['value' => $maxValue / 2, 'y' => $plotTop + ($plotHeight / 2)],
        ['value' => 0, 'y' => $plotBottom],
    ];

    $pointData = collect($items)->values()->map(function ($item, $index) use ($maxValue, $plotLeft, $plotTop, $plotWidth, $plotHeight, $count) {
        $value = (float) ($item['value'] ?? 0);

        return [
            'label' => $item['label'] ?? '-',
            'value' => $value,
            'x' => $plotLeft + (($plotWidth / $count) * $index),
            'y' => $plotTop + ($plotHeight - (($value / $maxValue) * $plotHeight)),
        ];
    });

    $linePath = '';
    $points = $pointData->values();

    if ($points->isNotEmpty()) {
        $first = $points->first();
        $linePath = 'M '.round($first['x'], 2).' '.round($first['y'], 2);

        for ($i = 1; $i < $points->count(); $i++) {
            $previous = $points[$i - 1];
            $current = $points[$i];
            $midX = ($previous['x'] + $current['x']) / 2;

            $linePath .= ' C '.round($midX, 2).' '.round($previous['y'], 2).', '.round($midX, 2).' '.round($current['y'], 2).', '.round($current['x'], 2).' '.round($current['y'], 2);
        }
    }

    $areaPath = $linePath !== ''
        ? $linePath.' L '.round($points->last()['x'], 2).' '.round($plotBottom, 2).' L '.round($points->first()['x'], 2).' '.round($plotBottom, 2).' Z'
        : '';
@endphp

<div class="line-chart">
    <svg viewBox="0 0 {{ $width }} {{ $height }}" role="img" aria-label="Trend saham tahunan" style="min-width: {{ $width }}px;">
        <defs>
            <linearGradient id="lineChartAreaGradient" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#0B5ED7" stop-opacity=".14"/>
                <stop offset="100%" stop-color="#0B5ED7" stop-opacity="0"/>
            </linearGradient>
        </defs>
        <g class="line-chart__axis-labels">
            @foreach ($ticks as $tick)
                <text x="{{ $plotLeft - 14 }}" y="{{ round($tick['y'] + 4, 2) }}" text-anchor="end">{{ $moneyTick((float) $tick['value']) }}</text>
            @endforeach
        </g>
        <g class="line-chart__grid">
            @foreach ($ticks as $tick)
                <line x1="{{ $plotLeft }}" y1="{{ round($tick['y'], 2) }}" x2="{{ $plotRight }}" y2="{{ round($tick['y'], 2) }}"/>
            @endforeach
            <line class="line-chart__axis" x1="{{ $plotLeft }}" y1="{{ $plotTop }}" x2="{{ $plotLeft }}" y2="{{ $plotBottom }}"/>
            <line class="line-chart__axis" x1="{{ $plotLeft }}" y1="{{ $plotBottom }}" x2="{{ $plotRight }}" y2="{{ $plotBottom }}"/>
        </g>
        @if ($areaPath !== '')
            <path class="line-chart__area" d="{{ $areaPath }}"/>
            <path class="line-chart__line" d="{{ $linePath }}"/>
        @endif
        @foreach ($pointData as $point)
            <text class="line-chart__value-label" x="{{ round($point['x'], 2) }}" y="{{ round(max(16, $point['y'] - 14), 2) }}" text-anchor="middle">{{ $moneyTick((float) $point['value']) }}</text>
            <circle class="line-chart__dot" cx="{{ round($point['x'], 2) }}" cy="{{ round($point['y'], 2) }}" r="5">
                <title>{{ $point['label'] }}: RM {{ number_format((float) $point['value'], 2) }}</title>
            </circle>
            <text class="line-chart__x-label" x="{{ round($point['x'], 2) }}" y="{{ $height - 12 }}" text-anchor="middle">{{ $point['label'] }}</text>
        @endforeach
    </svg>
</div>
