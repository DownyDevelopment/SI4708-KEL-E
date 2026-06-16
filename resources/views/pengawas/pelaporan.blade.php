@extends('layouts.app')
@section('title', 'Pelaporan Masalah Lapangan')

@section('content')
<div style="padding: 2rem;" x-data="pelaporanData()">
    <x-hero-banner title="Laporan & Ekonomi" description="Rekapitulasi penerimaan upah/insentif pekerja desa dan pelaporan masalah operasional lapangan.">
        <x-slot:actions>
            <button class="global-hero-banner-btn-white" @click="showAddForm = true" style="background: #ef4444; border-color: #ef4444; color: white;">
                <i data-lucide="plus" style="width: 18px; height: 18px; margin-right: 8px;"></i>
                <span>Lapor Masalah</span>
            </button>
        </x-slot:actions>
    </x-hero-banner>

    <div class="global-tabs">
        <a href="/pengawas/ekonomi" class="global-tab">
            <i data-lucide="dollar-sign" style="width: 16px; height: 16px;"></i>
            Insentif & Upah
        </a>
        <a href="/pengawas/pelaporan" class="global-tab active">
            <i data-lucide="alert-triangle" style="width: 16px; height: 16px;"></i>
            Pelaporan Masalah
        </a>
    </div>

    @if(session('success'))
        <div class="glass-panel" style="margin-bottom: 1rem; padding: 0.85rem 1.25rem; border-left: 4px solid var(--primary); color: var(--text-main);">
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

    <!-- Modal Tambah Laporan -->
    <template x-if="showAddForm">
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 99; display: flex; justify-content: center; align-items: center;">
            <div style="background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
                <h3 style="margin-bottom: 1.5rem; font-weight: 600; font-size: 1.25rem; display: flex; align-items: center; gap: 0.5rem; color: #ef4444;">
                    <i data-lucide="alert-triangle" style="width: 24px; height: 24px;"></i>
                    Formulir Pelaporan Masalah
                </h3>
                <form method="POST" action="/pengawas/pelaporan" style="display: flex; flex-direction: column; gap: 1rem;">
                    @csrf
                    <div style="display: flex; gap: 1rem;">
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem;">Tanggal Kejadian</label>
                            <input type="date" name="tanggal" class="search-input" style="width: 100%;" required :value="today" />
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem;">Waktu Kejadian</label>
                            <input type="time" name="waktu" class="search-input" style="width: 100%;" required :value="now" />
                        </div>
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem;">Deskripsi Masalah</label>
                        <textarea name="masalah" class="search-input" style="width: 100%; min-height: 100px; resize: vertical;" required placeholder="Ceritakan detail masalah yang terjadi..." x-model="formMasalah"></textarea>
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem;">Tingkatan Masalah</label>
                        <select name="tingkatan_masalah" class="search-input" style="width: 100%;">
                            <option value="low">Rendah (Low) - Masalah ringan</option>
                            <option value="mediate">Sedang (Mediate) - Cukup mengganggu</option>
                            <option value="high">Tinggi (High) - Kritis / berbahaya</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; display: flex; align-items: center; gap: 0.25rem;">
                            <i data-lucide="map-pin" style="width: 16px; height: 16px;"></i> Lokasi Kejadian
                        </label>
                        <input type="text" name="lokasi_masalah" class="search-input" style="width: 100%;" required placeholder="Cth: Lahan RT 03 / Area Tanam" />
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem;">Koordinat Geografis (Opsional)</label>
                        <input type="text" name="kordinat" class="search-input" style="width: 100%;" placeholder="Cth: -6.2146, 106.8451" />
                    </div>

                    <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem;">
                        <button type="button" class="btn btn-outline" @click="showAddForm = false">Batal</button>
                        <button type="submit" class="btn btn-primary" style="background: #ef4444; border-color: #ef4444;">Kirim Laporan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <div class="search-container" style="max-width: 400px; margin-bottom: 2rem;">
        <i data-lucide="search" class="search-icon" style="width: 18px; height: 18px;"></i>
        <input type="text" class="search-input" placeholder="Cari masalah atau lokasi..." x-model="searchTerm" />
    </div>

    <div style="background: white; border-radius: 12px; border: 1px solid #e1e4e8; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa; border-bottom: 1px solid #e1e4e8; text-align: left;">
                    <th style="padding: 1rem; font-size: 0.875rem; color: var(--text-muted);">Masalah</th>
                    <th style="padding: 1rem; font-size: 0.875rem; color: var(--text-muted);">Waktu & Lokasi</th>
                    <th style="padding: 1rem; font-size: 0.875rem; color: var(--text-muted);">Tingkatan</th>
                    <th style="padding: 1rem; font-size: 0.875rem; color: var(--text-muted);">Pelapor</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="report in filteredReports" :key="report.id">
                    <tr style="border-bottom: 1px solid #f1f2f4;">
                        <td style="padding: 1rem; max-width: 300px;">
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                                <div style="background: #fef2f2; padding: 0.5rem; border-radius: 8px; flex-shrink: 0;" :style="'color: ' + getSeverityColor(report.tingkatan_masalah)">
                                    <i data-lucide="alert-triangle" style="width: 20px; height: 20px;"></i>
                                </div>
                                <span style="font-weight: 500; color: var(--text-main); line-height: 1.4;" x-text="report.masalah"></span>
                            </div>
                        </td>
                        <td style="padding: 1rem;">
                            <div style="font-size: 0.875rem; color: var(--text-main); font-weight: 500;">
                                <span x-text="formatDate(report.tanggal)"></span> <span x-text="report.waktu.substring(0,5)"></span>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.25rem; margin-top: 0.25rem;">
                                <i data-lucide="map-pin" style="width: 14px; height: 14px;"></i> <span x-text="report.lokasi_masalah"></span>
                                <template x-if="report.kordinat">
                                    <a :href="'https://maps.google.com/?q=' + report.kordinat" target="_blank" rel="noreferrer" style="color: #3b82f6; margin-left: 0.5rem;" title="Lihat di Peta">
                                        <i data-lucide="external-link" style="width: 14px; height: 14px;"></i>
                                    </a>
                                </template>
                            </div>
                        </td>
                        <td style="padding: 1rem;">
                            <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;" 
                                  :style="'background: ' + getSeverityColor(report.tingkatan_masalah) + '15; color: ' + getSeverityColor(report.tingkatan_masalah) + '; border: 1px solid ' + getSeverityColor(report.tingkatan_masalah) + '40;'">
                                <span x-text="report.tingkatan_masalah"></span>
                            </span>
                        </td>
                        <td style="padding: 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                <div style="width: 24px; height: 24px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: bold;" x-text="report.nama_pengawas?.charAt(0) || '?'"></div>
                                <span style="font-size: 0.875rem; color: var(--text-main);" x-text="report.nama_pengawas || 'Unknown'"></span>
                            </div>
                        </td>
                    </tr>
                </template>
                <template x-if="filteredReports.length === 0">
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            <div style="margin: 0 auto 1rem; width: 48px; height: 48px; display: flex; justify-content: center; align-items: center; color: #d9d9d9;">
                                <i data-lucide="alert-triangle" style="width: 48px; height: 48px;"></i>
                            </div>
                            Belum ada laporan masalah
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('pelaporanData', () => ({
            reports: @json($reports),
            searchTerm: '',
            showAddForm: false,
            formMasalah: '',
            today: new Date().toISOString().slice(0, 10),
            now: new Date().toTimeString().slice(0, 5),

            get filteredReports() {
                if (this.searchTerm === '') {
                    return this.reports;
                }
                const term = this.searchTerm.toLowerCase();
                return this.reports.filter(r => 
                    r.masalah.toLowerCase().includes(term) ||
                    r.lokasi_masalah.toLowerCase().includes(term)
                );
            },

            init() {
                this.$watch('filteredReports', () => {
                    setTimeout(() => lucide.createIcons(), 50);
                });
                this.$watch('showAddForm', () => {
                    setTimeout(() => lucide.createIcons(), 50);
                });
                setTimeout(() => lucide.createIcons(), 50);
            },

            getSeverityColor(level) {
                if (level === 'high') return '#ef4444';
                if (level === 'mediate') return '#f59e0b';
                return '#10b981';
            },

            formatDate(dateStr) {
                return new Date(dateStr).toLocaleDateString('id-ID');
            }
        }));
    });
</script>
@endsection
