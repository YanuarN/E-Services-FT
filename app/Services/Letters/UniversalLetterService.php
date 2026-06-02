<?php

namespace App\Services\Letters;

use App\Models\LetterTemplate;
use App\Services\QrCodeService;
use Carbon\CarbonInterface;
use IntlCalendar;
use IntlDateFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

abstract class UniversalLetterService
{
    final public function ensureTemplateReady(): void
    {
        $template = $this->resolveTemplate();

        $this->resolveTemplatePath($template);
    }

    final public function generatePdf(Model $letter): string
    {
        $this->assertSupportedModel($letter);

        $existingPdfPath = trim((string) ($letter->getAttribute('pdf_path') ?? ''));

        if ($existingPdfPath !== '') {
            return $existingPdfPath;
        }

        $template = $this->resolveTemplate();
        $temporaryWordPath = $this->buildWordDocument($letter, $template);

        try {
            $temporaryPdfPath = $this->convertDocxToPdf($temporaryWordPath);

            try {
                $storedPdfPath = $this->storeGeneratedPdf($letter, $temporaryPdfPath);
            } finally {
                $this->cleanupTemporaryFile($temporaryPdfPath);
            }

            $this->persistPdfPath($letter, $storedPdfPath);

            return $storedPdfPath;
        } finally {
            $this->cleanupTemporaryFile($temporaryWordPath);
        }
    }

    final public function getPdfFilename(Model $letter): string
    {
        return $this->buildFilename($letter, 'pdf');
    }

    final public function getWordFilename(Model $letter): string
    {
        return $this->buildFilename($letter, 'docx');
    }

    abstract protected function letterType(): string;

    abstract protected function modelClass(): string;

    abstract protected function buildTemplatePayload(Model $letter): array;

    abstract protected function buildFilenameParts(Model $letter): array;

    protected function buildRowCollections(Model $letter): array
    {
        return [];
    }

    protected function templateDisk(): string
    {
        return 'local';
    }

    protected function outputDisk(): string
    {
        return 'local';
    }

    protected function baseLetterPayload(Model $letter): array
    {
        $letterDate = $this->resolveDate($letter->getAttribute('letter_date')) ?? now();
        $hijriLetterDate = $this->formatHijriDate($letterDate);
        $publicToken = app(DocumentVerificationService::class)->ensurePublicToken($letter);

        return [
            'nomor_surat' => (string) ($letter->getAttribute('letter_number') ?? ''),
            'no_surat' => (string) ($letter->getAttribute('letter_number') ?? ''),
            'nomor_permohonan' => (string) ($letter->getAttribute('letter_number') ?? ''),
            'tanggal_surat' => $this->formatDate($letterDate),
            'tanggal' => $this->formatDate($letterDate),
            'tanggal_hijriah' => $hijriLetterDate,
            'hari' => $letterDate->locale('id')->translatedFormat('l'),
            'bulan' => $letterDate->locale('id')->translatedFormat('F'),
            'tahun' => (string) $letterDate->year,
            'status' => (string) ($letter->getAttribute('status') ?? ''),
            'public_token' => $publicToken,
            'verification_url' => $this->buildVerificationUrl($letter),
        ];
    }

    protected function studentIdentityPayload(
        ?string $name,
        ?string $nim,
        ?string $studyProgram = null,
        ?string $phoneNumber = null
    ): array {
        return [
            'nama_mahasiswa' => $name,
            'nim' => $nim,
            'program_studi' => $studyProgram,
            'prodi' => $studyProgram,
            'nomor_telepon' => $phoneNumber,
            'no_hp' => $phoneNumber,
        ];
    }

    protected function formatDate(null|string|CarbonInterface $value, string $format = 'd F Y'): string
    {
        $date = $this->resolveDate($value);

        if (! $date) {
            return '';
        }

        return $date->locale('id')->translatedFormat($format);
    }

    protected function formatTime(null|string|CarbonInterface $value, string $format = 'H:i'): string
    {
        $date = $this->resolveDate($value);

        return $date?->format($format) ?? '';
    }

