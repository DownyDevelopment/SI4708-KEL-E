<p align="center">
  <img src="apps/web/public/images/logo.png" alt="Work4Village Logo" width="120" height="120" style="border-radius: 20px;">
</p>

<h1 align="center">Work4Village</h1>
<p align="center"><strong>Sistem Informasi Manajemen Program Kerja Mikro Prasejahtera Desa</strong></p>
<p align="center">
  <em>Pemberdayaan sosial, SDG 1 (Tanpa Kemiskinan) melalui penyediaan lapangan kerja mikro, SDG 2 (Tanpa Kelaparan) melalui pengembangan kebun pangan komunitas, serta SDG 3 (Kehidupan Sehat dan Sejahtera) melalui perbaikan sanitasi dan kualitas lingkungan permukiman.</em>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Next.js-14.x-000000?style=for-the-badge&logo=next.js&logoColor=white" alt="Next.js">
  <img src="https://img.shields.io/badge/Node.js-20.x-339933?style=for-the-badge&logo=nodedotjs&logoColor=white" alt="Node.js">
  <img src="https://img.shields.io/badge/Express.js-4.x-000000?style=for-the-badge&logo=express&logoColor=white" alt="Express.js">
  <img src="https://img.shields.io/badge/PostgreSQL-Database-4169E1?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL">
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="TailwindCSS">
  <img src="https://img.shields.io/badge/GraphQL-Enabled-E10098?style=for-the-badge&logo=graphql&logoColor=white" alt="GraphQL">
</p>

---

## Tim Pengembang (Scrum Team)

Aplikasi ini dikembangkan oleh Tim Mahasiswa S1 Sistem Informasi Universitas Telkom menggunakan metodologi Agile (Scrum):

| No | Nama | NIM | Peran | Tanggung Jawab Modul / PBI |
|----|------|-----|-------|-----------------------------|
| 1 | Muhammad Luthfi TR | 102022300133 | Project Manager | Monitoring Progres & Kendala Lapangan (PBI 10-12) |
| 2 | Zahra Annisa Inayah | 102022330277 | Scrum Master | Dashboard Evaluasi & Laporan Dampak (PBI 19-21) |
| 3 | Anisa Fatiimatus Zahro | 102022330350 | Developer | Manajemen Registrasi & Hak Akses Profil (PBI 1-3) |
| 4 | Raafi Naufal Fadhillah | 102022300053 | Developer | Perencanaan Program Kerja Mikro & Area (PBI 4-6) |
| 5 | Ariq Anugrah Zahid | 102022330115 | Developer | Sistem Operasional & Penjadwalan Kerja (PBI 7-9) |
| 6 | Fayyadl Ahsan Amala | 102022300346 | Developer | Sistem Finansial, Insentif & Akumulasi Bulanan (PBI 13-15) |
| 7 | Josua Immanuel Natanael P | 102022300271 | Developer | Manajemen Inventaris & Distribusi Hasil Desa (PBI 16-18) |

---

## Arsitektur Sistem

Aplikasi **Work4Village** menggunakan pendekatan *Three-Tier Layered Architecture* untuk memisahkan fungsionalitas visual, logika bisnis, dan data transaksional agar menjamin keamanan data warga prasejahtera.

