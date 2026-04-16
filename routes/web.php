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

    $bookings = RoomUsageRequest::query()
        ->whereIn('status', ['PENDING', 'APPROVED'])
        ->orderBy('start_at')
        ->get([
            'id',
            'room_id',
            'room_name',
            'student_name',
            'activity_name',
            'unit',
            'start_at',
            'end_at',
            'status',
        ])
        ->map(fn (RoomUsageRequest $booking) => [
            'id' => $booking->id,
            'roomId' => $booking->room_id,
            'roomName' => $booking->room_name ?: optional($booking->room)->name ?: 'Ruangan belum ditentukan',
            'studentName' => $booking->student_name,
            'activityName' => $booking->activity_name,
            'unit' => $booking->unit,
            'start' => $booking->start_at?->toIso8601String(),
            'end' => $booking->end_at?->toIso8601String(),
            'status' => $booking->status,
        ]);

    return Inertia::render('Public/RoomBooking', [
        'rooms' => $rooms,
        'bookings' => $bookings,
        'studyPrograms' => PublicServiceCatalog::studyPrograms(),
    ]);
})->name('booking');

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

            return $disk->download(
                $record->document_path,
                "template-{$record->letter_type}.docx",
            );
        })->name('download');
    });
