<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WhatsappLog;

class SendWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $target;
    protected $message;
    protected $type;
    protected $mediaUrl;
    protected $fileName;
    protected $mimeType;
    protected $sessionId; // Tambahan untuk Multi Gateway

    public $tries = 3;

    /**
     * Create a new job instance.
     * @param string $sessionId (Opsional) ID Gateway spesifik (misal: 'gateway_1'). Jika null, sistem pilih acak.
     */
    public function __construct($target, $message, $type = 'text', $mediaUrl = null, $fileName = null, $mimeType = null, $sessionId = null)
    {
        $this->target = $target;
        $this->message = $message;
        $this->type = $type;
        $this->mediaUrl = $mediaUrl;
        $this->fileName = $fileName;
        $this->mimeType = $mimeType;
        $this->sessionId = $sessionId;
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

            // Tambahkan data media jika ada
            if ($this->type !== 'text' && $this->mediaUrl) {
                $payload['media_url'] = $this->mediaUrl;
                $payload['file_name'] = $this->fileName;
                $payload['mime_type'] = $this->mimeType;
            }

            // Tambahkan Session ID jika ingin kirim lewat gateway tertentu
            // Jika null, Node.js akan menggunakan Load Balancing (Round Robin/Random)
            if ($this->sessionId) {
                $payload['session_id'] = $this->sessionId;
            }

            // Kirim Request ke Service Node.js
            $response = Http::timeout(15)->post('http://localhost:3000/send-message', $payload);

            $responseData = $response->json();
            $status = $response->successful() && ($responseData['status'] ?? false) ? 'success' : 'failed';
            $apiResponse = $response->body();
            
            // Simpan info sender (nomor pengirim) jika dikembalikan oleh Node.js
            $sender = $responseData['sender'] ?? null;
            $logNote = $sender ? "[Via: $sender] " : "";

            if ($status === 'failed') {
                Log::error("Gagal kirim WA ke {$this->target}: " . $apiResponse);
            } else {
                Log::info("WA Terkirim ke: {$this->target} " . $logNote);
            }

            // Simpan ke Log Database
            WhatsappLog::create([
                'recipient_number' => $this->target,
                'message'          => $this->message,
                'status'           => $status,
                'api_response'     => $logNote . $apiResponse,
            ]);

        } catch (\Exception $e) {
            Log::error("Queue WA Error ({$this->target}): " . $e->getMessage());

            WhatsappLog::create([
                'recipient_number' => $this->target,
                'message'          => $this->message,
                'status'           => 'error',
                'api_response'     => "Exception: " . $e->getMessage(),
            ]);
        }
    }
}