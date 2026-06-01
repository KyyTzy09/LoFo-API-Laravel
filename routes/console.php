<?php

use App\Models\Announcement;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Http; // Jika menembak FCM manual via HTTP

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    // Cari announcement PENDING yang sudah >= 3 hari
    $expiredAnnouncements = Announcement::where('status', 'PENDING')
        ->where('created_at', '<=', now()->subDays(3))
        ->with('user.deviceTokens')
        ->get();

    foreach ($expiredAnnouncements as $announcement) {
        $tokens = $announcement->user->deviceTokens->pluck('token')->toArray();

        if (!empty($tokens)) {
            sendFcmNotification($tokens, $announcement->title);
        }
    }
})->dailyAt('08:00');

function sendFcmNotification(array $tokens, $itemTitle) {
    $jsonPath = storage_path('app/firebase_credentials.json');
    if (!file_exists($jsonPath)) return;

    $credentials = json_decode(file_get_contents($jsonPath), true);
    $accessToken = generateGoogleAccessToken($credentials);

    foreach ($tokens as $token) {
        Http::withToken($accessToken)
            ->post("https://fcm.googleapis.com/v1/projects/{$credentials['project_id']}/messages:send", [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => 'Reminder Pengumuman LoFo 📢',
                        'body' => "Apakah barang '{$itemTitle}' sudah ketemu? Yuk update statusnya sekarang!"
                    ],
                    // TAMBAHKAN DATA PAYLOAD INI JUGA BRO:
                    'data' => [
                        'title' => 'Reminder Pengumuman LoFo 📢',
                        'body' => "Apakah barang '{$itemTitle}' sudah ketemu? Yuk update statusnya sekarang!"
                    ]
                ]
            ]);
    }
}

function generateGoogleAccessToken($credentials)
{
    $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
    $now = time();
    $payload = json_encode([
        'iss' => $credentials['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => $now + 3600,
        'iat' => $now
    ]);

    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

    $signature = '';
    openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $credentials['private_key'], 'SHA256');
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

    $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]);

    return $response->json()['access_token'];
}
