<?php

namespace Modules\LMS\Services\Grading;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Penilaian 100% lokal untuk berkas Excel (.xlsx / .xls / .csv) — 0 kuota AI.
 * Mendukung toleransi pembulatan desimal, bobot per sel, dan inspeksi rumus Excel.
 */
class ExcelGraderService
{
    /**
     * Validasi struktur sheet dan proteksi file Excel saat peserta mengunggah berkas.
     *
     * @return array{valid: bool, message: ?string, sheet_names: array<int, string>}
     */
    public function validateExcelFile(string $fullPath): array
    {
        if (!is_file($fullPath) || !is_readable($fullPath)) {
            return [
                'valid'       => false,
                'message'     => 'Berkas Excel tidak dapat dibaca oleh server.',
                'sheet_names' => [],
            ];
        }

        try {
            // Cek apakah berkas xlsx terenkripsi password (Compound Binary EncryptedPackage)
            $head = (string) @file_get_contents($fullPath, false, null, 0, 8);
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            if ($ext === 'xlsx' && str_starts_with($head, "\xD0\xCF\x11\xE0")) {
                return [
                    'valid'       => false,
                    'message'     => 'Berkas Excel terproteksi password. Mohon hilangkan proteksi kata sandi sebelum mengunggah.',
                    'sheet_names' => [],
                ];
            }

            $spreadsheet = IOFactory::load($fullPath);
            $sheetNames = $spreadsheet->getSheetNames();

            if (empty($sheetNames)) {
                return [
                    'valid'       => false,
                    'message'     => 'Berkas Excel tidak memiliki lembar kerja (sheet) yang valid.',
                    'sheet_names' => [],
                ];
            }

            return [
                'valid'       => true,
                'message'     => null,
                'sheet_names' => $sheetNames,
            ];
        } catch (\Throwable $e) {
            return [
                'valid'       => false,
                'message'     => 'Berkas Excel rusak atau tidak valid: ' . $e->getMessage(),
                'sheet_names' => [],
            ];
        }
    }

