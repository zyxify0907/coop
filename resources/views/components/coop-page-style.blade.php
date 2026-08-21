<style>
    .coop-wrap { display: grid; gap: 24px; }
    .coop-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        padding: 0;
        background: transparent;
        border: 0;
    }
    .coop-header h1 { margin: 10px 0 6px; font-size: clamp(25px, 3vw, 32px); line-height: 1.2; }
    .coop-header p { margin: 0; max-width: 760px; color: var(--muted); font-size: 14px; line-height: 1.55; }
    .coop-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 22px 24px;
        border: 1px solid var(--line);
        border-left: 4px solid #2453A6;
        border-radius: 10px;
        background: #FFFFFF;
        box-shadow: none;
    }
    .coop-hero h1 { margin: 10px 0 6px; font-size: clamp(25px, 3vw, 32px); line-height: 1.2; }
    .coop-hero p { margin: 0; max-width: 760px; color: var(--muted); font-size: 14px; line-height: 1.55; }
    .coop-kicker {
        display: inline-flex;
        align-items: center;
        min-height: 24px;
        padding: 0 8px;
        border-radius: 4px;
        background: #DBEAFE;
        color: #1D4ED8;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .06em;
    }
    .coop-panel { overflow: hidden; background: #fff; border: 1px solid var(--line); border-radius: 10px; }
    .coop-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--line);
    }
    .coop-panel-head h2 { margin: 0; font-size: 19px; }
    .coop-panel-head p { margin: 5px 0 0; color: var(--muted); font-size: 14px; }
    .coop-body { padding: 20px; }
    .coop-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    .coop-field { display: grid; gap: 6px; }
    .coop-field label { color: var(--muted); font-size: 12px; font-weight: 600; text-transform: uppercase; }
    .coop-actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .coop-table-wrap { overflow-x: auto; }
    .coop-table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 880px; }
    .coop-table th {
        padding: 13px 16px;
        background: #f8fafc;
        border-bottom: 1px solid var(--line);
        color: var(--muted);
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .coop-table td { padding: 14px 16px; border-bottom: 1px solid var(--line); vertical-align: middle; }
    .coop-table tr:last-child td { border-bottom: 0; }
    .coop-name { display: block; font-weight: 900; }
    .coop-sub { display: block; margin-top: 3px; color: var(--muted); font-size: 12px; }
    .coop-pill {
        display: inline-flex;
        align-items: center;
        min-height: 24px;
        padding: 0 8px;
        border-radius: 999px;
        background: var(--secondary-soft);
        color: var(--secondary);
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }
    .coop-pill.success { background: var(--success-soft); color: var(--success); }
    .coop-pill.warning { background: var(--warning-soft); color: var(--warning); }
    .coop-pill.danger { background: var(--danger-soft); color: var(--danger); }
    .coop-button-soft {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding: 0 14px;
        border-radius: 7px;
        border: 0;
        background: var(--secondary-soft);
        color: var(--secondary);
        font-weight: 600;
        text-decoration: none;
    }
    .coop-button-danger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding: 0 14px;
        border-radius: 7px;
        border: 0;
        background: var(--danger-soft);
        color: var(--danger);
        font-weight: 600;
    }
    @media (max-width: 900px) {
        .coop-header { flex-direction: column; }
        .coop-hero, .coop-panel-head { align-items: flex-start; flex-direction: column; padding: 26px 22px; }
        .coop-body { padding: 22px; }
        .coop-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .coop-wrap { gap: 18px; }
        .coop-hero, .coop-panel-head { padding: 18px 16px; }
        .coop-body { padding: 16px; }
        .coop-actions { align-items: stretch; }
        .coop-actions > * { flex: 1 1 100%; }
        .coop-table { min-width: 720px; }
    }
</style>
