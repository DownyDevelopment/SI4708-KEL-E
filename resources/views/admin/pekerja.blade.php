@extends('layouts.app')
@section('title', 'Manajemen Data Pekerja')

@section('content')
<div class="animate-fade-in" style="position: relative;" x-data="pekerjaData()">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.8rem;">Manajemen Data Pekerja</h1>
            <p>Daftar warga prasejahtera yang berpartisipasi dalam program desa.</p>
        </div>
        <button class="btn btn-primary" @click="openAddForm()">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px; margin-right: 8px;"></i> Tambah Pekerja
        </button>
    </div>

    <!-- Feedback Message -->
    @if(session('success'))
        <div style="background: #f0fdf4; color: #166534; padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-size: 0.85rem; text-align: center;">
            {{ session('success') }}
        </div>
    @endif

    <div x-show="showForm" class="glass-panel" style="padding: 2rem; margin-bottom: 2rem; display: none;">
        <h3 style="margin-bottom: 1.5rem;" x-text="editMode ? 'Edit Data Pekerja' : 'Pendaftaran Pekerja Baru'"></h3>
        <form method="POST" :action="editMode ? '/admin/pekerja/' + workerData.id : '/admin/pekerja'" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-input" required x-model="workerData.nama" />
            </div>
            <div class="form-group">
                <label class="form-label">Keluarga (Optional)</label>
                <select name="household_id" class="form-input" x-model="workerData.household_id">
                    <option value="">- Tanpa Kepala Keluarga -</option>
                    @foreach($households as $h)
                        <option value="{{ $h->id }}">{{ $h->kepala_keluarga }} ({{ $h->rt_rw }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" class="form-input" x-model="workerData.tanggal_lahir" />
            </div>
            <div class="form-group">
                <label class="form-label">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-input" x-model="workerData.jenis_kelamin">
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">No. HP / WhatsApp</label>
                <input type="text" name="no_telepon" class="form-input" placeholder="08xxx (Kosongkan jika tidak ada)" x-model="workerData.no_telepon" />
            </div>
            <div class="form-group">
                <label class="form-label">Kontak Darurat / Tetangga</label>
                <input type="text" name="kontak_darurat" class="form-input" placeholder="Nama & No. HP (Misal: Pak RT - 08xxx)" x-model="workerData.kontak_darurat" />
            </div>
            <div class="form-group">
                <label class="form-label">Kemampuan Utama / Keahlian</label>
                <input type="text" name="kemampuan_utama" class="form-input" placeholder="contoh: Berkebun, Mengolah Sampah" required x-model="workerData.kemampuan_utama" />
            </div>
            <div class="form-group">
                <label class="form-label">Status Keluarga</label>
                <select name="status_keluarga" class="form-input" x-model="workerData.status_keluarga">
                    <option value="Kepala Keluarga">Kepala Keluarga</option>
                    <option value="Anggota Keluarga">Anggota Keluarga</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Status Kepemilikan Rumah</label>
                <select name="status_rumah" class="form-input" x-model="workerData.status_rumah">
                    <option value="Milik Sendiri">Milik Sendiri</option>
                    <option value="Kontrak">Kontrak / Sewa</option>
                    <option value="Tidak Ada">Tidak Ada (Gelandangan/Numpang)</option>
                </select>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Riwayat Penyakit (Jika ada)</label>
                <textarea name="riwayat_penyakit" class="form-input" rows="2" placeholder="Sebutkan riwayat penyakit atau kondisi medis..." x-model="workerData.riwayat_penyakit"></textarea>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Alamat Lengkap</label>
                <textarea name="alamat" class="form-input" rows="2" x-model="workerData.alamat"></textarea>
            </div>
            <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                <button type="button" class="btn btn-outline" @click="showForm = false">Batal</button>
                <button type="submit" class="btn btn-primary" x-text="editMode ? 'Perbarui Data' : 'Simpan Data'"></button>
            </div>
        </form>
    </div>

    <div class="glass-panel" style="padding: 1.5rem;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Keahlian</th>
                    <th>Keluarga</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workers as $w)
                    <tr>
                        <td># {{ $w->id }}</td>
                        <td style="font-weight: 500;">{{ $w->nama }}</td>
                        <td><span class="badge badge-success">{{ $w->kemampuan_utama ?: 'Umum' }}</span></td>
                        <td>
                            @php
                                $household = collect($households)->firstWhere('id', $w->household_id);
                            @endphp
                            {{ $household ? $household->kepala_keluarga : '-' }}
                        </td>
                        <td>Aktif</td>
                        <td>
                            <button class="btn btn-outline btn-sm" @click="handleEdit({{ $w->id }})">Edit Data</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">Belum ada data pekerja.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>


</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('pekerjaData', () => ({
            showForm: false,
            editMode: false,
            workerData: {
                nama: '', household_id: '', tanggal_lahir: '', jenis_kelamin: 'L', no_telepon: '', kontak_darurat: '', kemampuan_utama: '', status_keluarga: 'Kepala Keluarga', status_rumah: 'Milik Sendiri', riwayat_penyakit: '', alamat: ''
            },

            openAddForm() {
                this.editMode = false;
                this.workerData = {
                    nama: '', household_id: '', tanggal_lahir: '', jenis_kelamin: 'L', no_telepon: '', kontak_darurat: '', kemampuan_utama: '', status_keluarga: 'Kepala Keluarga', status_rumah: 'Milik Sendiri', riwayat_penyakit: '', alamat: ''
                };
                this.showForm = true;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            async handleEdit(id) {
                try {
                    const res = await fetch(`/api/workers/${id}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (res.ok) {
                        this.workerData = await res.json();
                        if (this.workerData.household_id === null) this.workerData.household_id = '';
                        this.editMode = true;
                        this.showForm = true;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                } catch (err) {
                    console.error(err);
                }
            }
        }));
    });
</script>
@endsection
