@php
    $maxValue = max(1, collect($items)->max('value') ?? 1);
    $itemCount = max(1, count($items));
@endphp

<div class="stock-chart">
    @if (count($items) > 0)
        <div class="stock-chart__y-title">Bilangan Stok</div>
        <div class="stock-chart__viewport">
            <div class="stock-chart__plot" style="grid-template-columns: repeat({{ $itemCount }}, minmax(70px, 1fr));">
                @foreach ($items as $item)
                    @php
                        $value = (int) ($item['value'] ?? 0);
                        $height = max(8, round(($value / $maxValue) * 100));
                        $lowCount = (int) ($item['low_count'] ?? 0);
                    @endphp
                    <div class="stock-chart__item" style="--bar-height: {{ $height }}%;">
                        <div class="stock-chart__bar-area">
                            <strong>{{ number_format($value) }}</strong>
                            <div class="stock-chart__bar {{ $lowCount > 0 ? 'is-low' : '' }}"></div>
                        </div>
                        <span>{{ $item['label'] }}</span>
                        @if ($lowCount > 0)
                            <small>{{ number_format($lowCount) }} saiz stok rendah</small>
                        @else
                            <small>Stabil</small>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <div class="stock-chart__x-title">Item Baju</div>
    @else
        <div class="module-empty">Tiada stok baju lagi.</div>
    @endif
</div>
