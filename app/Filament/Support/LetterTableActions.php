<?php

namespace App\Filament\Support;

use App\Services\Letters\DocumentVerificationService;
use App\Services\Letters\UniversalLetterService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
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
            ->visible(fn (Model $record): bool => $record->getAttribute('status') === 'SUBMITTED')
            ->modalHeading('Accept dan Generate Surat')
            ->modalDescription('Isi nomor surat dan tanggal surat. Setelah disimpan, surat akan diproses menjadi approved.')
            ->fillForm(fn (Model $record): array => [
                'letter_number' => $record->getAttribute('letter_number'),
                'letter_date' => $record->getAttribute('letter_date')
                    ? Carbon::parse($record->getAttribute('letter_date'))->toDateString()
                    : now()->toDateString(),
            ])
            ->schema([
                TextInput::make('letter_number')
                    ->label('Nomor Surat')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('letter_date')
                    ->label('Tanggal Surat')
                    ->required()
                    ->native(false),
            ])
            ->action(function (Model $record, array $data) {
                try {
                    $service = static::resolveService($record);

                    $record->forceFill([
                        'letter_number' => $data['letter_number'],
                        'letter_date' => $data['letter_date'],
                    ]);

                    $service->ensureTemplateReady();

                    $pdfPath = $service->generatePdf($record);

                    $record->forceFill([
                        'status' => 'APPROVE',
                        'pdf_path' => $pdfPath,
                    ])->save();

                    Notification::make()
                        ->title('Surat berhasil di-accept.')
                        ->body('PDF surat berhasil dibuat dan disimpan.')
                        ->success()
                        ->send();
                } catch (Throwable $exception) {
                    Notification::make()
                        ->title('Gagal generate PDF')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

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
            ->requiresConfirmation()
            ->modalHeading('Reject pengajuan surat?')
            ->modalDescription('Status pengajuan akan diubah menjadi reject.')
            ->visible(fn (Model $record): bool => $record->getAttribute('status') === 'SUBMITTED')
            ->action(function (Model $record) {
                try {
                    $record->forceFill([
                        'status' => 'REJECT',
                    ])->save();

                    Notification::make()
                        ->title('Pengajuan berhasil di-reject.')
                        ->success()
                        ->send();
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

    private static function resolveService(Model $record): UniversalLetterService
    {
        $definition = app(DocumentVerificationService::class)->definitionForLetter($record);

        /** @var UniversalLetterService $service */
        $service = app($definition['service']);

        return $service;
    }
}
