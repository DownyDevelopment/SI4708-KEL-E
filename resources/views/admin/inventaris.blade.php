@extends('layouts.app')
@section('title', 'Manajemen Inventaris')

@section('content')
<div style="padding: 2rem;" x-data="inventarisData()">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: bold; color: var(--text-main); margin-bottom: 0.5rem;">Manajemen Inventaris</h1>
            <p style="color: var(--text-muted);">Lacak produk, tambah stok panen, & distribusi.</p>
        </div>
        <button class="btn btn-primary" @click="showAddForm = true">
            <i data-lucide="plus" style="width: 18px; height: 18px; margin-right: 8px;"></i>
            <span>Tambah Barang Baru</span>
        </button>
    </div>

    @if(session('success'))
        <div style="background: #f0fdf4; color: #166534; padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-size: 0.85rem; text-align: center;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Modal Tambah Barang -->
    <div x-show="showAddForm" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 99; display: flex; justify-content: center; align-items: center; display: none;">
        <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 500px;" @click.stop>
            <h3 style="margin-bottom: 1.5rem; font-weight: 600; font-size: 1.25rem;">Tambah Barang Inventaris</h3>
            <form method="POST" action="{{ route('admin.inventaris') }}" style="display: flex; flex-direction: column; gap: 1rem;">
                @csrf
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem;">Nama Barang</label>
                    <input type="text" name="nama_barang" class="search-input" style="width: 100%;" required placeholder="Cth: Hasil Panen Pakcoy" />
                </div>
                <div style="display: flex; gap: 1rem;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem;">Kategori</label>
                        <select name="kategori" class="search-input" style="width: 100%;">
                            <option value="Kompos">Kompos</option>
                            <option value="Sayur">Sayur</option>
                            <option value="Kerajinan">Kerajinan</option>
                            <option value="Peralatan Tani">Peralatan Tani</option>
                            <option value="Bibit & Benih">Bibit & Benih</option>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem;">Satuan</label>
                        <select name="satuan" class="search-input" style="width: 100%;">
                            <option value="Kg">Kilogram (Kg)</option>
                            <option value="Ikat">Ikat</option>
                            <option value="Unit">Unit</option>
                            <option value="Liter">Liter (L)</option>
                            <option value="Karung">Karung / Sak</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem;">Stok Awal</label>
                    <input type="number" step="0.1" name="kuantitas" class="search-input" style="width: 100%;" placeholder="0" required />
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem;">
                    <button type="button" class="btn btn-outline" @click="showAddForm = false">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Barang</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Kurangi/Tambah/Jual Stok -->
    <div x-show="activeAction && (activeAction.type === 'tambah' || activeAction.type === 'kurang' || activeAction.type === 'jual')" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 99; display: flex; justify-content: center; align-items: center; display: none;">
        <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 450px;" @click.stop>
            <h3 style="margin-bottom: 1.5rem; font-weight: bold; display: flex; align-items: center; gap: 0.5rem;" :style="'color: ' + (activeAction?.type === 'tambah' ? '#10b981' : (activeAction?.type === 'jual' ? '#f59e0b' : '#ef4444'))">
                <i :data-lucide="activeAction?.type === 'tambah' ? 'arrow-down-to-line' : (activeAction?.type === 'kurang' ? 'arrow-up-to-line' : 'shopping-cart')"></i>
                <span x-text="activeAction?.type === 'tambah' ? 'Tambah Stok Masuk' : (activeAction?.type === 'jual' ? 'Penjualan Barang' : 'Distribusi Gratis (Keluar)')"></span>
            </h3>
            <form method="POST" :action="'/admin/inventaris/' + (activeAction?.id) + '/adjust'" style="display: flex; flex-direction: column; gap: 1rem;">
                @csrf
                <input type="hidden" name="tipe" :value="activeAction?.type === 'jual' ? 'kurang' : activeAction?.type">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem;">Jumlah (<span x-text="selectedItem?.satuan"></span>)</label>
                    <input type="number" step="0.1" min="0.1" name="jumlah" class="search-input" style="width: 100%;" required placeholder="Cth: 5" />
                </div>

                <template x-if="activeAction?.type === 'jual'">
                    <div style="display: flex; gap: 1rem;">
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem;">Nama Pembeli</label>
                            <input type="text" class="search-input" style="width: 100%;" x-model="pembeli" placeholder="Bapak Budi..." />
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem;">Total Harga Jual (Rp)</label>
                            <input type="number" min="0" class="search-input" style="width: 100%;" x-model="harga" placeholder="150000" />
                        </div>
                    </div>
                </template>

                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem;">Catatan / Keterangan</label>
                    <textarea name="keterangan" class="search-input" style="width: 100%; min-height: 80px; resize: vertical;" x-model="keterangan" :placeholder="activeAction?.type === 'kurang' ? 'Dibagikan ke warga RT 01...' : (activeAction?.type === 'jual' ? 'Dijual eceran ke pasar...' : 'Hasil panen minggu ke-2...')" :required="activeAction?.type !== 'jual'"></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem;">
                    <button type="button" class="btn btn-outline" @click="closeAction()">Batal</button>
                    <button type="submit" class="btn" :style="'background: ' + (activeAction?.type === 'tambah' ? '#10b981' : (activeAction?.type === 'jual' ? '#f59e0b' : '#ef4444')) + '; color: white; display: flex; align-items: center; gap: 0.5rem;'" @click="prepareSubmit(event)">
                        <template x-if="activeAction?.type === 'jual'"><i data-lucide="shopping-cart" style="width: 16px; height: 16px;"></i></template>
                        <span x-text="'Konfirmasi ' + (activeAction?.type === 'tambah' ? 'Masuk' : (activeAction?.type === 'jual' ? 'Penjualan' : 'Keluar'))"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Riwayat Stok -->
    <div x-show="activeAction && activeAction.type === 'history'" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 99; display: flex; justify-content: center; align-items: center; display: none;" @click="activeAction = null">
        <div style="background: white; padding: 0; border-radius: 12px; width: 100%; max-width: 600px; max-height: 80vh; display: flex; flex-direction: column;" @click.stop>
            <div style="padding: 1.5rem; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-weight: bold; display: flex; align-items: center; gap: 0.5rem; color: var(--text-main);">
                    <i data-lucide="history" style="color: var(--primary);"></i> Riwayat Transaksi Stok: <span x-text="selectedItem?.nama_barang"></span>
                </h3>
                <button @click="activeAction = null" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <div style="padding: 1.5rem; overflow-y: auto; flex: 1;">
                <template x-if="historyData.length === 0">
                    <p style="text-align: center; color: #888;">Belum ada riwayat transaksi.</p>
                </template>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <template x-for="h in historyData" :key="h.id">
                        <div style="display: flex; gap: 1rem; padding: 1rem; border: 1px solid #eee; border-radius: 8px;" :style="h.tipe_perubahan === 'tambah' ? 'background: #ecfdf5;' : 'background: #fef2f2;'">
                            <div :style="'color: ' + (h.tipe_perubahan === 'tambah' ? '#10b981' : '#ef4444')">
                                <i :data-lucide="h.tipe_perubahan === 'tambah' ? 'arrow-down-to-line' : 'arrow-up-to-line'" style="width: 24px; height: 24px;"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between;">
                                    <strong :style="'color: ' + (h.tipe_perubahan === 'tambah' ? '#047857' : '#b91c1c')" x-text="h.tipe_perubahan === 'tambah' ? 'Stok Masuk' : 'Distribusi Keluar'"></strong>
                                    <span style="font-size: 0.8rem; color: #666;" x-text="new Date(h.created_at).toLocaleString('id-ID')"></span>
                                </div>
                                <div style="font-size: 1.2rem; font-weight: bold; margin: 0.25rem 0; color: #333;">
                                    <span x-text="(h.tipe_perubahan === 'tambah' ? '+' : '-') + Number(h.jumlah_perubahan) + ' ' + selectedItem?.satuan"></span>
                                </div>
                                <p style="font-size: 0.875rem; color: #555; margin: 0;" x-text="h.keterangan"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>


    <div class="search-container" style="max-width: 400px; margin-bottom: 2rem;">
        <i data-lucide="search" class="search-icon" style="width: 18px; height: 18px;"></i>
        <input type="text" class="search-input" placeholder="Cari nama barang atau kategori..." x-model="searchTerm" />
    </div>

    <div style="background: white; border-radius: 12px; border: 1px solid #e1e4e8; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa; border-bottom: 1px solid #e1e4e8; text-align: left;">
                    <th style="padding: 1rem; font-size: 0.875rem; color: var(--text-muted);">Barang</th>
                    <th style="padding: 1rem; font-size: 0.875rem; color: var(--text-muted);">Kategori</th>
                    <th style="padding: 1rem; font-size: 0.875rem; color: var(--text-muted);">Sisa Stok</th>
                    <th style="padding: 1rem; font-size: 0.875rem; color: var(--text-muted); text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="item in filteredItems" :key="item.id">
                    <tr style="border-bottom: 1px solid #f1f2f4;">
                        <td style="padding: 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="background: #f0f5ff; padding: 0.5rem; border-radius: 8px; color: var(--primary);">
                                    <i data-lucide="package" style="width: 20px; height: 20px;"></i>
                                </div>
                                <span style="font-weight: 500; color: var(--text-main);" x-text="item.nama_barang"></span>
                            </div>
                        </td>
                        <td style="padding: 1rem;">
                            <span style="display: inline-block; padding: 0.25rem 0.75rem; background: #f5f5f5; border-radius: 99px; font-size: 0.75rem; color: #555;" :style="'border: 1px solid ' + getStockColorClass(item.kategori) + '40'" x-text="item.kategori"></span>
                        </td>
                        <td style="padding: 1rem;">
                            <div style="display: flex; align-items: baseline; gap: 0.25rem;">
                                <span style="font-size: 1.25rem; font-weight: bold;" x-text="Number(item.kuantitas)"></span>
                                <span style="font-size: 0.875rem; color: var(--text-muted);" x-text="item.satuan"></span>
                            </div>
                        </td>
                        <td style="padding: 1rem; text-align: right;">
                            <div style="display: flex; gap: 0.4rem; justify-content: flex-end;">
                                <button class="btn btn-outline" style="padding: 0.4rem 0.5rem; color: #10b981; border-color: #10b981;" @click="openAction(item, 'tambah')" title="Tambah Stok (Panen/Masuk)">
                                    <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                                </button>
                                <button class="btn btn-outline" style="padding: 0.4rem 0.5rem; color: #ef4444; border-color: #ef4444;" @click="openAction(item, 'kurang')" title="Distribusi Keluar / Penggunaan">
                                    <i data-lucide="minus" style="width: 16px; height: 16px;"></i>
                                </button>
                                <button class="btn btn-primary" style="padding: 0.4rem 0.6rem; background: #f59e0b; border-color: #f59e0b; display: flex; align-items: center; gap: 0.4rem;" @click="openAction(item, 'jual')" title="Jual Barang ke Pembeli">
                                    <i data-lucide="shopping-cart" style="width: 16px; height: 16px;"></i>
                                    <span style="font-size: 0.8rem;">Jual</span>
                                </button>
                                <button class="btn btn-outline" style="padding: 0.4rem 0.5rem;" @click="fetchHistory(item)" title="Riwayat Transaksi">
                                    <i data-lucide="history" style="width: 16px; height: 16px;"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
                <template x-if="filteredItems.length === 0">
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            <i data-lucide="package" style="width: 48px; height: 48px; color: #d9d9d9; margin: 0 auto 1rem;"></i>
                            Belum ada barang di inventaris
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('inventarisData', () => ({
            items: @json($items),
            searchTerm: '',
            showAddForm: false,
            activeAction: null, // { id, type }
            selectedItem: null,
            historyData: [],
            
            pembeli: '',
            harga: '',
            keterangan: '',

            get filteredItems() {
                if (this.searchTerm === '') {
                    return this.items;
                }
                const term = this.searchTerm.toLowerCase();
                return this.items.filter(i => 
                    i.nama_barang.toLowerCase().includes(term) ||
                    i.kategori.toLowerCase().includes(term)
                );
            },

            init() {
                this.$watch('filteredItems', () => {
                    setTimeout(() => lucide.createIcons(), 50);
                });
            },

            getStockColorClass(kategori) {
                if (kategori === 'Kompos') return '#10b981'; // green
                if (kategori === 'Sayur') return '#3b82f6'; // blue
                if (kategori === 'Kerajinan') return '#f59e0b'; // orange
                if (kategori === 'Peralatan Tani') return '#8b5cf6'; // purple
                if (kategori === 'Bibit & Benih') return '#ec4899'; // pink
                return '#6b7280'; // gray
            },

            openAction(item, type) {
                this.selectedItem = item;
                this.activeAction = { id: item.id, type: type };
                this.pembeli = '';
                this.harga = '';
                this.keterangan = '';
                setTimeout(() => lucide.createIcons(), 50);
            },

            closeAction() {
                this.activeAction = null;
                this.selectedItem = null;
            },

            prepareSubmit(e) {
                if (this.activeAction.type === 'jual') {
                    const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
                    this.keterangan = `🛒 Penjualan kepada: ${this.pembeli || 'Umum'} | Total: ${formatter.format(this.harga || 0)} | Catatan: ${this.keterangan || '-'}`;
                }
            },

            async fetchHistory(item) {
                this.selectedItem = item;
                try {
                    const res = await fetch(`/api/inventaris/${item.id}/history`);
                    if (res.ok) {
                        this.historyData = await res.json();
                        this.activeAction = { id: item.id, type: 'history' };
                        setTimeout(() => lucide.createIcons(), 50);
                    }
                } catch (error) {
                    console.error('Error fetching history:', error);
                }
            }
        }));
    });
</script>
@endsection
