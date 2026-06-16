@extends('layouts.app')
@section('title', 'Manajemen Data Pekerja')

@php
    $totalWorkers = $workers->count();
    $aktifCount = $workers->where('status_program', 'aktif')->count();
    $sangatMiskinCount = $workers->where('status_kesejahteraan', 'Sangat Miskin')->count();
    $rentanMiskinCount = $workers->where('status_kesejahteraan', 'Rentan Miskin')->count();
    $tidakLayakCount = $workers->whereIn('status_program', ['tidak_layak', 'lulus'])->count();
    $accentColors = ['#0f766e', '#3b82f6', '#8b5cf6', '#f59e0b', '#ec4899', '#06b6d4'];
    $kategoriStyles = [
        'Sangat Miskin' => ['bg' => '#fef2f2', 'fg' => '#dc2626', 'border' => '#fecaca', 'icon' => 'alert-circle'],
        'Rentan Miskin' => ['bg' => '#fffbeb', 'fg' => '#d97706', 'border' => '#fde68a', 'icon' => 'alert-triangle'],
        'Pending' => ['bg' => '#eff6ff', 'fg' => '#2563eb', 'border' => '#bfdbfe', 'icon' => 'clock'],
        'Lulus/Tidak Layak' => ['bg' => '#f8fafc', 'fg' => '#64748b', 'border' => '#e2e8f0', 'icon' => 'user-x'],
    ];
@endphp

