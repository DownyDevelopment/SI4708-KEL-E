@extends(isset($isIncluded) && $isIncluded ? 'layouts.empty' : 'layouts.app')
@section('title', 'Kelompok Kerja')

@php
    $totalGroups = $groups->count();
    $totalMembers = $groups->sum('workers_count');
    $unassignedWorkers = $availableWorkers->filter(fn ($w) => $w->workerGroups->isEmpty())->count();
    $accentColors = ['#0f766e', '#3b82f6', '#8b5cf6', '#f59e0b', '#ec4899', '#06b6d4'];
@endphp

@push('styles')
<style>
    .groups-page {
        padding: 2rem;
        max-width: 1400px;
    }

    .groups-hero {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1.5rem;
        margin-bottom: 1.75rem;
        flex-wrap: wrap;
    }

    .groups-hero-text h1 {
        font-size: 1.85rem;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 0.35rem;
    }

    .groups-hero-text p {
        color: var(--text-muted);
        font-size: 0.95rem;
        max-width: 520px;
    }

    .groups-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    @media (max-width: 768px) {
        .groups-stats {
            grid-template-columns: 1fr;
        }
    }

    .groups-stat {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.85rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .groups-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    }

    .groups-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .groups-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-main);
        line-height: 1.1;
    }

    .groups-stat-label {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-top: 0.1rem;
    }

    .groups-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .groups-search {
        position: relative;
        flex: 1;
        min-width: 220px;
        max-width: 360px;
    }

    .groups-search i {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        color: var(--text-muted);
        pointer-events: none;
    }

    .groups-search input {
        width: 100%;
        padding: 0.65rem 1rem 0.65rem 2.5rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        background: var(--surface);
        font-size: 0.88rem;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .groups-search input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12);
    }

    .groups-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 1.25rem;
    }

    .group-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .group-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.08);
    }

    .group-card-header {
        padding: 1.25rem 1.25rem 1rem;
        position: relative;
        overflow: hidden;
    }

    .group-card-header::before {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0.08;
        background: linear-gradient(135deg, var(--card-accent) 0%, transparent 70%);
    }

    .group-card-header-inner {
        position: relative;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .group-card-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-bottom: 0.75rem;
    }

    .group-card-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-main);
        margin: 0 0 0.35rem;
        line-height: 1.3;
    }

    .group-card-meta {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .group-member-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem 0.65rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 600;
        background: rgba(255, 255, 255, 0.85);
        border: 1px solid rgba(0, 0, 0, 0.06);
    }

    .group-card-actions {
        display: flex;
        gap: 0.25rem;
        flex-shrink: 0;
    }

    .group-action-btn {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: var(--text-muted);
        transition: all 0.15s ease;
    }

    .group-action-btn:hover {
        background: var(--background);
        color: var(--text-main);
        border-color: var(--text-muted);
    }

    .group-action-btn--danger:hover {
        background: rgba(239, 68, 68, 0.08);
        border-color: var(--danger);
        color: var(--danger);
    }

    .group-card-body {
        padding: 0 1.25rem 1.25rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .group-card-desc {
        font-size: 0.85rem;
        color: var(--text-muted);
        line-height: 1.55;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .group-skills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-bottom: 1rem;
    }

    .group-skill-chip {
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        font-size: 0.68rem;
        font-weight: 600;
        background: var(--background);
        color: var(--text-muted);
        border: 1px solid var(--border);
    }

    .group-members-section {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
    }

    .group-members-label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-muted);
        margin-bottom: 0.65rem;
    }

    .group-avatar-row {
        display: flex;
        align-items: center;
    }

    .group-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        font-weight: 700;
        color: white;
        border: 2px solid var(--surface);
        margin-left: -8px;
        flex-shrink: 0;
    }

    .group-avatar:first-child {
        margin-left: 0;
    }

    .group-avatar-more {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.62rem;
        font-weight: 700;
        background: var(--background);
        color: var(--text-muted);
        border: 2px solid var(--surface);
        margin-left: -8px;
    }

    .group-member-list {
        margin-top: 0.75rem;
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        max-height: 140px;
        overflow-y: auto;
        padding-right: 0.25rem;
    }

    .group-member-list::-webkit-scrollbar {
        width: 4px;
    }

    .group-member-list::-webkit-scrollbar-thumb {
        background: var(--border);
        border-radius: 4px;
    }

    .group-member-item {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.4rem 0.5rem;
        border-radius: 8px;
        background: var(--background);
    }

    .group-member-item-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.6rem;
        font-weight: 700;
        color: white;
        flex-shrink: 0;
    }

    .group-member-item-info {
        min-width: 0;
        flex: 1;
    }

    .group-member-item-name {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-main);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .group-member-item-skill {
        font-size: 0.68rem;
        color: var(--text-muted);
    }

    .groups-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 4rem 2rem;
        background: var(--surface);
        border: 2px dashed var(--border);
        border-radius: var(--radius-md);
    }

    .groups-empty-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1.25rem;
        background: var(--primary-light);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
    }

    .groups-empty h3 {
        font-size: 1.15rem;
        margin-bottom: 0.5rem;
    }

    .groups-empty p {
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
    }

    .groups-alert {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.85rem 1.15rem;
        margin-bottom: 1.25rem;
        background: rgba(15, 118, 110, 0.08);
        border: 1px solid rgba(15, 118, 110, 0.2);
        border-radius: var(--radius-sm);
        color: var(--primary);
        font-size: 0.88rem;
        font-weight: 500;
    }

    /* Modal */
    .groups-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(4px);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .groups-modal {
        width: 100%;
        max-width: 640px;
        max-height: 90vh;
        overflow-y: auto;
        background: var(--surface);
        border-radius: var(--radius-md);
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.18);
        border: 1px solid var(--border);
    }

    .groups-modal-header {
        padding: 1.5rem 1.5rem 0;
    }

    .groups-modal-header h3 {
        font-size: 1.15rem;
        margin-bottom: 0.25rem;
    }

    .groups-modal-header p {
        font-size: 0.85rem;
    }

    .groups-modal-body {
        padding: 1.25rem 1.5rem 1.5rem;
    }

    .groups-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        padding-top: 1.25rem;
        border-top: 1px solid var(--border);
        margin-top: 0.5rem;
    }

    /* Worker picker (modal) */
    .worker-picker-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
        align-items: center;
    }

    .worker-picker-filters {
        display: flex;
        gap: 0.35rem;
        flex-wrap: wrap;
    }

    .worker-picker-filter {
        padding: 0.35rem 0.75rem;
        font-size: 0.78rem;
        border-radius: 999px;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .worker-picker-filter.is-active {
        background: var(--primary-light);
        border-color: var(--primary);
        color: var(--primary);
        font-weight: 600;
    }

    .worker-picker-list {
        max-height: 280px;
        overflow-y: auto;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 0.35rem;
        background: var(--background);
    }

    .worker-picker-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.65rem 0.75rem;
        margin-bottom: 0.2rem;
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: background 0.15s ease;
        border: 1px solid transparent;
    }

    .worker-picker-row:last-child {
        margin-bottom: 0;
    }

    .worker-picker-row:hover {
        background: var(--primary-light);
    }

    .worker-picker-row--has-group {
        border-left: 3px solid var(--secondary);
        background: var(--surface);
    }

    .worker-picker-row--has-group:hover {
        background: var(--primary-light);
    }

    .worker-picker-main {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        min-width: 0;
        flex: 1;
    }

    .worker-picker-checkbox {
        width: 1rem;
        height: 1rem;
        margin-top: 0.2rem;
        flex-shrink: 0;
        accent-color: var(--primary);
        cursor: pointer;
    }

    .worker-picker-name {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-main);
        line-height: 1.35;
    }

    .worker-picker-skill {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-top: 0.1rem;
    }

    .worker-picker-meta {
        flex-shrink: 0;
        text-align: right;
        max-width: 11rem;
    }

    .worker-picker-hint {
        display: block;
        font-size: 0.68rem;
        color: var(--text-muted);
        margin-top: 0.3rem;
        line-height: 1.3;
    }

    .worker-picker-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        margin-top: 0.65rem;
        padding-top: 0.65rem;
        border-top: 1px solid var(--border);
        font-size: 0.82rem;
        color: var(--text-muted);
    }

    .worker-picker-label-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .worker-picker-count {
        font-size: 0.78rem;
        color: var(--text-muted);
        font-weight: 400;
    }

    .groups-no-results {
        grid-column: 1 / -1;
        text-align: center;
        padding: 2.5rem;
        color: var(--text-muted);
        font-size: 0.9rem;
    }
