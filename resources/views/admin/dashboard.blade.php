@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="dashboard-layout animate-fade-in" x-data="dashboardMap()">
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

    <!-- Grid Bawah -->
    <div class="dashboard-grid-2">
        <!-- Visualisasi Area Kerja -->
        <div class="glass-panel stat-card" style="padding: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 class="stat-title" style="color: var(--text-main); font-size: 1rem; font-weight: 600; margin: 0;">Visualisasi Area Kerja</h3>
                <div style="display: flex; gap: 0.75rem;">
                    <button class="btn btn-outline" @click="toggleMapType" style="padding: 0.5rem 1rem; font-size: 0.8rem;" x-text="mapType === 'street' ? 'Satellite' : 'Street'"></button>
                    <button :class="isAddingMode ? 'btn' : 'btn btn-primary'" @click="isAddingMode = !isAddingMode" style="padding: 0.5rem 1rem; font-size: 0.8rem;" :style="isAddingMode ? 'background: #dc2626; color: white;' : ''" x-text="isAddingMode ? 'Batal Tambah' : '+ Tambah Titik'"></button>
                </div>
            </div>
            
            <div style="height: 300px; width: 100%; border-radius: 12px; overflow: hidden;" :style="isAddingMode ? 'border: 3px dashed var(--primary); cursor: crosshair;' : 'border: 1px solid var(--border);'">
                <div id="map" style="height: 100%; width: 100%; z-index: 0;"></div>
            </div>
            <div x-show="isAddingMode" style="font-size: 0.8rem; color: var(--primary); margin-top: 0.5rem; text-align: center; display: none;">Klik di mana saja pada peta untuk menambahkan titik lokasi area kerja baru.</div>
        </div>

        <!-- Progres Kebersihan Area -->
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
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dashboardMap', () => ({
            mapType: 'street',
            isAddingMode: false,
            map: null,
            streetLayer: null,
            satelliteLayer: null,
            markersData: @json($data['area']),

            init() {
                this.initMap();
            },

            initMap() {
                this.map = L.map('map').setView([-6.914744, 107.609810], 13);
                
                this.streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
                this.satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}');
                
                this.streetLayer.addTo(this.map);

                // Add markers
                this.markersData.forEach(a => {
                    if(a.kordinat) {
                        const [lat, lng] = a.kordinat.split(',').map(Number);
                        if(!isNaN(lat) && !isNaN(lng)) {
                            L.marker([lat, lng]).addTo(this.map)
                             .bindPopup(`<strong>${a.nama_program}</strong><br/>Lokasi: ${a.lokasi}<br/>Status: ${a.status}`);
                        }
                    }
                });

                // Add click event for adding mode
                this.map.on('click', async (e) => {
                    if (!this.isAddingMode) return;
                    
                    const nama = prompt("Masukkan Nama Program/Area Baru:");
                    if (!nama) {
                        this.isAddingMode = false;
                        return;
                    }
                    
                    const lokasi = prompt("Masukkan Deskripsi Lokasi (contoh: RT 01):") || "Area Baru";
                    const kordinat = `${e.latlng.lat},${e.latlng.lng}`;

                    try {
                        const res = await fetch('/api/programs', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                nama_program: nama,
                                jenis_program: 'Lainnya',
                                lokasi: lokasi,
                                kordinat: kordinat,
                                status: 'planned'
                            })
                        });
                        
                        if(res.ok) {
                            window.location.reload(); // Reload to reflect changes
                        }
                    } catch(err) {
                        alert('Gagal menambahkan titik.');
                    }
                    this.isAddingMode = false;
                });
            },

            toggleMapType() {
                if(this.mapType === 'street') {
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
