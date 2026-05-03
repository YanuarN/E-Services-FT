<?php

namespace App\Filament\Support;

use App\Models\RoomUsageRequest;
use App\Services\Letters\DocumentVerificationService;
use App\Services\Letters\UniversalLetterService;
use App\Services\WhatsAppNotificationService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Throwable;

class LetterTableActions
{
    public static function accept(): Action
    {
        return Action::make('accept')
            ->label('Accept')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->visible(fn (Model $record): bool => static::isPending($record) && AdminAccess::canMutate())
            ->modalHeading('Accept dan Generate Surat')
            ->modalDescription(fn (Model $record): string => static::requiresLetterNumber($record)
                ? 'Isi nomor surat dan tanggal surat. Setelah disimpan, surat akan diproses menjadi approved.'
                : 'Isi tanggal surat. Setelah disimpan, surat akan diproses menjadi approved.')
            ->fillForm(fn (Model $record): array => [
                'letter_number' => $record->getAttribute('letter_number'),
                'letter_date' => $record->getAttribute('letter_date')
                    ? Carbon::parse($record->getAttribute('letter_date'))->toDateString()
                    : now()->toDateString(),
            ])
            ->schema(fn (Model $record): array => array_values(array_filter([
                static::requiresLetterNumber($record)
                    ? TextInput::make('letter_number')
                        ->label('Nomor Surat')
                        ->required()
                        ->maxLength(255)
                    : null,
                DatePicker::make('letter_date')
                    ->label('Tanggal Surat')
                    ->required()
                    ->native(false),
            ])))
            ->action(function (Model $record, array $data) {
                abort_unless(AdminAccess::canMutate(), 403);

                try {
                    $service = static::resolveService($record);

                    $record->forceFill([
                        'letter_number' => static::requiresLetterNumber($record)
                            ? ($data['letter_number'] ?? null)
                            : $record->getAttribute('letter_number'),
                        'letter_date' => $data['letter_date'],
                    ]);

                    $service->ensureTemplateReady();

                    $pdfPath = $service->generatePdf($record);

                    $record->forceFill([
                        'status' => static::approvedStatus($record),
                        'pdf_path' => $pdfPath,
                    ])->save();

                    Notification::make()
                        ->title('Surat berhasil di-accept.')
                        ->body('PDF surat berhasil dibuat dan notifikasi WhatsApp siap dikirim.')
                        ->success()
                        ->send();

                    $url = WhatsAppNotificationService::buildApproveUrl(
                        $record,
                        static::verificationUrl($record),
                    );

                    if ($url) {
                        return redirect()->away($url);
                    }
                } catch (Throwable $exception) {
                    static::notifyPdfGenerationFailure($record, $exception);

                    return null;
                }
            });
    }

    public static function reject(): Action
    {
        return Action::make('reject')
            ->label('Reject')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->modalHeading('Reject pengajuan surat?')
            ->modalDescription('Status pengajuan akan diubah menjadi reject dan notifikasi WhatsApp siap dikirim.')
            ->schema([
                Textarea::make('rejection_reason')
                    ->label('Alasan Penolakan')
                    ->placeholder('Contoh: Data tidak lengkap atau dokumen pendukung tidak sesuai.')
                    ->required()
                    ->maxLength(500),
            ])
            ->visible(fn (Model $record): bool => static::isPending($record) && AdminAccess::canMutate())
            ->action(function (Model $record, array $data) {
                abort_unless(AdminAccess::canMutate(), 403);

                try {
                    $record->forceFill([
                        'status' => static::rejectedStatus($record),
                    ])->save();

                    Notification::make()
                        ->title('Pengajuan berhasil di-reject.')
                        ->success()
                        ->send();

                    $url = WhatsAppNotificationService::buildRejectUrl(
                        $record,
                        (string) ($data['rejection_reason'] ?? ''),
                    );

                    if ($url) {
                        return redirect()->away($url);
                    }
                } catch (Throwable $exception) {
                    Notification::make()
                        ->title('Gagal reject pengajuan')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

                    return null;
                }
            });
    }

