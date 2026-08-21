@php
    $palette = ['#2453A6', '#10B981', '#F59E0B', '#EF4444', '#7C3AED'];
    $total = max(0, collect($items)->sum(fn ($item) => (float) ($item['value'] ?? 0)));
    $cursor = 0;
    $segments = collect($items)->values()->map(function ($item, $index) use (&$cursor, $palette, $total) {
        $value = (float) ($item['value'] ?? 0);
        $start = $cursor;
        $size = $total > 0 ? ($value / $total) * 100 : 0;
        $cursor += $size;

        return [
            'label' => $item['label'] ?? '-',
            'value' => $value,
            'color' => $palette[$index % count($palette)],
            'start' => $start,
            'end' => $cursor,
        ];
    });
    $gradient = $total > 0
        ? $segments->map(fn ($segment) => "{$segment['color']} {$segment['start']}% {$segment['end']}%")->implode(', ')
        : '#E8F0FF 0 100%';
@endphp

<div class="donut-chart">
    <div class="donut-chart__visual" style="background: conic-gradient({{ $gradient }});">
        <div>
            <strong>{{ $money ? 'RM '.number_format($total, 2) : number_format($total) }}</strong>
            <span>Jumlah</span>
        </div>
    </div>

    <div class="donut-chart__legend">
        @foreach ($segments as $segment)
            <div class="donut-chart__item">
                <span style="background: {{ $segment['color'] }}"></span>
                <p>{{ $segment['label'] }}</p>
                <strong>{{ $money ? 'RM '.number_format($segment['value'], 2) : number_format($segment['value']) }}</strong>
            </div>
        @endforeach
    </div>
</div>
