@extends('layouts.app')
@section('title', 'Manajemen Inventaris')

@push('styles')
<style>
    .inv-page {
        padding: 2rem;
        max-width: 1440px;
        margin: 0 auto;
    }

    /* Hero */
    .inv-hero {
        background: linear-gradient(135deg, #b45309 0%, #d97706 38%, #0f766e 100%);
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
        box-shadow: 0 12px 40px rgba(217, 119, 6, 0.24);
    }

    .inv-hero::before {
        content: '';
        position: absolute;
        top: -42%;
        right: -6%;
        width: 340px;
        height: 340px;
        background: rgba(255, 255, 255, 0.07);
        border-radius: 50%;
        pointer-events: none;
    }

    .inv-hero::after {
        content: '';
        position: absolute;
        bottom: -38%;
        left: 8%;
        width: 190px;
        height: 190px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        pointer-events: none;
    }

    .inv-hero-content { position: relative; z-index: 1; }

    .inv-hero-badge {
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

    .inv-hero h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        margin: 0 0 0.4rem;
        line-height: 1.25;
    }

    .inv-hero p {
        color: rgba(255, 255, 255, 0.88);
        font-size: 0.92rem;
        margin: 0;
        max-width: 540px;
        line-height: 1.55;
    }

    .inv-hero-actions {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
        flex-shrink: 0;
    }

    .inv-btn-white {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: white;
        color: #b45309;
        border: none;
        border-radius: 99px;
        padding: 0.65rem 1.25rem;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.15s, box-shadow 0.15s;
    }

    .inv-btn-white:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .inv-btn-ghost {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(255, 255, 255, 0.12);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 99px;
        padding: 0.65rem 1.25rem;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        backdrop-filter: blur(4px);
        transition: background 0.15s;
    }

    .inv-btn-ghost:hover { background: rgba(255, 255, 255, 0.2); color: white; }

    /* Stats */
    .inv-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    @media (max-width: 900px) { .inv-stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 520px) {
        .inv-stats { grid-template-columns: 1fr; }
        .inv-hero { flex-direction: column; }
    }

    .inv-stat {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.25rem;
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        transition: box-shadow 0.2s, transform 0.2s;
    }

    .inv-stat:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        transform: translateY(-2px);
    }

    .inv-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .inv-stat-label {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 500;
        margin-bottom: 0.15rem;
    }

    .inv-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-main);
        line-height: 1.1;
    }

    .inv-stat-sub {
        font-size: 0.72rem;
        color: var(--text-muted);
        margin-top: 0.2rem;
    }

    /* Alert */
    .inv-alert {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        background: #f0fdf4;
        color: #166534;
        padding: 0.85rem 1.1rem;
        border-radius: var(--radius-sm);
        margin-bottom: 1.25rem;
        font-size: 0.85rem;
        border: 1px solid #bbf7d0;
    }

    /* Toolbar */
    .inv-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .inv-search {
        position: relative;
        flex: 1;
        min-width: 220px;
        max-width: 420px;
    }

    .inv-search input {
        width: 100%;
        padding: 0.7rem 1rem 0.7rem 2.5rem;
        border-radius: 99px;
        border: 1px solid var(--border);
        background: var(--surface);
        font-size: 0.875rem;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .inv-search input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12);
    }

    .inv-search-icon {
        position: absolute;
        left: 0.9rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
    }

    .inv-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .inv-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 0.9rem;
        border-radius: 99px;
        font-size: 0.78rem;
        font-weight: 500;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.15s;
        white-space: nowrap;
        text-decoration: none;
    }

    .inv-filter-chip:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .inv-filter-chip.active {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .inv-filter-chip .chip-count {
        background: rgba(0, 0, 0, 0.08);
        padding: 0.1rem 0.45rem;
        border-radius: 99px;
        font-size: 0.68rem;
        font-weight: 600;
    }

    .inv-filter-chip.active .chip-count {
        background: rgba(255, 255, 255, 0.25);
    }

    .inv-results-info {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 1.25rem;
    }

    /* Grid */
    .inv-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.25rem;
    }

    .inv-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .inv-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
    }

    .inv-card-header {
        padding: 1.1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    }

    .inv-card-kategori {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.28);
        padding: 0.22rem 0.65rem;
        border-radius: 99px;
        font-size: 0.68rem;
        font-weight: 600;
        color: white;
        backdrop-filter: blur(4px);
    }

    .inv-stock-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.22rem 0.6rem;
        border-radius: 99px;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        background: rgba(255, 255, 255, 0.92);
    }

    .inv-card-body {
        padding: 1.25rem;
        flex: 1;
    }

    .inv-card-name {
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 1rem;
        line-height: 1.35;
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
    }

    .inv-card-name-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .inv-stock-display {
        display: flex;
        align-items: baseline;
        gap: 0.35rem;
        margin-bottom: 0.75rem;
    }

    .inv-stock-qty {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
        color: var(--text-main);
    }

    .inv-stock-unit {
        font-size: 0.9rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .inv-stock-bar-wrap {
        margin-bottom: 0.35rem;
    }

    .inv-stock-bar {
        height: 6px;
        background: #f1f5f9;
        border-radius: 99px;
        overflow: hidden;
    }

    .inv-stock-bar-fill {
        height: 100%;
        border-radius: 99px;
        transition: width 0.4s ease;
    }

    .inv-stock-hint {
        font-size: 0.72rem;
        color: var(--text-muted);
    }

    .inv-card-footer {
        padding: 0.85rem 1.25rem;
        border-top: 1px solid var(--border);
        background: #fafbfc;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.45rem;
    }

    .inv-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
        padding: 0.55rem 0.25rem;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: white;
        cursor: pointer;
        font-size: 0.65rem;
        font-weight: 600;
        color: var(--text-muted);
        transition: all 0.15s;
    }

    .inv-action-btn span { line-height: 1; }

    .inv-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .inv-action-btn--in { color: #059669; border-color: #a7f3d0; }
    .inv-action-btn--in:hover { background: #ecfdf5; border-color: #059669; }

    .inv-action-btn--out { color: #dc2626; border-color: #fecaca; }
    .inv-action-btn--out:hover { background: #fef2f2; border-color: #dc2626; }

    .inv-action-btn--sell { color: #d97706; border-color: #fde68a; background: #fffbeb; }
    .inv-action-btn--sell:hover { background: #fef3c7; border-color: #d97706; }

    .inv-action-btn--hist { color: var(--primary); border-color: rgba(15, 118, 110, 0.25); }
    .inv-action-btn--hist:hover { background: var(--primary-light); border-color: var(--primary); }

    /* Empty */
    .inv-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 4rem 2rem;
        background: var(--surface);
        border-radius: var(--radius-md);
        border: 2px dashed var(--border);
    }

    .inv-empty-icon {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: rgba(217, 119, 6, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.25rem;
        color: #d97706;
    }

    .inv-empty h3 { font-size: 1.1rem; margin-bottom: 0.4rem; }
    .inv-empty p { font-size: 0.875rem; margin-bottom: 1.25rem; }

    /* Modals */
    .inv-modal {
        background: white;
        width: 100%;
        max-width: 520px;
        max-height: 90vh;
        border-radius: var(--radius-md);
        overflow: hidden;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
    }

    .inv-modal--wide { max-width: 640px; }

    .inv-modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
    }

    .inv-modal-header h3 {
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .inv-modal-close {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        background: transparent;
        cursor: pointer;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s;
    }

    .inv-modal-close:hover { background: var(--border); }

    .inv-modal-body {
        padding: 1.5rem;
        overflow-y: auto;
        max-height: calc(90vh - 64px);
    }

    .inv-form-group { margin-bottom: 1rem; }

    .inv-form-group label {
        display: block;
        font-size: 0.8rem;
        font-weight: 500;
        margin-bottom: 0.4rem;
        color: var(--text-muted);
    }

    .inv-form-group input,
    .inv-form-group textarea,
    .inv-form-group select {
        width: 100%;
        padding: 0.65rem 0.85rem;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border);
        font-size: 0.875rem;
        outline: none;
        transition: border-color 0.2s;
        font-family: inherit;
    }

    .inv-form-group input:focus,
    .inv-form-group textarea:focus,
    .inv-form-group select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
    }

    .inv-form-group textarea {
        min-height: 80px;
        resize: vertical;
    }

    .inv-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    @media (max-width: 520px) { .inv-form-grid { grid-template-columns: 1fr; } }

    .inv-form-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        margin-top: 1.25rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
    }

    .inv-modal-item-preview {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        margin-bottom: 1.25rem;
    }

    .inv-modal-item-preview strong {
        display: block;
        font-size: 0.9rem;
        color: var(--text-main);
    }

    .inv-modal-item-preview span {
        font-size: 0.78rem;
        color: var(--text-muted);
    }

    /* History */
    .inv-history-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .inv-history-item {
        display: flex;
        gap: 0.85rem;
        padding: 1rem;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border);
    }

    .inv-history-item--in {
        background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
        border-color: #a7f3d0;
    }

    .inv-history-item--out {
        background: linear-gradient(135deg, #fef2f2, #fff1f2);
        border-color: #fecaca;
    }

    .inv-history-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .inv-history-item--in .inv-history-icon { background: #d1fae5; color: #059669; }
    .inv-history-item--out .inv-history-icon { background: #fee2e2; color: #dc2626; }

    .inv-history-meta {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.5rem;
        margin-bottom: 0.25rem;
    }

    .inv-history-type {
        font-size: 0.82rem;
        font-weight: 700;
    }

    .inv-history-item--in .inv-history-type { color: #047857; }
    .inv-history-item--out .inv-history-type { color: #b91c1c; }

    .inv-history-date {
        font-size: 0.72rem;
        color: var(--text-muted);
        white-space: nowrap;
    }

    .inv-history-qty {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 0.25rem;
    }

    .inv-history-note {
        font-size: 0.82rem;
        color: #475569;
        line-height: 1.45;
    }

    .inv-history-recipient {
        font-size: 0.75rem;
        color: var(--primary);
        margin-top: 0.35rem;
        font-weight: 500;
    }

    .inv-history-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        color: var(--text-muted);
    }
</style>
@endpush

@section('content')
<div class="inv-page animate-fade-in" x-data="inventarisData()">

    {{-- Hero --}}
    <section class="inv-hero">
        <div class="inv-hero-content">
            <div class="inv-hero-badge">
                <i data-lucide="warehouse" style="width: 14px; height: 14px;"></i>
                Gudang Hasil Kerja
            </div>
            <h1>Manajemen Inventaris</h1>
            <p>Lacak stok hasil panen, kompos, kerajinan, dan peralatan. Kelola distribusi gratis, penjualan, serta riwayat transaksi stok secara real-time.</p>
        </div>
        <div class="inv-hero-actions">
            <button class="inv-btn-white" @click="showAddForm = true">
                <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                Tambah Barang
            </button>
            <a href="{{ route('admin.inventaris', ['filter' => 'kompos-kerajinan']) }}" class="inv-btn-ghost">
                <i data-lucide="recycle" style="width: 15px; height: 15px;"></i>
                Kompos & Kerajinan
            </a>
        </div>
    </section>

    {{-- Stats --}}
    <div class="inv-stats">
        <div class="inv-stat">
            <div class="inv-stat-icon" style="background: rgba(217, 119, 6, 0.12); color: #d97706;">
                <i data-lucide="package" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
                <div class="inv-stat-label">Total Barang</div>
                <div class="inv-stat-value" x-text="items.length"></div>
                <div class="inv-stat-sub">Jenis item terdaftar</div>
            </div>
        </div>
        <div class="inv-stat">
            <div class="inv-stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;">
                <i data-lucide="layers" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
                <div class="inv-stat-label">Total Stok</div>
                <div class="inv-stat-value" x-text="totalStockUnits"></div>
                <div class="inv-stat-sub">Akumulasi kuantitas</div>
            </div>
        </div>
        <div class="inv-stat">
            <div class="inv-stat-icon" style="background: rgba(15, 118, 110, 0.1); color: #0f766e;">
                <i data-lucide="tags" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
                <div class="inv-stat-label">Kategori</div>
                <div class="inv-stat-value" x-text="uniqueCategories"></div>
                <div class="inv-stat-sub">Jenis kategori aktif</div>
            </div>
        </div>
        <div class="inv-stat">
            <div class="inv-stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                <i data-lucide="alert-triangle" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
                <div class="inv-stat-label">Stok Menipis</div>
                <div class="inv-stat-value" x-text="lowStockCount"></div>
                <div class="inv-stat-sub">Barang &le; 10 unit</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="inv-alert">
            <i data-lucide="check-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Toolbar --}}
    <div class="inv-toolbar">
        <div class="inv-search">
            <i data-lucide="search" class="inv-search-icon" style="width: 17px; height: 17px;"></i>
            <input type="text" placeholder="Cari nama barang atau kategori..." x-model="searchTerm" />
        </div>
        <div class="inv-filters">
            <a href="{{ route('admin.inventaris', ['filter' => 'semua']) }}"
               class="inv-filter-chip {{ ($filter ?? 'semua') === 'semua' ? 'active' : '' }}">
                <i data-lucide="layout-grid" style="width: 13px; height: 13px;"></i>
                Semua Barang
                <span class="chip-count">{{ \App\Models\Inventaris::count() }}</span>
            </a>
            <a href="{{ route('admin.inventaris', ['filter' => 'kompos-kerajinan']) }}"
               class="inv-filter-chip {{ ($filter ?? 'semua') === 'kompos-kerajinan' ? 'active' : '' }}">
                <i data-lucide="recycle" style="width: 13px; height: 13px;"></i>
                Kompos & Kerajinan
            </a>
        </div>
    </div>

    <div class="inv-filters" style="margin-bottom: 1rem;">
        <template x-for="cat in categoryList" :key="cat">
            <button type="button"
                class="inv-filter-chip"
                :class="{ active: activeCategory === cat }"
                @click="activeCategory = cat">
                <i :data-lucide="categoryIcon(cat)" style="width: 13px; height: 13px;"></i>
                <span x-text="cat"></span>
                <span class="chip-count" x-text="cat === 'Semua' ? items.length : countByCategory(cat)"></span>
            </button>
        </template>
    </div>

    <p class="inv-results-info">
        Menampilkan <strong x-text="filteredItems.length"></strong> barang
        <template x-if="categoryFilter === 'kompos-kerajinan'">
            <span> &mdash; filter kompos & kerajinan aktif</span>
        </template>
    </p>

    {{-- Grid kartu --}}
    <div class="inv-grid">
        <template x-for="item in filteredItems" :key="item.id">
            <article class="inv-card">
                <div class="inv-card-header" :style="'background: linear-gradient(135deg, ' + categoryColor(item.kategori).from + ', ' + categoryColor(item.kategori).to + ')'">
                    <span class="inv-card-kategori">
                        <i :data-lucide="categoryIcon(item.kategori)" style="width: 12px; height: 12px;"></i>
                        <span x-text="item.kategori"></span>
                    </span>
                    <span class="inv-stock-badge" :style="'color: ' + stockLevel(item.kuantitas).color">
                        <i :data-lucide="stockLevel(item.kuantitas).icon" style="width: 11px; height: 11px;"></i>
                        <span x-text="stockLevel(item.kuantitas).label"></span>
                    </span>
                </div>
                <div class="inv-card-body">
                    <div class="inv-card-name">
                        <div class="inv-card-name-icon">
                            <i data-lucide="package" style="width: 18px; height: 18px;"></i>
                        </div>
                        <span x-text="item.nama_barang"></span>
                    </div>
                    <div class="inv-stock-display">
                        <span class="inv-stock-qty" x-text="Number(item.kuantitas)"></span>
                        <span class="inv-stock-unit" x-text="item.satuan"></span>
                    </div>
                    <div class="inv-stock-bar-wrap">
                        <div class="inv-stock-bar">
                            <div class="inv-stock-bar-fill"
                                :style="'width: ' + stockBarWidth(item.kuantitas) + '%; background: ' + stockLevel(item.kuantitas).color"></div>
                        </div>
                    </div>
                    <div class="inv-stock-hint" x-text="stockLevel(item.kuantitas).hint"></div>
                </div>
                <div class="inv-card-footer">
                    <button type="button" class="inv-action-btn inv-action-btn--in" @click="openAction(item, 'tambah')" title="Tambah Stok">
                        <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                        <span>Masuk</span>
                    </button>
                    <button type="button" class="inv-action-btn inv-action-btn--out" @click="openAction(item, 'kurang')" title="Distribusi Keluar">
                        <i data-lucide="minus" style="width: 16px; height: 16px;"></i>
                        <span>Keluar</span>
                    </button>
                    <button type="button" class="inv-action-btn inv-action-btn--sell" @click="openAction(item, 'jual')" title="Jual Barang">
                        <i data-lucide="shopping-cart" style="width: 16px; height: 16px;"></i>
                        <span>Jual</span>
                    </button>
                    <button type="button" class="inv-action-btn inv-action-btn--hist" @click="fetchHistory(item)" title="Riwayat">
                        <i data-lucide="history" style="width: 16px; height: 16px;"></i>
                        <span>Riwayat</span>
                    </button>
                </div>
            </article>
        </template>

        <template x-if="filteredItems.length === 0">
            <div class="inv-empty">
                <div class="inv-empty-icon">
                    <i data-lucide="package-x" style="width: 32px; height: 32px;"></i>
                </div>
                <h3>Belum ada barang ditemukan</h3>
                <p x-text="items.length === 0 ? 'Tambahkan barang pertama ke inventaris gudang desa.' : 'Coba ubah filter atau kata kunci pencarian.'"></p>
                <button class="btn btn-primary" @click="showAddForm = true" x-show="items.length === 0">
                    <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                    Tambah Barang Pertama
                </button>
            </div>
        </template>
    </div>

    {{-- Modal Tambah Barang --}}
    <div x-show="showAddForm" x-cloak class="modal-overlay modal-overlay--blur" style="padding: 1.5rem;" @click="showAddForm = false">
        <div class="inv-modal" @click.stop>
            <div class="inv-modal-header">
                <h3>
                    <i data-lucide="package-plus" style="width: 18px; height: 18px; color: var(--primary);"></i>
                    Tambah Barang Inventaris
                </h3>
                <button type="button" class="inv-modal-close" @click="showAddForm = false">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>
            <div class="inv-modal-body">
                <form method="POST" action="{{ route('admin.inventaris') }}">
                    @csrf
                    <div class="inv-form-group">
                        <label>Nama Barang</label>
                        <input type="text" name="nama_barang" required placeholder="Contoh: Hasil Panen Pakcoy" />
                    </div>
                    <div class="inv-form-grid">
                        <div class="inv-form-group">
                            <label>Kategori</label>
                            <select name="kategori">
                                <option value="Kompos">Kompos</option>
                                <option value="Sayur">Sayur</option>
                                <option value="Kerajinan">Kerajinan</option>
                                <option value="Peralatan Tani">Peralatan Tani</option>
                                <option value="Bibit & Benih">Bibit & Benih</option>
                            </select>
                        </div>
                        <div class="inv-form-group">
                            <label>Satuan</label>
                            <select name="satuan">
                                <option value="Kg">Kilogram (Kg)</option>
                                <option value="Ikat">Ikat</option>
                                <option value="Unit">Unit</option>
                                <option value="Liter">Liter (L)</option>
                                <option value="Karung">Karung / Sak</option>
                            </select>
                        </div>
                    </div>
                    <div class="inv-form-group">
                        <label>Stok Awal</label>
                        <input type="number" step="0.1" name="kuantitas" placeholder="0" required />
                    </div>
                    <div class="inv-form-actions">
                        <button type="button" class="btn btn-outline" @click="showAddForm = false">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                            Simpan Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Adjust Stok --}}
    <div x-show="activeAction && (activeAction.type === 'tambah' || activeAction.type === 'kurang' || activeAction.type === 'jual')" x-cloak class="modal-overlay modal-overlay--blur" style="padding: 1.5rem;" @click="closeAction()">
        <div class="inv-modal" @click.stop>
            <div class="inv-modal-header">
                <h3 :style="'color: ' + actionColor(activeAction?.type)">
                    <i :data-lucide="actionIcon(activeAction?.type)" style="width: 18px; height: 18px;"></i>
                    <span x-text="actionTitle(activeAction?.type)"></span>
                </h3>
                <button type="button" class="inv-modal-close" @click="closeAction()">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>
            <div class="inv-modal-body">
                <div class="inv-modal-item-preview" x-show="selectedItem">
                    <div class="inv-card-name-icon">
                        <i data-lucide="package" style="width: 18px; height: 18px;"></i>
                    </div>
                    <div>
                        <strong x-text="selectedItem?.nama_barang"></strong>
                        <span>Stok saat ini: <strong x-text="Number(selectedItem?.kuantitas) + ' ' + selectedItem?.satuan"></strong></span>
                    </div>
                </div>
                <form method="POST" :action="'/admin/inventaris/' + (activeAction?.id) + '/adjust'">
                    @csrf
                    <input type="hidden" name="tipe" :value="activeAction?.type === 'jual' ? 'kurang' : activeAction?.type">
                    <input type="hidden" name="household_id" :value="household_id || ''">
                    <div class="inv-form-group">
                        <label>Jumlah (<span x-text="selectedItem?.satuan"></span>)</label>
                        <input type="number" step="0.1" min="0.1" name="jumlah" required placeholder="Contoh: 5" />
                    </div>

                    <template x-if="activeAction?.type === 'jual'">
                        <div class="inv-form-grid">
                            <div class="inv-form-group">
                                <label>Nama Pembeli</label>
                                <input type="text" x-model="pembeli" placeholder="Bapak Budi..." />
                            </div>
                            <div class="inv-form-group">
                                <label>Total Harga Jual (Rp)</label>
                                <input type="number" min="0" x-model="harga" placeholder="150000" />
                            </div>
                        </div>
                    </template>

                    <template x-if="activeAction?.type === 'kurang'">
                        <div class="inv-form-group">
                            <label>Penerima Distribusi (Keluarga Miskin)</label>
                            <select x-model="household_id">
                                <option value="">-- Pilih Keluarga / Lainnya --</option>
                                <template x-for="h in households" :key="h.id">
                                    <option :value="h.id" x-text="'Keluarga ' + h.kepala_keluarga + ' (RT/RW ' + h.rt_rw + ')'"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    <div class="inv-form-group">
                        <label>Catatan / Keterangan</label>
                        <textarea name="keterangan" x-model="keterangan"
                            :placeholder="activeAction?.type === 'kurang' ? 'Dibagikan ke warga RT 01...' : (activeAction?.type === 'jual' ? 'Dijual eceran ke pasar...' : 'Hasil panen minggu ke-2...')"
                            :required="activeAction?.type !== 'jual'"></textarea>
                    </div>

                    <div class="inv-form-actions">
                        <button type="button" class="btn btn-outline" @click="closeAction()">Batal</button>
                        <button type="submit" class="btn"
                            :style="'background: ' + actionColor(activeAction?.type) + '; color: white; display: inline-flex; align-items: center; gap: 0.5rem;'"
                            @click="prepareSubmit($event)">
                            <i :data-lucide="actionIcon(activeAction?.type)" style="width: 16px; height: 16px;"></i>
                            <span x-text="'Konfirmasi ' + (activeAction?.type === 'tambah' ? 'Masuk' : (activeAction?.type === 'jual' ? 'Penjualan' : 'Keluar'))"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Riwayat --}}
    <div x-show="activeAction && activeAction.type === 'history'" x-cloak class="modal-overlay modal-overlay--blur" style="padding: 1.5rem;" @click="activeAction = null">
        <div class="inv-modal inv-modal--wide" @click.stop style="display: flex; flex-direction: column; max-height: 85vh;">
            <div class="inv-modal-header">
                <h3>
                    <i data-lucide="history" style="width: 18px; height: 18px; color: var(--primary);"></i>
                    Riwayat: <span x-text="selectedItem?.nama_barang" style="color: var(--text-main); font-weight: 600;"></span>
                </h3>
                <button type="button" class="inv-modal-close" @click="activeAction = null">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>
            <div class="inv-modal-body" style="flex: 1;">
                <template x-if="historyData.length === 0">
                    <div class="inv-history-empty">
                        <i data-lucide="inbox" style="width: 40px; height: 40px; color: #cbd5e1; margin-bottom: 0.75rem;"></i>
                        <p>Belum ada riwayat transaksi.</p>
                    </div>
                </template>
                <div class="inv-history-list">
                    <template x-for="h in historyData" :key="h.id">
                        <div class="inv-history-item" :class="h.tipe_perubahan === 'tambah' ? 'inv-history-item--in' : 'inv-history-item--out'">
                            <div class="inv-history-icon">
                                <i :data-lucide="h.tipe_perubahan === 'tambah' ? 'arrow-down-to-line' : 'arrow-up-to-line'" style="width: 20px; height: 20px;"></i>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div class="inv-history-meta">
                                    <span class="inv-history-type" x-text="h.tipe_perubahan === 'tambah' ? 'Stok Masuk' : 'Distribusi Keluar'"></span>
                                    <span class="inv-history-date" x-text="new Date(h.created_at).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="inv-history-qty" x-text="(h.tipe_perubahan === 'tambah' ? '+' : '-') + Number(h.jumlah_perubahan) + ' ' + selectedItem?.satuan"></div>
                                <p class="inv-history-note" x-text="h.keterangan"></p>
                                <template x-if="h.household">
                                    <p class="inv-history-recipient">
                                        Penerima: <span x-text="h.household.kepala_keluarga + ' (RT/RW ' + h.household.rt_rw + ')'"></span>
                                    </p>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('inventarisData', () => ({
            items: @json($items),
            households: @json($households ?? []),
            categoryFilter: @json($filter ?? 'semua'),
            activeCategory: 'Semua',
            categoryList: ['Semua', 'Kompos', 'Sayur', 'Kerajinan', 'Peralatan Tani', 'Bibit & Benih'],
            searchTerm: '',
            showAddForm: false,
            activeAction: null,
            selectedItem: null,
            historyData: [],
            pembeli: '',
            harga: '',
            keterangan: '',
            household_id: '',

            get filteredItems() {
                let list = this.items;
                if (this.activeCategory !== 'Semua') {
                    list = list.filter(i => i.kategori === this.activeCategory);
                }
                if (this.searchTerm === '') return list;
                const term = this.searchTerm.toLowerCase();
                return list.filter(i =>
                    i.nama_barang.toLowerCase().includes(term) ||
                    i.kategori.toLowerCase().includes(term)
                );
            },

            get totalStockUnits() {
                return this.items.reduce((sum, i) => sum + Number(i.kuantitas), 0).toLocaleString('id-ID');
            },

            get uniqueCategories() {
                return new Set(this.items.map(i => i.kategori)).size;
            },

            get lowStockCount() {
                return this.items.filter(i => Number(i.kuantitas) <= 10).length;
            },

            countByCategory(cat) {
                return this.items.filter(i => i.kategori === cat).length;
            },

            categoryColor(kategori) {
                const map = {
                    'Kompos': { from: '#059669', to: '#34d399' },
                    'Sayur': { from: '#2563eb', to: '#60a5fa' },
                    'Kerajinan': { from: '#d97706', to: '#fbbf24' },
                    'Peralatan Tani': { from: '#7c3aed', to: '#a78bfa' },
                    'Bibit & Benih': { from: '#db2777', to: '#f472b6' },
                };
                return map[kategori] || { from: '#64748b', to: '#94a3b8' };
            },

            categoryIcon(kategori) {
                const map = {
                    'Semua': 'layout-grid',
                    'Kompos': 'recycle',
                    'Sayur': 'sprout',
                    'Kerajinan': 'palette',
                    'Peralatan Tani': 'shovel',
                    'Bibit & Benih': 'leaf',
                };
                return map[kategori] || 'tag';
            },

            stockLevel(qty) {
                const n = Number(qty);
                if (n <= 0) return { label: 'Habis', color: '#64748b', icon: 'circle-off', hint: 'Stok habis — perlu restock segera' };
                if (n <= 10) return { label: 'Menipis', color: '#ef4444', icon: 'alert-triangle', hint: 'Stok rendah — pertimbangkan penambahan' };
                if (n <= 50) return { label: 'Cukup', color: '#f59e0b', icon: 'minus-circle', hint: 'Stok dalam batas aman' };
                return { label: 'Aman', color: '#22c55e', icon: 'check-circle', hint: 'Stok melimpah' };
            },

            stockBarWidth(qty) {
                const n = Number(qty);
                return Math.min(100, Math.max(5, (n / 100) * 100));
            },

            actionColor(type) {
                if (type === 'tambah') return '#059669';
                if (type === 'jual') return '#d97706';
                return '#dc2626';
            },

            actionIcon(type) {
                if (type === 'tambah') return 'arrow-down-to-line';
                if (type === 'jual') return 'shopping-cart';
                return 'arrow-up-to-line';
            },

            actionTitle(type) {
                if (type === 'tambah') return 'Tambah Stok Masuk';
                if (type === 'jual') return 'Penjualan Barang';
                return 'Distribusi Gratis (Keluar)';
            },

            init() {
                this.$watch('filteredItems', () => setTimeout(() => lucide.createIcons(), 50));
                this.$watch('showAddForm', () => setTimeout(() => lucide.createIcons(), 50));
                this.$watch('activeCategory', () => setTimeout(() => lucide.createIcons(), 50));
                this.$watch('activeAction', () => setTimeout(() => lucide.createIcons(), 50));
            },

            openAction(item, type) {
                this.selectedItem = item;
                this.activeAction = { id: item.id, type: type };
                this.pembeli = '';
                this.harga = '';
                this.keterangan = '';
                this.household_id = '';
                setTimeout(() => lucide.createIcons(), 50);
            },

            closeAction() {
                this.activeAction = null;
                this.selectedItem = null;
            },

            prepareSubmit(e) {
                if (this.activeAction.type === 'jual') {
                    const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
                    this.keterangan = `🛒 Penjualan kepada: ${this.pembeli || 'Umum'} | Total: ${formatter.format(this.harga || 0)} | Catatan: ${this.keterangan || '-'}`;
                } else if (this.activeAction.type === 'kurang' && this.household_id) {
                    const targetKeluarga = this.households.find(h => h.id.toString() === this.household_id);
                    if (targetKeluarga) {
                        this.keterangan = `🤝 Distribusi gratis kepada: Keluarga ${targetKeluarga.kepala_keluarga} (RT/RW ${targetKeluarga.rt_rw}) | Catatan: ${this.keterangan || '-'}`;
                    }
                }
            },

            async fetchHistory(item) {
                this.selectedItem = item;
                try {
                    const res = await fetch(`/api/inventaris/${item.id}/history`);
                    if (res.ok) {
                        this.historyData = await res.json();
                        this.activeAction = { id: item.id, type: 'history' };
                        setTimeout(() => lucide.createIcons(), 50);
                    }
                } catch (error) {
                    console.error('Error fetching history:', error);
                }
            }
        }));
    });
</script>
@endsection