    public static function printPdf(): Action
    {
        return Action::make('printPdf')
            ->label('Cetak PDF')
            ->icon(Heroicon::OutlinedPrinter)
            ->color('success')
            ->visible(fn (Model $record): bool => static::canPrintDocument($record))
            ->url(fn (Model $record): string => static::pdfUrl($record))
            ->openUrlInNewTab();
    }

    private static function resolveService(Model $record): UniversalLetterService
    {
        $definition = app(DocumentVerificationService::class)->definitionForLetter($record);

        /** @var UniversalLetterService $service */
        $service = app($definition['service']);

        return $service;
    }

    private static function notifyPdfGenerationFailure(Model $record, Throwable $exception): void
    {
        $message = trim($exception->getMessage());

        if (static::isTemplateFailure($message)) {
            Notification::make()
                ->title('Template surat belum siap')
                ->body(static::templateFailureMessage($record, $message))
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        Notification::make()
            ->title('Gagal generate PDF')
            ->body($message !== '' ? $message : 'Terjadi kesalahan saat membuat PDF surat.')
            ->danger()
            ->persistent()
            ->send();
    }

    private static function isTemplateFailure(string $message): bool
    {
        $message = strtolower($message);

        return str_contains($message, 'template')
            && (
                str_contains($message, 'belum tersedia')
                || str_contains($message, 'tidak ditemukan')
                || str_contains($message, 'docx')
            );
    }

    private static function templateFailureMessage(Model $record, string $message): string
    {
        try {
            $definition = app(DocumentVerificationService::class)->definitionForLetter($record);
            $letterType = $definition['letter_type'];
            $label = app(DocumentVerificationService::class)->letterLabel($letterType);
        } catch (Throwable) {
            $label = 'surat ini';
        }

        if (str_contains(strtolower($message), 'tidak ditemukan')) {
            return "File template untuk {$label} tidak ditemukan di storage. Upload ulang template DOCX melalui menu Template Surat.";
        }

        if (str_contains(strtolower($message), 'docx')) {
            return "Template untuk {$label} harus berupa file DOCX. Upload ulang template yang sesuai melalui menu Template Surat.";
        }

        return "Template untuk {$label} belum tersedia. Upload template DOCX terlebih dahulu melalui menu Template Surat.";
    }

    private static function isPending(Model $record): bool
    {
        return in_array((string) $record->getAttribute('status'), ['SUBMITTED', 'PENDING'], true);
    }

    private static function canPrintDocument(Model $record): bool
    {
        $status = (string) $record->getAttribute('status');

        return filled($record->getAttribute('pdf_path'))
            && in_array($status, ['APPROVE', 'APPROVED'], true);
    }

    private static function approvedStatus(Model $record): string
    {
        return static::isRoomUsageStatusSet($record) ? 'APPROVED' : 'APPROVE';
    }

    private static function rejectedStatus(Model $record): string
    {
        return static::isRoomUsageStatusSet($record) ? 'REJECTED' : 'REJECT';
    }

    private static function isRoomUsageStatusSet(Model $record): bool
    {
        return $record instanceof RoomUsageRequest;
    }

    private static function requiresLetterNumber(Model $record): bool
    {
        return ! static::isRoomUsageStatusSet($record);
    }

    private static function verificationUrl(Model $record): string
    {
        $verificationService = app(DocumentVerificationService::class);
        $definition = $verificationService->definitionForLetter($record);

        return $verificationService->buildVerificationUrl($definition['letter_type'], $record);
    }

    private static function pdfUrl(Model $record): string
    {
        $verificationService = app(DocumentVerificationService::class);
        $definition = $verificationService->definitionForLetter($record);
        $token = $verificationService->ensurePublicToken($record);

        return route('verification.file', [
            'letterType' => $definition['letter_type'],
            'token' => $token,
        ]);
    }
}
