@extends('layouts.app')
@section('title', 'Profil Pekerja — ' . $worker->nama)

@section('content')
@php
    $backUrl = request()->is('pengawas/*') ? '/pengawas/profiling' : '/admin/pekerja';
@endphp
<div class="animate-fade-in" style="padding: 2rem;">
    @if(session('success'))
        <div style="background: #f0fdf4; color: #166534; padding: 0.75rem; border-radius: 8px; margin-bottom: 1.5rem;">{{ session('success') }}</div>
    @endif
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
                Skor vulnerabilitas: <strong>{{ $worker->skor_vulnerabilitas ?? '—' }}</strong> ({{ $worker->prioritas_label }}).
                Total skor profiling: <strong>{{ $worker->total_skor ?? '—' }}</strong> — {{ $worker->status_kesejahteraan ?? 'Pending' }}.
                @if($worker->riwayat_penyakit)
                    Catatan kesehatan (SDG 3): {{ $worker->riwayat_penyakit }}.
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

    @if($worker->profiling_awal)
    <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 1.5rem;">
        <h3 style="margin: 0 0 1rem;">Perbandingan Profiling Awal vs Sekarang</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted);">
                    <th style="padding: 0.75rem; text-align: left;">Indikator</th>
                    <th style="padding: 0.75rem; text-align: left;">Awal</th>
                    <th style="padding: 0.75rem; text-align: left;">Sekarang</th>
                </tr>
            </thead>
            <tbody>
                @foreach([
                    'frekuensi_makan' => 'Frekuensi Makan (SDG 2)',
                    'status_gizi' => 'Status Gizi',
                    'kondisi_sanitasi' => 'Sanitasi',
                    'pendidikan_terakhir' => 'Pendidikan',
                    'skor_vulnerabilitas' => 'Skor Vulnerabilitas',
                ] as $key => $label)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 0.75rem;">{{ $label }}</td>
                    <td style="padding: 0.75rem;">{{ $worker->profiling_awal[$key] ?? '—' }}</td>
                    <td style="padding: 0.75rem; font-weight: 500;">
                        @if($key === 'skor_vulnerabilitas')
                            {{ $worker->skor_vulnerabilitas ?? '—' }}
                        @else
                            {{ $worker->$key ?? '—' }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if(auth()->user()->role === 'admin' && $worker->status_program === 'aktif')
            <form method="POST" action="{{ route('admin.profiling.lulus', $worker->id) }}" style="margin-top: 1rem; display: inline;" onsubmit="return confirm('Tandai peserta lulus program?');">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">Tandai Lulus Program</button>
            </form>
        @endif
        @if($worker->status_program === 'aktif')
        <details style="margin-top: 1.25rem;">
            <summary class="btn btn-outline btn-sm" style="cursor: pointer; display: inline-block;">Update Profiling (Survei Ulang)</summary>
            <form method="POST" action="{{ route(auth()->user()->role === 'admin' ? 'admin.profiling.update' : 'pengawas.profiling.update', $worker->id) }}"
                  enctype="multipart/form-data" style="margin-top: 1rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                @csrf
                <div class="form-group">
                    <label class="form-label">Frekuensi Makan *</label>
                    <select name="frekuensi_makan" class="form-input" required>
                        @foreach(['1 kali', '2 kali', '3 kali atau lebih'] as $opt)
                            <option value="{{ $opt }}" @selected(old('frekuensi_makan', $worker->frekuensi_makan) === $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Kondisi Sanitasi *</label>
                    <select name="kondisi_sanitasi" class="form-input" required>
                        @foreach(['Tidak Ada Jamban', 'Jamban Bersama', 'Jamban Sendiri', 'Jamban Sendiri + Septic Tank'] as $opt)
                            <option value="{{ $opt }}" @selected(old('kondisi_sanitasi', $worker->kondisi_sanitasi) === $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Pendidikan *</label>
                    <select name="pendidikan_terakhir" class="form-input" required>
                        @foreach(['Tidak Sekolah', 'SD / Sederajat', 'SMP / Sederajat', 'SMA / Sederajat', 'Diploma / S1+'] as $opt)
                            <option value="{{ $opt }}" @selected(old('pendidikan_terakhir', $worker->pendidikan_terakhir) === $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Gizi</label>
                    <select name="status_gizi" class="form-input">
                        <option value="">— Tidak diubah —</option>
                        @foreach(['Buruk', 'Kurang', 'Normal'] as $opt)
                            <option value="{{ $opt }}" @selected(old('status_gizi', $worker->status_gizi) === $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Bukti Foto</label>
                    <input type="file" name="bukti_foto_kondisi" class="form-input" accept="image/*" />
                </div>
                <div class="form-group">
                    <label class="form-label">Catatan</label>
                    <input type="text" name="catatan" class="form-input" value="{{ old('catatan') }}" placeholder="Catatan pemantauan..." />
                </div>
                <div style="grid-column: 1 / -1;">
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Update Profiling</button>
                </div>
            </form>
        </details>
        @endif
    </div>
    @endif

    @if($worker->profilingHistories->isNotEmpty())
    <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 1.5rem;">
        <h3 style="margin: 0 0 1rem;">Riwayat Profiling (Skor per Dimensi)</h3>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted);">
                        <th style="padding: 0.75rem; text-align: left;">Tanggal</th>
                        <th style="padding: 0.75rem;">Makan</th>
                        <th style="padding: 0.75rem;">Sanitasi</th>
                        <th style="padding: 0.75rem;">Pendapatan</th>
                        <th style="padding: 0.75rem;">Pendidikan</th>
                        <th style="padding: 0.75rem;">Total</th>
                        <th style="padding: 0.75rem;">Kategori</th>
                        <th style="padding: 0.75rem;">Bukti</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($worker->profilingHistories->take(10) as $hist)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 0.75rem;">{{ $hist->created_at->format('d M Y H:i') }}</td>
                        <td style="padding: 0.75rem; text-align: center;">{{ $hist->skor_makan }}</td>
                        <td style="padding: 0.75rem; text-align: center;">{{ $hist->skor_sanitasi }}</td>
                        <td style="padding: 0.75rem; text-align: center;">{{ $hist->skor_pendapatan }}</td>
                        <td style="padding: 0.75rem; text-align: center;">{{ $hist->skor_pendidikan }}</td>
                        <td style="padding: 0.75rem; text-align: center; font-weight: 700;">{{ $hist->total_skor }}</td>
                        <td style="padding: 0.75rem;">{{ $hist->kategori_kelayakan }}</td>
                        <td style="padding: 0.75rem;">
                            @if($hist->bukti_foto_kondisi)
                                <a href="{{ $hist->bukti_foto_kondisi }}" target="_blank" style="color: var(--primary);">Lihat</a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($worker->profilingSnapshots->isNotEmpty())
    <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 1.5rem;">
        <h3 style="margin: 0 0 1rem;">Riwayat Pemantauan Profiling</h3>
        @foreach($worker->profilingSnapshots->take(5) as $snap)
            <div style="border-bottom: 1px solid var(--border); padding: 0.75rem 0; font-size: 0.875rem;">
                <strong>{{ $snap->recorded_at?->format('d M Y') }}</strong> — Skor {{ $snap->skor_vulnerabilitas }},
                Makan: {{ $snap->frekuensi_makan ?? '—' }}.
                <span style="color: var(--text-muted);">{{ $snap->catatan }}</span>
            </div>
        @endforeach
    </div>
    @endif

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
                        {{ $program->jenis_program ?? 'Program' }} · {{ $program->desa_lokasi ?? $program->lokasi ?? 'Lokasi belum diisi' }}
                        @if($worker->desa_asal && ($program->desa_lokasi ?? $program->lokasi) && $worker->desa_asal !== ($program->desa_lokasi ?? $program->lokasi))
                            <span style="color: #d97706; font-weight: 600;"> · Lintas Desa</span>
                        @endif
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
