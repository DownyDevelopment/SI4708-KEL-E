@extends('layouts.app')
@section('title', 'Operasional & Penjadwalan')

@section('content')
<div class="animate-fade-in" style="padding: 2rem;" x-data="tugasData()">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.8rem; display: flex; align-items: center; gap: 0.5rem; color: var(--text-main);">
                <i data-lucide="calendar" style="width: 28px; height: 28px; color: var(--primary);"></i>
                Operasional & Penjadwalan
            </h1>
            <p style="color: var(--text-muted);">Atur jadwal kerja harian/mingguan dan penugasan pekerja per program.</p>
        </div>
        <button type="button" class="btn btn-primary" @click="openCreateModal()">
            <i data-lucide="plus" style="width: 18px; height: 18px; margin-right: 6px;"></i> Tambah Jadwal
        </button>
    </div>

    @if(session('success'))
        <div class="glass-panel" style="margin-bottom: 1rem; padding: 0.85rem 1.25rem; border-left: 4px solid var(--primary);">
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

    <div class="glass-panel" style="padding: 1.5rem;">
        <h3 style="margin-bottom: 1rem; color: var(--text-main);">Daftar Jadwal</h3>
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                        <th style="padding: 0.75rem;">Program</th>
                        <th style="padding: 0.75rem;">Tanggal</th>
                        <th style="padding: 0.75rem;">Shift / Jam</th>
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
                                {{ $item->shift_label ?? '—' }}
                                @if($item->jam_mulai)
                                    <br><span style="color: var(--text-muted);">{{ $item->jam_mulai }}@if($item->jam_selesai) – {{ $item->jam_selesai }}@endif</span>
                                @endif
                            </td>
                            <td style="padding: 0.75rem; font-size: 0.85rem;">
                                @if(!empty($item->pekerja_nama))
                                    {{ implode(', ', array_slice($item->pekerja_nama, 0, 3)) }}@if(count($item->pekerja_nama) > 3) +{{ count($item->pekerja_nama) - 3 }}@endif
                                @else
                                    —
                                @endif
                            </td>
                            <td style="padding: 0.75rem; min-width: 120px;">
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
                                <button type="button" class="btn btn-outline btn-sm" @click="openEditModal(@js($item))">Edit</button>
                                <form method="POST" action="{{ url('/admin/tugas/' . $item->id) }}" style="display:inline;" onsubmit="return confirm('Hapus jadwal ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline btn-sm" style="color: var(--danger); margin-left: 0.25rem;">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 1.5rem; text-align: center; color: var(--text-muted);">Belum ada jadwal.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <template x-if="showModal">
        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 99; display: flex; align-items: center; justify-content: center; padding: 1rem;">
            <div class="glass-panel" style="width: 100%; max-width: 640px; max-height: 90vh; overflow-y: auto; padding: 2rem; background: var(--surface);">
                <h3 style="margin-bottom: 1.25rem; color: var(--text-main);" x-text="isEdit ? 'Edit Jadwal' : 'Tambah Jadwal Baru'"></h3>
                <form :method="isEdit ? 'POST' : 'POST'" :action="formAction">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="form-group">
                        <label class="form-label">Program kerja</label>
                        <select name="program_id" class="form-input" x-model="form.program_id" required>
                            <option value="">— Pilih program —</option>
                            @foreach($programs as $p)
                                <option value="{{ $p->id }}">{{ $p->nama_program }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-input" x-model="form.tanggal" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-input" x-model="form.status" required>
                                <option value="scheduled">Terjadwal</option>
                                <option value="in_progress">Berjalan</option>
                                <option value="completed">Selesai</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Jam mulai</label>
                            <input type="time" name="jam_mulai" class="form-input" x-model="form.jam_mulai">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jam selesai</label>
                            <input type="time" name="jam_selesai" class="form-input" x-model="form.jam_selesai">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Label shift</label>
                            <input type="text" name="shift_label" class="form-input" x-model="form.shift_label" placeholder="Pagi / Siang">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi tugas</label>
                        <textarea name="deskripsi" class="form-input" rows="2" x-model="form.deskripsi" placeholder="Detail pekerjaan hari ini..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pekerja ditugaskan <span style="font-weight: normal; color: var(--text-muted);">(urut prioritas skor profiling)</span></label>
                        @if(!empty($workerMatches))
                        <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; padding: 0.75rem; margin-bottom: 0.75rem; font-size: 0.85rem; color: var(--text-muted);">
                            <strong style="color: var(--text-main);">Rekomendasi matching</strong> — pilih program dulu, lalu centang pekerja yang keahliannya cocok.
                        </div>
                        @endif
                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border); border-radius: 8px; padding: 0.75rem;">
                            @foreach($workers as $w)
                                <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.35rem 0; cursor: pointer; font-size: 0.9rem;">
                                    <input type="checkbox" name="worker_ids[]" value="{{ $w->id }}" :checked="form.worker_ids.includes('{{ $w->id }}')" @change="toggleWorker('{{ $w->id }}', $event.target.checked)">
                                    <span>{{ $w->nama }}</span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">· Skor {{ $w->skor_vulnerabilitas ?? '—' }} · {{ $w->kemampuan_utama }}</span>
                                    @if($w->desa_asal)
                                        <span style="font-size: 0.75rem; color: #d97706;">· {{ $w->desa_asal }}</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                        <button type="button" class="btn btn-outline" @click="showModal = false">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<style>
    .progress-track { height: 8px; background: var(--border); border-radius: 999px; overflow: hidden; }
    .progress-fill { height: 100%; background: linear-gradient(90deg, var(--primary), #34d399); border-radius: 999px; }
</style>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('tugasData', () => ({
            showModal: false,
            isEdit: false,
            formAction: '{{ url('/admin/tugas') }}',
            form: {
                program_id: '',
                tanggal: '',
                status: 'scheduled',
                jam_mulai: '',
                jam_selesai: '',
                shift_label: '',
                deskripsi: '',
                worker_ids: [],
            },

            init() {
                setTimeout(() => lucide.createIcons(), 50);
            },

            openCreateModal() {
                this.isEdit = false;
                this.formAction = '{{ url('/admin/tugas') }}';
                this.form = {
                    program_id: '',
                    tanggal: new Date().toISOString().slice(0, 10),
                    status: 'scheduled',
                    jam_mulai: '',
                    jam_selesai: '',
                    shift_label: '',
                    deskripsi: '',
                    worker_ids: [],
                };
                this.showModal = true;
                setTimeout(() => lucide.createIcons(), 50);
            },

            openEditModal(item) {
                this.isEdit = true;
                this.formAction = '{{ url('/admin/tugas') }}/' + item.id;
                this.form = {
                    program_id: String(item.program_id || ''),
                    tanggal: item.tanggal ? String(item.tanggal).slice(0, 10) : '',
                    status: item.status || 'scheduled',
                    jam_mulai: item.jam_mulai || '',
                    jam_selesai: item.jam_selesai || '',
                    shift_label: item.shift_label || '',
                    deskripsi: item.deskripsi || '',
                    worker_ids: (item.assignments || []).map(a => String(a.worker_id)),
                };
                this.showModal = true;
                setTimeout(() => lucide.createIcons(), 50);
            },

            toggleWorker(id, checked) {
                if (checked) {
                    if (!this.form.worker_ids.includes(id)) {
                        this.form.worker_ids.push(id);
                    }
                } else {
                    this.form.worker_ids = this.form.worker_ids.filter(w => w !== id);
                }
            },
        }));
    });
</script>
@endsection
