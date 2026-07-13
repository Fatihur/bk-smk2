<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Teguran {{ $tingkat }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; margin: 0; padding: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { font-size: 18pt; font-weight: bold; margin: 0; text-transform: uppercase; }
        .header h2 { font-size: 16pt; font-weight: bold; margin: 5px 0; text-transform: uppercase; }
        .header p { font-size: 11pt; margin: 2px 0; }
        .garis { border-top: 2px solid #000; border-bottom: 1px solid #000; height: 3px; margin: 10px 0 20px; }
        .title { text-align: center; font-size: 14pt; font-weight: bold; text-decoration: underline; margin-bottom: 20px; }
        .content { text-align: justify; }
        .content p { margin: 10px 0; }
        .signature { margin-top: 50px; text-align: right; }
        .signature p { margin: 5px 0; }
        .signature .space { margin-top: 80px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PEMERINTAH PROVINSI NUSA TENGGARA BARAT</h1>
        <h2>SMK NEGERI 2 SUMBAWA BESAR</h2>
        <p>Jalan Garuda No. 10, Sumbawa Besar, NTB</p>
        <p>Email: info@smkn2sumbawa.sch.id | Telp: (0371) 12345</p>
    </div>
    <div class="garis"></div>

    <div class="title">SURAT TEGURAN {{ $tingkat }}</div>

    <div class="content">
        <p>Nomor: {{ $tingkat }}/SMKN2-SB/{{ date('Y') }}/{{ rand(100, 999) }}</p>
        <p>Lampiran: -</p>
        <p>Perihal: Surat Teguran {{ $tingkat }}</p>

        <p>Kepada Yth.,</p>
        <p><strong>{{ $siswa->nama_siswa }}</strong></p>
        <p>NISN: {{ $siswa->nisn }}</p>
        <p>Kelas: {{ $siswa->rombel }}</p>
        <p>di tempat</p>

        <p>Dengan ini kami sampaikan bahwa berdasarkan data pelanggaran yang tercatat, Saudara telah mencapai total poin pelanggaran sebesar <strong>{{ $totalPoin }} poin</strong>.</p>

        <p>Schubungan dengan hal tersebut, kami memberikan <strong>Surat Teguran {{ $tingkat }}</strong> sebagai bentuk peringatan resmi dari pihak sekolah. Kami mengharapkan Saudara untuk segera memperbaiki sikap dan perilaku agar tidak terjadi pelanggaran lebih lanjut.</p>

        <p>Apabila Saudara kembali melakukan pelanggaran dan mencapai batas poin yang lebih tinggi, maka pihak sekolah akan memberikan sanksi yang lebih tegas sesuai dengan peraturan yang berlaku.</p>

        <p>Demikian surat teguran ini kami sampaikan untuk diketahui dan dipatuhi.</p>
    </div>

    <div class="signature">
        <p>Sumbawa Besar, {{ $tanggal }}</p>
        <p>Kepala Sekolah,</p>
        <div class="space"></div>
        <p><strong>{{ config('app.kepala_sekolah', 'Kepala Sekolah') }}</strong></p>
        <p>NIP. {{ config('app.kepala_sekolah_nip', '-') }}</p>
    </div>
</body>
</html>
