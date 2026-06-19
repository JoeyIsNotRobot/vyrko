<style>
    .resume-actions-bar {
        position: sticky;
        top: 86px;
        z-index: 8;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        border: 1px solid var(--vscode-border, #3e3e42);
        border-radius: 18px;
        padding: 12px;
        margin-bottom: 18px;
        background: rgba(15, 17, 23, .95);
    }

    .resume-preview-frame {
        overflow: auto;
        border: 1px solid rgba(62, 62, 66, .88);
        border-radius: 22px;
        padding: clamp(14px, 3vw, 28px);
        background:
            linear-gradient(135deg, rgba(0, 122, 204, .14), transparent 34%),
            rgba(15, 17, 23, .72);
        box-shadow: 0 16px 42px rgba(0, 0, 0, .26);
    }

    .resume-preview-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 330px;
        gap: 18px;
        align-items: start;
    }

    .resume-side-panel {
        position: sticky;
        top: 166px;
        display: grid;
        gap: 14px;
    }

    .resume-document {
        width: min(100%, 860px);
        margin: 0 auto;
        border: 1px solid #dbe3ef;
        background: #ffffff;
        color: #172033;
        font-family: Arial, Helvetica, sans-serif;
        line-height: 1.48;
        padding: clamp(28px, 5vw, 54px);
        box-shadow: 0 18px 44px rgba(0, 0, 0, .28);
    }

    .resume-document * {
        box-sizing: border-box;
    }

    .resume-document h1,
    .resume-document h2,
    .resume-document h3,
    .resume-document p,
    .resume-document ul {
        margin-top: 0;
    }

    .resume-document h1 {
        color: #0f172a;
        font-size: 34px;
        line-height: 1.04;
        letter-spacing: -.03em;
        margin-bottom: 8px;
    }

    .resume-document h2 {
        color: #0f172a;
        font-size: 13px;
        letter-spacing: .12em;
        text-transform: uppercase;
        margin-bottom: 12px;
    }

    .resume-document h3 {
        color: #172033;
        font-size: 16px;
        margin-bottom: 4px;
    }

    .resume-document p,
    .resume-document li {
        color: #334155;
        font-size: 13.5px;
    }

    .resume-document ul {
        padding-left: 18px;
        margin-bottom: 0;
    }

    .resume-header {
        border-bottom: 2px solid #0f172a;
        padding-bottom: 18px;
        margin-bottom: 22px;
    }

    .resume-headline {
        color: #1d4ed8;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .resume-contact {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 12px;
        color: #475569;
        font-size: 12.5px;
    }

    .resume-section {
        break-inside: avoid;
        margin-bottom: 20px;
    }

    .resume-item {
        break-inside: avoid;
        margin-bottom: 14px;
    }

    .resume-meta {
        color: #64748b;
        font-size: 12.5px;
        font-weight: 700;
        margin-bottom: 7px;
    }

    .resume-skills {
        display: grid;
        gap: 10px;
    }

    .resume-skill-row strong {
        color: #0f172a;
    }

    .resume-pill-list {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .resume-pill {
        display: inline-flex;
        border: 1px solid #dbe3ef;
        border-radius: 999px;
        padding: 4px 8px;
        color: #1f2937;
        background: #f8fafc;
        font-size: 12px;
        font-weight: 700;
    }

    .resume-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px 22px;
    }

    .resume-document.tech-compact {
        padding: 38px;
        border-top: 8px solid #007acc;
        font-family: "Geist Sans", Arial, Helvetica, sans-serif;
    }

    .resume-document.tech-compact .resume-header {
        border-bottom: 1px solid #cbd5e1;
    }

    .resume-tech-layout {
        display: grid;
        grid-template-columns: 230px minmax(0, 1fr);
        gap: 26px;
        align-items: start;
    }

    .resume-sidebar {
        border-radius: 16px;
        padding: 18px;
        background: #f1f5f9;
    }

    .resume-sidebar .resume-section {
        margin-bottom: 18px;
    }

    .resume-document.international-clean {
        border: 0;
        border-radius: 4px;
        font-family: Georgia, "Times New Roman", serif;
    }

    .resume-document.international-clean .resume-header {
        border-bottom: 0;
        padding-bottom: 0;
        text-align: center;
    }

    .resume-document.international-clean .resume-headline {
        color: #0f766e;
    }

    .resume-document.international-clean h2 {
        border-bottom: 1px solid #cbd5e1;
        padding-bottom: 7px;
        letter-spacing: .16em;
    }

    .resume-document.ats-classic {
        box-shadow: none;
    }

    .resume-print-page {
        min-height: 100vh;
        padding: 24px;
        background: #e5e7eb;
    }

    .resume-print-shell {
        width: min(100%, 940px);
        margin: 0 auto;
    }

    @media (max-width: 760px) {
        .resume-actions-bar,
        .resume-preview-layout,
        .resume-grid,
        .resume-tech-layout {
            grid-template-columns: 1fr;
            display: grid;
        }

        .resume-side-panel {
            position: static;
        }

        .resume-actions-bar .actions {
            width: 100%;
        }
    }

    @media print {
        @page {
            size: A4;
            margin: 13mm;
        }

        body {
            background: #fff !important;
        }

        .no-print,
        .resume-actions-bar {
            display: none !important;
        }

        .resume-print-page,
        .resume-preview-frame {
            padding: 0 !important;
            background: #fff !important;
            border: 0 !important;
            box-shadow: none !important;
        }

        .resume-print-shell,
        .resume-document {
            width: 100% !important;
            margin: 0 !important;
        }

        .resume-document {
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
    }
</style>
