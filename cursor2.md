# Work4Village — Catatan Lanjutan: Kesesuaian PBI (Setelah 11.A, 11.B, 11.C)

> Dokumen ini melanjutkan `cursor.md`. Status terbaru: **Bagian 11.A, 11.B, dan 11.C sudah dikerjakan semua.** Dengan begitu, klaim-klaim "sudah jalan" di analisis kekurangan PBI (search bar, MonitoringSeeder, role supervisor/relawan, peta di Perencanaan, workflow validasi→upah otomatis) **konsisten** dengan progres yang sudah dilakukan — jadi tidak ada kejanggalan besar lagi seperti yang sempat saya curigai sebelumnya.

Fokus dokumen ini sekarang: **apa saja gap PBI yang TERSISA** setelah semua perbaikan di `cursor.md` selesai, supaya kelompok bisa fokus ke sisa pekerjaan menjelang presentasi.

---

## Ringkasan Skor PBI (Estimasi Setelah A+B+C Selesai)

| Anggota | PBI | Estimasi |
|---------|-----|----------|
| 1 | 3 PBI | ~55% |
| 2 | 3 PBI | ~75% |
| 3 | 3 PBI | ~75% |
| 4 | 3 PBI | ~55% |
| 5 | 3 PBI | ~75% |
| 6 | 3 PBI | ~75% |
| 7 | 3 PBI | ~55% |

**Total: ~65–70%** dari spesifikasi PBI awal.

> 💡 Catatan kecil: angka ini berasal dari analisis AI berdasarkan PBI spec, bukan hasil cek manual baris-per-baris. Kalau mau yakin 100%, sesekali minta Cursor **mengutip kode asli** untuk klaim tertentu — tapi untuk keperluan presentasi, gap di bawah ini sudah cukup jelas dan masuk akal sebagai daftar kerja.

---

## Sisa Kekurangan Per Anggota

### Anggota 1 — Manajemen Data Dasar (~55%)

| PBI | Status | Yang masih kurang |
|---|---|---|
| Registrasi Pekerja | ⚠️ Sebagian | Data warga masih terpecah di 2 menu (Keluarga vs Pekerja); usia disimpan sebagai `tanggal_lahir`, tidak ditampilkan sebagai usia; belum ada halaman registrasi terpadu. |
| **Profil Pekerja** | ❌ Belum ada | **Tidak ada halaman profil** (ringkasan kemampuan + program yang diikuti + jadwal kerja). Menu **Profiling** ≠ ini — Profiling adalah analisis kesejahteraan ekonomi, bukan profil individu pekerja. |
| Role & Permission | ⚠️ Sebagian | Role `supervisor`/`relawan` sudah diarahkan ke modul pengawas, tapi belum *granular per fitur* — semua role non-admin punya akses yang sama, belum ada pembatasan permission spesifik sesuai spek ("admin, petugas lapangan, supervisor, relawan" punya akses berbeda-beda). |

**🎯 Gap paling jelas:** halaman **Profil Pekerja** — ini satu-satunya PBI di seluruh proyek yang statusnya masih 0%/belum ada sama sekali (bukan sekadar "kurang lengkap").

---

### Anggota 2 — Perencanaan Program & Area (~75%)

| PBI | Status | Yang masih kurang |
|---|---|---|
| Daftar Program Mikro | ✅ Cukup | Route `/admin/program` kemungkinan masih ada sebagai sisa duplikat backend — cek apakah sudah benar-benar dihapus/redirect ke `/admin/perencanaan`. |
| Peta Area Kerja | ✅ Cukup | Marker hanya muncul jika program punya data `kordinat` di DB — pastikan semua program contoh/seeder punya koordinat supaya peta tidak kosong saat demo. |
| Koordinasi Multi-Stakeholder | ⚠️ Sebagian | Masih sebatas catatan teks JSON — belum ada koneksi nyata ke akun user relawan/donatur atau notifikasi otomatis ke mereka. |

---

### Anggota 3 — Operasional & Penjadwalan (~75%)

| PBI | Status | Yang masih kurang |
|---|---|---|
| Jadwal Harian/Mingguan | ✅ Cukup | Label "mingguan" di dashboard kemungkinan masih belum benar-benar memfilter per minggu (cek lagi setelah perbaikan 11.B). |
| Notifikasi Tugas | ⚠️ Sebagian | Masih hanya notif "jadwal baru" ke pengawas — belum ada notif untuk pengiriman bahan/kebun atau saat jadwal di-edit/dihapus. |
| Logbook Harian | ✅ Cukup | Pastikan menu logbook dobel sudah benar-benar digabung (bukan cuma redirect tanpa konten tambahan). |

---

### Anggota 4 — Monitoring & Kendala Lapangan (~55%)

| PBI | Status | Yang masih kurang |
|---|---|---|
| Monitoring Pekerjaan | ⚠️ Sebagian | `progres_persentase` + `catatan` masih generik — belum ada field spesifik per jenis pekerjaan (luas area dibersihkan, jenis tanaman kebun, berat sampah dikumpulkan, dll). |
| Pelaporan Masalah | ✅ Cukup | Relawan sudah bisa akses via middleware — pastikan ada UI/flow yang memang ditujukan untuk role relawan (bukan cuma "ikut numpang" di tampilan pengawas). |
| **Validasi Hasil Kerja (BARU)** | ⚠️ Sebagian | Workflow validasi→upah otomatis sudah ada ✅, tapi **upload foto masih cuma 1, belum before & after** seperti yang diminta spek ("foto bukti pekerjaan sebelum & sesudah"). |

**🎯 Gap paling jelas:** **foto before/after** untuk validasi hasil kerja — ini PBI [BARU] skala menengah dan biasanya jadi sorotan penilaian, jadi worth diprioritaskan.

