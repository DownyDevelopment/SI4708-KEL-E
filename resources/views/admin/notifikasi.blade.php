@extends('layouts.app')
@section('title', 'Notifikasi Sistem')

@section('content')
<div class="animate-fade-in" style="padding: 2rem;">
    <x-hero-banner title="Notifikasi Sistem" description="Daftar notifikasi operasional yang dikirim ke pengguna sistem." />

    @if(session('success'))
        <div class="glass-panel" style="margin-bottom: 1rem; padding: 0.85rem 1.25rem; border-left: 4px solid var(--primary);">
            {{ session('success') }}
        </div>
    @endif

    <div class="glass-panel" style="padding: 1.5rem;">
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Penerima</th>
                        <th>Judul</th>
                        <th>Pesan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notif)
                        <tr style="{{ $notif->is_read ? '' : 'background: rgba(16,185,129,0.05);' }}">
                            <td style="white-space: nowrap;">{{ $notif->created_at->format('d M Y H:i') }}</td>
                            <td>{{ $notif->user?->nama ?? '—' }}</td>
                            <td style="font-weight: 500;">{{ $notif->judul }}</td>
                            <td style="font-size: 0.85rem; color: var(--text-muted); max-width: 280px;">{{ Str::limit($notif->pesan, 80) }}</td>
                            <td>
                                @if($notif->is_read)
                                    <span class="badge badge-outline">Dibaca</span>
                                @else
                                    <span class="badge badge-primary">Baru</span>
                                @endif
                            </td>
                            <td>
                                @if(!$notif->is_read)
                                    <form method="POST" action="{{ route('admin.notifikasi.read', $notif->id) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-outline btn-sm">Tandai dibaca</button>
                                    </form>
                                @endif
                                @if($notif->link_url)
                                    <a href="{{ $notif->link_url }}" class="btn btn-outline btn-sm" style="text-decoration: none;">Buka</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">Belum ada notifikasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($notifications->hasPages())
            <div style="margin-top: 1.5rem;">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