@push('styles')
<style>
    .pekerja-page {
        padding: 2rem;
        max-width: 1400px;
    }

    .pekerja-hero {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1.5rem;
        margin-bottom: 1.75rem;
        flex-wrap: wrap;
    }

    .pekerja-hero h1 {
        font-size: 1.85rem;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 0.35rem;
    }

    .pekerja-hero p {
        color: var(--text-muted);
        font-size: 0.95rem;
        max-width: 560px;
    }

    .pekerja-hero-actions {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
    }

    .pekerja-stats {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 0.85rem;
        margin-bottom: 1.5rem;
    }

    @media (max-width: 1100px) {
        .pekerja-stats { grid-template-columns: repeat(3, 1fr); }
    }

    @media (max-width: 640px) {
        .pekerja-stats { grid-template-columns: repeat(2, 1fr); }
    }

    .pekerja-stat {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .pekerja-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    }

    .pekerja-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .pekerja-stat-value {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--text-main);
        line-height: 1.1;
    }

    .pekerja-stat-label {
        font-size: 0.72rem;
        color: var(--text-muted);
        margin-top: 0.05rem;
    }

    .pekerja-info-banner {
        display: flex;
        gap: 0.85rem;
        padding: 1rem 1.15rem;
        margin-bottom: 1.5rem;
        background: linear-gradient(135deg, rgba(15, 118, 110, 0.06) 0%, rgba(59, 130, 246, 0.04) 100%);
        border: 1px solid rgba(15, 118, 110, 0.18);
        border-radius: var(--radius-md);
    }

    .pekerja-info-banner-icon {
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

    .pekerja-info-banner strong {
        display: block;
        font-size: 0.88rem;
        color: var(--text-main);
        margin-bottom: 0.25rem;
    }

    .pekerja-info-banner p {
        margin: 0;
        font-size: 0.82rem;
        line-height: 1.55;
    }

    .pekerja-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        padding: 0.85rem 1.15rem;
        margin-bottom: 1.25rem;
        border-radius: var(--radius-sm);
        font-size: 0.88rem;
    }

    .pekerja-alert--success {
        background: rgba(34, 197, 94, 0.08);
        border: 1px solid rgba(34, 197, 94, 0.25);
        color: #166534;
    }

    .pekerja-alert--error {
        background: rgba(239, 68, 68, 0.06);
        border: 1px solid rgba(239, 68, 68, 0.2);
        color: #991b1b;
    }

    /* Form panel */
    .pekerja-form-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        margin-bottom: 1.75rem;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    }

    .pekerja-form-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.15rem 1.5rem;
        background: linear-gradient(135deg, var(--primary) 0%, #0d9488 100%);
        color: white;
    }

    .pekerja-form-header h3 {
        margin: 0;
        font-size: 1.05rem;
        color: white;
    }

    .pekerja-form-header p {
        margin: 0.2rem 0 0;
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.85);
    }

    .pekerja-form-close {
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
    }

    .pekerja-form-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .pekerja-form-body {
        padding: 1.5rem;
    }

    .pekerja-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }

    @media (max-width: 768px) {
        .pekerja-form-grid { grid-template-columns: 1fr; }
    }

    .pekerja-form-section {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 0.85rem;
        background: var(--background);
        border-radius: var(--radius-sm);
        border-left: 3px solid var(--primary);
        margin-top: 0.25rem;
    }

    .pekerja-form-section span {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--primary);
    }

    .pekerja-form-footer {
        grid-column: 1 / -1;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        padding-top: 1rem;
        margin-top: 0.5rem;
        border-top: 1px solid var(--border);
    }

    /* List section */
    .pekerja-list-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        overflow: hidden;
    }

    .pekerja-list-header {
        padding: 1.15rem 1.35rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .pekerja-list-header h2 {
        font-size: 1.05rem;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .pekerja-list-header p {
        margin: 0.2rem 0 0;
        font-size: 0.78rem;
    }

    .pekerja-toolbar {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
        align-items: center;
        padding: 0.85rem 1.35rem;
        background: var(--background);
        border-bottom: 1px solid var(--border);
    }

    .pekerja-search {
        position: relative;
        flex: 1;
        min-width: 200px;
        max-width: 320px;
    }

    .pekerja-search i {
        position: absolute;
        left: 0.8rem;
        top: 50%;
        transform: translateY(-50%);
        width: 15px;
        height: 15px;
        color: var(--text-muted);
        pointer-events: none;
    }

    .pekerja-search input {
        width: 100%;
        padding: 0.55rem 0.85rem 0.55rem 2.35rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        background: var(--surface);
        font-size: 0.85rem;
        outline: none;
    }

    .pekerja-search input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
    }

    .pekerja-filters {
        display: flex;
        gap: 0.35rem;
        flex-wrap: wrap;
    }

    .pekerja-filter {
        padding: 0.35rem 0.7rem;
        font-size: 0.75rem;
        border-radius: 999px;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.15s;
        font-weight: 500;
    }

    .pekerja-filter.is-active {
        background: var(--primary-light);
        border-color: var(--primary);
        color: var(--primary);
        font-weight: 600;
    }

    .pekerja-list {
        padding: 0.75rem;
    }

    .pekerja-row {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 1rem;
        align-items: center;
        padding: 1rem 1.1rem;
        border-radius: var(--radius-sm);
        border: 1px solid transparent;
        transition: all 0.2s;
        margin-bottom: 0.35rem;
    }

    .pekerja-row:hover {
        background: var(--background);
        border-color: var(--border);
    }

    @media (max-width: 900px) {
        .pekerja-row {
            grid-template-columns: auto 1fr;
        }
        .pekerja-row-actions {
            grid-column: 1 / -1;
            justify-content: flex-start !important;
        }
    }

    .pekerja-avatar {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: 700;
        color: white;
        flex-shrink: 0;
    }

    .pekerja-row-main {
        min-width: 0;
    }

    .pekerja-row-top {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 0.3rem;
    }

    .pekerja-row-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .pekerja-row-id {
        font-size: 0.68rem;
        color: var(--text-muted);
        background: var(--background);
        padding: 0.1rem 0.45rem;
        border-radius: 4px;
        font-weight: 600;
    }

    .pekerja-row-meta {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .pekerja-skill-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.15rem 0.55rem;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 600;
        background: rgba(34, 197, 94, 0.08);
        color: #15803d;
        border: 1px solid rgba(34, 197, 94, 0.2);
    }

    .pekerja-score {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.6rem;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 700;
        background: var(--primary-light);
        color: var(--primary);
    }

    .pekerja-kategori {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 600;
        border: 1px solid;
    }

    .pekerja-status-badge {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.15rem 0.5rem;
        border-radius: 6px;
        background: var(--background);
        color: var(--text-muted);
    }

    .pekerja-status-badge--aktif {
        background: rgba(34, 197, 94, 0.1);
        color: #15803d;
    }

    .pekerja-row-actions {
        display: flex;
        gap: 0.35rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .pekerja-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.4rem 0.7rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text-main);
        cursor: pointer;
        text-decoration: none;
        transition: all 0.15s;
        white-space: nowrap;
    }

    .pekerja-action-btn:hover {
        background: var(--background);
        border-color: var(--text-muted);
    }

    .pekerja-action-btn--primary {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .pekerja-action-btn--primary:hover {
        background: var(--primary-hover);
        border-color: var(--primary-hover);
    }

    .pekerja-empty {
        text-align: center;
        padding: 3.5rem 2rem;
    }

    .pekerja-empty-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1rem;
        background: var(--primary-light);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
    }

    .pekerja-no-results {
        text-align: center;
        padding: 2.5rem;
        color: var(--text-muted);
        font-size: 0.88rem;
    }

    /* Modal */
    .pekerja-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(4px);
        z-index: 100;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .pekerja-modal {
        width: 100%;
        max-width: 560px;
        max-height: 90vh;
        overflow-y: auto;
        background: var(--surface);
        border-radius: var(--radius-md);
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.18);
        border: 1px solid var(--border);
    }

    .pekerja-modal-header {
        padding: 1.35rem 1.5rem 0;
    }

    .pekerja-modal-header h3 {
        font-size: 1.1rem;
        margin-bottom: 0.25rem;
    }

    .pekerja-modal-body {
        padding: 1.25rem 1.5rem 1.5rem;
    }

    .pekerja-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        padding-top: 1rem;
        margin-top: 0.5rem;
        border-top: 1px solid var(--border);
    }
