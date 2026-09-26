<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WhatsappLog; // Pastikan Model Log diimport

class SendWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $target;
    protected $message;
    protected $type;
    protected $mediaUrl;
    protected $fileName;
    protected $mimeType;

    /**
     * Jumlah percobaan ulang jika gagal (misal bot mati sebentar)
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct($target, $message, $type = 'text', $mediaUrl = null, $fileName = null, $mimeType = null)
    {
        $this->target = $target;
        $this->message = $message;
        $this->type = $type;
        $this->mediaUrl = $mediaUrl;
        $this->fileName = $fileName;
        $this->mimeType = $mimeType;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Susun Payload Data
            $payload = [
                'number' => $this->target,
                'message' => $this->message,
                'type' => $this->type,
            ];

            // Tambahkan data media jika tipe bukan text
            if ($this->type !== 'text' && $this->mediaUrl) {
                $payload['media_url'] = $this->mediaUrl;
                $payload['file_name'] = $this->fileName;
                $payload['mime_type'] = $this->mimeType;
            }

            // Kirim Request ke Service Node.js (Baileys)
            // Timeout 15 detik untuk memberi waktu download file jika ada media
            $response = Http::timeout(15)->post('http://localhost:3000/send-message', $payload);

            // LOGIKA PENCATATAN LOG KE DATABASE
            $status = $response->successful() ? 'success' : 'failed';
            $apiResponse = $response->body();

            // Cek Respon
            if ($response->failed()) {
                Log::error("Gagal kirim WA ke {$this->target}: " . $apiResponse);
            } else {
                Log::info("WA Terkirim ke: " . $this->target . " | Tipe: " . $this->type);
            }

            // Simpan ke Tabel WhatsappLog
            WhatsappLog::create([
                'recipient_number' => $this->target,
                'message'          => $this->message, // Bisa dipotong Str::limit() jika terlalu panjang
                'status'           => $status,
                'api_response'     => $apiResponse,
            ]);

        } catch (\Exception $e) {
            // Tangkap error koneksi (misal Node.js mati)
            Log::error("Queue WA Error ({$this->target}): " . $e->getMessage());

            // Simpan Log Error Sistem
            WhatsappLog::create([
                'recipient_number' => $this->target,
                'message'          => $this->message,
                'status'           => 'error', // Status khusus error sistem
                'api_response'     => "Exception: " . $e->getMessage(),
            ]);

            // Opsional: throw $e; jika ingin Job ini masuk ke tabel failed_jobs untuk di-retry nanti
            // throw $e; 
        }
    }
}