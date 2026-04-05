<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;
use Illuminate\Support\Str;

class QrCodeService
{
    public function generateLetterQrCode(string $payload): string
    {
        $directory = storage_path('app/temp/qr-codes');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory.'/qr-'.Str::uuid().'.png';

        $options = new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'outputBase64' => false,
            'scale' => 6,
            'eccLevel' => QRCode::ECC_M,
            'addQuietzone' => true,
            'quietzoneSize' => 2,
            'imageTransparent' => false,
        ]);

        (new QRCode($options))->render($payload, $path);

        return $path;
    }
}
