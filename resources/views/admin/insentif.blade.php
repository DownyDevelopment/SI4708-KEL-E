@extends('layouts.app')
@section('title', 'Daftar Insentif & Reward')

@section('content')
<div class="animate-fade-in" style="padding: 2rem;">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.8rem; display: flex; align-items: center; gap: 0.5rem; color: var(--text-main);">
            <i data-lucide="gift" style="width: 28px; height: 28px; color: var(--primary);"></i>
            Insentif & Reward Pekerja
        </h1>
        <p style="color: var(--text-muted);">Ringkasan seluruh pencatatan upah, insentif, dan penghargaan pekerja desa.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="glass-panel" style="padding: 1.25rem;">
            <div style="font-size: 0.85rem; color: var(--text-muted);">Total Upah / Insentif</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-top: 0.35rem;">
                Rp {{ number_format($totalUpah, 0, ',', '.') }}
            </div>
        </div>
        <div class="glass-panel" style="padding: 1.25rem;">
            <div style="font-size: 0.85rem; color: var(--text-muted);">Jumlah Entri Insentif</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin-top: 0.35rem;">{{ $insentifs->count() }}</div>
        </div>
        <div class="glass-panel" style="padding: 1.25rem;">
            <div style="font-size: 0.85rem; color: var(--text-muted);">Penghargaan (Reward)</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin-top: 0.35rem;">{{ $rewards->count() }}</div>
        </div>
    </div>

    <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
            <h3 style="margin: 0;">Daftar Insentif / Upah</h3>
            <a href="/admin/ekonomi" class="btn btn-outline btn-sm" style="text-decoration: none;">Catat Insentif Baru</a>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pekerja</th>
                        <th>Program</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($insentifs as $item)
                        <tr>
                            <td>{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d M Y') : '—' }}</td>
                            <td style="font-weight: 500;">{{ $item->worker?->nama ?? '—' }}</td>
                            <td>{{ $item->logbook?->schedule?->program?->nama_program ?? '—' }}</td>
                            <td><span class="badge badge-success">{{ $item->jenis_insentif }}</span></td>
                            <td>Rp {{ number_format($item->jumlah_upah, 0, ',', '.') }}</td>
                            <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $item->keterangan ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">Belum ada data insentif.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="glass-panel" style="padding: 1.5rem;">
        <h3 style="margin: 0 0 1rem;">Daftar Reward / Penghargaan</h3>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pekerja</th>
                        <th>Nama Penghargaan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rewards as $reward)
                        <tr>
                            <td>{{ $reward->tanggal_pemberian ? \Carbon\Carbon::parse($reward->tanggal_pemberian)->format('d M Y') : '—' }}</td>
                            <td style="font-weight: 500;">{{ $reward->worker?->nama ?? '—' }}</td>
                            <td>{{ $reward->nama_penghargaan }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align: center; color: var(--text-muted); padding: 2rem;">Belum ada data reward.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
