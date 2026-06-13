@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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

    <!-- Peta Area Kerja -->
    <div class="glass-panel stat-card" style="padding: 2rem;" x-data="dashboardMapData()">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <h3 class="stat-title" style="margin: 0; color: var(--text-main); font-size: 1rem; font-weight: 600;">Peta Area Kerja</h3>
            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <button class="btn btn-outline" @click="toggleMapType" style="padding: 0.5rem 1rem; font-size: 0.8rem;" x-text="mapType === 'street' ? 'Satellite' : 'Street'"></button>
                <a href="/admin/perencanaan" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8rem; text-decoration: none;">Kelola Area</a>
            </div>
        </div>

        <div style="height: 400px; width: 100%; border-radius: 12px; overflow: hidden; border: 1px solid var(--border); position: relative; background: #e2e8f0;">
            <div id="dashboard-map" style="height: 100%; width: 100%; z-index: 0;"></div>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1rem; font-size: 0.8rem; color: var(--text-muted);">
            <span style="display: flex; align-items: center; gap: 0.35rem;">
                <span style="width: 10px; height: 10px; border-radius: 50%; background: var(--success);"></span>
                Aktif / Dalam Proses
            </span>
            <span style="display: flex; align-items: center; gap: 0.35rem;">
                <span style="width: 10px; height: 10px; border-radius: 50%; background: var(--secondary);"></span>
                Selesai
            </span>
            <span style="display: flex; align-items: center; gap: 0.35rem;">
                <span style="width: 10px; height: 10px; border-radius: 50%; background: var(--warning);"></span>
                Direncanakan
            </span>
            <span style="margin-left: auto;" x-text="programs.length + ' program area'"></span>
        </div>
    </div>
</div>

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

    document.addEventListener('alpine:init', () => {
        Alpine.data('dashboardMapData', () => ({
            programs: @json($data['area']),
            mapType: 'street',
            map: null,
            streetLayer: null,
            satelliteLayer: null,
            markers: [],

            init() {
                fixLeafletIcons();
                this.$nextTick(() => {
                    setTimeout(() => this.initMap(), 100);
                });
            },

            initMap() {
                if (this.map || typeof L === 'undefined') return;

                const container = document.getElementById('dashboard-map');
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

                setTimeout(() => {
                    if (this.map) this.map.invalidateSize();
                }, 450);
            },

            getStatusLabel(status) {
                if (['completed', 'selesai'].includes(status)) return 'Selesai';
                if (['active', 'in_progress', 'ongoing'].includes(status)) return 'Dalam Proses';
                return 'Direncanakan';
            },

            refreshMarkers() {
                if (!this.map) return;

                this.markers.forEach(marker => this.map.removeLayer(marker));
                this.markers = [];

                const bounds = [];
                this.programs.forEach((program) => {
                    if (!program.kordinat) return;
                    const [lat, lng] = program.kordinat.split(',').map(Number);
                    if (isNaN(lat) || isNaN(lng)) return;

                    const marker = L.marker([lat, lng]).addTo(this.map)
                        .bindPopup(`<strong>${program.nama_program}</strong><br/>Lokasi: ${program.lokasi || '-'}<br/>Status: ${this.getStatusLabel(program.status)}`);
                    this.markers.push(marker);
                    bounds.push([lat, lng]);
                });

                if (bounds.length > 0) {
                    this.map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
                }
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
            }
        }));
    });
</script>
@endsection
