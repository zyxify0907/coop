@push('styles')
<style>
    .portal-home{display:grid;gap:20px;color:var(--text)}
    .portal-home__welcome{display:flex;align-items:flex-end;justify-content:space-between;gap:22px;padding:30px;border:1px solid var(--line);border-left:5px solid var(--secondary);border-radius:18px;background:#fff;box-shadow:0 16px 40px rgba(15,23,42,.06)}
    .portal-home__kicker,.portal-home__panel header span{display:inline-flex;color:var(--secondary);font-size:12px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
    .portal-home__welcome h1{margin:8px 0 0;color:#0F172A;font-size:34px;line-height:1.15;font-weight:900;letter-spacing:0}
    .portal-home__welcome p{margin:8px 0 0;color:#334155;font-size:16px;font-weight:800}
    .portal-home__identity{display:grid;gap:4px;min-width:260px;padding:16px 18px;border:1px solid #D7E3F5;border-radius:12px;background:#F8FBFF;text-align:right}
    .portal-home__identity span{color:#64748B;font-size:12px;font-weight:900;text-transform:uppercase}
    .portal-home__identity strong{color:#14213D;font-size:16px;font-weight:900}
    .portal-home__identity small{color:#64748B;font-weight:800}
    .portal-home__grid{display:grid;gap:18px}
    .portal-home__grid--main{grid-template-columns:minmax(0,1.35fr) minmax(320px,.65fr)}
    .portal-home__grid--support{grid-template-columns:minmax(0,1fr) minmax(320px,.75fr)}
    .portal-home__panel{padding:22px;border:1px solid var(--line);border-radius:16px;background:#fff;box-shadow:0 12px 30px rgba(15,23,42,.05)}
    .portal-home__panel header{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid var(--line)}
    .portal-home__panel h2{margin:4px 0 0;color:#0F172A;font-size:20px;line-height:1.25;font-weight:900}
    .portal-home__primary{display:inline-flex;align-items:center;justify-content:center;min-height:40px;border:1px solid #BFD7FF;border-radius:8px;background:#fff;color:var(--secondary);padding:0 16px;text-decoration:none;font-weight:900}
    .portal-home__stack,.portal-home__notifications,.portal-home__actions,.portal-home__activity{display:grid;gap:12px}
    .portal-home__announcement{display:grid;gap:10px;padding:16px;border:1px solid #D7E3F5;border-radius:12px;background:#F8FBFF}
    .portal-home__announcement-top,.portal-home__announcement-meta{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
    .portal-home__announcement h3{margin:0;color:#0F172A;font-size:17px;font-weight:900}
    .portal-home__announcement p{margin:0;color:#334155;font-size:14px;font-weight:700;line-height:1.55;white-space:pre-line}
    .portal-home__announcement-meta{justify-content:flex-start;color:#64748B;font-size:12px;font-weight:800}
    .portal-home__pill{display:inline-flex;align-items:center;justify-content:center;border-radius:999px;padding:6px 10px;font-size:12px;font-weight:900}
    .portal-home__pill.important{background:#FEE2E2;color:#B91C1C}
    .portal-home__pill.reminder{background:#FEF3C7;color:#B45309}
    .portal-home__pill.update{background:#DCFCE7;color:#15803D}
    .portal-home__pill.general{background:#DBEAFE;color:#1D4ED8}
    .portal-home__notice,.portal-home__action,.portal-home__activity-item{border:1px solid #D7E3F5;border-radius:12px;background:#fff;color:inherit;text-decoration:none}
    .portal-home__notice{display:grid;grid-template-columns:auto auto minmax(0,1fr);align-items:center;gap:10px;padding:14px}
    .portal-home__dot{width:10px;height:10px;border-radius:999px;background:#2563EB}
    .portal-home__notice.yellow .portal-home__dot{background:#D97706}
    .portal-home__notice.green .portal-home__dot{background:#16A34A}
    .portal-home__notice.red .portal-home__dot{background:#DC2626}
    .portal-home__notice strong{color:#0F172A;font-size:20px;font-weight:900}
    .portal-home__notice p{margin:0;color:#334155;font-weight:800}
    .portal-home__actions{grid-template-columns:repeat(2,minmax(0,1fr))}
    .portal-home__action{display:grid;gap:5px;padding:16px}
    .portal-home__action strong{color:#0F172A;font-weight:900}
    .portal-home__action span{color:#64748B;font-size:13px;font-weight:700;line-height:1.45}
    .portal-home__activity-item{display:grid;grid-template-columns:auto minmax(0,1fr);gap:12px;align-items:flex-start;padding:12px}
    .portal-home__activity-item strong{display:block;color:#0F172A;font-weight:900}
    .portal-home__activity-item small{display:block;margin-top:4px;color:#64748B;font-weight:800}
    .portal-home__empty{display:grid;gap:4px;padding:24px;border:1px dashed #CBD5E1;border-radius:12px;background:#fff;text-align:center}
    .portal-home__empty strong{color:#0F172A;font-weight:900}
    .portal-home__empty span{color:#64748B;font-weight:700}
    @media (max-width:1100px){.portal-home__grid--main,.portal-home__grid--support{grid-template-columns:1fr}}
    @media (max-width:720px){.portal-home__welcome,.portal-home__panel header{align-items:stretch;flex-direction:column}.portal-home__identity{text-align:left;min-width:0}.portal-home__welcome h1{font-size:28px}.portal-home__primary{width:100%}.portal-home__actions{grid-template-columns:1fr}}
</style>
@endpush
