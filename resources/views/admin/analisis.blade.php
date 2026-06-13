@extends('layouts.app')
@section('title', 'Dashboard Analisis')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="dashboard-analisis" style="padding: 2rem;" x-data="analisisData()">
    <!-- Header and Controls -->
    <div class="flex-between" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="margin: 0; font-size: 1.8rem; color: var(--text-main);">Laporan Dampak Program</h1>
            <p style="margin: 0.5rem 0 0; color: var(--text-muted);">Pusat kendali evaluasi pencapaian desa.</p>
        </div>
        
        <div class="action-buttons no-print" style="display: flex; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; background: white; padding: 0.5rem 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                <i data-lucide="filter" style="width: 18px; height: 18px;"></i>
                <select 
                    x-model="period" 
                    @change="changePeriod"
                    style="border: none; outline: none; background: transparent;"
                >
                    <option value="mingguan">Mingguan</option>
                    <option value="bulanan">Bulanan</option>
                    <option value="tahunan">Tahunan</option>
                </select>
            </div>
            
            <button 
                onclick="window.location.href='/admin/analisis/pdf?period={{ $period }}'"
                style="display: flex; align-items: center; gap: 0.5rem; background: var(--primary); color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer;"
            >
                <i data-lucide="download" style="width: 18px; height: 18px;"></i>
                Unduh PDF
            </button>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #f0fdf4; color: #166534; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            {{ session('success') }}
        </div>
    @endif

    <div class="no-print glass-panel" style="padding: 1.5rem; margin-bottom: 1.5rem;">
        <h3 style="margin: 0 0 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <i data-lucide="leaf" style="width: 20px; height: 20px; color: var(--primary);"></i>
            Input Dampak Lingkungan
        </h3>
        <form method="POST" action="{{ route('admin.analisis.dampak') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; align-items: end;">
            @csrf
            <div class="form-group" style="margin:0;">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-input" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Jenis Limbah</label>
                <select name="jenis_limbah" class="form-input" required>
                    <option value="Organik">Organik</option>
                    <option value="Kompos">Kompos</option>
                    <option value="Plastik Daur Ulang">Plastik Daur Ulang</option>
                    <option value="Sampah Terpilah">Sampah Terpilah</option>
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Volume (Kg)</label>
                <input type="number" name="volume_kg" class="form-input" min="0" step="0.01" placeholder="25.5" required>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Estimasi Emisi Berkurang (Kg CO₂)</label>
                <input type="number" name="estimasi_emisi_berkurang_kg" class="form-input" min="0" step="0.01" placeholder="10">
            </div>
            <button type="submit" class="btn btn-primary">Simpan Data</button>
        </form>

        @if(isset($environmentalRecords) && $environmentalRecords->count())
            <div style="margin-top: 1.25rem; overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis Limbah</th>
                            <th>Volume (Kg)</th>
                            <th>Emisi Berkurang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($environmentalRecords as $record)
                            <tr>
                                <td>{{ $record->tanggal->format('d/m/Y') }}</td>
                                <td>{{ $record->jenis_limbah }}</td>
                                <td>{{ number_format($record->volume_kg, 2, ',', '.') }}</td>
                                <td>{{ number_format($record->estimasi_emisi_berkurang_kg, 2, ',', '.') }} Kg</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Headline Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 1rem;">
            <div style="background: #e0f2fe; padding: 1rem; border-radius: 12px; color: #0284c7;">
                <i data-lucide="users" style="width: 32px; height: 32px;"></i>
            </div>
            <div>
                <h3 style="margin: 0; color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">Warga Prasejahtera Bekerja</h3>
                <p style="margin: 0.5rem 0 0; font-size: 1.8rem; font-weight: bold; color: var(--text-main);">
                    {{ $data['total_warga_bekerja'] }} <span style="font-size: 1rem; font-weight: normal; color: var(--text-muted);">Orang</span>
                </p>
            </div>
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 1rem;">
            <div style="background: #dcfce7; padding: 1rem; border-radius: 12px; color: #16a34a;">
                <i data-lucide="leaf" style="width: 32px; height: 32px;"></i>
            </div>
            <div>
                <h3 style="margin: 0; color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">Akumulasi Dampak Lingkungan</h3>
                <p style="margin: 0.5rem 0 0; font-size: 1.8rem; font-weight: bold; color: var(--text-main);">
                    {{ number_format($data['dampak_lingkungan']['value'], 0, ',', '.') }} <span style="font-size: 1rem; font-weight: normal; color: var(--text-muted);">{{ $data['dampak_lingkungan']['unit'] }}</span>
                </p>
            </div>
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 1rem;">
            <div style="background: #fef3c7; padding: 1rem; border-radius: 12px; color: #d97706;">
                <i data-lucide="dollar-sign" style="width: 32px; height: 32px;"></i>
            </div>
            <div>
                <h3 style="margin: 0; color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">Total Dana Insentif</h3>
                <p style="margin: 0.5rem 0 0; font-size: 1.8rem; font-weight: bold; color: var(--text-main);">
                    Rp {{ number_format($data['total_insentif'], 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Visual Dashboard -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="chart-container" style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 1.5rem 0; color: var(--text-main);">
                Tren Partisipasi Warga (<span x-text="period === 'mingguan' ? 'Per Minggu' : period === 'tahunan' ? 'Per Tahun' : 'Per Bulan'"></span>)
            </h3>
            <div style="width: 100%; height: 300px;">
                <canvas id="trenChart"></canvas>
            </div>
        </div>

        <div class="chart-container" style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 1.5rem 0; color: var(--text-main);">Sebaran Dampak Program</h3>
            <div style="width: 100%; height: 300px; display: flex; justify-content: center;">
                <canvas id="sebaranChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Table Rincian Capaian -->
    <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="margin: 0 0 1.5rem 0; color: var(--text-main);">Rincian Capaian Program Kerja</h3>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid #e2e8f0; color: var(--text-muted);">
                        <th style="padding: 1rem;">Nama Program</th>
                        <th style="padding: 1rem;">Jenis Sektor</th>
                        <th style="padding: 1rem;">Mulai</th>
                        <th style="padding: 1rem;">Selesai</th>
                        <th style="padding: 1rem;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['rincian_capaian'] as $item)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 1rem; font-weight: 500;">{{ $item->nama_program }}</td>
                        <td style="padding: 1rem;">{{ $item->jenis_program }}</td>
                        <td style="padding: 1rem;">{{ $item->tanggal_mulai ? \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') : '-' }}</td>
                        <td style="padding: 1rem;">{{ $item->tanggal_selesai ? \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') : '-' }}</td>
                        <td style="padding: 1rem;">
                            <span style="
                                padding: 0.25rem 0.75rem; 
                                border-radius: 99px; 
                                font-size: 0.875rem;
                                background: {{ in_array($item->status, ['selesai', 'completed']) ? '#dcfce7' : '#f1f5f9' }};
                                color: {{ in_array($item->status, ['selesai', 'completed']) ? '#16a34a' : '#64748b' }};
                            ">
                                {{ $item->status ?? 'Berjalan' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding: 1rem; text-align: center; color: var(--text-muted);">
                            Belum ada data program kerja.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .dashboard-analisis, .dashboard-analisis * {
                visibility: visible;
            }
            .dashboard-analisis {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .chart-container {
                page-break-inside: avoid;
            }
        }
    </style>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('analisisData', () => ({
            period: '{{ $period }}',
            data: @json($data),
            
            init() {
                setTimeout(() => lucide.createIcons(), 50);
                this.renderCharts();
            },

            changePeriod() {
                window.location.href = `/admin/analisis?period=${this.period}`;
            },

            renderCharts() {
                // Tren Partisipasi Chart (Bar Chart)
                const trenLabels = this.data.tren_partisipasi.map(d => d.bulan);
                const trenValues = this.data.tren_partisipasi.map(d => d.partisipasi);
                
                const ctxTren = document.getElementById('trenChart').getContext('2d');
                new Chart(ctxTren, {
                    type: 'bar',
                    data: {
                        labels: trenLabels,
                        datasets: [{
                            label: 'Jumlah Pekerja',
                            data: trenValues,
                            backgroundColor: '#3b82f6',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true },
                            x: { grid: { display: false } }
                        }
                    }
                });

                // Sebaran Program Chart (Pie Chart)
                const sebaranLabels = this.data.sebaran_program.length > 0 ? this.data.sebaran_program.map(d => d.name) : ['Belum Ada Program'];
                const sebaranValues = this.data.sebaran_program.length > 0 ? this.data.sebaran_program.map(d => d.value) : [1];
                const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884d8', '#82ca9d'];
                
                const ctxSebaran = document.getElementById('sebaranChart').getContext('2d');
                new Chart(ctxSebaran, {
                    type: 'pie',
                    data: {
                        labels: sebaranLabels,
                        datasets: [{
                            data: sebaranValues,
                            backgroundColor: this.data.sebaran_program.length > 0 ? COLORS : ['#e2e8f0'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        }));
    });
</script>
@endsection
