@extends('layouts.app')
@section('title', 'Pengaturan Sistem')

@section('content')
<div class="animate-fade-in" style="padding: 2rem;">
    <x-hero-banner title="Pengaturan Sistem" description="Atur variabel operasional untuk insentif, jadwal, dan validasi logbook." />

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

    <div class="glass-panel" style="padding: 2rem; max-width: 640px;">
        <form method="POST" action="{{ route('admin.settings.update') }}" style="display: flex; flex-direction: column; gap: 1.25rem;">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Batas Minimum Poin untuk Reward</label>
                <input type="number" name="min_poin_reward" class="form-input" min="0" required
                       value="{{ old('min_poin_reward', $settings->get('min_poin_reward')?->value ?? 100) }}">
                <small style="color: var(--text-muted);">Pekerja harus mengumpulkan minimal poin ini sebelum memenuhi syarat reward.</small>
            </div>

            <div class="form-group">
                <label class="form-label">Kuota Jadwal Harian per Program</label>
                <input type="number" name="kuota_jadwal_harian" class="form-input" min="1" max="100" required
                       value="{{ old('kuota_jadwal_harian', $settings->get('kuota_jadwal_harian')?->value ?? 10) }}">
                <small style="color: var(--text-muted);">Batas maksimum penjadwalan tugas per program dalam satu hari.</small>
            </div>

            <div class="form-group">
                <label class="form-label">Kuota Kerja Kelompok per Hari</label>
                <input type="number" name="kuota_kelompok_kerja" class="form-input" min="1" max="50" required
                       value="{{ old('kuota_kelompok_kerja', $settings->get('kuota_kelompok_kerja')?->value ?? 5) }}">
                <small style="color: var(--text-muted);">Batas maksimum penugasan per kelompok kerja dalam satu hari.</small>
            </div>

            <div class="form-group">
                <label class="form-label">Upah Default Validasi Logbook (Rp)</label>
                <input type="number" name="upah_default_logbook" class="form-input" min="0" step="1000" required
                       value="{{ old('upah_default_logbook', $settings->get('upah_default_logbook')?->value ?? 50000) }}">
                <small style="color: var(--text-muted);">Nilai awal saat admin menyetujui logbook dan mencairkan upah.</small>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save" style="width: 16px; height: 16px; margin-right: 6px;"></i> Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
