@extends('layouts.app')
@section('title', 'Profil Pekerja — ' . $worker->nama)

@php
    $backUrl = request()->is('pengawas/*') ? '/pengawas/profiling' : '/admin/pekerja';
    $accentColors = ['#0f766e', '#3b82f6', '#8b5cf6', '#f59e0b', '#ec4899', '#06b6d4'];
    $initials = collect(explode(' ', $worker->nama))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
    $avatarColor = $accentColors[$worker->id % count($accentColors)];

    $prioritasStyles = [
        'tinggi'    => ['bg' => '#fef2f2', 'fg' => '#dc2626', 'border' => '#fecaca', 'icon' => 'alert-circle'],
        'sedang'    => ['bg' => '#fffbeb', 'fg' => '#d97706', 'border' => '#fde68a', 'icon' => 'alert-triangle'],
        'rendah'    => ['bg' => '#f0fdf4', 'fg' => '#16a34a', 'border' => '#bbf7d0', 'icon' => 'check-circle'],
        'tidak_layak' => ['bg' => '#f8fafc', 'fg' => '#64748b', 'border' => '#e2e8f0', 'icon' => 'x-circle'],
    ];
    $prioritas = $worker->prioritas ?? 'sedang';
    $pStyle = $prioritasStyles[$prioritas] ?? $prioritasStyles['sedang'];

    $kesejahteraanStyles = [
        'Sangat Miskin'  => ['bg' => '#fef2f2', 'fg' => '#dc2626'],
        'Miskin'         => ['bg' => '#fff7ed', 'fg' => '#ea580c'],
        'Rentan Miskin'  => ['bg' => '#fffbeb', 'fg' => '#d97706'],
        'Sejahtera'      => ['bg' => '#f0fdf4', 'fg' => '#16a34a'],
        'Tidak Diketahui'=> ['bg' => '#f8fafc', 'fg' => '#64748b'],
    ];
    $kStyle = $kesejahteraanStyles[$worker->klasifikasi_kesejahteraan] ?? $kesejahteraanStyles['Tidak Diketahui'];

    $statusProgramStyles = [
        'aktif'       => ['bg' => '#ecfdf5', 'fg' => '#059669', 'label' => 'Aktif'],
        'lulus'       => ['bg' => '#eff6ff', 'fg' => '#2563eb', 'label' => 'Lulus Program'],
        'tidak_layak' => ['bg' => '#f8fafc', 'fg' => '#64748b', 'label' => 'Tidak Layak'],
    ];
    $spStyle = $statusProgramStyles[$worker->status_program ?? 'aktif'] ?? $statusProgramStyles['aktif'];

    $totalSkor = $worker->total_skor ?? $worker->skor_vulnerabilitas ?? 0;
    $skorPercent = min(100, round(($totalSkor / 30) * 100));
    $layakProgram = $totalSkor >= 6;

    $latestHist = $worker->profilingHistories->first();
    $sdgIndicators = [
        ['label' => 'SDG 1 — Tanpa Kemiskinan', 'icon' => 'home', 'value' => $worker->klasifikasi_kesejahteraan, 'color' => '#dc2626'],
        ['label' => 'SDG 2 — Tanpa Kelaparan', 'icon' => 'utensils', 'value' => $worker->frekuensi_makan ?? '—', 'color' => '#ea580c'],
        ['label' => 'SDG 3 — Kesehatan', 'icon' => 'heart-pulse', 'value' => $worker->status_gizi ?? ($worker->riwayat_penyakit ?: 'Normal'), 'color' => '#059669'],
        ['label' => 'SDG 4 — Pendidikan', 'icon' => 'graduation-cap', 'value' => $worker->pendidikan_terakhir ?? '—', 'color' => '#2563eb'],
        ['label' => 'SDG 6 — Air Bersih', 'icon' => 'droplets', 'value' => $worker->akses_air_bersih ?? '—', 'color' => '#0891b2'],
        ['label' => 'Sanitasi', 'icon' => 'bath', 'value' => $worker->kondisi_sanitasi ?? '—', 'color' => '#7c3aed'],
    ];

    $scheduleStatusLabels = [
        'completed'   => ['label' => 'Selesai', 'bg' => '#dcfce7', 'fg' => '#16a34a'],
        'in_progress' => ['label' => 'Berjalan', 'bg' => '#fef3c7', 'fg' => '#d97706'],
        'scheduled'   => ['label' => 'Terjadwal', 'bg' => '#f1f5f9', 'fg' => '#64748b'],
    ];
    $totalPendapatan = (float) ($worker->total_pendapatan ?? 0);
@endphp