```
┌─────────────────────────────────────────────────────────────────────┐
│                           CLIENT LAYER                                │
│      ┌───────────────────────────┐   ┌───────────────────────────┐  │
│      │     Web Admin Portal       │   │   Pengawas Field Mobile    │  │
│      │     (Next.js App)          │   │   (Responsive View)        │  │
│      └─────────────┬───────────────┘   └─────────────┬───────────┘  │
│                    │   HTML5 / CSS3 / Tailwind CSS    │              │
└────────────────────┼──────────────────────────────────┼──────────────┘
                      │                                  │
                      ▼                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│                         APPLICATION LAYER                             │
│  ┌───────────────────────────────────────────────────────────────┐  │
│  │                     REST / GRAPHQL API                        │  │
│  │   AuthRouter    │ ProgramController   │ LogbookController      │  │
│  │   WorkerRouter  │ IncentiveController │ DistributionController │  │
│  └───────────────────────────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────────────────────────┐  │
│  │                     BUSINESS LOGIC RUNTIME                     │  │
│  │     Node.js Run-Time Engine & Express Layer                    │  │
│  └───────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
                      │                                  │
                      ▼                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│                            DATA LAYER                                 │
│  ┌──────────────────────────────────────┐  ┌──────────────────────┐  │
│  │   Relational PostgreSQL Database      │  │   Cloud Asset Drive  │  │
│  │   (Tables: worker, daily_log,         │  │   (Bukti Foto Kerja  │  │
│  │   incentive_record, inventory_item)   │  │   Before & After)    │  │
│  └──────────────────────────────────────┘  └──────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
```

### Alur Kerja Sistem (Main Workflow)

```
Admin Buat Tugas & Alokasikan Warga ──► Pengawas Cek Jadwal di Lapangan
                                                  │
                                                  ▼
                                  Presensi & Input Logbook
                                  (Pencatatan Progres + Foto)
                                                  │
                                                  ▼
                                       Validasi Bukti Lapangan
                                                  │
                              ┌───────────────────┴───────────────────┐
                              ▼                                       ▼
                       Foto Invalid                          Valid (Disetujui)
                       (Ditolak Admin)                               │
                                                                      ▼
                                                       Kalkulasi Upah Otomatis
                                                       & Catat Virtual Ledger
                                                                      │
                                                                      ▼
                                                       Update Stok Hasil Desa
                                                       & Grafik Tren Bulanan
```

---

## Fitur Utama Sistem

Sistem ini membatasi hak akses secara ketat menjadi dua peran pengguna utama demi penyederhanaan alur birokrasi digital di tingkat desa.

### Sisi Admin Desa (Manajerial & Analitis)

| Fitur | Deskripsi |
|-------|-----------|
| **Registrasi & Profiling** | Pendaftaran data warga miskin secara terstruktur (kemampuan, status keluarga, riwayat penugasan). |
| **Penjadwalan Mikro** | Pembuatan dan alokasi jadwal shift harian/mingguan untuk kegiatan kebersihan, kebun komunitas, atau pengelolaan sampah. |
| **Kalkulator Insentif** | Perhitungan upah otomatis berbasis hasil kerja valid serta akumulasi ledger bulanan untuk melacak kenaikan taraf hidup warga. |
| **Inventaris Hasil Desa** | Operasi CRUD untuk melacak sisa stok komoditas fisik seperti kuantitas kompos dan hasil panen kebun desa. |
| **Dashboard & Tren** | Visualisasi dashboard analitik mengenai tren produktivitas historis pekerja dan ekspor laporan dampak berformat PDF. |

### Sisi Pengawas Lapangan (Operasional & Validasi)

| Fitur | Deskripsi |
|-------|-----------|
| **Pemetaan Titik Tugas** | Visualisasi peta interaktif berbasis koordinat geografis untuk panduan lokasi penugasan di area desa. |
| **Pencatatan Logbook** | Form presensi digital harian warga dan pengisian volume hasil kerja (luas area bersih/berat sampah). |
| **Evidence Upload** | Fitur wajib mengunggah foto bukti fisik pekerjaan (sebelum dan sesudah) sebagai syarat utama akuntabilitas pencairan upah. |
| **Pelaporan Kendala** | Eskalasi pelaporan masalah operasional di lapangan (seperti alat penunjang rusak) secara *real-time* ke admin. |
| **Log Distribusi** | Pencatatan alur penyaluran hasil produksi desa agar tepat sasaran ke keluarga prasejahtera yang berhak. |

---

## Hak Akses Berbasis Peran (RBAC)

