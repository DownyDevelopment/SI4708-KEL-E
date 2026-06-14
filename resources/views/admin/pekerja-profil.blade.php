@extends('layouts.app')
@section('title', 'Profil Pekerja — ' . $worker->nama)

@section('content')
@php
    $backUrl = request()->is('pengawas/*') ? '/pengawas/profiling' : '/admin/pekerja';
@endphp
<div class="animate-fade-in" style="padding: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
        <div>
            <a href="{{ $backUrl }}" style="display: inline-flex; align-items: center; gap: 0.35rem; color: var(--text-muted); font-size: 0.875rem; text-decoration: none; margin-bottom: 0.75rem;">
                <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Kembali
            </a>
            <h1 style="font-size: 1.8rem; margin: 0; color: var(--text-main);">Profil Pekerja</h1>
            <p style="color: var(--text-muted); margin: 0.35rem 0 0;">Ringkasan kemampuan, program diikuti, dan jadwal kerja.</p>
        </div>
        @if(auth()->user()->role === 'admin')
            <a href="/admin/pekerja?edit={{ $worker->id }}" class="btn btn-outline btn-sm" style="text-decoration: none;">Edit Data Pekerja</a>
        @endif
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; margin-bottom: 1.5rem;">
        <div class="glass-panel" style="padding: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700;">
                    {{ strtoupper(substr($worker->nama, 0, 1)) }}
                </div>
                <div>
                    <h2 style="margin: 0; font-size: 1.25rem;">{{ $worker->nama }}</h2>
                    <span class="badge badge-success" style="margin-top: 0.35rem; display: inline-block;">{{ $worker->kemampuan_utama ?: 'Umum' }}</span>
                </div>
            </div>

            <dl style="display: grid; gap: 0.75rem; font-size: 0.9rem;">
                <div>
                    <dt style="color: var(--text-muted); margin-bottom: 0.15rem;">Usia</dt>
                    <dd style="margin: 0; font-weight: 500;">{{ $usia !== null ? $usia . ' tahun' : '—' }}</dd>
                </div>
                <div>
                    <dt style="color: var(--text-muted); margin-bottom: 0.15rem;">Jenis Kelamin</dt>
                    <dd style="margin: 0; font-weight: 500;">{{ $worker->jenis_kelamin === 'P' ? 'Perempuan' : 'Laki-laki' }}</dd>
                </div>
                <div>
                    <dt style="color: var(--text-muted); margin-bottom: 0.15rem;">Kontak</dt>
                    <dd style="margin: 0; font-weight: 500;">{{ $worker->no_telepon ?: '—' }}</dd>
                </div>
                <div>
                    <dt style="color: var(--text-muted); margin-bottom: 0.15rem;">Keluarga</dt>
                    <dd style="margin: 0; font-weight: 500;">{{ $worker->household?->kepala_keluarga ?? '—' }}</dd>
                </div>
                <div>
                    <dt style="color: var(--text-muted); margin-bottom: 0.15rem;">Sektor Pekerjaan</dt>
                    <dd style="margin: 0; font-weight: 500;">{{ $worker->pekerjaan_makro }}</dd>
                </div>
                <div>
                    <dt style="color: var(--text-muted); margin-bottom: 0.15rem;">Alamat</dt>
                    <dd style="margin: 0;">{{ $worker->alamat ?: '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="glass-panel" style="padding: 1.5rem;">
            <h3 style="margin: 0 0 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="award" style="width: 20px; height: 20px; color: var(--primary);"></i>
                Ringkasan Kemampuan
            </h3>
            <p style="color: var(--text-main); margin: 0 0 1rem; line-height: 1.6;">
                Keahlian utama: <strong>{{ $worker->kemampuan_utama ?: 'Belum diisi' }}</strong>.
                @if($worker->riwayat_penyakit)
                    Catatan kesehatan: {{ $worker->riwayat_penyakit }}.
                @endif
            </p>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                <div style="background: var(--background); border-radius: 8px; padding: 1rem; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">{{ $programs->count() }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">Program Diikuti</div>
                </div>
                <div style="background: var(--background); border-radius: 8px; padding: 1rem; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">{{ $schedules->count() }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">Jadwal Tugas</div>
                </div>
                <div style="background: var(--background); border-radius: 8px; padding: 1rem; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">{{ $worker->klasifikasi_kesejahteraan }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">Klasifikasi Ekonomi</div>
                </div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        <div class="glass-panel" style="padding: 1.5rem;">
            <h3 style="margin: 0 0 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="map-pin" style="width: 20px; height: 20px; color: var(--primary);"></i>
                Program yang Diikuti
            </h3>
            @forelse($programs as $program)
                <div style="border: 1px solid var(--border); border-radius: 8px; padding: 1rem; margin-bottom: 0.75rem;">
                    <div style="font-weight: 600;">{{ $program->nama_program }}</div>
                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">
                        {{ $program->jenis_program ?? 'Program' }} · {{ $program->lokasi ?? 'Lokasi belum diisi' }}
                    </div>
                    <span class="badge" style="margin-top: 0.5rem; display: inline-block; background: rgba(59,130,246,0.1); color: var(--primary);">
                        {{ $program->status ?? 'Berjalan' }}
                    </span>
                </div>
            @empty
                <p style="color: var(--text-muted); text-align: center; padding: 1rem 0;">Belum terdaftar di program manapun.</p>
            @endforelse
        </div>

        <div class="glass-panel" style="padding: 1.5rem;">
            <h3 style="margin: 0 0 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="calendar-clock" style="width: 20px; height: 20px; color: var(--primary);"></i>
                Jadwal Kerja
            </h3>
            @forelse($schedules as $schedule)
                <div style="border: 1px solid var(--border); border-radius: 8px; padding: 1rem; margin-bottom: 0.75rem;">
                    <div style="display: flex; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap;">
                        <strong>{{ $schedule->program?->nama_program ?? 'Program' }}</strong>
                        <span style="font-size: 0.85rem; color: var(--text-muted);">
                            {{ $schedule->tanggal ? \Carbon\Carbon::parse($schedule->tanggal)->format('d M Y') : '—' }}
                        </span>
                    </div>
                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.35rem;">
                        {{ $schedule->jam_mulai ?? '—' }} – {{ $schedule->jam_selesai ?? '—' }}
                        @if($schedule->shift_label)
                            · {{ $schedule->shift_label }}
                        @endif
                    </div>
                    @php
                        $statusColors = [
                            'completed' => ['#dcfce7', '#16a34a'],
                            'in_progress' => ['#fef3c7', '#d97706'],
                            'scheduled' => ['#f1f5f9', '#64748b'],
                        ];
                        [$bg, $fg] = $statusColors[$schedule->status] ?? ['#f1f5f9', '#64748b'];
                    @endphp
                    <span style="margin-top: 0.5rem; display: inline-block; padding: 0.2rem 0.6rem; border-radius: 99px; font-size: 0.75rem; background: {{ $bg }}; color: {{ $fg }};">
                        {{ $schedule->status ?? 'scheduled' }}
                    </span>
                </div>
            @empty
                <p style="color: var(--text-muted); text-align: center; padding: 1rem 0;">Belum ada jadwal kerja.</p>
            @endforelse
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endsection
