<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Siswa;
use App\Models\SuratTeguran;
use Kstmostofa\LaravelWhatsApp\Facades\WhatsApp;

class KirimWaTeguran implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $idSiswa,
        public string $tingkat,
        public string $filename
    ) {}

    public function handle(): void
    {
        $siswa = Siswa::with(['kelas', 'orangTua'])->find($this->idSiswa);
        if (!$siswa) return;

        $surat = SuratTeguran::where('id_siswa', $this->idSiswa)
            ->where('tingkat', $this->tingkat)
            ->latest('id')
            ->first();

        $filePath = storage_path('app/public/teguran/' . $this->filename);

        $pesan = "Assalamu'alaikum Wr. Wb.\n\n"
            . "Kepada Yth. Bapak/Ibu {nama}\n"
            . "Orang tua/wali dari {$siswa->nama} - Kelas {$siswa->kelas->nama_kelas}\n\n"
            . "Dengan ini kami sampaikan bahwa putra/putri Bapak/Ibu telah mencapai "
            . "akumulasi poin pelanggaran sebesar {$surat->total_poin} poin "
            . "dan diterbitkan Surat Teguran " . strtoupper($this->tingkat) . ".\n\n"
            . "Untuk informasi lebih lanjut, silakan lihat surat teguran terlampir.\n\n"
            . "Atas perhatian dan kerja samanya, kami ucapkan terima kasih.\n\n"
            . "Wassalamu'alaikum Wr. Wb.\n"
            . "SMK Negeri 2 Sumbawa Besar";

        foreach ($siswa->orangTua as $ortu) {
            try {
                WhatsApp::sendDocument($ortu->nomor_wa, $filePath, "Surat_Teguran_{$this->tingkat}.pdf");
                WhatsApp::sendMessage($ortu->nomor_wa, str_replace('{nama}', $ortu->nama, $pesan));
            } catch (\Exception $e) {
                \Log::error("WA send failed for {$ortu->nomor_wa}: " . $e->getMessage());
            }
        }

        if ($surat) {
            $surat->update(['status_terkirim' => true]);
        }
    }
}
