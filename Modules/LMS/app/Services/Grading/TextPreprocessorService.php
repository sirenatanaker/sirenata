<?php

namespace Modules\LMS\Services\Grading;

class TextPreprocessorService
{
    /**
     * Jalankan pipeline pembersihan penuh sebelum masuk antrean AI:
     * normalisasi -> template stripping -> normalisasi ulang -> hard cap.
     *
     * @return array{text: string, is_truncated: bool, original_length: int}
     */
    public function preprocess(string $rawStudentText, ?string $templateText = null, ?int $maxChars = null): array
    {
        $normalized = $this->normalize($rawStudentText);

        if (!empty($templateText)) {
            $normalized = $this->stripTemplate($normalized, $templateText);
            $normalized = $this->normalize($normalized);
        }

        return $this->applyHardCap($normalized, $maxChars);
    }

    /**
     * Hapus baris/kalimat pada teks peserta yang identik 100% dengan teks template Admin
     * (kop instansi, instruksi soal, cover standar, header/footer berulang).
     */
    public function stripTemplate(string $studentText, ?string $templateText): string
    {
        if (trim((string) $templateText) === '') {
            return $studentText;
        }

        $templateLines = $this->splitNormalizedLines($templateText);
        $templateLineLookup = array_flip($templateLines);

        // Kumpulkan juga kalimat template (>= 15 karakter) untuk mendeteksi blok instruksi inline
        $templateSentences = $this->splitNormalizedSentences($templateText);

        $lines = preg_split('/\R/u', $studentText) ?: [];
        $keptLines = [];

        foreach ($lines as $line) {
            $normLine = $this->canonicalLine($line);
            if ($normLine === '') {
                $keptLines[] = '';
                continue;
            }

            // Buang jika baris identik 100% dengan salah satu baris template
            if (isset($templateLineLookup[$normLine])) {
                continue;
            }

            // Hapus kalimat inline yang identik 100% dengan kalimat template
            $cleanedLine = $line;
            foreach ($templateSentences as $tplSentence) {
                $pattern = '/' . preg_quote($tplSentence, '/') . '/iu';
                $cleanedLine = preg_replace($pattern, '', $cleanedLine) ?? $cleanedLine;
            }

            if (trim($cleanedLine) !== '') {
                $keptLines[] = trim($cleanedLine);
            }
        }

        return implode("\n", $keptLines);
    }

    /**
     * Normalisasi spasi, tab berlebih, nomor halaman berulang, dan baris kosong berulang.
     */
    public function normalize(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Hapus karakter kontrol tak terlihat selain newline & tab
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? $text;

        $lines = explode("\n", $text);
        $cleaned = [];

        foreach ($lines as $line) {
            // Gabungkan tab dan spasi ganda dalam satu baris
            $line = preg_replace('/[ \t]+/u', ' ', trim($line)) ?? '';

            // Hapus baris nomor halaman standar: "Halaman 1 dari 10", "Page 2 of 5", "- 3 -", atau angka berdiri sendiri
            if ($this->isPageNumberLine($line)) {
                continue;
            }

            $cleaned[] = $line;
        }

        $result = implode("\n", $cleaned);

        // Gabungkan baris kosong berulang (maksimal 1 baris kosong beruntun)
        $result = preg_replace("/\n{3,}/", "\n\n", $result) ?? $result;

        return trim($result);
    }

    /**
     * Terapkan batas aman maksimal karakter per request dengan pemotongan terkontrol.
     *
     * @return array{text: string, is_truncated: bool, original_length: int}
     */
    public function applyHardCap(string $text, ?int $maxChars = null): array
    {
        $limit = $maxChars ?? (int) config('grading.preprocessing.text_max_chars', 25000);
        $originalLength = mb_strlen($text);

        if ($limit <= 0 || $originalLength <= $limit) {
            return [
                'text'            => $text,
                'is_truncated'    => false,
                'original_length' => $originalLength,
            ];
        }

        $slice = mb_substr($text, 0, $limit);

        // Potong di batas spasi / baris terdekat agar kata terakhir tidak terpenggal
        $lastBreak = max((int) mb_strrpos($slice, "\n"), (int) mb_strrpos($slice, ' '));
        if ($lastBreak > (int) ($limit * 0.85)) {
            $slice = mb_substr($slice, 0, $lastBreak);
        }

        return [
            'text'            => rtrim($slice),
            'is_truncated'    => true,
            'original_length' => $originalLength,
        ];
    }

    private function isPageNumberLine(string $line): bool
    {
        if ($line === '') {
            return false;
        }

        return (bool) preg_match(
            '/^(?:(?:halaman|hal\.?|page|pg\.?)\s*\d+(?:\s*(?:dari|of|\/)\s*\d+)?|[-–—]?\s*\d{1,4}\s*[-–—]?|\d{1,4}\s*\/\s*\d{1,4})$/iu',
            $line
        );
    }

    /**
     * @return array<int, string>
     */
    private function splitNormalizedLines(string $text): array
    {
        $lines = preg_split('/\R/u', $this->normalize($text)) ?: [];
        $result = [];

        foreach ($lines as $line) {
            $canonical = $this->canonicalLine($line);
            if ($canonical !== '') {
                $result[] = $canonical;
            }
        }

        return array_values(array_unique($result));
    }

    /**
     * @return array<int, string>
     */
    private function splitNormalizedSentences(string $text): array
    {
        $normalized = $this->normalize($text);
        $rawParts = preg_split('/(?<=[\.\!\?])\s+|\R+/u', $normalized) ?: [];
        $sentences = [];

        foreach ($rawParts as $part) {
            $trimmed = trim(preg_replace('/[ \t]+/u', ' ', $part) ?? '');
            if (mb_strlen($trimmed) >= 20) {
                $sentences[] = $trimmed;
            }
        }

        return array_values(array_unique($sentences));
    }

    private function canonicalLine(string $line): string
    {
        $clean = preg_replace('/\s+/u', ' ', trim($line)) ?? '';

        return mb_strtolower($clean);
    }
}