    protected function formatHijriDate(null|string|CarbonInterface $value, string $pattern = 'd MMMM y G'): string
    {
        $date = $this->resolveDate($value);

        if (! $date || ! class_exists(IntlCalendar::class) || ! class_exists(IntlDateFormatter::class)) {
            return '';
        }

        $calendar = IntlCalendar::createInstance(
            $date->getTimezone()->getName(),
            'id_ID@calendar=islamic'
        );

        if (! $calendar) {
            return '';
        }

        $calendar->setTime($date->valueOf());

        $formatter = new IntlDateFormatter(
            'id_ID@calendar=islamic',
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE,
            $date->getTimezone()->getName(),
            $calendar,
            $pattern,
        );

        $formattedDate = $formatter->format($calendar);

        return is_string($formattedDate) ? $formattedDate : '';
    }

    protected function normalizePeople(array $people): array
    {
        $normalized = [];

        foreach (array_values($people) as $index => $person) {
            if (is_string($person)) {
                $normalized[] = [
                    'no' => $index + 1,
                    'name' => $person,
                    'nim' => '',
                    'study_program' => '',
                    'phone_number' => '',
                ];

                continue;
            }

            if (! is_array($person)) {
                continue;
            }

            $normalized[] = [
                'no' => $index + 1,
                'name' => $this->firstFilledValue($person, ['nama_mahasiswa', 'nama', 'name', 'student_name']) ?? '',
                'nim' => $this->firstFilledValue($person, ['nim', 'student_nim', 'mahasiswa_nim']) ?? '',
                'study_program' => $this->firstFilledValue($person, ['program_studi', 'prodi', 'study_program']) ?? '',
                'phone_number' => $this->firstFilledValue($person, ['nomor_telepon', 'no_hp', 'phone_number', 'phone', 'whatsapp']) ?? '',
            ];
        }

        return $normalized;
    }

    protected function buildMemberRows(array $people): array
    {
        return array_map(function (array $person): array {
            return [
                'anggota_no' => $person['no'],
                'mahasiswa_no' => $person['no'],
                'm_no' => $person['no'],
                'nama_mahasiswa' => $person['name'],
                'm_nama' => $person['name'],
                'anggota_nim' => $person['nim'],
                'mahasiswa_nim' => $person['nim'],
                'nim' => $person['nim'],
                'm_nim' => $person['nim'],
                'anggota_prodi' => $person['study_program'],
                'mahasiswa_prodi' => $person['study_program'],
                'program_studi' => $person['study_program'],
                'prodi' => $person['study_program'],
                'm_prodi' => $person['study_program'],
                'anggota_nomor_telepon' => $person['phone_number'],
                'anggota_no_hp' => $person['phone_number'],
                'mahasiswa_nomor_telepon' => $person['phone_number'],
                'mahasiswa_no_hp' => $person['phone_number'],
                'm_nomor_telepon' => $person['phone_number'],
                'm_no_hp' => $person['phone_number'],
            ];
        }, $people);
    }

    protected function buildMemberRowCollection(array $people): array
    {
        return $this->buildRowCollection(
            [
                'anggota_no',
                'mahasiswa_no',
                'm_no',
                'm_nama',
                'm_nim',
                'm_prodi',
                'anggota_nomor_telepon',
                'anggota_no_hp',
                'mahasiswa_nomor_telepon',
                'mahasiswa_no_hp',
                'm_nomor_telepon',
                'm_no_hp',
            ],
            $this->buildMemberRows($people),
            [
                'anggota_no',
                'mahasiswa_no',
                'm_no',
                'nama_mahasiswa',
                'm_nama',
                'anggota_nim',
                'mahasiswa_nim',
                'nim',
                'm_nim',
                'anggota_prodi',
                'mahasiswa_prodi',
                'program_studi',
                'prodi',
                'm_prodi',
                'anggota_nomor_telepon',
                'anggota_no_hp',
                'mahasiswa_nomor_telepon',
                'mahasiswa_no_hp',
                'm_nomor_telepon',
                'm_no_hp',
            ],
        );
    }

