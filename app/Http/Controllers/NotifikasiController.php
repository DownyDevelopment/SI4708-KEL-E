<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index()
    {
        $notifications = Notification::with('user')
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('admin.notifikasi', compact('notifications'));
    }

    public function markRead(int $id)
    {
        Notification::where('id', $id)->update(['is_read' => true]);

        return redirect()->back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }
}