| Peran (Role) | Ruang Lingkup Hak Akses |
|--------------|--------------------------|
| **Admin Desa** | Akses Penuh (Insert, Read, Update, Delete) pada konfigurasi data master, master pekerja, penentuan insentif, manajemen inventaris desa, dan dashboard analitik laporan dampak. |
| **Pengawas Lapangan** | Akses Operasional Teknis (Insert, Read, Update, Delete) pada logbook harian, upload bukti foto, koordinasi logistik internal, mencatat distribusi barang, dan modul materi edukasi warga. |

---

## Panduan Instalasi & Menjalankan Server

### Prasyarat Sistem
- **Node.js** v18.x atau v20.x (Disarankan versi LTS)
- **PostgreSQL** atau **MySQL** Server aktif
- **Git**

### Langkah Pengaturan

#### 1. Kloning Repositori
```bash
git clone https://github.com/downydevelopment/si4708-kel-e.git
cd si4708-kel-e
```

#### 2. Instalasi Dependensi (Monorepo Node)
```bash
# Menginstal paket dependency untuk sub-frontend (apps) dan sub-backend (services)
npm install
```

#### 3. Konfigurasi Environment File

Salin berkas template `.env.example` menjadi `.env` di root direktori atau di dalam masing-masing folder servis:

```bash
cp .env.example .env
```

Sesuaikan isi kredensial database di dalam berkas `.env`:

```env
PORT=
DATABASE_URL=
JWT_SECRET=
```

#### 4. Migrasi Skema Basis Data

Jalankan perintah otomatisasi pembuatan struktur tabel database dan injeksi data awal (seeding):

```bash
npm run db:setup
```

#### 5. Menjalankan Server Pengembangan (Development Mode)

Disarankan menggunakan **dua terminal terpisah** agar proses pemantauan log sistem berjalan optimal:

- **Terminal 1 Sisi Backend API (Express & GraphQL Server)**
  ```bash
  npm run dev:api
  ```
- **Terminal 2 Sisi Frontend Web Portal (Next.js)**
  ```bash
  npm run dev:web
  ```

*Catatan: Anda juga bisa menjalankan keduanya secara bersamaan lewat satu instruksi jika terpasang script concurrent: `npm run dev`*

Aplikasi dapat diakses penuh melalui peramban di alamat: **http://localhost:3000**

---

## Struktur Direktori Proyek

```text
Work4Village/
├── apps/                        # Lapisan Antarmuka Pengguna (Client Layer)
│   ├── web/                     # Proyek Utama Next.js (Admin & Pengawas Portal)
│   │   ├── pages/               # Routing halaman web portal
│   │   ├── components/          # Kumpulan komponen UI modular
│   │   └── public/              # Berkas static assets & gambar mockup
│   └── admin/                   # Cadangan dashboard web internal
├── services/                    # Lapisan Logika Sistem (Application Layer)
│   ├── api/                     # Runtime server Express & GraphQL endpoints
│   │   ├── routes/              # Definisi router URI endpoint REST
│   │   ├── controllers/         # Handler logika utama fungsional (PBI)
│   │   └── models/              # Pemetaan skema tabel DB (Prisma/Sequelize ORM)
│   └── worker/                  # Background task handler untuk rekap upah bulanan
├── database/                    # Lapisan Penyimpanan Data (Data Layer)
│   ├── migrations/              # Berkas rekam historis migrasi SQL
│   └── seeds/                   # Data master awal untuk pengujian sistem (Seeder)
├── config/                      # Berkas penampung file JSON konfigurasi global
├── .env.example                 # Template acuan variabel environment sistem
├── package.json                 # Manajemen scripts perintah npm proyek root
└── tsconfig.json                # Pengaturan kompilator bahasa TypeScript
```

---

## Kesimpulan

**Work4Village** mentransformasikan pengelolaan program penanggulangan kemiskinan desa (skema *cash-for-work*). Melalui pemisahan arsitektur yang modular, pencatatan logbook berbasis bukti fisik (*evidence upload*), dan visualisasi data, platform ini menjamin bahwa setiap upah insentif terdistribusi secara adil, sekaligus memacu kemandirian ekonomi desa.