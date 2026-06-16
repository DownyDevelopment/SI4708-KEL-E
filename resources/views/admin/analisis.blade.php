@extends('layouts.app')
@section('title', 'Dashboard Analisis')

@php
    $tren = collect($data['tren_partisipasi']);
    $trenTotal = $tren->sum('partisipasi');
    $trenActivePeriods = $tren->filter(fn ($t) => $t['partisipasi'] > 0)->count();
    $trenLatest = $tren->last()['partisipasi'] ?? 0;
    $trenPrev = $tren->count() >= 2 ? ($tren->slice(-2, 1)->first()['partisipasi'] ?? 0) : 0;
    $trenDelta = $trenPrev > 0 ? round((($trenLatest - $trenPrev) / $trenPrev) * 100) : ($trenLatest > 0 ? 100 : 0);

    $programs = collect($data['rincian_capaian']);
    $programTotal = $programs->count();
    $programSelesai = $programs->filter(fn ($p) => in_array($p->status, ['selesai', 'completed']))->count();
    $programAktif = $programs->filter(fn ($p) => in_array($p->status, ['active', 'ongoing', 'in_progress', 'berjalan']))->count();
    $completionRate = $programTotal > 0 ? round(($programSelesai / $programTotal) * 100) : 0;

    $sebaran = collect($data['sebaran_program']);
    $sebaranTotal = max(1, $sebaran->sum('value'));

    $totalEmisi = $environmentalRecords->sum('estimasi_emisi_berkurang_kg');

    $periodLabel = match ($period) {
        'mingguan' => '8 minggu terakhir',
        'tahunan' => '3 tahun terakhir',
        default => '12 bulan terakhir',
    };

    $periodShort = match ($period) {
        'mingguan' => 'Per Minggu',
        'tahunan' => 'Per Tahun',
        default => 'Per Bulan',
    };
@endphp

