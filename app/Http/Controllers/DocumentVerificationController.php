<?php

namespace App\Http\Controllers;

use App\Services\Letters\DocumentVerificationService;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

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
}