    /**
     * Nilai berkas Excel peserta terhadap berkas kunci Admin secara 100% lokal.
     *
     * @param  array<string, mixed>  $rubricConfig
     * @return array{
     *   score: float,
     *   is_passed: bool,
     *   matched_cells: int,
     *   total_cells: int,
     *   mismatched_cells: array<int, string>,
     *   feedback_json: array<string, mixed>,
     *   summary_note: string
     * }
     */
    public function grade(
        string $keyFullPath,
        string $studentFullPath,
        ?string $templateFullPath = null,
        array $rubricConfig = [],
        float $passingGrade = 75.0
    ): array {
        $keySpreadsheet = IOFactory::load($keyFullPath);
        $studentSpreadsheet = IOFactory::load($studentFullPath);

        $tplSpreadsheet = null;
        if ($templateFullPath && is_file($templateFullPath)) {
            $tplExt = strtolower(pathinfo($templateFullPath, PATHINFO_EXTENSION));
            if (in_array($tplExt, ['xlsx', 'xls', 'csv'], true)) {
                $tplSpreadsheet = IOFactory::load($templateFullPath);
            }
        }

        $defaultTolerance = isset($rubricConfig['default_tolerance'])
            ? (float) $rubricConfig['default_tolerance']
            : 0.01;

        $explicitCells = is_array($rubricConfig['cells'] ?? null) ? $rubricConfig['cells'] : [];

        if (!empty($explicitCells)) {
            return $this->gradeWithExplicitCells(
                $keySpreadsheet,
                $studentSpreadsheet,
                $explicitCells,
                $defaultTolerance,
                $passingGrade
            );
        }

        return $this->gradeWithTemplateDiff(
            $keySpreadsheet,
            $studentSpreadsheet,
            $tplSpreadsheet,
            $defaultTolerance,
            $passingGrade
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $explicitCells
     */
    private function gradeWithExplicitCells(
        \PhpOffice\PhpSpreadsheet\Spreadsheet $keySpreadsheet,
        \PhpOffice\PhpSpreadsheet\Spreadsheet $studentSpreadsheet,
        array $explicitCells,
        float $defaultTolerance,
        float $passingGrade
    ): array {
        $totalWeight = 0.0;
        $awardedWeight = 0.0;
        $matchedCells = 0;
        $mismatchedCoords = [];
        $breakdown = [];

        foreach ($explicitCells as $rule) {
            $coord = strtoupper(trim((string) ($rule['cell'] ?? '')));
            if ($coord === '') {
                continue;
            }

            $sheetName = $rule['sheet'] ?? null;
            $weight = (float) ($rule['weight'] ?? 1);
            $tolerance = isset($rule['tolerance']) ? (float) $rule['tolerance'] : $defaultTolerance;
            $requireFormula = (bool) ($rule['require_formula'] ?? false);
            $expectedFormula = isset($rule['expected_formula']) ? trim((string) $rule['expected_formula']) : null;

            $keySheet = $sheetName
                ? ($keySpreadsheet->getSheetByName($sheetName) ?? $keySpreadsheet->getSheet(0))
                : $keySpreadsheet->getSheet(0);

            $studentSheet = $sheetName
                ? ($studentSpreadsheet->getSheetByName($sheetName) ?? $studentSpreadsheet->getSheet(0))
                : $studentSpreadsheet->getSheet(0);

            $expectedCalc = $this->readCalculatedValue($keySheet, $coord);
            $actualCalc = $studentSheet ? $this->readCalculatedValue($studentSheet, $coord) : '';
            $actualRaw = $studentSheet ? (string) $studentSheet->getCell($coord)->getValue() : '';

            $valueMatched = $this->valuesMatch(
                $this->normalizeValue($expectedCalc),
                $this->normalizeValue($actualCalc),
                $tolerance
            );

            $formulaOk = true;
            $formulaReason = null;
            if ($requireFormula) {
                $isFormula = str_starts_with(trim($actualRaw), '=');
                if (!$isFormula) {
                    $formulaOk = false;
                    $formulaReason = 'Wajib menggunakan rumus Excel (=...), ditemukan nilai statis.';
                } elseif ($expectedFormula && !str_contains(strtoupper($actualRaw), strtoupper($expectedFormula))) {
                    $formulaOk = false;
                    $formulaReason = "Rumus tidak memuat fungsi yang diharapkan ({$expectedFormula}).";
                }
            }

            $cellPassed = $valueMatched && $formulaOk;
            $totalWeight += $weight;

            if ($cellPassed) {
                $awardedWeight += $weight;
                $matchedCells++;
            } else {
                $labelCoord = $sheetName ? "{$sheetName}!{$coord}" : $coord;
                $mismatchedCoords[] = $labelCoord;
            }

            $breakdown[] = [
                'criterion_name' => ($sheetName ? "{$sheetName}!" : '') . $coord,
                'max_score'      => round($weight, 2),
                'awarded_score'  => $cellPassed ? round($weight, 2) : 0.0,
                'expected'       => (string) $expectedCalc,
                'actual'         => (string) $actualCalc,
                'formula_used'   => str_starts_with(trim($actualRaw), '=') ? $actualRaw : null,
                'reason'         => $cellPassed
                    ? 'Nilai sel dan rumus sudah sesuai kunci jawaban.'
                    : ($formulaReason ?? (trim((string) $actualCalc) === ''
                        ? 'Sel masih kosong, belum diisi oleh peserta.'
                        : "Isian peserta ('" . (string) $actualCalc . "') belum sesuai dengan jawaban kunci ('" . (string) $expectedCalc . "').")),
            ];
        }

        $totalCells = count($breakdown);
        $score = $totalWeight > 0 ? round(($awardedWeight / $totalWeight) * 100, 2) : 0.0;
        $isPassed = $score >= $passingGrade;

        $summary = $this->buildSummary($matchedCells, $totalCells, $score, $passingGrade, $mismatchedCoords);

        return [
            'score'            => $score,
            'is_passed'        => $isPassed,
            'matched_cells'    => $matchedCells,
            'total_cells'      => $totalCells,
            'mismatched_cells' => $mismatchedCoords,
            'feedback_json'    => [
                'grader'                    => 'AUTO_EXCEL',
                'confidence_level'          => 'high',
                'needs_human_review'        => false,
                'prompt_injection_detected' => false,
                'matched_cells'             => $matchedCells,
                'total_cells'               => $totalCells,
                'criteria_breakdown'        => $breakdown,
                'overall_feedback'          => $summary,
            ],
            'summary_note'     => $summary,
        ];
    }

    private function gradeWithTemplateDiff(
        \PhpOffice\PhpSpreadsheet\Spreadsheet $keySpreadsheet,
        \PhpOffice\PhpSpreadsheet\Spreadsheet $studentSpreadsheet,
        ?\PhpOffice\PhpSpreadsheet\Spreadsheet $tplSpreadsheet,
        float $defaultTolerance,
        float $passingGrade
    ): array {
        $targets = [];

        foreach ($keySpreadsheet->getAllSheets() as $sheetIdx => $keySheet) {
            $sheetTitle = $keySheet->getTitle();
            $tplSheet = null;
            if ($tplSpreadsheet) {
                $tplSheet = $tplSpreadsheet->getSheetByName($sheetTitle)
                    ?? ($tplSpreadsheet->getSheetCount() > $sheetIdx ? $tplSpreadsheet->getSheet($sheetIdx) : null);
            }

            $highestRow = $keySheet->getHighestDataRow();
            $highestColIndex = Coordinate::columnIndexFromString($keySheet->getHighestDataColumn());

            $diffTargets = [];
            $allTargets = [];

            for ($row = 1; $row <= $highestRow; $row++) {
                for ($col = 1; $col <= $highestColIndex; $col++) {
                    $coord = Coordinate::stringFromColumnIndex($col) . $row;
                    $keyRawFormula = (string) $keySheet->getCell($coord)->getValue();
                    $keyCalc = $this->readCalculatedValue($keySheet, $coord);
                    $normKey = $this->normalizeValue($keyCalc);

                    if ($normKey === '') {
                        continue;
                    }

                    $entry = [
                        'sheet_idx'       => $sheetIdx,
                        'sheet_title'     => $sheetTitle,
                        'coord'           => $coord,
                        'expected_calc'   => $keyCalc,
                        'expected_norm'   => $normKey,
                        'key_has_formula' => str_starts_with(trim($keyRawFormula), '='),
                    ];
                    $allTargets[] = $entry;

                    if ($tplSheet) {
                        $tplCalc = $this->readCalculatedValue($tplSheet, $coord);
                        if ($normKey !== $this->normalizeValue($tplCalc)) {
                            $diffTargets[] = $entry;
                        }
                    }
                }
            }

            $targets = array_merge($targets, !empty($diffTargets) ? $diffTargets : $allTargets);
        }

        $totalCells = count($targets);
        $weightPerCell = $totalCells > 0 ? round(100 / $totalCells, 2) : 0.0;
        $matchedCells = 0;
        $mismatchedCoords = [];
        $breakdown = [];

        foreach ($targets as $item) {
            $studentSheet = $studentSpreadsheet->getSheetByName($item['sheet_title'])
                ?? ($studentSpreadsheet->getSheetCount() > $item['sheet_idx'] ? $studentSpreadsheet->getSheet($item['sheet_idx']) : null);

            $actualCalc = $studentSheet ? $this->readCalculatedValue($studentSheet, $item['coord']) : '';
            $actualRaw = $studentSheet ? (string) $studentSheet->getCell($item['coord'])->getValue() : '';
            $actualNorm = $this->normalizeValue($actualCalc);

            $matched = $this->valuesMatch($item['expected_norm'], $actualNorm, $defaultTolerance);
            $prefix = $keySpreadsheet->getSheetCount() > 1 ? ($item['sheet_title'] . '!') : '';
            $displayCoord = $prefix . $item['coord'];

            if ($matched) {
                $matchedCells++;
            } else {
                $mismatchedCoords[] = $displayCoord;
            }

            $breakdown[] = [
                'criterion_name' => $displayCoord,
                'max_score'      => $weightPerCell,
                'awarded_score'  => $matched ? $weightPerCell : 0.0,
                'expected'       => (string) $item['expected_calc'],
                'actual'         => (string) $actualCalc,
                'formula_used'   => str_starts_with(trim($actualRaw), '=') ? $actualRaw : null,
                'reason'         => $matched
                    ? 'Nilai sel sudah sesuai kunci jawaban.'
                    : (trim((string) $actualCalc) === ''
                        ? 'Sel masih kosong, belum diisi oleh peserta.'
                        : "Isian peserta ('" . (string) $actualCalc . "') belum sesuai dengan jawaban kunci ('" . (string) $item['expected_calc'] . "')."),
            ];
        }

        $score = $totalCells > 0 ? round(($matchedCells / $totalCells) * 100, 2) : 0.0;
        $isPassed = $score >= $passingGrade;
        $summary = $this->buildSummary($matchedCells, $totalCells, $score, $passingGrade, $mismatchedCoords);

        return [
            'score'            => $score,
            'is_passed'        => $isPassed,
            'matched_cells'    => $matchedCells,
            'total_cells'      => $totalCells,
            'mismatched_cells' => $mismatchedCoords,
            'feedback_json'    => [
                'grader'                    => 'AUTO_EXCEL',
                'confidence_level'          => 'high',
                'needs_human_review'        => false,
                'prompt_injection_detected' => false,
                'matched_cells'             => $matchedCells,
                'total_cells'               => $totalCells,
                'criteria_breakdown'        => $breakdown,
                'overall_feedback'          => $summary,
            ],
            'summary_note'     => $summary,
        ];
    }

    private function buildSummary(
        int $matchedCells,
        int $totalCells,
        float $score,
        float $passingGrade,
        array $mismatchedCoords
    ): string {
        if (empty($mismatchedCoords)) {
            return "Seluruh {$totalCells} sel jawaban pada berkas Excel sudah sesuai dengan kunci jawaban. Hasil pekerjaan Anda sangat baik.";
        }

        $preview = implode(', ', array_slice($mismatchedCoords, 0, 8))
            . (count($mismatchedCoords) > 8 ? ', dan beberapa sel lainnya' : '');

        if ($matchedCells === 0) {
            return "Belum ada sel jawaban yang sesuai dengan kunci jawaban (0 dari {$totalCells} sel). Pastikan seluruh sel isian ({$preview}) telah diisi sesuai rumus dan ketentuan soal.";
        }

        return "Sebanyak {$matchedCells} dari {$totalCells} sel jawaban sudah sesuai kunci. Sel yang masih belum sesuai: {$preview}. Silakan periksa kembali perhitungan atau isian pada sel tersebut.";
    }

    private function readCalculatedValue(Worksheet $sheet, string $coord): mixed
    {
        $cell = $sheet->getCell($coord);
        try {
            $val = $cell->getCalculatedValue();
        } catch (\Throwable) {
            $val = $cell->getValue();
        }

        if ($val === null || $val === '') {
            $val = $cell->getValue();
        }

        return $val;
    }

    private function normalizeValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        $str = trim((string) $value);
        if ($str === '') {
            return '';
        }

        $str = mb_strtolower($str);
        $str = preg_replace('/\s+/u', ' ', $str) ?? $str;

        return trim($str);
    }

    private function valuesMatch(string $expected, string $actual, float $tolerance): bool
    {
        if ($expected === $actual) {
            return true;
        }

        if (is_numeric($expected) && is_numeric($actual)) {
            return abs((float) $expected - (float) $actual) <= $tolerance;
        }

        $expNum = str_replace([' ', ','], ['', '.'], $expected);
        $actNum = str_replace([' ', ','], ['', '.'], $actual);

        if (is_numeric($expNum) && is_numeric($actNum)) {
            return abs((float) $expNum - (float) $actNum) <= $tolerance;
        }

        return false;
    }
}
