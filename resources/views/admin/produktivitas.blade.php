@extends('layouts.app')
@section('title', 'Tren Produktivitas Warga')

@section('content')
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div style="padding: 2rem; max-width: 1200px; margin: 0 auto;" x-data="produktivitasData()">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.8rem; font-weight: bold; color: var(--text-main); margin-bottom: 0.5rem;">
                Analisis Tren Produktivitas Warga
            </h1>
            <p style="color: var(--text-muted);">
                Visualisasi data perbandingan jumlah pekerjaan yang selesai antar periode guna mengevaluasi efektivitas program kerja.
            </p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card" style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid var(--border-color);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Total Pekerjaan Selesai</p>
                    <h3 style="font-size: 1.8rem; font-weight: bold; color: var(--text-main);" x-text="totalSelesai"></h3>
                </div>
                <div style="background: var(--primary-light); padding: 0.75rem; border-radius: 8px; color: var(--primary);">
                    <i data-lucide="check-circle" style="width: 24px; height: 24px;"></i>
                </div>
            </div>
            <div style="margin-top: 1rem; font-size: 0.875rem; color: var(--text-muted);">
                Dari <span x-text="data.length"></span> periode tercatat
            </div>
        </div>

        <div class="card" style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid var(--border-color);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Rata-rata Penyelesaian</p>
                    <h3 style="font-size: 1.8rem; font-weight: bold; color: var(--text-main);" x-text="avgSelesai"></h3>
                </div>
                <div style="background: #e0e7ff; padding: 0.75rem; border-radius: 8px; color: #4f46e5;">
                    <i data-lucide="activity" style="width: 24px; height: 24px;"></i>
                </div>
            </div>
            <div style="margin-top: 1rem; font-size: 0.875rem; color: var(--text-muted);">
                Pekerjaan per bulan
            </div>
        </div>

        <div class="card" style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid var(--border-color);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Tren Bulan Terakhir</p>
                    <h3 style="font-size: 1.8rem; font-weight: bold;" :style="'color: ' + (isPositiveTrend ? '#16a34a' : '#dc2626')">
                        <span x-text="(isPositiveTrend ? '+' : '') + trendPercentage.toFixed(1) + '%'"></span>
                    </h3>
                </div>
                <div :style="'background: ' + (isPositiveTrend ? '#dcfce7' : '#fee2e2') + '; padding: 0.75rem; border-radius: 8px; color: ' + (isPositiveTrend ? '#16a34a' : '#dc2626')">
                    <i data-lucide="trending-up" style="width: 24px; height: 24px;"></i>
                </div>
            </div>
            <div style="margin-top: 1rem; font-size: 0.875rem; color: var(--text-muted);">
                Dibanding bulan sebelumnya
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div style="display: grid; grid-template-columns: 1fr; gap: 2rem;">
        <!-- Bar Chart -->
        <div class="card" style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid var(--border-color);">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem;">
                <i data-lucide="activity" style="width: 20px; height: 20px; color: var(--secondary);"></i>
                <h3 style="font-size: 1.125rem; font-weight: bold; color: var(--text-main); margin: 0;">
                    Perbandingan Antar Periode (Batang)
                </h3>
            </div>
            <div style="height: 300px; width: 100%;">
                <canvas id="barChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('produktivitasData', () => ({
            data: @json($data),
            
            get totalSelesai() {
                return this.data.reduce((sum, item) => sum + item.PekerjaanSelesai, 0);
            },
            
            get avgSelesai() {
                return this.data.length > 0 ? (this.totalSelesai / this.data.length).toFixed(1) : 0;
            },
            
            get trendPercentage() {
                if (this.data.length >= 2) {
                    const lastMonth = this.data[this.data.length - 1].PekerjaanSelesai;
                    const prevMonth = this.data[this.data.length - 2].PekerjaanSelesai;
                    if (prevMonth > 0) {
                        return ((lastMonth - prevMonth) / prevMonth) * 100;
                    }
                }
                return 0;
            },
            
            get isPositiveTrend() {
                return this.trendPercentage >= 0;
            },

            init() {
                setTimeout(() => lucide.createIcons(), 50);
                this.renderCharts();
            },

            renderCharts() {
                const labels = this.data.map(d => d.name);
                const dataRencana = this.data.map(d => d.PekerjaanRencana);
                const dataBerjalan = this.data.map(d => d.PekerjaanBerjalan);
                const dataSelesai = this.data.map(d => d.PekerjaanSelesai);

                const ctxBar = document.getElementById('barChart').getContext('2d');
                new Chart(ctxBar, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Jumlah Pekerjaan Rencana',
                                data: dataRencana,
                                backgroundColor: '#cbd5e1',
                                borderRadius: 4
                            },
                            {
                                label: 'Jumlah Pekerjaan Berjalan',
                                data: dataBerjalan,
                                backgroundColor: '#fbbf24',
                                borderRadius: 4
                            },
                            {
                                label: 'Jumlah Pekerjaan Selesai',
                                data: dataSelesai,
                                backgroundColor: '#10b981', // var(--secondary) approx
                                borderRadius: 4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        scales: {
                            y: { beginAtZero: true },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }
        }));
    });
</script>
@endsection