    protected function buildPeopleSummary(array $people): string
    {
        return collect($people)
            ->map(function (array $person): string {
                $segments = array_filter([
                    $person['name'] ?? '',
                    $person['nim'] ?? '',
                    $person['study_program'] ?? '',
                    $person['phone_number'] ?? '',
                ], fn (mixed $value): bool => filled($value));

                return implode(' - ', $segments);
            })
            ->filter()
            ->implode('; ');
    }

    protected function buildRowCollection(array $anchors, array $rows, array $emptyPlaceholders = []): array
    {
        return [
            'anchors' => $anchors,
            'rows' => $rows,
            'empty_placeholders' => $emptyPlaceholders,
        ];
    }

    final public function buildVerificationData(Model $letter): array
    {
        $this->assertSupportedModel($letter);

        return [
            'title' => $this->letterLabel(),
            'status' => (string) ($letter->getAttribute('status') ?? ''),
            'letterNumber' => $this->normalizePlaceholderValue($letter->getAttribute('letter_number')),
            'letterDate' => $this->normalizePlaceholderValue(
                $this->formatDate($letter->getAttribute('letter_date'))
            ),
            'subject' => $this->letterLabel(),
            'studentName' => $this->resolveVerificationStudentName($letter),
            'documentUrl' => $this->resolveVerificationDocumentUrl($letter),
            'fields' => collect([
                $this->makeVerificationField('Nomor Surat', $letter->getAttribute('letter_number')),
                $this->makeVerificationField('Tanggal Surat', $this->formatDate($letter->getAttribute('letter_date'))),
                $this->makeVerificationField('Hal', $this->letterLabel()),
                $this->makeVerificationField('Nama Mahasiswa', $this->resolveVerificationStudentName($letter)),
                $this->makeVerificationField(
                    'Link Surat',
                    $this->resolveVerificationDocumentUrl($letter) ? 'Tersedia untuk dibuka' : 'Belum tersedia'
                ),
            ])->values()->all(),
        ];
    }

    protected function verificationFields(Model $letter): array
    {
        return [];
    }

    protected function makeVerificationField(string $label, mixed $value): array
    {
        return [
            'label' => $label,
            'value' => $this->normalizePlaceholderValue($value),
        ];
    }

    protected function resolveVerificationStudentName(Model $letter): string
    {
        $name = $this->firstFilledValue(
            $letter->getAttributes(),
            ['student_name', 'name']
        );

        return $this->normalizePlaceholderValue($name);
    }

    protected function resolveVerificationDocumentUrl(Model $letter): ?string
    {
        $pdfPath = (string) ($letter->getAttribute('pdf_path') ?? '');
        $publicToken = (string) ($letter->getAttribute('public_token') ?? '');

        if ($pdfPath === '' || $publicToken === '') {
            return null;
        }

        if (! Route::has('verification.file')) {
            return null;
        }

        return route('verification.file', [
            'letterType' => $this->letterType(),
            'token' => $publicToken,
        ]);
    }

    private function buildWordDocument(Model $letter, ?LetterTemplate $template = null): string
    {
        $template ??= $this->resolveTemplate();

        $templatePath = $this->resolveTemplatePath($template);
        $processor = new TemplateProcessor($templatePath);
        $temporaryQrPath = $this->generateQrCodePath($letter);

        try {
            $this->applyRowCollections($processor, $this->buildRowCollections($letter));
            $this->applyQrCodeImage($processor, $temporaryQrPath);

            foreach ($this->buildTemplatePayload($letter) as $key => $value) {
                $processor->setValue($key, $this->normalizePlaceholderValue($value));
            }

            $temporaryWordPath = $this->temporaryFilePath($this->getWordFilename($letter));
            $processor->saveAs($temporaryWordPath);

            return $temporaryWordPath;
        } finally {
            $this->cleanupTemporaryFile($temporaryQrPath);
        }
    }

