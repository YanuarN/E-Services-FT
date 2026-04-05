<?php

namespace App\Services\Letters;

use App\Models\LetterTemplate;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

abstract class UniversalLetterService
{
    final public function generatePdf(Model $letter): string
    {
        $this->assertSupportedModel($letter);

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

        return [
            'nomor_surat' => (string) ($letter->getAttribute('letter_number') ?? ''),
            'no_surat' => (string) ($letter->getAttribute('letter_number') ?? ''),
            'tanggal_surat' => $this->formatDate($letterDate),
            'tanggal' => $this->formatDate($letterDate),
            'hari' => $letterDate->locale('id')->translatedFormat('l'),
            'bulan' => $letterDate->locale('id')->translatedFormat('F'),
            'tahun' => (string) $letterDate->year,
            'status' => (string) ($letter->getAttribute('status') ?? ''),
            'public_token' => (string) ($letter->getAttribute('public_token') ?? ''),
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
                'nama_mahasiswa' => $person['name'],
                'anggota_nim' => $person['nim'],
                'mahasiswa_nim' => $person['nim'],
                'nim' => $person['nim'],
                'anggota_prodi' => $person['study_program'],
                'mahasiswa_prodi' => $person['study_program'],
                'program_studi' => $person['study_program'],
                'prodi' => $person['study_program'],
            ];
        }, $people);
    }

    protected function buildPeopleSummary(array $people): string
    {
        return collect($people)
            ->map(function (array $person): string {
                $segments = array_filter([
                    $person['name'] ?? '',
                    $person['nim'] ?? '',
                    $person['study_program'] ?? '',
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

    private function buildWordDocument(Model $letter, ?LetterTemplate $template = null): string
    {
        $template ??= $this->resolveTemplate();

        $templatePath = $this->resolveTemplatePath($template);
        $processor = new TemplateProcessor($templatePath);

        $this->applyRowCollections($processor, $this->buildRowCollections($letter));

        foreach ($this->buildTemplatePayload($letter) as $key => $value) {
            $processor->setValue($key, $this->normalizePlaceholderValue($value));
        }

        $temporaryWordPath = $this->temporaryFilePath($this->getWordFilename($letter));
        $processor->saveAs($temporaryWordPath);

        return $temporaryWordPath;
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