@push('styles')
<style>
    .profil-page {
        padding: 0 0 3rem;
        max-width: 1280px;
        margin: 0 auto;
    }

    /* ── Hero ── */
    .profil-hero {
        background: linear-gradient(135deg, #1e3a5f 0%, #0f766e 50%, #0891b2 100%);
        padding: 2rem 2rem 5rem;
        position: relative;
        overflow: hidden;
    }
    .profil-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }
    .profil-hero-inner {
        position: relative;
        max-width: 1280px;
        margin: 0 auto;
    }
    .profil-back {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: rgba(255,255,255,0.75);
        font-size: 0.85rem;
        text-decoration: none;
        margin-bottom: 1.25rem;
        transition: color 0.2s;
    }
    .profil-back:hover { color: white; text-decoration: none; }
    .profil-hero-body {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }
    .profil-avatar {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        border: 4px solid rgba(255,255,255,0.3);
        box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        flex-shrink: 0;
    }
    .profil-hero-info h1 {
        color: white;
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0 0 0.35rem;
    }
    .profil-hero-meta {
        color: rgba(255,255,255,0.8);
        font-size: 0.9rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem 1rem;
        align-items: center;
    }
    .profil-hero-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    .profil-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.75rem;
    }
    .profil-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.75rem;
        border-radius: 99px;
        font-size: 0.78rem;
        font-weight: 600;
        border: 1px solid transparent;
    }
    .profil-hero-actions {
        margin-left: auto;
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
        align-self: flex-start;
    }
    .profil-btn-white {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.55rem 1.1rem;
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.35);
        border-radius: var(--radius-sm);
        color: white;
        font-size: 0.85rem;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.2s;
        backdrop-filter: blur(8px);
    }
    .profil-btn-white:hover {
        background: rgba(255,255,255,0.25);
        text-decoration: none;
        color: white;
    }

    /* ── Stats floating row ── */
    .profil-stats-wrap {
        padding: 0 2rem;
        margin-top: -3rem;
        position: relative;
        z-index: 2;
        max-width: 1280px;
        margin-left: auto;
        margin-right: auto;
    }
    .profil-stats {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 0.85rem;
    }
    @media (max-width: 900px) { .profil-stats { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 560px) { .profil-stats { grid-template-columns: repeat(2, 1fr); } }

    .profil-stat {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.1rem 1.15rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .profil-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    }
    .profil-stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .profil-stat-value {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--text-main);
        line-height: 1.1;
    }
    .profil-stat-label {
        font-size: 0.72rem;
        color: var(--text-muted);
        margin-top: 0.05rem;
    }

    /* ── Content area ── */
    .profil-content {
        padding: 1.75rem 2rem 0;
    }

    .profil-alert {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.85rem 1.15rem;
        margin-bottom: 1.25rem;
        border-radius: var(--radius-sm);
        font-size: 0.88rem;
        background: rgba(34, 197, 94, 0.08);
        border: 1px solid rgba(34, 197, 94, 0.25);
        color: #166534;
    }

    /* ── Tabs ── */
    .profil-tabs {
        display: flex;
        gap: 0.25rem;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 0.35rem;
        margin-bottom: 1.5rem;
        overflow-x: auto;
    }
    .profil-tab {
        flex: 1;
        min-width: fit-content;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.65rem 1.1rem;
        border: none;
        border-radius: calc(var(--radius-md) - 4px);
        background: transparent;
        color: var(--text-muted);
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
        font-family: inherit;
    }
    .profil-tab:hover { color: var(--text-main); background: var(--background); }
    .profil-tab.active {
        background: var(--primary);
        color: white;
        box-shadow: 0 2px 8px rgba(15,118,110,0.25);
    }

    /* ── Grid layout ── */
    .profil-grid {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 1.5rem;
        align-items: start;
    }
    @media (max-width: 960px) {
        .profil-grid { grid-template-columns: 1fr; }
    }

    /* ── Cards ── */
    .profil-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        overflow: hidden;
    }
    .profil-card-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border);
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--text-main);
    }
    .profil-card-header i { color: var(--primary); }
    .profil-card-body { padding: 1.25rem; }

    /* ── Info list ── */
    .profil-info-list {
        display: grid;
        gap: 0;
    }
    .profil-info-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .profil-info-item:last-child { border-bottom: none; }
    .profil-info-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--background);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: var(--text-muted);
    }
    .profil-info-label {
        font-size: 0.72rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.1rem;
    }
    .profil-info-value {
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--text-main);
    }
    .profil-info-value--warn { color: var(--warning); font-weight: 500; }

    /* ── Score gauge ── */
    .profil-score-gauge {
        text-align: center;
        padding: 1.5rem 1rem;
    }
    .profil-score-ring {
        position: relative;
        width: 140px;
        height: 140px;
        margin: 0 auto 1rem;
    }
    .profil-score-ring svg { transform: rotate(-90deg); }
    .profil-score-ring-value {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .profil-score-ring-value .num {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1;
    }
    .profil-score-ring-value .den {
        font-size: 0.75rem;
        color: var(--text-muted);
    }
    .profil-score-bar-wrap {
        margin-top: 1rem;
    }
    .profil-score-bar-label {
        display: flex;
        justify-content: space-between;
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-bottom: 0.35rem;
    }
    .profil-score-bar {
        height: 8px;
        background: #f1f5f9;
        border-radius: 99px;
        overflow: hidden;
    }
    .profil-score-bar-fill {
        height: 100%;
        border-radius: 99px;
        transition: width 0.6s ease;
    }

    /* ── SDG grid ── */
    .profil-sdg-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
    }
    @media (max-width: 700px) { .profil-sdg-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 420px) { .profil-sdg-grid { grid-template-columns: 1fr; } }

    .profil-sdg-item {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        padding: 0.85rem;
        background: var(--background);
        border-radius: var(--radius-sm);
        border: 1px solid #f1f5f9;
    }
    .profil-sdg-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .profil-sdg-label {
        font-size: 0.68rem;
        color: var(--text-muted);
        line-height: 1.3;
        margin-bottom: 0.15rem;
    }
    .profil-sdg-value {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-main);
    }

    /* ── Dimension bars ── */
    .profil-dim-bars { display: grid; gap: 0.85rem; }
    .profil-dim-row {
        display: grid;
        grid-template-columns: 100px 1fr 36px;
        align-items: center;
        gap: 0.75rem;
    }
    .profil-dim-label { font-size: 0.8rem; color: var(--text-muted); }
    .profil-dim-track {
        height: 10px;
        background: #f1f5f9;
        border-radius: 99px;
        overflow: hidden;
    }
    .profil-dim-fill { height: 100%; border-radius: 99px; }
    .profil-dim-score {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-main);
        text-align: right;
    }

    /* ── Comparison table ── */
    .profil-compare-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }
    .profil-compare-table thead tr {
        border-bottom: 2px solid var(--border);
    }
    .profil-compare-table th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--text-muted);
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .profil-compare-table td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .profil-compare-table tr:last-child td { border-bottom: none; }
    .profil-compare-awal {
        color: var(--text-muted);
        font-size: 0.85rem;
    }
    .profil-compare-sekarang {
        font-weight: 600;
        color: var(--text-main);
    }
    .profil-compare-arrow {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.75rem;
        padding: 0.15rem 0.5rem;
        border-radius: 99px;
        margin-left: 0.5rem;
    }
    .profil-compare-arrow--up { background: #fef2f2; color: #dc2626; }
    .profil-compare-arrow--down { background: #f0fdf4; color: #16a34a; }
    .profil-compare-arrow--same { background: #f8fafc; color: #64748b; }

    /* ── Timeline ── */
    .profil-timeline { position: relative; padding-left: 1.5rem; }
    .profil-timeline::before {
        content: '';
        position: absolute;
        left: 5px;
        top: 8px;
        bottom: 8px;
        width: 2px;
        background: var(--border);
    }
    .profil-timeline-item {
        position: relative;
        padding-bottom: 1.25rem;
    }
    .profil-timeline-item:last-child { padding-bottom: 0; }
    .profil-timeline-dot {
        position: absolute;
        left: -1.5rem;
        top: 4px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--primary);
        border: 2px solid white;
        box-shadow: 0 0 0 2px var(--primary);
    }
    .profil-timeline-date {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-bottom: 0.2rem;
    }
    .profil-timeline-body {
        font-size: 0.875rem;
        color: var(--text-main);
    }
    .profil-timeline-note {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 0.2rem;
    }

    /* ── Program & schedule cards ── */
    .profil-activity-list { display: grid; gap: 0.75rem; }
    .profil-activity-item {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        padding: 1rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .profil-activity-item:hover {
        border-color: rgba(15,118,110,0.3);
        box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    }
    .profil-activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .profil-activity-title {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--text-main);
        margin-bottom: 0.2rem;
    }
    .profil-activity-meta {
        font-size: 0.8rem;
        color: var(--text-muted);
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem 0.75rem;
        align-items: center;
    }
    .profil-activity-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.15rem 0.55rem;
        border-radius: 99px;
        font-size: 0.72rem;
        font-weight: 600;
    }
    .profil-lintas-desa {
        color: #d97706;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
    }

    /* ── History table ── */
    .profil-history-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.82rem;
    }
    .profil-history-table th {
        padding: 0.65rem 0.75rem;
        text-align: center;
        font-weight: 600;
        color: var(--text-muted);
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 2px solid var(--border);
        white-space: nowrap;
    }
    .profil-history-table th:first-child,
    .profil-history-table td:first-child { text-align: left; }
    .profil-history-table td {
        padding: 0.7rem 0.75rem;
        border-bottom: 1px solid #f1f5f9;
        text-align: center;
    }
    .profil-history-table tr:hover td { background: #fafbfc; }
    .profil-history-total {
        font-weight: 700;
        color: var(--primary);
    }

    /* ── Empty state ── */
    .profil-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        color: var(--text-muted);
    }
    .profil-empty-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--background);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.75rem;
        color: var(--text-muted);
    }

    /* ── Update form panel ── */
    .profil-update-panel {
        margin-top: 1.25rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        overflow: hidden;
    }
    .profil-update-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.85rem 1.15rem;
        background: var(--background);
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-main);
        list-style: none;
        gap: 0.5rem;
    }
    .profil-update-summary::-webkit-details-marker { display: none; }
    .profil-update-form {
        padding: 1.25rem;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        border-top: 1px solid var(--border);
    }
    @media (max-width: 600px) {
        .profil-update-form { grid-template-columns: 1fr; }
    }

    .profil-two-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
    @media (max-width: 768px) {
        .profil-two-col { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="profil-page animate-fade-in" x-data="{ tab: 'ringkasan' }">

    {{-- Hero --}}
    <div class="profil-hero">
        <div class="profil-hero-inner">
            <a href="{{ $backUrl }}" class="profil-back">
                <i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Kembali
            </a>
            <div class="profil-hero-body">
                <div class="profil-avatar" style="background: {{ $avatarColor }};">{{ $initials }}</div>
                <div class="profil-hero-info">
                    <h1>{{ $worker->nama }}</h1>
                    <div class="profil-hero-meta">
                        @if($usia !== null)
                            <span><i data-lucide="calendar" style="width:14px;height:14px;"></i> {{ $usia }} tahun</span>
                        @endif
                        <span><i data-lucide="user" style="width:14px;height:14px;"></i> {{ $worker->jenis_kelamin === 'P' ? 'Perempuan' : 'Laki-laki' }}</span>
                        @if($worker->desa_asal)
                            <span><i data-lucide="map-pin" style="width:14px;height:14px;"></i> {{ $worker->desa_asal }}</span>
                        @endif
                        <span><i data-lucide="briefcase" style="width:14px;height:14px;"></i> {{ $worker->pekerjaan_makro }}</span>
                    </div>
                    <div class="profil-badges">
                        <span class="profil-badge" style="background:{{ $pStyle['bg'] }};color:{{ $pStyle['fg'] }};border-color:{{ $pStyle['border'] }};">
                            <i data-lucide="{{ $pStyle['icon'] }}" style="width:13px;height:13px;"></i>
                            {{ $worker->prioritas_label }}
                        </span>
                        <span class="profil-badge" style="background:{{ $kStyle['bg'] }};color:{{ $kStyle['fg'] }};">
                            {{ $worker->klasifikasi_kesejahteraan }}
                        </span>
                        <span class="profil-badge" style="background:{{ $spStyle['bg'] }};color:{{ $spStyle['fg'] }};">
                            {{ $spStyle['label'] }}
                        </span>
                        @if($worker->kemampuan_utama)
                            <span class="profil-badge" style="background:rgba(255,255,255,0.15);color:white;border-color:rgba(255,255,255,0.3);">
                                <i data-lucide="wrench" style="width:13px;height:13px;"></i>
                                {{ $worker->kemampuan_utama }}
                            </span>
                        @endif
                    </div>
                </div>
                @if(auth()->user()->role === 'admin')
                <div class="profil-hero-actions">
                    <a href="/admin/pekerja?edit={{ $worker->id }}" class="profil-btn-white">
                        <i data-lucide="pencil" style="width:15px;height:15px;"></i> Edit Data
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="profil-stats-wrap">
        <div class="profil-stats">
            <div class="profil-stat">
                <div class="profil-stat-icon" style="background:rgba(15,118,110,0.1);color:var(--primary);">
                    <i data-lucide="bar-chart-2" style="width:20px;height:20px;"></i>
                </div>
                <div>
                    <div class="profil-stat-value">{{ $totalSkor ?: '—' }}</div>
                    <div class="profil-stat-label">Total Skor Profiling</div>
                </div>
            </div>
            <div class="profil-stat">
                <div class="profil-stat-icon" style="background:rgba(59,130,246,0.1);color:#3b82f6;">
                    <i data-lucide="map" style="width:20px;height:20px;"></i>
                </div>
                <div>
                    <div class="profil-stat-value">{{ $programs->count() }}</div>
                    <div class="profil-stat-label">Program Diikuti</div>
                </div>
            </div>
            <div class="profil-stat">
                <div class="profil-stat-icon" style="background:rgba(139,92,246,0.1);color:#8b5cf6;">
                    <i data-lucide="calendar-clock" style="width:20px;height:20px;"></i>
                </div>
                <div>
                    <div class="profil-stat-value">{{ $schedules->count() }}</div>
                    <div class="profil-stat-label">Jadwal Tugas</div>
                </div>
            </div>
            <div class="profil-stat">
                <div class="profil-stat-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b;">
                    <i data-lucide="banknote" style="width:20px;height:20px;"></i>
                </div>
                <div>
                    <div class="profil-stat-value" style="font-size:{{ $totalPendapatan >= 1000000 ? '1rem' : '1.3rem' }};">
                        @if($totalPendapatan > 0)
                            Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                        @else
                            Rp 0
                        @endif
                    </div>
                    <div class="profil-stat-label">Total Pendapatan (Tunai)</div>
                </div>
            </div>
            <div class="profil-stat">
                <div class="profil-stat-icon" style="background:{{ $layakProgram ? 'rgba(34,197,94,0.1)' : 'rgba(239,68,68,0.1)' }};color:{{ $layakProgram ? '#16a34a' : '#dc2626' }};">
                    <i data-lucide="{{ $layakProgram ? 'shield-check' : 'shield-x' }}" style="width:20px;height:20px;"></i>
                </div>
                <div>
                    <div class="profil-stat-value" style="font-size:1rem;">{{ $layakProgram ? 'Layak' : 'Tidak Layak' }}</div>
                    <div class="profil-stat-label">Status Kelayakan</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main content --}}
    <div class="profil-content">
        @if(session('success'))
            <div class="profil-alert">
                <i data-lucide="check-circle" style="width:18px;height:18px;flex-shrink:0;"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- Tabs --}}
        <div class="profil-tabs">
            <button class="profil-tab" :class="{ active: tab === 'ringkasan' }" @click="tab = 'ringkasan'">
                <i data-lucide="layout-dashboard" style="width:16px;height:16px;"></i> Ringkasan
            </button>
            <button class="profil-tab" :class="{ active: tab === 'profiling' }" @click="tab = 'profiling'">
                <i data-lucide="pie-chart" style="width:16px;height:16px;"></i> Profiling & SDG
            </button>
            <button class="profil-tab" :class="{ active: tab === 'aktivitas' }" @click="tab = 'aktivitas'">
                <i data-lucide="activity" style="width:16px;height:16px;"></i> Program & Jadwal
            </button>
        </div>

        {{-- Tab: Ringkasan --}}
        <div x-show="tab === 'ringkasan'" x-cloak>
            <div class="profil-grid">
                {{-- Sidebar info --}}
                <div style="display:grid;gap:1.25rem;">
                    <div class="profil-card">
                        <div class="profil-card-header">
                            <i data-lucide="contact" style="width:18px;height:18px;"></i> Data Pribadi
                        </div>
                        <div class="profil-card-body">
                            <div class="profil-info-list">
                                <div class="profil-info-item">
                                    <div class="profil-info-icon"><i data-lucide="phone" style="width:15px;height:15px;"></i></div>
                                    <div>
                                        <div class="profil-info-label">Kontak</div>
                                        <div class="profil-info-value">{{ $worker->no_telepon ?: '—' }}</div>
                                    </div>
                                </div>
                                <div class="profil-info-item">
                                    <div class="profil-info-icon"><i data-lucide="banknote" style="width:15px;height:15px;"></i></div>
                                    <div>
                                        <div class="profil-info-label">Total Pendapatan</div>
                                        <div class="profil-info-value">
                                            Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                                        </div>
                                        <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.15rem;">
                                            Akumulasi uang tunai dari insentif & reward program
                                        </div>
                                    </div>
                                </div>
                                <div class="profil-info-item">
                                    <div class="profil-info-icon"><i data-lucide="users" style="width:15px;height:15px;"></i></div>
                                    <div>
                                        <div class="profil-info-label">Keluarga</div>
                                        <div class="profil-info-value">{{ $worker->household?->kepala_keluarga ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="profil-info-item">
                                    <div class="profil-info-icon"><i data-lucide="home" style="width:15px;height:15px;"></i></div>
                                    <div>
                                        <div class="profil-info-label">Status Rumah</div>
                                        <div class="profil-info-value">{{ $worker->status_rumah ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="profil-info-item">
                                    <div class="profil-info-icon"><i data-lucide="map-pin" style="width:15px;height:15px;"></i></div>
                                    <div>
                                        <div class="profil-info-label">Alamat</div>
                                        <div class="profil-info-value">{{ $worker->alamat ?: '—' }}</div>
                                    </div>
                                </div>
                                @if($worker->riwayat_penyakit)
                                <div class="profil-info-item">
                                    <div class="profil-info-icon"><i data-lucide="heart-pulse" style="width:15px;height:15px;"></i></div>
                                    <div>
                                        <div class="profil-info-label">Riwayat Kesehatan</div>
                                        <div class="profil-info-value">{{ $worker->riwayat_penyakit }}</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Score gauge --}}
                    <div class="profil-card">
                        <div class="profil-card-header">
                            <i data-lucide="gauge" style="width:18px;height:18px;"></i> Skor Vulnerabilitas
                        </div>
                        <div class="profil-card-body profil-score-gauge">
                            @php
                                $circumference = 2 * 3.14159 * 58;
                                $dashOffset = $circumference * (1 - $skorPercent / 100);
                                $gaugeColor = $totalSkor >= 14 ? '#dc2626' : ($totalSkor >= 10 ? '#d97706' : ($totalSkor >= 6 ? '#2563eb' : '#64748b'));
                            @endphp
                            <div class="profil-score-ring">
                                <svg width="140" height="140" viewBox="0 0 140 140">
                                    <circle cx="70" cy="70" r="58" fill="none" stroke="#f1f5f9" stroke-width="12"/>
                                    <circle cx="70" cy="70" r="58" fill="none" stroke="{{ $gaugeColor }}" stroke-width="12"
                                            stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $dashOffset }}"
                                            stroke-linecap="round"/>
                                </svg>
                                <div class="profil-score-ring-value">
                                    <span class="num">{{ $totalSkor ?: '—' }}</span>
                                    <span class="den">dari 30</span>
                                </div>
                            </div>
                            <div style="font-size:0.875rem;font-weight:600;color:var(--text-main);">{{ $worker->status_kesejahteraan ?? 'Pending' }}</div>
                            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.2rem;">{{ $worker->prioritas_label }}</div>
                            <div class="profil-score-bar-wrap">
                                <div class="profil-score-bar-label">
                                    <span>Threshold layak: 6</span>
                                    <span>{{ $skorPercent }}%</span>
                                </div>
                                <div class="profil-score-bar">
                                    <div class="profil-score-bar-fill" style="width:{{ $skorPercent }}%;background:{{ $gaugeColor }};"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Main ringkasan --}}
                <div style="display:grid;gap:1.25rem;">
                    <div class="profil-card">
                        <div class="profil-card-header">
                            <i data-lucide="award" style="width:18px;height:18px;"></i> Kemampuan & Keahlian
                        </div>
                        <div class="profil-card-body">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
                                <div style="background:var(--background);border-radius:var(--radius-sm);padding:1rem;">
                                    <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.03em;margin-bottom:0.35rem;">Keahlian Utama</div>
                                    <div style="font-weight:600;font-size:0.95rem;">{{ $worker->kemampuan_utama ?: 'Belum diisi' }}</div>
                                </div>
                                <div style="background:var(--background);border-radius:var(--radius-sm);padding:1rem;">
                                    <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.03em;margin-bottom:0.35rem;">Sektor Pekerjaan</div>
                                    <div style="font-weight:600;font-size:0.95rem;">{{ $worker->pekerjaan_makro }}</div>
                                </div>
                            </div>
                            @if($worker->keahlian_kerja)
                                <p style="font-size:0.875rem;color:var(--text-muted);margin:0;line-height:1.6;">
                                    <strong style="color:var(--text-main);">Detail keahlian:</strong> {{ $worker->keahlian_kerja }}
                                </p>
                            @endif
                        </div>
                    </div>

                    @if($latestHist)
                    <div class="profil-card">
                        <div class="profil-card-header">
                            <i data-lucide="layers" style="width:18px;height:18px;"></i> Skor per Dimensi (Terbaru)
                        </div>
                        <div class="profil-card-body">
                            <div class="profil-dim-bars">
                                @foreach([
                                    ['label' => 'Makan', 'score' => $latestHist->skor_makan, 'max' => 8, 'color' => '#ea580c'],
                                    ['label' => 'Sanitasi', 'score' => $latestHist->skor_sanitasi, 'max' => 6, 'color' => '#7c3aed'],
                                    ['label' => 'Pendapatan', 'score' => $latestHist->skor_pendapatan, 'max' => 8, 'color' => '#dc2626'],
                                    ['label' => 'Pendidikan', 'score' => $latestHist->skor_pendidikan, 'max' => 8, 'color' => '#2563eb'],
                                ] as $dim)
                                <div class="profil-dim-row">
                                    <span class="profil-dim-label">{{ $dim['label'] }}</span>
                                    <div class="profil-dim-track">
                                        <div class="profil-dim-fill" style="width:{{ $dim['max'] > 0 ? round(($dim['score'] / $dim['max']) * 100) : 0 }}%;background:{{ $dim['color'] }};"></div>
                                    </div>
                                    <span class="profil-dim-score">{{ $dim['score'] }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="profil-card">
                        <div class="profil-card-header">
                            <i data-lucide="globe" style="width:18px;height:18px;"></i> Indikator SDG
                        </div>
                        <div class="profil-card-body">
                            <div class="profil-sdg-grid">
                                @foreach($sdgIndicators as $sdg)
                                <div class="profil-sdg-item">
                                    <div class="profil-sdg-icon" style="background:{{ $sdg['color'] }}15;color:{{ $sdg['color'] }};">
                                        <i data-lucide="{{ $sdg['icon'] }}" style="width:16px;height:16px;"></i>
                                    </div>
                                    <div>
                                        <div class="profil-sdg-label">{{ $sdg['label'] }}</div>
                                        <div class="profil-sdg-value">{{ $sdg['value'] }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab: Profiling --}}
        <div x-show="tab === 'profiling'" x-cloak>
            @if($worker->profiling_awal)
            <div class="profil-card" style="margin-bottom:1.25rem;">
                <div class="profil-card-header">
                    <i data-lucide="git-compare" style="width:18px;height:18px;"></i> Perbandingan Profiling Awal vs Sekarang
                </div>
                <div class="profil-card-body" style="padding:0;overflow-x:auto;">
                    <table class="profil-compare-table">
                        <thead>
                            <tr>
                                <th>Indikator</th>
                                <th>Saat Pendaftaran</th>
                                <th>Kondisi Sekarang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach([
                                'frekuensi_makan' => ['label' => 'Frekuensi Makan', 'sdg' => 'SDG 2'],
                                'status_gizi' => ['label' => 'Status Gizi', 'sdg' => 'SDG 2'],
                                'kondisi_sanitasi' => ['label' => 'Sanitasi', 'sdg' => 'SDG 6'],
                                'pendidikan_terakhir' => ['label' => 'Pendidikan', 'sdg' => 'SDG 4'],
                                'skor_vulnerabilitas' => ['label' => 'Skor Vulnerabilitas', 'sdg' => ''],
                            ] as $key => $meta)
                            @php
                                $awal = $worker->profiling_awal[$key] ?? '—';
                                $sekarang = $key === 'skor_vulnerabilitas' ? ($worker->skor_vulnerabilitas ?? '—') : ($worker->$key ?? '—');
                                $changed = $awal !== '—' && $sekarang !== '—' && $awal != $sekarang;
                                if ($key === 'skor_vulnerabilitas' && is_numeric($awal) && is_numeric($sekarang)) {
                                    $diff = $sekarang - $awal;
                                    $arrowClass = $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'same');
                                    $arrowLabel = $diff > 0 ? "+{$diff}" : ($diff < 0 ? "{$diff}" : '=');
                                } else {
                                    $arrowClass = $changed ? 'up' : 'same';
                                    $arrowLabel = $changed ? 'Berubah' : 'Sama';
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-weight:500;">{{ $meta['label'] }}</div>
                                    @if($meta['sdg'])
                                        <div style="font-size:0.72rem;color:var(--text-muted);">{{ $meta['sdg'] }}</div>
                                    @endif
                                </td>
                                <td class="profil-compare-awal">{{ $awal }}</td>
                                <td>
                                    <span class="profil-compare-sekarang">{{ $sekarang }}</span>
                                    @if($changed || ($key === 'skor_vulnerabilitas' && isset($diff) && $diff != 0))
                                        <span class="profil-compare-arrow profil-compare-arrow--{{ $arrowClass }}">
                                            @if($key === 'skor_vulnerabilitas')
                                                <i data-lucide="{{ $diff > 0 ? 'trending-up' : ($diff < 0 ? 'trending-down' : 'minus') }}" style="width:12px;height:12px;"></i>
                                                {{ $arrowLabel }}
                                            @else
                                                <i data-lucide="refresh-cw" style="width:12px;height:12px;"></i> {{ $arrowLabel }}
                                            @endif
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding:1rem 1.25rem;border-top:1px solid var(--border);display:flex;gap:0.75rem;flex-wrap:wrap;">
                    @if(auth()->user()->role === 'admin' && $worker->status_program === 'aktif')
                        <form method="POST" action="{{ route('admin.profiling.lulus', $worker->id) }}" onsubmit="return confirm('Tandai peserta lulus program?');">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i data-lucide="graduation-cap" style="width:14px;height:14px;margin-right:0.25rem;"></i> Tandai Lulus Program
                            </button>
                        </form>
                    @endif
                </div>
                @if($worker->status_program === 'aktif')
                <details class="profil-update-panel">
                    <summary class="profil-update-summary">
                        <span style="display:flex;align-items:center;gap:0.4rem;">
                            <i data-lucide="refresh-cw" style="width:16px;height:16px;color:var(--primary);"></i>
                            Update Profiling (Survei Ulang)
                        </span>
                        <i data-lucide="chevron-down" style="width:16px;height:16px;color:var(--text-muted);"></i>
                    </summary>
                    <form method="POST" action="{{ route(auth()->user()->role === 'admin' ? 'admin.profiling.update' : 'pengawas.profiling.update', $worker->id) }}"
                          enctype="multipart/form-data" class="profil-update-form">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Frekuensi Makan *</label>
                            <select name="frekuensi_makan" class="form-input" required>
                                @foreach(['1 kali', '2 kali', '3 kali atau lebih'] as $opt)
                                    <option value="{{ $opt }}" @selected(old('frekuensi_makan', $worker->frekuensi_makan) === $opt)>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kondisi Sanitasi *</label>
                            <select name="kondisi_sanitasi" class="form-input" required>
                                @foreach(['Tidak Ada Jamban', 'Jamban Bersama', 'Jamban Sendiri', 'Jamban Sendiri + Septic Tank'] as $opt)
                                    <option value="{{ $opt }}" @selected(old('kondisi_sanitasi', $worker->kondisi_sanitasi) === $opt)>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pendidikan *</label>
                            <select name="pendidikan_terakhir" class="form-input" required>
                                @foreach(['Tidak Sekolah', 'SD / Sederajat', 'SMP / Sederajat', 'SMA / Sederajat', 'Diploma / S1+'] as $opt)
                                    <option value="{{ $opt }}" @selected(old('pendidikan_terakhir', $worker->pendidikan_terakhir) === $opt)>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status Gizi</label>
                            <select name="status_gizi" class="form-input">
                                <option value="">— Tidak diubah —</option>
                                @foreach(['Buruk', 'Kurang', 'Normal'] as $opt)
                                    <option value="{{ $opt }}" @selected(old('status_gizi', $worker->status_gizi) === $opt)>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bukti Foto</label>
                            <input type="file" name="bukti_foto_kondisi" class="form-input" accept="image/*" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Catatan</label>
                            <input type="text" name="catatan" class="form-input" value="{{ old('catatan') }}" placeholder="Catatan pemantauan..." />
                        </div>
                        <div style="grid-column:1/-1;">
                            <button type="submit" class="btn btn-primary btn-sm">Simpan Update Profiling</button>
                        </div>
                    </form>
                </details>
                @endif
            </div>
            @endif

            <div class="profil-two-col">
                @if($worker->profilingHistories->isNotEmpty())
                <div class="profil-card">
                    <div class="profil-card-header">
                        <i data-lucide="history" style="width:18px;height:18px;"></i> Riwayat Skor Profiling
                    </div>
                    <div class="profil-card-body" style="padding:0;overflow-x:auto;">
                        <table class="profil-history-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Makan</th>
                                    <th>Sanitasi</th>
                                    <th>Pendapatan</th>
                                    <th>Pendidikan</th>
                                    <th>Total</th>
                                    <th>Bukti</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($worker->profilingHistories->take(10) as $hist)
                                <tr>
                                    <td style="white-space:nowrap;">{{ $hist->created_at->format('d M Y') }}</td>
                                    <td>{{ $hist->skor_makan }}</td>
                                    <td>{{ $hist->skor_sanitasi }}</td>
                                    <td>{{ $hist->skor_pendapatan }}</td>
                                    <td>{{ $hist->skor_pendidikan }}</td>
                                    <td class="profil-history-total">{{ $hist->total_skor }}</td>
                                    <td>
                                        @if($hist->bukti_foto_kondisi)
                                            <a href="{{ $hist->bukti_foto_kondisi }}" target="_blank" style="color:var(--primary);font-size:0.8rem;">Lihat</a>
                                        @else
                                            <span style="color:var(--text-muted);">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                @if($worker->profilingSnapshots->isNotEmpty())
                <div class="profil-card">
                    <div class="profil-card-header">
                        <i data-lucide="clock" style="width:18px;height:18px;"></i> Timeline Pemantauan
                    </div>
                    <div class="profil-card-body">
                        <div class="profil-timeline">
                            @foreach($worker->profilingSnapshots->take(8) as $snap)
                            <div class="profil-timeline-item">
                                <div class="profil-timeline-dot"></div>
                                <div class="profil-timeline-date">{{ $snap->recorded_at?->format('d M Y, H:i') }}</div>
                                <div class="profil-timeline-body">
                                    Skor <strong>{{ $snap->skor_vulnerabilitas }}</strong>
                                    · Makan: {{ $snap->frekuensi_makan ?? '—' }}
                                </div>
                                @if($snap->catatan)
                                    <div class="profil-timeline-note">{{ $snap->catatan }}</div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            @if(!$worker->profiling_awal && $worker->profilingHistories->isEmpty() && $worker->profilingSnapshots->isEmpty())
            <div class="profil-card">
                <div class="profil-empty">
                    <div class="profil-empty-icon"><i data-lucide="clipboard-list" style="width:22px;height:22px;"></i></div>
                    <p>Belum ada data profiling untuk pekerja ini.</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Tab: Aktivitas --}}
        <div x-show="tab === 'aktivitas'" x-cloak>
            <div class="profil-two-col">
                <div class="profil-card">
                    <div class="profil-card-header">
                        <i data-lucide="map-pin" style="width:18px;height:18px;"></i> Program yang Diikuti
                        <span style="margin-left:auto;font-size:0.78rem;font-weight:500;color:var(--text-muted);">{{ $programs->count() }} program</span>
                    </div>
                    <div class="profil-card-body">
                        <div class="profil-activity-list">
                            @forelse($programs as $program)
                                @php
                                    $programDesa = $program->desa_lokasi ?? $program->lokasi;
                                    $isLintasDesa = $worker->desa_asal && $programDesa && $worker->desa_asal !== $programDesa;
                                @endphp
                                <div class="profil-activity-item">
                                    <div class="profil-activity-icon" style="background:rgba(59,130,246,0.1);color:#3b82f6;">
                                        <i data-lucide="map" style="width:18px;height:18px;"></i>
                                    </div>
                                    <div style="flex:1;min-width:0;">
                                        <div class="profil-activity-title">{{ $program->nama_program }}</div>
                                        <div class="profil-activity-meta">
                                            <span>{{ $program->jenis_program ?? 'Program' }}</span>
                                            <span>·</span>
                                            <span>{{ $programDesa ?? 'Lokasi belum diisi' }}</span>
                                            @if($isLintasDesa)
                                                <span class="profil-lintas-desa">
                                                    <i data-lucide="arrow-left-right" style="width:12px;height:12px;"></i> Lintas Desa
                                                </span>
                                            @endif
                                        </div>
                                        <span class="profil-activity-badge" style="background:rgba(59,130,246,0.1);color:#3b82f6;margin-top:0.4rem;">
                                            {{ $program->status ?? 'Berjalan' }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="profil-empty">
                                    <div class="profil-empty-icon"><i data-lucide="map-pin-off" style="width:22px;height:22px;"></i></div>
                                    <p>Belum terdaftar di program manapun.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="profil-card">
                    <div class="profil-card-header">
                        <i data-lucide="calendar-clock" style="width:18px;height:18px;"></i> Jadwal Kerja
                        <span style="margin-left:auto;font-size:0.78rem;font-weight:500;color:var(--text-muted);">{{ $schedules->count() }} jadwal</span>
                    </div>
                    <div class="profil-card-body">
                        <div class="profil-activity-list">
                            @forelse($schedules as $schedule)
                                @php
                                    $st = $scheduleStatusLabels[$schedule->status] ?? $scheduleStatusLabels['scheduled'];
                                @endphp
                                <div class="profil-activity-item">
                                    <div class="profil-activity-icon" style="background:rgba(139,92,246,0.1);color:#8b5cf6;">
                                        <i data-lucide="calendar" style="width:18px;height:18px;"></i>
                                    </div>
                                    <div style="flex:1;min-width:0;">
                                        <div class="profil-activity-title">{{ $schedule->program?->nama_program ?? 'Program' }}</div>
                                        <div class="profil-activity-meta">
                                            <span style="font-weight:500;color:var(--text-main);">
                                                {{ $schedule->tanggal ? \Carbon\Carbon::parse($schedule->tanggal)->format('d M Y') : '—' }}
                                            </span>
                                            <span>·</span>
                                            <span>{{ $schedule->jam_mulai ?? '—' }} – {{ $schedule->jam_selesai ?? '—' }}</span>
                                            @if($schedule->shift_label)
                                                <span>· {{ $schedule->shift_label }}</span>
                                            @endif
                                        </div>
                                        <span class="profil-activity-badge" style="background:{{ $st['bg'] }};color:{{ $st['fg'] }};margin-top:0.4rem;">
                                            {{ $st['label'] }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="profil-empty">
                                    <div class="profil-empty-icon"><i data-lucide="calendar-x" style="width:22px;height:22px;"></i></div>
                                    <p>Belum ada jadwal kerja.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endsection
