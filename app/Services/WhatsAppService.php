<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

/**
 * WhatsAppService
 *
 * Menggunakan Fonnte API (https://fonnte.com)
 * .env:
 *   FONNTE_TOKEN=your_token_here
 *   WHATSAPP_ADMIN_PHONE=601125855211
 */
class WhatsAppService
{
    private string $token;
    private string $adminPhone;
    private bool $enabled;

    public function __construct()
    {
        $this->token      = config('services.fonnte.token', '');
        $rawPhone         = config('services.fonnte.admin_phone', '');

        // ✅ FIX: Format admin phone semasa construct supaya konsisten
        $this->adminPhone = $this->formatPhone($rawPhone);
        $this->enabled    = !empty($this->token) && !empty($this->adminPhone);
    }

    /**
     * Hantar notifikasi booking baru kepada admin
     */
    public function sendBookingNotification(array $bookingData): array
    {
        $message = $this->buildBookingMessage($bookingData);

        if ($this->enabled) {
            // ✅ FIX: adminPhone dah diformat dalam __construct
            $result = $this->sendViaFonnte($this->adminPhone, $message);
            if ($result['success']) {
                return $result;
            }
            Log::warning('Fonnte API failed, falling back to wa.me', ['error' => $result['message']]);
        }

        return [
            'success' => true,
            'method'  => 'walink',
            'wa_link' => $this->buildWaLink($this->adminPhone ?: '601125855211', $message),
            'message' => 'WhatsApp API tidak dikonfigurasi. Guna link di bawah.',
        ];
    }

    /**
     * Hantar mesej ke nombor tertentu
     */
    public function sendMessage(string $phone, string $message): array
    {
        $phone = $this->formatPhone($phone);

        if ($this->enabled) {
            return $this->sendViaFonnte($phone, $message);
        }

        return [
            'success' => false,
            'method'  => 'walink',
            'wa_link' => $this->buildWaLink($phone, $message),
            'message' => 'API tidak dikonfigurasi',
        ];
    }

    /**
     * Hantar via Fonnte API
     */
    private function sendViaFonnte(string $phone, string $message): array
    {
        try {
            Log::info('WhatsApp sending to: ' . $phone); // ← debug log

            /** @var Response $response */
            $response = Http::timeout(15)
                ->withHeaders(['Authorization' => $this->token])
                ->post('https://api.fonnte.com/send', [
                    'target'      => $phone,
                    'message'     => $message,
                    'typing'      => false,
                    'countryCode' => '60',
                ]);

            $body = $response->json();

            if ($response->successful() && isset($body['status']) && $body['status'] === true) {
                return [
                    'success' => true,
                    'method'  => 'fonnte',
                    'message' => 'WhatsApp berjaya dihantar!',
                    'data'    => $body,
                ];
            }

            return [
                'success' => false,
                'method'  => 'fonnte',
                'message' => 'Gagal hantar: ' . ($body['reason'] ?? 'Unknown error'),
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp send failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'method'  => 'fonnte',
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Bina mesej booking untuk WhatsApp
     */
    private function buildBookingMessage(array $data): string
    {
        $line = "─────────────────────";
        return implode("\n", [
            "🎉 *BOOKING BARU DITERIMA*",
            $line,
            "📋 *No. Booking:* {$data['booking_no']}",
            "👤 *Pelanggan:* {$data['customer_name']}",
            "📞 *No. Tel:* " . ($data['customer_phone'] ?? '-'),
            "🆔 *IC:* " . ($data['customer_ic'] ?? '-'),
            $line,
            "✈️ *Trip:* {$data['trip_location']}",
            "🌍 *Destinasi:* " . ($data['trip_country'] ?? '-'),
            "📅 *Tarikh:* " . ($data['trip_date_from'] ?? '-') . " → " . ($data['trip_date_to'] ?? '-'),
            $line,
            "💰 *Jumlah:* RM " . number_format($data['total_amount'], 2),
            "💳 *Status Bayaran:* " . ($data['payment_status'] ?? '-'),
            "🏢 *Company:* " . ($data['company_name'] ?? '-'),
            $line,
            "📝 *Nota:* " . ($data['notes'] ?? 'Tiada'),
            $line,
            "⏰ " . now()->format('d/m/Y H:i:s'),
            "",
            "_Booking dibuat melalui sistem tempahan online_",
        ]);
    }

    /**
     * Jana wa.me link sebagai fallback
     */
    private function buildWaLink(string $phone, string $message): string
    {
        $phone = ltrim($phone, '+');
        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
    }

    /**
     * ✅ FIX: Format nombor telefon — handle semua format input
     *
     * Input yang disokong:
     *   "0112585521"      → "60112585521"   (tambah 6)
     *   "60112585521"     → "60112585521"   (dah betul, tak berubah)
     *   "+60112585521"    → "60112585521"   (buang +)
     *   "011-258-5521"    → "60112585521"   (buang dash)
     *   "601125855211"    → "601125855211"  (dah betul, tak berubah)
     */
    private function formatPhone(?string $phone): string
    {
        if (empty($phone)) return '';

        // Step 1: Buang semua bukan digit
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Step 2: Buang leading zeros berlebihan
        // Contoh: "00601125855211" → "601125855211"
        if (str_starts_with($phone, '00')) {
            $phone = ltrim($phone, '0');
        }

        // Step 3: Dah ada 60 — guna terus (jangan tambah 6 lagi)
        if (str_starts_with($phone, '60')) {
            return $phone;
        }

        // Step 4: Nombor Malaysia bermula 0 — tambah 6 sahaja
        if (str_starts_with($phone, '0')) {
            return '6' . $phone;
        }

        // Step 5: Nombor dah dalam format antarabangsa lain — guna terus
        return $phone;
    }

    /**
     * Semak sama ada WhatsApp API dikonfigurasi
     */
    public function isConfigured(): bool
    {
        return $this->enabled;
    }

    /**
     * Dapatkan wa.me link untuk admin
     */
    public function getAdminWaLink(string $message = ''): string
    {
        $phone = $this->adminPhone ?: '601125855211';
        return $this->buildWaLink($phone, $message);
    }
}