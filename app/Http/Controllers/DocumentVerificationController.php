<?php

namespace App\Http\Controllers;

use App\Services\Letters\DocumentVerificationService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentVerificationController extends Controller
{
    public function show(
        string $letterType,
        string $token,
        DocumentVerificationService $verificationService,
    ): InertiaResponse|Response {
        $verification = $verificationService->resolve($letterType, $token);

        abort_if($verification === null, 404);

        return Inertia::render('DocumentVerification', [
            ...$verification['service']->buildVerificationData($verification['letter']),
            'scannedAt' => now()->locale('id')->translatedFormat('d F Y H:i'),
        ]);
    }

    public function download(
        string $letterType,
        string $token,
        DocumentVerificationService $verificationService,
    ): BinaryFileResponse {
        $verification = $verificationService->resolve($letterType, $token);

        abort_if($verification === null, 404);

        $letter = $verification['letter'];
        $pdfPath = (string) ($letter->getAttribute('pdf_path') ?? '');

        abort_if($pdfPath === '', 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($pdfPath), 404);

        return response()->download(
            $disk->path($pdfPath),
            $verification['service']->getPdfFilename($letter),
        );
    }
}
