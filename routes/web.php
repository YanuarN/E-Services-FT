<?php

use App\Http\Controllers\DocumentVerificationController;
use App\Http\Controllers\PublicSubmissionController;
use App\Models\LetterTemplate;
use App\Models\Room;
use App\Models\RoomUsageRequest;
use App\Support\PublicServiceCatalog;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Public/Home');
})->name('home');

Route::get('/services', function () {
    return Inertia::render('Public/Services', [
        'services' => PublicServiceCatalog::services(),
    ]);
})->name('services');

Route::get('/booking', function () {
    $rooms = Room::query()
        ->orderBy('name')
        ->get(['id', 'name', 'capacity'])
        ->map(fn (Room $room) => [
            'id' => $room->id,
            'name' => $room->name,
            'capacity' => $room->capacity,
        ]);

    return Inertia::render('Public/RoomBooking', [
        'rooms' => $rooms,
        'studyPrograms' => PublicServiceCatalog::studyPrograms(),
    ]);
})->name('booking');

Route::get('/booking/bookings', [PublicSubmissionController::class, 'roomBookingsByDate'])
    ->name('booking.bookings.by-date');

Route::post('/booking', [PublicSubmissionController::class, 'storeRoomBooking'])
    ->name('booking.store');

Route::get('/guidelines', function () {
    return Inertia::render('Public/Guidelines');
})->name('guidelines');

Route::get('/form/{letterType}', function (string $letterType) {
    $service = PublicServiceCatalog::find($letterType);
    abort_if(! $service, 404);

    return Inertia::render('Public/ServiceForm', [
        'service' => $service,
        'services' => PublicServiceCatalog::services(),
        'studyPrograms' => PublicServiceCatalog::studyPrograms(),
    ]);
})->name('form');

Route::post('/form/{letterType}', [PublicSubmissionController::class, 'storeLetter'])
    ->name('form.store');

Route::get('/verify/{letterType}/{token}', [DocumentVerificationController::class, 'show'])
    ->name('verification.show');
Route::get('/verify/{letterType}/{token}/file', [DocumentVerificationController::class, 'download'])
    ->name('verification.file');

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('filament.admin.resources.letter-templates.')
    ->group(function (): void {
        Route::get('/letter-templates/{record}/download', function (LetterTemplate $record) {
            abort_if(blank($record->document_path), 404);

            // Keep disk resolution aligned with Filament private upload behavior.
            $diskName = config('filament.default_filesystem_disk', config('filesystems.default'));
            if ($diskName === 'public') {
                $diskName = 'local';
            }

            $disk = Storage::disk($diskName);
            abort_unless($disk->exists($record->document_path), 404);

            return response()->download(
                $disk->path($record->document_path),
                "template-{$record->letter_type}.docx",
            );
        })->name('download');
    });

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.room-usage-requests.evidence.')
    ->group(function (): void {
        Route::get('/room-usage-requests/{record}/evidence', function (RoomUsageRequest $record) {
            abort_if(blank($record->document), 404);

            $disk = Storage::disk('local');
            abort_unless($disk->exists($record->document), 404);

            $mimeType = $disk->mimeType($record->document) ?: 'application/octet-stream';
            $filename = basename((string) $record->document);
            $isInline = str_starts_with($mimeType, 'image/') || $mimeType === 'application/pdf';

            return response()->file($disk->path($record->document), [
                'Content-Type' => $mimeType,
                'Content-Disposition' => sprintf(
                    '%s; filename="%s"',
                    $isInline ? 'inline' : 'attachment',
                    $filename,
                ),
            ]);
        })->name('download');
    });
