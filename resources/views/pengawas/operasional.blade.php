@extends('layouts.app')
@section('title', 'Operasional & Penjadwalan')

@section('content')
<div class="animate-fade-in" style="padding: 2rem;" x-data="operasionalData()">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.8rem; display: flex; align-items: center; gap: 0.5rem; color: var(--text-main);">
            <i data-lucide="calendar-clock" style="width: 28px; height: 28px; color: var(--primary);"></i>
            Operasional & Penjadwalan
        </h1>
        <p style="color: var(--text-muted); margin-top: 0.5rem;">
            Kelola jadwal kerja harian/mingguan dan catat logbook progres pekerjaan lapangan.
        </p>
    </div>

    @if(session('success'))
        <div class="glass-panel" style="margin-bottom: 1rem; padding: 0.85rem 1.25rem; border-left: 4px solid var(--primary); color: var(--text-main);">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="glass-panel" style="margin-bottom: 1rem; padding: 0.85rem 1.25rem; border-left: 4px solid var(--danger); color: var(--danger);">
            @foreach($errors->all() as $error)
                <p style="margin:0;">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
        <button type="button" class="btn" :class="activeTab === 'jadwal' ? 'btn-primary' : 'btn-outline'" @click="setTab('jadwal')">
            <i data-lucide="calendar" style="width: 16px; height: 16px; margin-right: 6px;"></i> Jadwal Kerja
        </button>
        <button type="button" class="btn" :class="activeTab === 'logbook' ? 'btn-primary' : 'btn-outline'" @click="setTab('logbook')">
            <i data-lucide="notebook-pen" style="width: 16px; height: 16px; margin-right: 6px;"></i> Logbook Harian
        </button>
    </div>

    <div x-show="activeTab === 'jadwal'" x-cloak>
        <div class="glass-panel" style="padding: 1.5rem;">
            <h3 style="margin: 0 0 1rem; color: var(--text-main);">Daftar Jadwal</h3>
            <div style="overflow-x: auto;">
                <table class="data-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                            <th style="padding: 0.75rem;">Program</th>
                            <th style="padding: 0.75rem;">Tanggal</th>
                            <th style="padding: 0.75rem;">Pekerja</th>
                            <th style="padding: 0.75rem;">Progres</th>
                            <th style="padding: 0.75rem;">Status</th>
                            <th style="padding: 0.75rem;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwal as $item)
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 0.75rem; font-weight: 500;">{{ $item->tugas ?? '—' }}</td>
                                <td style="padding: 0.75rem;">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d M Y') : '—' }}</td>
                                <td style="padding: 0.75rem; font-size: 0.85rem;">
                                    @if(!empty($item->pekerja_nama))
                                        {{ implode(', ', $item->pekerja_nama) }}
                                    @else
                                        <span style="color: var(--text-muted);">Belum ditugaskan</span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem; min-width: 140px;">
                                    <div class="progress-track">
                                        <div class="progress-fill" style="width: {{ min(100, (int)($item->progres_terakhir ?? 0)) }}%;"></div>
                                    </div>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);">{{ (int)($item->progres_terakhir ?? 0) }}%</span>
                                </td>
                                <td style="padding: 0.75rem;">
                                    @php
                                        $statusClass = match($item->status) {
                                            'completed' => 'badge-success',
                                            'in_progress' => 'badge-primary',
                                            default => 'badge-warning',
                                        };
                                        $statusLabel = match($item->status) {
                                            'completed' => 'Selesai',
                                            'in_progress' => 'Berjalan',
                                            'scheduled' => 'Terjadwal',
                                            default => $item->status,
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <button type="button" class="btn btn-outline btn-sm" @click="openLogbookForSchedule(@js($item))">
                                        Isi Logbook
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 1.5rem; text-align: center; color: var(--text-muted);">
                                    Belum ada jadwal. Admin desa akan menambahkan jadwal baru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'logbook'" x-cloak>
        <div style="display: grid; grid-template-columns: minmax(280px, 1fr) 1.6fr; gap: 1.5rem;">
            <div>
                <h3 style="margin-bottom: 1rem; color: var(--text-main);">Pilih Jadwal</h3>
                <div style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 520px; overflow-y: auto;">
                    @foreach($jadwal as $s)
                        <div
                            class="glass-panel"
                            style="padding: 1rem; cursor: pointer; transition: border 0.2s;"
                            :style="selectedSchedule?.id === {{ $s->id }} ? 'border: 2px solid var(--primary); background: rgba(16,185,129,0.05);' : 'border: 1px solid var(--border);'"
                            @click="selectSchedule(@js(['id' => $s->id, 'tugas' => $s->tugas, 'tanggal' => $s->tanggal, 'status' => $s->status, 'progres_terakhir' => $s->progres_terakhir ?? 0]))"
                        >
                            <div style="font-weight: 600; color: var(--text-main);">{{ $s->tugas }}</div>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0.35rem 0 0.5rem;">
                                {{ $s->tanggal ? \Carbon\Carbon::parse($s->tanggal)->format('d M Y') : '—' }}
                            </p>
                            <div class="progress-track" style="margin-bottom: 0.25rem;">
                                <div class="progress-fill" style="width: {{ min(100, (int)($s->progres_terakhir ?? 0)) }}%;"></div>
                            </div>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">{{ (int)($s->progres_terakhir ?? 0) }}% progres</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="glass-panel" style="padding: 1.5rem;">
                <template x-if="!selectedSchedule">
                    <div style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
                        <i data-lucide="clipboard-list" style="width: 40px; height: 40px; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>Pilih jadwal di sebelah kiri untuk mengisi logbook harian.</p>
                    </div>
                </template>
                <template x-if="selectedSchedule">
                    <form method="POST" action="{{ url('/pengawas/logbook') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="schedule_id" :value="selectedSchedule.id">
                        <input type="hidden" name="tanggal" :value="today">

                        <h3 style="margin-bottom: 0.25rem; color: var(--text-main);">Logbook Harian</h3>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.25rem;">
                            <span x-text="selectedSchedule.tugas"></span>
                        </p>

                        <div class="form-group">
                            <label class="form-label">Pekerja (opsional)</label>
                            <select name="worker_id" class="form-input">
                                <option value="">— Semua / umum —</option>
                                @foreach($workers as $w)
                                    <option value="{{ $w->id }}">{{ $w->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Lokasi pekerjaan</label>
                            <input type="text" name="lokasi_pekerjaan" class="form-input" placeholder="Contoh: RT 02, Bank Sampah RW 03">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Catatan progres</label>
                            <textarea name="catatan_progres" class="form-input" rows="3" placeholder="Uraian aktivitas hari ini..."></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Progres penyelesaian</label>
                            <input type="range" name="progres_persentase" min="0" max="100" x-model="progres" style="width: 100%;">
                            <div class="progress-track" style="margin-top: 0.75rem;">
                                <div class="progress-fill" :style="'width:' + progres + '%'"></div>
                            </div>
                            <div style="text-align: right; font-weight: 600; color: var(--primary); margin-top: 0.35rem;">
                                <span x-text="progres"></span>% — <span x-text="progres >= 100 ? 'Siap untuk insentif' : 'Dalam proses'"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Foto bukti kerja</label>
                            <div style="border: 2px dashed var(--border); border-radius: 8px; padding: 1.5rem; text-align: center; background: var(--background); position: relative;">
                                <template x-if="!previewUrl">
                                    <div>
                                        <i data-lucide="camera" style="width: 28px; height: 28px; color: var(--text-muted); margin: 0 auto 0.5rem;"></i>
                                        <p style="font-size: 0.85rem; color: var(--text-muted);">Unggah JPG/PNG (maks. 2MB)</p>
                                    </div>
                                </template>
                                <template x-if="previewUrl">
                                    <img :src="previewUrl" alt="Preview" style="max-width: 100%; max-height: 180px; border-radius: 8px;">
                                </template>
                                <input type="file" name="foto_bukti" accept="image/jpeg,image/png,image/jpg" style="position: absolute; inset: 0; opacity: 0; cursor: pointer;" @change="handleFileChange">
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save" style="width: 16px; height: 16px; margin-right: 6px;"></i> Simpan Logbook
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

        <div class="glass-panel" style="padding: 1.5rem; margin-top: 1.5rem;">
            <h3 style="margin-bottom: 1rem; color: var(--text-main);">Riwayat Logbook</h3>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @forelse($logbooks as $log)
                    <div style="border: 1px solid var(--border); border-radius: 8px; padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <strong>{{ $log->schedule?->program?->nama_program ?? 'Program' }}</strong>
                            <span style="font-size: 0.85rem; color: var(--text-muted);">
                                {{ $log->tanggal ? \Carbon\Carbon::parse($log->tanggal)->format('d M Y') : $log->created_at->format('d M Y') }}
                            </span>
                        </div>
                        <p style="font-size: 0.9rem; color: var(--text-muted); margin: 0 0 0.5rem;">
                            {{ $log->catatan_progres ?? $log->catatan ?? '—' }}
                        </p>
                        <div class="progress-track" style="margin-bottom: 0.25rem;">
                            <div class="progress-fill" style="width: {{ min(100, (int)$log->progres_persentase) }}%;"></div>
                        </div>
                        <span style="font-size: 0.8rem;">{{ (int)$log->progres_persentase }}%</span>
                        @if($log->status_validasi === 'menunggu')
                            <span class="badge" style="margin-left: 0.5rem; background: rgba(245, 158, 11, 0.15); color: var(--warning);">Menunggu validasi admin</span>
                        @elseif($log->status_validasi === 'disetujui')
                            <span class="badge badge-success" style="margin-left: 0.5rem;">Tervalidasi · upah dicairkan</span>
                        @elseif($log->status_validasi === 'ditolak')
                            <span class="badge" style="margin-left: 0.5rem; background: rgba(239, 68, 68, 0.15); color: var(--danger);">Validasi ditolak</span>
                        @endif
                    </div>
                @empty
                    <p style="color: var(--text-muted); text-align: center;">Belum ada entri logbook.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    .progress-track {
        height: 8px;
        background: var(--border);
        border-radius: 999px;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--primary), #34d399);
        border-radius: 999px;
        transition: width 0.3s ease;
    }
</style>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('operasionalData', () => ({
            activeTab: @json($activeTab ?? 'jadwal'),
            selectedSchedule: null,
            progres: 0,
            previewUrl: null,
            today: new Date().toISOString().slice(0, 10),

            init() {
                setTimeout(() => lucide.createIcons(), 50);
            },

            setTab(tab) {
                this.activeTab = tab;
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url);
                setTimeout(() => lucide.createIcons(), 50);
            },

            selectSchedule(s) {
                this.selectedSchedule = s;
                this.progres = s.progres_terakhir ?? 0;
                this.previewUrl = null;
                this.activeTab = 'logbook';
                this.setTab('logbook');
            },

            openLogbookForSchedule(s) {
                this.selectSchedule({
                    id: s.id,
                    tugas: s.tugas ?? (s.program ? s.program.nama_program : ''),
                    tanggal: s.tanggal,
                    status: s.status,
                    progres_terakhir: s.progres_terakhir ?? 0,
                });
            },

            handleFileChange(e) {
                const file = e.target.files[0];
                if (file) {
                    this.previewUrl = URL.createObjectURL(file);
                }
            },
        }));
    });
</script>
@endsection
