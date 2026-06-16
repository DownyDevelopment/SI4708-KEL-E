@extends('layouts.app')
@section('title', 'Kelompok Kerja')

@section('content')
<div class="animate-fade-in" style="padding: 2rem;" x-data="{ showForm: false, editId: null, form: { nama_kelompok: '', deskripsi: '', worker_ids: [] } }">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.8rem; display: flex; align-items: center; gap: 0.5rem; color: var(--text-main);">
                <i data-lucide="users" style="width: 28px; height: 28px; color: var(--primary);"></i>
                Kelompok Kerja
            </h1>
            <p style="color: var(--text-muted);">Kelola kelompok pekerja untuk penugasan dan evaluasi berbasis tim.</p>
        </div>
        <button type="button" class="btn btn-primary" @click="showForm = true; editId = null; form = { nama_kelompok: '', deskripsi: '', worker_ids: [] }">
            <i data-lucide="plus" style="width: 18px; height: 18px; margin-right: 6px;"></i> Tambah Kelompok
        </button>
    </div>

    @if(session('success'))
        <div class="glass-panel" style="margin-bottom: 1rem; padding: 0.85rem 1.25rem; border-left: 4px solid var(--primary);">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
        @forelse($groups as $group)
            <div class="glass-panel" style="padding: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                    <div>
                        <h3 style="margin: 0 0 0.35rem; color: var(--text-main);">{{ $group->nama_kelompok }}</h3>
                        <span class="badge badge-primary">{{ $group->workers_count }} anggota</span>
                    </div>
                    <div style="display: flex; gap: 0.35rem;">
                        <button type="button" class="btn btn-outline btn-sm"
                            @click="showForm = true; editId = {{ $group->id }}; form = { nama_kelompok: @json($group->nama_kelompok), deskripsi: @json($group->deskripsi ?? ''), worker_ids: @json($group->workers->pluck('id')->map(fn ($id) => (string) $id)->values()) }">
                            Edit
                        </button>
                        <form method="POST" action="{{ route('pengawas.groups.destroy', $group->id) }}" onsubmit="return confirm('Hapus kelompok ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline btn-sm" style="color: var(--danger);">Hapus</button>
                        </form>
                    </div>
                </div>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem;">{{ $group->deskripsi ?: '—' }}</p>
                <div style="font-size: 0.85rem;">
                    <strong style="color: var(--text-main);">Anggota:</strong>
                    @if($group->workers->isEmpty())
                        <span style="color: var(--text-muted);">Belum ada anggota</span>
                    @else
                        <ul style="margin: 0.5rem 0 0; padding-left: 1.1rem; color: var(--text-muted);">
                            @foreach($group->workers as $w)
                                <li>{{ $w->nama }} · {{ $w->kemampuan_utama }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @empty
            <div class="glass-panel" style="padding: 2rem; grid-column: 1 / -1; text-align: center; color: var(--text-muted);">
                Belum ada kelompok kerja. Buat kelompok pertama untuk penugasan tim.
            </div>
        @endforelse
    </div>

    <template x-teleport="body">
        <div x-show="showForm" x-cloak style="position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 1rem;">
            <div class="glass-panel" style="width: 100%; max-width: 520px; padding: 1.5rem;" @click.outside="showForm = false">
                <h3 style="margin: 0 0 1rem; color: var(--text-main);" x-text="editId ? 'Edit Kelompok' : 'Tambah Kelompok'"></h3>
                <form :method="editId ? 'POST' : 'POST'" :action="editId ? '/pengawas/groups/' + editId : '{{ route('pengawas.groups.store') }}'">
                    @csrf
                    <template x-if="editId">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <div class="form-group">
                        <label class="form-label">Nama Kelompok</label>
                        <input type="text" name="nama_kelompok" class="form-input" x-model="form.nama_kelompok" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-input" rows="2" x-model="form.deskripsi"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Anggota Kelompok</label>
                        <div style="max-height: 180px; overflow-y: auto; border: 1px solid var(--border); border-radius: 8px; padding: 0.75rem;">
                            @foreach($availableWorkers as $w)
                                <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.25rem 0; font-size: 0.9rem; cursor: pointer;">
                                    <input type="checkbox" name="worker_ids[]" value="{{ $w->id }}" :checked="form.worker_ids.includes('{{ $w->id }}')" @change="$event.target.checked ? form.worker_ids.push('{{ $w->id }}') : form.worker_ids = form.worker_ids.filter(id => id !== '{{ $w->id }}')">
                                    {{ $w->nama }} · {{ $w->kemampuan_utama }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
                        <button type="button" class="btn btn-outline" @click="showForm = false">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
@endsection
