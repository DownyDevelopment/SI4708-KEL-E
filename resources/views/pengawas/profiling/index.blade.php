@extends('layouts.app')

@section('title', 'Profiling & Analisis Kesejahteraan')

@section('content')
<div style="padding: 2rem;">
    <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">Profiling & Analisis Kesejahteraan</h1>
    <p style="color: var(--text-muted); margin-bottom: 2rem;">Survei indikator Kemensos/BPS — filter threshold (layak vs tidak layak), prioritas penugasan, dan pemantauan progres SDG 1/2/3.</p>

    @if(session('success'))
        <div style="background: #f0fdf4; color: #166534; padding: 0.75rem; border-radius: 8px; margin-bottom: 1.5rem;">{{ session('success') }}</div>
    @endif

    <!-- Overview Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="background: white; border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Total Survei</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main);">{{ $totalWorkers }}</div>
        </div>
        <div style="background: white; border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Layak Program</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #16a34a;">{{ $layakCount }}</div>
            <div style="font-size: 0.875rem; color: var(--text-muted);">{{ $persentaseLayak }}% dari survei</div>
        </div>
        <div style="background: white; border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Tidak Layak (Threshold)</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #64748b;">{{ $tidakLayakCount }}</div>
            <div style="font-size: 0.875rem; color: var(--text-muted);">Skor &lt; 6 — tidak masuk antrean</div>
        </div>
        <div style="background: white; border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Lulus Program</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #3b82f6;">{{ $lulusCount }}</div>
            <div style="font-size: 0.875rem; color: var(--text-muted);">Slot dialihkan ke calon baru</div>
        </div>
    </div>

    <!-- Charts -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="background: white; border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <h2 style="font-size: 1.125rem; font-weight: 600; color: var(--text-main); margin-bottom: 1rem;">Distribusi Prioritas (Threshold)</h2>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="prioritasChart"></canvas>
            </div>
        </div>

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
                        <th style="padding: 1rem; font-weight: 600;">Total Skor</th>
                        <th style="padding: 1rem; font-weight: 600;">Kategori</th>
                        <th style="padding: 1rem; font-weight: 600;">Makan/Hari</th>
                        <th style="padding: 1rem; font-weight: 600;">Desa Asal</th>
                        <th style="padding: 1rem; font-weight: 600;">Status</th>
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
                        <td style="padding: 1rem; color: var(--text-main); font-weight: 700;">
                            {{ $worker->total_skor ?? $worker->skor_vulnerabilitas ?? '—' }}
                        </td>
                        <td style="padding: 1rem;">
                            <span style="background: #f1f5f9; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem;">
                                {{ $worker->status_kesejahteraan ?? $worker->prioritas_label }}
                            </span>
                        </td>
                        <td style="padding: 1rem; color: var(--text-muted);">
                            {{ $worker->frekuensi_makan ?? '—' }}
                        </td>
                        <td style="padding: 1rem; color: var(--text-muted);">
                            {{ $worker->desa_asal ?? '—' }}
                        </td>
                        <td style="padding: 1rem;">
                            {{ $worker->status_program_label }}
                        </td>
                        <td style="padding: 1rem;">
                            @php
                                $profilUrl = request()->is('admin/*') ? '/admin/pekerja/' . $worker->id . '/profil' : '/pengawas/pekerja/' . $worker->id . '/profil';
                                $lulusRoute = request()->is('admin/*') ? route('admin.profiling.lulus', $worker->id) : null;
                            @endphp
                            <a href="{{ $profilUrl }}" class="btn btn-primary btn-sm" style="text-decoration: none;">Profil</a>
                            @if($worker->status_program === 'aktif')
                                <a href="{{ $profilUrl }}" class="btn btn-outline btn-sm" style="text-decoration: none;">Update Profiling</a>
                            @endif
                            @if($lulusRoute && $worker->status_program === 'aktif' && auth()->user()->role === 'admin')
                                <form method="POST" action="{{ $lulusRoute }}" style="display: inline;" onsubmit="return confirm('Tandai {{ $worker->nama }} lulus program?');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm">Lulus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="padding: 2rem; text-align: center; color: var(--text-muted);">
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
        // Data for Prioritas Chart
        const prioritasData = @json($prioritasStats ?? []);
        if (document.getElementById('prioritasChart')) {
            const prioritasCtx = document.getElementById('prioritasChart').getContext('2d');
            new Chart(prioritasCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(prioritasData).map(k => k.replace('_', ' ')),
                    datasets: [{
                        data: Object.values(prioritasData),
                        backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6', '#9ca3af'],
                        borderWidth: 0,
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, cutout: '70%' }
            });
        }

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
