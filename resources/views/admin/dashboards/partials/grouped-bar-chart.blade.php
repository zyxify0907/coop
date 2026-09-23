@php
    $maxValue = max(1, collect($items)->flatMap(fn ($item) => [(int) ($item['pelajar'] ?? 0), (int) ($item['staff'] ?? 0)])->max() ?? 1);
@endphp

<div class="grouped-chart">
    <div class="grouped-chart__legend">
        <span><i class="is-student"></i> Pelajar</span>
        <span><i class="is-staff"></i> Staff Pengurusan</span>
    </div>
    <div class="grouped-chart__plot">
        @foreach ($items as $item)
            @php
                $student = (int) ($item['pelajar'] ?? 0);
                $staff = (int) ($item['staff'] ?? 0);
                $studentHeight = max(6, round(($student / $maxValue) * 100));
                $staffHeight = max(6, round(($staff / $maxValue) * 100));
            @endphp
            <div class="grouped-chart__year">
                <div class="grouped-chart__bars">
                    <span class="grouped-chart__bar is-student" style="height: {{ $studentHeight }}%;" title="Pelajar: {{ number_format($student) }}"><b>{{ number_format($student) }}</b></span>
                    <span class="grouped-chart__bar is-staff" style="height: {{ $staffHeight }}%;" title="Staff: {{ number_format($staff) }}"><b>{{ number_format($staff) }}</b></span>
                </div>
                <strong>{{ $item['label'] }}</strong>
            </div>
        @endforeach
    </div>
</div>
