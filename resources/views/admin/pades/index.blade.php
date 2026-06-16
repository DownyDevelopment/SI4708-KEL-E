@extends('layouts.app')
@section('title', 'Pencairan PADes')

@push('styles')
<style>
    .pades-page {
        padding: 2rem;
        max-width: 1440px;
        margin: 0 auto;
    }

    /* KPI Cards */
    .pades-kpis {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    @media (max-width: 960px) {
        .pades-kpis {
            grid-template-columns: 1fr;
        }
    }

    .pades-kpi {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .pades-kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.06);
    }

    .pades-kpi::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 80px;
        height: 80px;
        border-radius: 0 0 0 80px;
        opacity: 0.07;
    }

    .pades-kpi--balance::after { background: var(--success); }
    .pades-kpi--disbursed::after { background: var(--secondary); }
    .pades-kpi--sales::after { background: var(--warning); }

    .pades-kpi-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .pades-kpi-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pades-kpi-value {
        font-size: 1.65rem;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1.15;
        letter-spacing: -0.02em;
    }

    .pades-kpi-label {
        font-size: 0.82rem;
        color: var(--text-muted);
        margin-top: 0.35rem;
        font-weight: 500;
    }

    /* Alerts */
    .pades-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        border-radius: var(--radius-md);
        margin-bottom: 1.5rem;
        font-size: 0.88rem;
    }

    .pades-alert--success {
        background: rgba(34, 197, 94, 0.08);
        border: 1px solid rgba(34, 197, 94, 0.25);
        color: #15803d;
    }

    .pades-alert--error {
        background: rgba(239, 68, 68, 0.06);
        border: 1px solid rgba(239, 68, 68, 0.2);
        color: #991b1b;
    }

    /* Modal Form Custom Styles */
    .modal-box {
        background: white;
        padding: 2.25rem;
        border-radius: var(--radius-md);
        width: 100%;
        max-width: 500px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        position: relative;
    }

    .photo-preview-box {
        background: rgba(15, 23, 42, 0.95);
        padding: 1rem;
        border-radius: var(--radius-md);
        max-width: 90vw;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }
</style>
@endpush

