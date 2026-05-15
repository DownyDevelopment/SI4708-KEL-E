<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Worker;
use App\Models\MicroProgram;
use App\Models\WorkSchedule;
use App\Models\Logbook;
use App\Models\FieldProblem;

class MonitoringSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info("Memulai seeding data monitoring...");
        $today = Carbon::today()->toDateString();
        $waktuSekarang = Carbon::now()->format('H:i');

        // 1. Get Pengawas ID
        $pengawas = User::where('email', 'pengawas@village.com')->first();
        if (!$pengawas) {
            $this->command->error("Pengawas tidak ditemukan. Pastikan sudah ada user pengawas@village.com");
            return;
        }
        $pengawasId = $pengawas->id;

        // 1.5 Insert Workers (20 data)
        $names = [
            "Budi Santoso", "Siti Aminah", "Joko Susilo", "Andi Pratama", "Rina Melati", 
            "Agus Setiawan", "Dewi Lestari", "Hendra Wijaya", "Sari Indah", "Eko Prasetyo", 
            "Fitriani", "Dedi Saputra", "Yuniarti", "Rudi Haryanto", "Lina Herlina", 
            "Bambang Suryono", "Nita Permatasari", "Iwan Kusuma", "Maya Anggraini", "Reza Rahardian"
        ];

        $workersData = [];
        foreach ($names as $i => $name) {
            $workersData[] = [
                'nama' => $name,
                'tanggal_lahir' => "199" . rand(0, 9) . "-01-01",
                'jenis_kelamin' => $i % 2 === 0 ? 'Laki-laki' : 'Perempuan',
                'alamat' => "Jalan Mawar RT 0" . (($i % 5) + 1),
                'no_telepon' => "0812345678" . str_pad($i, 2, '0', STR_PAD_LEFT),
                'kemampuan_utama' => $i % 3 === 0 ? 'Pembersih' : 'Petani',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        Worker::insert($workersData);

        // 2. Insert Micro Programs
        $programs = [
            ['nama_program' => 'Pembersihan Saluran Air Desa', 'jenis_program' => 'Infrastruktur', 'deskripsi' => 'Pembersihan got utama', 'lokasi' => 'Desa Sukamaju RT 01', 'status' => 'active'],
            ['nama_program' => 'Pembuatan Kompos Organik', 'jenis_program' => 'Lingkungan', 'deskripsi' => 'Pengolahan sampah', 'lokasi' => 'Bank Sampah RW 03', 'status' => 'active'],
            ['nama_program' => 'Perbaikan Jalan Setapak', 'jenis_program' => 'Infrastruktur', 'deskripsi' => 'Pengecoran jalan', 'lokasi' => 'Jalan Mawar RT 02', 'status' => 'active']
        ];
        
        foreach ($programs as $progData) {
            $progData['created_at'] = now();
            $progData['updated_at'] = now();
            MicroProgram::create($progData);
        }

        $prog1Id = MicroProgram::where('nama_program', 'Pembersihan Saluran Air Desa')->first()->id;
        $prog2Id = MicroProgram::where('nama_program', 'Pembuatan Kompos Organik')->first()->id;
        $prog3Id = MicroProgram::where('nama_program', 'Perbaikan Jalan Setapak')->first()->id;

        // 3. Insert Work Schedules (All for TODAY)
        $schedules = [
            ['program_id' => $prog1Id, 'tanggal' => $today, 'jam_mulai' => '08:00', 'jam_selesai' => '12:00', 'shift_label' => 'Pagi', 'status' => 'in_progress'],
            ['program_id' => $prog2Id, 'tanggal' => $today, 'jam_mulai' => '13:00', 'jam_selesai' => '16:00', 'shift_label' => 'Siang', 'status' => 'scheduled'],
            ['program_id' => $prog3Id, 'tanggal' => $today, 'jam_mulai' => '09:00', 'jam_selesai' => '15:00', 'shift_label' => 'Penuh', 'status' => 'scheduled']
        ];

        foreach ($schedules as $schedData) {
            $schedData['created_at'] = now();
            $schedData['updated_at'] = now();
            WorkSchedule::create($schedData);
        }

        $sched1Id = WorkSchedule::where('program_id', $prog1Id)->first()->id;
        $sched2Id = WorkSchedule::where('program_id', $prog2Id)->first()->id;

        // 4. Insert Logbooks (Progress updates)
        $logbooks = [
            ['schedule_id' => $sched1Id, 'pengawas_id' => $pengawasId, 'progres_persentase' => 100, 'catatan' => 'Pembersihan selesai 100%', 'pekerja_terlibat' => '[]', 'lokasi_pekerjaan' => 'Desa Sukamaju RT 01'],
            ['schedule_id' => $sched2Id, 'pengawas_id' => $pengawasId, 'progres_persentase' => 50, 'catatan' => 'Sedang memilah sampah organik', 'pekerja_terlibat' => '[]', 'lokasi_pekerjaan' => 'Bank Sampah RW 03']
        ];

        foreach ($logbooks as $logData) {
            $logData['created_at'] = now();
            $logData['updated_at'] = now();
            Logbook::create($logData);
        }

        // 5. Insert Field Problems
        $problems = [
            ['pengawas_id' => $pengawasId, 'tanggal' => $today, 'waktu' => $waktuSekarang, 'masalah' => 'Ada tumpukan sampah yang menyumbat keras di got utama', 'tingkatan_masalah' => 'high', 'lokasi_masalah' => 'Desa Sukamaju RT 01'],
            ['pengawas_id' => $pengawasId, 'tanggal' => $today, 'waktu' => $waktuSekarang, 'masalah' => 'Kurang alat untuk memilah sampah basah', 'tingkatan_masalah' => 'mediate', 'lokasi_masalah' => 'Bank Sampah RW 03']
        ];

        foreach ($problems as $probData) {
            $probData['created_at'] = now();
            $probData['updated_at'] = now();
            FieldProblem::create($probData);
        }

        $this->command->info("Seeding data monitoring selesai! Silakan cek database/dashboard.");
    }
}
