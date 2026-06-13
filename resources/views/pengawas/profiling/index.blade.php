@extends('layouts.app')

@section('title', 'Profiling Pekerja')

@section('content')
<div style="padding: 2rem;">
    <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">Profiling Pekerja</h1>
    <p style="color: var(--text-muted); margin-bottom: 2rem;">Analisis dan klasifikasi data pekerja berdasarkan status ekonomi dan kemampuan.</p>

    <!-- Overview Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="background: white; border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Total Pekerja</div>
                    <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main);">{{ $totalWorkers }}</div>
                </div>
                <div style="background: #eff6ff; color: #3b82f6; padding: 0.75rem; border-radius: 8px;">
                    <i data-lucide="users" style="width: 24px; height: 24px;"></i>
                </div>
            </div>
        </div>

        <div style="background: white; border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Kategori Miskin</div>
                    <div style="font-size: 1.75rem; font-weight: 700; color: #ef4444;">{{ $miskinCount }}</div>
                    <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;">{{ $persentaseMiskin }}% dari total pekerja</div>
                </div>
                <div style="background: #fef2f2; color: #ef4444; padding: 0.75rem; border-radius: 8px;">
                    <i data-lucide="trending-down" style="width: 24px; height: 24px;"></i>
                </div>
            </div>
        </div>

        <div style="background: white; border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Pekerjaan Mayoritas</div>
                    @php
                        $mayoritasMakro = !empty($pekerjaanMakroStats) ? array_keys($pekerjaanMakroStats, max($pekerjaanMakroStats))[0] : '-';
                    @endphp
                    <div style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-top: 0.5rem;">{{ $mayoritasMakro }}</div>
                </div>
                <div style="background: #f0fdf4; color: #22c55e; padding: 0.75rem; border-radius: 8px;">
                    <i data-lucide="briefcase" style="width: 24px; height: 24px;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="background: white; border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <h2 style="font-size: 1.125rem; font-weight: 600; color: var(--text-main); margin-bottom: 1rem;">Klasifikasi Kesejahteraan</h2>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="kesejahteraanChart"></canvas>
            </div>
        </div>

        <div style="background: white; border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <h2 style="font-size: 1.125rem; font-weight: 600; color: var(--text-main); margin-bottom: 1rem;">Sektor Pekerjaan Makro</h2>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="pekerjaanMakroChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h2 style="font-size: 1.125rem; font-weight: 600; color: var(--text-main); margin-bottom: 1rem;">Daftar Profiling Pekerja</h2>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted);">
                        <th style="padding: 1rem; font-weight: 600;">Nama</th>
                        <th style="padding: 1rem; font-weight: 600;">Kemampuan Utama</th>
                        <th style="padding: 1rem; font-weight: 600;">Pekerjaan Makro</th>
                        <th style="padding: 1rem; font-weight: 600;">Pendapatan / Kapita</th>
                        <th style="padding: 1rem; font-weight: 600;">Klasifikasi Kesejahteraan</th>
                        <th style="padding: 1rem; font-weight: 600;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($workers as $worker)
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 1rem; color: var(--text-main); font-weight: 500;">
                            {{ $worker->nama }}
                        </td>
                        <td style="padding: 1rem; color: var(--text-muted);">
                            {{ $worker->kemampuan_utama ?? '-' }}
                        </td>
                        <td style="padding: 1rem;">
                            <span style="background: #f1f5f9; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; color: var(--text-main);">
                                {{ $worker->pekerjaan_makro }}
                            </span>
                        </td>
                        <td style="padding: 1rem; color: var(--text-main);">
                            Rp {{ number_format($worker->total_pendapatan, 0, ',', '.') }}
                        </td>
                        <td style="padding: 1rem;">
                            @if($worker->klasifikasi_kesejahteraan == 'Sangat Miskin' || $worker->klasifikasi_kesejahteraan == 'Miskin')
                                <span style="background: #fef2f2; color: #ef4444; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500;">
                                    {{ $worker->klasifikasi_kesejahteraan }}
                                </span>
                            @elseif($worker->klasifikasi_kesejahteraan == 'Rentan Miskin')
                                <span style="background: #fef9c3; color: #ca8a04; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500;">
                                    {{ $worker->klasifikasi_kesejahteraan }}
                                </span>
                            @elseif($worker->klasifikasi_kesejahteraan == 'Tidak Diketahui')
                                <span style="background: #f1f5f9; color: #64748b; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500;">
                                    {{ $worker->klasifikasi_kesejahteraan }}
                                </span>
                            @else
                                <span style="background: #f0fdf4; color: #16a34a; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500;">
                                    {{ $worker->klasifikasi_kesejahteraan }}
                                </span>
                            @endif
                        </td>
                        <td style="padding: 1rem;">
                            <a href="/pengawas/pekerja/{{ $worker->id }}/profil" class="btn btn-primary btn-sm" style="text-decoration: none;">Profil</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding: 2rem; text-align: center; color: var(--text-muted);">
                            Belum ada data pekerja yang dapat dianalisis.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data for Kesejahteraan Chart
        const kesejahteraanData = @json($kesejahteraanStats);
        const kesejahteraanCtx = document.getElementById('kesejahteraanChart').getContext('2d');
        
        new Chart(kesejahteraanCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(kesejahteraanData),
                datasets: [{
                    data: Object.values(kesejahteraanData),
                    backgroundColor: [
                        '#ef4444', // Merah (Sangat Miskin / Miskin)
                        '#f59e0b', // Orange (Rentan)
                        '#22c55e', // Hijau (Sejahtera)
                        '#9ca3af'  // Abu (Tidak diketahui)
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '70%'
            }
        });

        // Data for Pekerjaan Makro Chart
        const makroData = @json($pekerjaanMakroStats);
        const makroCtx = document.getElementById('pekerjaanMakroChart').getContext('2d');
        
        new Chart(makroCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(makroData),
                datasets: [{
                    label: 'Jumlah Pekerja',
                    data: Object.values(makroData),
                    backgroundColor: '#3b82f6',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