@section('content')
<div class="pades-page animate-fade-in" x-data="padesData()">
    
    {{-- Banner --}}
    <x-hero-banner title="Pencairan PADes" description="Lacak hasil penjualan inventaris desa dan cairkan ke kas Pendapatan Asli Desa (PADes).">
        <x-slot:actions>
            <button type="button" class="global-hero-banner-btn-white" @click="openModal()">
                <i data-lucide="wallet-cards" style="width: 16px; height: 16px;"></i>
                Cairkan ke PADes
            </button>
        </x-slot:actions>
    </x-hero-banner>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="pades-alert pades-alert--success">
            <i data-lucide="check-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if($errors->any())
        <div class="pades-alert pades-alert--error">
            <i data-lucide="alert-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
            <div>
                @foreach($errors->all() as $error)
                    <p style="margin:0;">{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    {{-- KPI Cards Grid --}}
    <div class="pades-kpis">
        {{-- Card 1: Saldo Siap Cair --}}
        <div class="pades-kpi pades-kpi--balance">
            <div class="pades-kpi-top">
                <div class="pades-kpi-icon" style="background: rgba(34, 197, 94, 0.1); color: var(--success);">
                    <i data-lucide="wallet" style="width: 24px; height: 24px;"></i>
                </div>
                <span class="badge badge-success">Siap Cair</span>
            </div>
            <div class="pades-kpi-value">Rp {{ number_format($availableBalance, 0, ',', '.') }}</div>
            <div class="pades-kpi-label">Saldo Siap Cair</div>
        </div>

        {{-- Card 2: Total Dicairkan ke PADes --}}
        <div class="pades-kpi pades-kpi--disbursed">
            <div class="pades-kpi-top">
                <div class="pades-kpi-icon" style="background: rgba(59, 130, 246, 0.1); color: var(--secondary);">
                    <i data-lucide="landmark" style="width: 24px; height: 24px;"></i>
                </div>
                <span class="badge badge-primary">Masuk PADes</span>
            </div>
            <div class="pades-kpi-value">Rp {{ number_format($totalDisbursed, 0, ',', '.') }}</div>
            <div class="pades-kpi-label">Total Dicairkan ke PADes</div>
        </div>

        {{-- Card 3: Total Hasil Penjualan --}}
        <div class="pades-kpi pades-kpi--sales">
            <div class="pades-kpi-top">
                <div class="pades-kpi-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                    <i data-lucide="shopping-bag" style="width: 24px; height: 24px;"></i>
                </div>
                <span class="badge badge-warning">Komersial</span>
            </div>
            <div class="pades-kpi-value">Rp {{ number_format($totalSales, 0, ',', '.') }}</div>
            <div class="pades-kpi-label">Total Hasil Penjualan Inventaris</div>
        </div>
    </div>

    {{-- History Table Panel --}}
    <div class="glass-panel" style="padding: 1.75rem;">
        <h3 style="margin-bottom: 1.25rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem;">
            <i data-lucide="history" style="width: 20px; height: 20px; color: var(--primary);"></i>
            Riwayat Penyetoran PADes
        </h3>
        
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 160px;">Tanggal Pencairan</th>
                        <th style="width: 180px;">Nominal (Rp)</th>
                        <th>Keterangan / Catatan</th>
                        <th style="width: 120px; text-align: center;">Bukti Foto</th>
                        <th style="width: 180px;">Waktu Pencatatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pencairans as $p)
                        <tr>
                            <td style="font-weight: 600;">{{ $p->tanggal_pencairan->format('d M Y') }}</td>
                            <td style="font-weight: 700; color: var(--primary);">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                            <td style="font-size: 0.9rem; color: var(--text-muted);">{{ $p->keterangan }}</td>
                            <td style="text-align: center;">
                                @if($p->bukti_foto)
                                    <button type="button" @click="viewPhoto('{{ $p->bukti_foto }}')" style="background: none; border: none; padding: 0; cursor: pointer;" title="Lihat Bukti Foto">
                                        <img src="{{ $p->bukti_foto }}" alt="Bukti Foto" style="width: 48px; height: 32px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border);" />
                                    </button>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">Tidak Ada</span>
                                @endif
                            </td>
                            <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $p->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                                <i data-lucide="info" style="width: 24px; height: 24px; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                                <p style="margin: 0;">Belum ada riwayat pencairan hasil penjualan ke PADes.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Form Pencairan Modal --}}
    <div x-show="showModal" x-cloak class="modal-overlay modal-overlay--blur" @click="closeModal()">
        <div class="modal-box" @click.stop>
            <h3 style="margin-bottom: 1.5rem; font-weight: 700; font-size: 1.2rem; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="wallet-cards" style="width: 22px; height: 22px; color: var(--primary);"></i>
                Formulir Pencairan PADes
            </h3>
            
            <form method="POST" action="{{ route('admin.pades.store') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.25rem;">
                @csrf

                <div class="form-group">
                    <label class="form-label">Nominal Pencairan (Rp)</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--text-muted);">Rp</span>
                        <input type="number" name="nominal" class="form-input" style="padding-left: 2.75rem; font-weight: 700;" required min="1" max="{{ $availableBalance }}" placeholder="Cth: 500000" x-model="nominalInput" />
                    </div>
                    <small style="color: var(--text-muted); margin-top: 0.25rem; display: block;">
                        Batas maksimum penarikan: <strong style="color: var(--primary);">Rp {{ number_format($availableBalance, 0, ',', '.') }}</strong>
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal Pencairan</label>
                    <input type="date" name="tanggal_pencairan" class="form-input" required value="{{ date('Y-m-d') }}" />
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan / Keterangan</label>
                    <textarea name="keterangan" class="form-input" rows="3" required placeholder="Tulis rincian pencairan ke kas PADes..." style="resize: vertical;"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Bukti Foto / Kuitansi (Opsional)</label>
                    <input type="file" name="bukti_foto" class="form-input" accept="image/*" />
                    <small style="color: var(--text-muted); margin-top: 0.25rem;">Gunakan format JPG, PNG, atau JPEG max. 5MB.</small>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem;">
                    <button type="button" class="btn btn-outline" @click="closeModal()">Batal</button>
                    <button type="submit" class="btn btn-primary" :disabled="nominalInput <= 0 || nominalInput > {{ $availableBalance }}">
                        <i data-lucide="check" style="width: 16px; height: 16px;"></i>
                        Konfirmasi Pencairan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bukti Foto Preview Modal --}}
    <div x-show="activePhoto" x-cloak class="modal-overlay modal-overlay--blur" @click="activePhoto = null">
        <div class="photo-preview-box" @click.stop>
            <button type="button" @click="activePhoto = null" style="position: absolute; top: -1rem; right: -1rem; background: white; border: none; width: 2rem; height: 2rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.15);" title="Tutup">
                <i data-lucide="x" style="width: 16px; height: 16px; color: var(--text-main);"></i>
            </button>
            <img :src="activePhoto" alt="Bukti Foto Preview" style="max-width: 100%; max-height: 80vh; object-fit: contain; border-radius: 8px;" />
        </div>
    </div>

</div>

<script>
    function padesData() {
        return {
            showModal: false,
            activePhoto: null,
            nominalInput: '',

            openModal() {
                this.showModal = true;
                this.nominalInput = '';
            },

            closeModal() {
                this.showModal = false;
            },

            viewPhoto(url) {
                this.activePhoto = url;
                setTimeout(() => lucide.createIcons(), 50);
            }
        }
    }
</script>
@endsection
