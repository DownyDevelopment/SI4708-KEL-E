@extends('layouts.app')
@section('title', 'Profiling Keluarga Prasejahtera')

@section('content')
<div class="animate-fade-in" x-data="keluargaData()">
    <x-hero-banner title="Profiling Keluarga Prasejahtera" description="Data awal rumah tangga — indikator kemiskinan, kelaparan (SDG 2), dan kesehatan (SDG 3).">
        <x-slot:actions>
            <a href="/admin/pekerja" class="global-hero-banner-btn-ghost" style="text-decoration: none;">
                <i data-lucide="user" style="width: 18px; height: 18px; margin-right: 8px;"></i> Data Pekerja
            </a>
            <button class="global-hero-banner-btn-white" @click="openAddForm()">
                <i data-lucide="plus-circle" style="width: 18px; height: 18px; margin-right: 8px;"></i> Tambah Keluarga
            </button>
        </x-slot:actions>
    </x-hero-banner>

    @if(session('success'))
        <div style="background: #f0fdf4; color: #166534; padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-size: 0.85rem; text-align: center;">
            {{ session('success') }}
        </div>
    @endif

    <div style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
        <div style="position: relative; flex: 1;">
            <i data-lucide="search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 18px; height: 18px;"></i>
            <input 
                type="text" 
                class="form-input" 
                placeholder="Cari nama kepala keluarga atau RT/RW..." 
                style="padding-left: 3rem;"
                x-model="searchTerm"
            />
        </div>
    </div>

    <div x-show="showForm" x-cloak class="glass-panel" style="padding: 2rem; margin-bottom: 2rem;">
        <h3 style="margin-bottom: 1.5rem;" x-text="editMode ? 'Edit Data Keluarga' : 'Input Data Keluarga Baru'"></h3>
        <form method="POST" :action="editMode ? '/admin/keluarga/' + householdData.id : '{{ route('admin.keluarga') }}'" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>
            <div class="form-group">
                <label class="form-label">Nama Kepala Keluarga</label>
                <input type="text" name="kepala_keluarga" class="form-input" required x-model="householdData.kepala_keluarga" />
            </div>
            <div class="form-group">
                <label class="form-label">Nama Desa / Kelurahan</label>
                <input type="text" name="nama_desa" class="form-input" placeholder="Contoh: Desa Karya Makmur" x-model="householdData.nama_desa" />
            </div>
            <div class="form-group">
                <label class="form-label">RT / RW</label>
                <input type="text" name="rt_rw" class="form-input" placeholder="Contoh: 002/005" required x-model="householdData.rt_rw" />
            </div>
            <div class="form-group">
                <label class="form-label">Jumlah Anggota Keluarga</label>
                <input type="number" name="jumlah_anggota" class="form-input" required min="1" x-model="householdData.jumlah_anggota" />
            </div>
            <div class="form-group">
                <label class="form-label">Estimasi Pendapatan / Bulan (Rp)</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); font-weight: bold;">Rp</span>
                    <input type="number" name="pendapatan_per_bulan" class="form-input" style="padding-left: 3rem;" required min="0" x-model="householdData.pendapatan_per_bulan" />
                </div>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Alamat Rumah</label>
                <textarea name="alamat" class="form-input" rows="2" required x-model="householdData.alamat"></textarea>
            </div>
            <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                <button type="button" class="btn btn-outline" @click="showForm = false; editMode = false">Batal</button>
                <button type="submit" class="btn btn-primary" x-text="editMode ? 'Simpan Perubahan' : 'Simpan Data'"></button>
            </div>
        </form>
    </div>

    <div class="glass-panel" style="padding: 1.5rem;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kepala Keluarga</th>
                    <th>Desa</th>
                    <th>RT/RW</th>
                    <th>Anggota</th>
                    <th>Pendapatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="h in filteredHouseholds" :key="h.id">
                    <tr>
                        <td x-text="'# ' + h.id"></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <i data-lucide="home" style="color: var(--primary); width: 16px; height: 16px;"></i>
                                <span style="font-weight: 500;" x-text="h.kepala_keluarga"></span>
                            </div>
                        </td>
                        <td x-text="h.nama_desa || '—'"></td>
                        <td x-text="h.rt_rw"></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <i data-lucide="users" style="width: 14px; height: 14px;"></i> <span x-text="h.jumlah_anggota"></span>
                            </div>
                        </td>
                        <td x-text="'Rp ' + parseInt(h.pendapatan_per_bulan).toLocaleString('id-ID')"></td>
                        <td>
                            <button class="btn btn-outline btn-sm" @click="openEditForm(h)">Edit</button>
                        </td>
                    </tr>
                </template>
                <template x-if="filteredHouseholds.length === 0">
                    <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">Tidak ada data keluarga yang cocok.</td></tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('keluargaData', () => ({
            showForm: false,
            editMode: false,
            searchTerm: '',
            households: @json($households),
            householdData: {
                id: null,
                kepala_keluarga: '',
                rt_rw: '',
                jumlah_anggota: 1,
                pendapatan_per_bulan: 0,
                alamat: '',
                nama_desa: '',
            },

            get filteredHouseholds() {
                if (this.searchTerm === '') {
                    return this.households;
                }
                const term = this.searchTerm.toLowerCase();
                return this.households.filter(h => 
                    h.kepala_keluarga.toLowerCase().includes(term) ||
                    h.rt_rw.toLowerCase().includes(term)
                );
            },

            openAddForm() {
                this.editMode = false;
                this.householdData = {
                    id: null,
                    kepala_keluarga: '',
                    rt_rw: '',
                    jumlah_anggota: 1,
                    pendapatan_per_bulan: 0,
                    alamat: '',
                    nama_desa: '',
                };
                this.showForm = true;
            },

            openEditForm(h) {
                this.editMode = true;
                this.householdData = { ...h };
                this.showForm = true;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            init() {
                this.$watch('filteredHouseholds', () => {
                    setTimeout(() => lucide.createIcons(), 50);
                });
            }
        }));
    });
</script>
@endsection
