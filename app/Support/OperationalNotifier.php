<?php

namespace App\Support;

use App\Models\Notification;
use App\Models\User;

class OperationalNotifier
{
    public static function notify(string $judul, string $pesan, string $linkUrl = '/pengawas/operasional'): void
    {
        $users = User::whereIn('role', ['pengawas', 'supervisor'])->get();

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'judul' => $judul,
                'pesan' => $pesan,
                'is_read' => false,
                'link_url' => $linkUrl,
            ]);
        }
    }
}
