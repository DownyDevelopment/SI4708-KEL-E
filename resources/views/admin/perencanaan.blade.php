@extends('layouts.app')
@section('title', 'Perencanaan Program & Area')

@php
    $totalPrograms = $programs->count();
    $activeCount = $programs->whereIn('status', ['active', 'ongoing', 'in_progress'])->count();
    $plannedCount = $programs->where('status', 'planned')->count();
    $completedCount = $programs->whereIn('status', ['completed', 'selesai'])->count();
    $mappedCount = $programs->filter(fn ($p) => !empty($p->kordinat))->count();
    $jenisList = $programs->pluck('jenis_program')->filter()->unique()->values();
@endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .perencanaan-page {
        padding: 2rem;
        max-width: 1440px;
        margin: 0 auto;
    }

    .perencanaan-hero {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1.5rem;
        margin-bottom: 1.75rem;
        flex-wrap: wrap;
    }

    .perencanaan-hero h1 {
        font-size: 1.85rem;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 0.35rem;
    }

    .perencanaan-hero p {
        color: var(--text-muted);
        font-size: 0.95rem;
        max-width: 560px;
        margin: 0;
    }

    .perencanaan-hero-actions {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
    }

    .perencanaan-stats {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 0.85rem;
        margin-bottom: 1.5rem;
    }

    @media (max-width: 1100px) {
        .perencanaan-stats { grid-template-columns: repeat(3, 1fr); }
    }

    @media (max-width: 640px) {
        .perencanaan-stats { grid-template-columns: repeat(2, 1fr); }
    }

    .perencanaan-stat {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .perencanaan-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    }

    .perencanaan-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .perencanaan-stat-value {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--text-main);
        line-height: 1.1;
    }

    .perencanaan-stat-label {
        font-size: 0.72rem;
        color: var(--text-muted);
        margin-top: 0.05rem;
    }

    .perencanaan-info-banner {
        display: flex;
        gap: 0.85rem;
        padding: 1rem 1.15rem;
        margin-bottom: 1.5rem;
        background: linear-gradient(135deg, rgba(15, 118, 110, 0.06) 0%, rgba(59, 130, 246, 0.04) 100%);
        border: 1px solid rgba(15, 118, 110, 0.18);
        border-radius: var(--radius-md);
    }

    .perencanaan-info-banner-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(15, 118, 110, 0.12);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .perencanaan-info-banner strong {
        display: block;
        font-size: 0.88rem;
        color: var(--text-main);
        margin-bottom: 0.25rem;
    }

    .perencanaan-info-banner p {
        margin: 0;
        font-size: 0.82rem;
        line-height: 1.55;
        color: var(--text-muted);
    }

    .perencanaan-alert {
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

    /* Map + sidebar layout */
    .perencanaan-map-section {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    @media (max-width: 960px) {
        .perencanaan-map-section { grid-template-columns: 1fr; }
    }

    .perencanaan-map-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .perencanaan-map-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .perencanaan-map-header h2 {
        font-size: 0.95rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .perencanaan-map-controls {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .perencanaan-map-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.75rem;
        font-size: 0.78rem;
        font-weight: 500;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--background);
        color: var(--text-main);
        cursor: pointer;
        transition: all 0.15s;
    }

    .perencanaan-map-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .perencanaan-map-btn--active {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .perencanaan-map-btn--danger {
        background: #dc2626;
        border-color: #dc2626;
        color: white;
    }

    .perencanaan-map-wrap {
        position: relative;
        height: 420px;
        background: #e2e8f0;
    }

    .perencanaan-map-wrap--adding {
        outline: 3px dashed var(--primary);
        outline-offset: -3px;
    }

    .perencanaan-map-wrap #perencanaan-map {
        height: 100%;
        width: 100%;
        z-index: 0;
    }

    .perencanaan-map-hint {
        position: absolute;
        bottom: 1rem;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(15, 118, 110, 0.92);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 99px;
        font-size: 0.78rem;
        z-index: 500;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        pointer-events: none;
    }

    .perencanaan-map-legend {
        position: absolute;
        top: 0.75rem;
        left: 0.75rem;
        background: white;
        border-radius: 10px;
        padding: 0.6rem 0.85rem;
        font-size: 0.72rem;
        z-index: 500;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        border: 1px solid var(--border);
    }

    .perencanaan-map-legend-item {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.25rem;
    }

    .perencanaan-map-legend-item:last-child { margin-bottom: 0; }

    .perencanaan-map-legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    /* Sidebar program list on map */
    .perencanaan-map-sidebar {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        max-height: 480px;
    }

    .perencanaan-map-sidebar-header {
        padding: 1rem 1.15rem;
        border-bottom: 1px solid var(--border);
        background: var(--background);
    }

    .perencanaan-map-sidebar-header h3 {
        font-size: 0.88rem;
        font-weight: 600;
        margin: 0 0 0.15rem;
    }

    .perencanaan-map-sidebar-header p {
        font-size: 0.72rem;
        color: var(--text-muted);
        margin: 0;
    }

    .perencanaan-map-sidebar-list {
        flex: 1;
        overflow-y: auto;
        padding: 0.5rem;
    }

    .perencanaan-map-sidebar-item {
        padding: 0.75rem 0.85rem;
        border-radius: 10px;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all 0.15s;
        margin-bottom: 0.35rem;
    }

    .perencanaan-map-sidebar-item:hover {
        background: var(--background);
        border-color: var(--border);
    }

    .perencanaan-map-sidebar-item--selected {
        background: rgba(15, 118, 110, 0.08);
        border-color: rgba(15, 118, 110, 0.3);
    }

    .perencanaan-map-sidebar-item-title {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 0.2rem;
        line-height: 1.3;
    }

    .perencanaan-map-sidebar-item-meta {
        font-size: 0.72rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    /* Program catalog */
    .perencanaan-catalog {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        overflow: hidden;
    }

    .perencanaan-catalog-header {
        padding: 1.15rem 1.35rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .perencanaan-catalog-header h2 {
        font-size: 1.05rem;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .perencanaan-catalog-header p {
        margin: 0.2rem 0 0;
        font-size: 0.78rem;
        color: var(--text-muted);
    }

    .perencanaan-toolbar {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
        align-items: center;
        padding: 0.85rem 1.35rem;
        background: var(--background);
        border-bottom: 1px solid var(--border);
    }

    .perencanaan-search {
        position: relative;
        flex: 1;
        min-width: 200px;
        max-width: 320px;
    }

    .perencanaan-search i {
        position: absolute;
        left: 0.8rem;
        top: 50%;
        transform: translateY(-50%);
        width: 15px;
        height: 15px;
        color: var(--text-muted);
        pointer-events: none;
    }

    .perencanaan-search input {
        width: 100%;
        padding: 0.55rem 0.85rem 0.55rem 2.2rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 0.85rem;
        background: var(--surface);
        color: var(--text-main);
        outline: none;
        transition: border-color 0.15s;
    }

    .perencanaan-search input:focus {
        border-color: var(--primary);
    }

    .perencanaan-filter-tabs {
        display: flex;
        gap: 0.35rem;
        flex-wrap: wrap;
    }

    .perencanaan-filter-tab {
        padding: 0.4rem 0.75rem;
        font-size: 0.78rem;
        font-weight: 500;
        border-radius: 99px;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.15s;
    }

    .perencanaan-filter-tab:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .perencanaan-filter-tab--active {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .perencanaan-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 1.25rem;
        padding: 1.35rem;
    }

    .perencanaan-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.35rem;
        display: flex;
        flex-direction: column;
        transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
        cursor: pointer;
    }

    .perencanaan-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
        border-color: rgba(15, 118, 110, 0.25);
    }

    .perencanaan-card--selected {
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(15, 118, 110, 0.15);
    }

    .perencanaan-card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 0.85rem;
    }

    .perencanaan-card-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }

    .perencanaan-badge {
        padding: 3px 9px;
        border-radius: 99px;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .perencanaan-badge--jenis {
        background: rgba(15, 118, 110, 0.1);
        color: var(--primary);
    }

    .perencanaan-badge--planned {
        background: rgba(245, 158, 11, 0.12);
        color: #b45309;
    }

    .perencanaan-badge--active {
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
    }

    .perencanaan-badge--completed {
        background: rgba(59, 130, 246, 0.12);
        color: #1d4ed8;
    }

    .perencanaan-card-actions {
        display: flex;
        gap: 0.2rem;
        background: var(--background);
        padding: 3px;
        border-radius: 8px;
        flex-shrink: 0;
    }

    .perencanaan-card-actions button {
        background: transparent;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 5px;
        border-radius: 6px;
        display: flex;
        transition: background 0.15s;
    }

    .perencanaan-card-actions button:hover {
        background: rgba(0, 0, 0, 0.05);
    }

    .perencanaan-card-actions button.danger:hover {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

    .perencanaan-card-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-main);
        margin: 0 0 0.5rem;
        line-height: 1.35;
    }

    .perencanaan-card-desc {
        font-size: 0.85rem;
        color: var(--text-muted);
        line-height: 1.55;
        margin-bottom: 1rem;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .perencanaan-progress {
        margin-bottom: 1rem;
    }

    .perencanaan-progress-label {
        display: flex;
        justify-content: space-between;
        font-size: 0.72rem;
        color: var(--text-muted);
        margin-bottom: 0.35rem;
    }

    .perencanaan-progress-bar {
        height: 5px;
        background: var(--border);
        border-radius: 99px;
        overflow: hidden;
    }

    .perencanaan-progress-fill {
        height: 100%;
        border-radius: 99px;
        background: linear-gradient(90deg, var(--primary), #0d9488);
        transition: width 0.4s ease;
    }

    .perencanaan-card-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.65rem;
        padding: 0.85rem;
        background: var(--background);
        border-radius: 10px;
        border: 1px solid var(--border);
        font-size: 0.8rem;
    }

    .perencanaan-meta-item {
        display: flex;
        align-items: flex-start;
        gap: 0.4rem;
    }

    .perencanaan-meta-item i {
        width: 14px;
        height: 14px;
        color: var(--primary);
        flex-shrink: 0;
        margin-top: 2px;
    }

    .perencanaan-meta-label {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-muted);
        margin-bottom: 0.1rem;
    }

    .perencanaan-stakeholders {
        margin-top: 0.85rem;
        padding-top: 0.85rem;
        border-top: 1px dashed var(--border);
    }

    .perencanaan-stakeholders-label {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-muted);
        margin-bottom: 0.45rem;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .perencanaan-stakeholder-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }

    .perencanaan-stakeholder-tag {
        background: white;
        border: 1px solid var(--border);
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .perencanaan-stakeholder-tag strong {
        color: var(--primary);
        font-weight: 600;
    }

    .perencanaan-stakeholder-tag span {
        color: var(--text-muted);
        font-size: 0.68rem;
        padding-left: 4px;
        border-left: 1px solid var(--border);
    }

    .perencanaan-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-muted);
    }

    .perencanaan-empty-icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 1rem;
        background: var(--background);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px dashed var(--border);
    }

    .perencanaan-empty h3 {
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--text-main);
        margin: 0 0 0.35rem;
    }

    .perencanaan-empty p {
        font-size: 0.85rem;
        margin: 0 0 1.25rem;
    }

    /* Modal */
    .perencanaan-form-panel {
        width: 100%;
        max-width: 780px;
        max-height: 90vh;
        overflow-y: auto;
        background: var(--surface);
        border-radius: var(--radius-md);
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.18);
    }

    .perencanaan-form-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.15rem 1.5rem;
        background: linear-gradient(135deg, var(--primary) 0%, #0d9488 100%);
        color: white;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .perencanaan-form-header h2 {
        margin: 0;
        font-size: 1.05rem;
        color: white;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .perencanaan-form-header p {
        margin: 0.2rem 0 0;
        font-size: 0.78rem;
        color: rgba(255, 255, 255, 0.85);
    }

    .perencanaan-form-close {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.1);
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s;
        flex-shrink: 0;
    }

    .perencanaan-form-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .perencanaan-form-body {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .perencanaan-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.15rem;
    }

    @media (max-width: 640px) {
        .perencanaan-form-grid { grid-template-columns: 1fr; }
        .perencanaan-grid { grid-template-columns: 1fr; }
    }

    .perencanaan-form-section {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 0.85rem;
        background: var(--background);
        border-radius: var(--radius-sm);
        border-left: 3px solid var(--primary);
    }

    .perencanaan-form-section span {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--primary);
    }

    .perencanaan-stakeholder-box {
        grid-column: 1 / -1;
        background: rgba(15, 118, 110, 0.03);
        padding: 1.25rem;
        border-radius: 12px;
        border: 1px solid rgba(15, 118, 110, 0.12);
    }

    .perencanaan-stakeholder-inputs {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 0.85rem;
        flex-wrap: wrap;
    }

    .perencanaan-stakeholder-inputs > * {
        flex: 1;
        min-width: 140px;
    }

    .perencanaan-form-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
    }
