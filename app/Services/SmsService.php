<?php namespace Services;

class SmsService {
    /**
     * Send SMS notification via configured provider (BeemSMS, Africa's Talking, or Log)
     */
    public static function send(string $phone, string $message): bool {
        $cfg = \config('sms', [
            'provider' => 'beem', // 'beem', 'africastalking', 'log'
            'api_key'  => '',
            'secret_key' => '',
            'sender_id' => 'VICOBA',
        ]);

        $phone = self::formatPhone($phone);
        if (!$phone) return false;

        $provider = $cfg['provider'] ?? 'log';

        if ($provider === 'beem' && !empty($cfg['api_key'])) {
            return self::sendBeem($phone, $message, $cfg);
        } elseif ($provider === 'africastalking' && !empty($cfg['api_key'])) {
            return self::sendAfricasTalking($phone, $message, $cfg);
        } else {
            // Log fallback for development/testing
            error_log("[SMS SENT to $phone]: $message");
            return true;
        }
    }

    private static function sendBeem(string $phone, string $message, array $cfg): bool {
        $url = 'https://api.beem.africa/v1/send';
        $postData = [
            'source_addr' => $cfg['sender_id'] ?? 'INFO',
            'encoding'    => 0,
            'message'     => $message,
            'recipients'  => [
                ['recipient_id' => 1, 'dest_addr' => $phone]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic ' . base64_encode(($cfg['api_key'] ?? '') . ':' . ($cfg['secret_key'] ?? '')),
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($postData)
        ]);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            error_log("BeemSMS Error: " . $err);
            return false;
        }

        $res = json_decode($response, true);
        return isset($res['successful']) && $res['successful'] === true;
    }

    private static function sendAfricasTalking(string $phone, string $message, array $cfg): bool {
        $url = 'https://api.africastalking.com/version1/messaging';
        $postData = http_build_query([
            'username' => $cfg['api_key'] ?? 'sandbox',
            'to'       => '+' . $phone,
            'message'  => $message,
            'from'     => $cfg['sender_id'] ?? ''
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'apiKey: ' . ($cfg['secret_key'] ?? ''),
                'Content-Type: application/x-www-form-urlencoded'
            ],
            CURLOPT_POSTFIELDS => $postData
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        return !empty($response);
    }

    public static function formatPhone(string $phone): string {
        $p = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($p, '0')) {
            $p = '255' . substr($p, 1);
        } elseif (str_starts_with($p, '7') || str_starts_with($p, '6')) {
            $p = '255' . $p;
        }
        return (strlen($p) >= 10) ? $p : '';
    }
}
