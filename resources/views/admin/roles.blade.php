@extends('layouts.app')
@section('title', 'Manajemen Akses & Role')

@section('content')
<div style="padding: 2rem;" class="animate-fade-in" x-data="userManagementData()">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.8rem; font-weight: bold; color: var(--text-main); margin-bottom: 0.5rem;">Manajemen Akses & Role</h1>
        <p style="color: var(--text-muted);">Atur hak akses untuk Admin dan Pengawas.</p>
    </div>

    @if(session('success'))
        <div style="padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;">
            <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
            <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="glass-panel" style="padding: 1.5rem;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Pengguna</th>
                    <th>Email</th>
                    <th>Role Saat Ini</th>
                    <th>Ubah Role</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="u in users" :key="u.id">
                    <tr>
                        <td># <span x-text="u.id"></span></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 32px; height: 32px; background: var(--background-alt); border-radius: 50%; display: flex; justify-content: center; align-items: center;">
                                    <i data-lucide="user" style="width: 16px; height: 16px; color: var(--primary);"></i>
                                </div>
                                <span x-text="u.nama"></span>
                            </div>
                        </td>
                        <td x-text="u.email"></td>
                        <td>
                            <span :class="'badge ' + (u.role === 'admin' ? 'badge-primary' : 'badge-outline')" x-text="u.role.toUpperCase()"></span>
                        </td>
                        <td>
                            <form method="POST" :action="'/admin/roles/' + u.id" style="display: inline-flex; gap: 0.5rem;">
                                @csrf
                                @method('PUT')
                                <select name="role" class="form-input" style="padding: 0.25rem 0.5rem; width: auto;" :value="u.role">
                                    <option value="admin" :selected="u.role === 'admin'">Admin</option>
                                    <option value="pengawas" :selected="u.role === 'pengawas'">Pengawas</option>
                                </select>
                                <button type="submit" class="btn btn-outline btn-sm">Simpan</button>
                            </form>
                        </td>
                    </tr>
                </template>
                <template x-if="users.length === 0">
                    <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">Memuat data pengguna...</td></tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('userManagementData', () => ({
            users: @json($users),

            init() {
                setTimeout(() => lucide.createIcons(), 50);
            }
        }));
    });
</script>
@endsection
