<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Support\AdminAccess;
use App\Models\RoomUsageRequest;
use App\Services\Letters\DocumentVerificationService;
use App\Services\Letters\UniversalLetterService;
use App\Services\WhatsAppNotificationService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Events\RecordSaved;
use Filament\Events\RecordUpdated;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Throwable;

abstract class EditLetterRecord extends EditRecord
{
    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->authorizeAccess();

        $willApprove = false;
        $generatedPdf = false;

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState(afterValidate: function (): void {
                $this->callHook('afterValidate');

                $this->callHook('beforeSave');
            });

            $data = $this->mutateFormDataBeforeSave($data);
            $record = $this->getRecord();
            $willApprove = $this->isLetterApprovalSave($record, $data);

            if ($willApprove) {
                $this->ensureApprovalMetadataIsComplete($record, $data);
            }

            $this->handleRecordUpdate($record, $data);

            if ($willApprove && $this->shouldGenerateApprovedLetter($record)) {
                $this->generateApprovedLetter($record);
                $generatedPdf = true;
            }

            $this->callHook('afterSave');
            Event::dispatch(RecordUpdated::class, ['record' => $this->record, 'data' => $data, 'page' => $this]);
            Event::dispatch(RecordSaved::class, ['record' => $this->record, 'data' => $data, 'page' => $this]);
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction()
                ? $this->rollBackDatabaseTransaction()
                : $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            if ($willApprove) {
                $this->refreshFormAfterFailedApproval();
                $this->sendApprovalFailureNotification($exception);

                return;
            }

            throw $exception;
        }

        $this->commitDatabaseTransaction();

        $this->rememberData();

        if ($willApprove) {
            $approvalWhatsAppUrl = $this->getApprovalWhatsAppUrl($this->getRecord());

            if (! $approvalWhatsAppUrl) {
                Notification::make()
                    ->title('Surat berhasil di-approve, tetapi link WhatsApp tidak dapat dibuat.')
                    ->body('Pastikan nomor WhatsApp mahasiswa sudah terisi dengan benar.')
                    ->danger()
                    ->send();

                return;
            }

            $this->sendApprovalSavedNotification($shouldSendSavedNotification, $generatedPdf);
            $this->redirect($approvalWhatsAppUrl);

            return;
        }

        if ($shouldSendSavedNotification) {
            $this->getSavedNotification()?->send();
        }

        if ($shouldRedirect && ($redirectUrl = $this->getRedirectUrl())) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode($redirectUrl));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()->visible(fn (): bool => AdminAccess::canMutate()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function isLetterApprovalSave(Model $record, array $data): bool
    {
        $statusField = $this->getApprovalStatusField($data);

        if (! $statusField) {
            return false;
        }

        $nextStatus = $this->normalizeApprovalStatus($data[$statusField] ?? null);
        $previousStatus = $this->normalizeApprovalStatus($record->getOriginal($statusField));

        return $this->isApprovedStatus($nextStatus) && ! $this->isApprovedStatus($previousStatus);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function getApprovalStatusField(array $data): ?string
    {
        return array_key_exists('status', $data) ? 'status' : null;
    }

    private function normalizeApprovalStatus(mixed $status): string
    {
        return strtoupper((string) $status);
    }

    private function isApprovedStatus(string $status): bool
    {
        return in_array($status, ['APPROVE', 'APPROVED'], true);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function ensureApprovalMetadataIsComplete(Model $record, array $data): void
    {
        if ($this->requiresLetterNumber($record) && blank($data['letter_number'] ?? null)) {
            throw new RuntimeException('Nomor surat wajib diisi sebelum status diubah menjadi approve.');
        }

        if ($this->requiresLetterDate($record) && blank($data['letter_date'] ?? null)) {
            throw new RuntimeException('Tanggal surat wajib diisi sebelum status diubah menjadi approve.');
        }
    }

    private function generateApprovedLetter(Model $record): void
    {
        $service = $this->resolveLetterService($record);

        $service->ensureTemplateReady();
        $service->generatePdf($record);
    }

    private function shouldGenerateApprovedLetter(Model $record): bool
    {
        return blank($record->getAttribute('pdf_path'));
    }

    private function resolveLetterService(Model $record): UniversalLetterService
    {
        $definition = app(DocumentVerificationService::class)->definitionForLetter($record);

        /** @var UniversalLetterService $service */
        $service = app($definition['service']);

        return $service;
    }

    private function getApprovalWhatsAppUrl(Model $record): ?string
    {
        return WhatsAppNotificationService::buildApproveUrl(
            $record,
            $this->getApprovalDocumentUrl($record),
        );
    }

    private function getApprovalDocumentUrl(Model $record): string
    {
        $verificationService = app(DocumentVerificationService::class);
        $definition = $verificationService->definitionForLetter($record);

        return $verificationService->buildVerificationUrl($definition['letter_type'], $record);
    }

    private function sendApprovalSavedNotification(bool $shouldSendSavedNotification, bool $generatedPdf): void
    {
        if (! $shouldSendSavedNotification) {
            return;
        }

        Notification::make()
            ->title($generatedPdf
                ? 'Surat berhasil di-approve & PDF dibuat'
                : 'Surat berhasil di-approve')
            ->success()
            ->send();
    }

    private function sendApprovalFailureNotification(Throwable $exception): void
    {
        $message = trim($exception->getMessage());

        Notification::make()
            ->title('Gagal approve surat')
            ->body($message !== '' ? $message : 'Terjadi kesalahan saat membuat PDF surat.')
            ->danger()
            ->persistent()
            ->send();
    }

    private function refreshFormAfterFailedApproval(): void
    {
        try {
            $this->getRecord()->refresh();
            $this->fillForm();
        } catch (Throwable) {
            //
        }
    }

    private function requiresLetterNumber(Model $record): bool
    {
        return ! $record instanceof RoomUsageRequest;
    }

    private function requiresLetterDate(Model $record): bool
    {
        return ! $record instanceof RoomUsageRequest;
    }
}
