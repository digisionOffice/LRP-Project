<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * A pure API Connector for the StarSender service.
 * This version is a complete match for the original CI4 helper's functionality.
 */
class StarSenderService
{
    protected string $baseUrl;
    protected string $defaultSender;
    protected array $accounts;
    
    public function __construct()
    {
        $this->baseUrl = config('services.starsender.base_url');
        $this->accounts = config('services.starsender.accounts');
        $this->defaultSender = config('services.starsender.default_sender');

        if (empty($this->accounts) || !$this->baseUrl) {
            Log::critical('StarSender Service is not configured properly in config/services.php.');
            throw new \Exception('StarSender Service is not configured properly.');
        }
    }

    /**
     * The single public method responsible for making the API call.
     *
     * @param string $receiverNumber The recipient's phone number.
     * @param string $message The pre-formatted message content.
     * @param string|null $senderAccount The key of the sender account (e.g., 'ilham').
     * @param string|null $fileUrl Optional URL of a file to send with the message.
     * @return array|null The API response or null on failure.
     */
    public function send(string $receiverNumber, string $message, ?string $senderAccount = null, ?string $fileUrl = null): ?array
    {
        $senderKey = $senderAccount ?? $this->defaultSender;
        $token = $this->getAccountToken($senderKey);

        if (!$token) {
            Log::error("StarSender Service: Sender account '{$senderKey}' not found or token is missing.");
            return null;
        }

        // --- CORRECTED PAYLOAD ---
        // This payload now matches the structure of your original CI4 code.
        $payload = [
            'messageType' => 'text', // CORRECTED: from 'MessageType' and 'tex' to 'messageType' and 'text'
            'to' => $receiverNumber,
            'body' => $message,
            'delay' => 10,           // ADDED: Missing delay parameter
        ];

        // ADDED: Logic to handle file attachments
        if ($fileUrl) {
            $payload['file'] = $fileUrl;
        }

        $response = Http::withHeaders(['Authorization' => $token])
            ->timeout(30)
            ->post($this->baseUrl . '/send', $payload);

        if ($response->failed()) {
            Log::error('StarSender API request failed.', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return null;
        }

        return $response->json();
    }
    
    /**
     * Gets the token for a given account key.
     */
    private function getAccountToken(string $accountKey): ?string
    {
        return $this->accounts[strtolower($accountKey)]['token'] ?? null;
    }
}