@push('styles')
<style>
    .analisis-page {
        padding: 2rem;
        max-width: 1440px;
        margin: 0 auto;
    }

    /* Hero */
    .analisis-hero {
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

    .analisis-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -8%;
        width: 380px;
        height: 380px;
        background: rgba(255, 255, 255, 0.06);
        border-radius: 50%;
        pointer-events: none;
    }

    .analisis-hero::after {
        content: '';
        position: absolute;
        bottom: -40%;
        left: 30%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.04);
        border-radius: 50%;
        pointer-events: none;
    }

    .analisis-hero-content { position: relative; z-index: 1; }

    .analisis-hero-badge {
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

    .analisis-hero h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        margin: 0 0 0.4rem;
    }

    .analisis-hero p {
        color: rgba(255, 255, 255, 0.88);
        font-size: 0.92rem;
        margin: 0;
        max-width: 540px;
        line-height: 1.55;
    }

    .analisis-hero-actions {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
        flex-shrink: 0;
        align-items: flex-start;
    }

    .analisis-period-select {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.28);
        border-radius: 99px;
        padding: 0.55rem 1rem;
        backdrop-filter: blur(4px);
    }

    .analisis-period-select select {
        border: none;
        outline: none;
        background: transparent;
        color: white;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        appearance: none;
        padding-right: 0.25rem;
    }

    .analisis-period-select select option { color: var(--text-main); }

    .analisis-btn-white {
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

    .analisis-btn-white:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    /* Alert */
    .analisis-alert {
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

    /* KPI grid */
    .analisis-kpis {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    @media (max-width: 1100px) {
        .analisis-kpis { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 520px) {
        .analisis-kpis { grid-template-columns: 1fr; }
    }

    .analisis-kpi {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.25rem 1.35rem;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .analisis-kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.06);
    }

    .analisis-kpi::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 80px;
        height: 80px;
        border-radius: 0 0 0 80px;
        opacity: 0.07;
    }

    .analisis-kpi--workers::after { background: #3b82f6; }
    .analisis-kpi--env::after { background: #22c55e; }
    .analisis-kpi--money::after { background: #f59e0b; }
    .analisis-kpi--programs::after { background: #8b5cf6; }

    .analisis-kpi-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.85rem;
    }

    .analisis-kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .analisis-kpi-badge {
        font-size: 0.68rem;
        font-weight: 600;
        padding: 0.2rem 0.55rem;
        border-radius: 99px;
    }

    .analisis-kpi-badge--up {
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
    }

    .analisis-kpi-badge--down {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
    }

    .analisis-kpi-badge--neutral {
        background: var(--background);
        color: var(--text-muted);
    }

    .analisis-kpi-value {
        font-size: 1.85rem;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1.1;
        letter-spacing: -0.02em;
    }

    .analisis-kpi-unit {
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--text-muted);
    }

    .analisis-kpi-label {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
    }

    .analisis-kpi-sub {
        font-size: 0.7rem;
        color: var(--text-muted);
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid var(--border);
    }

    /* Main layout */
    .analisis-main {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.25rem;
        margin-bottom: 1.25rem;
    }

    @media (max-width: 960px) {
        .analisis-main { grid-template-columns: 1fr; }
    }

    .analisis-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.35rem;
    }

    .analisis-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.15rem;
        gap: 0.75rem;
    }

    .analisis-panel-header h2 {
        font-size: 0.92rem;
        font-weight: 600;
        color: var(--text-main);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .analisis-panel-header p {
        font-size: 0.72rem;
        color: var(--text-muted);
        margin: 0.15rem 0 0;
    }

    .analisis-panel-link {
        font-size: 0.72rem;
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .analisis-panel-link:hover { text-decoration: underline; }

    .analisis-chart-hint {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        color: var(--text-muted);
        background: var(--background);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 0.55rem 0.85rem;
        margin-bottom: 0.85rem;
    }

    .analisis-chart-hint i { flex-shrink: 0; color: var(--primary); opacity: 0.7; }

    .analisis-chart-wrap {
        height: 300px;
        position: relative;
    }

    .analisis-chart-wrap--sm { height: 240px; }

    /* Sebaran sidebar */
    .analisis-sebaran-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-top: 0.5rem;
    }

    .analisis-sebaran-item {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }

    .analisis-sebaran-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .analisis-sebaran-info {
        flex: 1;
        min-width: 0;
    }

    .analisis-sebaran-name {
        font-size: 0.78rem;
        color: var(--text-main);
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .analisis-sebaran-bar-track {
        height: 4px;
        background: var(--border);
        border-radius: 99px;
        margin-top: 0.3rem;
        overflow: hidden;
    }

    .analisis-sebaran-bar-fill {
        height: 100%;
        border-radius: 99px;
        transition: width 0.6s ease;
    }

    .analisis-sebaran-val {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--text-main);
        flex-shrink: 0;
    }

    /* Bottom grid */
    .analisis-bottom {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
        margin-bottom: 1.25rem;
    }

    @media (max-width: 960px) {
        .analisis-bottom { grid-template-columns: 1fr; }
    }

    /* Environmental form */
    .analisis-env-form {
        display: grid;
        grid-template-columns: repeat(4, 1fr) auto;
        gap: 0.85rem;
        align-items: end;
    }

    @media (max-width: 900px) {
        .analisis-env-form { grid-template-columns: 1fr 1fr; }
    }

    @media (max-width: 520px) {
        .analisis-env-form { grid-template-columns: 1fr; }
    }

    .analisis-env-form .form-group { margin: 0; }

    .analisis-env-summary {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
        margin-bottom: 1.15rem;
    }

    @media (max-width: 640px) {
        .analisis-env-summary { grid-template-columns: 1fr; }
    }

    .analisis-env-stat {
        text-align: center;
        padding: 0.85rem;
        background: var(--background);
        border-radius: 10px;
        border: 1px solid var(--border);
    }

    .analisis-env-stat-val {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .analisis-env-stat-label {
        font-size: 0.68rem;
        color: var(--text-muted);
        margin-top: 0.1rem;
    }

    /* Table */
    .analisis-table-wrap {
        overflow-x: auto;
        border-radius: 10px;
        border: 1px solid var(--border);
    }

    .analisis-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.82rem;
    }

    .analisis-table thead {
        background: var(--background);
    }

    .analisis-table th {
        padding: 0.85rem 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--text-muted);
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1px solid var(--border);
    }

    .analisis-table td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid var(--border);
        color: var(--text-main);
    }

    .analisis-table tbody tr:last-child td { border-bottom: none; }

    .analisis-table tbody tr:hover {
        background: rgba(15, 118, 110, 0.03);
    }

    .analisis-status {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.25rem 0.65rem;
        border-radius: 99px;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .analisis-status--done {
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
    }

    .analisis-status--active {
        background: rgba(59, 130, 246, 0.12);
        color: #1d4ed8;
    }

    .analisis-status--planned {
        background: rgba(245, 158, 11, 0.12);
        color: #b45309;
    }

    .analisis-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2.5rem 1rem;
        color: var(--text-muted);
        font-size: 0.85rem;
        text-align: center;
    }

    .analisis-empty i { opacity: 0.35; margin-bottom: 0.5rem; }

    /* Progress ring mini */
    .analisis-completion {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        padding: 0.5rem 0;
    }

    .analisis-completion-ring {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        position: relative;
    }

    .analisis-completion-ring-inner {
        width: 54px;
        height: 54px;
        background: var(--surface);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--text-main);
    }

    .analisis-completion-stats {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
    }

    .analisis-completion-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.75rem;
    }

    .analisis-completion-row span:first-child { color: var(--text-muted); }
    .analisis-completion-row span:last-child { font-weight: 700; }

    @media print {
        body * { visibility: hidden; }
        .analisis-page, .analisis-page * { visibility: visible; }
        .analisis-page {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 0 !important;
        }
        .no-print { display: none !important; }
        .analisis-panel { page-break-inside: avoid; }
    }
