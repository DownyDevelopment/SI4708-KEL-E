@extends('layouts.app')
@section('title', 'Pesan Internal')

@section('content')
<div class="animate-fade-in" style="padding: 2rem;">
    <x-hero-banner title="Pesan Internal" description="Riwayat pesan antar admin dan pengawas dalam sistem." />

    @if(session('success'))
        <div class="glass-panel" style="margin-bottom: 1rem; padding: 0.85rem 1.25rem; border-left: 4px solid var(--primary);">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="glass-panel" style="margin-bottom: 1rem; padding: 0.85rem 1.25rem; border-left: 4px solid var(--danger); color: var(--danger);">
            @foreach($errors->all() as $error)
                <p style="margin:0;">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem;">
        <div class="glass-panel" style="padding: 1.5rem;">
            <h3 style="margin: 0 0 1rem;">Riwayat Pesan</h3>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pengirim</th>
                            <th>Penerima</th>
                            <th>Pesan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $msg)
                            <tr>
                                <td style="white-space: nowrap;">{{ $msg->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $msg->sender?->nama ?? '—' }}</td>
                                <td>{{ $msg->receiver?->nama ?? '—' }}</td>
                                <td style="font-size: 0.9rem;">{{ Str::limit($msg->pesan, 100) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">Belum ada pesan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($messages->hasPages())
                <div style="margin-top: 1.5rem;">
                    {{ $messages->links() }}
                </div>
            @endif
        </div>

        <div class="glass-panel" style="padding: 1.5rem; height: fit-content;">
            <h3 style="margin: 0 0 1rem;">Kirim Pesan Baru</h3>
            <form method="POST" action="{{ route('admin.pesan.store') }}" style="display: flex; flex-direction: column; gap: 1rem;">
                @csrf
                <div class="form-group">
                    <label class="form-label">Penerima</label>
                    <select name="receiver_id" class="form-input" required>
                        <option value="">— Pilih pengguna —</option>
                        @foreach($users as $user)
                            @if($user->id !== auth()->id())
                                <option value="{{ $user->id }}">{{ $user->nama }} ({{ $user->role }})</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Isi Pesan</label>
                    <textarea name="pesan" class="form-input" rows="4" required placeholder="Tulis pesan..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="send" style="width: 16px; height: 16px; margin-right: 6px;"></i> Kirim
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
