<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Kedisiplinan</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.5; color: #000; margin: 0; padding: 30px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 14pt; font-weight: bold; margin: 0; text-transform: uppercase; }
        .header h2 { font-size: 13pt; font-weight: bold; margin: 3px 0; text-transform: uppercase; }
        .header p { font-size: 10pt; margin: 1px 0; }
        .garis { border-top: 2px solid #000; border-bottom: 1px solid #000; height: 3px; margin: 8px 0 15px; }
        .title { text-align: center; font-size: 12pt; font-weight: bold; text-decoration: underline; margin-bottom: 15px; }
        .info { font-size: 10pt; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; font-size: 10pt; }
        th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PEMERINTAH PROVINSI NUSA TENGGARA BARAT</h1>
        <h2>SMK NEGERI 2 SUMBAWA BESAR</h2>
        <p>Jalan Garuda No. 10, Sumbawa Besar, NTB</p>
    </div>
    <div class="garis"></div>

    <div class="title">LAPORAN REKAP KEDISIPLINAN SISWA</div>

    <div class="info">
        @if ($siswa)
        Siswa: {{ $siswa->nama }} ({{ $siswa->kelas->nama_kelas ?? '-' }})<br>
        @endif
        @if (request('dari'))
        Dari: {{ request('dari') }}<br>
        @endif
        @if (request('sampai'))
        Sampai: {{ request('sampai') }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Nama Siswa</th>
                <th style="width: 80px;">Kelas</th>
                <th>Pelanggaran</th>
                <th style="width: 50px;">Poin</th>
                <th style="width: 90px;">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pelanggaran as $i => $p)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $p->siswa->nama ?? '-' }}</td>
                <td class="text-center">{{ $p->siswa->kelas->nama_kelas ?? '-' }}</td>
                <td>{{ $p->jenis->nama ?? '-' }}</td>
                <td class="text-center">{{ $p->jenis->poin ?? 0 }}</td>
                <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data pelanggaran</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if ($pelanggaran->count() > 0)
    <p style="margin-top: 15px; font-size: 10pt;">
        Total Poin: <strong>{{ $pelanggaran->sum(fn($p) => $p->jenis->poin ?? 0) }}</strong>
    </p>
    @endif
</body>
</html>
