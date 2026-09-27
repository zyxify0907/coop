@extends('layouts.app')

@section('title', 'Saham')
@section('page-title', 'Saham')
@section('page-subtitle', 'Rekod saham pelajar, staff dan rumusan keseluruhan.')

@section('content')
<style>
    :root {
        --line: #e2e8f0;
        --soft: #f8fafc;
        --text: #0f172a;
        --muted: #64748b;
        --blue: var(--secondary);
        --red: var(--danger);
        --green: #16a34a;
    }

    .page-heading {
        display: none;
    }

    .share-wrap {
        max-width: none;
        margin: 0 auto;
        padding: 0 0 48px;
    }

    .share-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 20px;
        margin-bottom: 24px;
        padding: 34px 36px;
        border: 1px solid var(--line);
        border-left: 5px solid var(--secondary);
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 14px 34px rgba(15, 23, 42, .055);
        flex-wrap: wrap;
    }

    .share-head h1 {
        margin: 0;
        color: var(--text);
        font-size: 38px;
        line-height: 1.15;
        font-weight: 900;
        letter-spacing: 0;
    }

    .share-head p {
        margin: 14px 0 0;
        color: var(--muted-2);
        font-size: 16px;
        font-weight: 800;
    }

    .tabs {
        display: inline-flex;
        gap: 8px;
        padding: 6px;
        border: 1px solid var(--line);
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .04);
    }

    .tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding: 0 16px;
        border-radius: 10px;
        color: #334155;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }

    .tab.is-active {
        background: var(--blue);
        color: #fff;
        box-shadow: 0 10px 22px rgba(30, 64, 175, .16);
    }

    .head-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .alert {
        padding: 13px 15px;
        border: 1px solid var(--danger-soft);
        border-radius: 14px;
        background: var(--danger-soft);
        color: var(--primary-dark);
        margin-bottom: 16px;
    }

    .panel {
        border: 1px solid var(--line);
        border-radius: 22px;
        background: #fff;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, .055);
    }

    .panel-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 24px 28px;
        border-bottom: 1px solid var(--line);
        background: #fff;
        flex-wrap: wrap;
    }

    .panel-head h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 900;
        color: var(--text);
    }

    .panel-head span {
        color: var(--muted);
        display: block;
        margin-top: 8px;
        font-size: 14px;
        font-weight: 700;
    }

    .share-filter {
        display: grid;
        grid-template-columns: 240px 168px 210px 210px 210px auto;
        column-gap: 8px;
        row-gap: 10px;
        padding: 16px 18px;
        border-bottom: 1px solid var(--line);
        background: #fbfdff;
        align-items: end;
        justify-content: start;
    }

    .share-filter .field {
        grid-column: auto;
    }

    .share-filter.share-filter--staff {
        grid-template-columns: 240px 168px 210px 230px auto;
    }

    .share-filter .field.search-compact {
        width: 240px;
        max-width: 240px;
    }

    .share-filter .field.date-compact {
        width: 168px;
        max-width: 168px;
    }

    .share-filter input,
    .share-filter select {
        min-height: 44px;
        border-radius: 12px;
        background: #fff;
    }

    .share-filter__actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 12px;
        padding: 18px;
        align-items: end;
    }

    .field {
        grid-column: span 12;
    }

    .field label {
        display: block;
        margin-bottom: 6px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #475569;
    }

    .field input,
    .field select {
        width: 100%;
        min-height: 42px;
        padding: 9px 11px;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: #fff;
        color: var(--text);
        font-size: 14px;
    }

    .button,
    .link-button {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        min-height: 40px;
        padding: 0 14px;
        border-radius: 12px;
        border: 1px solid transparent;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .button {
        background: var(--blue);
        color: #fff;
        box-shadow: 0 10px 22px rgba(30, 64, 175, .14);
    }

    .link-button {
        background: var(--secondary-soft);
        border-color: var(--secondary-soft);
        color: var(--secondary);
    }

    .link-button.danger {
        border-color: var(--danger-soft);
        color: var(--red);
        background: var(--danger-soft);
    }

    .link-button.export-button {
        border-color: #bbf7d0;
        background: #f0fdf4;
        color: #15803d;
    }

    .link-button.print-button {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: var(--secondary);
    }

    .profile-button {
        background: var(--secondary-soft);
        border-color: #bfdbfe;
        color: var(--secondary);
        box-shadow: none;
    }

    .profile-button:hover {
        background: var(--secondary);
        border-color: var(--secondary);
        color: #fff;
    }

    .table-wrap {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        border-top: 1px solid var(--line);
        background: #fff;
        box-shadow: inset -18px 0 20px -24px rgba(15, 23, 42, .35);
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 1180px;
    }

    th {
        padding: 14px 16px;
        border-bottom: 1px solid var(--line);
        background: var(--soft);
        color: var(--muted);
        text-align: left;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    th:last-child,
    td:last-child {
        width: 210px;
        min-width: 210px;
        max-width: 210px;
        text-align: center !important;
    }

    td {
        padding: 16px;
        border-bottom: 1px solid var(--line);
        color: var(--text);
        vertical-align: middle;
        font-size: 14px;
    }

    tbody tr:hover td {
        background: #f8fafc;
    }

    .name {
        font-weight: 900;
    }

    .sub {
        display: block;
        margin-top: 3px;
        color: var(--muted);
        font-size: 12px;
    }

    .amount {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        white-space: nowrap;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 108px;
        min-height: 32px;
        padding: 0 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-badge.active {
        background: #dcfce7;
        color: #166534;
    }

    .status-badge.inactive {
        background: #fee2e2;
        color: #b91c1c;
    }

    .actions-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
        min-width: 0;
    }

    .staff-share-table .actions-cell {
        gap: 12px;
    }

    .staff-share-table {
        min-width: 1260px;
        table-layout: fixed;
    }

    .staff-share-table th {
        white-space: normal;
        line-height: 1.25;
        padding: 13px 10px;
        letter-spacing: .02em;
    }

    .staff-share-table td {
        padding: 15px 10px;
    }

    .staff-share-table th:nth-child(1),
    .staff-share-table td:nth-child(1) { width: 54px; }
    .staff-share-table th:nth-child(2),
    .staff-share-table td:nth-child(2) { width: 180px; }
    .staff-share-table th:nth-child(3),
    .staff-share-table td:nth-child(3) { width: 128px; }
    .staff-share-table th:nth-child(4),
    .staff-share-table td:nth-child(4) { width: 118px; }
    .staff-share-table th:nth-child(5),
    .staff-share-table td:nth-child(5) { width: 130px; }
    .staff-share-table th:nth-child(6),
    .staff-share-table td:nth-child(6) { width: 125px; }
    .staff-share-table th:nth-child(7),
    .staff-share-table td:nth-child(7),
    .staff-share-table th:nth-child(8),
    .staff-share-table td:nth-child(8),
    .staff-share-table th:nth-child(9),
    .staff-share-table td:nth-child(9),
    .staff-share-table th:nth-child(10),
    .staff-share-table td:nth-child(10) { width: 120px; }
    .staff-share-table th:nth-child(11),
    .staff-share-table td:nth-child(11) { width: 140px; text-align:center; }
    .staff-share-table th:nth-child(12),
    .staff-share-table td:nth-child(12) {
        width: 320px;
        min-width: 320px;
        max-width: 320px;
        text-align:center!important;
    }

    .student-share-table {
        min-width: 1500px;
        table-layout: fixed;
    }

    .student-share-table th {
        white-space: normal;
        line-height: 1.25;
        padding: 13px 10px;
        letter-spacing: .02em;
    }

    .student-share-table td {
        padding: 15px 10px;
    }

    .student-share-table th:nth-child(1),
    .student-share-table td:nth-child(1) { width: 54px; }
    .student-share-table th:nth-child(2),
    .student-share-table td:nth-child(2) { width: 170px; }
    .student-share-table th:nth-child(3),
    .student-share-table td:nth-child(3) { width: 120px; }
    .student-share-table th:nth-child(4),
    .student-share-table td:nth-child(4) { width: 120px; }
    .student-share-table th:nth-child(5),
    .student-share-table td:nth-child(5) { width: 105px; }
    .student-share-table th:nth-child(6),
    .student-share-table td:nth-child(6) { width: 90px; }
    .student-share-table th:nth-child(7),
    .student-share-table td:nth-child(7) { width: 125px; }
    .student-share-table th:nth-child(8),
    .student-share-table td:nth-child(8),
    .student-share-table th:nth-child(9),
    .student-share-table td:nth-child(9),
    .student-share-table th:nth-child(10),
    .student-share-table td:nth-child(10),
    .student-share-table th:nth-child(11),
    .student-share-table td:nth-child(11),
    .student-share-table th:nth-child(12),
    .student-share-table td:nth-child(12) { width: 118px; }
    .student-share-table th:nth-child(13),
    .student-share-table td:nth-child(13) { width: 130px; text-align:center; }
    .student-share-table th:nth-child(14),
    .student-share-table td:nth-child(14) {
        width: 280px;
        min-width: 280px;
        max-width: 280px;
        text-align:center!important;
    }

    .actions-cell form,
    .actions-cell > * {
        margin: 0;
    }

    .actions-cell form {
        display: flex;
        flex: 0 0 auto;
    }

    .actions-cell .button,
    .actions-cell .link-button,
    .actions-cell .profile-button {
        min-width: 96px;
        padding: 0 14px !important;
        white-space: nowrap;
    }

    .summary-table {
        max-width: 920px;
        min-width: 820px;
        margin: 0 auto;
        border-collapse: collapse;
    }

    .summary-table th,
    .summary-table td {
        border: 1px solid #111827;
        text-align: center;
        padding: 10px 14px;
        background: #fff;
        color: #000;
        font-weight: 700;
        text-transform: uppercase;
    }

    .summary-table th {
        font-size: 13px;
    }

    .summary-table .summary-label {
        text-align: center;
    }

    .summary-table tfoot td,
    .summary-table .summary-highlight td {
        background: #ffff00;
        font-weight: 900;
    }

    .summary-report-title {
        margin: 0;
        color: #000;
        font-size: 18px;
        line-height: 1.45;
        font-weight: 900;
        text-transform: uppercase;
    }

    .summary-year-form {
        display: flex;
        align-items: end;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .summary-year-form .field {
        min-width: 155px;
    }

    .summary-year-form select,
    .summary-year-form input {
        min-height: 40px;
        width: 100%;
        border: 1px solid var(--line);
        border-radius: 10px;
        background: #fff;
        padding: 0 12px;
        color: var(--text);
        font-weight: 800;
    }

    .empty {
        padding: 36px 18px;
        text-align: center;
        color: var(--muted);
        font-weight: 700;
    }

    .pagination {
        padding: 18px 24px;
        display: flex;
        justify-content: center;
    }

    .print-report {
        display: none;
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        *,
        *::before,
        *::after {
            overflow: visible !important;
            scrollbar-width: none !important;
        }

        *::-webkit-scrollbar {
            display: none !important;
        }

        html,
        body {
            width: auto !important;
            height: auto !important;
            overflow: visible !important;
        }

        body {
            background: #fff !important;
            font-family: Arial, Helvetica, sans-serif;
        }

        .panel,
        .share-wrap,
        .table-wrap {
            overflow: visible !important;
        }

        body * {
            visibility: hidden !important;
        }

        .print-report,
        .print-report * {
            visibility: visible !important;
        }

        .print-report {
            display: block !important;
            position: absolute;
            inset: 0 auto auto 0;
            width: 100%;
            padding: 0;
            color: #111827;
            background: #fff;
            font-family: Arial, sans-serif;
        }

        .print-report__title {
            margin: 0;
            padding: 0 0 6px;
            border-bottom: 1px solid #111827;
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .04em;
        }

        .print-report__meta {
            margin: 8px 0 10px;
            font-size: 9px;
        }

        .print-table {
            width: 100% !important;
            min-width: 0 !important;
            table-layout: fixed;
            border-collapse: collapse;
            border-spacing: 0;
            font-size: 8.3px;
            background: #fff;
        }

        .print-table thead {
            display: table-header-group;
        }

        .print-table tfoot {
            display: table-footer-group;
        }

        .print-table tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .print-table th,
        .print-table td {
            padding: 4px 4px;
            border: 1px solid #9ca3af;
            background: #fff !important;
            color: #111827 !important;
            vertical-align: middle;
            line-height: 1.2;
            word-break: normal;
            overflow-wrap: normal;
        }

        .print-table th {
            padding: 5px 4px;
            background: #f8fafc !important;
            font-size: 7.6px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
            white-space: nowrap !important;
        }

        .print-table td:nth-child(1),
        .print-table td:nth-child(3),
        .print-table td:nth-child(4),
        .print-table td:nth-child(5),
        .print-table td:nth-child(6),
        .print-table td:nth-child(7),
        .print-table td:nth-child(13) {
            text-align: center;
            white-space: nowrap !important;
        }

        .print-table td:nth-child(8),
        .print-table td:nth-child(9),
        .print-table td:nth-child(10),
        .print-table td:nth-child(11),
        .print-table td:nth-child(12) {
            text-align: right;
            white-space: nowrap !important;
            overflow-wrap: normal;
        }

        .print-table--staff td:nth-child(7),
        .print-table--staff td:nth-child(8),
        .print-table--staff td:nth-child(9),
        .print-table--staff td:nth-child(10) {
            text-align: right;
            white-space: nowrap !important;
            overflow-wrap: normal;
        }

        .print-table--staff td:nth-child(11) {
            text-align: center;
            white-space: nowrap !important;
        }

        .print-report__name {
            display: block;
            font-weight: 700;
            width: 100%;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            font-size: 9.5px !important;
            letter-spacing: 0;
        }

        .print-report__email {
            display: none !important;
        }
    }

    @media (min-width: 860px) {
        .field.col-4 { grid-column: span 4; }
        .field.col-2 { grid-column: span 2; }
        .field.actions { grid-column: span 2; }
    }

    @media (max-width: 1180px) {
        .share-filter {
            grid-template-columns: repeat(3, minmax(180px, 1fr));
        }

        .share-filter__actions {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 720px) {
        .share-wrap {
            padding: 0 0 36px;
        }

        .share-head {
            align-items: flex-start;
            flex-direction: column;
            padding: 24px;
        }

        .share-head h1 {
            font-size: 30px;
        }

        .share-filter {
            grid-template-columns: 1fr;
        }

        .share-filter .field.search-compact,
        .share-filter .field.date-compact {
            max-width: none;
        }

        .share-filter__actions {
            align-items: stretch;
            flex-direction: column;
        }

        .share-filter__actions .button,
        .share-filter__actions .link-button {
            width: 100%;
        }

        .tabs {
            width: 100%;
            overflow-x: auto;
        }

        .tab {
            flex: 1 0 auto;
        }
    }

    /* Saham has many financial columns. Keep the table wide inside its own
       scroller instead of squeezing the action column on small laptops. */
    .student-share-table th:nth-child(14),
    .student-share-table td:nth-child(14),
    .staff-share-table th:nth-child(12),
    .staff-share-table td:nth-child(12) {
        width: 344px;
        min-width: 344px;
        max-width: 344px;
    }

    .student-share-table .actions-cell,
    .staff-share-table .actions-cell {
        flex-wrap: nowrap;
        gap: 8px;
    }

    .student-share-table .actions-cell .button,
    .student-share-table .actions-cell .link-button,
    .student-share-table .actions-cell .profile-button,
    .staff-share-table .actions-cell .button,
    .staff-share-table .actions-cell .link-button,
    .staff-share-table .actions-cell .profile-button {
        min-width: 0;
        padding-inline: 12px !important;
    }

    @container coop-content (max-width: 1180px) {
        .share-filter,
        .share-filter.share-filter--staff {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .share-filter .field.search-compact,
        .share-filter .field.date-compact {
            width: auto;
            max-width: none;
        }

        .share-filter__actions {
            grid-column: 1 / -1;
            min-width: 0;
            justify-self: start;
            flex-wrap: wrap;
        }

        .table-wrap > .student-share-table {
            width: max-content !important;
            min-width: 1564px !important;
            table-layout: fixed !important;
        }

        .table-wrap > .staff-share-table {
            width: max-content !important;
            min-width: 1344px !important;
            table-layout: fixed !important;
        }
    }

    @container coop-content (max-width: 720px) {
        .share-filter,
        .share-filter.share-filter--staff {
            grid-template-columns: minmax(0, 1fr);
            padding: 14px;
        }

        .share-filter__actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
            gap: 8px;
        }

        .share-filter__actions .button,
        .share-filter__actions .link-button {
            width: 100%;
            min-width: 0;
            padding-inline: 10px;
        }
    }

    .content:has(> .share-wrap) {
        background: #F4F7FB;
    }

    .content .share-wrap .share-head {
        position: relative;
        align-items: center !important;
        margin-bottom: 18px !important;
        padding: 26px 28px !important;
        border: 1px solid #D7E2EF !important;
        border-left: 6px solid #082F59 !important;
        border-radius: 10px !important;
        background: #fff !important;
        background-image: none !important;
        box-shadow: 0 7px 18px rgba(8, 47, 89, .035) !important;
    }

    .content .share-wrap .share-head::before {
        content: "";
        position: absolute;
        top: 22px;
        left: 28px;
        width: 42px;
        height: 3px;
        border-radius: 999px;
        background: #ED1C2E;
    }

    .content .share-wrap .share-head h1 {
        margin: 14px 0 0 !important;
        color: #082F59 !important;
        font-size: 34px !important;
        line-height: 1.15 !important;
        font-weight: 900 !important;
    }

    .content .share-wrap .share-head p {
        margin: 8px 0 0 !important;
        color: #496487 !important;
        font-size: 15px !important;
        font-weight: 750 !important;
    }

    .content .share-wrap .panel {
        border: 1px solid #D7E2EF !important;
        border-radius: 10px !important;
        background: #fff !important;
        box-shadow: 0 7px 18px rgba(8, 47, 89, .035) !important;
    }

    .content .share-wrap .panel-head {
        padding: 22px 24px 18px !important;
        border-bottom: 0 !important;
        background: #fff !important;
    }

    .content .share-wrap .panel-head h2 {
        color: #082F59 !important;
        font-size: 24px !important;
        font-weight: 900 !important;
    }

    .content .share-wrap .panel-head h2::before {
        content: "";
        display: block;
        width: 42px;
        height: 3px;
        margin-bottom: 10px;
        border-radius: 999px;
        background: #ED1C2E;
    }

    .content .share-wrap .panel-head span {
        margin-top: 7px !important;
        color: #496487 !important;
        font-size: 14px !important;
        font-weight: 750 !important;
    }

    .content .share-wrap .tabs {
        gap: 8px !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    .content .share-wrap .tab {
        min-height: 38px !important;
        padding: 0 16px !important;
        border: 1px solid #B9CBE4 !important;
        border-radius: 7px !important;
        background: #fff !important;
        color: #0B5ED7 !important;
        font-size: 13px !important;
        font-weight: 900 !important;
    }

    .content .share-wrap .tab.is-active {
        border-color: #0B5ED7 !important;
        background: #0B5ED7 !important;
        color: #fff !important;
        box-shadow: 0 6px 14px rgba(11, 94, 215, .14) !important;
    }

    .content .share-wrap .share-filter,
    .content .share-wrap .share-filter.share-filter--staff {
        display: flex !important;
        align-items: end !important;
        gap: 10px !important;
        padding: 12px 24px 16px !important;
        border-top: 0 !important;
        border-bottom: 1px solid #D7E2EF !important;
        background: #fff !important;
        flex-wrap: nowrap !important;
    }

    .content .share-wrap .share-filter .field,
    .content .share-wrap .share-filter .field.search-compact,
    .content .share-wrap .share-filter .field.date-compact {
        width: auto !important;
        max-width: none !important;
        min-width: 0 !important;
        flex: 1 1 0 !important;
    }

    .content .share-wrap .share-filter .field.search-compact {
        flex-basis: 230px !important;
    }

    .content .share-wrap .share-filter .field.date-compact {
        flex: 0 0 150px !important;
    }

    .content .share-wrap .share-filter__actions {
        flex: 0 0 auto !important;
        display: flex !important;
        gap: 8px !important;
        align-items: center !important;
        flex-wrap: nowrap !important;
    }

    .content .share-wrap .field label {
        color: #496487 !important;
        font-size: 11px !important;
        font-weight: 900 !important;
    }

    .content .share-wrap .share-filter input,
    .content .share-wrap .share-filter select {
        min-height: 38px !important;
        border: 1px solid #D7E2EF !important;
        border-radius: 7px !important;
        color: #082F59 !important;
        font-size: 13px !important;
        font-weight: 700 !important;
    }

    .content .share-wrap .button,
    .content .share-wrap .link-button {
        min-height: 38px !important;
        border-radius: 7px !important;
        font-size: 12px !important;
        font-weight: 900 !important;
    }

    .content .share-wrap .button {
        background: #0B5ED7 !important;
        border-color: #0B5ED7 !important;
        color: #fff !important;
        box-shadow: 0 6px 14px rgba(11, 94, 215, .14) !important;
    }

    .content .share-wrap .link-button {
        border-color: #B9CBE4 !important;
        background: #fff !important;
        color: #0B5ED7 !important;
    }

    .content .share-wrap .link-button.danger {
        border-color: #F6C7C7 !important;
        background: #FDECEC !important;
        color: #C4162A !important;
    }

    .content .share-wrap .table-wrap {
        width: calc(100% - 32px) !important;
        margin: 0 16px 16px !important;
        overflow-x: auto !important;
        border: 1px solid #D7E2EF !important;
        border-radius: 8px !important;
        background: #fff !important;
        box-shadow: none !important;
    }

    .content .share-wrap .student-share-table,
    .content .share-wrap .staff-share-table {
        width: 100% !important;
        min-width: 0 !important;
        table-layout: fixed !important;
        border-collapse: collapse !important;
    }

    .content .share-wrap .student-share-table th,
    .content .share-wrap .student-share-table td,
    .content .share-wrap .staff-share-table th,
    .content .share-wrap .staff-share-table td {
        padding: 9px 8px !important;
        border-bottom: 1px solid #D7E2EF !important;
        color: #082F59 !important;
        font-size: 12px !important;
        line-height: 1.3 !important;
        vertical-align: middle !important;
    }

    .content .share-wrap .student-share-table th,
    .content .share-wrap .staff-share-table th {
        background: #F8FBFF !important;
        color: #496487 !important;
        font-size: 10px !important;
        font-weight: 900 !important;
        letter-spacing: .03em !important;
        text-transform: uppercase !important;
    }

    .content .share-wrap .student-share-table th:nth-child(1),
    .content .share-wrap .student-share-table td:nth-child(1),
    .content .share-wrap .staff-share-table th:nth-child(1),
    .content .share-wrap .staff-share-table td:nth-child(1) {
        width: 40px !important;
        min-width: 40px !important;
        max-width: 40px !important;
        text-align: center !important;
    }

    .content .share-wrap .student-share-table th:nth-child(2),
    .content .share-wrap .student-share-table td:nth-child(2) {
        width: 180px !important;
        min-width: 180px !important;
        max-width: 180px !important;
    }

    .content .share-wrap .staff-share-table th:nth-child(2),
    .content .share-wrap .staff-share-table td:nth-child(2) {
        width: 210px !important;
        min-width: 210px !important;
        max-width: 210px !important;
    }

    .content .share-wrap .student-share-table th:last-child,
    .content .share-wrap .student-share-table td:last-child,
    .content .share-wrap .staff-share-table th:last-child,
    .content .share-wrap .staff-share-table td:last-child {
        width: 150px !important;
        min-width: 150px !important;
        max-width: 150px !important;
        text-align: center !important;
    }

    .content .share-wrap .student-share-table .name,
    .content .share-wrap .staff-share-table .name {
        display: block !important;
        color: #082F59 !important;
        font-size: 12px !important;
        font-weight: 900 !important;
        line-height: 1.25 !important;
        white-space: normal !important;
        overflow-wrap: anywhere !important;
    }

    .content .share-wrap .amount {
        color: #082F59 !important;
        font-family: inherit !important;
        font-size: 12px !important;
        font-weight: 750 !important;
        white-space: nowrap !important;
    }

    .content .share-wrap .status-badge {
        min-width: 62px !important;
        min-height: 24px !important;
        padding: 0 9px !important;
        border-radius: 999px !important;
        font-size: 11px !important;
        font-weight: 900 !important;
    }

    .content .share-wrap .status-badge.active {
        border: 1px solid #B8EFCB !important;
        background: #DDF8E7 !important;
        color: #128A4A !important;
    }

    .content .share-wrap .actions-cell {
        display: flex !important;
        justify-content: center !important;
        gap: 6px !important;
        flex-wrap: wrap !important;
    }

    .content .share-wrap .actions-cell .button,
    .content .share-wrap .actions-cell .link-button,
    .content .share-wrap .actions-cell .profile-button {
        width: 88px !important;
        min-width: 88px !important;
        min-height: 30px !important;
        padding: 0 9px !important;
        border-radius: 6px !important;
        font-size: 11px !important;
    }

    @media (max-width: 1180px) {
        .content .share-wrap .share-filter,
        .content .share-wrap .share-filter.share-filter--staff {
            flex-wrap: wrap !important;
        }

        .content .share-wrap .share-filter__actions {
            flex-wrap: wrap !important;
        }

        .content .share-wrap .student-share-table,
        .content .share-wrap .staff-share-table {
            min-width: 1180px !important;
        }
    }
</style>

@php
    $money = fn ($value) => 'RM ' . number_format((float) $value, 2);
    $summaryNumber = function ($value): string {
        $formatted = number_format((float) $value, 2, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    };
    $studentStatusLabel = function ($record) use ($inactiveReasons) {
        if ($record->ahli->status_aktif ?? false) {
            return 'Aktif';
        }

        return $inactiveReasons[$record->id_ahli] ?? 'Pindah / Berhenti';
    };
    $staffStatusLabel = function ($staff) use ($inactiveStaffReasons) {
        if ($staff->status_aktif) {
            return 'Aktif';
        }

        return $inactiveStaffReasons[$staff->id_pekerja] ?? 'Pindah / Berhenti';
    };
@endphp

<div class="share-wrap">
    <div class="share-head">
        <div>
            <h1>Saham</h1>
            <p>Layout digital berdasarkan fail saham pelajar, saham staff dan rumusan saham.</p>
        </div>

        <div class="head-actions">
            @if ($category === 'rumusan')
                <a class="link-button" href="{{ route('admin.saham.export.csv', array_merge(request()->query(), ['kategori' => $category])) }}" download>CSV</a>
            @endif
            <nav class="tabs" aria-label="Kategori saham">
                <a class="tab {{ $category === 'pelajar' ? 'is-active' : '' }}" href="{{ route('admin.saham.index', ['kategori' => 'pelajar']) }}">Pelajar</a>
                <a class="tab {{ $category === 'staff' ? 'is-active' : '' }}" href="{{ route('admin.saham.index', ['kategori' => 'staff']) }}">Staff</a>
                <a class="tab {{ $category === 'rumusan' ? 'is-active' : '' }}" href="{{ route('admin.saham.index', ['kategori' => 'rumusan']) }}">Rumusan Saham</a>
            </nav>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    @if ($category === 'pelajar')
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2>Saham Pelajar</h2>
                    <span>{{ $records->total() }} rekod pelajar. Tambah saham dibuat melalui page permohonan.</span>
                </div>
            </div>

            <form class="share-filter" method="GET" action="{{ route('admin.saham.index') }}">
                <input type="hidden" name="kategori" value="pelajar">
                <div class="field search-compact">
                    <label for="student_search">Cari Pelajar</label>
                    <input id="student_search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama, no matrik atau IC">
                </div>
                <div class="field date-compact">
                    <label for="student_tarikh">Tarikh Daftar</label>
                    <input id="student_tarikh" name="tarikh" type="date" value="{{ $filters['tarikh'] ?? '' }}">
                </div>
                <div class="field">
                    <label for="student_status">Status</label>
                    <select id="student_status" name="status">
                        <option value="">Semua status</option>
                        <option value="aktif" @selected(($filters['status'] ?? '') === 'aktif')>Aktif</option>
                        <option value="tidak_aktif" @selected(($filters['status'] ?? '') === 'tidak_aktif')>Tidak Aktif</option>
                    </select>
                </div>
                <div class="field">
                    <label for="student_program">Program</label>
                    <select id="student_program" name="program">
                        <option value="">Semua program</option>
                        @foreach ($studentPrograms as $programOption)
                            <option value="{{ $programOption }}" @selected(($filters['program'] ?? '') === $programOption)>{{ $programOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="student_kelas">Kelas</label>
                    <select id="student_kelas" name="kelas">
                        <option value="">Semua kelas</option>
                        @foreach ($studentClasses as $classOption)
                            <option value="{{ $classOption }}" @selected(($filters['kelas'] ?? '') === $classOption)>{{ $classOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="share-filter__actions">
                    <button class="button" type="submit">Cari</button>
                    <a class="link-button export-button" href="{{ route('admin.saham.export.csv', array_merge(request()->query(), ['kategori' => 'pelajar'])) }}" download>Export CSV</a>
                    <button class="link-button print-button" type="button" onclick="openSahamPrintPreview()">Print</button>
                    <a class="link-button secondary" href="{{ route('admin.saham.index', ['kategori' => 'pelajar']) }}">Reset</a>
                </div>
            </form>

            <div class="table-wrap">
                <table class="student-share-table">
                    <thead>
                        <tr>
                            <th>Bil</th>
                            <th>Nama</th>
                            <th>No KP</th>
                            <th>No Matrik</th>
                            <th>Program</th>
                            <th>Kelas</th>
                            <th>Tarikh Daftar Ahli</th>
                            <th>Yuran Ahli</th>
                            <th>Saham Semasa</th>
                            <th>Tambahan Saham</th>
                            <th>Jumlah Saham</th>
                            <th>Status Pelajar</th>
                            <th style="text-align:center;">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            <tr>
                                <td>{{ $records->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="name">{{ $record->ahli->nama ?? '-' }}</span>
                                </td>
                                <td>{{ $record->ahli->nric ?? '-' }}</td>
                                <td>{{ $record->ahli->no_matrik ?? '-' }}</td>
                                <td>{{ $record->ahli->program ?? '-' }}</td>
                                <td>{{ $record->ahli->kelas ?? '-' }}</td>
                                <td>{{ optional($record->ahli->tarikh_daftar)->format('d/m/Y') ?? '-' }}</td>
                                <td><span class="amount">{{ $money($record->yuran) }}</span></td>
                                <td><span class="amount">{{ $money($record->syer) }}</span></td>
                                <td><span class="amount">{{ $money($record->tambahan_saham) }}</span></td>
                                <td><span class="amount">{{ $money((float) $record->syer + (float) $record->tambahan_saham) }}</span></td>
                                <td>
                                    <span class="status-badge {{ ($record->ahli->status_aktif ?? false) ? 'active' : 'inactive' }}">
                                        {{ $studentStatusLabel($record) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        @if (($role === 'admin' || ($role === 'staff' && ($user->staff_type ?? null) === \App\Models\Pekerja::SHARE_MANAGER_STAFF_TYPE)) && isset($memberProfiles[$record->id_ahli]))
                                            <a class="button profile-button" href="{{ route('admin.anggota.show', $memberProfiles[$record->id_ahli]) }}">Lihat Profil</a>
                                        @else
                                            <span class="link-button" aria-disabled="true">Profil Tiada</span>
                                        @endif
                                        @if ($role === 'admin' || ($role === 'staff' && ($user->staff_type ?? null) === \App\Models\Pekerja::SHARE_MANAGER_STAFF_TYPE))
                                            <form method="POST" action="{{ route('admin.saham.destroy', $record) }}" onsubmit="return confirm('Padam rekod saham ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="link-button danger" type="submit">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13"><div class="empty">Tiada rekod saham pelajar.</div></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($records->hasPages())
                <div class="pagination">{{ $records->links() }}</div>
            @endif

            <section class="print-report" aria-hidden="true">
                <h1 class="print-report__title">SENARAI SAHAM PELAJAR</h1>
                <p class="print-report__meta">Tarikh Cetakan: {{ now()->format('d/m/Y') }}</p>
                <table class="print-table">
                    <colgroup>
                        <col style="width: 3%;">
                        <col style="width: 19%;">
                        <col style="width: 9%;">
                        <col style="width: 10%;">
                        <col style="width: 6%;">
                        <col style="width: 5%;">
                        <col style="width: 8%;">
                        <col style="width: 7%;">
                        <col style="width: 8%;">
                        <col style="width: 8%;">
                        <col style="width: 8%;">
                        <col style="width: 9%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Bil</th>
                            <th>Nama</th>
                            <th>No KP</th>
                            <th>No Matrik</th>
                            <th>Program</th>
                            <th>Kelas</th>
                            <th>Tarikh<br>Daftar</th>
                            <th>Yuran</th>
                            <th>Saham<br>Semasa</th>
                            <th>Tambahan<br>Saham</th>
                            <th>Jumlah<br>Saham</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($printStudentRecords as $printRecord)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="print-report__name">{{ $printRecord->ahli->nama ?? '-' }}</span>
                                </td>
                                <td>{{ $printRecord->ahli->nric ?? '-' }}</td>
                                <td>{{ $printRecord->ahli->no_matrik ?? '-' }}</td>
                                <td>{{ $printRecord->ahli->program ?? '-' }}</td>
                                <td>{{ $printRecord->ahli->kelas ?? '-' }}</td>
                                <td>{{ optional($printRecord->ahli->tarikh_daftar)->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $money($printRecord->yuran) }}</td>
                                <td>{{ $money($printRecord->syer) }}</td>
                                <td>{{ $money($printRecord->tambahan_saham) }}</td>
                                <td>{{ $money((float) $printRecord->syer + (float) $printRecord->tambahan_saham) }}</td>
                                <td>{{ $studentStatusLabel($printRecord) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12">Tiada rekod saham pelajar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </section>
    @elseif ($category === 'staff')
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2>Saham Staff</h2>
                    <span>{{ $staffMembers->total() }} rekod staff dipaparkan dengan susunan yang sama seperti pelajar.</span>
                </div>
            </div>

            <form class="share-filter share-filter--staff" method="GET" action="{{ route('admin.saham.index') }}">
                <input type="hidden" name="kategori" value="staff">
                <div class="field search-compact">
                    <label for="staff_search">Cari Staff</label>
                    <input id="staff_search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama, no anggota staff atau IC">
                </div>
                <div class="field date-compact">
                    <label for="staff_tarikh">Tarikh Mula</label>
                    <input id="staff_tarikh" name="tarikh" type="date" value="{{ $filters['tarikh'] ?? '' }}">
                </div>
                <div class="field">
                    <label for="staff_status">Status</label>
                    <select id="staff_status" name="status">
                        <option value="">Semua status</option>
                        <option value="aktif" @selected(($filters['status'] ?? '') === 'aktif')>Aktif</option>
                        <option value="tidak_aktif" @selected(($filters['status'] ?? '') === 'tidak_aktif')>Tidak Aktif</option>
                    </select>
                </div>
                <div class="field">
                    <label for="staff_type">Jenis Staff</label>
                    <select id="staff_type" name="staff_type">
                        <option value="">Semua jenis staff</option>
                        @foreach ($staffTypes as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['staff_type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="share-filter__actions">
                    <button class="button" type="submit">Cari</button>
                    <a class="link-button export-button" href="{{ route('admin.saham.export.csv', array_merge(request()->query(), ['kategori' => 'staff'])) }}" download>Export CSV</a>
                    <button class="link-button print-button" type="button" onclick="openSahamPrintPreview()">Print</button>
                    <a class="link-button secondary" href="{{ route('admin.saham.index', ['kategori' => 'staff']) }}">Reset</a>
                </div>
            </form>

            <div class="table-wrap">
                <table class="staff-share-table">
                    <thead>
                        <tr>
                            <th>Bil</th>
                            <th>Nama</th>
                            <th>No Anggota Staff</th>
                            <th>No KP</th>
                            <th>Jenis Staff</th>
                            <th>Yuran Ahli</th>
                            <th>Saham Semasa</th>
                            <th>Tambahan Saham</th>
                            <th>Jumlah Saham</th>
                            <th>Status Staff</th>
                            <th style="text-align:right;">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($staffMembers as $staff)
                            @php
                                $staffShare = $staff->sahamStaff;
                                $approvedStaffApplication = $approvedStaffApplications->get($staff->no_pekerja);
                                $approvedStaffData = $approvedStaffApplication?->data_permohonan ?? [];
                                $staffMemberNumber = $staff->no_anggota ?? $approvedStaffData['no_anggota'] ?? '-';
                                $staffMemberFee = $staffShare->yuran ?? $approvedStaffData['yuran_anggota'] ?? 0;
                                $staffTotalShare = (float) ($staffShare->syer ?? 0) + (float) ($staffShare->tambahan_saham ?? 0);
                            @endphp
                            <tr>
                                <td>{{ $staffMembers->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="name">{{ $staff->nama }}</span>
                                </td>
                                <td>{{ $staffMemberNumber }}</td>
                                <td>{{ $staff->nric ?? '-' }}</td>
                                <td>{{ $staff->staff_type_label }}</td>
                                <td><span class="amount">{{ $money($staffMemberFee) }}</span></td>
                                <td><span class="amount">{{ $money($staffShare->syer ?? 0) }}</span></td>
                                <td><span class="amount">{{ $money($staffShare->tambahan_saham ?? 0) }}</span></td>
                                <td><span class="amount">{{ $money($staffTotalShare) }}</span></td>
                                <td>
                                    <span class="status-badge {{ $staff->status_aktif ? 'active' : 'inactive' }}">
                                        {{ $staffStatusLabel($staff) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        @if ($role === 'admin')
                                            <a class="button profile-button" href="{{ route('admin.users.edit', ['type' => 'staff', 'id' => $staff->id_pekerja]) }}">Lihat Profil</a>
                                        @endif
                                        @if ($role === 'admin' || ($role === 'staff' && ($user->staff_type ?? null) === \App\Models\Pekerja::SHARE_MANAGER_STAFF_TYPE))
                                            <form method="POST" action="{{ route('admin.saham.staff.destroy', $staff) }}" onsubmit="return confirm('Padam rekod saham staff ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="link-button danger" type="submit">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11"><div class="empty">Tiada rekod staff.</div></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($staffMembers->hasPages())
                <div class="pagination">{{ $staffMembers->links() }}</div>
            @endif

            <section class="print-report" aria-hidden="true">
                <h1 class="print-report__title">SENARAI SAHAM STAFF</h1>
                <p class="print-report__meta">Tarikh Cetakan: {{ now()->format('d/m/Y') }}</p>
                <table class="print-table print-table--staff">
                    <colgroup>
                        <col style="width: 4%;">
                        <col style="width: 23%;">
                        <col style="width: 11%;">
                        <col style="width: 10%;">
                        <col style="width: 13%;">
                        <col style="width: 9%;">
                        <col style="width: 8%;">
                        <col style="width: 8%;">
                        <col style="width: 8%;">
                        <col style="width: 6%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Bil</th>
                            <th>Nama</th>
                            <th>No Anggota Staff</th>
                            <th>No KP</th>
                            <th>Jenis Staff</th>
                            <th>Yuran<br>Ahli</th>
                            <th>Saham<br>Semasa</th>
                            <th>Tambahan<br>Saham</th>
                            <th>Jumlah<br>Saham</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($printStaffRecords as $printStaff)
                            @php
                                $printStaffShare = $printStaff->sahamStaff;
                                $approvedPrintStaffApplication = $approvedStaffApplications->get($printStaff->no_pekerja);
                                $approvedPrintStaffData = $approvedPrintStaffApplication?->data_permohonan ?? [];
                                $printStaffMemberNumber = $printStaff->no_anggota ?? $approvedPrintStaffData['no_anggota'] ?? '-';
                                $printStaffMemberFee = $printStaffShare->yuran ?? $approvedPrintStaffData['yuran_anggota'] ?? 0;
                                $printStaffTotalShare = (float) ($printStaffShare->syer ?? 0) + (float) ($printStaffShare->tambahan_saham ?? 0);
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="print-report__name">{{ $printStaff->nama ?? '-' }}</span>
                                </td>
                                <td>{{ $printStaffMemberNumber }}</td>
                                <td>{{ $printStaff->nric ?? '-' }}</td>
                                <td>{{ $printStaff->staff_type_label }}</td>
                                <td>{{ $money($printStaffMemberFee) }}</td>
                                <td>{{ $money($printStaffShare->syer ?? 0) }}</td>
                                <td>{{ $money($printStaffShare->tambahan_saham ?? 0) }}</td>
                                <td>{{ $money($printStaffTotalShare) }}</td>
                                <td>{{ $staffStatusLabel($printStaff) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10">Tiada rekod saham staff.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </section>
    @else
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2 class="summary-report-title">
                        REKOD PENAMBAHAN SAHAM ANGGOTA KOPERASI POLITEKNIK BESUT TEMPOH {{ $summary['period_start_label'] }} HINGGA {{ $summary['period_end_label'] }}
                    </h2>
                    <span>Rumusan digital berdasarkan nilai anggota dan saham dalam sistem.</span>
                </div>
                <form class="summary-year-form" method="GET" action="{{ route('admin.saham.index') }}">
                    <input type="hidden" name="kategori" value="rumusan">
                    <div class="field">
                        <label for="summary_member_type">Kategori</label>
                        <select id="summary_member_type" name="kategori_anggota">
                            <option value="all" @selected(($filters['kategori_anggota'] ?? 'all') === 'all')>Semua</option>
                            <option value="student" @selected(($filters['kategori_anggota'] ?? 'all') === 'student')>Pelajar</option>
                            <option value="staff" @selected(($filters['kategori_anggota'] ?? 'all') === 'staff')>Staff</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="summary_start_date">Tarikh Awal</label>
                        <input id="summary_start_date" type="date" name="tarikh_awal" value="{{ $filters['tarikh_awal'] ?? '' }}">
                    </div>
                    <div class="field">
                        <label for="summary_end_date">Tarikh Akhir</label>
                        <input id="summary_end_date" type="date" name="tarikh_akhir" value="{{ $filters['tarikh_akhir'] ?? '' }}">
                    </div>
                    <button class="button" type="submit">Papar</button>
                    <a class="link-button" href="{{ route('admin.saham.index', ['kategori' => 'rumusan']) }}">Reset</a>
                </form>
            </div>

            <div class="table-wrap">
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>SAHAM ANGGOTA</th>
                            <th>ANGGOTA</th>
                            <th>SAHAM</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (in_array($summary['member_type'], ['all', 'staff'], true))
                            <tr>
                                <td class="summary-label">STAFF</td>
                                <td>{{ $summary['period_staff_count'] }}</td>
                                <td>{{ $summaryNumber($summary['period_staff_total']) }}</td>
                            </tr>
                        @endif
                        @if (in_array($summary['member_type'], ['all', 'student'], true))
                            <tr>
                                <td class="summary-label">PELAJAR</td>
                                <td>{{ $summary['period_student_count'] }}</td>
                                <td>{{ $summaryNumber($summary['period_student_total']) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="summary-label">PENAMBAHAN ANGGOTA & SAHAM TEMPOH {{ $summary['period_start_label'] }} HINGGA {{ $summary['period_end_label'] }}</td>
                            <td>{{ $summary['period_count'] }}</td>
                            <td>{{ $summaryNumber($summary['period_total']) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">JUMLAH ANGGOTA & SAHAM TERKUMPUL SEBELUM {{ $summary['period_start_label'] }}</td>
                            <td>{{ $summary['previous_count'] }}</td>
                            <td>{{ $summaryNumber($summary['previous_total']) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">JUMLAH ANGGOTA & SAHAM TERKUMPUL TEMPOH {{ $summary['period_start_label'] }} HINGGA {{ $summary['period_end_label'] }}</td>
                            <td>{{ $summary['current_cumulative_count'] }}</td>
                            <td>{{ $summaryNumber($summary['current_cumulative_total']) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">JUMLAH ANGGOTA BERHENTI/BERPINDAH TEMPOH {{ $summary['period_start_label'] }} HINGGA {{ $summary['period_end_label'] }}</td>
                            <td>{{ $summary['stopped_count'] }}</td>
                            <td>{{ $summaryNumber($summary['stopped_share_total']) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="summary-highlight">
                            <td class="summary-label">JUMLAH ANGGOTA DAN SAHAM TEMPOH {{ $summary['period_start_label'] }} HINGGA {{ $summary['period_end_label'] }}</td>
                            <td>{{ $summary['active_count'] }}</td>
                            <td>{{ $summaryNumber($summary['active_total']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    @endif
</div>

@endsection

@push('scripts')
<script>
    function openSahamPrintPreview() {
        const report = document.querySelector('.print-report');

        if (!report) {
            return;
        }

        const title = report.querySelector('.print-report__title')?.textContent.trim() || 'Laporan Saham';
        const originalTitle = document.title;
        const printMarkup = report.outerHTML.replace(' aria-hidden="true"', '');
        const printStyles = document.createElement('style');
        const printRoot = document.createElement('div');

        printStyles.id = 'saham-print-styles';
        printStyles.textContent = `
            @page { size: A4 landscape; margin: 8mm; }
            @media print {
                body > *:not(#saham-print-root) { display: none !important; }
                #saham-print-root { display: block !important; color: #111827; background: #fff; font-family: Arial, sans-serif; }
                #saham-print-root .print-report { display: block !important; width: 100%; color: #111827; background: #fff; font-family: Arial, sans-serif; }
                #saham-print-root .print-report__title { margin: 0; padding: 0 0 6px; border-bottom: 1px solid #111827; text-align: center; font-size: 15px; font-weight: 700; letter-spacing: .04em; }
                #saham-print-root .print-report__meta { margin: 8px 0 10px; font-size: 9px; }
                #saham-print-root .print-table { width: 100% !important; min-width: 0 !important; border-collapse: collapse; border-spacing: 0; table-layout: fixed; font-size: 8px; line-height: 1.2; }
                #saham-print-root .print-table col { box-sizing: border-box; }
                #saham-print-root .print-table th,
                #saham-print-root .print-table td { box-sizing: border-box; padding: 3px 2px; border: 1px solid #94a3b8; color: #111827 !important; font-size: 8px !important; line-height: 1.2 !important; letter-spacing: 0 !important; vertical-align: middle; overflow: hidden !important; text-overflow: clip; white-space: nowrap; word-break: normal; overflow-wrap: normal; }
                #saham-print-root .print-table th:last-child,
                #saham-print-root .print-table td:last-child { width: auto !important; min-width: 0 !important; max-width: none !important; }
                #saham-print-root .print-table th { background: #e2e8f0 !important; font-size: 7px !important; font-weight: 700; text-align: center; text-transform: uppercase; white-space: normal !important; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
                #saham-print-root .print-table td:nth-child(1),
                #saham-print-root .print-table td:nth-child(3),
                #saham-print-root .print-table td:nth-child(4),
                #saham-print-root .print-table td:nth-child(5),
                #saham-print-root .print-table td:nth-child(6),
                #saham-print-root .print-table td:nth-child(7),
                #saham-print-root .print-table td:nth-child(12) { text-align: center; }
                #saham-print-root .print-table td:nth-child(8),
                #saham-print-root .print-table td:nth-child(9),
                #saham-print-root .print-table td:nth-child(10),
                #saham-print-root .print-table td:nth-child(11) { text-align: right; }
                #saham-print-root .print-table--staff td:nth-child(6),
                #saham-print-root .print-table--staff td:nth-child(7),
                #saham-print-root .print-table--staff td:nth-child(8),
                #saham-print-root .print-table--staff td:nth-child(9) { text-align: right; }
                #saham-print-root .print-table--staff td:nth-child(10) { text-align: center; }
                #saham-print-root .print-report__name { display: block; width: 100%; overflow: hidden !important; text-overflow: ellipsis; white-space: nowrap !important; font-size: 8px !important; line-height: 1.2 !important; letter-spacing: 0 !important; font-weight: 700; }
            }
        `;

        printRoot.id = 'saham-print-root';
        printRoot.innerHTML = printMarkup;
        document.head.appendChild(printStyles);
        document.body.appendChild(printRoot);
        document.title = title;

        const cleanup = () => {
            printRoot.remove();
            printStyles.remove();
            document.title = originalTitle;
            window.removeEventListener('afterprint', cleanup);
        };

        window.addEventListener('afterprint', cleanup);
        window.print();
    }
</script>
@endpush