    private function resolveTemplate(): LetterTemplate
    {
        return LetterTemplate::query()
            ->where('letter_type', $this->letterType())
            ->firstOr(function (): never {
                throw new RuntimeException(
                    "Template surat untuk tipe '{$this->letterType()}' belum tersedia. "
                    .'Silakan upload template DOCX terlebih dahulu.'
                );
            });
    }

    private function resolveTemplatePath(LetterTemplate $template): string
    {
        $templatePath = Storage::disk($this->templateDisk())->path($template->document_path);

        if (! file_exists($templatePath)) {
            throw new RuntimeException("File template tidak ditemukan di path '{$template->document_path}'.");
        }

        if (strtolower(pathinfo($templatePath, PATHINFO_EXTENSION)) !== 'docx') {
            throw new RuntimeException(
                "Template '{$template->letter_type}' harus berupa file DOCX agar bisa diproses menjadi PDF."
            );
        }

        return $templatePath;
    }

    private function convertDocxToPdf(string $temporaryWordPath): string
    {
        return match (config('services.pdf_converter.driver', 'local')) {
            'http' => $this->convertDocxToPdfViaHttp($temporaryWordPath),
            default => $this->convertDocxToPdfViaLocal($temporaryWordPath),
        };
    }

    private function convertDocxToPdfViaLocal(string $temporaryWordPath): string
    {
        $process = new Process([
            'libreoffice',
            '--headless',
            '--convert-to',
            'pdf',
            '--outdir',
            dirname($temporaryWordPath),
            $temporaryWordPath,
        ]);

        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException(
                'Gagal mengonversi dokumen DOCX ke PDF. '
                .trim($process->getErrorOutput() ?: $process->getOutput())
            );
        }

        $temporaryPdfPath = preg_replace('/\.docx$/i', '.pdf', $temporaryWordPath);

        if (! $temporaryPdfPath || ! file_exists($temporaryPdfPath)) {
            throw new RuntimeException('File PDF hasil konversi tidak ditemukan.');
        }

