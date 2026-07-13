<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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
        $siswa = Siswa::find($this->idSiswa);
        if (!$siswa) return;
        if (!$siswa->no_wali) {
            \Log::error("WA send skipped: Siswa {$this->idSiswa} has no no_wali set");
            return;
        }

        $surat = SuratTeguran::where('id_siswa', $this->idSiswa)
            ->where('tingkat', $this->tingkat)
            ->latest('id')
            ->first();

        if (!$surat) return;

        $namaWali = $siswa->ayah ?: $siswa->ibu ?: 'Bapak/Ibu Wali';

        $pesan = "Assalamu'alaikum Wr. Wb.\n\n"
            . "Kepada Yth. {$namaWali}\n"
            . "Orang tua/wali dari {$siswa->nama_siswa} - {$siswa->rombel}\n\n"
            . "Dengan ini kami sampaikan bahwa putra/putri Bapak/Ibu telah mencapai "
            . "akumulasi poin pelanggaran sebesar {$surat->total_poin} poin "
            . "dan diterbitkan Surat Teguran " . strtoupper($this->tingkat) . ".\n\n"
            . "Untuk informasi lebih lanjut, silakan lihat surat teguran terlampir.\n\n"
            . "Atas perhatian dan kerja samanya, kami ucapkan terima kasih.\n\n"
            . "Wassalamu'alaikum Wr. Wb.\n"
            . "SMK Negeri 2 Sumbawa Besar";

        try {
            WhatsApp::web('smkn2_monitoring')->messages()->sendDocument($siswa->no_wali, [
                'url' => config('app.url') . '/storage/teguran/' . $this->filename,
                'filename' => "Surat_Teguran_{$this->tingkat}.pdf"
            ]);
            WhatsApp::web('smkn2_monitoring')->messages()->sendText($siswa->no_wali, $pesan);

            $surat->update(['status_terkirim' => true]);
        } catch (\Exception $e) {
            \Log::error("WA send failed for {$siswa->no_wali}: " . $e->getMessage());
        }
    }
}
