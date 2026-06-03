<?php

namespace App\Jobs;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class GenerateAndUploadQrCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $item;

    /**
     * Create a new job instance.
     */
    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cloudName = env("CLOUDINARY_CLOUD_NAME");
        $apiKey = env("CLOUDINARY_API_KEY");
        $apiSecret = env("CLOUDINARY_API_SECRET");

        // 1. Generate QR Code murni di memori worker background
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($this->item->itemId)
            ->build();

        // 2. Simpan sementara di folder temp server
        $tempQrPath = sys_get_temp_dir() . "/" . uniqid() . "_qr.png";
        file_put_contents($tempQrPath, $result->getString());

        // 3. Tembak ke Cloudinary dari background proses
        $qrResponse = Http::asMultipart()
            ->withBasicAuth($apiKey, $apiSecret)
            ->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", [
                "file" => fopen($tempQrPath, "r"),
                "folder" => "qrcodes",
            ]);

        if ($qrResponse->successful()) {
            // 4. Update kolom qr_url jika upload sukses
            $this->item->update([
                'qr_url' => $qrResponse->json("secure_url")
            ]);
        }

        // Hapus file temporary
        @unlink($tempQrPath);
    }
}
