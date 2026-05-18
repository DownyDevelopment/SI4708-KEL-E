<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/analisis', [\App\Http\Controllers\AnalisisController::class, 'index'])->name('admin.analisis');
    Route::get('/pekerja', [\App\Http\Controllers\WorkerController::class, 'index'])->name('admin.pekerja');
    Route::post('/pekerja', [\App\Http\Controllers\WorkerController::class, 'store']);
    Route::put('/pekerja/{id}', [\App\Http\Controllers\WorkerController::class, 'update']);
    Route::get('/keluarga', [\App\Http\Controllers\HouseholdController::class, 'index'])->name('admin.keluarga');
    Route::post('/keluarga', [\App\Http\Controllers\HouseholdController::class, 'store']);
    
    Route::get('/perencanaan', [\App\Http\Controllers\ProgramController::class, 'perencanaanIndex'])->name('admin.perencanaan');
    Route::get('/program', [\App\Http\Controllers\ProgramController::class, 'programIndex'])->name('admin.program');
    Route::post('/program', [\App\Http\Controllers\ProgramController::class, 'store']);
    Route::put('/program/{id}', [\App\Http\Controllers\ProgramController::class, 'update']);
    Route::delete('/program/{id}', [\App\Http\Controllers\ProgramController::class, 'destroy']);
    
    Route::get('/edukasi', [\App\Http\Controllers\EdukasiController::class, 'index'])->name('admin.edukasi');
    Route::post('/edukasi', [\App\Http\Controllers\EdukasiController::class, 'store']);
    
    Route::get('/inventaris', [\App\Http\Controllers\InventarisController::class, 'index'])->name('admin.inventaris');
    Route::post('/inventaris', [\App\Http\Controllers\InventarisController::class, 'store']);
    Route::post('/inventaris/{id}/adjust', [\App\Http\Controllers\InventarisController::class, 'adjust']);
    
    Route::get('/produktivitas', [\App\Http\Controllers\ProduktivitasController::class, 'index'])->name('admin.produktivitas');
    
    Route::get('/roles', [\App\Http\Controllers\UserController::class, 'index'])->name('admin.roles');
    Route::get('/pengawas', [\App\Http\Controllers\UserController::class, 'index'])->name('admin.pengawas'); // Point to same view for now
    Route::put('/roles/{id}', [\App\Http\Controllers\UserController::class, 'updateRole']);
    
    Route::get('/ekonomi', [\App\Http\Controllers\EkonomiController::class, 'index'])->name('admin.ekonomi');
    Route::post('/ekonomi/insentif', [\App\Http\Controllers\EkonomiController::class, 'storeInsentif']);
    Route::post('/ekonomi/reward', [\App\Http\Controllers\EkonomiController::class, 'storeReward']);
    Route::get('/ekonomi/detail/{workerId}', [\App\Http\Controllers\EkonomiController::class, 'detail']);
    
    Route::get('/tracking-reducing', [\App\Http\Controllers\InventarisController::class, 'trackingIndex'])->name('admin.tracking');
    
    Route::get('/tugas', [\App\Http\Controllers\JadwalController::class, 'index'])->name('admin.tugas');
    Route::get('/profiling', [\App\Http\Controllers\ProfilingController::class, 'index'])->name('admin.profiling');
    Route::post('/tugas', [\App\Http\Controllers\JadwalController::class, 'store']);
    Route::put('/tugas/{id}', [\App\Http\Controllers\JadwalController::class, 'update']);
    Route::delete('/tugas/{id}', [\App\Http\Controllers\JadwalController::class, 'destroy']);
});

Route::middleware(['auth', 'role:pengawas'])->prefix('pengawas')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'pengawasDashboard'])->name('pengawas.dashboard');
    Route::get('/operasional', [\App\Http\Controllers\JadwalController::class, 'pengawasIndex'])->name('pengawas.operasional');
    Route::get('/jadwal', fn () => redirect()->route('pengawas.operasional', ['tab' => 'jadwal']));
    Route::get('/logbook', [\App\Http\Controllers\LogbookController::class, 'index'])->name('pengawas.logbook');
    Route::post('/logbook', [\App\Http\Controllers\LogbookController::class, 'store']);
    Route::put('/logbook/{id}', [\App\Http\Controllers\LogbookController::class, 'update']);
    
    Route::get('/distribusi', [\App\Http\Controllers\DistribusiController::class, 'index'])->name('pengawas.distribusi');
    Route::post('/distribusi', [\App\Http\Controllers\DistribusiController::class, 'store']);
    
    Route::get('/ekonomi', [\App\Http\Controllers\EkonomiController::class, 'index'])->name('pengawas.ekonomi');
    Route::get('/ekonomi/detail/{workerId}', [\App\Http\Controllers\EkonomiController::class, 'detail']);
    
    Route::get('/pelaporan', [\App\Http\Controllers\PelaporanController::class, 'index'])->name('pengawas.pelaporan');
    Route::post('/pelaporan', [\App\Http\Controllers\PelaporanController::class, 'store']);

    Route::get('/profiling', [\App\Http\Controllers\ProfilingController::class, 'index'])->name('pengawas.profiling');
});

Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/user/notifications', function () {
        return response()->json(\App\Models\Notification::where('user_id', auth()->id())->get());
    });
    Route::put('/user/notifications/{id}/read', function ($id) {
        \App\Models\Notification::where('id', $id)->where('user_id', auth()->id())->update(['is_read' => true]);
        return response()->json(['status' => 'success']);
    });
    Route::get('/user/messages', function () {
        $msgs = \App\Models\Message::where('receiver_id', auth()->id())->orWhere('sender_id', auth()->id())->get();
        // Mock sender_name for simplicity in layout
        foreach($msgs as $m) {
            $sender = \App\Models\User::find($m->sender_id);
            $m->sender_name = $sender ? $sender->nama : 'Unknown';
        }
        return response()->json($msgs);
    });
    Route::post('/user/messages', function (\Illuminate\Http\Request $request) {
        \App\Models\Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'pesan' => $request->pesan,
        ]);
        return response()->json(['status' => 'success']);
    });
    Route::get('/user/list', function () {
        return response()->json(\App\Models\User::where('id', '!=', auth()->id())->select('id', 'nama', 'role')->get());
    });
    Route::get('/search', function (\Illuminate\Http\Request $request) {
        $q = $request->q;
        $results = [];
        return response()->json($results);
    });

    Route::post('/programs', function (\Illuminate\Http\Request $request) {
        $program = \App\Models\MicroProgram::create([
            'nama_program' => $request->nama_program,
            'jenis_program' => $request->jenis_program,
            'lokasi' => $request->lokasi,
            'kordinat' => $request->kordinat,
            'status' => $request->status,
        ]);
        return response()->json($program);
    });

    Route::get('/workers/{id}', [\App\Http\Controllers\WorkerController::class, 'show']);
    Route::get('/inventaris/{id}/history', [\App\Http\Controllers\InventarisController::class, 'history']);
});
