@extends('layouts.app')
@section('title', 'Ekonomi & Insentif')

@section('content')
<div class="animate-fade-in" style="padding: 2rem;" x-data="ekonomiData()">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.8rem; display: flex; align-items: center; gap: 0.5rem; color: var(--text-main);">
            <i data-lucide="coins" style="width: 28px; height: 28px; color: var(--primary);"></i>
            Ekonomi & Insentif
        </h1>
        <p style="color: var(--text-muted);">Catat upah / voucher, penghargaan, dan pantau akumulasi pendapatan bulanan pekerja.</p>
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

    <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 1.5rem;">
        <div class="form-group">
            <label class="form-label">Pilih pekerja</label>
            <select class="form-input" x-model="workerId" @change="loadWorkerData">
                <option value="">— Pilih —</option>
                @foreach($workers as $w)
                    <option value="{{ $w->id }}">#{{ $w->id }} — {{ $w->nama }}</option>
                @endforeach
            </select>
        </div>
        <template x-if="selectedWorker">
            <p style="font-size: 0.9rem; color: var(--text-muted); margin-top: 0.5rem;">
                Keahlian: <span class="badge badge-success" x-text="selectedWorker.kemampuan_utama || 'Umum'"></span>
            </p>
        </template>
    </div>

    <template x-if="workerId">
        <div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                
                <!-- Akumulasi Upah -->
                <div class="glass-panel" style="padding: 1.5rem;">
                    <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="trending-up" style="width: 20px; height: 20px;"></i> Akumulasi upah (bulanan)
                    </h3>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem;">
                        <div class="form-group" style="flex: 1; min-width: 100px;">
                            <label class="form-label">Bulan</label>
                            <select class="form-input" x-model="bulan" @change="loadWorkerData">
                                @for($i=1; $i<=12; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group" style="flex: 1; min-width: 120px;">
                            <label class="form-label">Tahun</label>
                            <input type="number" class="form-input" x-model="tahun" @change="loadWorkerData" min="2000" max="2100" />
                        </div>
                        <div style="align-self: flex-end;">
                            <button type="button" class="btn btn-outline" @click="loadWorkerData" :disabled="loadingDetail">
                                <i data-lucide="refresh-cw" style="width: 16px; height: 16px; margin-right: 6px;"></i> Muat ulang
                            </button>
                        </div>
                    </div>

                    <template x-if="loadingDetail">
                        <p style="color: var(--text-muted);">Menghitung…</p>
                    </template>
                    <template x-if="!loadingDetail && akumulasi">
                        <div>
                            <div style="font-size: 1.75rem; font-weight: 700; color: var(--primary);" x-text="fmtIdr(akumulasi.total_upah)"></div>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">
                                Periode <span x-text="akumulasi.periode.label"></span> · <span x-text="akumulasi.jumlah_entri"></span> entri
                            </p>
                            <template x-if="akumulasi.per_jenis && akumulasi.per_jenis.length > 0">
                                <ul style="margin-top: 1rem; padding-left: 1.1rem; font-size: 0.9rem;">
                                    <template x-for="p in akumulasi.per_jenis" :key="p.jenis_insentif">
                                        <li><span x-text="p.jenis_insentif"></span>: <strong x-text="fmtIdr(p.subtotal)"></strong></li>
                                    </template>
                                </ul>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Form Insentif -->
                <div class="glass-panel" style="padding: 1.5rem;">
                    <h3 style="margin-bottom: 1rem;">Catat insentif / upah</h3>
                    <form method="POST" action="/admin/ekonomi/insentif" style="display: flex; flex-direction: column; gap: 1rem;">
                        @csrf
                        <input type="hidden" name="worker_id" :value="workerId">
                        <div class="form-group">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-input" required :value="today" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jenis</label>
                            <select name="jenis_insentif" class="form-input">
                                <option>Upah Harian</option>
                                <option>Voucher Pangan</option>
                                <option>Insentif Langsung</option>
                                <option>Lainnya (isi keterangan)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jumlah (rupiah / nilai setara)</label>
                            <input type="number" name="jumlah_upah" class="form-input" required min="0" step="1000" placeholder="50000" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-input" rows="2" placeholder="Program / tugas / catatan validasi"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan insentif</button>
                    </form>
                </div>

                <!-- Form Penghargaan -->
                <div class="glass-panel" style="padding: 1.5rem;">
                    <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="award" style="width: 20px; height: 20px;"></i> Penghargaan
                    </h3>
                    <form method="POST" action="/admin/ekonomi/reward" style="display: flex; flex-direction: column; gap: 1rem;">
                        @csrf
                        <input type="hidden" name="worker_id" :value="workerId">
                        <div class="form-group">
                            <label class="form-label">Nama penghargaan</label>
                            <input type="text" name="nama_penghargaan" class="form-input" required placeholder="Pekerja teladan, sertifikat..." />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal pemberian</label>
                            <input type="date" name="tanggal_pemberian" class="form-input" required :value="today" />
                        </div>
                        <button type="submit" class="btn btn-outline">Simpan penghargaan</button>
                    </form>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="glass-panel" style="padding: 1.5rem;">
                    <h3 style="margin-bottom: 1rem;">Riwayat insentif</h3>
                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="riwayat.length === 0">
                                    <tr><td colspan="3" style="text-align: center; color: var(--text-muted);">Belum ada data.</td></tr>
                                </template>
                                <template x-for="r in riwayat" :key="r.id">
                                    <tr>
                                        <td x-text="r.tanggal"></td>
                                        <td><span class="badge badge-success" x-text="r.jenis_insentif"></span></td>
                                        <td style="font-weight: 600;" x-text="fmtIdr(r.jumlah_upah)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="glass-panel" style="padding: 1.5rem;">
                    <h3 style="margin-bottom: 1rem;">Riwayat penghargaan</h3>
                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="rewards.length === 0">
                                    <tr><td colspan="2" style="text-align: center; color: var(--text-muted);">Belum ada data.</td></tr>
                                </template>
                                <template x-for="r in rewards" :key="r.id">
                                    <tr>
                                        <td x-text="r.tanggal_pemberian"></td>
                                        <td x-text="r.nama_penghargaan"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <style>
        @media (max-width: 900px) {
            .ekonomi-grid-two { grid-template-columns: 1fr !important; }
        }
    </style>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('ekonomiData', () => ({
            workers: @json($workers),
            workerId: '',
            tahun: new Date().getFullYear(),
            bulan: new Date().getMonth() + 1,
            akumulasi: null,
            riwayat: [],
            rewards: [],
            loadingDetail: false,
            today: new Date().toISOString().slice(0, 10),

            get selectedWorker() {
                return this.workers.find(w => String(w.id) === String(this.workerId)) || null;
            },

            init() {
                setTimeout(() => lucide.createIcons(), 50);
            },

            fmtIdr(n) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(n) || 0);
            },

            async loadWorkerData() {
                if (!this.workerId) {
                    this.akumulasi = null;
                    this.riwayat = [];
                    this.rewards = [];
                    return;
                }
                this.loadingDetail = true;
                try {
                    const prefix = window.location.pathname.includes('/admin') ? '/admin' : '/pengawas';
                    const res = await fetch(`${prefix}/ekonomi/detail/${this.workerId}?tahun=${this.tahun}&bulan=${this.bulan}`);
                    if (res.ok) {
                        const data = await res.json();
                        this.akumulasi = data.akumulasi;
                        this.riwayat = data.riwayat;
                        this.rewards = data.rewards;
                        setTimeout(() => lucide.createIcons(), 50);
                    }
                } catch (e) {
                    console.error('Error fetching data', e);
                } finally {
                    this.loadingDetail = false;
                }
            }
        }));
    });
</script>
@endsection
