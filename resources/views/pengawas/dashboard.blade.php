@extends('layouts.app')
@section('title', 'Dashboard Pengawas Lapangan')

@section('content')
<div class="animate-fade-in" style="padding: 2rem;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.8rem; color: var(--text-main);">Dashboard Pengawas Lapangan</h1>
        <p style="color: var(--text-muted);">Selamat bertugas, {{ Auth::user()->nama }}. Berikut adalah status pekerjaan lapangan hari ini.</p>
    </div>

    <!-- Cards Statistik -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
        <div class="glass-panel stat-card" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%); border: 1px solid var(--primary); padding: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <div style="padding: 0.75rem; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center;">
                    <i data-lucide="check-circle" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <h3 style="margin: 0; color: var(--text-main);">Progress Validasi (Pending)</h3>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">Bukti Kerja belum diunggah</p>
                </div>
            </div>
            <div class="stat-value" style="color: var(--primary); font-size: 2rem; font-weight: bold;">
                {{ $stats['pendingLogbooks'] }} Tugas
            </div>
        </div>

        <div class="glass-panel stat-card" style="padding: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <div style="padding: 0.75rem; background: var(--secondary); color: white; border-radius: 50%; display: flex; align-items: center;">
                    <i data-lucide="clock" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <h3 style="margin: 0; color: var(--text-main);">Jadwal Hari Ini</h3>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">Total lokasi penugasan</p>
                </div>
            </div>
            <div class="stat-value" style="font-size: 2rem; font-weight: bold; color: var(--text-main);">
                {{ $stats['todaySchedules'] }} Lokasi
            </div>
        </div>

        <div class="glass-panel stat-card" style="border-left: 4px solid var(--danger); padding: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <div style="padding: 0.75rem; background: var(--danger); color: white; border-radius: 50%; display: flex; align-items: center;">
                    <i data-lucide="alert-triangle" style="width: 24px; height: 24px;"></i>
                </div>
                <div>
                    <h3 style="margin: 0; color: var(--text-main);">Kendala Lapangan</h3>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">Dilaporkan hari ini</p>
                </div>
            </div>
            <div class="stat-value" style="color: var(--danger); font-size: 2rem; font-weight: bold;">
                {{ $stats['reportedProblems'] }} Kendala
            </div>
        </div>
    </div>

    <!-- Daftar Pekerjaan Hari Ini -->
    <div class="glass-panel" style="padding: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
            <i data-lucide="activity" style="width: 24px; height: 24px; color: var(--primary);"></i>
            <h3 style="margin: 0; font-size: 1.2rem; color: var(--text-main);">Monitoring Pekerjaan Hari Ini</h3>
        </div>

        @if($schedules->isEmpty())
            <div style="padding: 2rem; text-align: center; color: var(--text-muted); background: var(--background); border-radius: var(--radius-sm);">
                Belum ada jadwal penugasan hari ini.
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @foreach($schedules as $schedule)
                    @php
                        $progres = $schedule->progres_persentase ?? 0;
                    @endphp
                    <div style="
                        display: flex; 
                        flex-direction: column; 
                        gap: 1rem;
                        padding: 1.25rem; 
                        background: var(--background); 
                        border-radius: var(--radius-sm);
                        border-left: 4px solid {{ $progres == 100 ? 'var(--primary)' : 'var(--warning)' }}
                    ">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                            <div>
                                <h4 style="margin: 0 0 0.5rem 0; font-size: 1.1rem; color: var(--text-main);">{{ $schedule->tugas }}</h4>
                                <div style="display: flex; align-items: center; gap: 1rem; color: var(--text-muted); font-size: 0.9rem; flex-wrap: wrap;">
                                    <span style="display: flex; align-items: center; gap: 0.25rem;">
                                        <i data-lucide="clock" style="width: 16px; height: 16px;"></i> 
                                        {{ \Carbon\Carbon::parse($schedule->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->jam_selesai ?? $schedule->jam_mulai)->format('H:i') }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <span class="badge {{ $progres == 100 ? 'badge-primary' : 'badge-warning' }}">
                                    {{ $progres == 100 ? 'Selesai' : ($schedule->logbook_id ? 'Dalam Proses' : 'Belum Mulai') }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.85rem; color: var(--text-main);">
                                <span>Progres Pekerjaan</span>
                                <span style="font-weight: bold;">{{ $progres }}%</span>
                            </div>
                            <div style="width: 100%; height: 8px; background: rgba(0,0,0,0.1); border-radius: 4px; overflow: hidden;">
                                <div style="
                                    height: 100%; 
                                    width: {{ $progres }}%; 
                                    background: {{ $progres == 100 ? 'var(--primary)' : 'var(--warning)' }};
                                    transition: width 0.5s ease-in-out;
                                "></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>
@endsection
