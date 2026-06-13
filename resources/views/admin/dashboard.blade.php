@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
<div class="dashboard-layout animate-fade-in">
    <!-- Banner -->
    <div class="banner">
        <div>
            <h1>Halo, Admin Desa!</h1>
            <p>Selamat datang di sistem manajemen Work4Village. Pantau progres program kerja mikro hari ini.</p>
        </div>
        <button class="btn btn-white">
            Laporan Harian
        </button>
    </div>

    <!-- Grid Atas -->
    <div class="dashboard-grid">
        <!-- Total Profiling -->
        <div class="glass-panel stat-card" style="padding: 2rem;">
            <h3 class="stat-title" style="margin-bottom: 1.5rem; color: var(--text-main); font-size: 1rem; font-weight: 600;">Total Profiling</h3>
            <div class="donut-chart-container">
                <div class="donut-chart">
                    <div class="donut-inner">
                        <span class="donut-value">{{ $data['profiling']['total'] }}</span>
                        <span class="donut-label">Profiling</span>
                    </div>
                </div>
                
                <div class="donut-legend">
                    <div class="legend-item">
                        <div><span class="legend-color" style="background: var(--success);"></span> PETANI</div>
                        <div class="legend-val">{{ $data['profiling']['petani'] }}</div>
                    </div>
                    <div class="legend-item">
                        <div><span class="legend-color" style="background: var(--orange);"></span> PEMBERSIH</div>
                        <div class="legend-val">{{ $data['profiling']['pembersih'] }}</div>
                    </div>
                    <div class="legend-item">
                        <div><span class="legend-color" style="background: var(--purple);"></span> PENGRAJIN</div>
                        <div class="legend-val">{{ $data['profiling']['pengrajin'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tugas Mingguan -->
        @php
            $tugas = $data['tugas'];
            $totalTugasForProgress = $tugas['total'] === 0 ? 1 : $tugas['total'];
        @endphp
        <div class="glass-panel stat-card" style="padding: 2rem;">
            <h3 class="stat-title" style="margin-bottom: 1rem; color: var(--text-main); font-size: 1rem; font-weight: 600;">Tugas Mingguan</h3>
            <div style="font-size: 3.5rem; font-weight: 800; color: var(--text-main); line-height: 1;">{{ $tugas['total'] }}</div>
            
            <div class="progress-list">
                <div class="progress-item">
                    <div class="progress-label-row">
                        <span>Aktif</span>
                        <span class="progress-val-text">{{ $tugas['aktif'] }}</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: {{ ($tugas['aktif'] / $totalTugasForProgress) * 100 }}%; background: var(--success);"></div>
                    </div>
                </div>
                
                <div class="progress-item">
                    <div class="progress-label-row">
                        <span>Terjadwal</span>
                        <span class="progress-val-text">{{ $tugas['terjadwal'] }}</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: {{ ($tugas['terjadwal'] / $totalTugasForProgress) * 100 }}%; background: var(--secondary);"></div>
                    </div>
                </div>

                <div class="progress-item">
                    <div class="progress-label-row">
                        <span>Selesai</span>
                        <span class="progress-val-text">{{ $tugas['selesai'] }}</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: {{ ($tugas['selesai'] / $totalTugasForProgress) * 100 }}%; background: var(--text-muted);"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hasil Produksi & Dampak -->
        <div class="glass-panel stat-card" style="padding: 2rem;">
            <h3 class="stat-title" style="margin-bottom: 1.5rem; color: var(--text-main); font-size: 1rem; font-weight: 600;">Hasil Produksi & Dampak</h3>
            
            <div class="impact-list">
                @forelse($data['dampak'] as $i => $d)
                    @php
                        $bg = $i % 3 === 0 ? 'rgba(34, 197, 94, 0.1)' : ($i % 3 === 1 ? 'rgba(245, 158, 11, 0.1)' : 'rgba(139, 92, 246, 0.1)');
                        $col = $i % 3 === 0 ? 'var(--success)' : ($i % 3 === 1 ? 'var(--warning)' : 'var(--purple)');
                        $icon = $i % 3 === 0 ? 'sprout' : ($i % 3 === 1 ? 'trash-2' : 'hammer');
                    @endphp
                    <div class="impact-item">
                        <div class="impact-icon" style="background: {{ $bg }}; color: {{ $col }};">
                            <i data-lucide="{{ $icon }}" style="width: 20px; height: 20px;"></i>
                        </div>
                        <div class="impact-content">
                            <div class="impact-title">{{ $d->nama_barang }}</div>
                            <div class="impact-value">{{ $d->kuantitas }} {{ $d->satuan }}</div>
                        </div>
                        <div class="impact-desc">{{ $d->kategori }}</div>
                    </div>
                @empty
                    <p style="color: var(--text-muted);">Belum ada data produksi.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Ringkasan Progres Area -->
    <div class="glass-panel stat-card" style="padding: 2rem;">
        <h3 class="stat-title" style="margin-bottom: 1.5rem; color: var(--text-main); font-size: 1rem; font-weight: 600;">Progres Kebersihan Area</h3>
        
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            @forelse($data['area'] as $i => $a)
                @php
                    $isDone = in_array($a->status, ['completed', 'selesai']);
                    $isInProgress = in_array($a->status, ['active', 'in_progress']);
                @endphp
                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 1rem; border-bottom: {{ $i < count($data['area']) - 1 ? '1px solid var(--border)' : 'none' }};">
                    <span style="font-weight: 600; color: var(--text-main);">{{ $a->lokasi ?: $a->nama_program }}</span>
                    @if($isDone)
                        <span class="badge badge-success" style="background: rgba(16, 185, 129, 0.15); color: var(--primary); padding: 0.25rem 0.75rem; font-size: 0.7rem;">SELESAI</span>
                    @elseif($isInProgress)
                        <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: var(--warning); padding: 0.25rem 0.75rem; font-size: 0.7rem;">DALAM PROSES</span>
                    @else
                        <span class="badge" style="background: var(--background); color: var(--text-muted); padding: 0.25rem 0.75rem; font-size: 0.7rem;">BELUM MULAI</span>
                    @endif
                </div>
            @empty
                <p style="color: var(--text-muted);">Belum ada data program area.</p>
            @endforelse
        </div>
        <p style="margin-top: 1rem; font-size: 0.85rem; color: var(--text-muted);">
            Peta interaktif area kerja tersedia di menu <a href="/admin/perencanaan" style="color: var(--primary);">Perencanaan Program</a>.
        </p>
    </div>
</div>
@endsection
