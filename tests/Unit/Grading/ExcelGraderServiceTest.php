<?php

namespace Tests\Unit\Grading;

use Modules\LMS\Services\Grading\ExcelGraderService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelGraderServiceTest extends TestCase
{
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        parent::tearDown();
    }

    public function test_excel_auto_diff_grades_locally_with_rounding_tolerance(): void
    {
        $templatePath = $this->createXlsx([
            ['Aspek', 'Nilai', 'Status'],
            ['Target A', '', ''],
            ['Target B', '', ''],
        ]);

        $keyPath = $this->createXlsx([
            ['Aspek', 'Nilai', 'Status'],
            ['Target A', 1.25, 'Sesuai Regulasi'],
            ['Target B', 100, 'Tervalidasi'],
        ]);

        // Nilai 1.254 dalam toleransi 0.01 terhadap 1.25; huruf kapital/spasi berbeda
        $studentPath = $this->createXlsx([
            ['Aspek', 'Nilai', 'Status'],
            ['Target A', 1.254, '  sesuai   regulasi '],
            ['Target B', 100, 'TERVALIDASI'],
        ]);

        $grader = new ExcelGraderService();
        $result = $grader->grade($keyPath, $studentPath, $templatePath, ['default_tolerance' => 0.01], 75.0);

        $this->assertEquals(100.0, $result['score']);
        $this->assertTrue($result['is_passed']);
        $this->assertEquals(4, $result['matched_cells']);
        $this->assertEquals(4, $result['total_cells']);
        $this->assertEquals('AUTO_EXCEL', $result['feedback_json']['grader']);
    }

    public function test_excel_explicit_rubric_enforces_formula_inspection_and_weights(): void
    {
        $keyPath = $this->createXlsx([
            ['Input 1', 'Input 2', 'Total'],
            [40, 60, '=SUM(A2:B2)'],
        ]);

        // Peserta 1 memakai rumus =SUM(A2:B2) -> lulus penuh
        $studentWithFormula = $this->createXlsx([
            ['Input 1', 'Input 2', 'Total'],
            [40, 60, '=SUM(A2:B2)'],
        ]);

        // Peserta 2 hanya mengetik angka statis 100 tanpa rumus -> gagal cek rumus
        $studentStaticValue = $this->createXlsx([
            ['Input 1', 'Input 2', 'Total'],
            [40, 60, 100],
        ]);

        $rubric = [
            'cells' => [
                [
                    'cell'             => 'C2',
                    'weight'           => 100,
                    'tolerance'        => 0.01,
                    'require_formula'  => true,
                    'expected_formula' => 'SUM',
                ],
            ],
        ];

        $grader = new ExcelGraderService();

        $passRes = $grader->grade($keyPath, $studentWithFormula, null, $rubric, 75.0);
        $this->assertEquals(100.0, $passRes['score']);
        $this->assertTrue($passRes['is_passed']);

        $failRes = $grader->grade($keyPath, $studentStaticValue, null, $rubric, 75.0);
        $this->assertEquals(0.0, $failRes['score']);
        $this->assertFalse($failRes['is_passed']);
        $this->assertStringContainsString('rumus Excel', $failRes['feedback_json']['criteria_breakdown'][0]['reason']);
    }

    private function createXlsx(array $rows): string
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lms_excel_test_' . uniqid() . '.xlsx';
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A1');
        (new Xlsx($spreadsheet))->save($path);
        $this->tempFiles[] = $path;

        return $path;
    }
}
