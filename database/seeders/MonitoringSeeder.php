<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Worker;
use App\Models\MicroProgram;
use App\Models\WorkSchedule;
use App\Models\WorkerGroup;
use App\Models\Logbook;
use App\Models\FieldProblem;
use App\Models\EnvironmentalTracking;

class MonitoringSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Memulai seeding data monitoring...');

        $today = Carbon::today();
        $todayStr = $today->toDateString();
        $waktuSekarang = Carbon::now()->format('H:i:s');

        $pengawas = User::where('email', 'pengawas@village.com')->first();
        if (!$pengawas) {
            $this->command->error('Pengawas tidak ditemukan. Pastikan sudah ada user pengawas@village.com');
            return;
        }
        $pengawasId = $pengawas->id;

        $workerIds = $this->seedWorkers();
        $groupMap = $this->seedWorkerGroups($workerIds);
        $this->backfillWorkerIncome();
        $programMap = $this->seedMicroPrograms();
        $scheduleMap = $this->seedWorkSchedules($programMap, $groupMap, $today);
        $this->backfillScheduleGroups($groupMap);
        $this->seedLogbooks($scheduleMap, $pengawasId, $todayStr);
        $this->backfillLogbookGroups();
        $this->seedFieldProblems($pengawasId, $today, $waktuSekarang);
        $this->seedEnvironmentalTracking($today);

        $this->command->info('Seeding data monitoring selesai! Silakan cek database/dashboard.');
    }

    private function seedWorkers(): array
    {
        if (Worker::count() > 0) {
            $this->command->warn('Data pekerja sudah ada, melewati insert pekerja.');
            return Worker::orderBy('id')->pluck('id')->all();
        }

        $names = [
            'Budi Santoso', 'Siti Aminah', 'Joko Susilo', 'Andi Pratama', 'Rina Melati',
            'Agus Setiawan', 'Dewi Lestari', 'Hendra Wijaya', 'Sari Indah', 'Eko Prasetyo',
            'Fitriani', 'Dedi Saputra', 'Yuniarti', 'Rudi Haryanto', 'Lina Herlina',
            'Bambang Suryono', 'Nita Permatasari', 'Iwan Kusuma', 'Maya Anggraini', 'Reza Rahardian',
        ];

        $bidangKerja = [
            'Pertanian', 'Pengelolaan Sampah', 'Kerajinan Tangan', 'Pertukangan',
            'Menjahit', 'Supir', 'Pertanian', 'Pengelolaan Sampah', 'Kerajinan Tangan',
            'Pertukangan', 'Menjahit', 'Pertanian', 'Pengelolaan Sampah', 'Kerajinan Tangan',
            'Supir', 'Pertukangan', 'Pertanian', 'Menjahit', 'Pengelolaan Sampah', 'Supir',
        ];
        $kesejahteraan = ['Sangat Miskin', 'Miskin', 'Rentan Miskin', 'Sejahtera', 'Pending'];
        $prioritas = ['tinggi', 'sedang', 'rendah'];
        $statusProgram = ['aktif', 'aktif', 'aktif', 'lulus', 'tidak_layak'];

        $workersData = [];
        foreach ($names as $i => $name) {
            $workersData[] = [
                'nama' => $name,
                'tanggal_lahir' => '199' . ($i % 10) . '-0' . (($i % 9) + 1) . '-15',
                'jenis_kelamin' => $i % 2 === 0 ? 'Laki-laki' : 'Perempuan',
                'alamat' => 'Jalan Mawar RT 0' . (($i % 5) + 1) . ' RW 0' . (($i % 3) + 1),
                'no_telepon' => '0812' . str_pad((30000000 + $i), 8, '0', STR_PAD_LEFT),
                'kemampuan_utama' => $bidangKerja[$i],
                'keahlian_kerja' => $bidangKerja[$i],
                'total_pendapatan' => rand(150000, 3500000),
                'skor_vulnerabilitas' => rand(20, 95),
                'total_skor' => rand(30, 90),
                'prioritas' => $prioritas[$i % 3],
                'status_program' => $statusProgram[$i % 5],
                'status_kesejahteraan' => $kesejahteraan[$i % 5],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Worker::insert($workersData);

        return Worker::orderBy('id')->pluck('id')->all();
    }

    private function seedWorkerGroups(array $workerIds): array
    {
        if (WorkerGroup::count() > 0) {
            return WorkerGroup::pluck('id', 'nama_kelompok')->all();
        }

        $groupDefs = [
            ['nama_kelompok' => 'Kelompok Tani Sukamaju', 'deskripsi' => 'Tim pertanian dan penghijauan desa'],
            ['nama_kelompok' => 'Kelompok Sampah Hijau', 'deskripsi' => 'Tim pengelolaan sampah dan kompos organik'],
            ['nama_kelompok' => 'Kelompok Kreatif Desa', 'deskripsi' => 'Tim kerajinan, pertukangan, dan bidang khusus'],
        ];

        $shuffled = collect($workerIds)->shuffle()->values();
        $chunkSize = (int) ceil(count($workerIds) / count($groupDefs));

        $map = [];
        foreach ($groupDefs as $index => $groupData) {
            $group = WorkerGroup::create([
                'nama_kelompok' => $groupData['nama_kelompok'],
                'deskripsi' => $groupData['deskripsi'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $memberIds = $shuffled->slice($index * $chunkSize, $chunkSize)->all();
            $group->workers()->sync($memberIds);
            $map[$groupData['nama_kelompok']] = $group->id;
        }

        return $map;
    }

    private function backfillWorkerIncome(): void
    {
        Worker::whereNull('total_pendapatan')
            ->orWhere('total_pendapatan', 0)
            ->each(function (Worker $worker) {
                $worker->update(['total_pendapatan' => rand(150000, 3500000)]);
            });
    }

    private function backfillScheduleGroups(array $groupMap): void
    {
        if (empty($groupMap)) {
            return;
        }

        $groupIds = array_values($groupMap);
        WorkSchedule::whereNull('worker_group_id')
            ->orderBy('id')
            ->each(function (WorkSchedule $schedule, int $index) use ($groupIds) {
                $schedule->update(['worker_group_id' => $groupIds[$index % count($groupIds)]]);
            });
    }

    private function backfillLogbookGroups(): void
    {
        Logbook::whereNull('worker_group_id')
            ->with('schedule')
            ->each(function (Logbook $logbook) {
                $groupId = $logbook->schedule?->worker_group_id;
                if ($groupId) {
                    $logbook->update(['worker_group_id' => $groupId]);
                }
            });
    }

    private function seedMicroPrograms(): array
    {
        if (MicroProgram::count() > 0) {
            return MicroProgram::pluck('id', 'nama_program')->all();
        }

        $programs = [
            ['nama_program' => 'Pembersihan Saluran Air Desa', 'jenis_program' => 'Infrastruktur', 'deskripsi' => 'Pembersihan got utama dan saluran drainase', 'lokasi' => 'Desa Sukamaju RT 01', 'desa_lokasi' => 'Sukamaju', 'kordinat' => '-6.914744,107.609810', 'status' => 'active'],
            ['nama_program' => 'Pembuatan Kompos Organik', 'jenis_program' => 'Lingkungan', 'deskripsi' => 'Pengolahan sampah organik menjadi kompos', 'lokasi' => 'Bank Sampah RW 03', 'desa_lokasi' => 'Sukamaju', 'kordinat' => '-6.918500,107.612500', 'status' => 'active'],
            ['nama_program' => 'Perbaikan Jalan Setapak', 'jenis_program' => 'Infrastruktur', 'deskripsi' => 'Pengecoran dan perataan jalan setapak warga', 'lokasi' => 'Jalan Mawar RT 02', 'desa_lokasi' => 'Mekarsari', 'kordinat' => '-6.911200,107.606800', 'status' => 'active'],
            ['nama_program' => 'Penanaman Pohon Penghijauan', 'jenis_program' => 'Lingkungan', 'deskripsi' => 'Penanaman 200 bibit pohon di area terbuka', 'lokasi' => 'Lapangan Desa RT 04', 'desa_lokasi' => 'Sukamaju', 'kordinat' => '-6.916000,107.611000', 'status' => 'active'],
            ['nama_program' => 'Pelatihan Kerajinan Daur Ulang', 'jenis_program' => 'Keterampilan', 'deskripsi' => 'Workshop membuat kerajinan dari barang bekas', 'lokasi' => 'Balai Desa', 'desa_lokasi' => 'Mekarsari', 'kordinat' => '-6.913000,107.608500', 'status' => 'completed'],
            ['nama_program' => 'Pemeliharaan Taman Komunal', 'jenis_program' => 'Infrastruktur', 'deskripsi' => 'Perawatan tanaman dan irigasi taman warga', 'lokasi' => 'Taman Harmoni RW 02', 'desa_lokasi' => 'Sukamaju', 'kordinat' => '-6.915500,107.610200', 'status' => 'planned'],
        ];

        foreach ($programs as $progData) {
            $progData['created_at'] = now();
            $progData['updated_at'] = now();
            MicroProgram::create($progData);
        }

        return MicroProgram::pluck('id', 'nama_program')->all();
    }

    private function seedWorkSchedules(array $programMap, array $groupMap, Carbon $today): array
    {
        if (WorkSchedule::count() > 0) {
            return WorkSchedule::with('program')
                ->get()
                ->mapWithKeys(fn ($s) => [$s->program?->nama_program . '|' . $s->tanggal => $s->id])
                ->all();
        }

        $groupTani = $groupMap['Kelompok Tani Sukamaju'] ?? null;
        $groupSampah = $groupMap['Kelompok Sampah Hijau'] ?? null;
        $groupKreatif = $groupMap['Kelompok Kreatif Desa'] ?? null;

        $schedules = [
            ['key' => 'saluran|today', 'program' => 'Pembersihan Saluran Air Desa', 'group' => $groupSampah, 'tanggal' => $today, 'jam_mulai' => '08:00', 'jam_selesai' => '12:00', 'shift_label' => 'Pagi', 'status' => 'in_progress'],
            ['key' => 'kompos|today', 'program' => 'Pembuatan Kompos Organik', 'group' => $groupSampah, 'tanggal' => $today, 'jam_mulai' => '13:00', 'jam_selesai' => '16:00', 'shift_label' => 'Siang', 'status' => 'scheduled'],
            ['key' => 'jalan|today', 'program' => 'Perbaikan Jalan Setapak', 'group' => $groupKreatif, 'tanggal' => $today, 'jam_mulai' => '09:00', 'jam_selesai' => '15:00', 'shift_label' => 'Penuh', 'status' => 'delayed'],
            ['key' => 'pohon|today', 'program' => 'Penanaman Pohon Penghijauan', 'group' => $groupTani, 'tanggal' => $today, 'jam_mulai' => '07:00', 'jam_selesai' => '11:00', 'shift_label' => 'Pagi', 'status' => 'completed'],
            ['key' => 'taman|today', 'program' => 'Pemeliharaan Taman Komunal', 'group' => $groupTani, 'tanggal' => $today, 'jam_mulai' => '14:00', 'jam_selesai' => '17:00', 'shift_label' => 'Sore', 'status' => 'scheduled'],
            ['key' => 'saluran|yesterday', 'program' => 'Pembersihan Saluran Air Desa', 'group' => $groupSampah, 'tanggal' => $today->copy()->subDay(), 'jam_mulai' => '08:00', 'jam_selesai' => '12:00', 'shift_label' => 'Pagi', 'status' => 'completed'],
            ['key' => 'kompos|2days', 'program' => 'Pembuatan Kompos Organik', 'group' => $groupSampah, 'tanggal' => $today->copy()->subDays(2), 'jam_mulai' => '13:00', 'jam_selesai' => '16:00', 'shift_label' => 'Siang', 'status' => 'completed'],
            ['key' => 'jalan|3days', 'program' => 'Perbaikan Jalan Setapak', 'group' => $groupKreatif, 'tanggal' => $today->copy()->subDays(3), 'jam_mulai' => '09:00', 'jam_selesai' => '15:00', 'shift_label' => 'Penuh', 'status' => 'delayed'],
            ['key' => 'pohon|4days', 'program' => 'Penanaman Pohon Penghijauan', 'group' => $groupTani, 'tanggal' => $today->copy()->subDays(4), 'jam_mulai' => '07:00', 'jam_selesai' => '11:00', 'shift_label' => 'Pagi', 'status' => 'in_progress'],
            ['key' => 'kerajinan|5days', 'program' => 'Pelatihan Kerajinan Daur Ulang', 'group' => $groupKreatif, 'tanggal' => $today->copy()->subDays(5), 'jam_mulai' => '10:00', 'jam_selesai' => '14:00', 'shift_label' => 'Siang', 'status' => 'completed'],
            ['key' => 'taman|6days', 'program' => 'Pemeliharaan Taman Komunal', 'group' => $groupTani, 'tanggal' => $today->copy()->subDays(6), 'jam_mulai' => '14:00', 'jam_selesai' => '17:00', 'shift_label' => 'Sore', 'status' => 'scheduled'],
        ];

        $map = [];
        foreach ($schedules as $entry) {
            $schedule = WorkSchedule::create([
                'program_id' => $programMap[$entry['program']],
                'worker_group_id' => $entry['group'],
                'tanggal' => $entry['tanggal']->toDateString(),
                'jam_mulai' => $entry['jam_mulai'],
                'jam_selesai' => $entry['jam_selesai'],
                'shift_label' => $entry['shift_label'],
                'status' => $entry['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $map[$entry['key']] = $schedule->id;
        }

        return $map;
    }

    private function seedLogbooks(array $scheduleMap, int $pengawasId, string $todayStr): void
    {
        if (Logbook::count() > 0) {
            return;
        }

        $schedulesByKey = collect($scheduleMap)->mapWithKeys(function ($id, $key) {
            return [$key => WorkSchedule::find($id)];
        });

        $logbooks = [
            // In Progress — belum ada validasi
            [
                'schedule_key' => 'saluran|today',
                'tanggal' => $todayStr,
                'progres_persentase' => 65,
                'status_validasi' => null,
                'catatan' => 'Pembersihan got utama sedang berjalan, 65% area sudah bersih',
                'lokasi_pekerjaan' => 'Desa Sukamaju RT 01',
                'detail_monitoring' => ['luas_area' => 150, 'berat_sampah' => 42.0],
            ],
            // Pending validasi (menunggu)
            [
                'schedule_key' => 'pohon|today',
                'tanggal' => $todayStr,
                'progres_persentase' => 100,
                'status_validasi' => 'menunggu',
                'catatan' => 'Penanaman 50 bibit pohon selesai 100%, menunggu validasi admin',
                'lokasi_pekerjaan' => 'Lapangan Desa RT 04',
                'detail_monitoring' => ['jumlah_bibit' => 50, 'luas_area' => 200],
            ],
            // Completed — disetujui
            [
                'schedule_key' => 'saluran|yesterday',
                'tanggal' => Carbon::parse($todayStr)->subDay()->toDateString(),
                'progres_persentase' => 100,
                'status_validasi' => 'disetujui',
                'catatan' => 'Pembersihan saluran kemarin selesai dan sudah divalidasi',
                'lokasi_pekerjaan' => 'Desa Sukamaju RT 01',
                'detail_monitoring' => ['luas_area' => 120, 'berat_sampah' => 55.5],
                'rating_kinerja' => 4,
            ],
            // In Progress — progres rendah
            [
                'schedule_key' => 'kompos|2days',
                'tanggal' => Carbon::parse($todayStr)->subDays(2)->toDateString(),
                'progres_persentase' => 40,
                'status_validasi' => null,
                'catatan' => 'Pemilahan sampah organik masih berlangsung',
                'lokasi_pekerjaan' => 'Bank Sampah RW 03',
                'detail_monitoring' => ['berat_sampah' => 18, 'luas_area' => 30],
            ],
            // Delayed — ditolak validasi
            [
                'schedule_key' => 'jalan|3days',
                'tanggal' => Carbon::parse($todayStr)->subDays(3)->toDateString(),
                'progres_persentase' => 100,
                'status_validasi' => 'ditolak',
                'catatan' => 'Pengecoran jalan belum merata, perlu perbaikan ulang',
                'lokasi_pekerjaan' => 'Jalan Mawar RT 02',
                'detail_monitoring' => ['panjang_jalan' => 45, 'volume_beton' => 2.5],
            ],
            // Completed
            [
                'schedule_key' => 'kerajinan|5days',
                'tanggal' => Carbon::parse($todayStr)->subDays(5)->toDateString(),
                'progres_persentase' => 100,
                'status_validasi' => 'disetujui',
                'catatan' => 'Pelatihan kerajinan daur ulang sukses, 12 peserta lulus',
                'lokasi_pekerjaan' => 'Balai Desa',
                'detail_monitoring' => ['jumlah_peserta' => 12, 'produk_jadi' => 24],
                'rating_kinerja' => 5,
            ],
            // In Progress — hampir selesai
            [
                'schedule_key' => 'pohon|4days',
                'tanggal' => Carbon::parse($todayStr)->subDays(4)->toDateString(),
                'progres_persentase' => 85,
                'status_validasi' => null,
                'catatan' => 'Penanaman pohon hampir selesai, tinggal area pinggir lapangan',
                'lokasi_pekerjaan' => 'Lapangan Desa RT 04',
                'detail_monitoring' => ['jumlah_bibit' => 170, 'luas_area' => 350],
            ],
            // Pending validasi
            [
                'schedule_key' => 'kompos|2days',
                'tanggal' => Carbon::parse($todayStr)->subDays(2)->toDateString(),
                'progres_persentase' => 100,
                'status_validasi' => 'menunggu',
                'catatan' => 'Kompos organik batch kedua siap panen, menunggu inspeksi',
                'lokasi_pekerjaan' => 'Bank Sampah RW 03',
                'detail_monitoring' => ['berat_sampah' => 35, 'luas_area' => 40],
            ],
        ];

        foreach ($logbooks as $logData) {
            $scheduleKey = $logData['schedule_key'];
            unset($logData['schedule_key']);

            if (!isset($scheduleMap[$scheduleKey])) {
                continue;
            }

            $logData['schedule_id'] = $scheduleMap[$scheduleKey];
            $logData['worker_group_id'] = $schedulesByKey[$scheduleKey]?->worker_group_id;
            $logData['pengawas_id'] = $pengawasId;
            $logData['pekerja_terlibat'] = '[]';
            $logData['created_at'] = now();
            $logData['updated_at'] = now();
            Logbook::create($logData);
        }
    }

    private function seedFieldProblems(int $pengawasId, Carbon $today, string $waktuSekarang): void
    {
        if (FieldProblem::count() > 0) {
            return;
        }

        $problems = [
            // High
            ['tanggal' => $today, 'waktu' => '08:15:00', 'masalah' => 'Hujan deras menghentikan pekerjaan pembersihan saluran, genangan air 30 cm', 'tingkatan_masalah' => 'high', 'lokasi_masalah' => 'Desa Sukamaju RT 01'],
            ['tanggal' => $today, 'waktu' => '09:30:00', 'masalah' => 'Cangkul dan sekop rusak total, tidak bisa melanjutkan penggalian', 'tingkatan_masalah' => 'high', 'lokasi_masalah' => 'Jalan Mawar RT 02'],
            ['tanggal' => $today, 'waktu' => '10:45:00', 'masalah' => 'Pekerja Andi Pratama sakit demam tinggi, tidak bisa hadir di lapangan', 'tingkatan_masalah' => 'high', 'lokasi_masalah' => 'Bank Sampah RW 03'],

            // Medium (mediate)
            ['tanggal' => $today, 'waktu' => $waktuSekarang, 'masalah' => 'Kurang alat untuk memilah sampah basah, antrian menumpuk', 'tingkatan_masalah' => 'mediate', 'lokasi_masalah' => 'Bank Sampah RW 03'],
            ['tanggal' => $today, 'waktu' => '11:20:00', 'masalah' => 'Jalan setapak licin setelah hujan, berbahaya bagi pekerja', 'tingkatan_masalah' => 'mediate', 'lokasi_masalah' => 'Jalan Mawar RT 02'],
            ['tanggal' => $today, 'waktu' => '13:00:00', 'masalah' => 'Truk pengangkut sampah terlambat 2 jam dari jadwal', 'tingkatan_masalah' => 'mediate', 'lokasi_masalah' => 'Desa Sukamaju RT 01'],
            ['tanggal' => $today->copy()->subDay(), 'waktu' => '14:30:00', 'masalah' => 'Stok bibit pohon hanya tersisa 10, kebutuhan 50 bibit', 'tingkatan_masalah' => 'mediate', 'lokasi_masalah' => 'Lapangan Desa RT 04'],

            // Low
            ['tanggal' => $today, 'waktu' => '07:50:00', 'masalah' => 'Cuaca berawan, visibilitas rendah di pagi hari', 'tingkatan_masalah' => 'low', 'lokasi_masalah' => 'Taman Harmoni RW 02'],
            ['tanggal' => $today, 'waktu' => '15:10:00', 'masalah' => 'Sarung tangan habis, pekerja menggunakan sarung cadangan', 'tingkatan_masalah' => 'low', 'lokasi_masalah' => 'Bank Sampah RW 03'],
            ['tanggal' => $today->copy()->subDays(2), 'waktu' => '16:00:00', 'masalah' => 'Kebisingan dari kendaraan lewat mengganggu konsentrasi pekerja', 'tingkatan_masalah' => 'low', 'lokasi_masalah' => 'Jalan Mawar RT 02'],
            ['tanggal' => $today->copy()->subDays(3), 'waktu' => '08:00:00', 'masalah' => 'Pemanas air kompos mati, proses pengomposan melambat', 'tingkatan_masalah' => 'low', 'lokasi_masalah' => 'Bank Sampah RW 03'],
        ];

        foreach ($problems as $probData) {
            $probData['pengawas_id'] = $pengawasId;
            $probData['tanggal'] = $probData['tanggal']->toDateString();
            $probData['created_at'] = now();
            $probData['updated_at'] = now();
            FieldProblem::create($probData);
        }
    }

    private function seedEnvironmentalTracking(Carbon $today): void
    {
        if (EnvironmentalTracking::count() > 0) {
            return;
        }

        $samples = [
            ['tanggal' => $today->toDateString(), 'jenis_limbah' => 'Organik', 'volume_kg' => 62.5, 'estimasi_emisi_berkurang_kg' => 18.2],
            ['tanggal' => $today->toDateString(), 'jenis_limbah' => 'Kompos', 'volume_kg' => 28.0, 'estimasi_emisi_berkurang_kg' => 9.5],
            ['tanggal' => $today->toDateString(), 'jenis_limbah' => 'Plastik Daur Ulang', 'volume_kg' => 15.3, 'estimasi_emisi_berkurang_kg' => 4.8],
            ['tanggal' => $today->copy()->subDay()->toDateString(), 'jenis_limbah' => 'Organik', 'volume_kg' => 48.0, 'estimasi_emisi_berkurang_kg' => 14.0],
            ['tanggal' => $today->copy()->subDays(2)->toDateString(), 'jenis_limbah' => 'Sampah Terpilah', 'volume_kg' => 35.5, 'estimasi_emisi_berkurang_kg' => 10.2],
            ['tanggal' => $today->copy()->subDays(3)->toDateString(), 'jenis_limbah' => 'Kompos', 'volume_kg' => 52.0, 'estimasi_emisi_berkurang_kg' => 16.8],
            ['tanggal' => $today->copy()->subDays(5)->toDateString(), 'jenis_limbah' => 'Organik', 'volume_kg' => 41.0, 'estimasi_emisi_berkurang_kg' => 11.5],
            ['tanggal' => $today->copy()->subDays(7)->toDateString(), 'jenis_limbah' => 'Sampah Terpilah', 'volume_kg' => 45.0, 'estimasi_emisi_berkurang_kg' => 12.0],
            ['tanggal' => $today->copy()->subDays(10)->toDateString(), 'jenis_limbah' => 'Kompos', 'volume_kg' => 38.0, 'estimasi_emisi_berkurang_kg' => 13.5],
            ['tanggal' => $today->copy()->subDays(14)->toDateString(), 'jenis_limbah' => 'Organik', 'volume_kg' => 55.0, 'estimasi_emisi_berkurang_kg' => 17.0],
        ];

        foreach ($samples as $envData) {
            $envData['created_at'] = now();
            $envData['updated_at'] = now();
            EnvironmentalTracking::create($envData);
        }
    }
}
