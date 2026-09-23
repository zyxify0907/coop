@php
    $values = collect($items)->map(fn ($item) => (float) ($item['value'] ?? 0));
    $maxValue = max(1, $values->max() ?? 1);
    $width = 720;
    $height = 230;
    $paddingX = 92;
    $paddingY = 28;
    $plotWidth = $width - ($paddingX * 2);
    $plotHeight = $height - ($paddingY * 2);
    $count = max(1, count($items) - 1);
    $moneyTick = fn (float $value) => 'RM '.number_format($value, $value >= 1000 ? 0 : 2);
    $ticks = [
        ['value' => $maxValue, 'y' => $paddingY],
        ['value' => $maxValue / 2, 'y' => $paddingY + ($plotHeight / 2)],
        ['value' => 0, 'y' => $height - $paddingY],
    ];
    $points = collect($items)->map(function ($item, $index) use ($maxValue, $paddingX, $paddingY, $plotWidth, $plotHeight, $count) {
        $x = $paddingX + (($plotWidth / $count) * $index);
        $y = $paddingY + ($plotHeight - (((float) ($item['value'] ?? 0) / $maxValue) * $plotHeight));

        return round($x, 2).','.round($y, 2);
    })->implode(' ');
@endphp

<div class="line-chart">
    <svg viewBox="0 0 {{ $width }} {{ $height }}" role="img" aria-label="Trend saham tahunan">
        <g class="line-chart__axis-labels">
            @foreach ($ticks as $tick)
                <text x="{{ $paddingX - 12 }}" y="{{ round($tick['y'] + 4, 2) }}" text-anchor="end">{{ $moneyTick((float) $tick['value']) }}</text>
            @endforeach
        </g>
        <g class="line-chart__grid">
            <line x1="{{ $paddingX }}" y1="{{ $paddingY }}" x2="{{ $paddingX }}" y2="{{ $height - $paddingY }}"/>
            <line x1="{{ $paddingX }}" y1="{{ $height - $paddingY }}" x2="{{ $width - $paddingX }}" y2="{{ $height - $paddingY }}"/>
            <line x1="{{ $paddingX }}" y1="{{ $paddingY + ($plotHeight * .33) }}" x2="{{ $width - $paddingX }}" y2="{{ $paddingY + ($plotHeight * .33) }}"/>
            <line x1="{{ $paddingX }}" y1="{{ $paddingY + ($plotHeight * .66) }}" x2="{{ $width - $paddingX }}" y2="{{ $paddingY + ($plotHeight * .66) }}"/>
        </g>
        <polyline class="line-chart__area" points="{{ $points }} {{ $width - $paddingX }},{{ $height - $paddingY }} {{ $paddingX }},{{ $height - $paddingY }}"/>
        <polyline class="line-chart__line" points="{{ $points }}"/>
        @foreach ($items as $index => $item)
            @php
                $value = (float) ($item['value'] ?? 0);
                $x = $paddingX + (($plotWidth / $count) * $index);
                $y = $paddingY + ($plotHeight - (($value / $maxValue) * $plotHeight));
            @endphp
            <circle class="line-chart__dot" cx="{{ round($x, 2) }}" cy="{{ round($y, 2) }}" r="4">
                <title>{{ $item['label'] }}: RM {{ number_format($value, 2) }}</title>
            </circle>
        @endforeach
    </svg>
    <div class="line-chart__labels" style="grid-template-columns: repeat({{ max(1, count($items)) }}, minmax(0, 1fr)); padding-left: {{ $paddingX }}px; padding-right: {{ $paddingX }}px;">
        @foreach ($items as $item)
            <span>{{ $item['label'] }}</span>
        @endforeach
    </div>
</div>