---

### Anggota 5 — Ekonomi & Insentif (~75%)

| PBI | Status | Yang masih kurang |
|---|---|---|
| Sistem Insentif & Upah | ✅ Cukup | Sudah otomatis dari validasi logbook + pengawas sudah bisa input (fix dari 11.A.3). Kondisi ini sudah cukup baik. |
| Reward & Pengakuan | ✅ Cukup | Sertifikat masih berupa catatan teks, bukan generate PDF — ini **wajar**, spek tidak mengharuskan PDF, jadi tidak perlu dikerjakan kecuali ada waktu lebih. |
| Kalkulator Upah Bulanan (BARU) | ✅ Cukup | Belum menggabungkan otomatis SEMUA sumber pendapatan keluarga lintas program — masih dihitung per jenis transaksi. |

---

### Anggota 6 — Produksi & Distribusi (~75%)

| PBI | Status | Yang masih kurang |
|---|---|---|
| Distribusi Hasil | ✅ Cukup | Belum auto-link ke data keluarga penerima (dropdown household ada di tracking, tapi tidak terhubung ke form distribusi). |
| Edukasi & Tips | ✅ Cukup | Edit/hapus materi edukasi masih belum lengkap. |
| Manajemen Stok | ✅ Cukup | Pastikan `/admin/tracking-reducing` sudah benar-benar digabung/dihapus, bukan cuma dibiarkan jadi dead route. |

---

### Anggota 7 — Evaluasi & Dampak (~55%)

| PBI | Status | Yang masih kurang |
|---|---|---|
| Dashboard Program | ✅ Cukup | Widget masih campuran dari modul lain (peta, produksi, tugas) — secara fungsi OK, tapi cek lagi apakah stat keahlian (`Bertani`/`Membersihkan` dll) sudah cocok dengan keyword di seeder. |
| **Laporan Dampak (PDF)** | ⚠️ Sebagian | "Cetak PDF" kemungkinan masih `window.print()`, bukan export PDF asli. **Form input `EnvironmentalTracking`** kemungkinan masih belum ada → data dampak lingkungan tetap kosong di laporan. |
| **Tren Produktivitas (BARU)** | ⚠️ Sebagian | Grafik kemungkinan masih dari status program, bukan dari jumlah pekerjaan selesai (logbook/jadwal) per periode — perlu cek apakah ini sudah diperbaiki di 11.C. |

**🎯 Gap paling jelas:** export PDF asli + form input dampak lingkungan — kalau dua ini tidak ada, "Laporan Dampak Program" sulit di-demo dengan baik.

---

## Daftar Kerja Tersisa (Urutan Saran)

Berdasarkan dampak ke nilai presentasi (PBI yang masih **0%** atau **paling kelihatan** kalau ditanya dosen/penilai didahulukan):

1. **Halaman Profil Pekerja** (Anggota 1) — satu-satunya PBI yang benar-benar 0%, paling mencolok kalau ditanya "mana fitur profil pekerjanya?"
2. **Foto before/after untuk Validasi Hasil Kerja** (Anggota 4) — PBI [BARU], gampang didemo ("upload foto sebelum, upload foto sesudah, baru bisa divalidasi").
3. **Form input Environmental Tracking + export PDF asli** (Anggota 7) — supaya "Laporan Dampak Program" bisa benar-benar didemo dengan data dan file PDF nyata.
4. **Tren Produktivitas dari data logbook**, bukan dari status program (Anggota 7) — kalau belum, ganti sumber data grafik.
5. **Permission granular per role** (Anggota 1) — kalau waktu cukup, beri pembatasan akses berbeda untuk supervisor vs relawan vs pengawas, bukan cuma "semua non-admin = pengawas".
6. Item-item "✅ Cukup tapi kurang" lainnya — sifatnya polish, kerjakan kalau masih ada waktu: notifikasi tugas lebih lengkap, auto-link distribusi ke keluarga, edit/hapus edukasi, integrasi lintas program di kalkulator upah.

---

## Checklist Pengerjaan (Sesi cursor2)

- [x] **1. Halaman Profil Pekerja** — Route `/admin/pekerja/{id}/profil` & `/pengawas/pekerja/{id}/profil`, view `admin/pekerja-profil.blade.php`, tombol Profil di tabel pekerja & profiling.
- [x] **2. Foto before/after Validasi Hasil Kerja** — Kolom `foto_sebelum` & `foto_sesudah` di logbooks, form operasional dua upload, validasi admin wajib kedua foto.
- [x] **3. Form Environmental Tracking + export PDF asli** — Form input di `/admin/analisis`, model `EnvironmentalTracking` diisi, unduh PDF via Dompdf (`/admin/analisis/pdf`).
- [x] **4. Tren Produktivitas dari logbook/jadwal** — `ProduktivitasController` pakai data `WorkSchedule` + `Logbook`, bukan status program.
- [x] **5. Permission granular per role** — Middleware `role.feature`: relawan (dashboard + pelaporan), supervisor (+ operasional, distribusi, profiling), pengawas (semua termasuk ekonomi).
- [x] **6. Polish opsional** — Notifikasi jadwal edit/hapus + stok masuk, `household_id` di distribusi, edit/hapus edukasi, kalkulator upah lintas program keluarga.

---

## Cara Lanjut ke Cursor

Contoh prompt untuk sesi berikutnya:

> "Baca `@cursor.md` dan `@cursor2.md`. Checklist cursor2 sudah selesai (#1–#6). Perbaiki bug demo atau polish tambahan jika diperlukan."

Setelah tiap item selesai, minta update checklist supaya progres tetap tercatat dan sesi berikutnya tidak mengulang kerja yang sama.