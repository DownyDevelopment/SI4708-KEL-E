<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work4Village - @yield('title', 'Dashboard')</title>
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/App.css') }}">
    @stack('styles')
    <script src="https://unpkg.com/lucide@latest"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="app-container" x-data="layoutData()" x-init="initData()">
    @php
        $unreadNotifs = auth()->check()
            ? \App\Models\Notification::where('user_id', auth()->id())->where('is_read', false)->count()
            : 0;
        $unreadMsgs = auth()->check()
            ? \App\Models\Message::where('receiver_id', auth()->id())->where('is_read', false)->count()
            : 0;
    @endphp
    <!-- Sidebar -->
    <aside class="sidebar" :style="sidebarOpen ? 'width: 260px; min-width: 260px; padding: 1.25rem;' : 'width: 68px; min-width: 68px; padding: 1rem 0.5rem;'" style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); overflow: hidden; flex-shrink: 0;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; overflow: hidden;">
                <div style="background: var(--primary); color: white; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem; flex-shrink: 0;">W</div>
                <span x-show="sidebarOpen" style="color: var(--text-main); white-space: nowrap; font-weight: 700; font-size: 1.1rem;">Work4Village</span>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" :title="sidebarOpen ? 'Tutup Sidebar' : 'Buka Sidebar'" style="background: var(--background); border: none; border-radius: 8px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; color: var(--text-muted); transition: background 0.2s;" @mouseenter="$el.style.background = 'var(--border)'" @mouseleave="$el.style.background = 'var(--background)'">
                <i data-lucide="chevron-right" :style="sidebarOpen ? 'transform: rotate(180deg)' : 'transform: rotate(0deg)'" style="width: 16px; height: 16px; transition: transform 0.3s;"></i>
            </button>
        </div>

        <nav class="sidebar-nav" style="display: flex; flex-direction: column; gap: 0.75rem;">
            @if(auth()->user()->role === 'admin')
                <!-- Group 1: Analisis & Dashboard -->
                <div x-data="{ open: {{ request()->is('admin/dashboard') || request()->is('admin/analisis') || request()->is('admin/produktivitas') ? 'true' : 'false' }} }" style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <button x-show="sidebarOpen" @click="open = !open" class="nav-item" style="justify-content: space-between; background: transparent; border: none; width: 100%; cursor: pointer; text-align: left; padding: 0.5rem 0.75rem;">
                        <span style="display: flex; align-items: center; gap: 0.65rem; font-weight: 700; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            <i data-lucide="layout" style="width: 16px; height: 16px; opacity: 0.7;"></i>
                            <span>Analisis & Kinerja</span>
                        </span>
                        <i data-lucide="chevron-down" :style="open ? 'transform: rotate(180deg); transition: transform 0.2s;' : 'transition: transform 0.2s;'" style="width: 14px; height: 14px; color: var(--text-muted);"></i>
                    </button>
                    <div x-show="!sidebarOpen || open" style="display: flex; flex-direction: column; gap: 0.25rem;" :style="sidebarOpen ? 'padding-left: 0.5rem;' : ''">
                        <x-sidebar-item icon="layout-dashboard" label="Dashboard Admin" to="/admin/dashboard" :active="request()->is('admin/dashboard')" />
                        <x-sidebar-item icon="bar-chart-2" label="Dashboard Analisis" to="/admin/analisis" :active="request()->is('admin/analisis')" />
                        <x-sidebar-item icon="trending-up" label="Tren Produktivitas" to="/admin/produktivitas" :active="request()->is('admin/produktivitas')" />
                    </div>
                </div>

                <!-- Group 2: Pekerja & Profiling -->
                <div x-data="{ open: {{ request()->is('admin/pekerja') || request()->is('admin/keluarga') || request()->is('admin/profiling') || request()->is('admin/pekerja/*/profil') ? 'true' : 'false' }} }" style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <button x-show="sidebarOpen" @click="open = !open" class="nav-item" style="justify-content: space-between; background: transparent; border: none; width: 100%; cursor: pointer; text-align: left; padding: 0.5rem 0.75rem;">
                        <span style="display: flex; align-items: center; gap: 0.65rem; font-weight: 700; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            <i data-lucide="users" style="width: 16px; height: 16px; opacity: 0.7;"></i>
                            <span>Manajemen Pekerja</span>
                        </span>
                        <i data-lucide="chevron-down" :style="open ? 'transform: rotate(180deg); transition: transform 0.2s;' : 'transition: transform 0.2s;'" style="width: 14px; height: 14px; color: var(--text-muted);"></i>
                    </button>
                    <div x-show="!sidebarOpen || open" style="display: flex; flex-direction: column; gap: 0.25rem;" :style="sidebarOpen ? 'padding-left: 0.5rem;' : ''">
                        <x-sidebar-item icon="users" label="Data Pekerja" to="/admin/pekerja" :active="request()->is('admin/pekerja')" />
                        <x-sidebar-item icon="square-user" label="Profiling Keluarga" to="/admin/keluarga" :active="request()->is('admin/keluarga')" />
                        <x-sidebar-item icon="pie-chart" label="Profiling" to="/admin/profiling" :active="request()->is('admin/profiling')" />
                    </div>
                </div>

                <!-- Group 3: Operasional & Aset -->
                <div x-data="{ open: {{ request()->is('admin/tugas') || request()->is('admin/perencanaan') || request()->is('admin/program') || request()->is('admin/inventaris') ? 'true' : 'false' }} }" style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <button x-show="sidebarOpen" @click="open = !open" class="nav-item" style="justify-content: space-between; background: transparent; border: none; width: 100%; cursor: pointer; text-align: left; padding: 0.5rem 0.75rem;">
                        <span style="display: flex; align-items: center; gap: 0.65rem; font-weight: 700; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            <i data-lucide="clipboard-list" style="width: 16px; height: 16px; opacity: 0.7;"></i>
                            <span>Operasional & Aset</span>
                        </span>
                        <i data-lucide="chevron-down" :style="open ? 'transform: rotate(180deg); transition: transform 0.2s;' : 'transition: transform 0.2s;'" style="width: 14px; height: 14px; color: var(--text-muted);"></i>
                    </button>
                    <div x-show="!sidebarOpen || open" style="display: flex; flex-direction: column; gap: 0.25rem;" :style="sidebarOpen ? 'padding-left: 0.5rem;' : ''">
                        <x-sidebar-item icon="file-text" label="Tugas" to="/admin/tugas" :active="request()->is('admin/tugas')" />
                        <x-sidebar-item icon="map-pin" label="Perencanaan Program" to="/admin/perencanaan" :active="request()->is('admin/perencanaan') || request()->is('admin/program')" />
                        <x-sidebar-item icon="package" label="Inventaris" to="/admin/inventaris" :active="request()->is('admin/inventaris')" />
                    </div>
                </div>

                <!-- Group 4: Keuangan & PADes -->
                <div x-data="{ open: {{ request()->is('admin/ekonomi') || request()->is('admin/insentif') || request()->is('admin/pades') ? 'true' : 'false' }} }" style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <button x-show="sidebarOpen" @click="open = !open" class="nav-item" style="justify-content: space-between; background: transparent; border: none; width: 100%; cursor: pointer; text-align: left; padding: 0.5rem 0.75rem;">
                        <span style="display: flex; align-items: center; gap: 0.65rem; font-weight: 700; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            <i data-lucide="wallet" style="width: 16px; height: 16px; opacity: 0.7;"></i>
                            <span>Keuangan Desa</span>
                        </span>
                        <i data-lucide="chevron-down" :style="open ? 'transform: rotate(180deg); transition: transform 0.2s;' : 'transition: transform 0.2s;'" style="width: 14px; height: 14px; color: var(--text-muted);"></i>
                    </button>
                    <div x-show="!sidebarOpen || open" style="display: flex; flex-direction: column; gap: 0.25rem;" :style="sidebarOpen ? 'padding-left: 0.5rem;' : ''">
                        <x-sidebar-item icon="dollar-sign" label="Ekonomi & Insentif" to="/admin/ekonomi" :active="request()->is('admin/ekonomi') || request()->is('admin/insentif')" />
                        <x-sidebar-item icon="wallet" label="Pencairan PADes" to="/admin/pades" :active="request()->is('admin/pades')" />
                    </div>
                </div>

                <!-- Group 5: Komunikasi & Edukasi -->
                <div x-data="{ open: {{ request()->is('admin/edukasi') || request()->is('admin/pesan') || request()->is('admin/notifikasi') ? 'true' : 'false' }} }" style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <button x-show="sidebarOpen" @click="open = !open" class="nav-item" style="justify-content: space-between; background: transparent; border: none; width: 100%; cursor: pointer; text-align: left; padding: 0.5rem 0.75rem;">
                        <span style="display: flex; align-items: center; gap: 0.65rem; font-weight: 700; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            <i data-lucide="message-square" style="width: 16px; height: 16px; opacity: 0.7;"></i>
                            <span>Komunikasi & Edukasi</span>
                        </span>
                        <i data-lucide="chevron-down" :style="open ? 'transform: rotate(180deg); transition: transform 0.2s;' : 'transition: transform 0.2s;'" style="width: 14px; height: 14px; color: var(--text-muted);"></i>
                    </button>
                    <div x-show="!sidebarOpen || open" style="display: flex; flex-direction: column; gap: 0.25rem;" :style="sidebarOpen ? 'padding-left: 0.5rem;' : ''">
                        <x-sidebar-item icon="book-open" label="Edukasi" to="/admin/edukasi" :active="request()->is('admin/edukasi')" />
                        <x-sidebar-item icon="mail" label="Pesan" to="/admin/pesan" :active="request()->is('admin/pesan')" />
                        <x-sidebar-item icon="bell" label="Notifikasi" to="/admin/notifikasi" :active="request()->is('admin/notifikasi')" />
                    </div>
                </div>

                <!-- Group 6: Konfigurasi -->
                <div x-data="{ open: {{ request()->is('admin/roles') || request()->is('admin/settings') ? 'true' : 'false' }} }" style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <button x-show="sidebarOpen" @click="open = !open" class="nav-item" style="justify-content: space-between; background: transparent; border: none; width: 100%; cursor: pointer; text-align: left; padding: 0.5rem 0.75rem;">
                        <span style="display: flex; align-items: center; gap: 0.65rem; font-weight: 700; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            <i data-lucide="settings" style="width: 16px; height: 16px; opacity: 0.7;"></i>
                            <span>Konfigurasi</span>
                        </span>
                        <i data-lucide="chevron-down" :style="open ? 'transform: rotate(180deg); transition: transform 0.2s;' : 'transition: transform 0.2s;'" style="width: 14px; height: 14px; color: var(--text-muted);"></i>
                    </button>
                    <div x-show="!sidebarOpen || open" style="display: flex; flex-direction: column; gap: 0.25rem;" :style="sidebarOpen ? 'padding-left: 0.5rem;' : ''">
                        <x-sidebar-item icon="shield-check" label="Pengaturan Akses" to="/admin/roles" :active="request()->is('admin/roles')" />
                        <x-sidebar-item icon="settings" label="Pengaturan Sistem" to="/admin/settings" :active="request()->is('admin/settings')" />
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" style="width: 100%; margin-top: 0.25rem;">
                    @csrf
                    <button type="submit" class="nav-item" style="width: 100%; background: transparent; border: none; cursor: pointer; color: var(--text-muted);" :style="sidebarOpen ? 'justify-content: flex-start; padding: 0.75rem 1rem; gap: 0.75rem;' : 'justify-content: center; padding: 0.75rem 0; gap: 0;'">
                        <i data-lucide="log-out" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
                        <span x-show="sidebarOpen">Keluar</span>
                    </button>
                </form>
            @else
                <!-- Pengawas Dashboard -->
                <x-sidebar-item icon="layout-dashboard" label="Dashboard Pengawas" to="/pengawas/dashboard" :active="request()->is('pengawas/dashboard')" />

                <!-- Group 1: Pengawas - Pekerja & Tim -->
                <div x-data="{ open: {{ request()->is('pengawas/groups') || request()->is('pengawas/profiling') || request()->is('pengawas/pekerja/*/profil') ? 'true' : 'false' }} }" style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <button x-show="sidebarOpen" @click="open = !open" class="nav-item" style="justify-content: space-between; background: transparent; border: none; width: 100%; cursor: pointer; text-align: left; padding: 0.5rem 0.75rem;">
                        <span style="display: flex; align-items: center; gap: 0.65rem; font-weight: 700; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            <i data-lucide="users" style="width: 16px; height: 16px; opacity: 0.7;"></i>
                            <span>Kelompok & Profiling</span>
                        </span>
                        <i data-lucide="chevron-down" :style="open ? 'transform: rotate(180deg); transition: transform 0.2s;' : 'transition: transform 0.2s;'" style="width: 14px; height: 14px; color: var(--text-muted);"></i>
                    </button>
                    <div x-show="!sidebarOpen || open" style="display: flex; flex-direction: column; gap: 0.25rem;" :style="sidebarOpen ? 'padding-left: 0.5rem;' : ''">
                        <x-sidebar-item icon="users" label="Kelompok Kerja" to="/pengawas/groups" :active="request()->is('pengawas/groups')" />
                        <x-sidebar-item icon="pie-chart" label="Profiling Pekerja" to="/pengawas/profiling" :active="request()->is('pengawas/profiling') || request()->is('pengawas/pekerja/*/profil')" />
                    </div>
                </div>

                <!-- Group 2: Pengawas - Aktivitas Lapangan -->
                <div x-data="{ open: {{ request()->is('pengawas/operasional') || request()->is('pengawas/jadwal') || request()->is('pengawas/logbook') || request()->is('pengawas/distribusi') ? 'true' : 'false' }} }" style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <button x-show="sidebarOpen" @click="open = !open" class="nav-item" style="justify-content: space-between; background: transparent; border: none; width: 100%; cursor: pointer; text-align: left; padding: 0.5rem 0.75rem;">
                        <span style="display: flex; align-items: center; gap: 0.65rem; font-weight: 700; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            <i data-lucide="calendar-clock" style="width: 16px; height: 16px; opacity: 0.7;"></i>
                            <span>Aktivitas Lapangan</span>
                        </span>
                        <i data-lucide="chevron-down" :style="open ? 'transform: rotate(180deg); transition: transform 0.2s;' : 'transition: transform 0.2s;'" style="width: 14px; height: 14px; color: var(--text-muted);"></i>
                    </button>
                    <div x-show="!sidebarOpen || open" style="display: flex; flex-direction: column; gap: 0.25rem;" :style="sidebarOpen ? 'padding-left: 0.5rem;' : ''">
                        <x-sidebar-item icon="calendar-clock" label="Operasional" to="/pengawas/operasional" :active="request()->is('pengawas/operasional') || request()->is('pengawas/jadwal') || request()->is('pengawas/logbook')" />
                        <x-sidebar-item icon="send" label="Distribusi Hasil" to="/pengawas/distribusi" :active="request()->is('pengawas/distribusi')" />
                    </div>
                </div>

                <!-- Group 3: Pengawas - Laporan & Ekonomi -->
                <div x-data="{ open: {{ request()->is('pengawas/ekonomi') || request()->is('pengawas/pelaporan') ? 'true' : 'false' }} }" style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <button x-show="sidebarOpen" @click="open = !open" class="nav-item" style="justify-content: space-between; background: transparent; border: none; width: 100%; cursor: pointer; text-align: left; padding: 0.5rem 0.75rem;">
                        <span style="display: flex; align-items: center; gap: 0.65rem; font-weight: 700; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            <i data-lucide="alert-triangle" style="width: 16px; height: 16px; opacity: 0.7;"></i>
                            <span>Laporan & Ekonomi</span>
                        </span>
                        <i data-lucide="chevron-down" :style="open ? 'transform: rotate(180deg); transition: transform 0.2s;' : 'transition: transform 0.2s;'" style="width: 14px; height: 14px; color: var(--text-muted);"></i>
                    </button>
                    <div x-show="!sidebarOpen || open" style="display: flex; flex-direction: column; gap: 0.25rem;" :style="sidebarOpen ? 'padding-left: 0.5rem;' : ''">
                        <x-sidebar-item icon="dollar-sign" label="Insentif & Upah" to="/pengawas/ekonomi" :active="request()->is('pengawas/ekonomi')" />
                        <x-sidebar-item icon="alert-triangle" label="Pelaporan Masalah" to="/pengawas/pelaporan" :active="request()->is('pengawas/pelaporan')" />
                    </div>
                </div>

                <!-- Pengawas Edukasi -->
                <x-sidebar-item icon="book-open" label="Edukasi" to="/pengawas/edukasi" :active="request()->is('pengawas/edukasi')" />

                <form method="POST" action="{{ route('logout') }}" style="width: 100%; margin-top: 0.25rem;">
                    @csrf
                    <button type="submit" class="nav-item" style="width: 100%; background: transparent; border: none; cursor: pointer; color: var(--text-muted);" :style="sidebarOpen ? 'justify-content: flex-start; padding: 0.75rem 1rem; gap: 0.75rem;' : 'justify-content: center; padding: 0.75rem 0; gap: 0;'">
                        <i data-lucide="log-out" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
                        <span x-show="sidebarOpen">Keluar</span>
                    </button>
                </form>
            @endif
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content" style="padding: 0; display: flex; flex-direction: column; flex: 1; min-width: 0;">
        <header class="topbar">
            <!-- Search -->
            <div class="search-container" style="position: relative;" @click.outside="showSearch = false">
                <i data-lucide="search" class="search-icon" style="width: 18px; height: 18px;"></i>
                <input type="text" class="search-input" placeholder="Cari navigasi, pekerja, atau program..." x-model="searchQuery" @input="handleSearch" @focus="if(searchQuery.length > 2) showSearch = true" />
                
                <div x-show="showSearch && searchResults.length > 0" x-cloak class="dropdown-panel" style="top: 110%; left: 0; width: 100%; max-height: 300px; overflow-y: auto;">
                    <template x-for="r in searchResults">
                        <a :href="r.link" style="padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9; cursor: pointer; display: flex; flex-direction: column; text-decoration: none;">
                            <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-main)" x-text="r.title"></span>
                            <span style="font-size: 0.75rem; color: var(--text-muted)" x-text="r.type + ' &bull; ' + r.desc"></span>
                        </a>
                    </template>
                </div>
                <div x-show="showSearch && searchResults.length === 0" x-cloak class="dropdown-panel" style="top: 110%; left: 0; width: 100%; padding: 1rem; text-align: center; color: var(--text-muted);">
                    Tidak ada hasil ditemukan.
                </div>
            </div>

            <div class="topbar-actions">
                @if(auth()->user()->role === 'admin')
                <a href="/admin/notifikasi" class="icon-btn" style="position: relative; text-decoration: none;" title="Notifikasi">
                    <i data-lucide="bell" style="width: 20px; height: 20px;"></i>
                    <span x-show="unreadNotifs > 0" x-cloak style="position: absolute; top: 4px; right: 4px; min-width: 8px; height: 8px; background: #ef4444; border-radius: 50%; border: 2px solid white;"></span>
                </a>
                <a href="/admin/pesan" class="icon-btn" style="position: relative; text-decoration: none;" title="Pesan">
                    <i data-lucide="mail" style="width: 20px; height: 20px;"></i>
                    <span x-show="unreadMsgs > 0" x-cloak style="position: absolute; top: 4px; right: 4px; min-width: 8px; height: 8px; background: #ef4444; border-radius: 50%; border: 2px solid white;"></span>
                </a>
                @endif

                <!-- User Profile -->
                <div class="user-profile">
                    <div class="user-info">
                        <div class="user-name">{{ auth()->user()->nama }}</div>
                        <div class="user-role">@switch(auth()->user()->role)
                            @case('admin') Administrator @break
                            @case('pengawas') Pengawas @break
                            @default {{ auth()->user()->role }}
                        @endswitch</div>
                    </div>
                    <div class="avatar" style="display: flex; align-items: center; justify-content: center; background: #e0e7ff; color: #4f46e5;">
                        <i data-lucide="user-circle" style="width: 28px; height: 28px;"></i>
                    </div>
                </div>
            </div>
        </header>

        <div style="flex: 1; overflow-y: auto;">
            @yield('content')
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });

        // Whenever Alpine renders new elements, we need to recall lucide.createIcons()
        document.addEventListener('alpine:initialized', () => {
            Alpine.effect(() => {
                setTimeout(() => {
                    lucide.createIcons();
                }, 50);
            });
        });

        function layoutData() {
            return {
                sidebarOpen: true,
                searchQuery: '',
                searchResults: [],
                showSearch: false,
                searchTimer: null,
                unreadNotifs: {{ $unreadNotifs }},
                unreadMsgs: {{ $unreadMsgs }},

                initData() {
                    this.refreshUnreadCounts();
                },

                async refreshUnreadCounts() {
                    try {
                        const [notifRes, msgRes] = await Promise.all([
                            fetch('/api/user/notifications/unread-count'),
                            fetch('/api/user/messages/unread-count'),
                        ]);
                        const notifData = await notifRes.json();
                        const msgData = await msgRes.json();
                        this.unreadNotifs = notifData.count ?? 0;
                        this.unreadMsgs = msgData.count ?? 0;
                    } catch (e) { /* ignore */ }
                },

                async handleSearch() {
                    if (this.searchQuery.length > 2) {
                        this.showSearch = true;
                        clearTimeout(this.searchTimer);
                        this.searchTimer = setTimeout(async () => {
                            try {
                                const res = await fetch(`/api/search?q=${encodeURIComponent(this.searchQuery)}`);
                                this.searchResults = await res.json();
                            } catch (err) {
                                this.searchResults = [];
                            }
                        }, 300);
                    } else {
                        this.showSearch = false;
                        this.searchResults = [];
                    }
                }
            }
        }
    </script>
</body>
</html>
