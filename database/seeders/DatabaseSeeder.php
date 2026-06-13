<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'nama' => 'Administrator',
            'email' => 'admin@village.com',
            'password_hash' => bcrypt('admin123'),
            'role' => 'admin'
        ]);

        User::factory()->create([
            'nama' => 'Pengawas Lapangan',
            'email' => 'pengawas@village.com',
            'password_hash' => bcrypt('pengawas123'),
            'role' => 'pengawas'
        ]);

        $defaultContents = [
            ['Cara Menanam Sayur Organik', 'Panduan dasar menanam sayuran organik di pekarangan rumah untuk kebutuhan sehari-hari.', 'Pertanian', 'Artikel', 'modal:menanam-sayur'],
            ['Mengelola Sampah Organik Menjadi Kompos', 'Langkah-langkah mudah mengubah sisa makanan dan sampah organik menjadi pupuk kompos yang berguna.', 'Lingkungan', 'Video', 'https://www.youtube.com/watch?v=eBjriH59MLg'],
            ['Membuat Kerajinan dari Barang Bekas', 'Ide kreatif mendaur ulang barang bekas menjadi kerajinan bernilai jual.', 'Keterampilan', 'Artikel', 'modal:kerajinan'],
            ['Tips & Trik: Membuat Kompos Kualitas Tinggi', 'Panduan singkat dan praktis dalam membuat kompos dari limbah rumah tangga dengan rasio karbon nitrogen yang pas.', 'Lingkungan', 'Artikel', 'modal:tips-kompos'],
            ['Tips & Trik: Menghemat Air Pertanian', 'Strategi cerdas mengelola penggunaan air untuk perkebunan dengan mulsa dan irigasi tetes.', 'Pertanian', 'Artikel', 'modal:tips-air'],
            ['Tips & Trik: Keselamatan Kerja Lapangan', 'Modul panduan menjaga kesehatan dan keselamatan, pencegahan dehidrasi saat bekerja di lapangan.', 'Kesehatan', 'Artikel', 'modal:tips-kesehatan'],
            ['Video: Cara Membuat Kompos Cair', 'Tutorial video langkah demi langkah membuat pupuk organik cair dari sampah dapur tangga rumah.', 'Lingkungan', 'Video', 'https://www.youtube.com/watch?v=F0OqNq8F4Xo'],
            ['Video: Panduan Membuat Kompos Padat Bokashi', 'Metode efektif menggunakan EM4 untuk mempercepat pembuatan kompos bokashi siap pakai.', 'Lingkungan', 'Video', 'https://www.youtube.com/watch?v=R9K2S8B72f0'],
            ['Video: Pembuatan Pupuk Kompos Daun Kering', 'Memanfaatkan limbah daun kering untuk pembuatan pupuk kompos dengan cara sederhana.', 'Pertanian', 'Video', 'https://www.youtube.com/watch?v=v2R8p6QzBqg']
        ];

        foreach ($defaultContents as $content) {
            \App\Models\EdukasiContent::create([
                'judul' => $content[0],
                'deskripsi' => $content[1],
                'kategori' => $content[2],
                'tipe_konten' => $content[3],
                'url_konten' => $content[4],
            ]);
        }

        $defaultInventaris = [
            ['Pupuk Kompos Organik', 'Kompos', 50, 'Kg'],
            ['Sayur Bayam', 'Sayur', 120, 'Ikat'],
            ['Tas Rajut Plastik', 'Kerajinan', 15, 'Unit']
        ];

        foreach ($defaultInventaris as $inv) {
            \App\Models\Inventaris::create([
                'nama_barang' => $inv[0],
                'kategori' => $inv[1],
                'kuantitas' => $inv[2],
                'satuan' => $inv[3],
            ]);
        }

        $this->call(MonitoringSeeder::class);
    }
}
