@extends(isset($isIncluded) && $isIncluded ? 'layouts.empty' : 'layouts.app')
@section('title', 'Profiling & Analisis Kesejahteraan')

@php
    $isAdmin = request()->is('admin/*');
    $avgSkor = $totalWorkers > 0 ? round($workers->avg(fn ($w) => $w->total_skor ?? $w->skor_vulnerabilitas ?? 0), 1) : 0;
    $aktifCount = $workers->where('status_program', 'aktif')->count();
    $sangatMiskinCount = $workers->where('status_kesejahteraan', 'Sangat Miskin')->count();
    $rentanCount = $workers->where('status_kesejahteraan', 'Rentan Miskin')->count();

    $kesejahteraanStyles = [
        'Sangat Miskin' => ['bg' => 'rgba(220,38,38,0.1)', 'fg' => '#dc2626', 'icon' => 'alert-circle'],
        'Miskin' => ['bg' => 'rgba(239,68,68,0.1)', 'fg' => '#ef4444', 'icon' => 'alert-triangle'],
        'Rentan Miskin' => ['bg' => 'rgba(245,158,11,0.12)', 'fg' => '#d97706', 'icon' => 'alert-triangle'],
        'Sejahtera' => ['bg' => 'rgba(34,197,94,0.12)', 'fg' => '#16a34a', 'icon' => 'check-circle'],
        'Pending' => ['bg' => 'rgba(59,130,246,0.1)', 'fg' => '#2563eb', 'icon' => 'clock'],
        'Lulus/Tidak Layak' => ['bg' => 'rgba(100,116,139,0.1)', 'fg' => '#64748b', 'icon' => 'user-check'],
    ];

    $prioritasStyles = [
        'tinggi' => ['bg' => 'rgba(239,68,68,0.1)', 'fg' => '#dc2626', 'label' => 'Prioritas Tinggi'],
        'sedang' => ['bg' => 'rgba(245,158,11,0.12)', 'fg' => '#d97706', 'label' => 'Prioritas Sedang'],
        'rendah' => ['bg' => 'rgba(59,130,246,0.1)', 'fg' => '#2563eb', 'label' => 'Prioritas Rendah'],
        'tidak_layak' => ['bg' => 'rgba(100,116,139,0.1)', 'fg' => '#64748b', 'label' => 'Tidak Layak'],
    ];

    $kesejahteraanColors = [
        'Sangat Miskin' => '#dc2626',
        'Miskin' => '#ef4444',
        'Rentan Miskin' => '#f59e0b',
        'Sejahtera' => '#22c55e',
        'Pending' => '#3b82f6',
        'Lulus/Tidak Layak' => '#94a3b8',
    ];

    $prioritasColors = [
        'tinggi' => '#ef4444',
        'sedang' => '#f59e0b',
        'rendah' => '#3b82f6',
        'tidak_layak' => '#94a3b8',
    ];
@endphp