</style>
@endpush

@section('content')
<div class="animate-fade-in groups-page" x-data="{
    showForm: false,
    editId: null,
    form: { nama_kelompok: '', deskripsi: '', worker_ids: [] },
    workerSearch: '',
    workerFilter: 'all',
    groupSearch: '',
    noGroupResults: false,
    init() {
        window.addEventListener('open-add-group-form', () => this.openForm());
    },
    checkGroupResults() {
        this.$nextTick(() => {
            const cards = this.$el.querySelectorAll('.group-card');
            const anyVisible = [...cards].some(c => c.offsetParent !== null);
            this.noGroupResults = this.groupSearch.trim().length > 0 && !anyVisible;
        });
    },
    openForm(editId = null, formData = null) {
        this.editId = editId;
        this.form = formData ?? { nama_kelompok: '', deskripsi: '', worker_ids: [] };
        this.workerSearch = '';
        this.workerFilter = 'all';
        this.showForm = true;
    },
    workerRowVisible(el) {
        const q = this.workerSearch.trim().toLowerCase();
        const searchOk = !q || (el.dataset.search || '').includes(q);
        const filterOk = this.workerFilter === 'all'
            || (this.workerFilter === 'belum' && el.dataset.hasGroup === '0');
        return searchOk && filterOk;
    },
    groupVisible(name, desc, members) {
        const q = this.groupSearch.trim().toLowerCase();
        if (!q) return true;
        return (name + ' ' + desc + ' ' + members).toLowerCase().includes(q);
    }
}">
    @if(!(isset($isIncluded) && $isIncluded))
    <x-hero-banner title="Kelompok & Profiling" description="Pembagian tim kerja lapangan bagi para pekerja prasejahtera, serta monitoring tingkat kesejahteraan mereka.">
        <x-slot:actions>
            <button type="button" class="global-hero-banner-btn-white" @click="openForm()">
                <i data-lucide="plus" style="width: 18px; height: 18px;"></i>
                Tambah Kelompok
            </button>
        </x-slot:actions>
    </x-hero-banner>

    <div class="global-tabs">
        <a href="/pengawas/groups" class="global-tab active">
            <i data-lucide="users" style="width: 16px; height: 16px;"></i>
            Kelompok Kerja
        </a>
        <a href="/pengawas/profiling" class="global-tab">
            <i data-lucide="pie-chart" style="width: 16px; height: 16px;"></i>
            Profiling Pekerja
        </a>
    </div>
    @endif

    {{-- Stats --}}
    <div class="groups-stats">
        <div class="groups-stat">
            <div class="groups-stat-icon" style="background: rgba(15, 118, 110, 0.12); color: var(--primary);">
                <i data-lucide="layers" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
                <div class="groups-stat-value">{{ $totalGroups }}</div>
                <div class="groups-stat-label">Total Kelompok</div>
            </div>
        </div>
        <div class="groups-stat">
            <div class="groups-stat-icon" style="background: rgba(59, 130, 246, 0.12); color: var(--secondary);">
                <i data-lucide="user-check" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
                <div class="groups-stat-value">{{ $totalMembers }}</div>
                <div class="groups-stat-label">Anggota Terdaftar</div>
            </div>
        </div>
        <div class="groups-stat">
            <div class="groups-stat-icon" style="background: rgba(245, 158, 11, 0.12); color: var(--warning);">
                <i data-lucide="user-plus" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
                <div class="groups-stat-value">{{ $unassignedWorkers }}</div>
                <div class="groups-stat-label">Belum Berkelompok</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="groups-alert">
            <i data-lucide="check-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Toolbar --}}
    @if($groups->isNotEmpty())
        <div class="groups-toolbar">
            <div class="groups-search">
                <i data-lucide="search"></i>
                <input type="search" placeholder="Cari kelompok atau anggota..." x-model="groupSearch" @input="checkGroupResults()">
            </div>
            <span style="font-size: 0.82rem; color: var(--text-muted);">{{ $totalGroups }} kelompok</span>
        </div>
    @endif

    {{-- Cards --}}
    <div class="groups-grid">
        @forelse($groups as $index => $group)
            @php
                $accent = $accentColors[$index % count($accentColors)];
                $memberSearchText = $group->workers->map(fn ($w) => $w->nama . ' ' . $w->kemampuan_utama)->join(' ');
                $skills = $group->workers->pluck('kemampuan_utama')->unique()->filter()->take(4);
            @endphp
            <article
                class="group-card"
                style="--card-accent: {{ $accent }};"
                x-show="groupVisible(@json($group->nama_kelompok), @json($group->deskripsi ?? ''), @json($memberSearchText))"
            >
                <div class="group-card-header" style="border-bottom: 3px solid {{ $accent }};">
                    <div class="group-card-icon" style="background: {{ $accent }}20; color: {{ $accent }};">
                        <i data-lucide="users" style="width: 20px; height: 20px;"></i>
                    </div>
                    <div class="group-card-header-inner">
                        <div style="min-width: 0; flex: 1;">
                            <h3 class="group-card-title">{{ $group->nama_kelompok }}</h3>
                            <div class="group-card-meta">
                                <span class="group-member-badge" style="color: {{ $accent }};">
                                    <i data-lucide="user" style="width: 12px; height: 12px;"></i>
                                    {{ $group->workers_count }} anggota
                                </span>
                            </div>
                        </div>
                        <div class="group-card-actions">
                            <button type="button" class="group-action-btn" title="Edit kelompok"
                                @click="openForm({{ $group->id }}, { nama_kelompok: @json($group->nama_kelompok), deskripsi: @json($group->deskripsi ?? ''), worker_ids: @json($group->workers->pluck('id')->map(fn ($id) => (string) $id)->values()) })">
                                <i data-lucide="pencil" style="width: 15px; height: 15px;"></i>
                            </button>
                            <form method="POST" action="{{ route('pengawas.groups.destroy', $group->id) }}" onsubmit="return confirm('Hapus kelompok &quot;{{ $group->nama_kelompok }}&quot;?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="group-action-btn group-action-btn--danger" title="Hapus kelompok">
                                    <i data-lucide="trash-2" style="width: 15px; height: 15px;"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="group-card-body">
                    <p class="group-card-desc">{{ $group->deskripsi ?: 'Kelompok kerja lapangan tanpa deskripsi.' }}</p>

                    @if($skills->isNotEmpty())
                        <div class="group-skills">
                            @foreach($skills as $skill)
                                <span class="group-skill-chip">{{ $skill }}</span>
                            @endforeach
                            @if($group->workers->pluck('kemampuan_utama')->unique()->filter()->count() > 4)
                                <span class="group-skill-chip">+{{ $group->workers->pluck('kemampuan_utama')->unique()->filter()->count() - 4 }}</span>
                            @endif
                        </div>
                    @endif

                    <div class="group-members-section">
                        <div class="group-members-label">Anggota Kelompok</div>

                        @if($group->workers->isEmpty())
                            <span style="font-size: 0.82rem; color: var(--text-muted); font-style: italic;">Belum ada anggota — tambahkan melalui edit.</span>
                        @else
                            <div class="group-avatar-row">
                                @foreach($group->workers->take(5) as $wi => $w)
                                    @php
                                        $initials = collect(explode(' ', $w->nama))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->join('');
                                    @endphp
                                    <div class="group-avatar" style="background: {{ $accentColors[($wi + $index) % count($accentColors)] }};" title="{{ $w->nama }}">
                                        {{ strtoupper($initials) }}
                                    </div>
                                @endforeach
                                @if($group->workers->count() > 5)
                                    <div class="group-avatar-more">+{{ $group->workers->count() - 5 }}</div>
                                @endif
                            </div>

                            <div class="group-member-list">
                                @foreach($group->workers as $wi => $w)
                                    @php
                                        $initials = collect(explode(' ', $w->nama))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->join('');
                                    @endphp
                                    <div class="group-member-item">
                                        <div class="group-member-item-avatar" style="background: {{ $accentColors[($wi + $index) % count($accentColors)] }};">
                                            {{ strtoupper($initials) }}
                                        </div>
                                        <div class="group-member-item-info">
                                            <div class="group-member-item-name">{{ $w->nama }}</div>
                                            <div class="group-member-item-skill">{{ $w->kemampuan_utama }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="groups-empty">
                <div class="groups-empty-icon">
                    <i data-lucide="users-round" style="width: 32px; height: 32px;"></i>
                </div>
                <h3>Belum Ada Kelompok Kerja</h3>
                <p>Buat kelompok pertama untuk mengorganisir pekerja dalam penugasan dan evaluasi tim.</p>
                <button type="button" class="btn btn-primary" @click="openForm()">
                    <i data-lucide="plus" style="width: 18px; height: 18px;"></i>
                    Buat Kelompok Pertama
                </button>
            </div>
        @endforelse

        <div class="groups-no-results" x-show="noGroupResults" x-cloak>
            <i data-lucide="search-x" style="width: 32px; height: 32px; color: var(--text-muted); margin-bottom: 0.75rem;"></i>
            <p style="margin: 0;">Tidak ada kelompok yang cocok dengan pencarian.</p>
        </div>
    </div>

    {{-- Modal --}}
    <template x-teleport="body">
        <div x-show="showForm" x-cloak class="groups-modal-backdrop" @keydown.escape.window="showForm = false">
            <div class="groups-modal" @click.outside="showForm = false">
                <div class="groups-modal-header">
                    <h3 x-text="editId ? 'Edit Kelompok' : 'Tambah Kelompok Baru'"></h3>
                    <p>Pilih pekerja untuk kelompok ini. Pekerja yang sudah berkelompok tetap bisa ditambahkan ke kelompok lain.</p>
                </div>
                <div class="groups-modal-body">
                    <form :method="editId ? 'POST' : 'POST'" :action="editId ? '/pengawas/groups/' + editId : '{{ route('pengawas.groups.store') }}'">
                        @csrf
                        <template x-if="editId">
                            <input type="hidden" name="_method" value="PUT">
                        </template>
                        <div class="form-group">
                            <label class="form-label">Nama Kelompok</label>
                            <input type="text" name="nama_kelompok" class="form-input" x-model="form.nama_kelompok" placeholder="Contoh: Kelompok Tani Sukamaju" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-input" rows="2" x-model="form.deskripsi" placeholder="Tujuan dan fokus kerja kelompok..."></textarea>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="worker-picker-label-row">
                                <label class="form-label" style="margin-bottom: 0;">Anggota Kelompok</label>
                                <span class="worker-picker-count">{{ $availableWorkers->count() }} pekerja · <span x-text="form.worker_ids.length + ' dipilih'"></span></span>
                            </div>

                            <div class="worker-picker-toolbar">
                                <input
                                    type="search"
                                    class="form-input"
                                    placeholder="Cari nama atau keahlian..."
                                    x-model="workerSearch"
                                    style="flex: 1; min-width: 180px; margin-bottom: 0; padding-top: 0.55rem; padding-bottom: 0.55rem;"
                                >
                                <div class="worker-picker-filters">
                                    <button type="button" class="worker-picker-filter" :class="{ 'is-active': workerFilter === 'all' }" @click="workerFilter = 'all'">Semua</button>
                                    <button type="button" class="worker-picker-filter" :class="{ 'is-active': workerFilter === 'belum' }" @click="workerFilter = 'belum'">Belum berkelompok</button>
                                </div>
                            </div>

                            <div class="worker-picker-list">
                                @foreach($availableWorkers as $w)
                                    @php
                                        $hasGroup = $w->workerGroups->isNotEmpty();
                                        $groupLabelFull = $w->workerGroups->pluck('nama_kelompok')->join(', ');
                                        $groupLabelShort = $w->workerGroups->pluck('nama_kelompok')->map(function ($name) {
                                            return str_starts_with($name, 'Kelompok ') ? substr($name, 8) : $name;
                                        })->join(', ');
                                        $searchText = strtolower($w->nama . ' ' . $w->kemampuan_utama . ' ' . $groupLabelFull);
                                    @endphp
                                    <label
                                        class="worker-picker-row{{ $hasGroup ? ' worker-picker-row--has-group' : '' }}"
                                        data-search="{{ $searchText }}"
                                        data-has-group="{{ $hasGroup ? '1' : '0' }}"
                                        x-show="workerRowVisible($el)"
                                    >
                                        <div class="worker-picker-main">
                                            <input
                                                type="checkbox"
                                                name="worker_ids[]"
                                                value="{{ $w->id }}"
                                                class="worker-picker-checkbox"
                                                :checked="form.worker_ids.includes('{{ $w->id }}')"
                                                @change="$event.target.checked ? form.worker_ids.push('{{ $w->id }}') : form.worker_ids = form.worker_ids.filter(id => id !== '{{ $w->id }}')"
                                                @click.stop
                                            >
                                            <div class="worker-picker-info">
                                                <div class="worker-picker-name">{{ $w->nama }}</div>
                                                <div class="worker-picker-skill">{{ $w->kemampuan_utama }}</div>
                                            </div>
                                        </div>
                                        <div class="worker-picker-meta">
                                            @if($hasGroup)
                                                <span class="badge badge-primary" title="{{ $groupLabelFull }}">{{ $groupLabelShort }}</span>
                                                <span class="worker-picker-hint">Sudah di kelompok lain</span>
                                            @else
                                                <span class="badge badge-success">Belum Berkelompok</span>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <div class="worker-picker-footer" x-show="form.worker_ids.length > 0" x-cloak>
                                <span x-text="form.worker_ids.length + ' pekerja akan masuk kelompok ini'"></span>
                                <button type="button" class="btn btn-outline btn-sm" @click="form.worker_ids = []">Kosongkan pilihan</button>
                            </div>
                        </div>
                        <div class="groups-modal-footer">
                            <button type="button" class="btn btn-outline" @click="showForm = false">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
