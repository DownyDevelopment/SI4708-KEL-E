@extends('layouts.app')
@section('title', 'Dashboard Admin')

@php
    $prof = $data['profiling'];
    $tugas = $data['tugas'];
    $programs = $data['area'];
    $dampak = $data['dampak'];

    $profTotal = max(1, $prof['total']);
    $petaniPct = round(($prof['petani'] / $profTotal) * 100);
    $pembersihPct = round(($prof['pembersih'] / $profTotal) * 100);
    $pengrajinPct = round(($prof['pengrajin'] / $profTotal) * 100);
    $lainnyaProf = max(0, $prof['total'] - $prof['petani'] - $prof['pembersih'] - $prof['pengrajin']);

    $donutGradient = $prof['total'] === 0
        ? 'conic-gradient(var(--border) 0% 100%)'
        : sprintf(
            'conic-gradient(#22c55e 0%% %s%%, #f59e0b %s%% %s%%, #8b5cf6 %s%% %s%%, #e2e8f0 %s%% 100%%)',
            $petaniPct,
            $petaniPct,
            $petaniPct + $pembersihPct,
            $petaniPct + $pembersihPct,
            $petaniPct + $pembersihPct + $pengrajinPct,
            $petaniPct + $pembersihPct + $pengrajinPct
        );

    $totalTugasForProgress = $tugas['total'] === 0 ? 1 : $tugas['total'];
    $tugasSelesaiPct = round(($tugas['selesai'] / $totalTugasForProgress) * 100);

    $programTotal = $programs->count();
    $programAktif = $programs->whereIn('status', ['active', 'ongoing', 'in_progress'])->count();
    $programPlanned = $programs->where('status', 'planned')->count();
    $programMapped = $programs->filter(fn ($p) => !empty($p->kordinat))->count();
    $produksiTotal = $dampak->count();

    $greeting = match (true) {
        now()->hour < 11 => 'Selamat pagi',
        now()->hour < 15 => 'Selamat siang',
        now()->hour < 18 => 'Selamat sore',
        default => 'Selamat malam',
    };
@endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .admin-dash {
        padding: 2rem;
        max-width: 1440px;
        margin: 0 auto;
    }

    /* Hero */
    .admin-dash-hero {
        background: linear-gradient(135deg, #1e3a5f 0%, #0f766e 50%, #0891b2 100%);
        border-radius: var(--radius-lg);
        padding: 2rem 2.25rem;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 1.75rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(15, 118, 110, 0.22);
    }

    .admin-dash-hero::before {
        content: '';
        position: absolute;
        top: -40%;
        right: -5%;
        width: 320px;
        height: 320px;
        background: rgba(255, 255, 255, 0.07);
        border-radius: 50%;
        pointer-events: none;
    }

    .admin-dash-hero::after {
        content: '';
        position: absolute;
        bottom: -30%;
        right: 15%;
        width: 180px;
        height: 180px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        pointer-events: none;
    }

    .admin-dash-hero-content {
        position: relative;
        z-index: 1;
    }

    .admin-dash-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.25);
        padding: 0.3rem 0.75rem;
        border-radius: 99px;
        font-size: 0.72rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        backdrop-filter: blur(4px);
    }

    .admin-dash-hero h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        margin: 0 0 0.4rem;
        line-height: 1.25;
    }

    .admin-dash-hero p {
        color: rgba(255, 255, 255, 0.88);
        font-size: 0.92rem;
        margin: 0;
        max-width: 520px;
        line-height: 1.55;
    }

    .admin-dash-hero-actions {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
        flex-shrink: 0;
    }

    .admin-dash-btn-white {
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

    .admin-dash-btn-white:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .admin-dash-btn-ghost {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(255, 255, 255, 0.12);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 99px;
        padding: 0.65rem 1.25rem;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.15s;
        backdrop-filter: blur(4px);
    }

    .admin-dash-btn-ghost:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    /* KPI stats */
    .admin-dash-kpis {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 0.85rem;
        margin-bottom: 1.75rem;
    }

    @media (max-width: 1200px) {
        .admin-dash-kpis { grid-template-columns: repeat(3, 1fr); }
    }

    @media (max-width: 640px) {
        .admin-dash-kpis { grid-template-columns: repeat(2, 1fr); }
    }

    .admin-dash-kpi {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: transform 0.2s, box-shadow 0.2s;
        text-decoration: none;
        color: inherit;
    }

    .admin-dash-kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        border-color: rgba(15, 118, 110, 0.2);
    }

    .admin-dash-kpi-icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .admin-dash-kpi-value {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--text-main);
        line-height: 1.1;
    }

    .admin-dash-kpi-label {
        font-size: 0.7rem;
        color: var(--text-muted);
        margin-top: 0.05rem;
    }

    /* Main grid */
    .admin-dash-main {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 1.25rem;
        margin-bottom: 1.25rem;
    }

    @media (max-width: 1100px) {
        .admin-dash-main { grid-template-columns: 1fr 1fr; }
    }

    @media (max-width: 720px) {
        .admin-dash-main { grid-template-columns: 1fr; }
    }

    .admin-dash-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.35rem;
        display: flex;
        flex-direction: column;
    }

    .admin-dash-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.15rem;
        gap: 0.75rem;
    }

    .admin-dash-panel-header h2 {
        font-size: 0.92rem;
        font-weight: 600;
        color: var(--text-main);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .admin-dash-panel-header p {
        font-size: 0.72rem;
        color: var(--text-muted);
        margin: 0.15rem 0 0;
    }

    .admin-dash-panel-link {
        font-size: 0.72rem;
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .admin-dash-panel-link:hover {
        text-decoration: underline;
    }

    /* Profiling donut */
    .admin-dash-donut-wrap {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex: 1;
    }

    @media (max-width: 480px) {
        .admin-dash-donut-wrap { flex-direction: column; }
    }

    .admin-dash-donut {
        width: 130px;
        height: 130px;
        border-radius: 50%;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: inset 0 0 0 3px rgba(255, 255, 255, 0.15);
    }

    .admin-dash-donut-inner {
        width: 88px;
        height: 88px;
        background: var(--surface);
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .admin-dash-donut-value {
        font-size: 1.65rem;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1;
    }

    .admin-dash-donut-label {
        font-size: 0.62rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-top: 0.15rem;
    }

    .admin-dash-legend {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
    }

    .admin-dash-legend-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.78rem;
    }

    .admin-dash-legend-left {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        color: var(--text-muted);
    }

    .admin-dash-legend-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .admin-dash-legend-val {
        font-weight: 700;
        color: var(--text-main);
    }

    /* Tugas */
    .admin-dash-tugas-ring {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        flex: 1;
    }

    .admin-dash-tugas-big {
        font-size: 2.75rem;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1;
        flex-shrink: 0;
    }

    .admin-dash-tugas-big span {
        display: block;
        font-size: 0.68rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-top: 0.2rem;
    }

    .admin-dash-progress-list {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }

    .admin-dash-progress-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.75rem;
        margin-bottom: 0.25rem;
    }

    .admin-dash-progress-row span:first-child { color: var(--text-muted); }
    .admin-dash-progress-row span:last-child { font-weight: 700; color: var(--text-main); }

    .admin-dash-progress-track {
        height: 6px;
        background: var(--border);
        border-radius: 99px;
        overflow: hidden;
    }

    .admin-dash-progress-fill {
        height: 100%;
        border-radius: 99px;
        transition: width 0.5s ease;
    }

    /* Program mini stats */
    .admin-dash-program-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.65rem;
        margin-bottom: 1rem;
    }

    .admin-dash-program-stat {
        text-align: center;
        padding: 0.75rem 0.5rem;
        background: var(--background);
        border-radius: 10px;
        border: 1px solid var(--border);
    }

    .admin-dash-program-stat-val {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .admin-dash-program-stat-label {
        font-size: 0.65rem;
        color: var(--text-muted);
        margin-top: 0.1rem;
    }

    .admin-dash-program-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        flex: 1;
    }

    .admin-dash-program-item {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.65rem 0.75rem;
        border-radius: 10px;
        border: 1px solid var(--border);
        transition: background 0.15s;
        text-decoration: none;
        color: inherit;
    }

    .admin-dash-program-item:hover {
        background: var(--background);
    }

    .admin-dash-program-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .admin-dash-program-name {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-main);
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .admin-dash-program-status {
        font-size: 0.65rem;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 99px;
        flex-shrink: 0;
    }

    /* Bottom grid */
    .admin-dash-bottom {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
        margin-bottom: 1.25rem;
    }

    @media (max-width: 900px) {
        .admin-dash-bottom { grid-template-columns: 1fr; }
    }

    .admin-dash-chart-wrap {
        height: 260px;
        position: relative;
    }

    /* Impact list */
    .admin-dash-impact-list {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        flex: 1;
    }

    .admin-dash-impact-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: var(--background);
    }

    .admin-dash-impact-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .admin-dash-impact-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-main);
    }

    .admin-dash-impact-value {
        font-size: 0.78rem;
        color: var(--primary);
        font-weight: 600;
    }

    .admin-dash-impact-cat {
        font-size: 0.68rem;
        color: var(--text-muted);
        margin-left: auto;
        flex-shrink: 0;
    }

    .admin-dash-empty {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        color: var(--text-muted);
        font-size: 0.85rem;
        text-align: center;
    }

    .admin-dash-empty i {
        opacity: 0.35;
        margin-bottom: 0.5rem;
    }

    /* Map section */
    .admin-dash-map-section {
        display: grid;
        grid-template-columns: 1fr 280px;
        gap: 1rem;
    }

    @media (max-width: 900px) {
        .admin-dash-map-section { grid-template-columns: 1fr; }
    }

    .admin-dash-map-wrap {
        position: relative;
        height: 380px;
        border-radius: 12px;
        overflow: hidden;
        background: #e2e8f0;
        border: 1px solid var(--border);
    }

    .admin-dash-map-wrap #dashboard-map {
        height: 100%;
        width: 100%;
        z-index: 0;
    }

    .admin-dash-map-legend {
        position: absolute;
        bottom: 0.75rem;
        left: 0.75rem;
        background: white;
        border-radius: 10px;
        padding: 0.55rem 0.75rem;
        font-size: 0.68rem;
        z-index: 500;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        border: 1px solid var(--border);
        display: flex;
        gap: 0.75rem;
    }

    .admin-dash-map-legend-item {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        color: var(--text-muted);
    }

    .admin-dash-map-sidebar {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        max-height: 380px;
        overflow-y: auto;
    }

    .admin-dash-map-sidebar-item {
        padding: 0.7rem 0.85rem;
        border: 1px solid var(--border);
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.15s;
        background: var(--background);
    }

    .admin-dash-map-sidebar-item:hover,
    .admin-dash-map-sidebar-item--active {
        border-color: rgba(15, 118, 110, 0.35);
        background: rgba(15, 118, 110, 0.06);
    }

    .admin-dash-map-sidebar-title {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 0.15rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .admin-dash-map-sidebar-loc {
        font-size: 0.68rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    /* Quick links */
    .admin-dash-quicklinks {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.85rem;
        margin-top: 1.25rem;
    }

    @media (max-width: 900px) {
        .admin-dash-quicklinks { grid-template-columns: repeat(2, 1fr); }
    }

    .admin-dash-quicklink {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 1.1rem;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        text-decoration: none;
        color: inherit;
        transition: all 0.2s;
    }

    .admin-dash-quicklink:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
    }

    .admin-dash-quicklink-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .admin-dash-quicklink-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-main);
    }

    .admin-dash-quicklink-desc {
        font-size: 0.68rem;
        color: var(--text-muted);
        margin-top: 0.1rem;
    }