</style>
@endpush

@section('content')
<div class="analisis-page animate-fade-in" x-data="analisisData()">

    <x-hero-banner title="Dashboard Analisis Desa" description="Pusat evaluasi pencapaian program kerja mikro, partisipasi warga prasejahtera, dan dampak lingkungan berkelanjutan.">
        <x-slot:actions>
            <div class="analisis-period-select no-print">
                <i data-lucide="calendar-range" style="width: 16px; height: 16px; color: rgba(255,255,255,0.85);"></i>
                <select x-model="period" @change="changePeriod">
                    <option value="mingguan">Mingguan</option>
                    <option value="bulanan">Bulanan</option>
                    <option value="tahunan">Tahunan</option>
                </select>
            </div>
            <button class="global-hero-banner-btn-white no-print" @click="downloadPdf">
                <i data-lucide="download" style="width: 16px; height: 16px;"></i>
                Unduh PDF
            </button>
        </x-slot:actions>
    </x-hero-banner>

    @if(session('success'))
        <div class="analisis-alert no-print">
            <i data-lucide="check-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="analisis-kpis">
        <div class="analisis-kpi analisis-kpi--workers">
            <div class="analisis-kpi-top">
                <div class="analisis-kpi-icon" style="background: rgba(59,130,246,0.1); color: #2563eb;">
                    <i data-lucide="users" style="width: 22px; height: 22px;"></i>
                </div>
                @if($trenDelta !== 0)
                    <span class="analisis-kpi-badge {{ $trenDelta >= 0 ? 'analisis-kpi-badge--up' : 'analisis-kpi-badge--down' }}">
                        {{ $trenDelta >= 0 ? '+' : '' }}{{ $trenDelta }}%
                    </span>
                @else
                    <span class="analisis-kpi-badge analisis-kpi-badge--neutral">Stabil</span>
                @endif
            </div>
            <div class="analisis-kpi-value">{{ $data['total_warga_bekerja'] }}</div>
            <div class="analisis-kpi-label">Warga Prasejahtera Bekerja</div>
            <div class="analisis-kpi-sub">{{ $trenTotal }} pendaftaran baru dalam periode ini</div>
        </div>

        <div class="analisis-kpi analisis-kpi--env">
            <div class="analisis-kpi-top">
                <div class="analisis-kpi-icon" style="background: rgba(34,197,94,0.1); color: #16a34a;">
                    <i data-lucide="leaf" style="width: 22px; height: 22px;"></i>
                </div>
            </div>
            <div class="analisis-kpi-value">
                {{ number_format($data['dampak_lingkungan']['value'], 0, ',', '.') }}
                <span class="analisis-kpi-unit">{{ $data['dampak_lingkungan']['unit'] }}</span>
            </div>
            <div class="analisis-kpi-label">Akumulasi Dampak Lingkungan</div>
            <div class="analisis-kpi-sub">{{ number_format($totalEmisi, 1, ',', '.') }} Kg CO₂ emisi berkurang</div>
        </div>

        <div class="analisis-kpi analisis-kpi--money">
            <div class="analisis-kpi-top">
                <div class="analisis-kpi-icon" style="background: rgba(245,158,11,0.1); color: #d97706;">
                    <i data-lucide="wallet" style="width: 22px; height: 22px;"></i>
                </div>
            </div>
            <div class="analisis-kpi-value" style="font-size: 1.45rem;">
                Rp {{ number_format($data['total_insentif'], 0, ',', '.') }}
            </div>
            <div class="analisis-kpi-label">Total Dana Insentif</div>
            <div class="analisis-kpi-sub">Distribusi upah pekerja desa</div>
        </div>

        <div class="analisis-kpi analisis-kpi--programs">
            <div class="analisis-kpi-top">
                <div class="analisis-kpi-icon" style="background: rgba(139,92,246,0.1); color: #7c3aed;">
                    <i data-lucide="target" style="width: 22px; height: 22px;"></i>
                </div>
                <span class="analisis-kpi-badge analisis-kpi-badge--up">{{ $completionRate }}%</span>
            </div>
            <div class="analisis-kpi-value">{{ $programTotal }}</div>
            <div class="analisis-kpi-label">Program Kerja Terdaftar</div>
            <div class="analisis-kpi-sub">{{ $programSelesai }} selesai · {{ $programAktif }} aktif</div>
        </div>
    </div>

    {{-- Charts row --}}
    <div class="analisis-main">
        <div class="analisis-panel">
            <div class="analisis-panel-header">
                <div>
                    <h2>
                        <i data-lucide="trending-up" style="width: 17px; height: 17px; color: var(--primary);"></i>
                        Tren Partisipasi Warga
                    </h2>
                    <p>{{ $periodShort }} · {{ $periodLabel }}</p>
                </div>
            </div>
            @if($trenTotal === 0)
                <div class="analisis-chart-hint">
                    <i data-lucide="info" style="width: 14px; height: 14px;"></i>
                    Belum ada pekerja baru terdaftar dalam periode ini. Grafik menampilkan timeline kosong.
                </div>
            @elseif($trenActivePeriods === 1)
                <div class="analisis-chart-hint">
                    <i data-lucide="info" style="width: 14px; height: 14px;"></i>
                    {{ $trenTotal }} pekerja terdaftar dalam 1 periode. Bulan tanpa pendaftaran ditampilkan sebagai 0.
                </div>
            @endif
            <div class="analisis-chart-wrap">
                <canvas id="trenChart"></canvas>
            </div>
        </div>

        <div class="analisis-panel">
            <div class="analisis-panel-header">
                <div>
                    <h2>
                        <i data-lucide="pie-chart" style="width: 17px; height: 17px; color: #7c3aed;"></i>
                        Sebaran Sektor Program
                    </h2>
                    <p>{{ $sebaran->count() }} jenis sektor</p>
                </div>
            </div>
            <div class="analisis-chart-wrap analisis-chart-wrap--sm">
                <canvas id="sebaranChart"></canvas>
            </div>
            @if($sebaran->count() > 0)
                <div class="analisis-sebaran-list">
                    @php $colors = ['#0f766e', '#3b82f6', '#f59e0b', '#8b5cf6', '#22c55e', '#ec4899']; @endphp
                    @foreach($sebaran as $i => $item)
                        @php $pct = round(($item->value / $sebaranTotal) * 100); @endphp
                        <div class="analisis-sebaran-item">
                            <div class="analisis-sebaran-dot" style="background: {{ $colors[$i % count($colors)] }};"></div>
                            <div class="analisis-sebaran-info">
                                <div class="analisis-sebaran-name">{{ $item->name ?: 'Tanpa Kategori' }}</div>
                                <div class="analisis-sebaran-bar-track">
                                    <div class="analisis-sebaran-bar-fill" style="width: {{ $pct }}%; background: {{ $colors[$i % count($colors)] }};"></div>
                                </div>
                            </div>
                            <div class="analisis-sebaran-val">{{ $item->value }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Bottom: env + completion --}}
    <div class="analisis-bottom">
        <div class="analisis-panel no-print">
            <div class="analisis-panel-header">
                <div>
                    <h2>
                        <i data-lucide="recycle" style="width: 17px; height: 17px; color: #16a34a;"></i>
                        Input Dampak Lingkungan
                    </h2>
                    <p>Catat volume limbah dan estimasi emisi berkurang</p>
                </div>
            </div>

            <div class="analisis-env-summary">
                <div class="analisis-env-stat">
                    <div class="analisis-env-stat-val" style="color: #16a34a;">{{ $environmentalRecords->count() }}</div>
                    <div class="analisis-env-stat-label">Entri Tercatat</div>
                </div>
                <div class="analisis-env-stat">
                    <div class="analisis-env-stat-val">{{ number_format($environmentalRecords->sum('volume_kg'), 1, ',', '.') }}</div>
                    <div class="analisis-env-stat-label">Total Volume (Kg)</div>
                </div>
                <div class="analisis-env-stat">
                    <div class="analisis-env-stat-val" style="color: #0891b2;">{{ number_format($totalEmisi, 1, ',', '.') }}</div>
                    <div class="analisis-env-stat-label">CO₂ Berkurang (Kg)</div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.analisis.dampak') }}" class="analisis-env-form">
                @csrf
                <div class="form-group">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-input" value="{{ now()->toDateString() }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Jenis Limbah</label>
                    <select name="jenis_limbah" class="form-input" required>
                        <option value="Organik">Organik</option>
                        <option value="Kompos">Kompos</option>
                        <option value="Plastik Daur Ulang">Plastik Daur Ulang</option>
                        <option value="Sampah Terpilah">Sampah Terpilah</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Volume (Kg)</label>
                    <input type="number" name="volume_kg" class="form-input" min="0" step="0.01" placeholder="25.5" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Emisi Berkurang (Kg CO₂)</label>
                    <input type="number" name="estimasi_emisi_berkurang_kg" class="form-input" min="0" step="0.01" placeholder="10">
                </div>
                <button type="submit" class="btn btn-primary" style="height: fit-content; white-space: nowrap;">
                    <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                    Simpan
                </button>
            </form>

            @if($environmentalRecords->count())
                <div class="analisis-table-wrap" style="margin-top: 1.15rem;">
                    <table class="analisis-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Volume</th>
                                <th>Emisi ↓</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($environmentalRecords as $record)
                                <tr>
                                    <td>{{ $record->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ $record->jenis_limbah }}</td>
                                    <td>{{ number_format($record->volume_kg, 1, ',', '.') }} Kg</td>
                                    <td style="color: #16a34a; font-weight: 600;">{{ number_format($record->estimasi_emisi_berkurang_kg, 1, ',', '.') }} Kg</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="analisis-panel">
            <div class="analisis-panel-header">
                <div>
                    <h2>
                        <i data-lucide="award" style="width: 17px; height: 17px; color: #d97706;"></i>
                        Ringkasan Capaian
                    </h2>
                    <p>Progress program kerja desa</p>
                </div>
            </div>

            <div class="analisis-completion">
                <div class="analisis-completion-ring" style="background: conic-gradient(#22c55e 0% {{ $completionRate }}%, var(--border) {{ $completionRate }}% 100%);">
                    <div class="analisis-completion-ring-inner">{{ $completionRate }}%</div>
                </div>
                <div class="analisis-completion-stats">
                    <div class="analisis-completion-row">
                        <span>Total Program</span>
                        <span>{{ $programTotal }}</span>
                    </div>
                    <div class="analisis-completion-row">
                        <span>Aktif / Berjalan</span>
                        <span style="color: #2563eb;">{{ $programAktif }}</span>
                    </div>
                    <div class="analisis-completion-row">
                        <span>Selesai</span>
                        <span style="color: #16a34a;">{{ $programSelesai }}</span>
                    </div>
                    <div class="analisis-completion-row">
                        <span>Direncanakan</span>
                        <span style="color: #d97706;">{{ $programTotal - $programSelesai - $programAktif }}</span>
                    </div>
                </div>
            </div>

            <div class="analisis-chart-wrap analisis-chart-wrap--sm" style="margin-top: 1rem;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Program table --}}
    <div class="analisis-panel">
        <div class="analisis-panel-header">
            <div>
                <h2>
                    <i data-lucide="list-checks" style="width: 17px; height: 17px; color: var(--primary);"></i>
                    Rincian Capaian Program Kerja
                </h2>
                <p>Daftar lengkap program dan status pencapaiannya</p>
            </div>
            <a href="/admin/perencanaan" class="analisis-panel-link">
                Kelola program <i data-lucide="arrow-right" style="width: 12px; height: 12px;"></i>
            </a>
        </div>

        @if($programs->count())
            <div class="analisis-table-wrap">
                <table class="analisis-table">
                    <thead>
                        <tr>
                            <th>Program</th>
                            <th>Sektor</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($programs as $item)
                            @php
                                $isDone = in_array($item->status, ['selesai', 'completed']);
                                $isActive = in_array($item->status, ['active', 'ongoing', 'in_progress', 'berjalan']);
                                $statusClass = $isDone ? 'analisis-status--done' : ($isActive ? 'analisis-status--active' : 'analisis-status--planned');
                                $statusLabel = $isDone ? 'Selesai' : ($isActive ? 'Aktif' : 'Direncanakan');
                                $statusIcon = $isDone ? 'check-circle' : ($isActive ? 'play-circle' : 'clock');
                            @endphp
                            <tr>
                                <td style="font-weight: 600;">{{ $item->nama_program }}</td>
                                <td>{{ $item->jenis_program ?: '-' }}</td>
                                <td>{{ $item->tanggal_mulai ? \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $item->tanggal_selesai ? \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') : '-' }}</td>
                                <td>
                                    <span class="analisis-status {{ $statusClass }}">
                                        <i data-lucide="{{ $statusIcon }}" style="width: 12px; height: 12px;"></i>
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="analisis-empty">
                <i data-lucide="briefcase" style="width: 32px; height: 32px;"></i>
                Belum ada data program kerja.<br>
                <span style="font-size: 0.78rem; margin-top: 0.35rem;">Tambahkan program melalui halaman Perencanaan.</span>
            </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('analisisData', () => ({
            period: '{{ $period }}',
            data: @json($data),
            programSelesai: {{ $programSelesai }},
            programAktif: {{ $programAktif }},
            programPlanned: {{ $programTotal - $programSelesai - $programAktif }},

            init() {
                this.$nextTick(() => {
                    setTimeout(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        this.renderCharts();
                    }, 50);
                });
            },

            changePeriod() {
                window.location.href = `/admin/analisis?period=${this.period}`;
            },

            downloadPdf() {
                window.location.href = `/admin/analisis/pdf?period=${this.period}`;
            },

            renderCharts() {
                if (typeof Chart === 'undefined') return;

                const trenLabels = this.data.tren_partisipasi.map(d => d.bulan);
                const trenValues = this.data.tren_partisipasi.map(d => d.partisipasi);
                const pointRadii = trenValues.map(v => v > 0 ? 5 : 2);
                const pointColors = trenValues.map(v => v > 0 ? '#0f766e' : 'rgba(15,118,110,0.25)');

                const ctxTren = document.getElementById('trenChart');
                if (ctxTren) {
                    const gradient = ctxTren.getContext('2d').createLinearGradient(0, 0, 0, 300);
                    gradient.addColorStop(0, 'rgba(15, 118, 110, 0.35)');
                    gradient.addColorStop(1, 'rgba(15, 118, 110, 0.02)');

                    new Chart(ctxTren.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: trenLabels,
                            datasets: [{
                                label: 'Pekerja Baru',
                                data: trenValues,
                                borderColor: '#0f766e',
                                backgroundColor: gradient,
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.35,
                                pointBackgroundColor: pointColors,
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: pointRadii,
                                pointHoverRadius: trenValues.map(v => v > 0 ? 7 : 4),
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { intersect: false, mode: 'index' },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#0f172a',
                                    padding: 12,
                                    cornerRadius: 8,
                                    titleFont: { size: 12 },
                                    bodyFont: { size: 13, weight: '600' },
                                    filter: (item) => item.raw > 0,
                                },
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    suggestedMax: Math.max(5, ...trenValues) + 2,
                                    grid: { color: 'rgba(226,232,240,0.6)' },
                                    ticks: {
                                        stepSize: Math.max(1, Math.ceil(Math.max(...trenValues, 1) / 5)),
                                        font: { size: 11 },
                                    },
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: {
                                        font: { size: 10 },
                                        maxRotation: 45,
                                        minRotation: trenLabels.length > 6 ? 30 : 0,
                                        autoSkip: false,
                                    },
                                },
                            },
                        },
                    });
                }

                const sebaranLabels = this.data.sebaran_program.length > 0
                    ? this.data.sebaran_program.map(d => d.name || 'Lainnya')
                    : ['Belum Ada'];
                const sebaranValues = this.data.sebaran_program.length > 0
                    ? this.data.sebaran_program.map(d => d.value)
                    : [1];
                const COLORS = ['#0f766e', '#3b82f6', '#f59e0b', '#8b5cf6', '#22c55e', '#ec4899'];

                const ctxSebaran = document.getElementById('sebaranChart');
                if (ctxSebaran) {
                    new Chart(ctxSebaran.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: sebaranLabels,
                            datasets: [{
                                data: sebaranValues,
                                backgroundColor: this.data.sebaran_program.length > 0 ? COLORS : ['#e2e8f0'],
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
                                            const total = sebaranValues.reduce((a, b) => a + b, 0);
                                            const pct = total > 0 ? Math.round((ctx.parsed / total) * 100) : 0;
                                            return `${ctx.label}: ${ctx.parsed} (${pct}%)`;
                                        },
                                    },
                                },
                            },
                        },
                    });
                }

                const ctxStatus = document.getElementById('statusChart');
                if (ctxStatus) {
                    new Chart(ctxStatus.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: ['Aktif', 'Selesai', 'Direncanakan'],
                            datasets: [{
                                data: [this.programAktif, this.programSelesai, this.programPlanned],
                                backgroundColor: ['#3b82f6', '#22c55e', '#f59e0b'],
                                borderRadius: 8,
                                borderSkipped: false,
                                barThickness: 36,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
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
