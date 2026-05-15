@extends('layouts.app')
@section('title', 'Manajemen Data Pekerja')

@section('content')
<div class="animate-fade-in" style="position: relative;" x-data="pekerjaData()">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.8rem;">Manajemen Data Pekerja</h1>
            <p>Daftar warga prasejahtera yang berpartisipasi dalam program desa.</p>
        </div>
        <button class="btn btn-primary" @click="showForm = !showForm">
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
        <h3 style="margin-bottom: 1.5rem;">Pendaftaran Pekerja Baru</h3>
        <form method="POST" action="/admin/pekerja" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            @csrf
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-input" required />
            </div>
            <div class="form-group">
                <label class="form-label">Keluarga (Optional)</label>
                <select name="household_id" class="form-input">
                    <option value="">- Tanpa Kepala Keluarga -</option>
                    @foreach($households as $h)
                        <option value="{{ $h->id }}">{{ $h->kepala_keluarga }} ({{ $h->rt_rw }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" class="form-input" />
            </div>
            <div class="form-group">
                <label class="form-label">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-input">
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">No. HP / WhatsApp</label>
                <input type="text" name="no_telepon" class="form-input" placeholder="08xxx (Kosongkan jika tidak ada)" />
            </div>
            <div class="form-group">
                <label class="form-label">Kontak Darurat / Tetangga</label>
                <input type="text" name="kontak_darurat" class="form-input" placeholder="Nama & No. HP (Misal: Pak RT - 08xxx)" />
            </div>
            <div class="form-group">
                <label class="form-label">Kemampuan Utama / Keahlian</label>
                <input type="text" name="kemampuan_utama" class="form-input" placeholder="contoh: Berkebun, Mengolah Sampah" required />
            </div>
            <div class="form-group">
                <label class="form-label">Status Keluarga</label>
                <select name="status_keluarga" class="form-input">
                    <option value="Kepala Keluarga">Kepala Keluarga</option>
                    <option value="Anggota Keluarga">Anggota Keluarga</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Status Kepemilikan Rumah</label>
                <select name="status_rumah" class="form-input">
                    <option value="Milik Sendiri">Milik Sendiri</option>
                    <option value="Kontrak">Kontrak / Sewa</option>
                    <option value="Tidak Ada">Tidak Ada (Gelandangan/Numpang)</option>
                </select>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Riwayat Penyakit (Jika ada)</label>
                <textarea name="riwayat_penyakit" class="form-input" rows="2" placeholder="Sebutkan riwayat penyakit atau kondisi medis..."></textarea>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Alamat Lengkap</label>
                <textarea name="alamat" class="form-input" rows="2"></textarea>
            </div>
            <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                <button type="button" class="btn btn-outline" @click="showForm = false">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Data</button>
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
                            <button class="btn btn-outline btn-sm" @click="handleViewProfile({{ $w->id }})">Profil Detail</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">Belum ada data pekerja.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Profile Detail - Professional Fullscreen Panel -->
    <div x-show="selectedWorker" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 99999; display: flex; flex-direction: column; background: white; animation: fadeIn 0.2s ease-out; overflow: hidden; display: none;">
        <!-- Header Bar -->
        <div style="background: linear-gradient(135deg, var(--primary) 0%, #0d9488 100%); padding: 1rem 2.5rem; flex-shrink: 0; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; gap: 1.25rem;">
                <div style="width: 40px; height: 40px; background: white; border-radius: 10px; display: flex; justify-content: center; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    <i data-lucide="user" style="color: var(--primary);"></i>
                </div>
                <h2 style="font-size: 1.35rem; margin: 0; color: white; font-weight: 600;">Detail Pekerja: <span x-text="workerData.nama"></span></h2>
            </div>
            
            <button style="background: rgba(255,255,255,0.2); border: none; cursor: pointer; color: white; padding: 8px 20px; border-radius: 8px; font-size: 0.85rem; display: flex; align-items: center; gap: 8px; font-weight: 500; transition: background 0.2s;" @click="closeProfile()" @mouseenter="$el.style.background = 'rgba(255,255,255,0.3)'" @mouseleave="$el.style.background = 'rgba(255,255,255,0.2)'">
                <i data-lucide="x" style="width: 18px; height: 18px;"></i> Tutup Detail
            </button>
        </div>

        <!-- Main Content Area -->
        <div style="flex: 1; padding: 2.5rem; background: #f8fafc; overflow: hidden; display: flex; justify-content: center; align-items: center;">
            <div style="width: 100%; max-width: 1200px; display: grid; grid-template-columns: repeat(3, 1fr); grid-template-rows: auto auto auto; gap: 1.5rem;">
                
                <!-- 1. Profile Highlight -->
                <div class="glass-panel" style="grid-column: span 2; padding: 1.75rem 2rem; display: flex; align-items: center; gap: 2rem; background: white; border: 1px solid var(--border); box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
                    <div style="width: 80px; height: 80px; background: rgba(15, 118, 110, 0.1); border-radius: 20px; display: flex; justify-content: center; align-items: center; flex-shrink: 0;">
                        <i data-lucide="user" style="width: 40px; height: 40px; color: var(--primary);"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.05em;">Identitas Utama</div>
                        <h2 style="font-size: 1.8rem; margin: 0; color: var(--text-main); font-weight: 700;" x-text="workerData.nama"></h2>
                        <div style="display: flex; gap: 1.5rem; margin-top: 0.6rem; color: var(--text-muted); font-size: 0.95rem;">
                            <span style="display: flex; align-items: center; gap: 6px;"><i data-lucide="briefcase" style="width: 16px; height: 16px;"></i> <span x-text="workerData.kemampuan_utama"></span></span>
                            <span>&bull;</span>
                            <span style="display: flex; align-items: center; gap: 6px;"><i data-lucide="calendar" style="width: 16px; height: 16px;"></i> <span x-text="workerData.tanggal_lahir || '-'"></span></span>
                        </div>
                    </div>
                </div>

                <!-- 2. Schedule List -->
                <div class="glass-panel" style="grid-row: span 3; padding: 2rem; background: white; border: 1px solid var(--border); display: flex; flex-direction: column; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
                    <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px; color: var(--text-main); font-weight: 600;">
                        <i data-lucide="calendar" style="color: var(--primary);"></i> Jadwal Kerja Aktif
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <template x-if="workerData.schedules && workerData.schedules.length > 0">
                            <template x-for="(s, index) in workerData.schedules.slice(0, 5)" :key="index">
                                <div style="padding: 1.25rem; background: #f8fafc; border-radius: 12px; border-left: 5px solid var(--primary); transition: transform 0.2s;">
                                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main); margin-bottom: 0.25rem;" x-text="s.nama_program"></div>
                                    <div style="font-size: 0.85rem; color: var(--text-muted);" x-text="s.jam_mulai + ' - ' + s.jam_selesai"></div>
                                </div>
                            </template>
                        </template>
                        <template x-if="!workerData.schedules || workerData.schedules.length === 0">
                            <div style="text-align: center; padding: 4rem 1rem; color: var(--text-muted); opacity: 0.5;">
                                <i data-lucide="calendar" style="width: 48px; height: 48px; margin-bottom: 1rem;"></i>
                                <p style="font-size: 0.95rem;">Belum ada jadwal penugasan.</p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 3. Personal Info -->
                <div class="glass-panel" style="padding: 1.75rem; background: white; border: 1px solid var(--border); display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.04em;">Status Keluarga</div>
                        <div style="font-size: 0.95rem; color: var(--text-main); font-weight: 600;" x-text="workerData.status_keluarga || '-'"></div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.04em;">Status Rumah</div>
                        <div style="font-size: 0.95rem; color: var(--text-main); font-weight: 600;" x-text="workerData.status_rumah || '-'"></div>
                    </div>
                </div>

                <!-- 4. Health & Contact -->
                <div class="glass-panel" style="padding: 1.75rem; background: white; border: 1px solid var(--border); display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.04em;">No. Telepon</div>
                        <div style="font-size: 0.95rem; color: var(--text-main); font-weight: 600;" x-text="workerData.no_telepon || '-'"></div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.04em;">Riwayat Sakit</div>
                        <div style="font-size: 0.95rem; color: var(--text-main); font-weight: 600;" x-text="workerData.riwayat_penyakit || '-'"></div>
                    </div>
                </div>

                <!-- 5. Full Address (Wide) -->
                <div class="glass-panel" style="grid-column: span 2; padding: 1.75rem 2rem; background: white; border: 1px solid var(--border); display: flex; align-items: flex-start; gap: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
                    <div style="width: 48px; height: 48px; background: rgba(15, 118, 110, 0.05); border-radius: 12px; display: flex; justify-content: center; align-items: center; flex-shrink: 0;">
                        <i data-lucide="map-pin" style="color: var(--primary);"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.05em;">Alamat Domisili</div>
                        <div style="font-size: 1.05rem; color: var(--text-main); line-height: 1.5;" x-text="workerData.alamat || 'Alamat lengkap belum tercatat di sistem.'"></div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Professional Footer Bar -->
        <div style="padding: 0.75rem 2.5rem; border-top: 1px solid var(--border); display: flex; justify-content: center; background: white;">
            <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                Sistem Informasi Work4Village &bull; Panel Verifikasi Data Pekerja
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('pekerjaData', () => ({
            showForm: false,
            selectedWorker: false,
            workerData: {},

            async handleViewProfile(id) {
                try {
                    const res = await fetch(`/api/workers/${id}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (res.ok) {
                        this.workerData = await res.json();
                        this.selectedWorker = true;
                        document.body.style.overflow = 'hidden';
                        setTimeout(() => lucide.createIcons(), 50);
                    }
                } catch (err) {
                    console.error(err);
                }
            },

            closeProfile() {
                this.selectedWorker = false;
                document.body.style.overflow = 'unset';
            }
        }));
    });
</script>
@endsection
