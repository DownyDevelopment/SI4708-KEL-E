@extends('layouts.app')
@section('title', 'Operasional & Penjadwalan')

@section('content')
<div class="animate-fade-in" style="padding: 2rem;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.8rem; display: flex; align-items: center; gap: 0.5rem; color: var(--text-main);">
            <i data-lucide="calendar" style="width: 28px; height: 28px; color: var(--primary);"></i>
            Operasional & Penjadwalan
        </h1>
        <p style="color: var(--text-muted);">Daftar tugas dan jadwal kerja operasional.</p>
    </div>

    <div class="glass-panel" style="padding: 1.5rem;">
        <h3 style="margin-bottom: 1rem; color: var(--text-main);">Daftar Jadwal</h3>
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #e2e8f0; text-align: left;">
                        <th style="padding: 1rem;">ID</th>
                        <th style="padding: 1rem;">Hari</th>
                        <th style="padding: 1rem;">Tugas</th>
                        <th style="padding: 1rem;">Jam Mulai</th>
                        <th style="padding: 1rem;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwal as $item)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 1rem;">#{{ $item->id }}</td>
                            <td style="padding: 1rem; font-weight: 500;">{{ $item->hari }}</td>
                            <td style="padding: 1rem;">{{ $item->tugas }}</td>
                            <td style="padding: 1rem;">{{ \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') }}</td>
                            <td style="padding: 1rem;">
                                <span class="badge {{ $item->status === 'Selesai' ? 'badge-success' : ($item->status === 'Berjalan' ? 'badge-primary' : 'badge-warning') }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 1rem; text-align: center; color: var(--text-muted);">
                                Belum ada jadwal.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>
@endsection
