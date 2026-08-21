@push('styles')
<style>
    .event-admin,.event-form{display:grid;gap:18px}
    .event-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;padding:28px 30px;border:1px solid var(--line);border-radius:18px;background:#fff;box-shadow:0 14px 34px rgba(15,23,42,.055)}
    .event-head span{color:var(--secondary);font-size:12px;font-weight:900;letter-spacing:.08em}
    .event-head h1{margin:6px 0 0;color:var(--text);font-size:30px;line-height:1.15;font-weight:900}
    .event-head p{margin:8px 0 0;color:var(--muted);font-weight:700}
    .event-head a,.event-actions a,.event-actions button{display:inline-flex;align-items:center;justify-content:center;min-height:42px;border:1px solid #BFD7FF;border-radius:8px;background:var(--secondary);color:#fff;padding:0 16px;text-decoration:none;font-weight:900}
    .event-panel{overflow:hidden;border:1px solid var(--line);border-radius:16px;background:#fff}
    .event-table{width:100%;border-collapse:collapse}
    .event-table th{background:#F8FAFC;color:#475569;font-size:12px;font-weight:900;letter-spacing:.04em;text-align:left;text-transform:uppercase}
    .event-table th,.event-table td{padding:16px 18px;border-bottom:1px solid var(--line);vertical-align:middle}
    .event-table td strong{display:block;color:var(--text);font-weight:900}
    .event-table td span{display:block;color:var(--muted-2);font-size:13px;font-weight:700}
    .event-status{display:inline-flex!important;align-items:center;justify-content:center;min-width:96px;border-radius:999px;padding:7px 12px;font-size:13px!important;font-weight:900!important}
    .event-status.active{background:var(--success-soft);color:var(--success)!important}
    .event-status.inactive{background:var(--danger-soft);color:var(--danger)!important}
    .event-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
    .event-actions a{background:#fff;color:var(--secondary)}
    .event-actions button{border-color:#FECACA;background:#FEF2F2;color:#B91C1C}
    .event-empty{text-align:center;color:var(--muted);font-weight:800}
    .event-fields{display:grid;gap:16px;padding:24px}
    .event-fields label{display:grid;gap:8px;color:var(--ink);font-weight:900}
    .event-fields input,.event-fields textarea{width:100%;border:1px solid var(--line-strong);border-radius:8px;background:#fff;padding:12px 14px;color:var(--text);font-weight:700}
    .event-fields textarea{resize:vertical}
    .event-check{display:flex!important;align-items:center;gap:10px}
    .event-check input{width:18px;height:18px}
    @media (max-width:720px){.event-head{align-items:stretch;flex-direction:column}.event-head a,.event-actions>*{width:100%}.event-actions{width:100%}}
</style>
@endpush
