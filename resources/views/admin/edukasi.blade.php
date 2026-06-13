@extends('layouts.app')
@section('title', 'Edukasi Pekerja')

@section('content')
<div style="padding: 2rem;" x-data="edukasiData()">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: bold; color: var(--text-main); margin-bottom: 0.5rem;">Edukasi Pekerja</h1>
            <p style="color: var(--text-muted);">Materi pelatihan & pengembangan keterampilan</p>
        </div>
        <button class="btn btn-primary" @click="showForm = !showForm">
            <i data-lucide="plus" style="width: 18px; height: 18px; margin-right: 8px;"></i>
            <span>Tambah Konten</span>
        </button>
    </div>

    @if(session('success'))
        <div style="background: #f0fdf4; color: #166534; padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-size: 0.85rem; text-align: center;">
            {{ session('success') }}
        </div>
    @endif

    <div x-show="showForm" style="background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid #e1e4e8; margin-bottom: 2rem; display: none;">
        <h3 style="margin-bottom: 1rem; font-weight: 600;">Upload Materi Baru</h3>
        <form method="POST" action="{{ route('admin.edukasi') }}" style="display: flex; flex-direction: column; gap: 1rem;">
            @csrf
            <div>
                <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; color: var(--text-muted);">Judul Materi</label>
                <input type="text" name="judul" class="search-input" style="width: 100%;" required placeholder="Contoh: Cara Menanam Sawi" />
            </div>
            <div>
                <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; color: var(--text-muted);">Deskripsi Singkat</label>
                <textarea name="deskripsi" class="search-input" style="width: 100%; min-height: 80px; resize: vertical;" required placeholder="Jelaskan isi materi..."></textarea>
            </div>
            <div style="display: flex; gap: 1rem;">
                <div style="flex: 1;">
                    <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; color: var(--text-muted);">Kategori</label>
                    <select name="kategori" class="search-input" style="width: 100%;">
                        <option value="Pertanian">Pertanian</option>
                        <option value="Lingkungan">Lingkungan</option>
                        <option value="Keterampilan">Keterampilan</option>
                        <option value="Kesehatan">Kesehatan</option>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; color: var(--text-muted);">Tipe Konten</label>
                    <select name="tipe_konten" class="search-input" style="width: 100%;">
                        <option value="Artikel">Artikel</option>
                        <option value="Video">Video</option>
                        <option value="Panduan PDF">Panduan PDF</option>
                    </select>
                </div>
            </div>
            <div>
                <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; color: var(--text-muted);">URL / Link Materi</label>
                <input type="text" name="url_konten" class="search-input" style="width: 100%;" placeholder="https://... atau modal:tips-kesehatan" />
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" class="btn btn-outline" @click="showForm = false">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Konten</button>
            </div>
        </form>
    </div>

    <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
        <div class="search-container" style="flex: 1; max-width: 400px;">
            <i data-lucide="search" class="search-icon" style="width: 18px; height: 18px;"></i>
            <input type="text" class="search-input" placeholder="Cari judul materi atau kategori..." x-model="searchTerm" />
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
        <template x-for="content in filteredContents" :key="content.id">
            <div style="background: white; border-radius: 12px; overflow: hidden; border: 1px solid #e1e4e8; display: flex; flex-direction: column;">
                <div style="padding: 1.5rem; border-bottom: 1px solid #f1f2f4;">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1rem;">
                        <div :style="'width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: ' + (content.tipe_konten === 'Video' ? '#fff1f0' : '#f0f5ff') + '; color: ' + (content.tipe_konten === 'Video' ? '#ff4d4f' : '#2f54eb')">
                            <i :data-lucide="content.tipe_konten === 'Video' ? 'video' : 'file-text'" style="width: 20px; height: 20px;"></i>
                        </div>
                        <span style="font-size: 0.75rem; font-weight: 500; background: #f5f5f5; padding: 0.25rem 0.75rem; border-radius: 99px; color: #595959;" x-text="content.kategori"></span>
                    </div>
                    <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem; line-height: 1.4;" x-text="content.judul"></h3>
                    <p style="font-size: 0.875rem; color: var(--text-muted); line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;" x-text="content.deskripsi"></p>
                </div>
                <div style="padding: 1rem 1.5rem; background-color: #fafafa; display: flex; justify-content: space-between; align-items: center; margin-top: auto; gap: 0.5rem;">
                    <span style="font-size: 0.75rem; color: var(--text-muted);" x-text="content.tipe_konten"></span>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <button type="button" @click="openEdit(content)" style="font-size: 0.8rem; color: var(--text-muted); background: none; border: none; cursor: pointer; padding: 0;" title="Edit">
                            <i data-lucide="pencil" style="width: 14px; height: 14px;"></i>
                        </button>
                        <form method="POST" :action="'/admin/edukasi/' + content.id" @submit="return confirm('Hapus materi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="font-size: 0.8rem; color: #ef4444; background: none; border: none; cursor: pointer; padding: 0;" title="Hapus">
                                <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                            </button>
                        </form>
                        <button @click="handleOpenMaterial(content)" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--primary); font-weight: 500; background: none; border: none; cursor: pointer; padding: 0;">
                            <span x-text="content.url_konten?.startsWith('modal:') ? 'Baca Modul' : 'Buka Materi'"></span>
                            <i :data-lucide="content.url_konten?.startsWith('modal:') ? 'book-open' : 'external-link'" style="width: 14px; height: 14px;"></i>
                        </button>
                    </div>
                </div>
            </div>
        </template>
        
        <template x-if="filteredContents.length === 0">
            <div style="grid-column: 1 / -1; text-align: center; padding: 4rem; background: white; border-radius: 12px; border: 1px dashed #d9d9d9;">
                <i data-lucide="book-open" style="width: 48px; height: 48px; color: #d9d9d9; margin: 0 auto 1rem;"></i>
                <h3 style="font-size: 1.125rem; font-weight: 500; color: var(--text-main);">Belum ada materi</h3>
                <p style="color: var(--text-muted);">Tambahkan materi edukasi pertama untuk pekerja.</p>
            </div>
        </template>
    </div>

    <!-- Modal Edit Konten -->
    <div x-show="editContent" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9998; display: flex; justify-content: center; align-items: center; padding: 2rem; display: none;" @click="editContent = null">
        <div style="background: white; padding: 1.5rem; border-radius: 12px; width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto;" @click.stop>
            <h3 style="margin-bottom: 1rem; font-weight: 600;">Edit Materi Edukasi</h3>
            <form method="POST" :action="'/admin/edukasi/' + editContent?.id" style="display: flex; flex-direction: column; gap: 1rem;">
                @csrf
                @method('PUT')
                <div>
                    <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem;">Judul Materi</label>
                    <input type="text" name="judul" class="search-input" style="width: 100%;" required x-model="editContent.judul" />
                </div>
                <div>
                    <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem;">Deskripsi</label>
                    <textarea name="deskripsi" class="search-input" style="width: 100%; min-height: 80px;" required x-model="editContent.deskripsi"></textarea>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem;">Kategori</label>
                        <select name="kategori" class="search-input" style="width: 100%;" x-model="editContent.kategori">
                            <option value="Pertanian">Pertanian</option>
                            <option value="Lingkungan">Lingkungan</option>
                            <option value="Keterampilan">Keterampilan</option>
                            <option value="Kesehatan">Kesehatan</option>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem;">Tipe Konten</label>
                        <select name="tipe_konten" class="search-input" style="width: 100%;" x-model="editContent.tipe_konten">
                            <option value="Artikel">Artikel</option>
                            <option value="Video">Video</option>
                            <option value="Panduan PDF">Panduan PDF</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem;">URL / Link Materi</label>
                    <input type="text" name="url_konten" class="search-input" style="width: 100%;" x-model="editContent.url_konten" />
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline" @click="editContent = null">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modul & Video Modal -->
    <div x-show="activeModul" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 9999; display: flex; justify-content: center; align-items: center; padding: 2rem; display: none;" @click="activeModul = null">
        <div :style="'background: ' + (activeModul?.startsWith('video:') ? 'black' : 'white') + '; width: 100%; max-width: ' + (activeModul?.startsWith('video:') ? '900px' : '700px') + '; max-height: 90vh; border-radius: 12px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);'" @click.stop>
            <div style="padding: 1.5rem; border-bottom: 1px solid #e1e4e8; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
                <h2 style="font-size: 1.25rem; font-weight: bold; display: flex; align-items: center; gap: 0.5rem; color: var(--text-main);">
                    <i :data-lucide="activeModul?.startsWith('video:') ? 'video' : 'book-open'" style="color: var(--primary);"></i>
                    <span x-text="activeModul?.startsWith('video:') ? 'Pemutar Video Edukasi' : 'Modul Edukasi Pekerja'"></span>
                </h2>
                <button @click="activeModul = null" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6a737d; line-height: 1;">&times;</button>
            </div>
            <div :style="'padding: ' + (activeModul?.startsWith('video:') ? '0' : '2.5rem') + '; overflow-y: auto; line-height: 1.6; color: #333; background-color: ' + (activeModul?.startsWith('video:') ? 'black' : 'white')">
                
                <!-- Video Player -->
                <template x-if="activeModul?.startsWith('video:')">
                    <div style="position: relative; width: 100%; padding-bottom: 56.25%; height: 0;">
                        <iframe 
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;"
                            :src="'https://www.youtube.com/embed/' + activeModul.split(':')[1] + '?autoplay=1'" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen
                        ></iframe>
                    </div>
                </template>

                <!-- Static Moduls translated from React -->
                <div x-show="activeModul === 'menanam-sayur'" style="display: none;">
                    <h1 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 1.5rem; color: #1a202c;">Panduan Lengkap: Cara Menanam Sayur Organik di Pekarangan</h1>
                    <h3 style="font-size: 1.2rem; font-weight: bold; margin-top: 1.5rem; color: var(--primary);">1. Persiapan Lahan dan Media Tanam</h3>
                    <p style="margin-bottom: 1rem;">Gunakan campuran tanah gembur, pupuk kompos, dan sekam bakar dengan perbandingan 1:1:1. Media tanam ini akan memastikan tanaman mendapatkan nutrisi organik yang cukup tanpa perlu pupuk kimia. Masukkan campuran ke dalam polybag atau bedengan kecil di pekarangan.</p>
                    <h3 style="font-size: 1.2rem; font-weight: bold; margin-top: 1.5rem; color: var(--primary);">2. Menyiapkan Bibit</h3>
                    <p style="margin-bottom: 1rem;">Pilih bibit sayuran yang mudah tumbuh seperti bayam, kangkung, pakcoy, atau sawi. Semai benih pada tray semai dengan kedalaman 1-2 cm. Simpan di tempat yang teduh dan siram dengan _sprayer_ halus setiap pagi.</p>
                    <h3 style="font-size: 1.2rem; font-weight: bold; margin-top: 1.5rem; color: var(--primary);">3. Memindahkan Bibit Semai</h3>
                    <p style="margin-bottom: 1rem;">Ketika tanaman sudah memiliki 3-4 helai daun sejati (sekitar 10-14 hari), pindahkan bibit ke pot/polybag yang lebih besar atau bedengan. Lakukan pemindahan pada sore hari agar tanaman tidak layu tersengat matahari.</p>
                    <h3 style="font-size: 1.2rem; font-weight: bold; margin-top: 1.5rem; color: var(--primary);">4. Perawatan dan Panen</h3>
                    <p style="margin-bottom: 1rem;">Lakukan penyiraman 1-2 kali sehari sesuai cuaca. Untuk mencegah hama, semprotkan pestisida nabati secara rutin sekali seminggu. Sayuran daun biasanya sudah bisa dipanen pada umur 25-30 hari setelah tanam.</p>
                </div>

                <div x-show="activeModul === 'kerajinan'" style="display: none;">
                    <h1 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 1.5rem; color: #1a202c;">Keterampilan: Membuat Kerajinan Bernilai Jual dari Barang Bekas</h1>
                    <h3 style="font-size: 1.2rem; font-weight: bold; margin-top: 1.5rem; color: var(--primary);">A. Mengubah Botol Bekas Menjadi Pot Menggantung</h3>
                    <p style="margin-bottom: 1rem;">Potong botol plastik bekas air mineral menjadi dua bagian. Warnai botol dengan cat akrilik agar lebih menarik. Lubangi bagian sisi botol untuk memasang tali gantungan. Pot ini sangat cocok digunakan dengan metode taman vertikal (vertical garden) yang ditanami tanaman hias.</p>
                    <h3 style="font-size: 1.2rem; font-weight: bold; margin-top: 1.5rem; color: var(--primary);">B. Merajut Plastik Kresek Bekas Menjadi Tas Belanja</h3>
                    <p style="margin-bottom: 1rem;">Kumpulkan kantong plastik kresek bekas, cuci bersih lalu keringkan. Potong plastik tersebut memanjang dan sambungkan setiap utasnya membentuk tali/benang plastik (sering disebut benang plarn). Rajut menggunakan hakpen ukuran besar. Hasil akhir bisa berupa keranjang multifungsi atau tas belanja tahan air.</p>
                    <h3 style="font-size: 1.2rem; font-weight: bold; margin-top: 1.5rem; color: var(--primary);">C. Kerajinan Mosaik dari Pecahan Kaca/Keramik</h3>
                    <p style="margin-bottom: 1rem;">Pecahan keramik bekas rumah atau mangkuk pecah dapat diubah menjadi hiasan pot, tatakan gelas (coaster), atau meja. Gunakan lem keramik untuk merekatkan pecahan tersebut lalu lumuri celah dengan semen nat agar terlihat tertutup dan artistik bernilai jual tinggi.</p>
                </div>

                <div x-show="activeModul === 'tips-kompos'" style="display: none;">
                    <h1 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 1.5rem; color: #1a202c;">Tips & Trik: Membuat Kompos Kualitas Tinggi</h1>
                    <h3 style="font-size: 1.2rem; font-weight: bold; margin-top: 1.5rem; color: var(--primary);">1. Jaga Rasio Karbon dan Nitrogen</h3>
                    <p style="margin-bottom: 1rem;">Campurkan bahan kaya karbon (daun kering, ranting, serbuk gergaji) dengan bahan kaya nitrogen (sisa sayuran, buah, ampas kopi) dengan rasio 2:1 atau 3:1. Hal ini membantu mikroorganisme bekerja lebih cepat.</p>
                    <h3 style="font-size: 1.2rem; font-weight: bold; margin-top: 1.5rem; color: var(--primary);">2. Potong Kecil-kecil Bahan Kompos</h3>
                    <p style="margin-bottom: 1rem;">Semakin kecil ukuran bahan, semakin cepat proses dekomposisi terjadi. Cincang sisa sayur dan remukkan daun kering sebelum dimasukkan ke komposter.</p>
                    <h3 style="font-size: 1.2rem; font-weight: bold; margin-top: 1.5rem; color: var(--primary);">3. Jaga Kelembapan</h3>
                    <p style="margin-bottom: 1rem;">Kompos yang baik harus memiliki kelembapan seperti spons yang diperas. Jika terlalu kering, tambahkan air. Jika terlalu basah, tambahkan bahan cokelat (daun kering/kertas).</p>
                </div>

                <div x-show="activeModul === 'tips-air'" style="display: none;">
                    <h1 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 1.5rem; color: #1a202c;">Tips & Trik: Menghemat Air Pertanian</h1>
                    <h3 style="font-size: 1.2rem; font-weight: bold; margin-top: 1.5rem; color: var(--primary);">1. Gunakan Mulsa Organik</h3>
                    <p style="margin-bottom: 1rem;">Tutup permukaan tanah di sekitar tanaman dengan mulsa organik (jerami, daun kering, atau potongan rumput). Mulsa dapat menahan penguapan air dari tanah hingga 70%.</p>
                    <h3 style="font-size: 1.2rem; font-weight: bold; margin-top: 1.5rem; color: var(--primary);">2. Siram pada Pagi atau Sore Hari</h3>
                    <p style="margin-bottom: 1rem;">Hindari menyiram tanaman pada siang hari karena panas matahari akan menguapkan air sebelum diserap akar. Waktu terbaik adalah pagi hari sebelum pukul 09.00 atau sore hari setelah pukul 16.00.</p>
                    <h3 style="font-size: 1.2rem; font-weight: bold; margin-top: 1.5rem; color: var(--primary);">3. Gunakan Sistem Irigasi Tetes</h3>
                    <p style="margin-bottom: 1rem;">Sistem irigasi tetes sangat efisien karena memberikan air langsung ke akar tanaman secara perlahan. Ini mengurangi limpasan air dan penguapan secara signifikan dibandingkan penyiraman manual.</p>
                </div>

                <div x-show="activeModul === 'tips-kesehatan'" style="display: none;">
                    <h1 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 1.5rem; color: #1a202c;">Tips & Trik: Keselamatan Kerja Lapangan</h1>
                    <h3 style="font-size: 1.2rem; font-weight: bold; margin-top: 1.5rem; color: var(--primary);">1. Selalu Gunakan Alat Pelindung Diri (APD)</h3>
                    <p style="margin-bottom: 1rem;">Gunakan sarung tangan, sepatu bot tebal, dan topi lebar saat bekerja di lahan terbuka. Hal ini akan melindungi Anda dari benda tajam, gigitan serangga, dan sengatan matahari berlebih.</p>
                    <h3 style="font-size: 1.2rem; font-weight: bold; margin-top: 1.5rem; color: var(--primary);">2. Cukupi Kebutuhan Cairan (Hidrasi)</h3>
                    <p style="margin-bottom: 1rem;">Pekerjaan fisik mengeluarkan banyak keringat. Pastikan meminum air putih setidaknya satu gelas setiap jam untuk mencegah dehidrasi, lemas, atau heatstroke (sengatan panas).</p>
                    <h3 style="font-size: 1.2rem; font-weight: bold; margin-top: 1.5rem; color: var(--primary);">3. Lakukan Peregangan Berkala</h3>
                    <p style="margin-bottom: 1rem;">Lakukan peregangan sederhana setiap 2 jam bekerja untuk melemaskan otot, terutama setelah melakukan gerakan berulang seperti mencangkul atau memanen. Hindari mengangkat beban terlalu berat sendirian.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('edukasiData', () => ({
            showForm: false,
            searchTerm: '',
            contents: @json($contents),
            activeModul: null,
            editContent: null,
            
            get filteredContents() {
                if (this.searchTerm === '') {
                    return this.contents;
                }
                const term = this.searchTerm.toLowerCase();
                return this.contents.filter(c => 
                    c.judul.toLowerCase().includes(term) ||
                    c.kategori.toLowerCase().includes(term)
                );
            },

            init() {
                this.$watch('filteredContents', () => {
                    setTimeout(() => lucide.createIcons(), 50);
                });
            },

            formatExternalUrl(url) {
                if (!url) return '#';
                if (url.startsWith('http://') || url.startsWith('https://')) return url;
                return `https://${url}`;
            },

            handleOpenMaterial(content) {
                if (content.url_konten && content.url_konten.startsWith('modal:')) {
                    this.activeModul = content.url_konten.split(':')[1];
                    setTimeout(() => lucide.createIcons(), 50);
                } else if (content.url_konten && content.url_konten.includes('youtube.com/watch?v=')) {
                    const videoId = content.url_konten.split('v=')[1]?.split('&')[0];
                    this.activeModul = `video:${videoId}`;
                    setTimeout(() => lucide.createIcons(), 50);
                } else {
                    const anchor = document.createElement('a');
                    anchor.href = this.formatExternalUrl(content.url_konten);
                    anchor.target = '_blank';
                    anchor.rel = 'noopener noreferrer';
                    anchor.click();
                }
            },

            openEdit(content) {
                this.editContent = { ...content };
                setTimeout(() => lucide.createIcons(), 50);
            }
        }));
    });
</script>
@endsection
