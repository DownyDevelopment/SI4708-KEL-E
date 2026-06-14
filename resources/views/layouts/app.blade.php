<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work4Village - @yield('title', 'Dashboard')</title>
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/App.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="app-container" x-data="layoutData()" x-init="initData()">
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

        <nav class="sidebar-nav">
            @if(auth()->user()->role === 'admin')
                <x-sidebar-item icon="layout-dashboard" label="Dashboard Admin" to="/admin/dashboard" :active="request()->is('admin/dashboard')" />
                <x-sidebar-item icon="bar-chart-2" label="Dashboard Analisis" to="/admin/analisis" :active="request()->is('admin/analisis')" />
                <x-sidebar-item icon="users" label="Data Pekerja" to="/admin/pekerja" :active="request()->is('admin/pekerja')" />
                <x-sidebar-item icon="square-user" label="Keluarga Miskin" to="/admin/keluarga" :active="request()->is('admin/keluarga')" />
                <x-sidebar-item icon="map-pin" label="Perencanaan Program" to="/admin/perencanaan" :active="request()->is('admin/perencanaan') || request()->is('admin/program')" />
                <x-sidebar-item icon="pie-chart" label="Profiling" to="/admin/profiling" :active="request()->is('admin/profiling')" />
                <x-sidebar-item icon="dollar-sign" label="Ekonomi & Insentif" to="/admin/ekonomi" :active="request()->is('admin/ekonomi')" />
                <x-sidebar-item icon="book-open" label="Edukasi" to="/admin/edukasi" :active="request()->is('admin/edukasi')" />
                <x-sidebar-item icon="shield-check" label="Pengaturan Akses" to="/admin/roles" :active="request()->is('admin/roles')" />
                <x-sidebar-item icon="trending-up" label="Tren Produktivitas" to="/admin/produktivitas" :active="request()->is('admin/produktivitas')" />
                <x-sidebar-item icon="file-text" label="Tugas" to="/admin/tugas" :active="request()->is('admin/tugas')" />
                <x-sidebar-item icon="package" label="Inventaris" to="/admin/inventaris" :active="request()->is('admin/inventaris')" />
            @else
                <x-sidebar-item icon="layout-dashboard" label="Dashboard Pengawas" to="/pengawas/dashboard" :active="request()->is('pengawas/dashboard')" />
                <x-sidebar-item icon="pie-chart" label="Profiling Pekerja" to="/pengawas/profiling" :active="request()->is('pengawas/profiling') || request()->is('pengawas/pekerja/*/profil')" />
                <x-sidebar-item icon="calendar-clock" label="Operasional" to="/pengawas/operasional" :active="request()->is('pengawas/operasional') || request()->is('pengawas/jadwal') || request()->is('pengawas/logbook')" />
                <x-sidebar-item icon="send" label="Distribusi Hasil" to="/pengawas/distribusi" :active="request()->is('pengawas/distribusi')" />
                <x-sidebar-item icon="dollar-sign" label="Insentif & Upah" to="/pengawas/ekonomi" :active="request()->is('pengawas/ekonomi')" />
                <x-sidebar-item icon="alert-triangle" label="Pelaporan Masalah" to="/pengawas/pelaporan" :active="request()->is('pengawas/pelaporan')" />
            @endif
        </nav>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}" style="width: 100%;">
                @csrf
                <button type="submit" class="nav-item" style="width: 100%; background: transparent; border: none; cursor: pointer; color: var(--text-muted);" :style="sidebarOpen ? 'justify-content: flex-start; padding: 0.75rem 1rem; gap: 0.75rem;' : 'justify-content: center; padding: 0.75rem 0; gap: 0;'">
                    <i data-lucide="log-out" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
                    <span x-show="sidebarOpen">Keluar</span>
                </button>
            </form>
        </div>
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
                <!-- Notifications -->
                <div style="position: relative;" @click.outside="showNotif = false">
                    <button class="icon-btn" @click="showNotif = !showNotif; if(showNotif) fetchNotifications()">
                        <i data-lucide="bell" style="width: 20px; height: 20px;"></i>
                        <div x-show="unreadNotifs > 0" x-cloak class="badge-dot" style="position: absolute; top: 5px; right: 5px; width: 8px; height: 8px; background: red; border-radius: 50%;"></div>
                    </button>
                    <div x-show="showNotif" x-cloak class="topbar-dropdown" style="width: 300px;">
                        <div style="padding: 1rem; border-bottom: 1px solid #f1f5f9; font-weight: 600;">Notifikasi</div>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <template x-if="notifications.length === 0">
                                <div style="padding: 1rem; text-align: center; color: var(--text-muted); font-size: 0.85rem;">Tidak ada notifikasi</div>
                            </template>
                            <template x-for="n in notifications">
                                <div style="padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: start;" :style="n.is_read ? 'background: white;' : 'background: #f0fdf4;'">
                                    <div>
                                        <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-main);" x-text="n.judul"></div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);" x-text="n.pesan"></div>
                                    </div>
                                    <button x-show="!n.is_read" @click="markNotifRead(n.id)" style="background: none; border: none; color: var(--primary); cursor: pointer;"><i data-lucide="check" style="width: 16px; height: 16px;"></i></button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div style="position: relative;" @click.outside="showMsg = false">
                    <button class="icon-btn" @click="showMsg = !showMsg; if(showMsg) { fetchMessages(); fetchUsers(); }">
                        <i data-lucide="mail" style="width: 20px; height: 20px;"></i>
                    </button>
                    <div x-show="showMsg" x-cloak class="topbar-dropdown" style="width: 320px; display: flex; flex-direction: column;">
                        <div style="padding: 1rem; border-bottom: 1px solid #f1f5f9; font-weight: 600;">Pesan Internal</div>
                        <div style="max-height: 250px; overflow-y: auto; padding: 0.5rem;">
                            <template x-if="messages.length === 0">
                                <div style="padding: 1rem; text-align: center; color: var(--text-muted); font-size: 0.85rem;">Belum ada pesan</div>
                            </template>
                            <template x-for="m in messages">
                                <div style="margin-bottom: 0.5rem;" :style="m.sender_id === {{ auth()->id() }} ? 'text-align: right;' : 'text-align: left;'">
                                    <div style="display: inline-block; max-width: 85%; padding: 0.5rem 0.75rem; border-radius: 12px; font-size: 0.85rem;" :style="m.sender_id === {{ auth()->id() }} ? 'background: var(--primary); color: white;' : 'background: #f1f5f9; color: var(--text-main);'">
                                        <div style="font-size: 0.7rem; opacity: 0.8; margin-bottom: 2px;" x-text="m.sender_id === {{ auth()->id() }} ? 'Anda' : m.sender_name"></div>
                                        <span x-text="m.pesan"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <form @submit.prevent="sendMessage" style="padding: 0.75rem; border-top: 1px solid #f1f5f9; display: flex; flex-direction: column; gap: 0.5rem; background: #f8fafc;">
                            <select x-model="composeMsg.receiver_id" style="padding: 0.4rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.8rem;" required>
                                <option value="">Pilih Penerima...</option>
                                <template x-for="u in usersList">
                                    <option :value="u.id" x-text="u.nama + ' (' + u.role + ')'"></option>
                                </template>
                            </select>
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="text" placeholder="Tulis pesan..." x-model="composeMsg.pesan" required style="flex: 1; padding: 0.4rem 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.8rem;" />
                                <button type="submit" style="background: var(--primary); color: white; border: none; border-radius: 6px; width: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer;"><i data-lucide="send" style="width: 16px; height: 16px;"></i></button>
                            </div>
                        </form>
                    </div>
                </div>

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
                notifications: [],
                showNotif: false,
                messages: [],
                usersList: [],
                showMsg: false,
                composeMsg: { receiver_id: '', pesan: '' },

                get unreadNotifs() {
                    return this.notifications.filter(n => !n.is_read).length;
                },

                initData() {
                    // Fetch initial data if needed
                },

                async fetchNotifications() {
                    try {
                        const res = await fetch('/api/user/notifications');
                        this.notifications = await res.json();
                        setTimeout(() => lucide.createIcons(), 50);
                    } catch (e) { console.error(e); }
                },

                async fetchMessages() {
                    try {
                        const res = await fetch('/api/user/messages');
                        this.messages = await res.json();
                    } catch (e) { console.error(e); }
                },

                async fetchUsers() {
                    try {
                        const res = await fetch('/api/user/list');
                        this.usersList = await res.json();
                    } catch (e) { console.error(e); }
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
                },

                async markNotifRead(id) {
                    await fetch(`/api/user/notifications/${id}/read`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });
                    this.fetchNotifications();
                },

                async sendMessage() {
                    if (!this.composeMsg.receiver_id || !this.composeMsg.pesan) return;
                    try {
                        await fetch('/api/user/messages', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.composeMsg)
                        });
                        this.composeMsg = { receiver_id: '', pesan: '' };
                        this.fetchMessages();
                    } catch (err) { console.error(err); }
                }
            }
        }
    </script>
</body>
</html>