</style>
@endpush

@section('content')
<div class="perencanaan-page animate-fade-in" x-data="{ activeTab: new URLSearchParams(window.location.search).get('tab') || 'program', updateUrl(tab) { window.history.replaceState(null, null, '?tab=' + tab); setTimeout(() => lucide.createIcons(), 50); window.dispatchEvent(new CustomEvent('tab-changed', { detail: tab })); } }" x-init="setTimeout(() => { window.dispatchEvent(new CustomEvent('tab-changed', { detail: activeTab })); }, 150)">

    <x-hero-banner title="Program & Operasional Desa" description="Perencanaan program kerja desa, penugasan pekerja, dan penjadwalan aktivitas operasional lapangan.">
        <x-slot:actions>
            <template x-if="activeTab === 'program'">
                <button @click="window.dispatchEvent(new CustomEvent('open-add-program-form'))" class="global-hero-banner-btn-white" style="display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="plus" style="width: 18px; height: 18px;"></i>
                    Tambah Program
                </button>
            </template>
            <template x-if="activeTab === 'tugas'">
                <button @click="window.dispatchEvent(new CustomEvent('open-add-tugas-form'))" class="global-hero-banner-btn-white" style="display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="plus" style="width: 18px; height: 18px;"></i>
                    Tambah Jadwal
                </button>
            </template>
        </x-slot:actions>
    </x-hero-banner>

    <div class="global-tabs">
        <button type="button" class="global-tab" :class="{ 'active': activeTab === 'program' }" @click="activeTab = 'program'; updateUrl('program')">
            <i data-lucide="map-pin" style="width: 16px; height: 16px;"></i>
            Program Kerja
        </button>
        <button type="button" class="global-tab" :class="{ 'active': activeTab === 'tugas' }" @click="activeTab = 'tugas'; updateUrl('tugas')">
            <i data-lucide="file-text" style="width: 16px; height: 16px;"></i>
            Penjadwalan Tugas
        </button>
    </div>

    <!-- TAB 1: Program Kerja -->
    <div x-show="activeTab === 'program'" x-data="perencanaanData()" x-init="window.addEventListener('open-add-program-form', () => { resetForm(); showModal = true; })" x-on:tab-changed.window="if ($event.detail === 'program') { $nextTick(() => { if (map) map.invalidateSize(); else initMap(); }) }">

    {{-- Stats --}}
    <div class="perencanaan-stats">
        <div class="perencanaan-stat">
            <div class="perencanaan-stat-icon" style="background: rgba(15, 118, 110, 0.1); color: var(--primary);">
                <i data-lucide="layers" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div class="perencanaan-stat-value">{{ $totalPrograms }}</div>
                <div class="perencanaan-stat-label">Total Program</div>
            </div>
        </div>
        <div class="perencanaan-stat">
            <div class="perencanaan-stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #16a34a;">
                <i data-lucide="activity" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div class="perencanaan-stat-value">{{ $activeCount }}</div>
                <div class="perencanaan-stat-label">Aktif Berjalan</div>
            </div>
        </div>
        <div class="perencanaan-stat">
            <div class="perencanaan-stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #d97706;">
                <i data-lucide="clock" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div class="perencanaan-stat-value">{{ $plannedCount }}</div>
                <div class="perencanaan-stat-label">Direncanakan</div>
            </div>
        </div>
        <div class="perencanaan-stat">
            <div class="perencanaan-stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #2563eb;">
                <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div class="perencanaan-stat-value">{{ $completedCount }}</div>
                <div class="perencanaan-stat-label">Selesai</div>
            </div>
        </div>
        <div class="perencanaan-stat">
            <div class="perencanaan-stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #7c3aed;">
                <i data-lucide="map-pin" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div class="perencanaan-stat-value">{{ $mappedCount }}</div>
                <div class="perencanaan-stat-label">Area Terpetakan</div>
            </div>
        </div>
    </div>

    {{-- Info banner --}}
    <div class="perencanaan-info-banner">
        <div class="perencanaan-info-banner-icon">
            <i data-lucide="users" style="width: 18px; height: 18px;"></i>
        </div>
        <div>
            <strong>Koordinasi Multi-Stakeholder</strong>
            <p>Tambahkan pemerintah desa, LSM, sponsor swasta, dan tokoh masyarakat ke setiap program. Stakeholder akan mendapat notifikasi otomatis saat program dibuat atau diperbarui.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="perencanaan-alert">
            <i data-lucide="check-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Map section --}}
    <div class="perencanaan-map-section">
        <div class="perencanaan-map-panel">
            <div class="perencanaan-map-header">
                <h2>
                    <i data-lucide="map" style="width: 18px; height: 18px; color: var(--primary);"></i>
                    Peta Area Kerja
                </h2>
                <div class="perencanaan-map-controls">
                    <button class="perencanaan-map-btn" @click="toggleMapType">
                        <i data-lucide="satellite" style="width: 14px; height: 14px;"></i>
                        <span x-text="mapType === 'street' ? 'Satelit' : 'Jalan'"></span>
                    </button>
                    <button
                        class="perencanaan-map-btn"
                        :class="isAddingMode ? 'perencanaan-map-btn--danger' : 'perencanaan-map-btn--active'"
                        @click="isAddingMode = !isAddingMode"
                    >
                        <i :data-lucide="isAddingMode ? 'x' : 'crosshair'" style="width: 14px; height: 14px;"></i>
                        <span x-text="isAddingMode ? 'Batal' : 'Tambah Titik'"></span>
                    </button>
                    <button class="perencanaan-map-btn" @click="fitAllMarkers()" x-show="programs.length > 0">
                        <i data-lucide="maximize-2" style="width: 14px; height: 14px;"></i>
                        Semua Area
                    </button>
                </div>
            </div>
            <div class="perencanaan-map-wrap" :class="isAddingMode ? 'perencanaan-map-wrap--adding' : ''">
                <div id="perencanaan-map"></div>
                <div class="perencanaan-map-legend">
                    <div class="perencanaan-map-legend-item">
                        <div class="perencanaan-map-legend-dot" style="background: #22c55e;"></div>
                        Aktif
                    </div>
                    <div class="perencanaan-map-legend-item">
                        <div class="perencanaan-map-legend-dot" style="background: #f59e0b;"></div>
                        Direncanakan
                    </div>
                    <div class="perencanaan-map-legend-item">
                        <div class="perencanaan-map-legend-dot" style="background: #3b82f6;"></div>
                        Selesai
                    </div>
                </div>
                <div x-show="isAddingMode" x-cloak class="perencanaan-map-hint">
                    <i data-lucide="mouse-pointer-click" style="width: 14px; height: 14px;"></i>
                    Klik peta untuk menambahkan titik program baru
                </div>
            </div>
        </div>

        <div class="perencanaan-map-sidebar">
            <div class="perencanaan-map-sidebar-header">
                <h3>Lokasi Program</h3>
                <p x-text="mappedPrograms.length + ' dari ' + programs.length + ' program terpetakan'"></p>
            </div>
            <div class="perencanaan-map-sidebar-list">
                <template x-for="prog in mappedPrograms" :key="'map-' + prog.id">
                    <div
                        class="perencanaan-map-sidebar-item"
                        :class="selectedProgramId === prog.id ? 'perencanaan-map-sidebar-item--selected' : ''"
                        @click="focusProgram(prog)"
                    >
                        <div class="perencanaan-map-sidebar-item-title" x-text="prog.nama_program"></div>
                        <div class="perencanaan-map-sidebar-item-meta">
                            <i data-lucide="map-pin" style="width: 11px; height: 11px;"></i>
                            <span x-text="prog.lokasi || 'Tanpa lokasi'"></span>
                        </div>
                    </div>
                </template>
                <template x-if="mappedPrograms.length === 0">
                    <div style="padding: 2rem 1rem; text-align: center; color: var(--text-muted); font-size: 0.82rem;">
                        <i data-lucide="map-pin-off" style="width: 28px; height: 28px; margin: 0 auto 0.5rem; display: block; opacity: 0.4;"></i>
                        Belum ada program dengan koordinat peta.
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Program catalog --}}
    <div class="perencanaan-catalog">
        <div class="perencanaan-catalog-header">
            <div>
                <h2>
                    <i data-lucide="briefcase" style="width: 20px; height: 20px; color: var(--primary);"></i>
                    Daftar Program Kerja
                </h2>
                <p x-text="filteredPrograms.length + ' program ditampilkan'"></p>
            </div>
        </div>

        <div class="perencanaan-toolbar">
            <div class="perencanaan-search">
                <i data-lucide="search"></i>
                <input type="text" placeholder="Cari nama program, lokasi, jenis..." x-model="searchQuery" />
            </div>
            <div class="perencanaan-filter-tabs">
                <button class="perencanaan-filter-tab" :class="statusFilter === 'all' ? 'perencanaan-filter-tab--active' : ''" @click="statusFilter = 'all'">Semua</button>
                <button class="perencanaan-filter-tab" :class="statusFilter === 'planned' ? 'perencanaan-filter-tab--active' : ''" @click="statusFilter = 'planned'">Direncanakan</button>
                <button class="perencanaan-filter-tab" :class="statusFilter === 'active' ? 'perencanaan-filter-tab--active' : ''" @click="statusFilter = 'active'">Aktif</button>
                <button class="perencanaan-filter-tab" :class="statusFilter === 'completed' ? 'perencanaan-filter-tab--active' : ''" @click="statusFilter = 'completed'">Selesai</button>
            </div>
        </div>

        <div class="perencanaan-grid">
            <template x-for="prog in filteredPrograms" :key="prog.id">
                <div
                    class="perencanaan-card"
                    :class="selectedProgramId === prog.id ? 'perencanaan-card--selected' : ''"
                    @click="focusProgram(prog)"
                >
                    <div class="perencanaan-card-top">
                        <div class="perencanaan-card-badges">
                            <span class="perencanaan-badge perencanaan-badge--jenis" x-text="prog.jenis_program"></span>
                            <span
                                class="perencanaan-badge"
                                :class="{
                                    'perencanaan-badge--active': ['active','ongoing','in_progress'].includes(prog.status),
                                    'perencanaan-badge--completed': ['completed','selesai'].includes(prog.status),
                                    'perencanaan-badge--planned': !['active','ongoing','in_progress','completed','selesai'].includes(prog.status)
                                }"
                                x-text="getStatusLabel(prog.status)"
                            ></span>
                        </div>
                        <div class="perencanaan-card-actions" @click.stop>
                            <button @click="handleEdit(prog)" title="Edit">
                                <i data-lucide="edit-2" style="width: 15px; height: 15px;"></i>
                            </button>
                            <form method="POST" :action="'/admin/perencanaan/' + prog.id" style="margin: 0; display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus program ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="danger" title="Hapus">
                                    <i data-lucide="trash-2" style="width: 15px; height: 15px;"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <h3 class="perencanaan-card-title" x-text="prog.nama_program"></h3>
                    <p class="perencanaan-card-desc" x-text="prog.deskripsi || 'Tidak ada deskripsi.'"></p>

                    <div class="perencanaan-progress" x-show="prog.tanggal_mulai && prog.tanggal_selesai">
                        <div class="perencanaan-progress-label">
                            <span>Progress Periode</span>
                            <span x-text="getProgressPercent(prog) + '%'"></span>
                        </div>
                        <div class="perencanaan-progress-bar">
                            <div class="perencanaan-progress-fill" :style="'width:' + getProgressPercent(prog) + '%'"></div>
                        </div>
                    </div>

                    <div class="perencanaan-card-meta">
                        <div class="perencanaan-meta-item">
                            <i data-lucide="map-pin"></i>
                            <div>
                                <div class="perencanaan-meta-label">Lokasi</div>
                                <div x-text="prog.lokasi || '-'"></div>
                            </div>
                        </div>
                        <div class="perencanaan-meta-item">
                            <i data-lucide="home"></i>
                            <div>
                                <div class="perencanaan-meta-label">Desa Kerja</div>
                                <div x-text="prog.desa_lokasi || '-'"></div>
                            </div>
                        </div>
                        <div class="perencanaan-meta-item">
                            <i data-lucide="calendar"></i>
                            <div>
                                <div class="perencanaan-meta-label">Periode</div>
                                <div><span x-text="formatDate(prog.tanggal_mulai)"></span> – <span x-text="formatDate(prog.tanggal_selesai)"></span></div>
                            </div>
                        </div>
                        <div class="perencanaan-meta-item">
                            <i data-lucide="wrench"></i>
                            <div>
                                <div class="perencanaan-meta-label">Sektor</div>
                                <div x-text="prog.sektor_keahlian || '-'"></div>
                            </div>
                        </div>
                    </div>

                    <div class="perencanaan-stakeholders" x-show="parseStakeholders(prog.stakeholders).length > 0">
                        <div class="perencanaan-stakeholders-label">
                            <i data-lucide="users" style="width: 13px; height: 13px;"></i>
                            Stakeholder
                        </div>
                        <div class="perencanaan-stakeholder-tags">
                            <template x-for="(st, i) in parseStakeholders(prog.stakeholders).slice(0, 3)" :key="i">
                                <div class="perencanaan-stakeholder-tag">
                                    <strong x-text="st.nama"></strong>
                                    <span x-text="st.peran"></span>
                                </div>
                            </template>
                            <template x-if="parseStakeholders(prog.stakeholders).length > 3">
                                <div class="perencanaan-stakeholder-tag" style="color: var(--text-muted);">
                                    +<span x-text="parseStakeholders(prog.stakeholders).length - 3"></span> lainnya
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="filteredPrograms.length === 0">
                <div class="perencanaan-empty">
                    <div class="perencanaan-empty-icon">
                        <i data-lucide="target" style="width: 28px; height: 28px; color: var(--text-muted);"></i>
                    </div>
                    <h3 x-text="programs.length === 0 ? 'Belum Ada Program' : 'Tidak Ditemukan'"></h3>
                    <p x-text="programs.length === 0 ? 'Mulai perencanaan dengan menambahkan program kerja dan area fokus desa.' : 'Coba ubah kata kunci pencarian atau filter status.'"></p>
                    <button x-show="programs.length === 0" @click="resetForm(); showModal = true" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                        <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                        Tambah Program Pertama
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- Modal --}}
    <div x-show="showModal" x-cloak class="modal-overlay modal-overlay--blur" style="padding: 1rem;" @keydown.escape.window="showModal = false">
        <div class="perencanaan-form-panel animate-fade-in" @click.outside="showModal = false">
            <div class="perencanaan-form-header">
                <div>
                    <h2>
                        <i data-lucide="briefcase" style="width: 20px; height: 20px;"></i>
                        <span x-text="editingId ? 'Edit Program Kerja' : 'Program Baru'"></span>
                    </h2>
                    <p x-text="editingId ? 'Perbarui detail program dan koordinasi stakeholder.' : 'Rencanakan program kerja mikro dengan area dan stakeholder.'"></p>
                </div>
                <button @click="showModal = false" class="perencanaan-form-close">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>

            <form method="POST" :action="editingId ? '/admin/perencanaan/' + editingId : '/admin/perencanaan'" class="perencanaan-form-body">
                @csrf
                <template x-if="editingId">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="stakeholders" :value="JSON.stringify(stakeholders)">

                <div class="perencanaan-form-section">
                    <i data-lucide="info" style="width: 14px; height: 14px; color: var(--primary);"></i>
                    <span>Informasi Program</span>
                </div>

                <div class="perencanaan-form-grid">
                    <div>
                        <label class="form-label">Nama Program</label>
                        <input type="text" class="form-input" name="nama_program" x-model="formData.nama_program" required placeholder="Contoh: Pemberdayaan Petani Sayur" style="width: 100%;" />
                    </div>
                    <div>
                        <label class="form-label">Kategori / Jenis</label>
                        <input type="text" class="form-input" name="jenis_program" x-model="formData.jenis_program" required placeholder="Contoh: Pertanian" style="width: 100%;" list="jenis-suggestions" />
                        <datalist id="jenis-suggestions">
                            @foreach($jenisList as $jenis)
                                <option value="{{ $jenis }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <label class="form-label">Deskripsi Lengkap</label>
                        <textarea class="form-input" name="deskripsi" x-model="formData.deskripsi" rows="3" placeholder="Jelaskan tujuan dan ruang lingkup program..." style="width: 100%; resize: vertical;"></textarea>
                    </div>
                </div>

                <div class="perencanaan-form-section">
                    <i data-lucide="map-pin" style="width: 14px; height: 14px; color: var(--primary);"></i>
                    <span>Lokasi & Area</span>
                </div>

                <div class="perencanaan-form-grid">
                    <div>
                        <label class="form-label">Desa Lokasi Kerja</label>
                        <input type="text" class="form-input" name="desa_lokasi" x-model="formData.desa_lokasi" placeholder="Desa tempat pekerjaan dilaksanakan" style="width: 100%;" />
                    </div>
                    <div>
                        <label class="form-label">Sektor Keahlian</label>
                        <input type="text" class="form-input" name="sektor_keahlian" x-model="formData.sektor_keahlian" placeholder="Contoh: Pertanian, Lingkungan" style="width: 100%;" />
                    </div>
                    <div>
                        <label class="form-label">Area / Lokasi Fokus</label>
                        <input type="text" class="form-input" name="lokasi" x-model="formData.lokasi" placeholder="Contoh: Dusun Mawar, RT 01" style="width: 100%;" />
                    </div>
                    <div>
                        <label class="form-label">Koordinat (Lat, Long)</label>
                        <input type="text" class="form-input" name="kordinat" x-model="formData.kordinat" placeholder="-6.914744, 107.609810" style="width: 100%; font-family: monospace; font-size: 0.85rem;" />
                    </div>
                </div>

                <div class="perencanaan-form-section">
                    <i data-lucide="users" style="width: 14px; height: 14px; color: var(--primary);"></i>
                    <span>Koordinasi Stakeholder</span>
                </div>

                <div class="perencanaan-stakeholder-box">
                    <div class="perencanaan-stakeholder-inputs">
                        <input type="text" class="form-input" x-model="shNama" placeholder="Nama institusi / tokoh" style="width: 100%;" />
                        <select class="form-input" x-model="shPeran" style="width: 100%;">
                            <option value="">-- Pilih Peran --</option>
                            <option value="Pemerintah Desa">Pemerintah Desa</option>
                            <option value="LSM / Komunitas">LSM / Komunitas</option>
                            <option value="Swasta / Sponsor">Swasta / Sponsor</option>
                            <option value="Relawan">Relawan</option>
                            <option value="Tokoh Masyarakat">Tokoh Masyarakat</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                        <button type="button" @click="handleAddStakeholder" class="btn btn-secondary" style="white-space: nowrap; flex: 0;">Tambah</button>
                    </div>
                    <div class="perencanaan-stakeholder-tags" style="min-height: 36px;">
                        <template x-for="(st, i) in stakeholders" :key="i">
                            <div class="perencanaan-stakeholder-tag" style="border-color: var(--primary); padding: 5px 10px;">
                                <strong x-text="st.nama"></strong>
                                <span x-text="st.peran"></span>
                                <button type="button" @click="handleRemoveStakeholder(i)" style="background: var(--danger); border: none; color: white; cursor: pointer; display: flex; padding: 2px; border-radius: 50%; margin-left: 4px;">
                                    <i data-lucide="x" style="width: 10px; height: 10px;"></i>
                                </button>
                            </div>
                        </template>
                        <template x-if="stakeholders.length === 0">
                            <span style="font-size: 0.85rem; color: var(--text-muted);">Belum ada stakeholder. Tambahkan melalui form di atas.</span>
                        </template>
                    </div>
                </div>

                <div class="perencanaan-form-section">
                    <i data-lucide="calendar" style="width: 14px; height: 14px; color: var(--primary);"></i>
                    <span>Jadwal & Status</span>
                </div>

                <div class="perencanaan-form-grid">
                    <div>
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-input" name="tanggal_mulai" x-model="formData.tanggal_mulai" style="width: 100%;" />
                    </div>
                    <div>
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" class="form-input" name="tanggal_selesai" x-model="formData.tanggal_selesai" style="width: 100%;" />
                    </div>
                    <div>
                        <label class="form-label">Status Program</label>
                        <select class="form-input" name="status" x-model="formData.status" style="width: 100%;">
                            <option value="planned">Direncanakan</option>
                            <option value="active">Aktif Berjalan</option>
                            <option value="completed">Selesai</option>
                        </select>
                    </div>
                </div>

                <div class="perencanaan-form-footer">
                    <button type="button" @click="showModal = false" class="btn btn-outline">Batal</button>
                    <button type="submit" class="btn btn-primary" x-text="editingId ? 'Simpan Perubahan' : 'Buat Program'"></button>
                </div>
            </form>
        </div>
    </div>
    </div>

    <!-- TAB 2: Penjadwalan Tugas -->
    <div x-show="activeTab === 'tugas'" x-data="tugasData()" x-init="window.addEventListener('open-add-tugas-form', () => openCreateModal())">
        @if(session('success') && request()->get('tab') === 'tugas')
            <div class="perencanaan-alert">
                <i data-lucide="check-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="glass-panel" style="margin-bottom: 1rem; padding: 0.85rem 1.25rem; border-left: 4px solid var(--danger); color: var(--danger); background: var(--surface);">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="glass-panel" style="margin-bottom: 1rem; padding: 0.85rem 1.25rem; border-left: 4px solid var(--danger); color: var(--danger); background: var(--surface);">
                @foreach($errors->all() as $error)
                    <p style="margin:0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="perencanaan-table-panel" style="padding: 1.5rem; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); margin-top: 1.5rem;">
            <div class="perencanaan-table-header" style="border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 1rem;">
                <div>
                    <h2 style="font-size: 1.1rem; font-weight: 600; color: var(--text-main); margin: 0;">Daftar Penjadwalan Tugas</h2>
                    <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0.2rem 0 0;">Kelola jadwal operasional harian kelompok kerja di lapangan</p>
                </div>
            </div>
            <div style="overflow-x: auto;">
                <table class="perencanaan-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                            <th style="padding: 0.75rem;">Program</th>
                            <th style="padding: 0.75rem;">Tanggal</th>
                            <th style="padding: 0.75rem;">Shift / Jam</th>
                            <th style="padding: 0.75rem;">Kelompok</th>
                            <th style="padding: 0.75rem;">Progres</th>
                            <th style="padding: 0.75rem;">Status</th>
                            <th style="padding: 0.75rem;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwal as $item)
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 0.75rem; font-weight: 600; color: var(--text-main);">{{ $item->tugas ?? '—' }}</td>
                                <td style="padding: 0.75rem;">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d M Y') : '—' }}</td>
                                <td style="padding: 0.75rem; font-size: 0.85rem;">
                                    {{ $item->shift_label ?? '—' }}
                                    @if($item->jam_mulai)
                                        <br><span style="color: var(--text-muted);">{{ $item->jam_mulai }}@if($item->jam_selesai) – {{ $item->jam_selesai }}@endif</span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem; font-size: 0.85rem;">
                                    @if($item->kelompok_nama)
                                        <strong>{{ $item->kelompok_nama }}</strong>
                                        @if(!empty($item->pekerja_nama))
                                            <br><span style="color: var(--text-muted);">{{ implode(', ', array_slice($item->pekerja_nama, 0, 2)) }}@if(count($item->pekerja_nama) > 2) +{{ count($item->pekerja_nama) - 2 }}@endif</span>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td style="padding: 0.75rem; min-width: 120px;">
                                    <div class="progress-track">
                                        <div class="progress-fill" style="width: {{ min(100, (int)($item->progres_terakhir ?? 0)) }}%;"></div>
                                    </div>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);">{{ (int)($item->progres_terakhir ?? 0) }}%</span>
                                </td>
                                <td style="padding: 0.75rem;">
                                    @php
                                        $statusClass = match($item->status) {
                                            'completed' => 'badge-success',
                                            'in_progress' => 'badge-primary',
                                            default => 'badge-warning',
                                        };
                                        $statusLabel = match($item->status) {
                                            'completed' => 'Selesai',
                                            'in_progress' => 'Berjalan',
                                            'scheduled' => 'Terjadwal',
                                            default => $item->status,
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <div style="display: flex; gap: 0.35rem;">
                                        <button type="button" class="perencanaan-action-btn" style="background: rgba(15, 118, 110, 0.08); color: var(--primary);" @click="openEditModal(@js($item))">
                                            <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                                        </button>
                                        <form method="POST" action="{{ url('/admin/tugas/' . $item->id) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="perencanaan-action-btn" style="background: rgba(239, 68, 68, 0.08); color: var(--danger);">
                                                <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 1.5rem; text-align: center; color: var(--text-muted);">Belum ada jadwal.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal Input Jadwal --}}
        <div class="perencanaan-modal-backdrop" x-show="showModal">
            <div class="perencanaan-modal">
                <div class="perencanaan-modal-header">
                    <div>
                        <h3 x-text="isEdit ? 'Edit Jadwal Kerja' : 'Tambah Jadwal Baru'"></h3>
                        <p>Atur penugasan dan shift operasional lapangan</p>
                    </div>
                    <button type="button" @click="showModal = false" class="perencanaan-modal-close">
                        <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                    </button>
                </div>
                <form :method="'POST'" :action="formAction">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <input type="hidden" name="tab" value="tugas" />

                    <div class="perencanaan-form-section" style="margin-top: 0;">
                        <i data-lucide="info" style="width: 14px; height: 14px; color: var(--primary);"></i>
                        <span>Program & Kelompok</span>
                    </div>

                    <div class="perencanaan-form-grid">
                        <div style="grid-column: 1 / -1;">
                            <label class="form-label">Program Kerja</label>
                            <select name="program_id" class="form-input" x-model="form.program_id" required style="width:100%;">
                                <option value="">— Pilih program —</option>
                                @foreach($programs as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama_program }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label class="form-label">Kelompok Kerja</label>
                            <select name="worker_group_id" class="form-input" x-model="form.worker_group_id" required style="width:100%;">
                                <option value="">— Pilih kelompok —</option>
                                @foreach($workerGroups as $g)
                                    <option value="{{ $g->id }}">{{ $g->nama_kelompok }} ({{ $g->workers_count }} anggota)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="perencanaan-form-section">
                        <i data-lucide="calendar" style="width: 14px; height: 14px; color: var(--primary);"></i>
                        <span>Jadwal Waktu</span>
                    </div>

                    <div class="perencanaan-form-grid">
                        <div>
                            <label class="form-label">Tanggal Pelaksanaan</label>
                            <input type="date" name="tanggal" class="form-input" x-model="form.tanggal" required style="width:100%;" />
                        </div>
                        <div>
                            <label class="form-label">Status Awal</label>
                            <select name="status" class="form-input" x-model="form.status" required style="width:100%;">
                                <option value="scheduled">Terjadwal</option>
                                <option value="in_progress">Berjalan</option>
                                <option value="completed">Selesai</option>
                                <option value="delayed">Tertunda</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Jam Mulai</label>
                            <input type="time" name="jam_mulai" class="form-input" x-model="form.jam_mulai" style="width:100%;" />
                        </div>
                        <div>
                            <label class="form-label">Jam Selesai</label>
                            <input type="time" name="jam_selesai" class="form-input" x-model="form.jam_selesai" style="width:100%;" />
                        </div>
                        <div>
                            <label class="form-label">Label Shift</label>
                            <input type="text" name="shift_label" class="form-input" x-model="form.shift_label" placeholder="Pagi / Siang" style="width:100%;" />
                        </div>
                    </div>

                    <div class="perencanaan-form-section">
                        <i data-lucide="file-text" style="width: 14px; height: 14px; color: var(--primary);"></i>
                        <span>Deskripsi Pekerjaan</span>
                    </div>
                    <div class="form-group" style="margin-bottom:1rem;">
                        <textarea name="deskripsi" class="form-input" rows="2" x-model="form.deskripsi" placeholder="Detail instruksi/pekerjaan hari ini..." style="width:100%;"></textarea>
                    </div>

                    <div class="perencanaan-form-footer">
                        <button type="button" @click="showModal = false" class="btn btn-outline">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<style>
    .progress-track { height: 8px; background: var(--border); border-radius: 999px; overflow: hidden; }
    .progress-fill { height: 100%; background: linear-gradient(90deg, var(--primary), #34d399); border-radius: 999px; }
</style>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
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
            html: `<div style="background:${color};width:14px;height:14px;border-radius:50%;border:2.5px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.35);"></div>`,
            iconSize: [14, 14],
            iconAnchor: [7, 7],
        });
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('perencanaanData', () => ({
            programs: @json($programs),
            showModal: false,
            editingId: null,
            mapType: 'street',
            isAddingMode: false,
            map: null,
            streetLayer: null,
            satelliteLayer: null,
            markers: [],
            markerMap: {},
            selectedProgramId: null,
            searchQuery: '',
            statusFilter: 'all',
            formData: {
                nama_program: '',
                jenis_program: '',
                sektor_keahlian: '',
                deskripsi: '',
                lokasi: '',
                desa_lokasi: '',
                kordinat: '',
                tanggal_mulai: '',
                tanggal_selesai: '',
                status: 'planned'
            },
            stakeholders: [],
            shNama: '',
            shPeran: '',

            get mappedPrograms() {
                return this.programs.filter(p => p.kordinat);
            },

            get filteredPrograms() {
                let list = this.programs;
                if (this.statusFilter !== 'all') {
                    list = list.filter(p => {
                        if (this.statusFilter === 'active') return ['active', 'ongoing', 'in_progress'].includes(p.status);
                        if (this.statusFilter === 'completed') return ['completed', 'selesai'].includes(p.status);
                        return p.status === 'planned';
                    });
                }
                if (this.searchQuery.trim()) {
                    const q = this.searchQuery.toLowerCase();
                    list = list.filter(p =>
                        (p.nama_program || '').toLowerCase().includes(q) ||
                        (p.lokasi || '').toLowerCase().includes(q) ||
                        (p.jenis_program || '').toLowerCase().includes(q) ||
                        (p.desa_lokasi || '').toLowerCase().includes(q) ||
                        (p.sektor_keahlian || '').toLowerCase().includes(q)
                    );
                }
                return list;
            },

            init() {
                this.$watch('programs', () => setTimeout(() => lucide.createIcons(), 50));
                this.$watch('filteredPrograms', () => setTimeout(() => lucide.createIcons(), 50));
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
                const container = document.getElementById('perencanaan-map');
                if (!container) return;

                this.map = L.map(container, { scrollWheelZoom: true }).setView([-6.914744, 107.609810], 13);
                this.streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                });
                this.satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 19,
                    attribution: '&copy; Esri'
                });
                this.streetLayer.addTo(this.map);
                this.refreshMarkers();

                setTimeout(() => { if (this.map) this.map.invalidateSize(); }, 450);
                window.addEventListener('resize', () => { if (this.map) this.map.invalidateSize(); });

                this.map.on('click', async (e) => {
                    if (!this.isAddingMode) return;
                    const nama = prompt('Masukkan Nama Program/Area Baru:');
                    if (!nama) { this.isAddingMode = false; return; }
                    const lokasi = prompt('Masukkan Deskripsi Lokasi (contoh: RT 01):') || 'Area Baru';
                    const kordinat = `${e.latlng.lat},${e.latlng.lng}`;
                    try {
                        const res = await fetch('/api/programs', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ nama_program: nama, jenis_program: 'Lainnya', lokasi, kordinat, status: 'planned' })
                        });
                        if (res.ok) window.location.reload();
                    } catch (err) {
                        alert('Gagal menambahkan titik.');
                    }
                    this.isAddingMode = false;
                });
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
                this.selectedProgramId = prog.id;
                if (!prog.kordinat || !this.map) return;
                const [lat, lng] = prog.kordinat.split(',').map(Number);
                if (isNaN(lat) || isNaN(lng)) return;
                this.map.flyTo([lat, lng], 16, { duration: 1 });
                const marker = this.markerMap[prog.id];
                if (marker) setTimeout(() => marker.openPopup(), 600);
            },

            fitAllMarkers() {
                const bounds = [];
                this.programs.forEach(p => {
                    if (!p.kordinat) return;
                    const [lat, lng] = p.kordinat.split(',').map(Number);
                    if (!isNaN(lat) && !isNaN(lng)) bounds.push([lat, lng]);
                });
                if (bounds.length > 0) this.map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
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

            resetForm() {
                this.formData = {
                    nama_program: '', jenis_program: '', sektor_keahlian: '', deskripsi: '',
                    lokasi: '', desa_lokasi: '', kordinat: '', tanggal_mulai: '', tanggal_selesai: '', status: 'planned'
                };
                this.stakeholders = [];
                this.shNama = '';
                this.shPeran = '';
                this.editingId = null;
            },

            handleEdit(prog) {
                this.formData = {
                    nama_program: prog.nama_program,
                    jenis_program: prog.jenis_program,
                    sektor_keahlian: prog.sektor_keahlian || '',
                    deskripsi: prog.deskripsi,
                    lokasi: prog.lokasi || '',
                    desa_lokasi: prog.desa_lokasi || '',
                    kordinat: prog.kordinat || '',
                    tanggal_mulai: prog.tanggal_mulai ? prog.tanggal_mulai.substring(0, 10) : '',
                    tanggal_selesai: prog.tanggal_selesai ? prog.tanggal_selesai.substring(0, 10) : '',
                    status: prog.status || 'planned'
                };
                this.stakeholders = this.parseStakeholders(prog.stakeholders);
                this.editingId = prog.id;
                this.showModal = true;
                setTimeout(() => lucide.createIcons(), 50);
            },

            handleAddStakeholder() {
                const nama = this.shNama.trim();
                const peran = this.shPeran.trim() || 'Lainnya';
                if (nama) {
                    this.stakeholders.push({ nama, peran });
                    this.shNama = '';
                    this.shPeran = '';
                    setTimeout(() => lucide.createIcons(), 50);
                }
            },

            handleRemoveStakeholder(index) {
                this.stakeholders.splice(index, 1);
            },

            parseStakeholders(str) {
                if (!str) return [];
                try {
                    const parsed = JSON.parse(str);
                    return parsed.map(item => typeof item === 'string' ? { nama: item, peran: 'Lainnya' } : item);
                } catch (e) {
                    if (typeof str === 'string') return str.split(',').map(s => ({ nama: s.trim(), peran: 'Lainnya' }));
                    return [];
                }
            },

            formatDate(dateString) {
                if (!dateString) return '-';
                return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            },

            getStatusLabel(status) {
                if (['active', 'ongoing', 'in_progress'].includes(status)) return 'Aktif';
                if (['completed', 'selesai'].includes(status)) return 'Selesai';
                return 'Direncanakan';
            },

            getProgressPercent(prog) {
                if (!prog.tanggal_mulai || !prog.tanggal_selesai) return 0;
                const start = new Date(prog.tanggal_mulai).getTime();
                const end = new Date(prog.tanggal_selesai).getTime();
                const now = Date.now();
                if (now <= start) return 0;
                if (now >= end) return 100;
                return Math.round(((now - start) / (end - start)) * 100);
            }
        }));

        Alpine.data('tugasData', () => ({
            showModal: false,
            isEdit: false,
            formAction: '{{ url('/admin/tugas') }}',
            form: {
                program_id: '',
                tanggal: '',
                status: 'scheduled',
                jam_mulai: '',
                jam_selesai: '',
                shift_label: '',
                deskripsi: '',
                worker_group_id: '',
            },

            init() {
                setTimeout(() => lucide.createIcons(), 50);
            },

            openCreateModal() {
                this.isEdit = false;
                this.formAction = '{{ url('/admin/tugas') }}';
                this.form = {
                    program_id: '',
                    tanggal: new Date().toISOString().slice(0, 10),
                    status: 'scheduled',
                    jam_mulai: '',
                    jam_selesai: '',
                    shift_label: '',
                    deskripsi: '',
                    worker_group_id: '',
                };
                this.showModal = true;
                setTimeout(() => lucide.createIcons(), 50);
            },

            openEditModal(item) {
                this.isEdit = true;
                this.formAction = '{{ url('/admin/tugas') }}/' + item.id;
                this.form = {
                    program_id: String(item.program_id || ''),
                    tanggal: item.tanggal ? String(item.tanggal).slice(0, 10) : '',
                    status: item.status || 'scheduled',
                    jam_mulai: item.jam_mulai || '',
                    jam_selesai: item.jam_selesai || '',
                    shift_label: item.shift_label || '',
                    deskripsi: item.deskripsi || '',
                    worker_group_id: String(item.worker_group_id || ''),
                };
                this.showModal = true;
                setTimeout(() => lucide.createIcons(), 50);
            },
        }));
    });
</script>
@endsection
