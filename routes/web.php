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
});

Route::middleware(['auth', 'role:pengawas'])->prefix('pengawas')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'pengawasDashboard'])->name('pengawas.dashboard');
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
});
