<?php

namespace Tests\Unit\Grading;

use Modules\LMS\Services\Grading\DocumentExtractorService;
use Modules\LMS\Services\Grading\TextPreprocessorService;
use Tests\TestCase;

class TextPreprocessorServiceTest extends TestCase
{
    public function test_normalize_strips_extra_whitespace_page_numbers_and_blank_lines(): void
    {
        $service = new TextPreprocessorService();

        $raw = "  Bab   1\t\tPendahuluan  \r\n\r\n\r\n\r\nHalaman 1 dari 10\n- 2 -\nPage 3 of 10\nIsi   laporan\tutama   peserta.\n\n\n\n12\nKesimpulan akhir.";
        $clean = $service->normalize($raw);

        $this->assertStringNotContainsString('Halaman 1 dari 10', $clean);
        $this->assertStringNotContainsString('Page 3 of 10', $clean);
        $this->assertStringNotContainsString('- 2 -', $clean);
        $this->assertStringContainsString("Bab 1 Pendahuluan\n\nIsi laporan utama peserta.\n\nKesimpulan akhir.", $clean);
    }

    public function test_template_stripping_removes_identical_template_lines_and_keeps_student_content(): void
    {
        $service = new TextPreprocessorService();

        $template = <<<TEXT
KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA
LEMBAR KERJA BIMBINGAN TEKNIS PERENCANAAN TENAGA KERJA
Petunjuk: Isi seluruh bagian analisis secara sistematis berdasarkan data daerah Anda.
TEXT;

        $student = <<<TEXT
KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA
LEMBAR KERJA BIMBINGAN TEKNIS PERENCANAAN TENAGA KERJA
Petunjuk: Isi seluruh bagian analisis secara sistematis berdasarkan data daerah Anda.

Hasil Analisis Wilayah Jawa Barat:
Proyeksi kebutuhan tenaga kerja sektor manufaktur meningkat 4,2% pada tahun 2026.
TEXT;

        $result = $service->preprocess($student, $template, 25000);

        $this->assertFalse($result['is_truncated']);
        $this->assertStringNotContainsString('KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA', $result['text']);
        $this->assertStringNotContainsString('LEMBAR KERJA BIMBINGAN TEKNIS', $result['text']);
        $this->assertStringContainsString('Hasil Analisis Wilayah Jawa Barat:', $result['text']);
        $this->assertStringContainsString('meningkat 4,2%', $result['text']);
    }

    public function test_hard_cap_truncates_and_sets_flag(): void
    {
        $service = new TextPreprocessorService();

        $longText = str_repeat('Analisis ketenagakerjaan daerah tahun 2026 sangat strategis. ', 20);
        $capped = $service->applyHardCap($longText, 200);

        $this->assertTrue($capped['is_truncated']);
        $this->assertLessThanOrEqual(200, mb_strlen($capped['text']));
        $this->assertGreaterThan(200, $capped['original_length']);
    }

    public function test_scanned_pdf_validation_rejects_short_or_sparse_text(): void
    {
        $extractor = new DocumentExtractorService();

        // Kasus 1: total karakter < 100
        $shortCheck = $extractor->validateNotScannedPdf('Dokumen hasil scan', 1);
        $this->assertFalse($shortCheck['valid']);
        $this->assertNotNull($shortCheck['message']);

        // Kasus 2: total karakter >= 100 tetapi tersebar di 5 halaman (rata-rata < 50 karakter/halaman)
        $sparseText = str_repeat('Kata ', 30); // ~150 chars across 5 pages = 30 chars/page
        $sparseCheck = $extractor->validateNotScannedPdf($sparseText, 5);
        $this->assertFalse($sparseCheck['valid']);

        // Kasus 3: PDF berbasis teks yang sah
        $validText = str_repeat('Laporan analisis perencanaan tenaga kerja makro berdasarkan data BPS. ', 5);
        $validCheck = $extractor->validateNotScannedPdf($validText, 2);
        $this->assertTrue($validCheck['valid']);
        $this->assertNull($validCheck['message']);
    }
}
