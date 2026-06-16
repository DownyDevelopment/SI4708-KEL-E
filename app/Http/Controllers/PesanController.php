<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class PesanController extends Controller
{
    public function index()
    {
        Message::where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::with(['sender', 'receiver'])
            ->orderByDesc('created_at')
            ->paginate(30);

        $users = User::orderBy('nama')->get(['id', 'nama', 'role']);

        return view('admin.pesan', compact('messages', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'pesan' => 'required|string|max:2000',
        ]);

        Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $validated['receiver_id'],
            'pesan' => $validated['pesan'],
        ]);

        return redirect()->back()->with('success', 'Pesan berhasil dikirim.');
    }
}
