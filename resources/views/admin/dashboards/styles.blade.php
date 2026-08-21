@push('styles')
<style>
    .module-dashboard{display:grid;gap:20px}
    .module-hero{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;padding:30px;border:1px solid var(--line);border-left:5px solid var(--secondary);border-radius:18px;background:#fff;box-shadow:0 16px 40px rgba(15,23,42,.06)}
    .module-hero span,.module-panel header span{display:inline-flex;color:var(--secondary);font-size:12px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
    .module-hero h1{margin:8px 0 0;color:#0F172A;font-size:34px;line-height:1.15;font-weight:900;letter-spacing:0}
    .module-hero p{margin:8px 0 0;color:#334155;font-size:16px;font-weight:800}
    .module-hero a,.module-panel header a{display:inline-flex;align-items:center;justify-content:center;min-height:42px;border:1px solid #BFD7FF;border-radius:8px;background:var(--secondary);color:#fff;padding:0 16px;text-decoration:none;font-weight:900}
    .module-metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
    .module-metrics div{display:grid;gap:7px;min-height:132px;padding:20px;border:1px solid var(--line);border-radius:14px;background:#fff;box-shadow:0 12px 30px rgba(15,23,42,.045)}
    .module-metrics span{color:#475569;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.05em}
    .module-metrics strong{color:#0F172A;font-size:28px;font-weight:900;line-height:1.15}
    .module-metrics small{color:#64748B;font-weight:800}
    .module-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
    .module-grid--full{grid-template-columns:1fr}
    .module-action-layout{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);align-items:start;gap:18px}
    .module-action-stack{display:grid;gap:18px}
    .module-panel{padding:22px;border:1px solid var(--line);border-radius:16px;background:#fff;box-shadow:0 12px 30px rgba(15,23,42,.05)}
    .module-panel header{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid var(--line)}
    .module-panel h2{margin:4px 0 0;color:#0F172A;font-size:20px;font-weight:900;line-height:1.25}
    .module-panel header a{background:#fff;color:var(--secondary)}
    .module-list{display:grid;gap:10px}
    .module-row{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px;border:1px solid #D7E3F5;border-radius:12px;background:#F8FBFF;color:inherit;text-decoration:none}
    .module-row strong{display:block;color:#0F172A;font-weight:900}
    .module-row small{display:block;margin-top:3px;color:#64748B;font-weight:800}
    .module-row span{display:inline-flex;align-items:center;justify-content:center;min-width:84px;border-radius:999px;background:#E8F0FF;color:#1D4ED8;padding:7px 11px;font-size:12px;font-weight:900;text-align:center}
    .module-empty{padding:24px;border:1px dashed #CBD5E1;border-radius:12px;background:#fff;color:#64748B;text-align:center;font-weight:800}
    .module-chart{display:grid;gap:15px}
    .chart-row{display:grid;gap:8px}
    .chart-row__head{display:flex;align-items:center;justify-content:space-between;gap:12px}
    .chart-row__head span{color:#334155;font-weight:900}
    .chart-row__head strong{color:#0F172A;font-weight:900}
    .chart-track{height:14px;border-radius:999px;background:#E8F0FF;overflow:hidden}
    .chart-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,#2453A6,#1D4ED8)}
    .donut-chart{display:grid;grid-template-columns:180px minmax(0,1fr);align-items:center;gap:24px;min-height:230px}
    .donut-chart__visual{position:relative;display:grid;place-items:center;width:180px;aspect-ratio:1;border-radius:50%;box-shadow:inset 0 0 0 1px rgba(15,23,42,.06)}
    .donut-chart__visual::after{content:"";position:absolute;inset:34px;border-radius:50%;background:#fff;box-shadow:0 10px 26px rgba(15,23,42,.08)}
    .donut-chart__visual div{position:relative;z-index:1;display:grid;gap:3px;text-align:center}
    .donut-chart__visual strong{color:#0F172A;font-size:28px;font-weight:900;line-height:1}
    .donut-chart__visual span{color:#64748B;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.05em}
    .donut-chart__legend{display:grid;gap:10px}
    .donut-chart__item{display:grid;grid-template-columns:auto minmax(0,1fr) auto;align-items:center;gap:10px;padding:11px 12px;border:1px solid #D7E3F5;border-radius:10px;background:#F8FBFF}
    .donut-chart__item span{width:11px;height:11px;border-radius:999px}
    .donut-chart__item p{margin:0;color:#334155;font-weight:900}
    .donut-chart__item strong{color:#0F172A;font-weight:900}
    @media (max-width:1100px){.module-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}.module-grid,.module-action-layout{grid-template-columns:1fr}}
    @media (max-width:720px){.module-hero,.module-panel header{align-items:stretch;flex-direction:column}.module-hero h1{font-size:28px}.module-hero a,.module-panel header a{width:100%}.module-metrics{grid-template-columns:1fr}.module-row{align-items:flex-start;flex-direction:column}.module-row span{min-width:0}.donut-chart{grid-template-columns:1fr;justify-items:center}.donut-chart__legend{width:100%}}
</style>
@endpush