</style>
@endpush

@section('content')
<div class="admin-dash animate-fade-in">

    <x-hero-banner title="{{ $greeting }}, {{ auth()->user()->nama }}!" description="Pantau progres program kerja mikro, pekerja desa, dan hasil produksi dari satu dashboard terpusat.">
        <x-slot:actions>
            <a href="/admin/analisis" class="global-hero-banner-btn-white">
                <i data-lucide="bar-chart-2" style="width: 16px; height: 16px;"></i>
                Laporan Analisis
            </a>
            <a href="/admin/perencanaan" class="global-hero-banner-btn-ghost">
                <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                Tambah Program
            </a>
        </x-slot:actions>
    </x-hero-banner>

    {{-- KPI row --}}
    <div class="admin-dash-kpis">
        <a href="/admin/pekerja" class="admin-dash-kpi">
            <div class="admin-dash-kpi-icon" style="background: rgba(15, 118, 110, 0.1); color: var(--primary);">
                <i data-lucide="users" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div class="admin-dash-kpi-value">{{ $prof['total'] }}</div>
                <div class="admin-dash-kpi-label">Total Pekerja</div>
            </div>
        </a>
        <a href="/admin/perencanaan" class="admin-dash-kpi">
            <div class="admin-dash-kpi-icon" style="background: rgba(34, 197, 94, 0.1); color: #16a34a;">
                <i data-lucide="briefcase" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div class="admin-dash-kpi-value">{{ $programAktif }}</div>
                <div class="admin-dash-kpi-label">Program Aktif</div>
            </div>
        </a>
        <a href="/admin/tugas" class="admin-dash-kpi">
            <div class="admin-dash-kpi-icon" style="background: rgba(59, 130, 246, 0.1); color: #2563eb;">
                <i data-lucide="clipboard-list" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div class="admin-dash-kpi-value">{{ $tugas['total'] }}</div>
                <div class="admin-dash-kpi-label">Tugas Minggu Ini</div>
            </div>
        </a>
        <a href="/admin/inventaris" class="admin-dash-kpi">
            <div class="admin-dash-kpi-icon" style="background: rgba(245, 158, 11, 0.1); color: #d97706;">
                <i data-lucide="package" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div class="admin-dash-kpi-value">{{ $produksiTotal }}</div>
                <div class="admin-dash-kpi-label">Item Produksi</div>
            </div>
        </a>
        <a href="/admin/perencanaan" class="admin-dash-kpi">
            <div class="admin-dash-kpi-icon" style="background: rgba(139, 92, 246, 0.1); color: #7c3aed;">
                <i data-lucide="map-pin" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div class="admin-dash-kpi-value">{{ $programMapped }}</div>
                <div class="admin-dash-kpi-label">Area Terpetakan</div>
            </div>
        </a>
        <a href="/admin/tugas" class="admin-dash-kpi">
            <div class="admin-dash-kpi-icon" style="background: rgba(6, 182, 212, 0.1); color: #0891b2;">
                <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div class="admin-dash-kpi-value">{{ $tugasSelesaiPct }}%</div>
                <div class="admin-dash-kpi-label">Tugas Selesai</div>
            </div>
        </a>
    </div>

    {{-- Top 3 panels --}}
    <div class="admin-dash-main">

        {{-- Profiling --}}
        <div class="admin-dash-panel">
            <div class="admin-dash-panel-header">
                <div>
                    <h2><i data-lucide="pie-chart" style="width: 17px; height: 17px; color: var(--primary);"></i> Profiling Pekerja</h2>
                    <p>Sebaran keahlian utama pekerja desa</p>
                </div>
                <a href="/admin/pekerja" class="admin-dash-panel-link">
                    Lihat semua <i data-lucide="arrow-right" style="width: 12px; height: 12px;"></i>
                </a>
            </div>
            <div class="admin-dash-donut-wrap">
                <div class="admin-dash-donut" style="background: {{ $donutGradient }};">
                    <div class="admin-dash-donut-inner">
                        <div class="admin-dash-donut-value">{{ $prof['total'] }}</div>
                        <div class="admin-dash-donut-label">Pekerja</div>
                    </div>
                </div>
                <div class="admin-dash-legend">
                    <div class="admin-dash-legend-item">
                        <div class="admin-dash-legend-left">
                            <div class="admin-dash-legend-dot" style="background: #22c55e;"></div>
                            Petani / Kebun
                        </div>
                        <span class="admin-dash-legend-val">{{ $prof['petani'] }}</span>
                    </div>
                    <div class="admin-dash-legend-item">
                        <div class="admin-dash-legend-left">
                            <div class="admin-dash-legend-dot" style="background: #f59e0b;"></div>
                            Pembersih / Lingkungan
                        </div>
                        <span class="admin-dash-legend-val">{{ $prof['pembersih'] }}</span>
                    </div>
                    <div class="admin-dash-legend-item">
                        <div class="admin-dash-legend-left">
                            <div class="admin-dash-legend-dot" style="background: #8b5cf6;"></div>
                            Pengrajin
                        </div>
                        <span class="admin-dash-legend-val">{{ $prof['pengrajin'] }}</span>
                    </div>
                    @if($lainnyaProf > 0)
                    <div class="admin-dash-legend-item">
                        <div class="admin-dash-legend-left">
                            <div class="admin-dash-legend-dot" style="background: #e2e8f0;"></div>
                            Lainnya
                        </div>
                        <span class="admin-dash-legend-val">{{ $lainnyaProf }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tugas mingguan --}}
        <div class="admin-dash-panel">
            <div class="admin-dash-panel-header">
                <div>
                    <h2><i data-lucide="calendar-check" style="width: 17px; height: 17px; color: #2563eb;"></i> Tugas Mingguan</h2>
                    <p>{{ $tugas['periode_label'] ?? 'Minggu ini' }}</p>
                </div>
                <a href="/admin/tugas" class="admin-dash-panel-link">
                    Kelola tugas <i data-lucide="arrow-right" style="width: 12px; height: 12px;"></i>
                </a>
            </div>
            <div class="admin-dash-tugas-ring">
                <div class="admin-dash-tugas-big">
                    {{ $tugas['total'] }}
                    <span>Total Tugas</span>
                </div>
                <div class="admin-dash-progress-list">
                    <div>
                        <div class="admin-dash-progress-row">
                            <span>Aktif</span>
                            <span>{{ $tugas['aktif'] }}</span>
                        </div>
                        <div class="admin-dash-progress-track">
                            <div class="admin-dash-progress-fill" style="width: {{ ($tugas['aktif'] / $totalTugasForProgress) * 100 }}%; background: #22c55e;"></div>
                        </div>
                    </div>
                    <div>
                        <div class="admin-dash-progress-row">
                            <span>Terjadwal</span>
                            <span>{{ $tugas['terjadwal'] }}</span>
                        </div>
                        <div class="admin-dash-progress-track">
                            <div class="admin-dash-progress-fill" style="width: {{ ($tugas['terjadwal'] / $totalTugasForProgress) * 100 }}%; background: #3b82f6;"></div>
                        </div>
                    </div>
                    <div>
                        <div class="admin-dash-progress-row">
                            <span>Selesai</span>
                            <span>{{ $tugas['selesai'] }}</span>
                        </div>
                        <div class="admin-dash-progress-track">
                            <div class="admin-dash-progress-fill" style="width: {{ ($tugas['selesai'] / $totalTugasForProgress) * 100 }}%; background: #94a3b8;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Program ringkasan --}}
        <div class="admin-dash-panel">
            <div class="admin-dash-panel-header">
                <div>
                    <h2><i data-lucide="layers" style="width: 17px; height: 17px; color: #7c3aed;"></i> Program Kerja</h2>
                    <p>{{ $programTotal }} program terdaftar</p>
                </div>
                <a href="/admin/perencanaan" class="admin-dash-panel-link">
                    Kelola <i data-lucide="arrow-right" style="width: 12px; height: 12px;"></i>
                </a>
            </div>
            <div class="admin-dash-program-stats">
                <div class="admin-dash-program-stat">
                    <div class="admin-dash-program-stat-val" style="color: #16a34a;">{{ $programAktif }}</div>
                    <div class="admin-dash-program-stat-label">Aktif</div>
                </div>
                <div class="admin-dash-program-stat">
                    <div class="admin-dash-program-stat-val" style="color: #d97706;">{{ $programPlanned }}</div>
                    <div class="admin-dash-program-stat-label">Direncanakan</div>
                </div>
                <div class="admin-dash-program-stat">
                    <div class="admin-dash-program-stat-val" style="color: #7c3aed;">{{ $programMapped }}</div>
                    <div class="admin-dash-program-stat-label">Terpetakan</div>
                </div>
            </div>
            <div class="admin-dash-program-list">
                @forelse($programs->take(4) as $prog)
                    @php
                        $isActive = in_array($prog->status, ['active', 'ongoing', 'in_progress']);
                        $isDone = in_array($prog->status, ['completed', 'selesai']);
                        $dotColor = $isActive ? '#22c55e' : ($isDone ? '#3b82f6' : '#f59e0b');
                        $statusLabel = $isActive ? 'Aktif' : ($isDone ? 'Selesai' : 'Direncanakan');
                        $statusBg = $isActive ? 'rgba(34,197,94,0.12)' : ($isDone ? 'rgba(59,130,246,0.12)' : 'rgba(245,158,11,0.12)');
                        $statusFg = $isActive ? '#15803d' : ($isDone ? '#1d4ed8' : '#b45309');
                    @endphp
                    <a href="/admin/perencanaan" class="admin-dash-program-item">
                        <div class="admin-dash-program-dot" style="background: {{ $dotColor }};"></div>
                        <div class="admin-dash-program-name">{{ $prog->nama_program }}</div>
                        <span class="admin-dash-program-status" style="background: {{ $statusBg }}; color: {{ $statusFg }};">{{ $statusLabel }}</span>
                    </a>
                @empty
                    <div class="admin-dash-empty">
                        <i data-lucide="briefcase" style="width: 28px; height: 28px;"></i>
                        Belum ada program kerja.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Bottom: chart + produksi --}}
    <div class="admin-dash-bottom">

        {{-- Bidang kerja chart --}}
        <div class="admin-dash-panel" x-data="bidangKerjaChart()">
            <div class="admin-dash-panel-header">
                <div>
                    <h2><i data-lucide="bar-chart" style="width: 17px; height: 17px; color: var(--primary);"></i> Sebaran Bidang Kerja</h2>
                    <p>Distribusi keahlian pekerja desa</p>
                </div>
                <a href="/admin/profiling" class="admin-dash-panel-link">
                    Profiling <i data-lucide="arrow-right" style="width: 12px; height: 12px;"></i>
                </a>
            </div>
            <div class="admin-dash-chart-wrap">
                <canvas id="bidangKerjaChart"></canvas>
            </div>
        </div>

        {{-- Hasil produksi --}}
        <div class="admin-dash-panel">
            <div class="admin-dash-panel-header">
                <div>
                    <h2><i data-lucide="sprout" style="width: 17px; height: 17px; color: #16a34a;"></i> Hasil Produksi & Dampak</h2>
                    <p>Stok inventaris hasil kerja desa</p>
                </div>
                <a href="/admin/inventaris" class="admin-dash-panel-link">
                    Inventaris <i data-lucide="arrow-right" style="width: 12px; height: 12px;"></i>
                </a>
            </div>
            <div class="admin-dash-impact-list">
                @forelse($dampak->take(5) as $i => $d)
                    @php
                        $colors = [
                            ['bg' => 'rgba(34,197,94,0.1)', 'fg' => '#16a34a', 'icon' => 'sprout'],
                            ['bg' => 'rgba(245,158,11,0.1)', 'fg' => '#d97706', 'icon' => 'recycle'],
                            ['bg' => 'rgba(139,92,246,0.1)', 'fg' => '#7c3aed', 'icon' => 'hammer'],
                            ['bg' => 'rgba(59,130,246,0.1)', 'fg' => '#2563eb', 'icon' => 'leaf'],
                            ['bg' => 'rgba(236,72,153,0.1)', 'fg' => '#db2777', 'icon' => 'package'],
                        ];
                        $c = $colors[$i % count($colors)];
                    @endphp
                    <div class="admin-dash-impact-item">
                        <div class="admin-dash-impact-icon" style="background: {{ $c['bg'] }}; color: {{ $c['fg'] }};">
                            <i data-lucide="{{ $c['icon'] }}" style="width: 18px; height: 18px;"></i>
                        </div>
                        <div>
                            <div class="admin-dash-impact-title">{{ $d->nama_barang }}</div>
                            <div class="admin-dash-impact-value">{{ number_format($d->kuantitas, 0, ',', '.') }} {{ $d->satuan }}</div>
                        </div>
                        <div class="admin-dash-impact-cat">{{ $d->kategori }}</div>
                    </div>
                @empty
                    <div class="admin-dash-empty">
                        <i data-lucide="package-open" style="width: 28px; height: 28px;"></i>
                        Belum ada data produksi.<br>
                        <span style="font-size: 0.78rem;">Hasil kerja pekerja akan muncul di sini.</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Map --}}
    <div class="admin-dash-panel" x-data="dashboardMapData()">
        <div class="admin-dash-panel-header">
            <div>
                <h2><i data-lucide="map" style="width: 17px; height: 17px; color: var(--primary);"></i> Peta Area Kerja</h2>
                <p x-text="programs.length + ' program · ' + mappedCount + ' terpetakan'"></p>
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <button class="btn btn-outline" @click="toggleMapType" style="padding: 0.4rem 0.75rem; font-size: 0.78rem;">
                    <span x-text="mapType === 'street' ? 'Satelit' : 'Jalan'"></span>
                </button>
                <a href="/admin/perencanaan" class="btn btn-primary" style="padding: 0.4rem 0.85rem; font-size: 0.78rem; text-decoration: none;">
                    Kelola Area
                </a>
            </div>
        </div>

        <div class="admin-dash-map-section">
            <div class="admin-dash-map-wrap">
                <div id="dashboard-map"></div>
                <div class="admin-dash-map-legend">
                    <div class="admin-dash-map-legend-item">
                        <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;"></span> Aktif
                    </div>
                    <div class="admin-dash-map-legend-item">
                        <span style="width:8px;height:8px;border-radius:50%;background:#f59e0b;display:inline-block;"></span> Direncanakan
                    </div>
                    <div class="admin-dash-map-legend-item">
                        <span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;display:inline-block;"></span> Selesai
                    </div>
                </div>
            </div>

            <div class="admin-dash-map-sidebar">
                <template x-for="prog in mappedPrograms" :key="prog.id">
                    <div
                        class="admin-dash-map-sidebar-item"
                        :class="selectedId === prog.id ? 'admin-dash-map-sidebar-item--active' : ''"
                        @click="focusProgram(prog)"
                    >
                        <div class="admin-dash-map-sidebar-title" x-text="prog.nama_program"></div>
                        <div class="admin-dash-map-sidebar-loc">
                            <i data-lucide="map-pin" style="width: 10px; height: 10px;"></i>
                            <span x-text="prog.lokasi || 'Tanpa lokasi'"></span>
                        </div>
                    </div>
                </template>
                <template x-if="mappedPrograms.length === 0">
                    <div class="admin-dash-empty" style="padding: 1.5rem;">
                        <i data-lucide="map-pin-off" style="width: 24px; height: 24px;"></i>
                        Belum ada area terpetakan.
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Quick links --}}
    <div class="admin-dash-quicklinks">
        <a href="/admin/pekerja" class="admin-dash-quicklink">
            <div class="admin-dash-quicklink-icon" style="background: rgba(15,118,110,0.1); color: var(--primary);">
                <i data-lucide="users" style="width: 18px; height: 18px;"></i>
            </div>
            <div>
                <div class="admin-dash-quicklink-title">Data Pekerja</div>
                <div class="admin-dash-quicklink-desc">Kelola profil & keahlian</div>
            </div>
        </a>
        <a href="/admin/keluarga" class="admin-dash-quicklink">
            <div class="admin-dash-quicklink-icon" style="background: rgba(59,130,246,0.1); color: #2563eb;">
                <i data-lucide="home" style="width: 18px; height: 18px;"></i>
            </div>
            <div>
                <div class="admin-dash-quicklink-title">Profiling Keluarga</div>
                <div class="admin-dash-quicklink-desc">Data rumah tangga desa</div>
            </div>
        </a>
        <a href="/admin/ekonomi" class="admin-dash-quicklink">
            <div class="admin-dash-quicklink-icon" style="background: rgba(245,158,11,0.1); color: #d97706;">
                <i data-lucide="dollar-sign" style="width: 18px; height: 18px;"></i>
            </div>
            <div>
                <div class="admin-dash-quicklink-title">Ekonomi & Insentif</div>
                <div class="admin-dash-quicklink-desc">Upah dan reward pekerja</div>
            </div>
        </a>
        <a href="/admin/analisis" class="admin-dash-quicklink">
            <div class="admin-dash-quicklink-icon" style="background: rgba(139,92,246,0.1); color: #7c3aed;">
                <i data-lucide="trending-up" style="width: 18px; height: 18px;"></i>
            </div>
            <div>
                <div class="admin-dash-quicklink-title">Dashboard Analisis</div>
                <div class="admin-dash-quicklink-desc">Laporan dampak program</div>
            </div>
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('bidangKerjaChart', () => ({
            chartData: @json($data['bidang_kerja']),
            init() {
                this.$nextTick(() => this.renderChart());
            },
            renderChart() {
                const canvas = document.getElementById('bidangKerjaChart');
                if (!canvas || typeof Chart === 'undefined') return;

                const breakdown = this.chartData.breakdown || {};
                const labels = this.chartData.labels || [];
                const values = this.chartData.values || [];

                new Chart(canvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels,
                        datasets: [{
                            data: values,
                            backgroundColor: ['#22c55e', '#f59e0b', '#8b5cf6', '#3b82f6', '#94a3b8'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 6,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { padding: 14, usePointStyle: true, pointStyle: 'circle', font: { size: 11 } },
                            },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => {
                                        const label = ctx.label || '';
                                        const val = ctx.parsed || 0;
                                        const total = values.reduce((a, b) => a + b, 0);
                                        const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                                        if (label === 'Lainnya' && Object.keys(breakdown).length > 0) {
                                            const detail = Object.entries(breakdown)
                                                .map(([name, count]) => `${name} (${count})`)
                                                .join(', ');
                                            return [`Lainnya: ${val} (${pct}%)`, `Detail: ${detail}`];
                                        }
                                        return `${label}: ${val} (${pct}%)`;
                                    },
                                },
                            },
                        },
                    },
                });
            },
        }));
    });

    function fixLeafletIcons() {
        if (typeof L === 'undefined') return;
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        });
    }

    function createColoredIcon(color) {
        return L.divIcon({
            className: '',
            html: `<div style="background:${color};width:13px;height:13px;border-radius:50%;border:2.5px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.35);"></div>`,
            iconSize: [13, 13],
            iconAnchor: [6, 6],
        });
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('dashboardMapData', () => ({
            programs: @json($programs),
            mapType: 'street',
            map: null,
            streetLayer: null,
            satelliteLayer: null,
            markers: [],
            markerMap: {},
            selectedId: null,

            get mappedPrograms() {
                return this.programs.filter(p => p.kordinat);
            },

            get mappedCount() {
                return this.mappedPrograms.length;
            },

            init() {
                fixLeafletIcons();
                this.$nextTick(() => setTimeout(() => this.initMap(), 100));
            },

            getMarkerColor(status) {
                if (['active', 'ongoing', 'in_progress'].includes(status)) return '#22c55e';
                if (['completed', 'selesai'].includes(status)) return '#3b82f6';
                return '#f59e0b';
            },

            initMap() {
                if (this.map || typeof L === 'undefined') return;
                const container = document.getElementById('dashboard-map');
                if (!container) return;

                this.map = L.map(container, { scrollWheelZoom: true }).setView([-6.914744, 107.609810], 13);
                this.streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19, attribution: '&copy; OpenStreetMap'
                });
                this.satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 19, attribution: '&copy; Esri'
                });
                this.streetLayer.addTo(this.map);
                this.refreshMarkers();
                setTimeout(() => { if (this.map) this.map.invalidateSize(); }, 450);
            },

            getStatusLabel(status) {
                if (['completed', 'selesai'].includes(status)) return 'Selesai';
                if (['active', 'in_progress', 'ongoing'].includes(status)) return 'Aktif';
                return 'Direncanakan';
            },

            refreshMarkers() {
                if (!this.map) return;
                this.markers.forEach(m => this.map.removeLayer(m));
                this.markers = [];
                this.markerMap = {};

                const bounds = [];
                this.programs.forEach((program) => {
                    if (!program.kordinat) return;
                    const [lat, lng] = program.kordinat.split(',').map(Number);
                    if (isNaN(lat) || isNaN(lng)) return;

                    const color = this.getMarkerColor(program.status);
                    const marker = L.marker([lat, lng], { icon: createColoredIcon(color) })
                        .addTo(this.map)
                        .bindPopup(`<strong>${program.nama_program}</strong><br/>${program.lokasi || '-'}<br/><em>${this.getStatusLabel(program.status)}</em>`);
                    this.markers.push(marker);
                    this.markerMap[program.id] = marker;
                    bounds.push([lat, lng]);
                });

                if (bounds.length > 0) {
                    this.map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
                }
            },

            focusProgram(prog) {
                this.selectedId = prog.id;
                if (!prog.kordinat || !this.map) return;
                const [lat, lng] = prog.kordinat.split(',').map(Number);
                if (isNaN(lat) || isNaN(lng)) return;
                this.map.flyTo([lat, lng], 16, { duration: 1 });
                const marker = this.markerMap[prog.id];
                if (marker) setTimeout(() => marker.openPopup(), 600);
            },

            toggleMapType() {
                if (!this.map) return;
                if (this.mapType === 'street') {
                    this.map.removeLayer(this.streetLayer);
                    this.satelliteLayer.addTo(this.map);
                    this.mapType = 'satellite';
                } else {
                    this.map.removeLayer(this.satelliteLayer);
                    this.streetLayer.addTo(this.map);
                    this.mapType = 'street';
                }
            },
        }));
    });
</script>
@endsection
