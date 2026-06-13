# Work4Village — Catatan Lanjutan: Kesesuaian PBI (Setelah 11.A, 11.B, 11.C)

> Dokumen ini melanjutkan `cursor.md`. **Checklist utama (#1–#7) sudah selesai.** Bagian "Sisa Kekurangan" di bawah sudah disinkronkan dengan kode terbaru — hanya item opsional yang benar-benar belum ada.

---

## Ringkasan Skor PBI (Estimasi Setelah Perbaikan cursor2)

| Anggota | PBI | Estimasi |
|---------|-----|----------|
| 1 | 3 PBI | ~80% |
| 2 | 3 PBI | ~85% |
| 3 | 3 PBI | ~85% |
| 4 | 3 PBI | ~80% |
| 5 | 3 PBI | ~85% |
| 6 | 3 PBI | ~85% |
| 7 | 3 PBI | ~80% |

**Total: ~82–85%** dari spesifikasi PBI awal.

---

## Status PBI Per Anggota (Sinkron dengan Kode)

### Anggota 1 — Manajemen Data Dasar (~80%)

| PBI | Status | Catatan |
|---|---|---|
| Registrasi Pekerja | ✅ Cukup | CRUD pekerja + keluarga; usia tampil di tabel & form; banner registrasi terpadu + tautan antar menu Keluarga/Pekerja. |
| **Profil Pekerja** | ✅ Selesai | `/admin/pekerja/{id}/profil` & `/pengawas/pekerja/{id}/profil` — kemampuan, program, jadwal. |
| Role & Permission | ✅ Selesai | Hanya `admin` dan `pengawas`; middleware, sidebar, Pengaturan Akses selaras. |

---

### Anggota 2 — Perencanaan Program & Area (~85%)

| PBI | Status | Catatan |
|---|---|---|
| Daftar Program Mikro | ✅ Selesai | `/admin/program` redirect ke `/admin/perencanaan`. |
| Peta Area Kerja | ✅ Selesai | Leaflet di Perencanaan; seeder program punya koordinat. |
| Koordinasi Multi-Stakeholder | ✅ Cukup | Stakeholder JSON + notifikasi ke pengawas saat program disimpan/diupdate. Belum auto-link ke akun user (opsional). |

---

### Anggota 3 — Operasional & Penjadwalan (~85%)

| PBI | Status | Catatan |
|---|---|---|
| Jadwal Harian/Mingguan | ✅ Selesai | Filter mingguan di dashboard admin + label periode. |
| Notifikasi Tugas | ✅ Cukup | Notif jadwal baru, edit, hapus + stok masuk/bahan kebun ke pengawas. |
| Logbook Harian | ✅ Selesai | Menu logbook redirect ke tab Operasional. |

---

### Anggota 4 — Monitoring & Kendala Lapangan (~80%)

| PBI | Status | Catatan |
|---|---|---|
| Monitoring Pekerjaan | ✅ Selesai | Field `detail_monitoring` per jenis pekerjaan (luas area, berat sampah, tanaman, dll). |
| Pelaporan Masalah | ✅ Selesai | Form + daftar masalah untuk pengawas. |
| **Validasi Hasil Kerja (BARU)** | ✅ Selesai | Foto before/after wajib; validasi admin → upah otomatis. |

---

### Anggota 5 — Ekonomi & Insentif (~85%)

| PBI | Status | Catatan |
|---|---|---|
| Sistem Insentif & Upah | ✅ Selesai | Validasi logbook + input pengawas/admin. |
| Reward & Pengakuan | ✅ Cukup | Catatan teks (spek tidak wajib PDF). |
| Kalkulator Upah Bulanan (BARU) | ✅ Selesai | Akumulasi lintas program keluarga di `EkonomiController@detail`. |

---

### Anggota 6 — Produksi & Distribusi (~85%)

| PBI | Status | Catatan |
|---|---|---|
| Distribusi Hasil | ✅ Selesai | `household_id` di form distribusi & histori inventaris. |
| Edukasi & Tips | ✅ Selesai | Tambah, edit, hapus materi edukasi. |
| Manajemen Stok | ✅ Selesai | `/admin/tracking-reducing` redirect ke inventaris (filter kompos/kerajinan). |

---

### Anggota 7 — Evaluasi & Dampak (~80%)

| PBI | Status | Catatan |
|---|---|---|
| Dashboard Program | ✅ Selesai | Stat keahlian fleksibel (Bertani/Petani, Membersihkan, Kerajinan). |
| **Laporan Dampak (PDF)** | ✅ Selesai | Form input dampak lingkungan + unduh PDF Dompdf; seeder demo environmental data. |
| **Tren Produktivitas (BARU)** | ✅ Selesai | Grafik dari logbook/jadwal (fallback tanggal schedule jika logbook kosong). |

---

## Sisa Opsional (Bukan Blocker Demo)

Hanya kerjakan jika masih ada waktu sebelum presentasi:

1. **Halaman registrasi satu layar** — gabung form keluarga + pekerja dalam satu wizard (saat ini sudah cukup via tautan silang).
2. **Stakeholder → akun user** — hubungkan nama stakeholder ke user di sistem + kirim pesan internal otomatis.
3. **Reward sertifikat PDF** — generate PDF penghargaan (spek tidak mewajibkan).

---

## Checklist Pengerjaan (Sesi cursor2)

- [x] **1. Halaman Profil Pekerja** — Route `/admin/pekerja/{id}/profil` & `/pengawas/pekerja/{id}/profil`, view `admin/pekerja-profil.blade.php`, tombol Profil di tabel pekerja & profiling.
- [x] **2. Foto before/after Validasi Hasil Kerja** — Kolom `foto_sebelum` & `foto_sesudah` di logbooks, form operasional dua upload, validasi admin wajib kedua foto.
- [x] **3. Form Environmental Tracking + export PDF asli** — Form input di `/admin/analisis`, model `EnvironmentalTracking` diisi, unduh PDF via Dompdf (`/admin/analisis/pdf`).
- [x] **4. Tren Produktivitas dari logbook/jadwal** — `ProduktivitasController` pakai data `WorkSchedule` + `Logbook`, bukan status program.
- [x] **5. Role disederhanakan** — Hanya `admin` dan `pengawas`; middleware, sidebar, seeder, dan Pengaturan Akses selaras.
- [x] **6. Polish opsional** — Notifikasi jadwal edit/hapus + stok masuk, `household_id` di distribusi, edit/hapus edukasi, kalkulator upah lintas program keluarga.

## Checklist Polish Demo (Sesi lanjutan)

- [x] **7a. Tugas mingguan dashboard** — Filter `WorkSchedule` per minggu berjalan + label periode di widget.
- [x] **7b. Stat keahlian dashboard** — Matching keyword fleksibel (Bertani/Petani, Membersihkan, Kerajinan).
- [x] **7c. Usia pekerja** — Kolom usia di tabel pekerja (accessor `Worker::usia`) + preview usia di form.
- [x] **7d. Monitoring per jenis pekerjaan** — Field `detail_monitoring` di logbook (luas area, berat sampah, tanaman, dll).
- [x] **7e. Akun demo & seeder** — User `admin@village.com` / `pengawas@village.com`, keahlian seeder selaras dashboard.

## Checklist Sinkronisasi Dokumen (Sesi terbaru)

- [x] **8a. Registrasi terpadu (ringan)** — Banner + tautan silang menu Keluarga ↔ Pekerja, preview usia saat input tanggal lahir.
- [x] **8b. Seeder demo analisis** — Data `EnvironmentalTracking` + `tanggal` logbook di `MonitoringSeeder`.
- [x] **8c. Produktivitas fallback tanggal** — Grafik pakai `logbook.tanggal` → `schedule.tanggal` → `created_at`.
- [x] **8d. Notifikasi stakeholder** — Pengawas dapat notif saat program dengan stakeholder disimpan/diupdate.
- [x] **8e. Update cursor2.md** — Tabel gap diselaraskan dengan implementasi aktual.

---

## Cara Lanjut ke Cursor

Contoh prompt untuk sesi berikutnya:

> "Baca `@cursor.md` dan `@cursor2.md`. Semua checklist sudah selesai. Fokus ke bug demo atau item opsional (#1–#3 di Sisa Opsional) jika diperlukan."

Setelah tiap item selesai, minta update checklist supaya progres tetap tercatat.
