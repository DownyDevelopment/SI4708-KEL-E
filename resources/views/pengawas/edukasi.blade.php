@extends('layouts.app')
@section('title', 'Edukasi Pekerja')

@push('styles')
<style>
    .edu-page {
        padding: 2rem;
        max-width: 1440px;
        margin: 0 auto;
    }

    /* Hero */
    .edu-hero {
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

    .edu-hero::before {
        content: '';
        position: absolute;
        top: -45%;
        right: -8%;
        width: 360px;
        height: 360px;
        background: rgba(255, 255, 255, 0.07);
        border-radius: 50%;
        pointer-events: none;
    }

    .edu-hero::after {
        content: '';
        position: absolute;
        bottom: -35%;
        left: 10%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        pointer-events: none;
    }

    .edu-hero-content { position: relative; z-index: 1; }

    .edu-hero-badge {
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

    .edu-hero h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        margin: 0 0 0.4rem;
        line-height: 1.25;
    }

    .edu-hero p {
        color: rgba(255, 255, 255, 0.88);
        font-size: 0.92rem;
        margin: 0;
        max-width: 520px;
        line-height: 1.55;
    }

    .edu-hero-actions {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
        flex-shrink: 0;
    }

    .edu-btn-white {
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
        transition: transform 0.15s, box-shadow 0.15s;
    }

    .edu-btn-white:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .edu-btn-ghost {
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
        backdrop-filter: blur(4px);
        transition: background 0.15s;
    }

    .edu-btn-ghost:hover { background: rgba(255, 255, 255, 0.2); }

    /* Stats */
    .edu-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    @media (max-width: 900px) {
        .edu-stats { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 520px) {
        .edu-stats { grid-template-columns: 1fr; }
        .edu-hero { flex-direction: column; }
    }

    .edu-stat {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.25rem;
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        transition: box-shadow 0.2s, transform 0.2s;
    }

    .edu-stat:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        transform: translateY(-2px);
    }

    .edu-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .edu-stat-label {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 500;
        margin-bottom: 0.15rem;
    }

    .edu-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-main);
        line-height: 1.1;
    }

    .edu-stat-sub {
        font-size: 0.72rem;
        color: var(--text-muted);
        margin-top: 0.2rem;
    }

    /* Toolbar */
    .edu-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
    }

    .edu-search {
        position: relative;
        flex: 1;
        min-width: 220px;
        max-width: 420px;
    }

    .edu-search input {
        width: 100%;
        padding: 0.7rem 1rem 0.7rem 2.5rem;
        border-radius: 99px;
        border: 1px solid var(--border);
        background: var(--surface);
        font-size: 0.875rem;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .edu-search input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12);
    }

    .edu-search-icon {
        position: absolute;
        left: 0.9rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
    }

    .edu-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .edu-filter-chip {
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
    }

    .edu-filter-chip:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .edu-filter-chip.active {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .edu-filter-chip .chip-count {
        background: rgba(0, 0, 0, 0.08);
        padding: 0.1rem 0.45rem;
        border-radius: 99px;
        font-size: 0.68rem;
        font-weight: 600;
    }

    .edu-filter-chip.active .chip-count {
        background: rgba(255, 255, 255, 0.25);
    }

    /* Alert */
    .edu-alert {
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

    /* Form panel */
    .edu-form-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.5rem;
        margin-bottom: 1.75rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    }

    .edu-form-panel h3 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .edu-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    @media (max-width: 640px) {
        .edu-form-grid { grid-template-columns: 1fr; }
    }

    .edu-form-group label {
        display: block;
        font-size: 0.8rem;
        font-weight: 500;
        margin-bottom: 0.4rem;
        color: var(--text-muted);
    }

    .edu-form-group input,
    .edu-form-group textarea,
    .edu-form-group select {
        width: 100%;
        padding: 0.65rem 0.85rem;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border);
        font-size: 0.875rem;
        outline: none;
        transition: border-color 0.2s;
        font-family: inherit;
    }

    .edu-form-group input:focus,
    .edu-form-group textarea:focus,
    .edu-form-group select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
    }

    .edu-form-group textarea {
        min-height: 88px;
        resize: vertical;
    }

    .edu-form-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        margin-top: 1.25rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
    }

    /* Cards grid */
    .edu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.25rem;
    }

    .edu-card {
        background: var(--surface);
        border-radius: var(--radius-md);
        border: 1px solid var(--border);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: default;
    }

    .edu-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
    }

    .edu-card-cover {
        height: 120px;
        position: relative;
        display: flex;
        align-items: flex-end;
        padding: 1rem;
    }

    .edu-card-cover::before {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0.15;
        background-image: radial-gradient(circle at 80% 20%, white 0%, transparent 50%);
    }

    .edu-card-type {
        position: absolute;
        top: 0.85rem;
        right: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(4px);
        padding: 0.25rem 0.65rem;
        border-radius: 99px;
        font-size: 0.68rem;
        font-weight: 600;
        color: var(--text-main);
        z-index: 1;
    }

    .edu-card-kategori {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0.25rem 0.65rem;
        border-radius: 99px;
        font-size: 0.7rem;
        font-weight: 600;
        color: white;
        backdrop-filter: blur(4px);
    }

    .edu-card-body {
        padding: 1.25rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .edu-card-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 0.5rem;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .edu-card-desc {
        font-size: 0.82rem;
        color: var(--text-muted);
        line-height: 1.55;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex: 1;
    }

    .edu-card-footer {
        padding: 0.85rem 1.25rem;
        border-top: 1px solid var(--border);
        background: #fafbfc;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
    }

    .edu-card-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .edu-icon-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: var(--text-muted);
        transition: all 0.15s;
    }

    .edu-icon-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: var(--primary-light);
    }

    .edu-icon-btn.danger:hover {
        border-color: var(--danger);
        color: var(--danger);
        background: rgba(239, 68, 68, 0.08);
    }

    .edu-open-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 0.85rem;
        border-radius: 99px;
        background: var(--primary);
        color: white;
        border: none;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s, transform 0.15s;
    }

    .edu-open-btn:hover {
        background: var(--primary-hover);
        transform: scale(1.02);
    }

    /* Empty state */
    .edu-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 4rem 2rem;
        background: var(--surface);
        border-radius: var(--radius-md);
        border: 2px dashed var(--border);
    }

    .edu-empty-icon {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: var(--primary-light);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.25rem;
        color: var(--primary);
    }

    .edu-empty h3 {
        font-size: 1.1rem;
        margin-bottom: 0.4rem;
    }

    .edu-empty p {
        font-size: 0.875rem;
        margin-bottom: 1.25rem;
    }

    /* Modals */
    .edu-modal {
        background: white;
        width: 100%;
        max-width: 560px;
        max-height: 90vh;
        border-radius: var(--radius-md);
        overflow: hidden;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
    }

    .edu-modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
    }

    .edu-modal-header h3 {
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .edu-modal-close {
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

    .edu-modal-close:hover { background: var(--border); }

    .edu-modal-body {
        padding: 1.5rem;
        overflow-y: auto;
        max-height: calc(90vh - 60px);
    }

    /* Reader modal */
    .edu-reader {
        width: 100%;
        max-width: 760px;
        max-height: 90vh;
        border-radius: var(--radius-md);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    }

    .edu-reader--video {
        max-width: 920px;
        background: #000;
    }

    .edu-reader-header {
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
        border-bottom: 1px solid var(--border);
    }

    .edu-reader-header--dark {
        background: #111;
        border-bottom-color: #222;
        color: white;
    }

    .edu-reader-header h2 {
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .edu-reader-content {
        overflow-y: auto;
        flex: 1;
        padding: 2rem 2.5rem;
        line-height: 1.7;
        color: #334155;
    }

    .edu-reader-content h1 {
        font-size: 1.65rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 1.5rem;
        line-height: 1.3;
    }

    .edu-reader-content h3 {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--primary);
        margin: 1.75rem 0 0.65rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .edu-reader-content h3::before {
        content: '';
        width: 4px;
        height: 1.1rem;
        background: var(--primary);
        border-radius: 2px;
        flex-shrink: 0;
    }

    .edu-reader-content p {
        margin-bottom: 1rem;
        color: #475569;
    }

    .edu-reader-tip {
        background: linear-gradient(135deg, rgba(15, 118, 110, 0.08), rgba(99, 102, 241, 0.06));
        border-left: 3px solid var(--primary);
        padding: 0.85rem 1rem;
        border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
        margin: 1.5rem 0;
        font-size: 0.85rem;
        color: #334155;
    }

    .edu-video-wrap {
        position: relative;
        width: 100%;
        padding-bottom: 56.25%;
        height: 0;
        background: #000;
    }

    .edu-video-wrap iframe {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border: none;
    }

    /* Results info */
    .edu-results-info {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
<div class="edu-page animate-fade-in" x-data="edukasiData()">

    <x-hero-banner title="Edukasi Pekerja" description="Materi pelatihan, video tutorial, dan panduan praktis untuk meningkatkan keterampilan pekerja di bidang pertanian, lingkungan, kesehatan, dan kerajinan.">
        <x-slot:actions>
            <button class="global-hero-banner-btn-white" @click="showForm = !showForm">
                <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                <span x-text="showForm ? 'Tutup Form' : 'Tambah Materi'"></span>
            </button>
            <button class="global-hero-banner-btn-ghost" @click="searchTerm = ''; activeCategory = 'Semua'; activeType = 'Semua'">
                <i data-lucide="refresh-cw" style="width: 15px; height: 15px;"></i>
                Reset Filter
            </button>
        </x-slot:actions>
    </x-hero-banner>

    {{-- Stats --}}
    <div class="edu-stats">
        <div class="edu-stat">
            <div class="edu-stat-icon" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
                <i data-lucide="library" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
                <div class="edu-stat-label">Total Materi</div>
                <div class="edu-stat-value" x-text="contents.length"></div>
                <div class="edu-stat-sub">Konten edukasi tersedia</div>
            </div>
        </div>
        <div class="edu-stat">
            <div class="edu-stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                <i data-lucide="video" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
                <div class="edu-stat-label">Video Tutorial</div>
                <div class="edu-stat-value" x-text="countByType('Video')"></div>
                <div class="edu-stat-sub">Pembelajaran visual</div>
            </div>
        </div>
        <div class="edu-stat">
            <div class="edu-stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;">
                <i data-lucide="sprout" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
                <div class="edu-stat-label">Pertanian</div>
                <div class="edu-stat-value" x-text="countByCategory('Pertanian')"></div>
                <div class="edu-stat-sub">Materi bidang pertanian</div>
            </div>
        </div>
        <div class="edu-stat">
            <div class="edu-stat-icon" style="background: rgba(15, 118, 110, 0.1); color: #0f766e;">
                <i data-lucide="leaf" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
                <div class="edu-stat-label">Lingkungan</div>
                <div class="edu-stat-value" x-text="countByCategory('Lingkungan')"></div>
                <div class="edu-stat-sub">Kompos & keberlanjutan</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="edu-alert">
            <i data-lucide="check-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Form tambah --}}
    <div x-show="showForm" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="edu-form-panel">
        <h3>
            <i data-lucide="upload" style="width: 18px; height: 18px; color: var(--primary);"></i>
            Upload Materi Baru
        </h3>
        <form method="POST" action="{{ route('pengawas.edukasi.store') }}">
            @csrf
            <div class="edu-form-group" style="margin-bottom: 1rem;">
                <label>Judul Materi</label>
                <input type="text" name="judul" required placeholder="Contoh: Cara Menanam Sawi Organik" />
            </div>
            <div class="edu-form-group" style="margin-bottom: 1rem;">
                <label>Deskripsi Singkat</label>
                <textarea name="deskripsi" required placeholder="Jelaskan isi materi dan manfaatnya untuk pekerja..."></textarea>
            </div>
            <div class="edu-form-grid">
                <div class="edu-form-group">
                    <label>Kategori</label>
                    <select name="kategori">
                        <option value="Pertanian">Pertanian</option>
                        <option value="Lingkungan">Lingkungan</option>
                        <option value="Keterampilan">Keterampilan</option>
                        <option value="Kesehatan">Kesehatan</option>
                    </select>
                </div>
                <div class="edu-form-group">
                    <label>Tipe Konten</label>
                    <select name="tipe_konten">
                        <option value="Artikel">Artikel</option>
                        <option value="Video">Video</option>
                        <option value="Panduan PDF">Panduan PDF</option>
                    </select>
                </div>
            </div>
            <div class="edu-form-group" style="margin-top: 1rem;">
                <label>URL / Link Materi</label>
                <input type="text" name="url_konten" placeholder="https://... atau modal:tips-kesehatan" />
            </div>
            <div class="edu-form-actions">
                <button type="button" class="btn btn-outline" @click="showForm = false">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                    Simpan Konten
                </button>
            </div>
        </form>
    </div>

    {{-- Toolbar --}}
    <div class="edu-toolbar">
        <div class="edu-search">
            <i data-lucide="search" class="edu-search-icon" style="width: 17px; height: 17px;"></i>
            <input type="text" placeholder="Cari judul materi atau kategori..." x-model="searchTerm" />
        </div>
        <div class="edu-filters">
            <template x-for="cat in categories" :key="cat">
                <button type="button"
                    class="edu-filter-chip"
                    :class="{ active: activeCategory === cat }"
                    @click="activeCategory = cat">
                    <span x-text="cat"></span>
                    <span class="chip-count" x-text="cat === 'Semua' ? contents.length : countByCategory(cat)"></span>
                </button>
            </template>
        </div>
    </div>

    <div class="edu-filters" style="margin-bottom: 1rem;">
        <template x-for="type in types" :key="type">
            <button type="button"
                class="edu-filter-chip"
                :class="{ active: activeType === type }"
                @click="activeType = type">
                <i :data-lucide="typeIcon(type)" style="width: 13px; height: 13px;"></i>
                <span x-text="type"></span>
            </button>
        </template>
    </div>

    <p class="edu-results-info">
        Menampilkan <strong x-text="filteredContents.length"></strong> dari <strong x-text="contents.length"></strong> materi
    </p>

    {{-- Grid kartu --}}
    <div class="edu-grid">
        <template x-for="content in filteredContents" :key="content.id">
            <article class="edu-card">
                <div class="edu-card-cover" :style="'background: linear-gradient(135deg, ' + categoryColor(content.kategori).from + ', ' + categoryColor(content.kategori).to + ')'">
                    <span class="edu-card-type">
                        <i :data-lucide="typeIcon(content.tipe_konten)" style="width: 12px; height: 12px;"></i>
                        <span x-text="content.tipe_konten"></span>
                    </span>
                    <span class="edu-card-kategori">
                        <i :data-lucide="categoryIcon(content.kategori)" style="width: 12px; height: 12px;"></i>
                        <span x-text="content.kategori"></span>
                    </span>
                </div>
                <div class="edu-card-body">
                    <h3 class="edu-card-title" x-text="content.judul"></h3>
                    <p class="edu-card-desc" x-text="content.deskripsi"></p>
                </div>
                <div class="edu-card-footer">
                    <div class="edu-card-actions">
                        <button type="button" class="edu-icon-btn" @click="openEdit(content)" title="Edit">
                            <i data-lucide="pencil" style="width: 14px; height: 14px;"></i>
                        </button>
                        <form method="POST" :action="'/pengawas/edukasi/' + content.id" @submit="return confirm('Hapus materi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="edu-icon-btn danger" title="Hapus">
                                <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                            </button>
                        </form>
                    </div>
                    <button type="button" class="edu-open-btn" @click="handleOpenMaterial(content)">
                        <span x-text="content.url_konten?.startsWith('modal:') ? 'Baca Modul' : (content.tipe_konten === 'Video' ? 'Tonton' : 'Buka')"></span>
                        <i :data-lucide="content.url_konten?.startsWith('modal:') ? 'book-open' : (content.tipe_konten === 'Video' ? 'play' : 'external-link')" style="width: 13px; height: 13px;"></i>
                    </button>
                </div>
            </article>
        </template>

        <template x-if="filteredContents.length === 0">
            <div class="edu-empty">
                <div class="edu-empty-icon">
                    <i data-lucide="book-open" style="width: 32px; height: 32px;"></i>
                </div>
                <h3>Belum ada materi ditemukan</h3>
                <p x-text="contents.length === 0 ? 'Tambahkan materi edukasi pertama untuk pekerja.' : 'Coba ubah filter atau kata kunci pencarian.'"></p>
                <button class="btn btn-primary" @click="showForm = true; searchTerm = ''; activeCategory = 'Semua'; activeType = 'Semua'" x-show="contents.length === 0">
                    <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                    Tambah Materi Pertama
                </button>
            </div>
        </template>
    </div>

    {{-- Modal Edit --}}
    <div x-show="editContent" x-cloak class="modal-overlay modal-overlay--blur" style="padding: 1.5rem;" @click="editContent = null">
        <div class="edu-modal" @click.stop>
            <div class="edu-modal-header">
                <h3>
                    <i data-lucide="pencil" style="width: 18px; height: 18px; color: var(--primary);"></i>
                    Edit Materi Edukasi
                </h3>
                <button type="button" class="edu-modal-close" @click="editContent = null">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>
            <div class="edu-modal-body">
                <form method="POST" :action="'/pengawas/edukasi/' + editContent?.id">
                    @csrf
                    @method('PUT')
                    <div class="edu-form-group" style="margin-bottom: 1rem;">
                        <label>Judul Materi</label>
                        <input type="text" name="judul" required x-model="editContent.judul" />
                    </div>
                    <div class="edu-form-group" style="margin-bottom: 1rem;">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" required x-model="editContent.deskripsi"></textarea>
                    </div>
                    <div class="edu-form-grid">
                        <div class="edu-form-group">
                            <label>Kategori</label>
                            <select name="kategori" x-model="editContent.kategori">
                                <option value="Pertanian">Pertanian</option>
                                <option value="Lingkungan">Lingkungan</option>
                                <option value="Keterampilan">Keterampilan</option>
                                <option value="Kesehatan">Kesehatan</option>
                            </select>
                        </div>
                        <div class="edu-form-group">
                            <label>Tipe Konten</label>
                            <select name="tipe_konten" x-model="editContent.tipe_konten">
                                <option value="Artikel">Artikel</option>
                                <option value="Video">Video</option>
                                <option value="Panduan PDF">Panduan PDF</option>
                            </select>
                        </div>
                    </div>
                    <div class="edu-form-group" style="margin-top: 1rem;">
                        <label>URL / Link Materi</label>
                        <input type="text" name="url_konten" x-model="editContent.url_konten" />
                    </div>
                    <div class="edu-form-actions">
                        <button type="button" class="btn btn-outline" @click="editContent = null">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Reader / Video --}}
    <div x-show="activeModul" x-cloak class="modal-overlay modal-overlay--blur" style="padding: 1.5rem;" @click="activeModul = null">
        <div class="edu-reader" :class="{ 'edu-reader--video': activeModul?.startsWith('video:') }" @click.stop>
            <div class="edu-reader-header" :class="{ 'edu-reader-header--dark': activeModul?.startsWith('video:') }">
                <h2 :style="activeModul?.startsWith('video:') ? 'color: white' : ''">
                    <i :data-lucide="activeModul?.startsWith('video:') ? 'play-circle' : 'book-open'" :style="activeModul?.startsWith('video:') ? 'color: #ef4444' : 'color: var(--primary)'" style="width: 20px; height: 20px;"></i>
                    <span x-text="activeModul?.startsWith('video:') ? 'Pemutar Video Edukasi' : 'Modul Edukasi Pekerja'"></span>
                </h2>
                <button type="button" class="edu-modal-close" @click="activeModul = null" :style="activeModul?.startsWith('video:') ? 'color: #aaa' : ''">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>

            <template x-if="activeModul?.startsWith('video:')">
                <div class="edu-video-wrap">
                    <iframe
                        :src="'https://www.youtube.com/embed/' + activeModul.split(':')[1] + '?autoplay=1'"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                </div>
            </template>

            <div x-show="!activeModul?.startsWith('video:')" class="edu-reader-content">

                <div x-show="activeModul === 'menanam-sayur'" x-cloak>
                    <h1>Panduan Lengkap: Cara Menanam Sayur Organik di Pekarangan</h1>
                    <div class="edu-reader-tip">Modul ini dirancang untuk pekerja yang ingin memulai berkebun sayur organik di pekarangan rumah dengan biaya minimal.</div>
                    <h3>1. Persiapan Lahan dan Media Tanam</h3>
                    <p>Gunakan campuran tanah gembur, pupuk kompos, dan sekam bakar dengan perbandingan 1:1:1. Media tanam ini akan memastikan tanaman mendapatkan nutrisi organik yang cukup tanpa perlu pupuk kimia. Masukkan campuran ke dalam polybag atau bedengan kecil di pekarangan.</p>
                    <h3>2. Menyiapkan Bibit</h3>
                    <p>Pilih bibit sayuran yang mudah tumbuh seperti bayam, kangkung, pakcoy, atau sawi. Semai benih pada tray semai dengan kedalaman 1-2 cm. Simpan di tempat yang teduh dan siram dengan sprayer halus setiap pagi.</p>
                    <h3>3. Memindahkan Bibit Semai</h3>
                    <p>Ketika tanaman sudah memiliki 3-4 helai daun sejati (sekitar 10-14 hari), pindahkan bibit ke pot/polybag yang lebih besar atau bedengan. Lakukan pemindahan pada sore hari agar tanaman tidak layu tersengat matahari.</p>
                    <h3>4. Perawatan dan Panen</h3>
                    <p>Lakukan penyiraman 1-2 kali sehari sesuai cuaca. Untuk mencegah hama, semprotkan pestisida nabati secara rutin sekali seminggu. Sayuran daun biasanya sudah bisa dipanen pada umur 25-30 hari setelah tanam.</p>
                </div>

                <div x-show="activeModul === 'kerajinan'" x-cloak>
                    <h1>Keterampilan: Membuat Kerajinan Bernilai Jual dari Barang Bekas</h1>
                    <div class="edu-reader-tip">Daur ulang barang bekas tidak hanya mengurangi sampah, tapi juga bisa menjadi sumber pendapatan tambahan.</div>
                    <h3>A. Mengubah Botol Bekas Menjadi Pot Menggantung</h3>
                    <p>Potong botol plastik bekas air mineral menjadi dua bagian. Warnai botol dengan cat akrilik agar lebih menarik. Lubangi bagian sisi botol untuk memasang tali gantungan. Pot ini sangat cocok digunakan dengan metode taman vertikal (vertical garden) yang ditanami tanaman hias.</p>
                    <h3>B. Merajut Plastik Kresek Bekas Menjadi Tas Belanja</h3>
                    <p>Kumpulkan kantong plastik kresek bekas, cuci bersih lalu keringkan. Potong plastik tersebut memanjang dan sambungkan setiap utasnya membentuk tali/benang plastik (sering disebut benang plarn). Rajut menggunakan hakpen ukuran besar. Hasil akhir bisa berupa keranjang multifungsi atau tas belanja tahan air.</p>
                    <h3>C. Kerajinan Mosaik dari Pecahan Kaca/Keramik</h3>
                    <p>Pecahan keramik bekas rumah atau mangkuk pecah dapat diubah menjadi hiasan pot, tatakan gelas (coaster), atau meja. Gunakan lem keramik untuk merekatkan pecahan tersebut lalu lumuri celah dengan semen nat agar terlihat tertutup dan artistik bernilai jual tinggi.</p>
                </div>

                <div x-show="activeModul === 'tips-kompos'" x-cloak>
                    <h1>Tips & Trik: Membuat Kompos Kualitas Tinggi</h1>
                    <div class="edu-reader-tip">Kompos berkualitas tinggi = tanaman sehat + hasil panen melimpah + lingkungan bersih.</div>
                    <h3>1. Jaga Rasio Karbon dan Nitrogen</h3>
                    <p>Campurkan bahan kaya karbon (daun kering, ranting, serbuk gergaji) dengan bahan kaya nitrogen (sisa sayuran, buah, ampas kopi) dengan rasio 2:1 atau 3:1. Hal ini membantu mikroorganisme bekerja lebih cepat.</p>
                    <h3>2. Potong Kecil-kecil Bahan Kompos</h3>
                    <p>Semakin kecil ukuran bahan, semakin cepat proses dekomposisi terjadi. Cincang sisa sayur dan remukkan daun kering sebelum dimasukkan ke komposter.</p>
                    <h3>3. Jaga Kelembapan</h3>
                    <p>Kompos yang baik harus memiliki kelembapan seperti spons yang diperas. Jika terlalu kering, tambahkan air. Jika terlalu basah, tambahkan bahan cokelat (daun kering/kertas).</p>
                </div>

                <div x-show="activeModul === 'tips-air'" x-cloak>
                    <h1>Tips & Trik: Menghemat Air Pertanian</h1>
                    <div class="edu-reader-tip">Pengelolaan air yang efisien bisa menghemat hingga 50% penggunaan air tanpa mengurangi hasil panen.</div>
                    <h3>1. Gunakan Mulsa Organik</h3>
                    <p>Tutup permukaan tanah di sekitar tanaman dengan mulsa organik (jerami, daun kering, atau potongan rumput). Mulsa dapat menahan penguapan air dari tanah hingga 70%.</p>
                    <h3>2. Siram pada Pagi atau Sore Hari</h3>
                    <p>Hindari menyiram tanaman pada siang hari karena panas matahari akan menguapkan air sebelum diserap akar. Waktu terbaik adalah pagi hari sebelum pukul 09.00 atau sore hari setelah pukul 16.00.</p>
                    <h3>3. Gunakan Sistem Irigasi Tetes</h3>
                    <p>Sistem irigasi tetes sangat efisien karena memberikan air langsung ke akar tanaman secara perlahan. Ini mengurangi limpahan air dan penguapan secara signifikan dibandingkan penyiraman manual.</p>
                </div>

                <div x-show="activeModul === 'tips-kesehatan'" x-cloak>
                    <h1>Tips & Trik: Keselamatan Kerja Lapangan</h1>
                    <div class="edu-reader-tip">Kesehatan pekerja adalah aset terpenting. Keselamatan kerja harus selalu menjadi prioritas utama.</div>
                    <h3>1. Selalu Gunakan Alat Pelindung Diri (APD)</h3>
                    <p>Gunakan sarung tangan, sepatu bot tebal, dan topi lebar saat bekerja di lahan terbuka. Hal ini akan melindungi Anda dari benda tajam, gigitan serangga, dan sengatan matahari berlebih.</p>
                    <h3>2. Cukupi Kebutuhan Cairan (Hidrasi)</h3>
                    <p>Pekerjaan fisik mengeluarkan banyak keringat. Pastikan meminum air putih setidaknya satu gelas setiap jam untuk mencegah dehidrasi, lemas, atau heatstroke (sengatan panas).</p>
                    <h3>3. Lakukan Peregangan Berkala</h3>
                    <p>Lakukan peregangan sederhana setiap 2 jam bekerja untuk melemaskan otot, terutama setelah melakukan gerakan berulang seperti mencangkul atau memanen. Hindari mengangkat beban terlalu berat sendirian.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('edukasiData', () => ({
            showForm: false,
            searchTerm: '',
            activeCategory: 'Semua',
            activeType: 'Semua',
            categories: ['Semua', 'Pertanian', 'Lingkungan', 'Keterampilan', 'Kesehatan'],
            types: ['Semua', 'Artikel', 'Video', 'Panduan PDF'],
            contents: @json($contents),
            activeModul: null,
            editContent: null,

            get filteredContents() {
                return this.contents.filter(c => {
                    const matchSearch = this.searchTerm === '' ||
                        c.judul.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                        c.kategori.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                        c.deskripsi.toLowerCase().includes(this.searchTerm.toLowerCase());
                    const matchCategory = this.activeCategory === 'Semua' || c.kategori === this.activeCategory;
                    const matchType = this.activeType === 'Semua' || c.tipe_konten === this.activeType;
                    return matchSearch && matchCategory && matchType;
                });
            },

            countByCategory(cat) {
                return this.contents.filter(c => c.kategori === cat).length;
            },

            countByType(type) {
                return this.contents.filter(c => c.tipe_konten === type).length;
            },

            categoryColor(kategori) {
                const map = {
                    'Pertanian': { from: '#16a34a', to: '#22c55e' },
                    'Lingkungan': { from: '#0f766e', to: '#0891b2' },
                    'Keterampilan': { from: '#7c3aed', to: '#a78bfa' },
                    'Kesehatan': { from: '#e11d48', to: '#fb7185' },
                };
                return map[kategori] || { from: '#6366f1', to: '#818cf8' };
            },

            categoryIcon(kategori) {
                const map = {
                    'Pertanian': 'sprout',
                    'Lingkungan': 'leaf',
                    'Keterampilan': 'palette',
                    'Kesehatan': 'heart-pulse',
                };
                return map[kategori] || 'tag';
            },

            typeIcon(type) {
                if (type === 'Video') return 'video';
                if (type === 'Panduan PDF') return 'file-down';
                if (type === 'Semua') return 'layers';
                return 'file-text';
            },

            init() {
                this.$watch('filteredContents', () => setTimeout(() => lucide.createIcons(), 50));
                this.$watch('showForm', () => setTimeout(() => lucide.createIcons(), 50));
                this.$watch('activeCategory', () => setTimeout(() => lucide.createIcons(), 50));
                this.$watch('activeType', () => setTimeout(() => lucide.createIcons(), 50));
            },

            formatExternalUrl(url) {
                if (!url) return '#';
                if (url.startsWith('http://') || url.startsWith('https://')) return url;
                return `https://${url}`;
            },

            handleOpenMaterial(content) {
                if (content.url_konten && content.url_konten.startsWith('modal:')) {
                    this.activeModul = content.url_konten.split(':')[1];
                    setTimeout(() => lucide.createIcons(), 50);
                } else if (content.url_konten && content.url_konten.includes('youtube.com/watch?v=')) {
                    const videoId = content.url_konten.split('v=')[1]?.split('&')[0];
                    this.activeModul = `video:${videoId}`;
                    setTimeout(() => lucide.createIcons(), 50);
                } else {
                    const anchor = document.createElement('a');
                    anchor.href = this.formatExternalUrl(content.url_konten);
                    anchor.target = '_blank';
                    anchor.rel = 'noopener noreferrer';
                    anchor.click();
                }
            },

            openEdit(content) {
                this.editContent = { ...content };
                setTimeout(() => lucide.createIcons(), 50);
            }
        }));
    });
</script>
@endsection
