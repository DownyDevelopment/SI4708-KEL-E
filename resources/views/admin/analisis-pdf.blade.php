<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Dampak Program</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; margin: 24px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .subtitle { color: #64748b; margin-bottom: 24px; }
        .cards { width: 100%; margin-bottom: 24px; }
        .card { display: inline-block; width: 30%; vertical-align: top; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-right: 2%; }
        .card-title { color: #64748b; font-size: 10px; text-transform: uppercase; }
        .card-value { font-size: 18px; font-weight: bold; margin-top: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        th { background: #f8fafc; }
        .footer { margin-top: 32px; font-size: 10px; color: #94a3b8; }
    </style>
</head>
<body>
    <h1>Laporan Dampak Program Work4Village</h1>
    <p class="subtitle">Periode: {{ ucfirst($period) }} · Dicetak {{ now()->format('d/m/Y H:i') }}</p>

    <div class="cards">
        <div class="card">
            <div class="card-title">Warga Prasejahtera Bekerja</div>
            <div class="card-value">{{ $data['total_warga_bekerja'] }} Orang</div>
        </div>
        <div class="card">
            <div class="card-title">Akumulasi Dampak Lingkungan</div>
            <div class="card-value">{{ number_format($data['dampak_lingkungan']['value'], 0, ',', '.') }} {{ $data['dampak_lingkungan']['unit'] }}</div>
        </div>
        <div class="card">
            <div class="card-title">Total Dana Insentif</div>
            <div class="card-value">Rp {{ number_format($data['total_insentif'], 0, ',', '.') }}</div>
        </div>
    </div>

    <h2>Tren Partisipasi Warga</h2>
    <table>
        <thead>
            <tr><th>Periode</th><th>Jumlah Pekerja</th></tr>
        </thead>
        <tbody>
            @forelse($data['tren_partisipasi'] as $row)
                <tr><td>{{ $row['bulan'] }}</td><td>{{ $row['partisipasi'] }}</td></tr>
            @empty
                <tr><td colspan="2">Belum ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2 style="margin-top: 24px;">Rincian Capaian Program Kerja</h2>
    <table>
        <thead>
            <tr>
                <th>Nama Program</th>
                <th>Jenis Sektor</th>
                <th>Mulai</th>
                <th>Selesai</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['rincian_capaian'] as $item)
                <tr>
                    <td>{{ $item->nama_program }}</td>
                    <td>{{ $item->jenis_program }}</td>
                    <td>{{ $item->tanggal_mulai ? \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->tanggal_selesai ? \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->status ?? 'Berjalan' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Belum ada data program kerja.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">Dokumen ini dihasilkan otomatis oleh sistem Work4Village.</p>
</body>
</html>
