@extends('layouts.app')
@section('title', 'Logbook & Validasi Kerja')

@section('content')
<div class="animate-fade-in" style="padding: 2rem;" x-data="logbookData()">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.8rem; color: var(--text-main);">Logbook & Validasi Kerja</h1>
        <p style="color: var(--text-muted);">Unggah bukti pekerjaan untuk mencatat kehadiran dan penyelesaian tugas harian.</p>
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

    <div style="display: grid; grid-template-columns: minmax(300px, 1fr) 2fr; gap: 2rem;">
        <!-- Kolom Kiri: Daftar Jadwal -->
        <div>
            <h3 style="margin-bottom: 1rem; color: var(--text-main);">Jadwal Penugasan</h3>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @forelse($schedules as $s)
                    <div 
                        class="glass-panel" 
                        :class="{'active': selectedSchedule?.id === {{ $s->id }}}"
                        style="padding: 1.25rem; cursor: pointer; transition: all 0.2s ease;"
                        :style="selectedSchedule?.id === {{ $s->id }} ? 'border: 2px solid var(--primary); background: rgba(16, 185, 129, 0.05);' : 'border: 1px solid var(--border); background: var(--surface);'"
                        @click="selectSchedule({{ $s }})"
                    >
                        <div style="font-weight: 600; color: var(--text-main);">{{ $s->tugas }}</div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem; display: flex; gap: 1rem;">
                            <span style="display: flex; align-items: center; gap: 0.25rem;">
                                <i data-lucide="clock" style="width: 14px; height: 14px;"></i> {{ \Carbon\Carbon::parse($s->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->jam_selesai ?? $s->jam_mulai)->format('H:i') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="glass-panel" style="padding: 1.5rem; text-align: center; color: var(--text-muted);">
                        Tidak ada jadwal aktif saat ini.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Kolom Kanan: Form Upload Bukti -->
        <div class="glass-panel" style="padding: 2rem; height: fit-content;">
            <template x-if="!selectedSchedule">
                <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                    <div style="margin: 0 auto 1rem; width: 64px; height: 64px; border-radius: 50%; background: var(--background); display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="check" style="width: 32px; height: 32px; opacity: 0.5;"></i>
                    </div>
                    <h3>Pilih Jadwal dari Kiri</h3>
                    <p>Pilih salah satu jadwal tugas untuk mengunggah bukti dan persentase kehadiran.</p>
                </div>
            </template>
            <template x-if="selectedSchedule">
                <form method="POST" action="/pengawas/logbook" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="schedule_id" :value="selectedSchedule.id">
                    <input type="hidden" name="pekerja_terlibat" :value="JSON.stringify(selectedWorkers)">

                    <h3 style="margin-bottom: 0.5rem; color: var(--text-main);">Upload Bukti Kerja</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 2rem;">
                        Tugas: <span x-text="selectedSchedule.tugas"></span> (<span x-text="selectedSchedule.hari"></span>)
                    </p>

                    <div class="form-group">
                        <label class="form-label">Lokasi Pekerjaan (Aktual)</label>
                        <input type="text" name="lokasi_pekerjaan" class="form-input" placeholder="Contoh: Jalan Mawar Barat RT 02..." required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Validasi Pekerja Hadir (<span x-text="selectedWorkers.length"></span>/10)</label>
                        <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <select class="form-input" style="flex: 1;" x-model="selectedWorkerId">
                                <option value="">-- Pilih Pekerja --</option>
                                @foreach($workers as $w)
                                    <option value="{{ $w->id }}">[ID: {{ $w->id }}] {{ $w->nama }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline" @click="addWorker" style="display: flex; align-items: center; gap: 0.25rem; padding: 0.5rem 1rem;">
                                <i data-lucide="plus" style="width: 16px; height: 16px;"></i> Tambah
                            </button>
                        </div>
                        
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 1rem;">
                            <template x-for="w in selectedWorkers" :key="w.id">
                                <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--background); padding: 0.4rem 0.75rem; border-radius: 1rem; font-size: 0.85rem; border: 1px solid var(--border);">
                                    <span><span x-text="w.nama"></span> <span style="opacity: 0.6;">(ID: <span x-text="w.id"></span>)</span></span>
                                    <button type="button" @click="removeWorker(w.id)" style="background: none; border: none; color: var(--danger); cursor: pointer; display: flex; align-items: center; padding: 0;">
                                        <i data-lucide="x" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <template x-if="selectedWorkers.length === 0">
                            <p style="font-size: 0.85rem; color: var(--danger); margin-top: 0.25rem;">Minimal harus menambahkan 1 pekerja yang hadir.</p>
                        </template>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Progres Penyelesaian (%)</label>
                        <input type="range" name="progres_persentase" min="0" max="100" x-model="progres" style="width: 100%;">
                        <div style="text-align: right; font-weight: 600; color: var(--primary); margin-top: 0.5rem;">
                            <span x-text="progres"></span>% Selesai
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Catatan Kegiatan (Opsional)</label>
                        <textarea name="catatan" class="form-input" rows="3" placeholder="Ceritakan kendala atau progres hari ini..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Unggah Foto Bukti Kerja (Evidence)</label>
                        <div style="border: 2px dashed var(--border); border-radius: 8px; padding: 2rem; text-align: center; background: var(--background); position: relative;">
                            <template x-if="!previewUrl">
                                <div>
                                    <i data-lucide="camera" style="width: 32px; height: 32px; color: var(--text-muted); margin: 0 auto 1rem;"></i>
                                    <p style="font-size: 0.9rem; color: var(--text-main);">Klik untuk mengambil foto atau unggah file</p>
                                    <p style="font-size: 0.8rem; color: var(--text-muted);">Mendukung format JPG, PNG</p>
                                </div>
                            </template>
                            <template x-if="previewUrl">
                                <div style="display: flex; flex-direction: column; align-items: center;">
                                    <img :src="previewUrl" alt="Preview" style="max-width: 100%; max-height: 200px; border-radius: 8px; object-fit: contain;">
                                    <p style="font-size: 0.85rem; margin-top: 1rem; color: var(--primary); cursor: pointer;">Ubah Foto</p>
                                </div>
                            </template>
                            <input type="file" name="foto" accept="image/jpeg, image/png" required style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;" @change="handleFileChange">
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="check" style="width: 18px; height: 18px; margin-right: 6px;"></i> Kirim Bukti Validasi
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('logbookData', () => ({
            selectedSchedule: null,
            progres: 0,
            selectedWorkerId: '',
            selectedWorkers: [],
            previewUrl: null,
            workers: @json($workers),

            init() {
                setTimeout(() => lucide.createIcons(), 50);
            },

            selectSchedule(s) {
                this.selectedSchedule = s;
                this.progres = 0;
                this.selectedWorkers = [];
                this.previewUrl = null;
                setTimeout(() => lucide.createIcons(), 50);
            },

            addWorker() {
                if (!this.selectedWorkerId) return;
                if (this.selectedWorkers.length >= 10) return alert('Maksimal 10 pekerja.');
                if (this.selectedWorkers.find(w => w.id == this.selectedWorkerId)) return alert('Pekerja sudah ditambahkan.');
                
                const worker = this.workers.find(w => w.id == this.selectedWorkerId);
                if (worker) {
                    this.selectedWorkers.push({ id: worker.id, nama: worker.nama });
                    this.selectedWorkerId = '';
                    setTimeout(() => lucide.createIcons(), 50);
                }
            },

            removeWorker(id) {
                this.selectedWorkers = this.selectedWorkers.filter(w => w.id !== id);
                setTimeout(() => lucide.createIcons(), 50);
            },

            handleFileChange(e) {
                const file = e.target.files[0];
                if (file) {
                    this.previewUrl = URL.createObjectURL(file);
                }
            }
        }));
    });
</script>
@endsection