</style>
@endpush

@section('content')
<div class="animate-fade-in pekerja-page" x-data="pekerjaData()">
    <x-hero-banner title="Data Pekerja" description="Profiling & pendaftaran pekerja berbasis skoring BPS/Kemensos — prioritas penugasan dari skor tertinggi.">
        <x-slot:actions>
            <a href="/admin/keluarga" class="global-hero-banner-btn-ghost">
                <i data-lucide="home" style="width: 16px; height: 16px;"></i>
                Data Keluarga
            </a>
            <button class="global-hero-banner-btn-white" @click="openAddForm()">
                <i data-lucide="clipboard-list" style="width: 16px; height: 16px;"></i>
                Survei Profiling Baru
            </button>
        </x-slot:actions>
    </x-hero-banner>

    {{-- Stats --}}
    <div class="pekerja-stats">
        <div class="pekerja-stat">
            <div class="pekerja-stat-icon" style="background: rgba(15, 118, 110, 0.12); color: var(--primary);">
                <i data-lucide="users" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div class="pekerja-stat-value">{{ $totalWorkers }}</div>
                <div class="pekerja-stat-label">Total Pekerja</div>
            </div>
        </div>
        <div class="pekerja-stat">
            <div class="pekerja-stat-icon" style="background: rgba(34, 197, 94, 0.12); color: var(--success);">
                <i data-lucide="user-check" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div class="pekerja-stat-value">{{ $aktifCount }}</div>
                <div class="pekerja-stat-label">Aktif Program</div>
            </div>
        </div>
        <div class="pekerja-stat">
            <div class="pekerja-stat-icon" style="background: rgba(239, 68, 68, 0.1); color: var(--danger);">
                <i data-lucide="alert-circle" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div class="pekerja-stat-value">{{ $sangatMiskinCount }}</div>
                <div class="pekerja-stat-label">Sangat Miskin (P1)</div>
            </div>
        </div>
        <div class="pekerja-stat">
            <div class="pekerja-stat-icon" style="background: rgba(245, 158, 11, 0.12); color: var(--warning);">
                <i data-lucide="alert-triangle" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div class="pekerja-stat-value">{{ $rentanMiskinCount }}</div>
                <div class="pekerja-stat-label">Rentan Miskin (P2)</div>
            </div>
        </div>
        <div class="pekerja-stat">
            <div class="pekerja-stat-icon" style="background: rgba(100, 116, 139, 0.12); color: #64748b;">
                <i data-lucide="user-x" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div class="pekerja-stat-value">{{ $tidakLayakCount }}</div>
                <div class="pekerja-stat-label">Lulus / Tidak Layak</div>
            </div>
        </div>
    </div>

    {{-- Info banner --}}
    <div class="pekerja-info-banner">
        <div class="pekerja-info-banner-icon">
            <i data-lucide="info" style="width: 18px; height: 18px;"></i>
        </div>
        <div>
            <strong>Threshold Kelayakan Program</strong>
            <p>
                Skor &gt; 10 = <strong>Sangat Miskin</strong> (Prioritas 1) · Skor 7–10 = <strong>Rentan Miskin</strong> (Prioritas 2) · Skor &lt; 7 = <strong>Tidak Layak</strong>.
                Lengkapi data keluarga di <a href="/admin/keluarga" style="color: var(--primary); font-weight: 600;">Profiling Keluarga</a> agar skor pendapatan akurat.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="pekerja-alert pekerja-alert--success">
            <i data-lucide="check-circle" style="width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px;"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="pekerja-alert pekerja-alert--error">
            <i data-lucide="alert-circle" style="width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px;"></i>
            <div>
                <strong>Validasi gagal — data form tetap tersimpan:</strong>
                <ul style="margin: 0.5rem 0 0 1rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Form panel --}}
    <div x-show="showForm" x-cloak class="pekerja-form-panel">
        <div class="pekerja-form-header">
            <div>
                <h3 x-text="editMode ? 'Edit Data Pekerja' : 'Form Survei Profiling Kesejahteraan'"></h3>
                <p x-show="!editMode">Isi indikator makan, sanitasi, pendapatan, dan pendidikan. Skor dihitung otomatis.</p>
                <p x-show="editMode">Perbarui data identitas dan indikator profiling pekerja.</p>
            </div>
            <button type="button" class="pekerja-form-close" @click="showForm = false" title="Tutup form">
                <i data-lucide="x" style="width: 18px; height: 18px;"></i>
            </button>
        </div>
        <div class="pekerja-form-body">
            <form method="POST"
                  :action="editMode ? '/admin/pekerja/' + workerData.id : '/admin/pekerja'"
                  enctype="multipart/form-data"
                  class="pekerja-form-grid">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="pekerja-form-section">
                    <i data-lucide="user" style="width: 15px; height: 15px; color: var(--primary);"></i>
                    <span>A. Identitas Dasar</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-input" required x-model="workerData.nama" placeholder="Nama lengkap pekerja" />
                </div>
                <div class="form-group">
                    <label class="form-label">Keluarga (untuk skor pendapatan)</label>
                    <select name="household_id" class="form-input" x-model="workerData.household_id">
                        <option value="">- Tanpa Kepala Keluarga -</option>
                        @foreach($households as $h)
                            <option value="{{ $h->id }}">{{ $h->kepala_keluarga }} ({{ $h->rt_rw }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-input" x-model="workerData.tanggal_lahir" />
                    <small x-show="computedUsia !== null" style="display: block; margin-top: 0.35rem; color: var(--text-muted);">
                        Usia: <span x-text="computedUsia"></span> tahun
                    </small>
                </div>
                <div class="form-group">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-input" x-model="workerData.jenis_kelamin">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">No. HP / WhatsApp</label>
                    <input type="text" name="no_telepon" class="form-input" placeholder="08xxx (Kosongkan jika tidak ada)" x-model="workerData.no_telepon" />
                </div>
                <div class="form-group">
                    <label class="form-label">Kontak Darurat / Tetangga</label>
                    <input type="text" name="kontak_darurat" class="form-input" placeholder="Nama & No. HP" x-model="workerData.kontak_darurat" />
                </div>

                <div class="pekerja-form-section">
                    <i data-lucide="banknote" style="width: 15px; height: 15px; color: var(--primary);"></i>
                    <span>Pendapatan Program (Tunai)</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Total Pendapatan (Rp)</label>
                    <input type="number" name="total_pendapatan" class="form-input" min="0" step="1000" placeholder="Total uang tunai dari insentif/reward" x-model="workerData.total_pendapatan" />
                    <small style="color: var(--text-muted);">Akumulasi pendapatan tunai pekerja dari program insentif & reward.</small>
                </div>

                <div class="pekerja-form-section">
                    <i data-lucide="bar-chart-3" style="width: 15px; height: 15px; color: var(--primary);"></i>
                    <span>B. Indikator Profiling (Skoring Otomatis)</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Frekuensi Makan / Hari <span style="color:#ef4444">*</span> <small>(1x=3 poin, 3x+=1 poin)</small></label>
                    <select name="frekuensi_makan" class="form-input" required x-model="workerData.frekuensi_makan">
                        <option value="">— Pilih —</option>
                        <option value="1 kali">1 kali sehari (skor 3)</option>
                        <option value="2 kali">2 kali sehari (skor 2)</option>
                        <option value="3 kali atau lebih">3 kali atau lebih (skor 1)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Kondisi Sanitasi / Jamban <span style="color:#ef4444">*</span></label>
                    <select name="kondisi_sanitasi" class="form-input" required x-model="workerData.kondisi_sanitasi">
                        <option value="">— Pilih —</option>
                        <option value="Tidak Ada Jamban">Tidak Ada Jamban (skor 3)</option>
                        <option value="Jamban Bersama">Jamban Bersama (skor 2)</option>
                        <option value="Jamban Sendiri">Jamban Sendiri (skor 1)</option>
                        <option value="Jamban Sendiri + Septic Tank">Jamban Sendiri + Septic Tank (skor 0)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Pendidikan Terakhir <span style="color:#ef4444">*</span></label>
                    <select name="pendidikan_terakhir" class="form-input" required x-model="workerData.pendidikan_terakhir">
                        <option value="">— Pilih —</option>
                        <option value="Tidak Sekolah">Tidak Sekolah (skor 3)</option>
                        <option value="SD / Sederajat">SD / Sederajat (skor 2)</option>
                        <option value="SMP / Sederajat">SMP / Sederajat (skor 2)</option>
                        <option value="SMA / Sederajat">SMA / Sederajat (skor 1)</option>
                        <option value="Diploma / S1+">Diploma / S1+ (skor 0)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Gizi (SDG 2)</label>
                    <select name="status_gizi" class="form-input" x-model="workerData.status_gizi">
                        <option value="">— Pilih —</option>
                        <option value="Buruk">Buruk</option>
                        <option value="Kurang">Kurang</option>
                        <option value="Normal">Normal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Akses Air Bersih</label>
                    <select name="akses_air_bersih" class="form-input" x-model="workerData.akses_air_bersih">
                        <option value="">— Pilih —</option>
                        <option value="Tidak Ada">Tidak Ada</option>
                        <option value="Sumur / Mata Air">Sumur / Mata Air</option>
                        <option value="PAM / PDAM">PAM / PDAM</option>
                        <option value="Air Kemasan">Air Kemasan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Bidang Kerja / Kemampuan Utama <span style="color:#ef4444">*</span></label>
                    <select name="bidang_kerja_select" class="form-input" x-model="bidangKerjaSelect" @change="onBidangChange()" required>
                        <option value="">— Pilih bidang —</option>
                        <option value="Pertanian">Pertanian</option>
                        <option value="Pengelolaan Sampah">Pengelolaan Sampah</option>
                        <option value="Kerajinan Tangan">Kerajinan Tangan</option>
                        <option value="Pertukangan">Pertukangan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    <input type="hidden" name="kemampuan_utama" :value="resolvedKemampuanUtama">
                    <div x-show="bidangKerjaSelect === 'Lainnya'" x-cloak style="margin-top: 0.75rem;">
                        <input type="text" class="form-input" placeholder="Tulis bidang kerja kustom, contoh: Menjahit, Supir..." x-model="bidangKerjaLainnya" @input="syncKemampuanUtama()" />
                    </div>
                </div>
                <div class="form-group" x-show="!editMode">
                    <label class="form-label">Bukti Foto Kondisi Lapangan</label>
                    <input type="file" name="bukti_foto_kondisi" class="form-input" accept="image/*" />
                    <small style="color: var(--text-muted);">Opsional, max 5MB — foto rumah/kondisi sanitasi</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Keluarga</label>
                    <select name="status_keluarga" class="form-input" x-model="workerData.status_keluarga">
                        <option value="Kepala Keluarga">Kepala Keluarga</option>
                        <option value="Anggota Keluarga">Anggota Keluarga</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Kepemilikan Rumah</label>
                    <select name="status_rumah" class="form-input" x-model="workerData.status_rumah">
                        <option value="Milik Sendiri">Milik Sendiri</option>
                        <option value="Kontrak">Kontrak / Sewa</option>
                        <option value="Tidak Ada">Tidak Ada (Gelandangan/Numpang)</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Riwayat Penyakit (Jika ada)</label>
                    <textarea name="riwayat_penyakit" class="form-input" rows="2" placeholder="Sebutkan riwayat penyakit..." x-model="workerData.riwayat_penyakit"></textarea>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Alamat Lengkap</label>
                    <textarea name="alamat" class="form-input" rows="2" x-model="workerData.alamat"></textarea>
                </div>
                <div class="pekerja-form-footer">
                    <button type="button" class="btn btn-outline" @click="showForm = false">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                        <span x-text="editMode ? 'Perbarui Data' : 'Simpan Survei Profiling'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Worker list --}}
    <div class="pekerja-list-panel">
        <div class="pekerja-list-header">
            <div>
                <h2>
                    <i data-lucide="list-ordered" style="width: 20px; height: 20px; color: var(--primary);"></i>
                    Daftar Pekerja
                </h2>
                <p>Diurutkan berdasarkan total skor tertinggi — prioritas penugasan otomatis</p>
            </div>
            <span style="font-size: 0.82rem; color: var(--text-muted);">{{ $totalWorkers }} pekerja</span>
        </div>

        @if($workers->isNotEmpty())
            <div class="pekerja-toolbar">
                <div class="pekerja-search">
                    <i data-lucide="search"></i>
                    <input type="search" placeholder="Cari nama atau keahlian..." x-model="listSearch" @input="checkListResults()">
                </div>
                <div class="pekerja-filters">
                    <button type="button" class="pekerja-filter" :class="{ 'is-active': listFilter === 'all' }" @click="listFilter = 'all'; checkListResults()">Semua</button>
                    <button type="button" class="pekerja-filter" :class="{ 'is-active': listFilter === 'aktif' }" @click="listFilter = 'aktif'; checkListResults()">Aktif</button>
                    <button type="button" class="pekerja-filter" :class="{ 'is-active': listFilter === 'sangat_miskin' }" @click="listFilter = 'sangat_miskin'; checkListResults()">P1</button>
                    <button type="button" class="pekerja-filter" :class="{ 'is-active': listFilter === 'rentan_miskin' }" @click="listFilter = 'rentan_miskin'; checkListResults()">P2</button>
                    <button type="button" class="pekerja-filter" :class="{ 'is-active': listFilter === 'lulus' }" @click="listFilter = 'lulus'; checkListResults()">Lulus/Tidak Layak</button>
                </div>
            </div>
        @endif

        <div class="pekerja-list">
            @forelse($workers as $index => $w)
                @php
                    $initials = collect(explode(' ', $w->nama))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->join('');
                    $kategori = $w->status_kesejahteraan ?? 'Pending';
                    $ks = $kategoriStyles[$kategori] ?? $kategoriStyles['Pending'];
                    $keahlian = $w->keahlian_kerja ?: $w->kemampuan_utama ?: 'Umum';
                    $skor = $w->total_skor ?? $w->skor_vulnerabilitas ?? '—';
                @endphp
                <div class="pekerja-row"
                     data-search="{{ strtolower($w->nama . ' ' . $keahlian . ' ' . $kategori) }}"
                     data-status="{{ $w->status_program }}"
                     data-kategori="{{ \Illuminate\Support\Str::slug($kategori, '_') }}"
                     x-show="rowVisible($el)"
                >
                    <div class="pekerja-avatar" style="background: {{ $accentColors[$index % count($accentColors)] }};">
                        {{ strtoupper($initials) }}
                    </div>
                    <div class="pekerja-row-main">
                        <div class="pekerja-row-top">
                            <span class="pekerja-row-name">{{ $w->nama }}</span>
                            <span class="pekerja-row-id">#{{ $w->id }}</span>
                        </div>
                        <div class="pekerja-row-meta">
                            <span class="pekerja-skill-chip">
                                <i data-lucide="briefcase" style="width: 11px; height: 11px;"></i>
                                {{ $keahlian }}
                            </span>
                            <span class="pekerja-score">
                                <i data-lucide="target" style="width: 12px; height: 12px;"></i>
                                Skor {{ $skor }}
                            </span>
                            <span class="pekerja-kategori" style="background: {{ $ks['bg'] }}; color: {{ $ks['fg'] }}; border-color: {{ $ks['border'] }};">
                                <i data-lucide="{{ $ks['icon'] }}" style="width: 11px; height: 11px;"></i>
                                {{ $kategori }}
                            </span>
                            <span class="pekerja-status-badge {{ $w->status_program === 'aktif' ? 'pekerja-status-badge--aktif' : '' }}">
                                {{ $w->status_program_label ?? 'Aktif' }}
                            </span>
                        </div>
                    </div>
                    <div class="pekerja-row-actions">
                        <a href="/admin/pekerja/{{ $w->id }}/profil" class="pekerja-action-btn pekerja-action-btn--primary">
                            <i data-lucide="user" style="width: 13px; height: 13px;"></i>
                            Profil
                        </a>
                        @if($w->status_program === 'aktif')
                            <button type="button" class="pekerja-action-btn"
                                @click="openUpdateProfiling({{ $w->id }}, @js($w->nama), @js($w->frekuensi_makan), @js($w->kondisi_sanitasi), @js($w->pendidikan_terakhir), @js($w->status_gizi))">
                                <i data-lucide="refresh-cw" style="width: 13px; height: 13px;"></i>
                                Update
                            </button>
                            <form method="POST" action="{{ route('admin.profiling.lulus', $w->id) }}" style="display: inline;" onsubmit="return confirm('Tandai {{ $w->nama }} lulus program?');">
                                @csrf
                                <button type="submit" class="pekerja-action-btn">
                                    <i data-lucide="graduation-cap" style="width: 13px; height: 13px;"></i>
                                    Lulus
                                </button>
                            </form>
                        @endif
                        <button type="button" class="pekerja-action-btn" @click="handleEdit({{ $w->id }})">
                            <i data-lucide="pencil" style="width: 13px; height: 13px;"></i>
                            Edit
                        </button>
                    </div>
                </div>
            @empty
                <div class="pekerja-empty">
                    <div class="pekerja-empty-icon">
                        <i data-lucide="clipboard-list" style="width: 32px; height: 32px;"></i>
                    </div>
                    <h3 style="margin-bottom: 0.5rem;">Belum Ada Data Survei</h3>
                    <p style="margin-bottom: 1.25rem;">Mulai dengan menambahkan pekerja pertama melalui form survei profiling.</p>
                    <button class="btn btn-primary" @click="openAddForm()">
                        <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                        Survei Profiling Baru
                    </button>
                </div>
            @endforelse

            <div class="pekerja-no-results" x-show="noListResults" x-cloak>
                <i data-lucide="search-x" style="width: 28px; height: 28px; color: var(--text-muted); margin-bottom: 0.5rem;"></i>
                <p style="margin: 0;">Tidak ada pekerja yang cocok dengan filter.</p>
            </div>
        </div>
    </div>

    {{-- Modal Update Profiling --}}
    <div x-show="showUpdateProfiling" x-cloak class="pekerja-modal-backdrop" @keydown.escape.window="showUpdateProfiling = false">
        <div class="pekerja-modal" @click.outside="showUpdateProfiling = false">
            <div class="pekerja-modal-header">
                <h3>Update Profiling — <span x-text="updateWorkerName"></span></h3>
                <p style="color: var(--text-muted); font-size: 0.85rem;">Survei ulang untuk memantau progres kesejahteraan (mis. makan 1x → 3x).</p>
            </div>
            <div class="pekerja-modal-body">
                <form :action="'/admin/profiling/' + updateWorkerId + '/update'" method="POST" enctype="multipart/form-data" style="display: grid; gap: 1rem;">
                    @csrf
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Frekuensi Makan / Hari *</label>
                        <select name="frekuensi_makan" class="form-input" required x-model="updateData.frekuensi_makan">
                            <option value="1 kali">1 kali sehari</option>
                            <option value="2 kali">2 kali sehari</option>
                            <option value="3 kali atau lebih">3 kali atau lebih</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Kondisi Sanitasi *</label>
                        <select name="kondisi_sanitasi" class="form-input" required x-model="updateData.kondisi_sanitasi">
                            <option value="Tidak Ada Jamban">Tidak Ada Jamban</option>
                            <option value="Jamban Bersama">Jamban Bersama</option>
                            <option value="Jamban Sendiri">Jamban Sendiri</option>
                            <option value="Jamban Sendiri + Septic Tank">Jamban Sendiri + Septic Tank</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Pendidikan Terakhir *</label>
                        <select name="pendidikan_terakhir" class="form-input" required x-model="updateData.pendidikan_terakhir">
                            <option value="Tidak Sekolah">Tidak Sekolah</option>
                            <option value="SD / Sederajat">SD / Sederajat</option>
                            <option value="SMP / Sederajat">SMP / Sederajat</option>
                            <option value="SMA / Sederajat">SMA / Sederajat</option>
                            <option value="Diploma / S1+">Diploma / S1+</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Status Gizi</label>
                        <select name="status_gizi" class="form-input" x-model="updateData.status_gizi">
                            <option value="">— Tidak diubah —</option>
                            <option value="Buruk">Buruk</option>
                            <option value="Kurang">Kurang</option>
                            <option value="Normal">Normal</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Bukti Foto Kondisi</label>
                        <input type="file" name="bukti_foto_kondisi" class="form-input" accept="image/*" />
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Catatan Pemantauan</label>
                        <textarea name="catatan" class="form-input" rows="2" placeholder="Contoh: Frekuensi makan meningkat setelah program gizi desa."></textarea>
                    </div>
                    <div class="pekerja-modal-footer">
                        <button type="button" class="btn btn-outline" @click="showUpdateProfiling = false">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                            Simpan Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('pekerjaData', () => ({
            showForm: false,
            showUpdateProfiling: false,
            editMode: false,
            updateWorkerId: null,
            updateWorkerName: '',
            updateData: { frekuensi_makan: '', kondisi_sanitasi: '', pendidikan_terakhir: '', status_gizi: '' },
            workerData: {},
            bidangKerjaSelect: '',
            bidangKerjaLainnya: '',
            listSearch: '',
            listFilter: 'all',
            noListResults: false,

            get resolvedKemampuanUtama() {
                if (this.bidangKerjaSelect === 'Lainnya') {
                    return this.bidangKerjaLainnya.trim();
                }
                return this.bidangKerjaSelect;
            },

            rowVisible(el) {
                const q = this.listSearch.trim().toLowerCase();
                const searchOk = !q || (el.dataset.search || '').includes(q);
                if (!searchOk) return false;

                if (this.listFilter === 'all') return true;
                if (this.listFilter === 'aktif') return el.dataset.status === 'aktif';
                if (this.listFilter === 'sangat_miskin') return el.dataset.kategori === 'sangat_miskin';
                if (this.listFilter === 'rentan_miskin') return el.dataset.kategori === 'rentan_miskin';
                if (this.listFilter === 'lulus') return ['lulus', 'tidak_layak'].includes(el.dataset.status);
                return true;
            },

            checkListResults() {
                this.$nextTick(() => {
                    const rows = this.$el.querySelectorAll('.pekerja-row');
                    const anyVisible = [...rows].some(r => r.offsetParent !== null);
                    this.noListResults = (this.listSearch.trim().length > 0 || this.listFilter !== 'all') && !anyVisible;
                });
            },

            onBidangChange() {
                if (this.bidangKerjaSelect !== 'Lainnya') {
                    this.bidangKerjaLainnya = '';
                }
                this.syncKemampuanUtama();
            },

            syncKemampuanUtama() {
                this.workerData.kemampuan_utama = this.resolvedKemampuanUtama;
            },

            setBidangFromValue(value) {
                const standar = ['Pertanian', 'Pengelolaan Sampah', 'Kerajinan Tangan', 'Pertukangan'];
                if (standar.includes(value)) {
                    this.bidangKerjaSelect = value;
                    this.bidangKerjaLainnya = '';
                } else if (value) {
                    this.bidangKerjaSelect = 'Lainnya';
                    this.bidangKerjaLainnya = value;
                } else {
                    this.bidangKerjaSelect = '';
                    this.bidangKerjaLainnya = '';
                }
                this.syncKemampuanUtama();
            },

            defaultWorkerData() {
                return {
                    nama: '', household_id: '', tanggal_lahir: '', jenis_kelamin: 'L',
                    no_telepon: '', kontak_darurat: '', total_pendapatan: 0, kemampuan_utama: '',
                    pendidikan_terakhir: '', frekuensi_makan: '', status_gizi: '',
                    kondisi_sanitasi: '', akses_air_bersih: '', kebiasaan: '',
                    status_keluarga: 'Kepala Keluarga', status_rumah: 'Milik Sendiri',
                    riwayat_penyakit: '', alamat: ''
                };
            },

            get computedUsia() {
                if (!this.workerData.tanggal_lahir) return null;
                const birth = new Date(this.workerData.tanggal_lahir);
                if (Number.isNaN(birth.getTime())) return null;
                const today = new Date();
                let age = today.getFullYear() - birth.getFullYear();
                const monthDiff = today.getMonth() - birth.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) age--;
                return age >= 0 ? age : null;
            },

            init() {
                this.workerData = this.defaultWorkerData();

                const oldInput = @json(old());
                if (oldInput && Object.keys(oldInput).length > 0) {
                    Object.keys(oldInput).forEach(key => {
                        if (key in this.workerData) {
                            this.workerData[key] = oldInput[key] ?? '';
                        }
                    });
                    if (this.workerData.household_id === null) this.workerData.household_id = '';
                    this.showForm = true;
                    this.editMode = false;
                }

                const params = new URLSearchParams(window.location.search);
                const editId = params.get('edit');
                if (editId) this.handleEdit(editId);
            },

            openAddForm() {
                this.editMode = false;
                this.workerData = this.defaultWorkerData();
                this.setBidangFromValue('');
                this.showForm = true;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            openUpdateProfiling(id, nama, makan, sanitasi, pendidikan, gizi) {
                this.updateWorkerId = id;
                this.updateWorkerName = nama;
                this.updateData = {
                    frekuensi_makan: makan || '1 kali',
                    kondisi_sanitasi: sanitasi || 'Tidak Ada Jamban',
                    pendidikan_terakhir: pendidikan || 'Tidak Sekolah',
                    status_gizi: gizi || ''
                };
                this.showUpdateProfiling = true;
            },

            async handleEdit(id) {
                try {
                    const res = await fetch(`/api/workers/${id}`, { headers: { 'Accept': 'application/json' } });
                    if (res.ok) {
                        this.workerData = { ...this.defaultWorkerData(), ...(await res.json()) };
                        if (this.workerData.household_id === null) this.workerData.household_id = '';
                        this.setBidangFromValue(this.workerData.kemampuan_utama || '');
                        this.editMode = true;
                        this.showForm = true;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                } catch (err) {
                    console.error(err);
                }
            }
        }));
    });
</script>
@endsection
