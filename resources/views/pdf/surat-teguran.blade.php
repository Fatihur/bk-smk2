<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Teguran {{ $tingkat }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.4; color: #000; margin: 0; padding: 25px 30px; }
        .header { display: flex; align-items: center; margin-bottom: 5px; }
        .header .logo { width: 90px; height: auto; margin-right: 12px; margin-top: 15px; }
        .header .text { flex: 1; text-align: center; }
        .header .text h1 { font-size: 11pt; font-weight: normal; margin: 0; text-transform: uppercase; }
        .header .text h2 { font-size: 11pt; font-weight: normal; margin: 0; text-transform: uppercase; }
        .header .text h3 { font-size: 13pt; font-weight: bold; margin: 2px 0; text-transform: uppercase; }
        .header .text p { font-size: 9pt; margin: 1px 0; }
        .garis { border-top: 2px solid #000; border-bottom: 1px solid #000; height: 3px; margin: 5px 0 12px; }
        .title { text-align: center; font-size: 13pt; font-weight: bold; text-decoration: underline; margin-bottom: 12px; }
        .content { text-align: justify; }
        .content p { margin: 6px 0; }
        .signature { margin-top: 25px; text-align: right; }
        .signature p { margin: 3px 0; }
        .signature .space { margin-top: 50px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="data:image/jpg;base64,{{ base64_encode(file_get_contents(public_path('images/logo.jpg'))) }}" class="logo" alt="Logo">
        <div class="text">
            <h1>PEMERINTAH PROVINSI NUSA TENGGARA BARAT</h1>
            <h2>DINAS PENDIDIKAN DAN KEBUDAYAAN</h2>
            <h3>SMK NEGERI 2 SUMBAWA BESAR</h3>
            <p>Jl. Lingkar Selatan Km. 04 Sumbawa Besar, Telp./Fax.: (0371) 2628048 / 2628047</p>
            <p>Situs Resmi: http://www.smkn2sumbawabesar.sch.id</p>
            <p>Email: smkn2_sumbawabesar@yahoo.co.id</p>
        </div>
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
        <p>Rombel: {{ $siswa->rombel }}</p>
        <p>di tempat</p>

        <p>Dengan ini kami sampaikan bahwa berdasarkan data pelanggaran yang tercatat, Saudara telah mencapai total poin pelanggaran sebesar <strong>{{ $totalPoin }} poin</strong>.</p>

        <p>Sehubungan dengan hal tersebut, kami memberikan <strong>Surat Teguran {{ $tingkat }}</strong> sebagai bentuk peringatan resmi dari pihak sekolah. Kami mengharapkan Saudara untuk segera memperbaiki sikap dan perilaku agar tidak terjadi pelanggaran lebih lanjut.</p>

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
