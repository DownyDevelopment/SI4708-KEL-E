@extends('layouts.app')
@section('title', 'Daftar Program Kerja Desa')

@section('content')
<div class="animate-fade-in" x-data="programKerjaData()">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.8rem;">Daftar Program Kerja Desa</h1>
            <p>Perencanaan dan pemantauan program kerja mikro untuk warga.</p>
        </div>
        <button class="btn btn-primary" @click="showForm = !showForm">
            <i data-lucide="plus-circle" style="width: 18px; height: 18px; margin-right: 8px;"></i> Tambah Program
        </button>
    </div>

    <!-- Feedback Message -->
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
                placeholder="Cari nama program atau jenis program..." 
                style="padding-left: 3rem;"
                x-model="searchTerm"
            />
        </div>
    </div>

    <div x-show="showForm" class="glass-panel" style="padding: 2rem; margin-bottom: 2rem; display: none;">
        <h3 style="margin-bottom: 1.5rem;">Buat Program Kerja Baru</h3>
        <form method="POST" action="{{ route('admin.program') }}" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            @csrf
            <div class="form-group">
                <label class="form-label">Nama Program</label>
                <input type="text" name="nama_program" class="form-input" placeholder="Contoh: Pembersihan Saluran Air RT 02" required />
            </div>
            <div class="form-group">
                <label class="form-label">Jenis Program</label>
                <select name="jenis_program" class="form-input" required>
                    <option value="Kesehatan">Kesehatan</option>
                    <option value="Infrastruktur">Infrastruktur</option>
                    <option value="Lingkungan">Lingkungan</option>
                    <option value="Pendidikan">Pendidikan</option>
                    <option value="Sosial">Sosial</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-input" required />
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-input" required />
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Deskripsi Program</label>
                <textarea name="deskripsi" class="form-input" rows="3" placeholder="Jelaskan tujuan dan detail pekerjaan..." required></textarea>
            </div>
            <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                <button type="button" class="btn btn-outline" @click="showForm = false">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Program</button>
            </div>
        </form>
    </div>

    <div class="glass-panel" style="padding: 1.5rem;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama Program</th>
                    <th>Jenis</th>
                    <th>Periode</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="p in filteredPrograms" :key="p.id">
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <i data-lucide="briefcase" style="color: var(--primary); width: 16px; height: 16px;"></i>
                                <span style="font-weight: 500;" x-text="p.nama_program"></span>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <i data-lucide="tag" style="width: 14px; height: 14px;"></i> <span x-text="p.jenis_program"></span>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem;">
                                <span x-text="formatDate(p.tanggal_mulai)"></span> - <span x-text="formatDate(p.tanggal_selesai)"></span>
                            </div>
                        </td>
                        <td x-html="getStatusBadge(p.status)"></td>
                        <td>
                            <a :href="'/admin/perencanaan'" class="btn btn-outline btn-sm">Detail</a>
                        </td>
                    </tr>
                </template>
                <template x-if="filteredPrograms.length === 0">
                    <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">Belum ada program kerja yang cocok.</td></tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('programKerjaData', () => ({
            showForm: false,
            searchTerm: '',
            programs: @json($programs),
            
            get filteredPrograms() {
                if (this.searchTerm === '') {
                    return this.programs;
                }
                const term = this.searchTerm.toLowerCase();
                return this.programs.filter(p => 
                    p.nama_program.toLowerCase().includes(term) ||
                    p.jenis_program.toLowerCase().includes(term)
                );
            },

            init() {
                this.$watch('filteredPrograms', () => {
                    setTimeout(() => lucide.createIcons(), 50);
                });
            },

            formatDate(dateString) {
                if (!dateString) return '-';
                return new Date(dateString).toLocaleDateString('id-ID');
            },

            getStatusBadge(status) {
                switch (status) {
                    case 'planned': return '<span class="badge badge-outline">Direncanakan</span>';
                    case 'active':
                    case 'ongoing':
                    case 'in_progress': return '<span class="badge badge-primary">Berjalan</span>';
                    case 'completed': 
                    case 'selesai': return '<span class="badge badge-success">Selesai</span>';
                    default: return '<span class="badge badge-outline">' + status + '</span>';
                }
            }
        }));
    });
</script>
@endsection
