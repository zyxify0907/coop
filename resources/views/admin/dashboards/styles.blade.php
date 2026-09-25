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
    .stock-chart{position:relative;display:grid;grid-template-columns:22px minmax(0,1fr);grid-template-areas:"y plot" ". x";gap:8px 8px;min-height:330px}
    .stock-chart__y-title{grid-area:y;align-self:center;color:#526987;font-size:11px;font-weight:900;letter-spacing:.04em;text-align:center;text-transform:uppercase;writing-mode:vertical-rl;transform:rotate(180deg)}
    .stock-chart__viewport{grid-area:plot;overflow-x:auto;overflow-y:hidden;padding-bottom:2px}
    .stock-chart__plot{position:relative;display:grid;align-items:end;gap:12px;min-width:100%;height:276px;padding:18px 14px 0;border-left:1px solid #D7E3F5;border-bottom:1px solid #D7E3F5;background:repeating-linear-gradient(to top, transparent 0, transparent 54px, #E7EEF8 55px)}
    .stock-chart__item{display:grid;grid-template-rows:180px minmax(42px,auto) auto;gap:8px;min-width:0;height:100%;text-align:center}
    .stock-chart__bar-area{position:relative;display:flex;align-items:end;justify-content:center;min-height:180px}
    .stock-chart__bar-area strong{position:absolute;left:50%;bottom:calc(var(--bar-height) + 8px);color:#061A3A;font-size:12px;font-weight:900;line-height:1;transform:translateX(-50%)}
    .stock-chart__bar{width:min(44px,62%);height:var(--bar-height);min-height:8px;border-radius:9px 9px 0 0;background:linear-gradient(180deg,#0B5ED7,#0A4EBC);box-shadow:0 8px 18px rgba(11,94,215,.16)}
    .stock-chart__bar.is-low{background:linear-gradient(180deg,#F59E0B 0%,#F97316 48%,#ED1C2E 100%);box-shadow:0 8px 18px rgba(237,28,46,.16)}
    .stock-chart__item span{align-self:start;color:#082F59;font-size:12px;font-weight:900;line-height:1.18;overflow-wrap:anywhere}
    .stock-chart__item small{align-self:start;color:#526987;font-size:11px;font-weight:900;line-height:1.25}
    .stock-chart__x-title{grid-area:x;color:#526987;font-size:11px;font-weight:900;letter-spacing:.04em;text-align:center;text-transform:uppercase}
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
    .line-chart{display:grid;gap:0;min-height:240px;overflow-x:auto;overflow-y:hidden}
    .line-chart svg{width:100%;height:auto;min-height:220px;display:block}
    .line-chart__axis-labels text{fill:#526987;font-size:12px;font-weight:900}
    .line-chart__grid line{stroke:#D7E3F5;stroke-width:1}
    .line-chart__grid .line-chart__axis{stroke:#BFD0E6}
    .line-chart__area{fill:url(#lineChartAreaGradient);stroke:0}
    .line-chart__line{fill:none;stroke:#0B5ED7;stroke-width:5;stroke-linecap:round;stroke-linejoin:round}
    .line-chart__dot{fill:#fff;stroke:#0B5ED7;stroke-width:4}
    .line-chart__value-label{fill:#061A3A;font-size:11px;font-weight:900}
    .line-chart__x-label{fill:#526987;font-size:12px;font-weight:900}
    .grouped-chart{display:grid;gap:18px;min-height:260px}
    .grouped-chart__legend{display:flex;align-items:center;gap:14px;color:#334155;font-size:12px;font-weight:900;text-transform:uppercase}
    .grouped-chart__legend span{display:inline-flex;align-items:center;gap:7px}
    .grouped-chart__legend i{width:12px;height:12px;border-radius:999px}
    .grouped-chart__viewport{overflow-x:auto;overflow-y:hidden;padding-bottom:4px}
    .grouped-chart__plot{display:flex;align-items:end;gap:14px;min-height:210px;width:max(100%, calc(var(--year-count) * 104px));padding:14px 10px 0;border-left:1px solid #D7E3F5;border-bottom:1px solid #D7E3F5}
    .grouped-chart__year{display:grid;grid-template-rows:1fr auto;gap:10px;flex:0 0 90px;min-width:90px;height:100%;text-align:center}
    .grouped-chart__bars{display:flex;align-items:end;justify-content:center;gap:7px;min-height:172px}
    .grouped-chart__bar{position:relative;width:min(34px,40%);min-height:8px;border-radius:8px 8px 0 0}
    .grouped-chart__bar b{position:absolute;left:50%;bottom:calc(100% + 6px);color:#0F172A;font-size:11px;font-weight:900;transform:translateX(-50%)}
    .grouped-chart__year strong{color:#334155;font-size:12px;font-weight:900}
    .is-student{background:#0B5ED7}
    .is-staff{background:#18B877}
    .content:has(.share-dashboard){background:#F4F7FB}
    .share-dashboard{width:min(100% - clamp(24px,4vw,64px),1500px);margin:0 auto;gap:18px;color:#061A3A}
    .share-dashboard-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:22px 24px;border:1px solid #D7E2EF;border-left:4px solid #ED1C2E;border-radius:12px;background:#fff;box-shadow:0 8px 18px rgba(8,47,89,.04)}
    .share-dashboard-header span{display:inline-flex;color:#0B5ED7;font-size:11px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
    .share-dashboard-header h1{margin:6px 0 0;color:#061A3A;font-size:clamp(24px,2.1vw,34px);line-height:1.12;font-weight:900;letter-spacing:0}
    .share-dashboard-header p{margin:7px 0 0;color:#526987;font-size:14px;font-weight:700}
    .share-dashboard-header a{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 16px;border:1px solid #0B5ED7;border-radius:7px;background:#0B5ED7;color:#fff;font-size:13px;font-weight:900;text-decoration:none;white-space:nowrap}
    .share-dashboard-header a:hover{background:#084FB5;border-color:#084FB5}
    .share-dashboard .module-metrics{gap:16px}
    .share-dashboard .module-metrics div{position:relative;min-height:98px;gap:6px;padding:17px 20px 16px 24px;border-color:#D7E2EF;border-radius:10px;box-shadow:0 8px 18px rgba(8,47,89,.04);overflow:hidden}
    .share-dashboard .module-metrics div::before{content:"";position:absolute;top:0;bottom:0;left:0;width:4px;background:#0B5ED7}
    .share-dashboard .module-metrics div:nth-child(2)::before{background:#ED1C2E}
    .share-dashboard .module-metrics div:nth-child(3)::before{background:#18B877}
    .share-dashboard .module-metrics div:nth-child(4)::before{background:#F59E0B}
    .share-dashboard .module-metrics span{color:#526987;font-size:11px;letter-spacing:.06em}
    .share-dashboard .module-metrics strong{color:#061A3A;font-size:clamp(20px,1.7vw,26px);line-height:1.05}
    .share-dashboard .module-metrics small{color:#526987;font-size:12px;font-weight:800}
    .share-dashboard-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:18px;align-items:start}
    .share-dashboard-column{display:grid;gap:18px;min-width:0}
    .share-dashboard .module-panel{padding:20px 22px;border-color:#D7E2EF;border-radius:12px;box-shadow:0 8px 18px rgba(8,47,89,.04)}
    .share-dashboard .module-panel header{margin-bottom:16px;padding-bottom:13px;border-bottom-color:#D7E2EF}
    .share-dashboard .module-panel header span{color:#0B5ED7;font-size:11px;letter-spacing:.08em}
    .share-dashboard .module-panel h2{margin-top:4px;color:#061A3A;font-size:19px;line-height:1.2}
    .share-dashboard .module-panel header p{margin:5px 0 0;color:#526987;font-size:13px;font-weight:700}
    .share-dashboard .module-panel--pending header div::before{content:"";display:block;width:4px;height:28px;margin:0 12px 0 0;border-radius:999px;background:#ED1C2E;float:left}
    .share-dashboard .module-panel header a{min-height:36px;padding:0 14px;border-color:#9EC5FF;border-radius:7px;background:#fff;color:#0B5ED7;font-size:12px}
    .share-dashboard .module-panel header a:hover{background:#EAF2FF}
    .share-dashboard .module-empty{min-height:58px;display:grid;place-items:center;padding:14px 18px;border-color:#BFD0E6;border-radius:8px;background:#fff;color:#526987;font-size:13px}
    .share-dashboard .module-row{padding:10px 12px;border-color:#D7E2EF;border-radius:8px;background:#FBFDFF}
    .share-dashboard .module-row strong{color:#061A3A;font-size:13px}
    .share-dashboard .module-row small{color:#526987;font-size:12px}
    .share-dashboard .module-row span{min-width:76px;padding:6px 10px;background:#EAF2FF;color:#0B5ED7}
    .share-dashboard .chart-track{height:12px;background:#E8F0FF}
    .share-dashboard .chart-fill{background:linear-gradient(90deg,#0B5ED7,#1D63F0)}
    .share-dashboard .donut-chart{grid-template-columns:160px minmax(0,1fr);gap:22px;min-height:190px}
    .share-dashboard .donut-chart__visual{width:160px}
    .share-dashboard .donut-chart__visual::after{inset:31px}
    .share-dashboard .donut-chart__item{padding:10px 12px;border-color:#D7E2EF;background:#fff}
    .share-dashboard .line-chart,.share-dashboard .grouped-chart{min-height:235px}
    .share-dashboard .line-chart svg{min-height:215px}
    .share-dashboard .line-chart__line{stroke-width:4}
    .share-dashboard .grouped-chart__plot{min-height:188px}
    .share-dashboard .grouped-chart__bars{min-height:150px}
    .share-dashboard .grouped-chart__bar{width:min(30px,40%);border-radius:7px 7px 0 0}
    @media (max-width:1100px){.module-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}.module-grid,.module-action-layout{grid-template-columns:1fr}}
    @media (max-width:1100px){.share-dashboard-grid{grid-template-columns:1fr}}
    @media (max-width:720px){.module-hero,.module-panel header{align-items:stretch;flex-direction:column}.module-hero h1{font-size:28px}.module-hero a,.module-panel header a{width:100%}.module-metrics{grid-template-columns:1fr}.module-row{align-items:flex-start;flex-direction:column}.module-row span{min-width:0}.donut-chart{grid-template-columns:1fr;justify-items:center}.donut-chart__legend{width:100%}.line-chart__labels{font-size:9px}.grouped-chart__plot{gap:8px}.grouped-chart__bar{width:min(24px,42%)}.stock-chart__plot{min-width:720px;gap:10px}.stock-chart__item span{font-size:11px}}
    @media (max-width:720px){.share-dashboard{width:min(100% - 24px,1500px)}.share-dashboard-header{align-items:flex-start;flex-direction:column}.share-dashboard-header a{width:100%}.share-dashboard .module-metrics div{min-height:88px}.share-dashboard .donut-chart{grid-template-columns:1fr}.share-dashboard .module-panel header a{width:auto}}
</style>
@endpush
