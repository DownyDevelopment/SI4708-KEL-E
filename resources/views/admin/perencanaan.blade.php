@extends('layouts.app')
@section('title', 'Perencanaan Program & Area')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<div class="animate-fade-in" style="padding: 2rem; max-width: 1400px; margin: 0 auto;" x-data="perencanaanData()">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 800; background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 0.5rem;">
                Perencanaan Program & Area
            </h1>
            <p style="color: var(--text-muted); font-size: 1.05rem;">Kelola program kerja, tentukan kordinat area, dan tingkatkan koordinasi multi-stakeholder.</p>
        </div>
        <button 
            @click="resetForm(); showModal = true"
            class="btn btn-primary"
            style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; border-radius: 99px; box-shadow: 0 8px 20px rgba(15, 118, 110, 0.3);"
        >
            <i data-lucide="plus" style="width: 20px; height: 20px;"></i>
            <span>Tambah Program</span>
        </button>
    </div>

    @if(session('success'))
        <div style="background: #f0fdf4; color: #166534; padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-size: 0.85rem; text-align: center;">
            {{ session('success') }}
        </div>
    @endif

    <div class="glass-panel" style="padding: 2rem; margin-bottom: 2.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="color: var(--text-main); font-size: 1rem; font-weight: 600; margin: 0;">Peta Area Kerja</h3>
            <div style="display: flex; gap: 0.75rem;">
                <button class="btn btn-outline" @click="toggleMapType" style="padding: 0.5rem 1rem; font-size: 0.8rem;" x-text="mapType === 'street' ? 'Satellite' : 'Street'"></button>
                <button :class="isAddingMode ? 'btn' : 'btn btn-primary'" @click="isAddingMode = !isAddingMode" style="padding: 0.5rem 1rem; font-size: 0.8rem;" :style="isAddingMode ? 'background: #dc2626; color: white;' : ''" x-text="isAddingMode ? 'Batal Tambah' : '+ Tambah Titik'"></button>
            </div>
        </div>
        <div style="height: 320px; width: 100%; border-radius: 12px; overflow: hidden;" :style="isAddingMode ? 'border: 3px dashed var(--primary); cursor: crosshair;' : 'border: 1px solid var(--border);'">
            <div id="perencanaan-map" style="height: 100%; width: 100%; z-index: 0;"></div>
        </div>
        <div x-show="isAddingMode" style="font-size: 0.8rem; color: var(--primary); margin-top: 0.5rem; text-align: center; display: none;">
            Klik pada peta untuk menambahkan titik lokasi program baru.
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 2rem;">
        <template x-for="prog in programs" :key="prog.id">
            <div class="glass-panel" style="padding: 1.75rem; display: flex; flex-direction: column; height: 100%; transition: all 0.3s ease; cursor: default;" 
                 @mouseenter="$el.style.transform = 'translateY(-5px)'"
                 @mouseleave="$el.style.transform = 'translateY(0)'"
            >
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem;">
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <span style="background: rgba(15, 118, 110, 0.1); color: var(--primary); padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;" x-text="prog.jenis_program"></span>
                            <span x-html="getStatusBadge(prog.status)"></span>
                        </div>
                        <h3 style="font-size: 1.35rem; font-weight: bold; color: var(--text-main); margin-top: 0.25rem; line-height: 1.3;" x-text="prog.nama_program"></h3>
                    </div>
                    <div style="display: flex; gap: 0.25rem; background: var(--background); padding: 4px; border-radius: 8px;">
                        <button @click="handleEdit(prog)" style="background: transparent; border: none; color: var(--text-muted); cursor: pointer; padding: 4px; border-radius: 4px;" @mouseenter="$el.style.background = 'rgba(0,0,0,0.05)'" @mouseleave="$el.style.background = 'transparent'"><i data-lucide="edit-2" style="width: 16px; height: 16px;"></i></button>
                        <form method="POST" :action="'/admin/perencanaan/' + prog.id" style="margin: 0; display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus program ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: transparent; border: none; color: var(--danger); cursor: pointer; padding: 4px; border-radius: 4px;" @mouseenter="$el.style.background = 'rgba(239, 68, 68, 0.1)'" @mouseleave="$el.style.background = 'transparent'"><i data-lucide="trash-2" style="width: 16px; height: 16px;"></i></button>
                        </form>
                    </div>
                </div>
                
                <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.75rem; flex: 1; line-height: 1.6;" x-text="prog.deskripsi"></p>
                
                <div style="display: flex; flex-direction: column; gap: 1rem; font-size: 0.9rem; color: var(--text-main); background: rgba(248, 250, 252, 0.5); padding: 1rem; border-radius: 12px; border: 1px solid var(--border);">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
                            <i data-lucide="map-pin" style="color: var(--primary); width: 16px; height: 16px; margin-top: 2px;"></i>
                            <div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">AREA / LOKASI</div>
                                <div x-text="prog.lokasi || '-'"></div>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
                            <i data-lucide="globe" style="color: var(--primary); width: 16px; height: 16px; margin-top: 2px;"></i>
                            <div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">KORDINAT PETA</div>
                                <div style="font-family: monospace; font-size: 0.8rem;" x-text="prog.kordinat || '-'"></div>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
                        <i data-lucide="calendar" style="color: var(--primary); width: 16px; height: 16px; margin-top: 2px;"></i>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">PERIODE PELAKSANAAN</div>
                            <div><span x-text="formatDate(prog.tanggal_mulai)"></span> s/d <span x-text="formatDate(prog.tanggal_selesai)"></span></div>
                        </div>
                    </div>
                    
                    <div style="display: flex; align-items: flex-start; gap: 0.5rem; margin-top: 0.5rem; padding-top: 0.75rem; border-top: 1px dashed var(--border);">
                        <i data-lucide="users" style="color: var(--primary); width: 16px; height: 16px; margin-top: 2px;"></i>
                        <div style="width: 100%;">
                            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; margin-bottom: 0.5rem;">KOORDINASI STAKEHOLDER</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                                <template x-for="(st, i) in parseStakeholders(prog.stakeholders)" :key="i">
                                    <div style="background: white; border: 1px solid var(--border); padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                                        <span style="font-weight: 600; color: var(--primary);" x-text="st.nama"></span>
                                        <span style="color: var(--text-muted); font-size: 0.7rem; padding-left: 4px; border-left: 1px solid var(--border);" x-text="st.peran"></span>
                                    </div>
                                </template>
                                <template x-if="parseStakeholders(prog.stakeholders).length === 0">
                                    <span style="font-size: 0.8rem; font-style: italic; color: var(--text-muted);">Belum ada stakeholder ditambahkan</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="programs.length === 0">
            <div style="grid-column: 1 / -1; text-align: center; padding: 5rem 2rem; color: var(--text-muted); background: var(--surface); border-radius: var(--radius-lg); border: 1px dashed var(--border);">
                <i data-lucide="target" style="width: 48px; height: 48px; color: var(--border); margin: 0 auto 1rem auto;"></i>
                <h3 style="font-size: 1.2rem; font-weight: 600; color: var(--text-main);">Belum Ada Program</h3>
                <p>Mulai perencanaan dengan menambahkan program kerja dan area fokus.</p>
            </div>
        </template>
    </div>

    <!-- Modal Form -->
    <div x-show="showModal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 1rem; display: none;">
        <div class="glass-panel animate-fade-in" style="width: 100%; max-width: 750px; padding: 0; max-height: 90vh; overflow-y: auto; background: var(--surface);">
            <div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: var(--surface); z-index: 10;">
                <h2 style="font-size: 1.5rem; font-weight: bold; display: flex; align-items: center; gap: 0.75rem;">
                    <i data-lucide="briefcase" style="color: var(--primary);"></i>
                    <span x-text="editingId ? 'Edit Program Kerja' : 'Perencanaan Program Baru'"></span>
                </h2>
                <button @click="showModal = false" style="background: var(--background); border: none; cursor: pointer; color: var(--text-muted); padding: 0.5rem; border-radius: 50%;"><i data-lucide="x" style="width: 20px; height: 20px;"></i></button>
            </div>
            
            <form method="POST" :action="editingId ? '/admin/perencanaan/' + editingId : '/admin/perencanaan'" style="padding: 2rem; display: flex; flex-direction: column; gap: 1.5rem;">
                @csrf
                <template x-if="editingId">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                
                <input type="hidden" name="stakeholders" :value="JSON.stringify(stakeholders)">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label class="form-label">Nama Program</label>
                        <input type="text" class="form-input" name="nama_program" x-model="formData.nama_program" required placeholder="Contoh: Pemberdayaan Petani Sayur" style="width: 100%;" />
                    </div>
                    <div>
                        <label class="form-label">Kategori / Jenis</label>
                        <input type="text" class="form-input" name="jenis_program" x-model="formData.jenis_program" required placeholder="Contoh: Pertanian" style="width: 100%;" />
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label class="form-label">Area / Lokasi Fokus</label>
                        <div style="position: relative;">
                            <div style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"><i data-lucide="map-pin" style="width: 18px; height: 18px;"></i></div>
                            <input type="text" class="form-input" name="lokasi" x-model="formData.lokasi" style="width: 100%; padding-left: 2.5rem;" placeholder="Contoh: Dusun Mawar, Desa Karya" />
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Kordinat Area (Lat, Long)</label>
                        <div style="position: relative;">
                            <div style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"><i data-lucide="globe" style="width: 18px; height: 18px;"></i></div>
                            <input type="text" class="form-input" name="kordinat" x-model="formData.kordinat" style="width: 100%; padding-left: 2.5rem;" placeholder="-6.914744, 107.609810" />
                        </div>
                    </div>
                </div>

                <div style="background: rgba(15, 118, 110, 0.03); padding: 1.5rem; border-radius: 12px; border: 1px solid rgba(15, 118, 110, 0.1);">
                    <label class="form-label" style="color: var(--primary); display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                        <i data-lucide="users" style="width: 18px; height: 18px;"></i> Koordinasi Multi-Stakeholder
                    </label>
                    
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                        <div style="flex: 1;">
                            <input type="text" class="form-input" x-model="shNama" placeholder="Nama Institusi / Tokoh" style="width: 100%;" />
                        </div>
                        <div style="flex: 1;">
                            <select class="form-input" x-model="shPeran" style="width: 100%;">
                                <option value="">-- Pilih Peran --</option>
                                <option value="Pemerintah Desa">Pemerintah Desa</option>
                                <option value="LSM / Komunitas">LSM / Komunitas</option>
                                <option value="Swasta / Sponsor">Swasta / Sponsor</option>
                                <option value="Relawan">Relawan</option>
                                <option value="Tokoh Masyarakat">Tokoh Masyarakat</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <button type="button" @click="handleAddStakeholder" class="btn btn-secondary" style="white-space: nowrap;">Tambah</button>
                    </div>

                    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; min-height: 40px;">
                        <template x-for="(st, i) in stakeholders" :key="i">
                            <div style="display: flex; align-items: center; gap: 0.5rem; background: white; border: 1px solid var(--primary); color: var(--text-main); padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; box-shadow: 0 2px 5px rgba(15, 118, 110, 0.1);">
                                <strong x-text="st.nama"></strong>
                                <span style="color: var(--text-muted); font-size: 0.75rem;" x-text="'(' + st.peran + ')'"></span>
                                <button type="button" @click="handleRemoveStakeholder(i)" style="background: var(--danger); border: none; color: white; cursor: pointer; display: flex; padding: 2px; border-radius: 50%; margin-left: 4px;"><i data-lucide="x" style="width: 12px; height: 12px;"></i></button>
                            </div>
                        </template>
                        <template x-if="stakeholders.length === 0">
                            <span style="font-size: 0.9rem; color: var(--text-muted); display: flex; align-items: center;">Belum ada stakeholder ditambahkan. Silakan isi form di atas.</span>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="form-label">Deskripsi Lengkap</label>
                    <textarea class="form-input" name="deskripsi" x-model="formData.deskripsi" rows="3" placeholder="Jelaskan tujuan dan ruang lingkup program ini..." style="width: 100%; resize: vertical;"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-input" name="tanggal_mulai" x-model="formData.tanggal_mulai" style="width: 100%;" />
                    </div>
                    <div>
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" class="form-input" name="tanggal_selesai" x-model="formData.tanggal_selesai" style="width: 100%;" />
                    </div>
                    <div>
                        <label class="form-label">Status Program</label>
                        <select class="form-input" name="status" x-model="formData.status" style="width: 100%;">
                            <option value="planned">Direncanakan</option>
                            <option value="active">Aktif Berjalan</option>
                            <option value="completed">Selesai</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                    <button type="button" @click="showModal = false" class="btn btn-outline" style="background: var(--background);">Batal</button>
                    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;" x-text="editingId ? 'Simpan Perubahan' : 'Buat Program'"></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('perencanaanData', () => ({
            programs: @json($programs),
            showModal: false,
            editingId: null,
            mapType: 'street',
            isAddingMode: false,
            map: null,
            streetLayer: null,
            satelliteLayer: null,
            formData: {
                nama_program: '',
                jenis_program: '',
                deskripsi: '',
                lokasi: '',
                kordinat: '',
                tanggal_mulai: '',
                tanggal_selesai: '',
                status: 'planned'
            },
            stakeholders: [],
            shNama: '',
            shPeran: '',

            init() {
                this.$watch('programs', () => {
                    setTimeout(() => lucide.createIcons(), 50);
                });
                this.$nextTick(() => this.initMap());
            },

            initMap() {
                if (this.map || typeof L === 'undefined') return;

                this.map = L.map('perencanaan-map').setView([-6.914744, 107.609810], 13);
                this.streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
                this.satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}');
                this.streetLayer.addTo(this.map);
                this.refreshMarkers();

                this.map.on('click', async (e) => {
                    if (!this.isAddingMode) return;

                    const nama = prompt('Masukkan Nama Program/Area Baru:');
                    if (!nama) {
                        this.isAddingMode = false;
                        return;
                    }

                    const lokasi = prompt('Masukkan Deskripsi Lokasi (contoh: RT 01):') || 'Area Baru';
                    const kordinat = `${e.latlng.lat},${e.latlng.lng}`;

                    try {
                        const res = await fetch('/api/programs', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                nama_program: nama,
                                jenis_program: 'Lainnya',
                                lokasi: lokasi,
                                kordinat: kordinat,
                                status: 'planned'
                            })
                        });

                        if (res.ok) {
                            window.location.reload();
                        }
                    } catch (err) {
                        alert('Gagal menambahkan titik.');
                    }
                    this.isAddingMode = false;
                });
            },

            refreshMarkers() {
                if (!this.map) return;
                this.map.eachLayer((layer) => {
                    if (layer instanceof L.Marker) {
                        this.map.removeLayer(layer);
                    }
                });

                this.programs.forEach((program) => {
                    if (!program.kordinat) return;
                    const [lat, lng] = program.kordinat.split(',').map(Number);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        L.marker([lat, lng]).addTo(this.map)
                            .bindPopup(`<strong>${program.nama_program}</strong><br/>Lokasi: ${program.lokasi || '-'}<br/>Status: ${program.status || '-'}`);
                    }
                });
            },

            toggleMapType() {
                if (!this.map) return;
                if (this.mapType === 'street') {
                    this.map.removeLayer(this.streetLayer);
                    this.satelliteLayer.addTo(this.map);
                    this.mapType = 'satellite';
                } else {
                    this.map.removeLayer(this.satelliteLayer);
                    this.streetLayer.addTo(this.map);
                    this.mapType = 'street';
                }
            },

            resetForm() {
                this.formData = {
                    nama_program: '', jenis_program: '', deskripsi: '', lokasi: '',
                    kordinat: '', tanggal_mulai: '', tanggal_selesai: '', status: 'planned'
                };
                this.stakeholders = [];
                this.shNama = '';
                this.shPeran = '';
                this.editingId = null;
            },

            handleEdit(prog) {
                this.formData = {
                    nama_program: prog.nama_program,
                    jenis_program: prog.jenis_program,
                    deskripsi: prog.deskripsi,
                    lokasi: prog.lokasi || '',
                    kordinat: prog.kordinat || '',
                    tanggal_mulai: prog.tanggal_mulai ? prog.tanggal_mulai.substring(0, 10) : '',
                    tanggal_selesai: prog.tanggal_selesai ? prog.tanggal_selesai.substring(0, 10) : '',
                    status: prog.status || 'planned'
                };
                this.stakeholders = this.parseStakeholders(prog.stakeholders);
                this.editingId = prog.id;
                this.showModal = true;
                setTimeout(() => lucide.createIcons(), 50);
            },

            handleAddStakeholder() {
                const nama = this.shNama.trim();
                const peran = this.shPeran.trim() || 'Lainnya';
                if (nama) {
                    this.stakeholders.push({ nama, peran });
                    this.shNama = '';
                    this.shPeran = '';
                }
            },

            handleRemoveStakeholder(index) {
                this.stakeholders.splice(index, 1);
            },

            parseStakeholders(str) {
                if (!str) return [];
                try {
                    const parsed = JSON.parse(str);
                    return parsed.map(item => {
                        if (typeof item === 'string') return { nama: item, peran: 'Lainnya' };
                        return item;
                    });
                } catch(e) {
                    if (typeof str === 'string') return str.split(',').map(s => ({ nama: s.trim(), peran: 'Lainnya' }));
                    return [];
                }
            },

            formatDate(dateString) {
                if (!dateString) return '-';
                return new Date(dateString).toLocaleDateString('id-ID');
            },

            getStatusBadge(status) {
                switch(status) {
                    case 'active':
                    case 'ongoing':
                    case 'in_progress':
                        return '<span style="background: rgba(34, 197, 94, 0.15); color: var(--success); padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; display: flex; align-items: center; gap: 4px;"><svg class="lucide lucide-activity" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg> Aktif</span>';
                    case 'completed':
                    case 'selesai':
                        return '<span style="background: rgba(59, 130, 246, 0.15); color: var(--secondary); padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; display: flex; align-items: center; gap: 4px;"><svg class="lucide lucide-check-circle" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Selesai</span>';
                    case 'planned':
                    default:
                        return '<span style="background: rgba(245, 158, 11, 0.15); color: var(--warning); padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; display: flex; align-items: center; gap: 4px;"><svg class="lucide lucide-clock" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg> Direncanakan</span>';
                }
            }
        }));
    });
</script>
@endsection
