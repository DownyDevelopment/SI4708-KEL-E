@extends('layouts.app')
@section('title', 'Manajemen Data Pekerja')

@section('content')
<div class="animate-fade-in" style="position: relative;" x-data="pekerjaData()">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.8rem;">Profiling & Pendaftaran Pekerja</h1>
            <p>Form survei kesejahteraan berbasis skoring BPS/Kemensos — prioritas penugasan dari skor tertinggi.</p>
        </div>
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <a href="/admin/keluarga" class="btn btn-outline" style="text-decoration: none;">
                <i data-lucide="users" style="width: 18px; height: 18px; margin-right: 8px;"></i> Data Keluarga
            </a>
            <button class="btn btn-primary" @click="openAddForm()">
                <i data-lucide="clipboard-list" style="width: 18px; height: 18px; margin-right: 8px;"></i> Survei Profiling Baru
            </button>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #f0fdf4; color: #166534; padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-size: 0.85rem; text-align: center;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background: #fef2f2; color: #991b1b; padding: 0.75rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-size: 0.85rem;">
            <strong>Validasi gagal — data form tetap tersimpan:</strong>
            <ul style="margin: 0.5rem 0 0 1rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="glass-panel" style="padding: 1rem 1.25rem; margin-bottom: 1.5rem; border-left: 4px solid var(--primary);">
        <strong style="color: var(--text-main);">Threshold Kelayakan Program</strong>
        <p style="margin: 0.35rem 0 0; font-size: 0.9rem; color: var(--text-muted);">
            Skor &gt; 10 = <strong>Sangat Miskin</strong> (Prioritas 1) · Skor 7–10 = <strong>Rentan Miskin</strong> (Prioritas 2) · Skor &lt; 7 = <strong>Tidak Layak</strong>.
            Lengkapi data keluarga di <a href="/admin/keluarga" style="color: var(--primary);">Profiling Keluarga</a> agar skor pendapatan akurat.
        </p>
    </div>

    <div x-show="showForm" x-cloak class="glass-panel" style="padding: 2rem; margin-bottom: 2rem;">
        <h3 style="margin-bottom: 0.5rem;" x-text="editMode ? 'Edit Data Pekerja' : 'Form Survei Profiling Kesejahteraan'"></h3>
        <p x-show="!editMode" style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
            Isi indikator makan, sanitasi, pendapatan (via keluarga), dan pendidikan. Skor dihitung otomatis saat disimpan.
        </p>
        <form method="POST"
              :action="editMode ? '/admin/pekerja/' + workerData.id : '/admin/pekerja'"
              enctype="multipart/form-data"
              style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="form-group" style="grid-column: 1 / -1; background: var(--background); padding: 1rem; border-radius: 8px;">
                <strong style="display: block; margin-bottom: 0.75rem; color: var(--primary);">A. Identitas Dasar</strong>
            </div>

            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-input" required x-model="workerData.nama" />
            </div>
            <div class="form-group">
                <label class="form-label">Keluarga (untuk skor pendapatan)</label>
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
                <small x-show="computedUsia !== null" style="display: block; margin-top: 0.35rem; color: var(--text-muted);">
                    Usia: <span x-text="computedUsia"></span> tahun
                </small>
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
                <input type="text" name="kontak_darurat" class="form-input" placeholder="Nama & No. HP" x-model="workerData.kontak_darurat" />
            </div>

            <div class="form-group" style="grid-column: 1 / -1; background: var(--background); padding: 1rem; border-radius: 8px; margin-top: 0.5rem;">
                <strong style="display: block; margin-bottom: 0.75rem; color: var(--primary);">Pendapatan Program (Tunai)</strong>
            </div>
            <div class="form-group">
                <label class="form-label">Total Pendapatan (Rp)</label>
                <input type="number" name="total_pendapatan" class="form-input" min="0" step="1000" placeholder="Total uang tunai dari insentif/reward" x-model="workerData.total_pendapatan" />
                <small style="color: var(--text-muted);">Akumulasi pendapatan tunai pekerja dari program insentif & reward.</small>
            </div>

            <div class="form-group" style="grid-column: 1 / -1; background: var(--background); padding: 1rem; border-radius: 8px; margin-top: 0.5rem;">
                <strong style="display: block; margin-bottom: 0.75rem; color: var(--primary);">B. Indikator Profiling (Skoring Otomatis)</strong>
            </div>

            <div class="form-group">
                <label class="form-label">Frekuensi Makan / Hari <span style="color:#ef4444">*</span> <small>(1x=3 poin, 3x+=1 poin)</small></label>
                <select name="frekuensi_makan" class="form-input" required x-model="workerData.frekuensi_makan">
                    <option value="">— Pilih —</option>
                    <option value="1 kali">1 kali sehari (skor 3)</option>
                    <option value="2 kali">2 kali sehari (skor 2)</option>
                    <option value="3 kali atau lebih">3 kali atau lebih (skor 1)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Kondisi Sanitasi / Jamban <span style="color:#ef4444">*</span></label>
                <select name="kondisi_sanitasi" class="form-input" required x-model="workerData.kondisi_sanitasi">
                    <option value="">— Pilih —</option>
                    <option value="Tidak Ada Jamban">Tidak Ada Jamban (skor 3)</option>
                    <option value="Jamban Bersama">Jamban Bersama (skor 2)</option>
                    <option value="Jamban Sendiri">Jamban Sendiri (skor 1)</option>
                    <option value="Jamban Sendiri + Septic Tank">Jamban Sendiri + Septic Tank (skor 0)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Pendidikan Terakhir <span style="color:#ef4444">*</span></label>
                <select name="pendidikan_terakhir" class="form-input" required x-model="workerData.pendidikan_terakhir">
                    <option value="">— Pilih —</option>
                    <option value="Tidak Sekolah">Tidak Sekolah (skor 3)</option>
                    <option value="SD / Sederajat">SD / Sederajat (skor 2)</option>
                    <option value="SMP / Sederajat">SMP / Sederajat (skor 2)</option>
                    <option value="SMA / Sederajat">SMA / Sederajat (skor 1)</option>
                    <option value="Diploma / S1+">Diploma / S1+ (skor 0)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Status Gizi (SDG 2)</label>
                <select name="status_gizi" class="form-input" x-model="workerData.status_gizi">
                    <option value="">— Pilih —</option>
                    <option value="Buruk">Buruk</option>
                    <option value="Kurang">Kurang</option>
                    <option value="Normal">Normal</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Akses Air Bersih</label>
                <select name="akses_air_bersih" class="form-input" x-model="workerData.akses_air_bersih">
                    <option value="">— Pilih —</option>
                    <option value="Tidak Ada">Tidak Ada</option>
                    <option value="Sumur / Mata Air">Sumur / Mata Air</option>
                    <option value="PAM / PDAM">PAM / PDAM</option>
                    <option value="Air Kemasan">Air Kemasan</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Bidang Kerja / Kemampuan Utama <span style="color:#ef4444">*</span></label>
                <select name="bidang_kerja_select" class="form-input" x-model="bidangKerjaSelect" @change="onBidangChange()" required>
                    <option value="">— Pilih bidang —</option>
                    <option value="Pertanian">Pertanian</option>
                    <option value="Pengelolaan Sampah">Pengelolaan Sampah</option>
                    <option value="Kerajinan Tangan">Kerajinan Tangan</option>
                    <option value="Pertukangan">Pertukangan</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
                <input type="hidden" name="kemampuan_utama" :value="resolvedKemampuanUtama">
                <div x-show="bidangKerjaSelect === 'Lainnya'" x-cloak style="margin-top: 0.75rem;">
                    <input type="text" class="form-input" placeholder="Tulis bidang kerja kustom, contoh: Menjahit, Supir..." x-model="bidangKerjaLainnya" @input="syncKemampuanUtama()" />
                </div>
            </div>
            <div class="form-group" x-show="!editMode">
                <label class="form-label">Bukti Foto Kondisi Lapangan</label>
                <input type="file" name="bukti_foto_kondisi" class="form-input" accept="image/*" />
                <small style="color: var(--text-muted);">Opsional, max 5MB — foto rumah/kondisi sanitasi</small>
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
                <textarea name="riwayat_penyakit" class="form-input" rows="2" placeholder="Sebutkan riwayat penyakit..." x-model="workerData.riwayat_penyakit"></textarea>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Alamat Lengkap</label>
                <textarea name="alamat" class="form-input" rows="2" x-model="workerData.alamat"></textarea>
            </div>
            <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                <button type="button" class="btn btn-outline" @click="showForm = false">Batal</button>
                <button type="submit" class="btn btn-primary" x-text="editMode ? 'Perbarui Data' : 'Simpan Survei Profiling'"></button>
            </div>
        </form>
    </div>

    {{-- Modal Update Profiling --}}
    <div x-show="showUpdateProfiling" x-cloak
         style="position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 100; display: flex; align-items: center; justify-content: center; padding: 1rem;"
         @click.self="showUpdateProfiling = false">
        <div class="glass-panel" style="padding: 2rem; max-width: 560px; width: 100%; max-height: 90vh; overflow-y: auto;">
            <h3 style="margin-bottom: 1rem;">Update Profiling — <span x-text="updateWorkerName"></span></h3>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.25rem;">Survei ulang untuk memantau progres kesejahteraan (mis. makan 1x → 3x).</p>
            <form :action="'/admin/profiling/' + updateWorkerId + '/update'" method="POST" enctype="multipart/form-data" style="display: grid; gap: 1rem;">
                @csrf
                <div class="form-group">
                    <label class="form-label">Frekuensi Makan / Hari *</label>
                    <select name="frekuensi_makan" class="form-input" required x-model="updateData.frekuensi_makan">
                        <option value="1 kali">1 kali sehari</option>
                        <option value="2 kali">2 kali sehari</option>
                        <option value="3 kali atau lebih">3 kali atau lebih</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Kondisi Sanitasi *</label>
                    <select name="kondisi_sanitasi" class="form-input" required x-model="updateData.kondisi_sanitasi">
                        <option value="Tidak Ada Jamban">Tidak Ada Jamban</option>
                        <option value="Jamban Bersama">Jamban Bersama</option>
                        <option value="Jamban Sendiri">Jamban Sendiri</option>
                        <option value="Jamban Sendiri + Septic Tank">Jamban Sendiri + Septic Tank</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Pendidikan Terakhir *</label>
                    <select name="pendidikan_terakhir" class="form-input" required x-model="updateData.pendidikan_terakhir">
                        <option value="Tidak Sekolah">Tidak Sekolah</option>
                        <option value="SD / Sederajat">SD / Sederajat</option>
                        <option value="SMP / Sederajat">SMP / Sederajat</option>
                        <option value="SMA / Sederajat">SMA / Sederajat</option>
                        <option value="Diploma / S1+">Diploma / S1+</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Gizi</label>
                    <select name="status_gizi" class="form-input" x-model="updateData.status_gizi">
                        <option value="">— Tidak diubah —</option>
                        <option value="Buruk">Buruk</option>
                        <option value="Kurang">Kurang</option>
                        <option value="Normal">Normal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Bukti Foto Kondisi</label>
                    <input type="file" name="bukti_foto_kondisi" class="form-input" accept="image/*" />
                </div>
                <div class="form-group">
                    <label class="form-label">Catatan Pemantauan</label>
                    <textarea name="catatan" class="form-input" rows="2" placeholder="Contoh: Frekuensi makan meningkat setelah program gizi desa."></textarea>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <button type="button" class="btn btn-outline" @click="showUpdateProfiling = false">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Update Profiling</button>
                </div>
            </form>
        </div>
    </div>

    <div class="glass-panel" style="padding: 1.5rem;">
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
            <i data-lucide="arrow-down-narrow-wide" style="width: 14px; height: 14px; display: inline;"></i>
            Diurutkan berdasarkan <strong>total skor tertinggi</strong> — prioritas penugasan otomatis.
        </p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Keahlian</th>
                    <th>Total Skor</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workers as $w)
                    @php
                        $kategoriColors = [
                            'Sangat Miskin' => ['#fef2f2', '#ef4444'],
                            'Rentan Miskin' => ['#fef9c3', '#ca8a04'],
                            'Pending' => ['#eff6ff', '#3b82f6'],
                            'Lulus/Tidak Layak' => ['#f1f5f9', '#64748b'],
                        ];
                        [$kbg, $kfg] = $kategoriColors[$w->status_kesejahteraan ?? 'Pending'] ?? ['#f1f5f9', '#64748b'];
                    @endphp
                    <tr>
                        <td># {{ $w->id }}</td>
                        <td style="font-weight: 500;">{{ $w->nama }}</td>
                        <td><span class="badge badge-success">{{ $w->keahlian_kerja ?: $w->kemampuan_utama ?: 'Umum' }}</span></td>
                        <td style="font-weight: 700; color: var(--primary);">{{ $w->total_skor ?? $w->skor_vulnerabilitas ?? '—' }}</td>
                        <td>
                            <span style="background: {{ $kbg }}; color: {{ $kfg }}; padding: 0.2rem 0.6rem; border-radius: 99px; font-size: 0.75rem;">
                                {{ $w->status_kesejahteraan ?? 'Pending' }}
                            </span>
                        </td>
                        <td>{{ $w->status_program_label ?? 'Aktif' }}</td>
                        <td style="white-space: nowrap;">
                            <a href="/admin/pekerja/{{ $w->id }}/profil" class="btn btn-primary btn-sm" style="text-decoration: none;">Profil</a>
                            @if($w->status_program === 'aktif')
                                <button class="btn btn-outline btn-sm"
                                    @click="openUpdateProfiling({{ $w->id }}, @js($w->nama), @js($w->frekuensi_makan), @js($w->kondisi_sanitasi), @js($w->pendidikan_terakhir), @js($w->status_gizi))">
                                    Update
                                </button>
                                <form method="POST" action="{{ route('admin.profiling.lulus', $w->id) }}" style="display: inline;" onsubmit="return confirm('Tandai {{ $w->nama }} lulus program?');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm">Lulus</button>
                                </form>
                            @endif
                            <button class="btn btn-outline btn-sm" @click="handleEdit({{ $w->id }})">Edit</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">Belum ada data survei profiling.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('pekerjaData', () => ({
            showForm: false,
            showUpdateProfiling: false,
            editMode: false,
            updateWorkerId: null,
            updateWorkerName: '',
            updateData: { frekuensi_makan: '', kondisi_sanitasi: '', pendidikan_terakhir: '', status_gizi: '' },
            workerData: {},
            bidangKerjaSelect: '',
            bidangKerjaLainnya: '',

            get resolvedKemampuanUtama() {
                if (this.bidangKerjaSelect === 'Lainnya') {
                    return this.bidangKerjaLainnya.trim();
                }
                return this.bidangKerjaSelect;
            },

            onBidangChange() {
                if (this.bidangKerjaSelect !== 'Lainnya') {
                    this.bidangKerjaLainnya = '';
                }
                this.syncKemampuanUtama();
            },

            syncKemampuanUtama() {
                this.workerData.kemampuan_utama = this.resolvedKemampuanUtama;
            },

            setBidangFromValue(value) {
                const standar = ['Pertanian', 'Pengelolaan Sampah', 'Kerajinan Tangan', 'Pertukangan'];
                if (standar.includes(value)) {
                    this.bidangKerjaSelect = value;
                    this.bidangKerjaLainnya = '';
                } else if (value) {
                    this.bidangKerjaSelect = 'Lainnya';
                    this.bidangKerjaLainnya = value;
                } else {
                    this.bidangKerjaSelect = '';
                    this.bidangKerjaLainnya = '';
                }
                this.syncKemampuanUtama();
            },

            defaultWorkerData() {
                return {
                    nama: '', household_id: '', tanggal_lahir: '', jenis_kelamin: 'L',
                    no_telepon: '', kontak_darurat: '', total_pendapatan: 0, kemampuan_utama: '',
                    pendidikan_terakhir: '', frekuensi_makan: '', status_gizi: '',
                    kondisi_sanitasi: '', akses_air_bersih: '', kebiasaan: '',
                    status_keluarga: 'Kepala Keluarga', status_rumah: 'Milik Sendiri',
                    riwayat_penyakit: '', alamat: ''
                };
            },

            get computedUsia() {
                if (!this.workerData.tanggal_lahir) return null;
                const birth = new Date(this.workerData.tanggal_lahir);
                if (Number.isNaN(birth.getTime())) return null;
                const today = new Date();
                let age = today.getFullYear() - birth.getFullYear();
                const monthDiff = today.getMonth() - birth.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) age--;
                return age >= 0 ? age : null;
            },

            init() {
                this.workerData = this.defaultWorkerData();

                const oldInput = @json(old());
                if (oldInput && Object.keys(oldInput).length > 0) {
                    Object.keys(oldInput).forEach(key => {
                        if (key in this.workerData) {
                            this.workerData[key] = oldInput[key] ?? '';
                        }
                    });
                    if (this.workerData.household_id === null) this.workerData.household_id = '';
                    this.showForm = true;
                    this.editMode = false;
                }

                const params = new URLSearchParams(window.location.search);
                const editId = params.get('edit');
                if (editId) this.handleEdit(editId);
            },

            openAddForm() {
                this.editMode = false;
                this.workerData = this.defaultWorkerData();
                this.setBidangFromValue('');
                this.showForm = true;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            openUpdateProfiling(id, nama, makan, sanitasi, pendidikan, gizi) {
                this.updateWorkerId = id;
                this.updateWorkerName = nama;
                this.updateData = {
                    frekuensi_makan: makan || '1 kali',
                    kondisi_sanitasi: sanitasi || 'Tidak Ada Jamban',
                    pendidikan_terakhir: pendidikan || 'Tidak Sekolah',
                    status_gizi: gizi || ''
                };
                this.showUpdateProfiling = true;
            },

            async handleEdit(id) {
                try {
                    const res = await fetch(`/api/workers/${id}`, { headers: { 'Accept': 'application/json' } });
                    if (res.ok) {
                        this.workerData = { ...this.defaultWorkerData(), ...(await res.json()) };
                        if (this.workerData.household_id === null) this.workerData.household_id = '';
                        this.setBidangFromValue(this.workerData.kemampuan_utama || '');
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
