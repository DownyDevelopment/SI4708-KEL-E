@extends('layouts.app')
@section('title', 'Ekonomi & Insentif')

@php
    $ekonomiPrefix = request()->is('pengawas/*') ? '/pengawas' : '/admin';
    $isAdmin = auth()->user()->role === 'admin';
    $pendingCount = $pendingLogbooks->count();
    $jenisTotal = max(1, $jenisStats->sum('total'));
@endphp

@push('styles')
<style>
    .eko-page {
        padding: 2rem;
        max-width: 1440px;
        margin: 0 auto;
    }

    /* Hero */
    .eko-hero {
        background: linear-gradient(135deg, #92400e 0%, #d97706 45%, #0f766e 100%);
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
        box-shadow: 0 12px 40px rgba(217, 119, 6, 0.22);
    }

    .eko-hero::before {
        content: '';
        position: absolute;
        top: -40%;
        right: -5%;
        width: 340px;
        height: 340px;
        background: rgba(255, 255, 255, 0.07);
        border-radius: 50%;
        pointer-events: none;
    }

    .eko-hero-content { position: relative; z-index: 1; }

    .eko-hero-badge {
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

    .eko-hero h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        margin: 0 0 0.4rem;
    }

    .eko-hero p {
        color: rgba(255, 255, 255, 0.88);
        font-size: 0.92rem;
        margin: 0;
        max-width: 540px;
        line-height: 1.55;
    }

    .eko-hero-actions {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
        flex-shrink: 0;
    }

    .eko-btn-white {
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
        text-decoration: none;
        transition: transform 0.15s, box-shadow 0.15s;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
    }

    .eko-btn-white:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    /* Alerts */
    .eko-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        padding: 0.85rem 1.15rem;
        border-radius: var(--radius-md);
        margin-bottom: 1.25rem;
        font-size: 0.88rem;
    }

    .eko-alert--success {
        background: rgba(34, 197, 94, 0.08);
        border: 1px solid rgba(34, 197, 94, 0.25);
        color: #15803d;
    }

    .eko-alert--error {
        background: rgba(239, 68, 68, 0.06);
        border: 1px solid rgba(239, 68, 68, 0.2);
        color: #991b1b;
    }

    /* KPI */
    .eko-kpis {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    @media (max-width: 1100px) { .eko-kpis { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 520px) { .eko-kpis { grid-template-columns: 1fr; } }

    .eko-kpi {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.25rem 1.35rem;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .eko-kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.06);
    }

    .eko-kpi::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 80px;
        height: 80px;
        border-radius: 0 0 0 80px;
        opacity: 0.07;
    }

    .eko-kpi--money::after { background: #d97706; }
    .eko-kpi--pending::after { background: #ef4444; }
    .eko-kpi--workers::after { background: #3b82f6; }
    .eko-kpi--reward::after { background: #8b5cf6; }

    .eko-kpi-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.85rem;
    }

    .eko-kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .eko-kpi-badge {
        font-size: 0.68rem;
        font-weight: 600;
        padding: 0.2rem 0.55rem;
        border-radius: 99px;
        background: var(--background);
        color: var(--text-muted);
    }

    .eko-kpi-badge--warn {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
    }

    .eko-kpi-value {
        font-size: 1.45rem;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1.15;
        letter-spacing: -0.02em;
    }

    .eko-kpi-label {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
    }

    .eko-kpi-sub {
        font-size: 0.7rem;
        color: var(--text-muted);
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid var(--border);
    }

    /* Overview charts row */
    .eko-overview {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.25rem;
        margin-bottom: 1.75rem;
    }

    @media (max-width: 960px) { .eko-overview { grid-template-columns: 1fr; } }

    .eko-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.35rem;
    }

    .eko-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.15rem;
        gap: 0.75rem;
    }

    .eko-panel-header h2 {
        font-size: 0.92rem;
        font-weight: 600;
        color: var(--text-main);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .eko-panel-header p {
        font-size: 0.72rem;
        color: var(--text-muted);
        margin: 0.15rem 0 0;
    }

    .eko-chart-wrap { height: 260px; position: relative; }
    .eko-chart-wrap--sm { height: 220px; }

    .eko-jenis-list {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        margin-top: 0.75rem;
    }

    .eko-jenis-item {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .eko-jenis-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .eko-jenis-info { flex: 1; min-width: 0; }

    .eko-jenis-name {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--text-main);
    }

    .eko-jenis-bar {
        height: 4px;
        background: var(--border);
        border-radius: 99px;
        margin-top: 0.25rem;
        overflow: hidden;
    }

    .eko-jenis-fill {
        height: 100%;
        border-radius: 99px;
    }

    .eko-jenis-val {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--text-main);
        white-space: nowrap;
    }

    /* Pending validation */
    .eko-pending-section {
        margin-bottom: 1.75rem;
    }

    .eko-pending-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .eko-pending-header h2 {
        font-size: 0.92rem;
        font-weight: 600;
        color: var(--text-main);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .eko-pending-count {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.25rem 0.65rem;
        border-radius: 99px;
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
    }

    .eko-pending-list {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }

    .eko-pending-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.15rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1.25rem;
        flex-wrap: wrap;
        transition: box-shadow 0.2s;
    }

    .eko-pending-card:hover {
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.05);
    }

    .eko-pending-card--urgent {
        border-left: 3px solid #f59e0b;
    }

    .eko-pending-program {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--text-main);
        margin-bottom: 0.25rem;
    }

    .eko-pending-meta {
        font-size: 0.78rem;
        color: var(--text-muted);
        line-height: 1.5;
    }

    .eko-pending-photos {
        display: flex;
        gap: 0.65rem;
        margin-top: 0.75rem;
        flex-wrap: wrap;
    }

    .eko-pending-photo {
        text-align: center;
    }

    .eko-pending-photo label {
        display: block;
        font-size: 0.68rem;
        color: var(--text-muted);
        margin-bottom: 0.2rem;
    }

    .eko-pending-photo img {
        width: 88px;
        height: 66px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid var(--border);
    }

    .eko-pending-warn {
        font-size: 0.75rem;
        color: #d97706;
        display: flex;
        align-items: center;
        gap: 0.3rem;
        margin-top: 0.5rem;
    }

    .eko-pending-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        flex-wrap: wrap;
        flex-shrink: 0;
    }

    .eko-pending-form {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .eko-upah-input {
        width: 130px;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 0.85rem;
        background: var(--background);
    }

    /* Worker selector */
    .eko-selector {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.25rem 1.35rem;
        margin-bottom: 1.75rem;
        display: flex;
        align-items: flex-end;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .eko-selector-label {
        flex: 1;
        min-width: 240px;
    }

    .eko-selector-label .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 0.35rem;
        display: block;
    }

    .eko-selector-row {
        display: flex;
        gap: 0.65rem;
        align-items: center;
    }

    .eko-worker-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.35rem 0.75rem;
        border-radius: 99px;
        background: rgba(217, 119, 6, 0.1);
        color: #b45309;
        font-size: 0.78rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    /* Worker detail */
    .eko-detail-grid {
        display: grid;
        grid-template-columns: 1.2fr 1fr 1fr;
        gap: 1.25rem;
        margin-bottom: 1.25rem;
    }

    @media (max-width: 1100px) {
        .eko-detail-grid { grid-template-columns: 1fr; }
    }

    .eko-akumulasi-big {
        font-size: 2rem;
        font-weight: 800;
        color: #d97706;
        line-height: 1.1;
        letter-spacing: -0.02em;
    }

    .eko-akumulasi-meta {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-top: 0.35rem;
    }

    .eko-keluarga-box {
        margin-top: 1rem;
        padding: 1rem;
        background: rgba(34, 197, 94, 0.06);
        border: 1px solid rgba(34, 197, 94, 0.2);
        border-radius: 10px;
    }

    .eko-keluarga-box-title {
        font-size: 0.72rem;
        font-weight: 600;
        color: #15803d;
        margin-bottom: 0.25rem;
    }

    .eko-keluarga-box-val {
        font-size: 1.25rem;
        font-weight: 700;
        color: #16a34a;
    }

    .eko-period-row {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
        align-items: flex-end;
    }

    .eko-period-row .form-group { margin: 0; flex: 1; min-width: 90px; }

    .eko-form-panel h3 {
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--text-main);
        margin: 0 0 1rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .eko-form-stack {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }

    .eko-form-stack .form-group { margin: 0; }

    /* History */
    .eko-history-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
        margin-bottom: 1.75rem;
    }

    @media (max-width: 768px) { .eko-history-grid { grid-template-columns: 1fr; } }

    .eko-table-wrap {
        overflow-x: auto;
        border-radius: 10px;
        border: 1px solid var(--border);
    }

    .eko-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.82rem;
    }

    .eko-table thead { background: var(--background); }

    .eko-table th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--text-muted);
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1px solid var(--border);
    }

    .eko-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--border);
        color: var(--text-main);
    }

    .eko-table tbody tr:last-child td { border-bottom: none; }
    .eko-table tbody tr:hover { background: rgba(217, 119, 6, 0.03); }

    .eko-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.6rem;
        border-radius: 99px;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .eko-badge--upah { background: rgba(217, 119, 6, 0.12); color: #b45309; }
    .eko-badge--voucher { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
    .eko-badge--insentif { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
    .eko-badge--default { background: var(--background); color: var(--text-muted); border: 1px solid var(--border); }

    .eko-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2.5rem 1rem;
        color: var(--text-muted);
        font-size: 0.85rem;
        text-align: center;
    }

    .eko-empty i { opacity: 0.35; margin-bottom: 0.5rem; }

    .eko-placeholder {
        background: var(--surface);
        border: 2px dashed var(--border);
        border-radius: var(--radius-md);
        padding: 3rem 2rem;
        text-align: center;
        color: var(--text-muted);
        margin-bottom: 1.75rem;
    }

    .eko-placeholder-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: rgba(217, 119, 6, 0.1);
        color: #d97706;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }

    .eko-recent-row {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }

    .eko-recent-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: linear-gradient(135deg, #d97706, #0f766e);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .eko-amount { font-weight: 700; color: #b45309; }

    .eko-btn-reject {
        background: transparent;
        border: 1px solid rgba(239, 68, 68, 0.4);
        color: #dc2626;
        border-radius: 8px;
        padding: 0.45rem 0.85rem;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
    }

    .eko-btn-reject:hover { background: rgba(239, 68, 68, 0.06); }
</style>
@endpush

@section('content')
<div class="eko-page animate-fade-in" x-data="ekonomiData()">

    {{-- Hero --}}
    <div class="eko-hero">
        <div class="eko-hero-content">
            <div class="eko-hero-badge">
                <i data-lucide="wallet" style="width: 13px; height: 13px;"></i>
                Manajemen Keuangan Desa · {{ $bulanLabel }}
            </div>
            <h1>Ekonomi & Insentif</h1>
            <p>Catat upah dan voucher, validasi hasil kerja, pantau akumulasi pendapatan bulanan pekerja, serta kelola penghargaan.</p>
        </div>
        <div class="eko-hero-actions">
            @if($isAdmin)
                <a href="/admin/insentif" class="eko-btn-white">
                    <i data-lucide="gift" style="width: 16px; height: 16px;"></i>
                    Insentif & Reward
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="eko-alert eko-alert--success">
            <i data-lucide="check-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="eko-alert eko-alert--error">
            <i data-lucide="alert-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="eko-alert eko-alert--error">
            <i data-lucide="alert-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
            <div>@foreach($errors->all() as $error)<p style="margin:0;">{{ $error }}</p>@endforeach</div>
        </div>
    @endif

    {{-- KPI --}}
    <div class="eko-kpis">
        <div class="eko-kpi eko-kpi--money">
            <div class="eko-kpi-top">
                <div class="eko-kpi-icon" style="background: rgba(217,119,6,0.1); color: #d97706;">
                    <i data-lucide="banknote" style="width: 22px; height: 22px;"></i>
                </div>
                <span class="eko-kpi-badge">{{ $bulanLabel }}</span>
            </div>
            <div class="eko-kpi-value">Rp {{ number_format($totalInsentifBulan, 0, ',', '.') }}</div>
            <div class="eko-kpi-label">Total Insentif Bulan Ini</div>
            <div class="eko-kpi-sub">{{ $entriBulan }} entri · akumulasi Rp {{ number_format($totalInsentifAll, 0, ',', '.') }}</div>
        </div>

        @if($isAdmin)
        <div class="eko-kpi eko-kpi--pending">
            <div class="eko-kpi-top">
                <div class="eko-kpi-icon" style="background: rgba(239,68,68,0.1); color: #ef4444;">
                    <i data-lucide="clipboard-check" style="width: 22px; height: 22px;"></i>
                </div>
                @if($pendingCount > 0)
                    <span class="eko-kpi-badge eko-kpi-badge--warn">Perlu tindakan</span>
                @endif
            </div>
            <div class="eko-kpi-value" style="color: {{ $pendingCount > 0 ? '#ef4444' : 'var(--text-main)' }};">{{ $pendingCount }}</div>
            <div class="eko-kpi-label">Menunggu Validasi Upah</div>
            <div class="eko-kpi-sub">Logbook selesai belum dicairkan</div>
        </div>
        @else
        <div class="eko-kpi eko-kpi--pending">
            <div class="eko-kpi-top">
                <div class="eko-kpi-icon" style="background: rgba(59,130,246,0.1); color: #2563eb;">
                    <i data-lucide="receipt" style="width: 22px; height: 22px;"></i>
                </div>
            </div>
            <div class="eko-kpi-value">{{ $entriBulan }}</div>
            <div class="eko-kpi-label">Entri Bulan Ini</div>
            <div class="eko-kpi-sub">Transaksi insentif tercatat</div>
        </div>
        @endif

        <div class="eko-kpi eko-kpi--workers">
            <div class="eko-kpi-top">
                <div class="eko-kpi-icon" style="background: rgba(59,130,246,0.1); color: #2563eb;">
                    <i data-lucide="users" style="width: 22px; height: 22px;"></i>
                </div>
            </div>
            <div class="eko-kpi-value">{{ $pekerjaDibayarBulan }}</div>
            <div class="eko-kpi-label">Pekerja Dibayar</div>
            <div class="eko-kpi-sub">Dari {{ $workers->count() }} pekerja terdaftar</div>
        </div>

        <div class="eko-kpi eko-kpi--reward">
            <div class="eko-kpi-top">
                <div class="eko-kpi-icon" style="background: rgba(139,92,246,0.1); color: #7c3aed;">
                    <i data-lucide="award" style="width: 22px; height: 22px;"></i>
                </div>
            </div>
            <div class="eko-kpi-value" style="color: #7c3aed;">{{ $totalRewards }}</div>
            <div class="eko-kpi-label">Total Penghargaan</div>
            <div class="eko-kpi-sub">Sertifikat & apresiasi pekerja</div>
        </div>
    </div>

    {{-- Overview charts --}}
    <div class="eko-overview">
        <div class="eko-panel">
            <div class="eko-panel-header">
                <div>
                    <h2>
                        <i data-lucide="trending-up" style="width: 17px; height: 17px; color: #d97706;"></i>
                        Tren Pencairan Insentif
                    </h2>
                    <p>6 bulan terakhir · total rupiah per bulan</p>
                </div>
            </div>
            <div class="eko-chart-wrap">
                <canvas id="trenChart"></canvas>
            </div>
        </div>

        <div class="eko-panel">
            <div class="eko-panel-header">
                <div>
                    <h2>
                        <i data-lucide="pie-chart" style="width: 17px; height: 17px; color: #2563eb;"></i>
                        Jenis Insentif
                    </h2>
                    <p>{{ $bulanLabel }}</p>
                </div>
            </div>
            @if($jenisStats->count())
                <div class="eko-chart-wrap eko-chart-wrap--sm">
                    <canvas id="jenisChart"></canvas>
                </div>
                <div class="eko-jenis-list">
                    @php $jColors = ['#d97706', '#22c55e', '#3b82f6', '#8b5cf6', '#ec4899']; @endphp
                    @foreach($jenisStats as $i => $j)
                        @php $pct = round(($j->total / $jenisTotal) * 100); @endphp
                        <div class="eko-jenis-item">
                            <div class="eko-jenis-dot" style="background: {{ $jColors[$i % count($jColors)] }};"></div>
                            <div class="eko-jenis-info">
                                <div class="eko-jenis-name">{{ $j->jenis_insentif }}</div>
                                <div class="eko-jenis-bar">
                                    <div class="eko-jenis-fill" style="width: {{ $pct }}%; background: {{ $jColors[$i % count($jColors)] }};"></div>
                                </div>
                            </div>
                            <div class="eko-jenis-val">Rp {{ number_format($j->total, 0, ',', '.') }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="eko-empty" style="padding: 2rem;">
                    <i data-lucide="inbox" style="width: 28px; height: 28px;"></i>
                    Belum ada insentif bulan ini
                </div>
            @endif
        </div>
    </div>

    {{-- Recent transactions --}}
    @if($recentInsentifs->count())
    <div class="eko-panel" style="margin-bottom: 1.75rem;">
        <div class="eko-panel-header">
            <div>
                <h2>
                    <i data-lucide="history" style="width: 17px; height: 17px; color: var(--primary);"></i>
                    Transaksi Terbaru
                </h2>
                <p>8 pencairan insentif terakhir</p>
            </div>
        </div>
        <div class="eko-table-wrap">
            <table class="eko-table">
                <thead>
                    <tr>
                        <th>Pekerja</th>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentInsentifs as $ins)
                        @php
                            $initials = collect(explode(' ', $ins->worker?->nama ?? '?'))->take(2)->map(fn ($w) => strtoupper(substr($w, 0, 1)))->join('');
                            $badgeClass = match(true) {
                                str_contains(strtolower($ins->jenis_insentif), 'upah') => 'eko-badge--upah',
                                str_contains(strtolower($ins->jenis_insentif), 'voucher') => 'eko-badge--voucher',
                                str_contains(strtolower($ins->jenis_insentif), 'insentif') => 'eko-badge--insentif',
                                default => 'eko-badge--default',
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="eko-recent-row">
                                    <div class="eko-recent-avatar">{{ $initials }}</div>
                                    {{ $ins->worker?->nama ?? '—' }}
                                </div>
                            </td>
                            <td style="color: var(--text-muted);">{{ \Carbon\Carbon::parse($ins->tanggal)->format('d/m/Y') }}</td>
                            <td><span class="eko-badge {{ $badgeClass }}">{{ $ins->jenis_insentif }}</span></td>
                            <td class="eko-amount">Rp {{ number_format($ins->jumlah_upah, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Pending logbooks (admin) --}}
    @if($isAdmin && $pendingCount > 0)
    <div class="eko-pending-section" id="validasi-section">
        <div class="eko-pending-header">
            <h2>
                <i data-lucide="clipboard-check" style="width: 17px; height: 17px; color: #ef4444;"></i>
                Validasi Hasil Kerja — Menunggu Pencairan
            </h2>
            <span class="eko-pending-count">{{ $pendingCount }} menunggu</span>
        </div>
        <div class="eko-pending-list">
            @foreach($pendingLogbooks as $log)
                @php
                    $hasPhotos = $log->foto_sebelum && ($log->foto_sesudah ?? $log->foto_bukti);
                @endphp
                <div class="eko-pending-card {{ !$hasPhotos ? 'eko-pending-card--urgent' : '' }}">
                    <div style="flex: 1; min-width: 240px;">
                        <div class="eko-pending-program">{{ $log->schedule?->program?->nama_program ?? 'Program' }}</div>
                        <div class="eko-pending-meta">
                            <strong>{{ $log->worker?->nama ?? 'Belum ditentukan' }}</strong>
                            · Progres {{ $log->progres_persentase }}%
                        </div>
                        <div class="eko-pending-meta">{{ $log->catatan_progres ?? $log->catatan ?? '—' }}</div>
                        <div class="eko-pending-photos">
                            @if($log->foto_sebelum)
                                <div class="eko-pending-photo">
                                    <label>Sebelum</label>
                                    <img src="{{ $log->foto_sebelum }}" alt="Sebelum">
                                </div>
                            @endif
                            @if($log->foto_sesudah ?? $log->foto_bukti)
                                <div class="eko-pending-photo">
                                    <label>Sesudah</label>
                                    <img src="{{ $log->foto_sesudah ?? $log->foto_bukti }}" alt="Sesudah">
                                </div>
                            @endif
                        </div>
                        @if(!$hasPhotos)
                            <div class="eko-pending-warn">
                                <i data-lucide="alert-triangle" style="width: 14px; height: 14px;"></i>
                                Foto before/after belum lengkap
                            </div>
                        @endif
                    </div>
                    <div class="eko-pending-actions">
                        <form method="POST" action="/admin/logbook/{{ $log->id }}/validasi" class="eko-pending-form">
                            @csrf
                            <input type="hidden" name="action" value="disetujui">
                            <input type="number" name="jumlah_upah" class="eko-upah-input" value="{{ $defaultUpah }}" min="0" step="1000" title="Jumlah upah">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i data-lucide="check" style="width: 14px; height: 14px;"></i>
                                Setujui & Cairkan
                            </button>
                        </form>
                        <form method="POST" action="/admin/logbook/{{ $log->id }}/validasi">
                            @csrf
                            <input type="hidden" name="action" value="ditolak">
                            <button type="submit" class="eko-btn-reject">Tolak</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Worker selector --}}
    <div class="eko-selector">
        <div class="eko-selector-label">
            <label class="form-label">Pilih Pekerja untuk Kelola Insentif</label>
            <div class="eko-selector-row">
                <select class="form-input" x-model="workerId" @change="loadWorkerData" style="flex: 1;">
                    <option value="">— Pilih pekerja —</option>
                    @foreach($workers as $w)
                        <option value="{{ $w->id }}">#{{ $w->id }} — {{ $w->nama }}</option>
                    @endforeach
                </select>
            </div>
            <template x-if="selectedWorker">
                <div class="eko-worker-chip">
                    <i data-lucide="wrench" style="width: 13px; height: 13px;"></i>
                    <span x-text="selectedWorker.kemampuan_utama || 'Keahlian umum'"></span>
                </div>
            </template>
        </div>
    </div>

    {{-- Worker detail --}}
    <template x-if="workerId">
        <div>
            <div class="eko-detail-grid">
                {{-- Akumulasi --}}
                <div class="eko-panel">
                    <div class="eko-panel-header">
                        <div>
                            <h2>
                                <i data-lucide="trending-up" style="width: 17px; height: 17px; color: #d97706;"></i>
                                Akumulasi Upah Bulanan
                            </h2>
                            <p>Pendapatan pekerja terpilih</p>
                        </div>
                    </div>

                    <div class="eko-period-row">
                        <div class="form-group">
                            <label class="form-label">Bulan</label>
                            <select class="form-input" x-model="bulan" @change="loadWorkerData">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}">{{ ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][$i] }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tahun</label>
                            <input type="number" class="form-input" x-model="tahun" @change="loadWorkerData" min="2000" max="2100">
                        </div>
                        <button type="button" class="btn btn-outline btn-sm" @click="loadWorkerData" :disabled="loadingDetail" style="height: fit-content;">
                            <i data-lucide="refresh-cw" style="width: 14px; height: 14px;"></i>
                        </button>
                    </div>

                    <template x-if="loadingDetail">
                        <p style="color: var(--text-muted); font-size: 0.85rem;">Menghitung…</p>
                    </template>

                    <template x-if="!loadingDetail && akumulasi">
                        <div>
                            <div class="eko-akumulasi-big" x-text="fmtIdr(akumulasi.total_upah)"></div>
                            <div class="eko-akumulasi-meta">
                                Periode <span x-text="akumulasi.periode.label"></span>
                                · <span x-text="akumulasi.jumlah_entri"></span> entri
                            </div>

                            <template x-if="akumulasi.total_keluarga_lintas_program > 0">
                                <div class="eko-keluarga-box">
                                    <div class="eko-keluarga-box-title">Total Pendapatan Keluarga (Lintas Program)</div>
                                    <div class="eko-keluarga-box-val" x-text="fmtIdr(akumulasi.total_keluarga_lintas_program)"></div>
                                    <p style="font-size: 0.75rem; color: #15803d; margin: 0.35rem 0 0;">
                                        Dasar keluarga: <span x-text="fmtIdr(akumulasi.pendapatan_keluarga_dasar)"></span>
                                        · Insentif anggota: <span x-text="fmtIdr(akumulasi.total_insentif_keluarga)"></span>
                                    </p>
                                </div>
                            </template>

                            <template x-if="akumulasi.per_jenis && akumulasi.per_jenis.length > 0">
                                <div style="margin-top: 1rem;">
                                    <div style="font-size: 0.72rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.04em;">Rincian per jenis</div>
                                    <template x-for="p in akumulasi.per_jenis" :key="p.jenis_insentif">
                                        <div style="display: flex; justify-content: space-between; font-size: 0.82rem; padding: 0.35rem 0; border-bottom: 1px solid var(--border);">
                                            <span x-text="p.jenis_insentif" style="color: var(--text-muted);"></span>
                                            <strong x-text="fmtIdr(p.subtotal)"></strong>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="akumulasi.per_program && akumulasi.per_program.length > 0">
                                <div style="margin-top: 1rem;">
                                    <div style="font-size: 0.72rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.04em;">Per program (keluarga)</div>
                                    <template x-for="p in akumulasi.per_program" :key="p.program">
                                        <div style="display: flex; justify-content: space-between; font-size: 0.82rem; padding: 0.35rem 0; border-bottom: 1px solid var(--border);">
                                            <span x-text="p.program" style="color: var(--text-muted);"></span>
                                            <strong x-text="fmtIdr(p.subtotal)"></strong>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Form Insentif --}}
                <div class="eko-panel eko-form-panel">
                    <h3>
                        <i data-lucide="plus-circle" style="width: 17px; height: 17px; color: #d97706;"></i>
                        Catat Insentif / Upah
                    </h3>
                    <form method="POST" action="{{ $ekonomiPrefix }}/ekonomi/insentif" class="eko-form-stack">
                        @csrf
                        <input type="hidden" name="worker_id" :value="workerId">
                        <div class="form-group">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-input" required :value="today">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jenis</label>
                            <select name="jenis_insentif" class="form-input">
                                <option>Upah Harian</option>
                                <option>Voucher Pangan</option>
                                <option>Insentif Langsung</option>
                                <option>Lainnya (isi keterangan)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jumlah (Rp)</label>
                            <input type="number" name="jumlah_upah" class="form-input" required min="0" step="1000" placeholder="50000">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-input" rows="2" placeholder="Program / tugas / catatan validasi"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                            Simpan Insentif
                        </button>
                    </form>
                </div>

                {{-- Form Penghargaan --}}
                <div class="eko-panel eko-form-panel">
                    <h3>
                        <i data-lucide="award" style="width: 17px; height: 17px; color: #7c3aed;"></i>
                        Penghargaan
                    </h3>
                    <form method="POST" action="{{ $ekonomiPrefix }}/ekonomi/reward" class="eko-form-stack">
                        @csrf
                        <input type="hidden" name="worker_id" :value="workerId">
                        <div class="form-group">
                            <label class="form-label">Nama Penghargaan</label>
                            <input type="text" name="nama_penghargaan" class="form-input" required placeholder="Pekerja teladan, sertifikat...">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Pemberian</label>
                            <input type="date" name="tanggal_pemberian" class="form-input" required :value="today">
                        </div>
                        <button type="submit" class="btn btn-outline">
                            <i data-lucide="medal" style="width: 16px; height: 16px;"></i>
                            Simpan Penghargaan
                        </button>
                    </form>
                </div>
            </div>

            {{-- History --}}
            <div class="eko-history-grid">
                <div class="eko-panel">
                    <div class="eko-panel-header">
                        <div>
                            <h2>
                                <i data-lucide="list" style="width: 17px; height: 17px; color: #d97706;"></i>
                                Riwayat Insentif
                            </h2>
                            <p>Semua transaksi pekerja terpilih</p>
                        </div>
                    </div>
                    <div class="eko-table-wrap">
                        <table class="eko-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="riwayat.length === 0">
                                    <tr><td colspan="3" style="text-align: center; color: var(--text-muted); padding: 2rem;">Belum ada data.</td></tr>
                                </template>
                                <template x-for="r in riwayat" :key="r.id">
                                    <tr>
                                        <td style="color: var(--text-muted);" x-text="r.tanggal"></td>
                                        <td><span class="eko-badge eko-badge--upah" x-text="r.jenis_insentif"></span></td>
                                        <td class="eko-amount" x-text="fmtIdr(r.jumlah_upah)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="eko-panel">
                    <div class="eko-panel-header">
                        <div>
                            <h2>
                                <i data-lucide="trophy" style="width: 17px; height: 17px; color: #7c3aed;"></i>
                                Riwayat Penghargaan
                            </h2>
                            <p>Apresiasi & sertifikat</p>
                        </div>
                    </div>
                    <div class="eko-table-wrap">
                        <table class="eko-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama Penghargaan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="rewards.length === 0">
                                    <tr><td colspan="2" style="text-align: center; color: var(--text-muted); padding: 2rem;">Belum ada data.</td></tr>
                                </template>
                                <template x-for="r in rewards" :key="r.id">
                                    <tr>
                                        <td style="color: var(--text-muted);" x-text="r.tanggal_pemberian"></td>
                                        <td style="font-weight: 500;" x-text="r.nama_penghargaan"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template x-if="!workerId">
        <div class="eko-placeholder">
            <div class="eko-placeholder-icon">
                <i data-lucide="user-search" style="width: 24px; height: 24px;"></i>
            </div>
            <p style="font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Pilih pekerja untuk mulai</p>
            <p style="font-size: 0.85rem; margin: 0;">Catat insentif, lihat akumulasi upah bulanan, dan kelola penghargaan per pekerja.</p>
        </div>
    </template>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('ekonomiData', () => ({
            workers: @json($workers),
            workerId: '',
            tahun: new Date().getFullYear(),
            bulan: new Date().getMonth() + 1,
            akumulasi: null,
            riwayat: [],
            rewards: [],
            loadingDetail: false,
            today: new Date().toISOString().slice(0, 10),
            ekonomiPrefix: '{{ $ekonomiPrefix }}',

            get selectedWorker() {
                return this.workers.find(w => String(w.id) === String(this.workerId)) || null;
            },

            init() {
                this.$nextTick(() => {
                    setTimeout(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        this.renderOverviewCharts();
                    }, 50);
                });
            },

            fmtIdr(n) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(n) || 0);
            },

            async loadWorkerData() {
                if (!this.workerId) {
                    this.akumulasi = null;
                    this.riwayat = [];
                    this.rewards = [];
                    return;
                }
                this.loadingDetail = true;
                try {
                    const res = await fetch(`${this.ekonomiPrefix}/ekonomi/detail/${this.workerId}?tahun=${this.tahun}&bulan=${this.bulan}`);
                    if (res.ok) {
                        const data = await res.json();
                        this.akumulasi = data.akumulasi;
                        this.riwayat = data.riwayat;
                        this.rewards = data.rewards;
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                    }
                } catch (e) {
                    console.error('Error fetching data', e);
                } finally {
                    this.loadingDetail = false;
                }
            },

            renderOverviewCharts() {
                if (typeof Chart === 'undefined') return;

                const trenData = @json($trenBulanan);
                const ctxTren = document.getElementById('trenChart');
                if (ctxTren) {
                    const gradient = ctxTren.getContext('2d').createLinearGradient(0, 0, 0, 260);
                    gradient.addColorStop(0, 'rgba(217, 119, 6, 0.35)');
                    gradient.addColorStop(1, 'rgba(217, 119, 6, 0.02)');

                    new Chart(ctxTren.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: trenData.map(d => d.bulan),
                            datasets: [{
                                label: 'Total Insentif',
                                data: trenData.map(d => d.total),
                                borderColor: '#d97706',
                                backgroundColor: gradient,
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.35,
                                pointBackgroundColor: '#d97706',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                pointHoverRadius: 7,
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
                                    callbacks: {
                                        label: (ctx) => ' Rp ' + new Intl.NumberFormat('id-ID').format(ctx.parsed.y),
                                    },
                                },
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: 'rgba(226,232,240,0.6)' },
                                    ticks: {
                                        font: { size: 11 },
                                        callback: (v) => 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(v),
                                    },
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { font: { size: 11 } },
                                },
                            },
                        },
                    });
                }

                const jenisStats = @json($jenisStats);
                const ctxJenis = document.getElementById('jenisChart');
                if (ctxJenis && jenisStats.length > 0) {
                    const jColors = ['#d97706', '#22c55e', '#3b82f6', '#8b5cf6', '#ec4899'];
                    new Chart(ctxJenis.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: jenisStats.map(j => j.jenis_insentif),
                            datasets: [{
                                data: jenisStats.map(j => j.total),
                                backgroundColor: jenisStats.map((_, i) => jColors[i % jColors.length]),
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
            },
        }));
    });
</script>
@endsection