        return $temporaryPdfPath;
    }

    private function convertDocxToPdfViaHttp(string $temporaryWordPath): string
    {
        $handle = fopen($temporaryWordPath, 'r');

        if ($handle === false) {
            throw new RuntimeException('File DOCX sementara tidak dapat dibaca untuk dikonversi.');
        }

        try {
            $response = Http::timeout(60)
                ->attach('file', $handle, basename($temporaryWordPath))
                ->post(config('services.pdf_converter.url'));
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Gagal mengonversi dokumen DOCX ke PDF melalui HTTP. '.$exception->getMessage(),
                previous: $exception,
            );
        } finally {
            fclose($handle);
        }

        if (! $response->successful()) {
            $message = trim((string) ($response->json('message') ?: $response->body()));

            throw new RuntimeException(
                'Gagal mengonversi dokumen DOCX ke PDF melalui HTTP. '
                .($message !== '' ? $message : 'Layanan konversi mengembalikan respons yang tidak valid.')
            );
        }

        $filename = basename($temporaryWordPath);
        $pdfFilename = preg_replace('/\.docx$/i', '.pdf', $filename) ?: pathinfo($filename, PATHINFO_FILENAME).'.pdf';
        $temporaryPdfPath = $this->temporaryFilePath($pdfFilename);
        $written = file_put_contents($temporaryPdfPath, $response->body());

        if ($written === false) {
            throw new RuntimeException('File PDF hasil konversi tidak dapat ditulis ke penyimpanan sementara.');
        }

        return $temporaryPdfPath;
    }

    private function storeGeneratedPdf(Model $letter, string $temporaryPdfPath): string
    {
        $relativePath = sprintf(
            'generated-letters/%s/%s',
            $this->letterType(),
            $this->getPdfFilename($letter),
        );

        $content = file_get_contents($temporaryPdfPath);

        if ($content === false) {
            throw new RuntimeException('File PDF hasil generate tidak dapat dibaca.');
        }

        Storage::disk($this->outputDisk())->put($relativePath, $content);

        return $relativePath;
    }

    private function persistPdfPath(Model $letter, string $pdfPath): void
    {
        $letter->forceFill([
            'pdf_path' => $pdfPath,
        ])->save();
    }

    private function applyRowCollections(TemplateProcessor $processor, array $collections): void
    {
        foreach ($collections as $collection) {
            $rows = array_values($collection['rows'] ?? []);
            $anchors = array_values($collection['anchors'] ?? []);
            $emptyPlaceholders = array_values($collection['empty_placeholders'] ?? []);

            if ($rows === []) {
                foreach ($emptyPlaceholders as $placeholder) {
                    $processor->setValue($placeholder, '');
                }

                continue;
            }

            $cloned = false;

            foreach ($anchors as $anchor) {
                try {
                    $processor->cloneRowAndSetValues($anchor, $rows);
                    $cloned = true;

                    break;
                } catch (Throwable) {
                    // Try the next placeholder anchor until one matches the uploaded template.
                }
            }

            if ($cloned) {
                continue;
            }

            foreach ($rows[0] as $key => $value) {
                $processor->setValue($key, $this->normalizePlaceholderValue($value));
            }
        }
    }

    private function normalizePlaceholderValue(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $this->formatDate($value);
        }

        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }

        if (is_array($value)) {
            return collect($value)
                ->map(fn (mixed $item): string => is_scalar($item) ? (string) $item : '')
                ->filter()
                ->implode(', ');
        }

        return (string) ($value ?? '');
    }

    private function applyQrCodeImage(TemplateProcessor $processor, ?string $path): void
    {
        if (! $path || ! file_exists($path)) {
            return;
        }

        $processor->setImageValue(['qr_code', 'verification_qr_code'], [
            'path' => $path,
            'width' => 110,
            'height' => 110,
        ]);
    }

    private function buildFilename(Model $letter, string $extension): string
    {
        $parts = collect($this->buildFilenameParts($letter))
            ->map(fn (mixed $part): string => Str::slug((string) $part))
            ->filter()
            ->values()
            ->all();

        $basename = implode('-', $parts);

        if ($basename === '') {
            $basename = sprintf('%s-%s', $this->letterType(), $letter->getKey());
        }

        return "{$basename}.{$extension}";
    }

    private function buildVerificationUrl(Model $letter): string
    {
        return app(DocumentVerificationService::class)
            ->buildVerificationUrl($this->letterType(), $letter);
    }

    private function generateQrCodePath(Model $letter): ?string
    {
        $verificationUrl = $this->buildVerificationUrl($letter);

        if (blank($verificationUrl)) {
            return null;
        }

        return app(QrCodeService::class)->generateLetterQrCode($verificationUrl);
    }

    private function letterLabel(): string
    {
        return app(DocumentVerificationService::class)->letterLabel($this->letterType());
    }

    private function temporaryFilePath(string $filename): string
    {
        $directory = storage_path('app/temp/letters');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return $directory.'/'.Str::uuid().'-'.$filename;
    }

    private function cleanupTemporaryFile(?string $path): void
    {
        if ($path && file_exists($path)) {
            @unlink($path);
        }
    }

    private function assertSupportedModel(Model $letter): void
    {
        $modelClass = $this->modelClass();

        if (! $letter instanceof $modelClass) {
            throw new RuntimeException(sprintf(
                '%s hanya mendukung model %s, %s diberikan.',
                static::class,
                $modelClass,
                $letter::class,
            ));
        }
    }

    private function resolveDate(null|string|CarbonInterface $value): ?Carbon
    {
        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value);
        }

        if (blank($value)) {
            return null;
        }

        return Carbon::parse($value);
    }

    private function firstFilledValue(array $values, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (filled($values[$key] ?? null)) {
                return $values[$key];
            }
        }

        return null;
    }
}