@push('styles')
<style>
    .prof-page {
        padding: 2rem;
        max-width: 1440px;
        margin: 0 auto;
    }

    /* Hero */
    .prof-hero {
        background: linear-gradient(135deg, #1e3a5f 0%, #0f766e 50%, #0891b2 100%);
        border-radius: var(--radius-lg);
        padding: 2rem 2.25rem;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1.5rem;
        margin-bottom: 1.75rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(15, 118, 110, 0.22);
    }

    .prof-hero::before {
        content: '';
        position: absolute;
        top: -45%;
        right: -5%;
        width: 360px;
        height: 360px;
        background: rgba(255, 255, 255, 0.07);
        border-radius: 50%;
        pointer-events: none;
    }

    .prof-hero::after {
        content: '';
        position: absolute;
        bottom: -50%;
        left: 20%;
        width: 220px;
        height: 220px;
        background: rgba(255, 255, 255, 0.04);
        border-radius: 50%;
        pointer-events: none;
    }

    .prof-hero-content { position: relative; z-index: 1; }

    .prof-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.22);
        padding: 0.3rem 0.75rem;
        border-radius: 99px;
        font-size: 0.72rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        backdrop-filter: blur(4px);
    }

    .prof-hero h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        margin: 0 0 0.4rem;
    }

    .prof-hero p {
        color: rgba(255, 255, 255, 0.88);
        font-size: 0.92rem;
        margin: 0;
        max-width: 560px;
        line-height: 1.55;
    }

    .prof-hero-actions {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
        flex-shrink: 0;
        align-items: flex-start;
    }

    .prof-btn-white {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: white;
        color: var(--primary);
        border: none;
        border-radius: 99px;
        padding: 0.65rem 1.25rem;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: transform 0.15s, box-shadow 0.15s;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
    }

    .prof-btn-white:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .prof-btn-ghost {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(255, 255, 255, 0.12);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.28);
        border-radius: 99px;
        padding: 0.65rem 1.15rem;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        backdrop-filter: blur(4px);
        transition: background 0.15s;
    }

    .prof-btn-ghost:hover { background: rgba(255, 255, 255, 0.2); }

    /* SDG strip */
    .prof-sdg-strip {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.85rem;
        margin-bottom: 1.75rem;
    }

    @media (max-width: 768px) {
        .prof-sdg-strip { grid-template-columns: 1fr; }
    }

    .prof-sdg-card {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1rem 1.15rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .prof-sdg-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
    }

    .prof-sdg-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-weight: 800;
        font-size: 0.7rem;
        color: white;
    }

    .prof-sdg-title {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 0.1rem;
    }

    .prof-sdg-desc {
        font-size: 0.72rem;
        color: var(--text-muted);
        line-height: 1.4;
    }

    /* Alert */
    .prof-alert {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        background: rgba(34, 197, 94, 0.08);
        border: 1px solid rgba(34, 197, 94, 0.25);
        color: #15803d;
        padding: 0.85rem 1.15rem;
        border-radius: var(--radius-md);
        margin-bottom: 1.25rem;
        font-size: 0.88rem;
    }

    /* KPI */
    .prof-kpis {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    @media (max-width: 1100px) {
        .prof-kpis { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 520px) {
        .prof-kpis { grid-template-columns: 1fr; }
    }

    .prof-kpi {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.25rem 1.35rem;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .prof-kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.06);
    }

    .prof-kpi::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 80px;
        height: 80px;
        border-radius: 0 0 0 80px;
        opacity: 0.07;
    }

    .prof-kpi--total::after { background: #7c3aed; }
    .prof-kpi--layak::after { background: #22c55e; }
    .prof-kpi--tidak::after { background: #64748b; }
    .prof-kpi--lulus::after { background: #3b82f6; }

    .prof-kpi-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.85rem;
    }

    .prof-kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .prof-kpi-badge {
        font-size: 0.68rem;
        font-weight: 600;
        padding: 0.2rem 0.55rem;
        border-radius: 99px;
    }

    .prof-kpi-badge--up {
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
    }

    .prof-kpi-badge--warn {
        background: rgba(245, 158, 11, 0.12);
        color: #b45309;
    }

    .prof-kpi-badge--neutral {
        background: var(--background);
        color: var(--text-muted);
    }

    .prof-kpi-value {
        font-size: 1.85rem;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1.1;
        letter-spacing: -0.02em;
    }

    .prof-kpi-label {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
    }

    .prof-kpi-sub {
        font-size: 0.7rem;
        color: var(--text-muted);
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid var(--border);
    }

    /* Threshold info */
    .prof-threshold {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        padding: 1rem 1.15rem;
        margin-bottom: 1.75rem;
        background: linear-gradient(135deg, rgba(124, 58, 237, 0.05) 0%, rgba(15, 118, 110, 0.04) 100%);
        border: 1px solid rgba(124, 58, 237, 0.15);
        border-radius: var(--radius-md);
    }

    .prof-threshold-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(124, 58, 237, 0.12);
        color: #7c3aed;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .prof-threshold strong {
        display: block;
        font-size: 0.88rem;
        color: var(--text-main);
        margin-bottom: 0.35rem;
    }

    .prof-threshold p {
        margin: 0;
        font-size: 0.82rem;
        color: var(--text-muted);
        line-height: 1.55;
    }

    .prof-threshold-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.65rem;
    }

    .prof-threshold-tag {
        font-size: 0.68rem;
        font-weight: 600;
        padding: 0.25rem 0.6rem;
        border-radius: 99px;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text-muted);
    }

    /* Charts */
    .prof-charts {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
        margin-bottom: 1.75rem;
    }

    @media (max-width: 1100px) {
        .prof-charts { grid-template-columns: 1fr; }
    }

    .prof-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.35rem;
    }

    .prof-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.15rem;
        gap: 0.75rem;
    }

    .prof-panel-header h2 {
        font-size: 0.92rem;
        font-weight: 600;
        color: var(--text-main);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .prof-panel-header p {
        font-size: 0.72rem;
        color: var(--text-muted);
        margin: 0.15rem 0 0;
    }

    .prof-chart-wrap {
        height: 220px;
        position: relative;
    }

    .prof-legend {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
        margin-top: 0.85rem;
    }

    .prof-legend-item {
        display: flex;
        align-items: center;
        gap: 0.55rem;
    }

    .prof-legend-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .prof-legend-label {
        flex: 1;
        font-size: 0.75rem;
        color: var(--text-main);
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .prof-legend-val {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--text-main);
    }

    /* Table section */
    .prof-table-section {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.35rem;
    }

    .prof-table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.15rem;
        flex-wrap: wrap;
    }

    .prof-table-toolbar h2 {
        font-size: 0.92rem;
        font-weight: 600;
        color: var(--text-main);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .prof-table-toolbar p {
        font-size: 0.72rem;
        color: var(--text-muted);
        margin: 0.15rem 0 0;
    }

    .prof-table-controls {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .prof-search {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        background: var(--background);
        border: 1px solid var(--border);
        border-radius: 99px;
        padding: 0.45rem 0.85rem;
        min-width: 220px;
    }

    .prof-search input {
        border: none;
        outline: none;
        background: transparent;
        font-size: 0.82rem;
        color: var(--text-main);
        width: 100%;
    }

    .prof-filter-chips {
        display: flex;
        gap: 0.4rem;
        flex-wrap: wrap;
    }

    .prof-chip {
        font-size: 0.72rem;
        font-weight: 500;
        padding: 0.35rem 0.75rem;
        border-radius: 99px;
        border: 1px solid var(--border);
        background: var(--background);
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.15s;
    }

    .prof-chip:hover { border-color: var(--primary); color: var(--primary); }

    .prof-chip.active {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .prof-table-wrap {
        overflow-x: auto;
        border-radius: 10px;
        border: 1px solid var(--border);
    }

    .prof-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.82rem;
    }

    .prof-table thead { background: var(--background); }

    .prof-table th {
        padding: 0.85rem 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--text-muted);
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    .prof-table td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid var(--border);
        color: var(--text-main);
        vertical-align: middle;
    }

    .prof-table tbody tr:last-child td { border-bottom: none; }

    .prof-table tbody tr:hover {
        background: rgba(124, 58, 237, 0.03);
    }

    .prof-worker-name {
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.55rem;
    }

    .prof-worker-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #7c3aed, #0f766e);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .prof-score-wrap {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        min-width: 100px;
    }

    .prof-score-bar {
        flex: 1;
        height: 6px;
        background: var(--border);
        border-radius: 99px;
        overflow: hidden;
        min-width: 48px;
    }

    .prof-score-fill {
        height: 100%;
        border-radius: 99px;
        transition: width 0.4s ease;
    }

    .prof-score-num {
        font-weight: 700;
        font-size: 0.85rem;
        min-width: 24px;
        text-align: right;
    }

    .prof-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.25rem 0.65rem;
        border-radius: 99px;
        font-size: 0.72rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .prof-makro-tag {
        background: var(--background);
        border: 1px solid var(--border);
        padding: 0.2rem 0.6rem;
        border-radius: 99px;
        font-size: 0.75rem;
        color: var(--text-main);
    }

    .prof-status-dot {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.78rem;
    }

    .prof-status-dot::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .prof-status-dot--aktif::before { background: #22c55e; }
    .prof-status-dot--lulus::before { background: #3b82f6; }
    .prof-status-dot--tidak::before { background: #94a3b8; }

    .prof-actions {
        display: flex;
        gap: 0.4rem;
        flex-wrap: wrap;
    }

    .prof-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.35rem 0.7rem;
        border-radius: 8px;
        font-size: 0.72rem;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text-main);
        cursor: pointer;
        transition: all 0.15s;
        white-space: nowrap;
    }

    .prof-action-btn--primary {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .prof-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .prof-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem 1rem;
        color: var(--text-muted);
        font-size: 0.85rem;
        text-align: center;
    }

    .prof-empty i { opacity: 0.35; margin-bottom: 0.5rem; }

    .prof-result-count {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-bottom: 0.75rem;
    }
</style>
@endpush

@section('content')
<div class="prof-page animate-fade-in" x-data="{ activeTab: new URLSearchParams(window.location.search).get('tab') || 'kelompok', updateUrl(tab) { window.history.replaceState(null, null, '?tab=' + tab); setTimeout(() => lucide.createIcons(), 50); } }">

    @if(!(isset($isIncluded) && $isIncluded))
    <x-hero-banner 
        title="Kelompok &amp; Profiling" 
        description="Pembagian tim kerja lapangan bagi para pekerja prasejahtera, serta monitoring tingkat kesejahteraan mereka.">
        <x-slot:actions>
            <template x-if="activeTab === 'kelompok'">
                <button type="button" class="global-hero-banner-btn-white" @click="window.dispatchEvent(new CustomEvent('open-add-group-form'))">
                    <i data-lucide="plus" style="width: 18px; height: 18px; margin-right: 6px;"></i> Tambah Kelompok
                </button>
            </template>
        </x-slot:actions>
    </x-hero-banner>

    <div class="global-tabs">
        @if($isAdmin)
            <a href="/admin/pekerja" class="global-tab">
                <i data-lucide="users" style="width: 16px; height: 16px;"></i>
                Daftar Pekerja
            </a>
            <a href="/admin/profiling" class="global-tab active">
                <i data-lucide="pie-chart" style="width: 16px; height: 16px;"></i>
                Profiling Kesejahteraan
            </a>
            <a href="/admin/keluarga" class="global-tab">
                <i data-lucide="square-user" style="width: 16px; height: 16px;"></i>
                Data Keluarga
            </a>
        @else
            <button type="button" class="global-tab" :class="{ 'active': activeTab === 'kelompok' }" @click="activeTab = 'kelompok'; updateUrl('kelompok')">
                <i data-lucide="users" style="width: 16px; height: 16px;"></i>
                Kelompok Kerja
            </button>
            <button type="button" class="global-tab" :class="{ 'active': activeTab === 'profiling' }" @click="activeTab = 'profiling'; updateUrl('profiling')">
                <i data-lucide="pie-chart" style="width: 16px; height: 16px;"></i>
                Profiling Pekerja
            </button>
        @endif
    </div>
    @endif

    @if(!(isset($isIncluded) && $isIncluded))
    <div x-show="activeTab === 'kelompok'" x-cloak>
        @include('pengawas.groups.index', ['isIncluded' => true])
    </div>
    @endif

    <div x-show="{{ (isset($isIncluded) && $isIncluded) ? 'true' : 'false' }} || activeTab === 'profiling'" x-data="profilingData()">

    @if(session('success'))
        <div class="prof-alert">
            <i data-lucide="check-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- SDG Strip --}}
    <div class="prof-sdg-strip">
        <div class="prof-sdg-card">
            <div class="prof-sdg-icon" style="background: linear-gradient(135deg, #dc2626, #ef4444);">SDG 1</div>
            <div>
                <div class="prof-sdg-title">Tanpa Kemiskinan</div>
                <div class="prof-sdg-desc">{{ $sangatMiskinCount + $rentanCount }} pekerja dalam kategori rentan · rata-rata skor {{ $avgSkor }}/30</div>
            </div>
        </div>
        <div class="prof-sdg-card">
            <div class="prof-sdg-icon" style="background: linear-gradient(135deg, #d97706, #f59e0b);">SDG 2</div>
            <div>
                <div class="prof-sdg-title">Tanpa Kelaparan</div>
                <div class="prof-sdg-desc">Indikator frekuensi makan & status gizi tercatat di setiap survei profiling</div>
            </div>
        </div>
        <div class="prof-sdg-card">
            <div class="prof-sdg-icon" style="background: linear-gradient(135deg, #0891b2, #06b6d4);">SDG 3</div>
            <div>
                <div class="prof-sdg-title">Kehidupan Sehat</div>
                <div class="prof-sdg-desc">Sanitasi, air bersih, dan riwayat kesehatan menjadi dimensi skoring</div>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="prof-kpis">
        <div class="prof-kpi prof-kpi--total">
            <div class="prof-kpi-top">
                <div class="prof-kpi-icon" style="background: rgba(124,58,237,0.1); color: #7c3aed;">
                    <i data-lucide="users" style="width: 22px; height: 22px;"></i>
                </div>
                <span class="prof-kpi-badge prof-kpi-badge--neutral">{{ $aktifCount }} aktif</span>
            </div>
            <div class="prof-kpi-value">{{ $totalWorkers }}</div>
            <div class="prof-kpi-label">Total Survei Profiling</div>
            <div class="prof-kpi-sub">Rata-rata skor vulnerabilitas: {{ $avgSkor }}</div>
        </div>

        <div class="prof-kpi prof-kpi--layak">
            <div class="prof-kpi-top">
                <div class="prof-kpi-icon" style="background: rgba(34,197,94,0.1); color: #16a34a;">
                    <i data-lucide="user-check" style="width: 22px; height: 22px;"></i>
                </div>
                <span class="prof-kpi-badge prof-kpi-badge--up">{{ $persentaseLayak }}%</span>
            </div>
            <div class="prof-kpi-value" style="color: #16a34a;">{{ $layakCount }}</div>
            <div class="prof-kpi-label">Layak Program</div>
            <div class="prof-kpi-sub">Skor ≥ 6 — masuk antrean penugasan</div>
        </div>

        <div class="prof-kpi prof-kpi--tidak">
            <div class="prof-kpi-top">
                <div class="prof-kpi-icon" style="background: rgba(100,116,139,0.1); color: #64748b;">
                    <i data-lucide="user-x" style="width: 22px; height: 22px;"></i>
                </div>
                <span class="prof-kpi-badge prof-kpi-badge--warn">Threshold</span>
            </div>
            <div class="prof-kpi-value" style="color: #64748b;">{{ $tidakLayakCount }}</div>
            <div class="prof-kpi-label">Tidak Layak Program</div>
            <div class="prof-kpi-sub">Skor &lt; 6 — tidak masuk antrean</div>
        </div>

        <div class="prof-kpi prof-kpi--lulus">
            <div class="prof-kpi-top">
                <div class="prof-kpi-icon" style="background: rgba(59,130,246,0.1); color: #2563eb;">
                    <i data-lucide="graduation-cap" style="width: 22px; height: 22px;"></i>
                </div>
            </div>
            <div class="prof-kpi-value" style="color: #2563eb;">{{ $lulusCount }}</div>
            <div class="prof-kpi-label">Lulus Program</div>
            <div class="prof-kpi-sub">Slot dialihkan ke calon baru</div>
        </div>
    </div>

    {{-- Threshold info --}}
    <div class="prof-threshold">
        <div class="prof-threshold-icon">
            <i data-lucide="sliders-horizontal" style="width: 18px; height: 18px;"></i>
        </div>
        <div>
            <strong>Sistem Threshold & Prioritas Penugasan</strong>
            <p>Skor vulnerabilitas dihitung dari 8 dimensi indikator (pendapatan, makan, sanitasi, pendidikan, air, rumah, gizi, kesehatan). Semakin tinggi skor, semakin rentan kondisi pekerja.</p>
            <div class="prof-threshold-tags">
                <span class="prof-threshold-tag" style="border-color: #fecaca; color: #dc2626;">≥ 14 — Prioritas Tinggi</span>
                <span class="prof-threshold-tag" style="border-color: #fde68a; color: #d97706;">10–13 — Prioritas Sedang</span>
                <span class="prof-threshold-tag" style="border-color: #bfdbfe; color: #2563eb;">6–9 — Prioritas Rendah</span>
                <span class="prof-threshold-tag">&lt; 6 — Tidak Layak</span>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="prof-charts">
        <div class="prof-panel">
            <div class="prof-panel-header">
                <div>
                    <h2>
                        <i data-lucide="target" style="width: 17px; height: 17px; color: #ef4444;"></i>
                        Distribusi Prioritas
                    </h2>
                    <p>Berdasarkan threshold skor</p>
                </div>
            </div>
            <div class="prof-chart-wrap">
                <canvas id="prioritasChart"></canvas>
            </div>
            <div class="prof-legend">
                @foreach($prioritasStats as $key => $val)
                    @php $pStyle = $prioritasStyles[$key] ?? ['fg' => '#64748b', 'label' => $key]; @endphp
                    <div class="prof-legend-item">
                        <div class="prof-legend-dot" style="background: {{ $prioritasColors[$key] ?? '#94a3b8' }};"></div>
                        <span class="prof-legend-label">{{ $pStyle['label'] ?? ucfirst(str_replace('_', ' ', $key)) }}</span>
                        <span class="prof-legend-val">{{ $val }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="prof-panel">
            <div class="prof-panel-header">
                <div>
                    <h2>
                        <i data-lucide="heart-pulse" style="width: 17px; height: 17px; color: #dc2626;"></i>
                        Klasifikasi Kesejahteraan
                    </h2>
                    <p>Kategori status pekerja</p>
                </div>
            </div>
            <div class="prof-chart-wrap">
                <canvas id="kesejahteraanChart"></canvas>
            </div>
            <div class="prof-legend">
                @foreach($kesejahteraanStats as $key => $val)
                    <div class="prof-legend-item">
                        <div class="prof-legend-dot" style="background: {{ $kesejahteraanColors[$key] ?? '#94a3b8' }};"></div>
                        <span class="prof-legend-label">{{ $key }}</span>
                        <span class="prof-legend-val">{{ $val }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="prof-panel">
            <div class="prof-panel-header">
                <div>
                    <h2>
                        <i data-lucide="briefcase" style="width: 17px; height: 17px; color: #2563eb;"></i>
                        Sektor Pekerjaan Makro
                    </h2>
                    <p>Distribusi keahlian utama</p>
                </div>
            </div>
            <div class="prof-chart-wrap">
                <canvas id="pekerjaanMakroChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Worker Table --}}
    <div class="prof-table-section">
        <div class="prof-table-toolbar">
            <div>
                <h2>
                    <i data-lucide="list-checks" style="width: 17px; height: 17px; color: var(--primary);"></i>
                    Daftar Profiling Pekerja
                </h2>
                <p>Diurutkan berdasarkan skor vulnerabilitas tertinggi</p>
            </div>
            <div class="prof-table-controls">
                <div class="prof-search">
                    <i data-lucide="search" style="width: 15px; height: 15px; color: var(--text-muted); flex-shrink: 0;"></i>
                    <input type="text" placeholder="Cari nama, desa, keahlian..." x-model="search" @input="filterRows">
                </div>
                <div class="prof-filter-chips">
                    <button class="prof-chip" :class="{ active: filter === 'all' }" @click="setFilter('all')">Semua</button>
                    <button class="prof-chip" :class="{ active: filter === 'layak' }" @click="setFilter('layak')">Layak</button>
                    <button class="prof-chip" :class="{ active: filter === 'tidak_layak' }" @click="setFilter('tidak_layak')">Tidak Layak</button>
                    <button class="prof-chip" :class="{ active: filter === 'aktif' }" @click="setFilter('aktif')">Aktif</button>
                    <button class="prof-chip" :class="{ active: filter === 'lulus' }" @click="setFilter('lulus')">Lulus</button>
                    @if($isAdmin)
                        <button type="button" class="prof-chip" style="background: var(--primary); color: white; border-color: var(--primary); font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem;" 
                            @click="activeTab = 'pekerja'; updateUrl('pekerja'); setTimeout(() => window.dispatchEvent(new CustomEvent('open-add-worker-form')), 80);">
                            <i data-lucide="user-plus" style="width: 13px; height: 13px;"></i>
                            Tambah Pekerja
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="prof-result-count" x-text="visibleCount + ' dari {{ $totalWorkers }} pekerja ditampilkan'"></div>

        @if($workers->count())
            <div class="prof-table-wrap">
                <table class="prof-table">
                    <thead>
                        <tr>
                            <th>Pekerja</th>
                            <th>Keahlian</th>
                            <th>Sektor</th>
                            <th>Skor</th>
                            <th>Kategori</th>
                            <th>Makan/Hari</th>
                            <th>Desa</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workers as $worker)
                            @php
                                $skor = $worker->total_skor ?? $worker->skor_vulnerabilitas ?? 0;
                                $skorPct = min(100, round(($skor / 30) * 100));
                                $skorColor = $skor >= 14 ? '#ef4444' : ($skor >= 10 ? '#f59e0b' : ($skor >= 6 ? '#3b82f6' : '#94a3b8'));
                                $kategori = $worker->status_kesejahteraan ?? $worker->klasifikasi_kesejahteraan ?? 'Pending';
                                $kStyle = $kesejahteraanStyles[$kategori] ?? $kesejahteraanStyles['Pending'];
                                $prioritas = $worker->prioritas ?? 'sedang';
                                $isLayak = \App\Support\ProfilingScorer::layakProgram($worker);
                                $profilUrl = $isAdmin ? '/admin/pekerja/' . $worker->id . '/profil' : '/pengawas/pekerja/' . $worker->id . '/profil';
                                $lulusRoute = $isAdmin ? route('admin.profiling.lulus', $worker->id) : null;
                                $initials = collect(explode(' ', $worker->nama))->take(2)->map(fn ($w) => strtoupper(substr($w, 0, 1)))->join('');
                                $statusClass = match($worker->status_program) {
                                    'aktif' => 'prof-status-dot--aktif',
                                    'lulus' => 'prof-status-dot--lulus',
                                    default => 'prof-status-dot--tidak',
                                };
                            @endphp
                            <tr
                                data-search="{{ strtolower($worker->nama . ' ' . ($worker->kemampuan_utama ?? '') . ' ' . ($worker->desa_asal ?? '') . ' ' . ($worker->pekerjaan_makro ?? '')) }}"
                                data-layak="{{ $isLayak ? '1' : '0' }}"
                                data-status="{{ $worker->status_program }}"
                            >
                                <td>
                                    <div class="prof-worker-name">
                                        <div class="prof-worker-avatar">{{ $initials }}</div>
                                        {{ $worker->nama }}
                                    </div>
                                </td>
                                <td style="color: var(--text-muted);">{{ $worker->kemampuan_utama ?? '—' }}</td>
                                <td><span class="prof-makro-tag">{{ $worker->pekerjaan_makro ?? '—' }}</span></td>
                                <td>
                                    <div class="prof-score-wrap">
                                        <div class="prof-score-bar">
                                            <div class="prof-score-fill" style="width: {{ $skorPct }}%; background: {{ $skorColor }};"></div>
                                        </div>
                                        <span class="prof-score-num" style="color: {{ $skorColor }};">{{ $skor ?: '—' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="prof-badge" style="background: {{ $kStyle['bg'] }}; color: {{ $kStyle['fg'] }};">
                                        <i data-lucide="{{ $kStyle['icon'] }}" style="width: 11px; height: 11px;"></i>
                                        {{ $kategori }}
                                    </span>
                                </td>
                                <td style="color: var(--text-muted);">{{ $worker->frekuensi_makan ?? '—' }}</td>
                                <td style="color: var(--text-muted);">{{ $worker->desa_asal ?? '—' }}</td>
                                <td>
                                    <span class="prof-status-dot {{ $statusClass }}">{{ $worker->status_program_label }}</span>
                                </td>
                                <td>
                                    <div class="prof-actions">
                                        <a href="{{ $profilUrl }}" class="prof-action-btn prof-action-btn--primary">
                                            <i data-lucide="user" style="width: 12px; height: 12px;"></i>
                                            Profil
                                        </a>
                                        @if($worker->status_program === 'aktif')
                                            <a href="{{ $profilUrl }}" class="prof-action-btn">
                                                <i data-lucide="refresh-cw" style="width: 12px; height: 12px;"></i>
                                                Update
                                            </a>
                                        @endif
                                        @if($lulusRoute && $worker->status_program === 'aktif' && auth()->user()->role === 'admin')
                                            <form method="POST" action="{{ $lulusRoute }}" style="display: inline;" onsubmit="return confirm('Tandai {{ $worker->nama }} lulus program?');">
                                                @csrf
                                                <button type="submit" class="prof-action-btn">
                                                    <i data-lucide="award" style="width: 12px; height: 12px;"></i>
                                                    Lulus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="prof-empty">
                <i data-lucide="users" style="width: 36px; height: 36px;"></i>
                Belum ada data pekerja yang dapat dianalisis.<br>
                @if($isAdmin)
                    <a href="/admin/pekerja" style="color: var(--primary); font-weight: 600; margin-top: 0.5rem; display: inline-block; text-decoration: none;">Mulai survei profiling →</a>
                @endif
            </div>
        @endif
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('profilingData', () => ({
            search: '',
            filter: 'all',
            visibleCount: {{ $totalWorkers }},

            init() {
                this.$nextTick(() => {
                    setTimeout(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        this.renderCharts();
                        this.filterRows();
                    }, 50);
                });
            },

            setFilter(f) {
                this.filter = f;
                this.filterRows();
            },

            filterRows() {
                const q = this.search.toLowerCase().trim();
                let count = 0;
                document.querySelectorAll('.prof-table tbody tr[data-search]').forEach(row => {
                    const matchSearch = !q || row.dataset.search.includes(q);
                    const layak = row.dataset.layak === '1';
                    const status = row.dataset.status;
                    let matchFilter = true;
                    if (this.filter === 'layak') matchFilter = layak && status !== 'lulus';
                    else if (this.filter === 'tidak_layak') matchFilter = !layak && status !== 'lulus';
                    else if (this.filter === 'aktif') matchFilter = status === 'aktif';
                    else if (this.filter === 'lulus') matchFilter = status === 'lulus';
                    const visible = matchSearch && matchFilter;
                    row.style.display = visible ? '' : 'none';
                    if (visible) count++;
                });
                this.visibleCount = count;
            },

            renderCharts() {
                if (typeof Chart === 'undefined') return;

                const prioritasData = @json($prioritasStats ?? []);
                const prioritasLabels = {
                    tinggi: 'Prioritas Tinggi',
                    sedang: 'Prioritas Sedang',
                    rendah: 'Prioritas Rendah',
                    tidak_layak: 'Tidak Layak',
                };
                const prioritasColors = ['#ef4444', '#f59e0b', '#3b82f6', '#94a3b8'];

                const ctxPrioritas = document.getElementById('prioritasChart');
                if (ctxPrioritas) {
                    const keys = Object.keys(prioritasData);
                    new Chart(ctxPrioritas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: keys.map(k => prioritasLabels[k] || k.replace('_', ' ')),
                            datasets: [{
                                data: Object.values(prioritasData),
                                backgroundColor: keys.map((k, i) => ({ tinggi: '#ef4444', sedang: '#f59e0b', rendah: '#3b82f6', tidak_layak: '#94a3b8' }[k] || prioritasColors[i % 4])),
                                borderWidth: 3,
                                borderColor: '#ffffff',
                                hoverOffset: 8,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '68%',
                            plugins: { legend: { display: false } },
                        },
                    });
                }

                const kesejahteraanData = @json($kesejahteraanStats);
                const kColors = {
                    'Sangat Miskin': '#dc2626',
                    'Miskin': '#ef4444',
                    'Rentan Miskin': '#f59e0b',
                    'Sejahtera': '#22c55e',
                    'Pending': '#3b82f6',
                    'Lulus/Tidak Layak': '#94a3b8',
                };
                const ctxKes = document.getElementById('kesejahteraanChart');
                if (ctxKes) {
                    const kKeys = Object.keys(kesejahteraanData);
                    new Chart(ctxKes.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: kKeys,
                            datasets: [{
                                data: Object.values(kesejahteraanData),
                                backgroundColor: kKeys.map(k => kColors[k] || '#94a3b8'),
                                borderWidth: 3,
                                borderColor: '#ffffff',
                                hoverOffset: 8,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '68%',
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => {
                                            const total = Object.values(kesejahteraanData).reduce((a, b) => a + b, 0);
                                            const pct = total > 0 ? Math.round((ctx.parsed / total) * 100) : 0;
                                            return `${ctx.label}: ${ctx.parsed} (${pct}%)`;
                                        },
                                    },
                                },
                            },
                        },
                    });
                }

                const makroData = @json($pekerjaanMakroStats);
                const ctxMakro = document.getElementById('pekerjaanMakroChart');
                if (ctxMakro) {
                    const mKeys = Object.keys(makroData);
                    const barColors = ['#7c3aed', '#3b82f6', '#0f766e', '#f59e0b', '#ec4899', '#06b6d4'];
                    new Chart(ctxMakro.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: mKeys,
                            datasets: [{
                                label: 'Jumlah Pekerja',
                                data: Object.values(makroData),
                                backgroundColor: mKeys.map((_, i) => barColors[i % barColors.length]),
                                borderRadius: 8,
                                borderSkipped: false,
                                barThickness: mKeys.length <= 3 ? 40 : undefined,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: mKeys.length > 4 ? 'y' : 'x',
                            plugins: { legend: { display: false } },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: 'rgba(226,232,240,0.6)' },
                                    ticks: { stepSize: 1, font: { size: 11 } },
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { font: { size: 11 } },
                                },
                            },
                        },
                    });
                }
            },
        }));
    });
</script>
@endsection
