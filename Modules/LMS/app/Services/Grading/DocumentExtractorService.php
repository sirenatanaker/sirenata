<?php

namespace Modules\LMS\Services\Grading;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentExtractorService
{
    /**
     * Ekstrak teks dan jumlah halaman dari berkas lokal (PDF, DOCX, TXT).
     *
     * @return array{text: string, page_count: int, extension: string}
     */
    public function extractFromPath(string $fullPath): array
    {
        if (!is_file($fullPath) || !is_readable($fullPath)) {
            return [
                'text'       => '',
                'page_count' => 0,
                'extension'  => strtolower(pathinfo($fullPath, PATHINFO_EXTENSION)),
            ];
        }

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf'   => $this->extractPdf($fullPath),
            'docx'  => $this->extractDocx($fullPath),
            'txt'   => [
                'text'       => (string) @file_get_contents($fullPath),
                'page_count' => 1,
                'extension'  => 'txt',
            ],
            default => [
                'text'       => '',
                'page_count' => 0,
                'extension'  => $ext,
            ],
        };
    }

    /**
     * Ekstrak dari path relatif pada disk public.
     *
     * @return array{text: string, page_count: int, extension: string}
     */
    public function extractFromPublicDisk(string $relativePath): array
    {
        if (!Storage::disk('public')->exists($relativePath)) {
            return [
                'text'       => '',
                'page_count' => 0,
                'extension'  => strtolower(pathinfo($relativePath, PATHINFO_EXTENSION)),
            ];
        }

        return $this->extractFromPath(Storage::disk('public')->path($relativePath));
    }

    /**
     * Deteksi PDF hasil scan/gambar di pintu masuk sebelum masuk antrean AI.
     *
     * @return array{valid: bool, message: ?string, char_count: int, chars_per_page: float}
     */
    public function validateNotScannedPdf(string $cleanText, int $pageCount): array
    {
        $minChars = (int) config('grading.preprocessing.pdf_min_chars', 100);
        $minCharsPerPage = (int) config('grading.preprocessing.pdf_min_chars_per_page', 50);

        $compact = preg_replace('/\s+/u', ' ', trim($cleanText)) ?? '';
        $charCount = mb_strlen($compact);
        $effectivePages = max(1, $pageCount);
        $charsPerPage = round($charCount / $effectivePages, 2);

        if ($charCount < $minChars || $charsPerPage < $minCharsPerPage) {
            return [
                'valid'          => false,
                'message'        => "Dokumen PDF terdeteksi sebagai hasil scan/gambar atau tidak memiliki teks memadai ({$charCount} karakter, rata-rata {$charsPerPage} karakter/halaman). Mohon unggah berkas PDF berbasis teks (hasil Export / Save as PDF dari Word/Google Docs).",
                'char_count'     => $charCount,
                'chars_per_page' => $charsPerPage,
            ];
        }

        return [
            'valid'          => true,
            'message'        => null,
            'char_count'     => $charCount,
            'chars_per_page' => $charsPerPage,
        ];
    }

    /**
     * @return array{text: string, page_count: int, extension: string}
     */
    private function extractPdf(string $fullPath): array
    {
        $text = '';
        $pageCount = 0;

        if (class_exists(\Smalot\PdfParser\Parser::class)) {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($fullPath);
                $pages = $pdf->getPages();
                $pageCount = count($pages);

                $pageTexts = [];
                foreach ($pages as $page) {
                    $pText = trim((string) $page->getText());
                    if ($pText !== '') {
                        $pageTexts[] = $pText;
                    }
                }
                $text = implode("\n\n", $pageTexts);

                if (trim($text) === '') {
                    $text = trim((string) $pdf->getText());
                }
            } catch (\Throwable $e) {
                Log::warning('DocumentExtractorService smalot/pdfparser fallback: ' . $e->getMessage());
            }
        }

        if (trim($text) === '') {
            $fallback = $this->extractPdfRawFallback($fullPath);
            $text = $fallback['text'];
            if ($pageCount === 0) {
                $pageCount = $fallback['page_count'];
            }
        }

        return [
            'text'       => trim($text),
            'page_count' => max(1, $pageCount),
            'extension'  => 'pdf',
        ];
    }

    /**
     * @return array{text: string, page_count: int}
     */
    private function extractPdfRawFallback(string $fullPath): array
    {
        $raw = (string) @file_get_contents($fullPath);
        if ($raw === '') {
            return ['text' => '', 'page_count' => 0];
        }

        $pageCount = max(1, preg_match_all('/\/Type\s*\/Page\b/', $raw));
        $chunks = [];

        if (preg_match_all('/stream[\r\n]+(.*?)[\r\n]*endstream/s', $raw, $streams)) {
            foreach ($streams[1] as $streamData) {
                $decoded = @gzuncompress($streamData);
                if ($decoded === false) {
                    $decoded = $streamData;
                }
                if (preg_match_all('/\(([^()]*)\)\s*Tj/s', $decoded, $tjMatches)) {
                    $chunks = array_merge($chunks, $tjMatches[1]);
                }
                if (preg_match_all('/\[(.*?)\]\s*TJ/s', $decoded, $tjArrMatches)) {
                    foreach ($tjArrMatches[1] as $arrContent) {
                        if (preg_match_all('/\(([^()]*)\)/', $arrContent, $inner)) {
                            $chunks = array_merge($chunks, $inner[1]);
                        }
                    }
                }
            }
        }

        $joined = trim(implode(' ', array_map(fn($c) => stripcslashes($c), $chunks)));

        return [
            'text'       => $joined,
            'page_count' => $pageCount,
        ];
    }

    /**
     * @return array{text: string, page_count: int, extension: string}
     */
    private function extractDocx(string $fullPath): array
    {
        if (!class_exists(\ZipArchive::class)) {
            return ['text' => '', 'page_count' => 1, 'extension' => 'docx'];
        }

        $zip = new \ZipArchive();
        if ($zip->open($fullPath) !== true) {
            return ['text' => '', 'page_count' => 1, 'extension' => 'docx'];
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            return ['text' => '', 'page_count' => 1, 'extension' => 'docx'];
        }

        $xml = str_replace(['</w:p>', '</w:tr>', '</w:tc>'], ["\n", "\n", ' '], $xml);
        $text = trim(html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8'));

        return [
            'text'       => $text,
            'page_count' => 1,
            'extension'  => 'docx',
        ];
    }
}
