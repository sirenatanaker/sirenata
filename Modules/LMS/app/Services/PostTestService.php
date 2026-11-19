<?php

namespace Modules\LMS\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\CourseSection;
use Modules\LMS\Models\PostTest;
use Modules\LMS\Services\Grading\DocumentExtractorService;
use Modules\LMS\Services\Grading\TextPreprocessorService;

class PostTestService
{
    public function __construct(
        private ?DocumentExtractorService $extractor = null,
        private ?TextPreprocessorService $preprocessor = null,
    ) {
        $this->extractor ??= app(DocumentExtractorService::class);
        $this->preprocessor ??= app(TextPreprocessorService::class);
    }
    public function findOrFail(string $id): PostTest
    {
        return PostTest::findOrFail($id);
    }

    public function findWithQuestionsOrFail(string $id): PostTest
    {
        return PostTest::with('questions.choices')->findOrFail($id);
    }

    public function getCreateData(string $courseSlug, ?string $sectionId = null): array
    {
        $course = Course::where('slug', $courseSlug)->firstOrFail();
        $section = $sectionId ? CourseSection::findOrFail($sectionId) : null;

        return compact('course', 'section');
    }

    public function getEditData(string $id, string $courseSlug): array
    {
        $course = Course::where('slug', $courseSlug)->firstOrFail();
        $postTest = PostTest::with('questions.choices')->findOrFail($id);
        $section = $postTest->course_section_id ? CourseSection::findOrFail($postTest->course_section_id) : null;

        $formattedQuestions = $postTest->questions->map(function ($q) {
            $correctIndex = 0;
            $choices = $q->choices->values()->map(function ($c, $index) use (&$correctIndex) {
                if ($c->is_correct) {
                    $correctIndex = $index;
                }

                return $c->choice;
            });

            return [
                'question'       => $q->question,
                'choices'        => $choices->isNotEmpty() ? $choices->all() : ['', '', '', ''],
                'correct_choice' => $correctIndex,
            ];
        });

        $formattedEssayQuestions = $postTest->questions->map(function ($q) {
            return [
                'question'   => $q->question,
                'answer_key' => $q->answer_key ?? '',
            ];
        });

        if ($formattedQuestions->isEmpty()) {
            $formattedQuestions = collect([[
                'question'       => '',
                'choices'        => ['', '', '', ''],
                'correct_choice' => 0,
            ]]);
        }

        if ($formattedEssayQuestions->isEmpty()) {
            $formattedEssayQuestions = collect([[
                'question'   => '',
                'answer_key' => '',
            ]]);
        }

        return compact('course', 'section', 'postTest', 'formattedQuestions', 'formattedEssayQuestions');
    }

    public function uploadEditorImage($imageFile): string
    {
        $path = $imageFile->store('post-test-images', 'public');

        return asset('storage/' . $path);
    }

    public function getStudentTestResult(int|string|null $userId, string $postTestId, bool $retake = false): ?object
    {
        if ($retake || !$userId) {
            return null;
        }

        return DB::table('post_test_results')
            ->where('user_id', $userId)
            ->where('post_test_id', $postTestId)
            ->first();
    }

    public function submitStudentTest(PostTest $postTest, string $slug, int|string $userId, array $userAnswers): array
    {
        $totalQuestions = $postTest->questions->count();
        if ($totalQuestions === 0) {
            return [
                'has_questions' => false,
                'message'       => 'Tidak ada soal pada ujian ini.',
            ];
        }

        $correctCount = 0;
        foreach ($postTest->questions as $question) {
            $userSelectedChoiceId = $userAnswers[$question->id] ?? null;
            if ($userSelectedChoiceId) {
                $correctChoice = $question->choices->where('is_correct', true)->first();
                if ($correctChoice && $correctChoice->id === $userSelectedChoiceId) {
                    $correctCount++;
                }
            }
        }

        $score = (int) round(($correctCount / $totalQuestions) * 100);
        $isPassed = $score >= $postTest->passing_score;

        $existingRecord = DB::table('post_test_results')
            ->where('user_id', $userId)
            ->where('post_test_id', $postTest->id)
            ->first();

        if ($existingRecord) {
            DB::table('post_test_results')
                ->where('id', $existingRecord->id)
                ->update([
                    'score'        => $score,
                    'is_passed'    => $isPassed,
                    'completed_at' => $isPassed ? now() : null,
                    'updated_at'   => now(),
                ]);
        } else {
            DB::table('post_test_results')->insert([
                'id'           => Str::uuid()->toString(),
                'user_id'      => $userId,
                'post_test_id' => $postTest->id,
                'score'        => $score,
                'is_passed'    => $isPassed,
                'completed_at' => $isPassed ? now() : null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        if (is_null($postTest->course_section_id) && $isPassed) {
            $course = Course::where('slug', $slug)->first();
            if ($course) {
                $course->students()->updateExistingPivot($userId, [
                    'status'   => 'completed',
                    'progress' => 100,
                ]);
            }
        }

        return [
            'has_questions' => true,
            'is_passed'     => $isPassed,
            'score'         => $score,
            'passing_score' => $postTest->passing_score,
            'message'       => $isPassed
                ? "Selamat! Anda lulus ujian dengan nilai {$score} (KKM: {$postTest->passing_score})"
                : "Nilai Anda {$score} belum mencapai KKM ({$postTest->passing_score}). Silakan pelajari materi kembali dan ulangi tes.",
        ];
    }

    public function storePostTestWithQuestions(array $data, ?UploadedFile $adminFile = null, ?UploadedFile $answerKeyFile = null): array
    {
        DB::beginTransaction();
        try {
            $course = null;
            if (!empty($data['course_slug'])) {
                $course = Course::where('slug', $data['course_slug'])->first();
            } elseif (!empty($data['course_id'])) {
                $course = Course::find($data['course_id']);
            }
            $courseName = $course ? ($course->course_name ?? $course->name) : 'Pelatihan';
            $title = !empty(trim($data['title'] ?? '')) ? $data['title'] : ('Evaluasi Akhir : ' . $courseName);
            $type = $data['type'] ?? 'multiple_choice';

            $adminFilePath = null;
            $adminFileName = null;
            $adminFileSize = null;
            $templateExtractedText = null;
            $answerKeyFilePath = null;
            $answerKeyFileName = null;
            $answerKeyFileSize = null;
            $keyExtractedText = null;

            if ($type === 'file_upload') {
                if ($adminFile) {
                    $adminFileName = $adminFile->getClientOriginalName();
                    $adminFileSize = $this->formatFileSize($adminFile->getSize());
                    $adminFilePath = $adminFile->store('assignments/soal', 'public');
                    $templateExtractedText = $this->extractReferenceTextOnce($adminFilePath);
                }
                if ($answerKeyFile) {
                    $answerKeyFileName = $answerKeyFile->getClientOriginalName();
                    $answerKeyFileSize = $this->formatFileSize($answerKeyFile->getSize());
                    $answerKeyFilePath = $answerKeyFile->store('assignments/kunci', 'public');
                    $keyExtractedText = $this->extractReferenceTextOnce($answerKeyFilePath);
                }
            }

            $passingScore = ($type === 'file_upload')
                ? ((int) ($data['passing_score'] ?? 0) ?: 75)
                : (int) ($data['passing_score'] ?? 70);

            $rubricJson = $this->parseRubricInput($data['rubric_json'] ?? ($data['rubric_json_text'] ?? null));
            $gradingType = $this->resolveAdminGradingType($type, $data['grading_type'] ?? null, $adminFilePath, $answerKeyFilePath, $rubricJson);

            $postTest = PostTest::create([
                'course_section_id'       => $data['course_section_id'] ?? null,
                'course_id'               => $data['course_id'] ?? null,
                'title'                   => $title,
                'description'             => $data['description'] ?? null,
                'passing_score'           => $passingScore,
                'passing_grade'           => $passingScore,
                'duration'                => ($type === 'file_upload') ? 0 : ($data['duration'] ?? 30),
                'type'                    => $type,
                'grading_type'            => $gradingType,
                'rubric_json'             => $rubricJson,
                'admin_file_path'         => $adminFilePath,
                'admin_file_name'         => $adminFileName,
                'admin_file_size'         => $adminFileSize,
                'template_extracted_text' => $templateExtractedText,
                'answer_key_file_path'    => $answerKeyFilePath,
                'answer_key_file_name'    => $answerKeyFileName,
                'answer_key_file_size'    => $answerKeyFileSize,
                'key_extracted_text'      => $keyExtractedText,
                'instructions'            => ($type === 'file_upload') ? ($data['instructions'] ?? null) : null,
                'max_words'               => ($type === 'essay') ? ($data['max_words'] ?? 500) : null,
            ]);

            if ($type === 'multiple_choice' && !empty($data['questions'])) {
                foreach ($data['questions'] as $qData) {
                    $question = $postTest->questions()->create([
                        'question' => $qData['question'],
                    ]);

                    if (!empty($qData['choices'])) {
                        $choices = array_values($qData['choices']);
                        foreach ($choices as $cIndex => $choiceText) {
                            $question->choices()->create([
                                'choice'     => $choiceText,
                                'is_correct' => (int) $qData['correct_choice'] === $cIndex,
                            ]);
                        }
                    }
                }
            } elseif ($type === 'essay' && (!empty($data['essay_questions']) || !empty($data['questions']))) {
                $essayItems = $data['essay_questions'] ?? $data['questions'];
                foreach ($essayItems as $qData) {
                    if (!empty(trim($qData['question'] ?? ''))) {
                        $answerKey = trim((string) ($qData['answer_key'] ?? ''));
                        $postTest->questions()->create([
                            'question'   => $qData['question'],
                            'answer_key' => $answerKey !== '' ? $answerKey : null,
                        ]);
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Evaluasi beserta soal berhasil disimpan',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PostTestService store error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage(),
            ];
        }
    }

    public function updatePostTestWithQuestions(PostTest $postTest, array $data, ?UploadedFile $adminFile = null, ?UploadedFile $answerKeyFile = null): array
    {
        DB::beginTransaction();
        try {
            $course = null;
            if (!empty($data['course_slug'])) {
                $course = Course::where('slug', $data['course_slug'])->first();
            } elseif ($postTest->course_id) {
                $course = Course::find($postTest->course_id);
            }
            $courseName = $course ? ($course->course_name ?? $course->name) : 'Pelatihan';
            $title = !empty(trim($data['title'] ?? '')) ? $data['title'] : ('Evaluasi Akhir : ' . $courseName);
            $type = $data['type'] ?? $postTest->type ?? 'multiple_choice';

            $adminFilePath = $postTest->admin_file_path;
            $adminFileName = $postTest->admin_file_name;
            $adminFileSize = $postTest->admin_file_size;
            $templateExtractedText = $postTest->template_extracted_text;
            $answerKeyFilePath = $postTest->answer_key_file_path;
            $answerKeyFileName = $postTest->answer_key_file_name;
            $answerKeyFileSize = $postTest->answer_key_file_size;
            $keyExtractedText = $postTest->key_extracted_text;

            if ($type === 'file_upload') {
                if ($adminFile) {
                    if ($postTest->admin_file_path && Storage::disk('public')->exists($postTest->admin_file_path)) {
                        Storage::disk('public')->delete($postTest->admin_file_path);
                    }
                    $adminFileName = $adminFile->getClientOriginalName();
                    $adminFileSize = $this->formatFileSize($adminFile->getSize());
                    $adminFilePath = $adminFile->store('assignments/soal', 'public');
                    $templateExtractedText = $this->extractReferenceTextOnce($adminFilePath);
                }
                if ($answerKeyFile) {
                    if ($postTest->answer_key_file_path && Storage::disk('public')->exists($postTest->answer_key_file_path)) {
                        Storage::disk('public')->delete($postTest->answer_key_file_path);
                    }
                    $answerKeyFileName = $answerKeyFile->getClientOriginalName();
                    $answerKeyFileSize = $this->formatFileSize($answerKeyFile->getSize());
                    $answerKeyFilePath = $answerKeyFile->store('assignments/kunci', 'public');
                    $keyExtractedText = $this->extractReferenceTextOnce($answerKeyFilePath);
                }
            } else {
                if ($postTest->admin_file_path && Storage::disk('public')->exists($postTest->admin_file_path)) {
                    Storage::disk('public')->delete($postTest->admin_file_path);
                }
                if ($postTest->answer_key_file_path && Storage::disk('public')->exists($postTest->answer_key_file_path)) {
                    Storage::disk('public')->delete($postTest->answer_key_file_path);
                }
                $adminFilePath = null;
                $adminFileName = null;
                $adminFileSize = null;
                $templateExtractedText = null;
                $answerKeyFilePath = null;
                $answerKeyFileName = null;
                $answerKeyFileSize = null;
                $keyExtractedText = null;
            }

            $passingScore = ($type === 'file_upload')
                ? ((int) ($data['passing_score'] ?? 0) ?: 75)
                : (int) ($data['passing_score'] ?? 70);

            $rubricJson = array_key_exists('rubric_json', $data) || array_key_exists('rubric_json_text', $data)
                ? $this->parseRubricInput($data['rubric_json'] ?? ($data['rubric_json_text'] ?? null))
                : $postTest->rubric_json;
            $gradingType = $this->resolveAdminGradingType($type, $data['grading_type'] ?? null, $adminFilePath, $answerKeyFilePath, $rubricJson);

            $postTest->update([
                'title'                   => $title,
                'description'             => $data['description'] ?? null,
                'passing_score'           => $passingScore,
                'passing_grade'           => $passingScore,
                'duration'                => ($type === 'file_upload') ? 0 : ($data['duration'] ?? 30),
                'type'                    => $type,
                'grading_type'            => $gradingType,
                'rubric_json'             => $rubricJson,
                'admin_file_path'         => $adminFilePath,
                'admin_file_name'         => $adminFileName,
                'admin_file_size'         => $adminFileSize,
                'template_extracted_text' => $templateExtractedText,
                'answer_key_file_path'    => $answerKeyFilePath,
                'answer_key_file_name'    => $answerKeyFileName,
                'answer_key_file_size'    => $answerKeyFileSize,
                'key_extracted_text'      => $keyExtractedText,
                'instructions'            => ($type === 'file_upload') ? ($data['instructions'] ?? null) : null,
                'max_words'               => ($type === 'essay') ? ($data['max_words'] ?? 500) : null,
            ]);

            // Hapus pertanyaan & pilihan lama
            foreach ($postTest->questions as $question) {
                $question->choices()->delete();
                $question->delete();
            }

            if ($type === 'multiple_choice' && !empty($data['questions'])) {
                foreach ($data['questions'] as $qData) {
                    $question = $postTest->questions()->create([
                        'question' => $qData['question'],
                    ]);

                    if (!empty($qData['choices'])) {
                        $choices = array_values($qData['choices']);
                        foreach ($choices as $cIndex => $choiceText) {
                            $question->choices()->create([
                                'choice'     => $choiceText,
                                'is_correct' => (int) $qData['correct_choice'] === $cIndex,
                            ]);
                        }
                    }
                }
            } elseif ($type === 'essay' && (!empty($data['essay_questions']) || !empty($data['questions']))) {
                $essayItems = $data['essay_questions'] ?? $data['questions'];
                foreach ($essayItems as $qData) {
                    if (!empty(trim($qData['question'] ?? ''))) {
                        $answerKey = trim((string) ($qData['answer_key'] ?? ''));
                        $postTest->questions()->create([
                            'question'   => $qData['question'],
                            'answer_key' => $answerKey !== '' ? $answerKey : null,
                        ]);
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Evaluasi berhasil diperbarui',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PostTestService update error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal memperbarui: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Koreksi otomatis jawaban tipe unggah berkas dengan mencocokkan berkas peserta
     * terhadap berkas kunci jawaban yang diunggah Admin (Excel per-sel / DOCX & PDF berbasis teks).
     */
    public function autoGradeFileUpload(PostTest $postTest, string $studentFileRelativePath): array
    {
        if (empty($postTest->answer_key_file_path) || !Storage::disk('public')->exists($postTest->answer_key_file_path)) {
            return [
                'auto_graded' => false,
                'status'      => 'submitted',
                'is_passed'   => true,
                'score'       => 100,
                'notes'       => null,
            ];
        }

        if (!Storage::disk('public')->exists($studentFileRelativePath)) {
            return [
                'auto_graded' => false,
                'status'      => 'submitted',
                'is_passed'   => true,
                'score'       => 100,
                'notes'       => null,
            ];
        }

        $passingScore = $postTest->passing_score > 0 ? (int) $postTest->passing_score : 75;
        $keyExt = strtolower(pathinfo($postTest->answer_key_file_path, PATHINFO_EXTENSION));
        $studentExt = strtolower(pathinfo($studentFileRelativePath, PATHINFO_EXTENSION));
        $spreadsheetExts = ['xlsx', 'xls', 'csv'];

        try {
            if (in_array($keyExt, $spreadsheetExts, true) && in_array($studentExt, $spreadsheetExts, true)) {
                return $this->gradeSpreadsheetSubmission(
                    $postTest->answer_key_file_path,
                    $studentFileRelativePath,
                    $postTest->admin_file_path,
                    $passingScore
                );
            }

            return $this->gradeDocumentSubmission(
                $postTest->answer_key_file_path,
                $studentFileRelativePath,
                $postTest->admin_file_path,
                $passingScore
            );
        } catch (\Throwable $e) {
            Log::warning('autoGradeFileUpload fallback to manual review: ' . $e->getMessage());

            return [
                'auto_graded' => false,
                'status'      => 'submitted',
                'is_passed'   => true,
                'score'       => 100,
                'notes'       => null,
            ];
        }
    }

    /**
     * Koreksi otomatis jawaban uraian berdasarkan kata/poin kunci pada tiap butir soal.
     */
    public function autoGradeEssay(PostTest $postTest, array $essayAnswers): array
    {
        $questions = $postTest->relationLoaded('questions')
            ? $postTest->questions->values()
            : $postTest->questions()->get()->values();

        $keyedQuestions = $questions->filter(fn($q) => !empty(trim((string) ($q->answer_key ?? ''))));

        if ($keyedQuestions->isEmpty()) {
            return [
                'auto_graded' => false,
                'status'      => 'submitted',
                'is_passed'   => true,
                'score'       => 85,
                'notes'       => null,
            ];
        }

        $questionScores = [];
        $detailNotes = [];

        foreach ($questions as $qIdx => $question) {
            $keyText = trim((string) ($question->answer_key ?? ''));
            if ($keyText === '') {
                continue;
            }

            $studentAnswer = (string) ($essayAnswers[$qIdx] ?? ($essayAnswers[(string) $question->id] ?? ''));
            $eval = $this->scoreEssayAgainstKey($studentAnswer, $keyText);

            $questionScores[] = $eval['score'];
            $detailNotes[] = 'No.' . ($qIdx + 1) . ": {$eval['score']} ({$eval['matched']}/{$eval['total']} poin kunci)";
        }

        $overallScore = !empty($questionScores)
            ? (int) round(array_sum($questionScores) / count($questionScores))
            : 0;
        $passingScore = $postTest->passing_score > 0 ? (int) $postTest->passing_score : 70;
        $isPassed = $overallScore >= $passingScore;

        $note = "[Koreksi Otomatis Sistem] Jawaban uraian dicocokkan dengan kata/poin kunci (Skor: {$overallScore}/100, KKM: {$passingScore}). Rincian: " . implode('; ', $detailNotes) . '.';

        return [
            'auto_graded' => true,
            'status'      => 'graded',
            'is_passed'   => $isPassed,
            'score'       => $overallScore,
            'notes'       => $note,
        ];
    }

    private function gradeSpreadsheetSubmission(
        string $keyPath,
        string $studentPath,
        ?string $templatePath,
        int $passingScore
    ): array {
        $keySpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(Storage::disk('public')->path($keyPath));
        $studentSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(Storage::disk('public')->path($studentPath));

        $tplSpreadsheet = null;
        if ($templatePath && Storage::disk('public')->exists($templatePath)) {
            $tplExt = strtolower(pathinfo($templatePath, PATHINFO_EXTENSION));
            if (in_array($tplExt, ['xlsx', 'xls', 'csv'], true)) {
                $tplSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(Storage::disk('public')->path($templatePath));
            }
        }

        $totalCells = 0;
        $matchedCells = 0;
        $mismatchedCoords = [];

        foreach ($keySpreadsheet->getAllSheets() as $sheetIdx => $keySheet) {
            $sheetTitle = $keySheet->getTitle();
            $studentSheet = $studentSpreadsheet->getSheetByName($sheetTitle)
                ?? ($studentSpreadsheet->getSheetCount() > $sheetIdx ? $studentSpreadsheet->getSheet($sheetIdx) : null);

            $tplSheet = null;
            if ($tplSpreadsheet) {
                $tplSheet = $tplSpreadsheet->getSheetByName($sheetTitle)
                    ?? ($tplSpreadsheet->getSheetCount() > $sheetIdx ? $tplSpreadsheet->getSheet($sheetIdx) : null);
            }

            $highestRow = $keySheet->getHighestDataRow();
            $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($keySheet->getHighestDataColumn());

            $diffTargets = [];
            $allNonEmptyTargets = [];

            for ($row = 1; $row <= $highestRow; $row++) {
                for ($col = 1; $col <= $highestColIndex; $col++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $coord = $colLetter . $row;
                    $keyRaw = $this->readSpreadsheetCell($keySheet, $coord);
                    $normKey = $this->normalizeComparableValue($keyRaw);

                    if ($normKey === '') {
                        continue;
                    }

                    $allNonEmptyTargets[$coord] = $normKey;

                    if ($tplSheet) {
                        $tplRaw = $this->readSpreadsheetCell($tplSheet, $coord);
                        $normTpl = $this->normalizeComparableValue($tplRaw);
                        if ($normKey !== $normTpl) {
                            $diffTargets[$coord] = $normKey;
                        }
                    }
                }
            }

            // Jika template ada dan ditemukan sel jawaban yang berbeda dari template, fokus periksa sel tersebut saja
            $targetCells = !empty($diffTargets) ? $diffTargets : $allNonEmptyTargets;

            foreach ($targetCells as $coord => $normExpected) {
                $totalCells++;
                $studentRaw = $studentSheet ? $this->readSpreadsheetCell($studentSheet, $coord) : '';
                $normActual = $this->normalizeComparableValue($studentRaw);

                if ($this->spreadsheetValuesMatch($normExpected, $normActual)) {
                    $matchedCells++;
                } else {
                    $prefix = $keySpreadsheet->getSheetCount() > 1 ? ($sheetTitle . '!') : '';
                    $mismatchedCoords[] = $prefix . $coord;
                }
            }
        }

        if ($totalCells === 0) {
            return $this->gradeDocumentSubmission($keyPath, $studentPath, $templatePath, $passingScore);
        }

        $score = (int) round(($matchedCells / $totalCells) * 100);
        $isPassed = $score >= $passingScore;

        $note = "[Koreksi Otomatis Sistem] Berkas Excel dicocokkan dengan kunci jawaban: {$matchedCells} dari {$totalCells} sel jawaban sesuai (Skor: {$score}/100, KKM: {$passingScore}).";
        if (!empty($mismatchedCoords)) {
            $preview = array_slice($mismatchedCoords, 0, 8);
            $note .= ' Sel belum sesuai: ' . implode(', ', $preview) . (count($mismatchedCoords) > 8 ? ', dll.' : '.');
        }

        return [
            'auto_graded' => true,
            'status'      => 'graded',
            'is_passed'   => $isPassed,
            'score'       => $score,
            'notes'       => $note,
        ];
    }

    private function gradeDocumentSubmission(
        string $keyPath,
        string $studentPath,
        ?string $templatePath,
        int $passingScore
    ): array {
        $keyText = $this->extractTextFromPublicFile($keyPath);
        $studentText = $this->extractTextFromPublicFile($studentPath);

        if (trim($keyText) === '') {
            return [
                'auto_graded' => false,
                'status'      => 'submitted',
                'is_passed'   => true,
                'score'       => 100,
                'notes'       => null,
            ];
        }

        $templateWords = [];
        if ($templatePath && Storage::disk('public')->exists($templatePath)) {
            $tplText = $this->extractTextFromPublicFile($templatePath);
            $templateWords = $this->extractContentTokens($tplText);
        }

        $keyTokens = array_values(array_diff($this->extractContentTokens($keyText), $templateWords));
        if (empty($keyTokens)) {
            $keyTokens = $this->extractContentTokens($keyText);
        }

        $studentTokens = $this->extractContentTokens($studentText);
        $matchedTokens = array_intersect($keyTokens, $studentTokens);

        $tokenCount = count($keyTokens);
        $matchedCount = count($matchedTokens);
        $tokenRatio = $tokenCount > 0 ? ($matchedCount / $tokenCount) : 0;

        $normKey = $this->normalizeComparableValue($keyText);
        $normStudent = $this->normalizeComparableValue($studentText);
        $simPct = 0.0;
        if ($normKey !== '' && $normStudent !== '') {
            similar_text(substr($normKey, 0, 2000), substr($normStudent, 0, 2000), $simPct);
        }

        $score = $tokenCount > 0
            ? (int) min(100, round(($tokenRatio * 80) + (($simPct / 100) * 20)))
            : (int) round($simPct);

        if (trim($studentText) === '') {
            $score = 0;
        }

        $isPassed = $score >= $passingScore;
        $note = "[Koreksi Otomatis Sistem] Dokumen dicocokkan dengan kunci jawaban: {$matchedCount} dari {$tokenCount} kata/poin kunci ditemukan (Skor: {$score}/100, KKM: {$passingScore}).";

        return [
            'auto_graded' => true,
            'status'      => 'graded',
            'is_passed'   => $isPassed,
            'score'       => $score,
            'notes'       => $note,
        ];
    }

    private function scoreEssayAgainstKey(string $studentAnswer, string $keyText): array
    {
        $normStudent = $this->normalizeComparableValue($studentAnswer);
        $normKey = $this->normalizeComparableValue($keyText);

        if ($normStudent === '' || $normKey === '') {
            return ['score' => 0, 'matched' => 0, 'total' => 0];
        }

        $phrases = $this->extractEssayKeyPoints($keyText);
        $matched = 0;

        foreach ($phrases as $phrase) {
            if (str_contains($normStudent, $phrase)) {
                $matched++;
                continue;
            }

            // Untuk frasa multi-kata, cek apakah seluruh token intinya muncul di jawaban
            $phraseTokens = $this->extractContentTokens($phrase);
            if (count($phraseTokens) > 1) {
                $studentTokens = $this->extractContentTokens($normStudent);
                $hits = count(array_intersect($phraseTokens, $studentTokens));
                if ($hits >= (int) ceil(count($phraseTokens) * 0.75)) {
                    $matched++;
                }
            }
        }

        $total = count($phrases);
        $keywordRatio = $total > 0 ? ($matched / $total) : 0;

        $simPct = 0.0;
        similar_text(substr($normKey, 0, 1500), substr($normStudent, 0, 1500), $simPct);

        if ($keywordRatio <= 0) {
            $score = (int) min(25, round(($simPct / 100) * 25));
        } else {
            $score = (int) min(100, round(($keywordRatio * 85) + (($simPct / 100) * 20)));
        }

        return [
            'score'   => $score,
            'matched' => $matched,
            'total'   => $total,
        ];
    }

    private function extractEssayKeyPoints(string $keyText): array
    {
        $hasDelimiters = (bool) preg_match('/[,;\r\n]/', $keyText);
        if ($hasDelimiters) {
            $parts = preg_split('/[,;\r\n]+/', $keyText) ?: [];
            $phrases = [];
            foreach ($parts as $part) {
                $cleaned = $this->normalizeComparableValue(preg_replace('/^\d+[\.\)]\s*/', '', trim($part)) ?? '');
                if ($cleaned !== '' && mb_strlen($cleaned) >= 3) {
                    $phrases[] = $cleaned;
                }
            }
            if (!empty($phrases)) {
                return array_values(array_unique($phrases));
            }
        }

        return $this->extractContentTokens($keyText);
    }

    private function extractContentTokens(string $text): array
    {
        $normalized = mb_strtolower(strip_tags($text));
        $normalized = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $normalized) ?? '';
        $words = preg_split('/\s+/u', trim($normalized)) ?: [];

        $stopwords = [
            'yang', 'dan', 'di', 'ke', 'dari', 'untuk', 'pada', 'dengan', 'adalah',
            'dalam', 'atau', 'sebagai', 'oleh', 'secara', 'terhadap', 'antara', 'serta',
            'dapat', 'akan', 'harus', 'agar', 'juga', 'ini', 'itu', 'bahwa', 'telah',
            'sudah', 'lebih', 'seperti', 'karena', 'melalui', 'saat', 'yaitu', 'yakni',
            'para', 'bagi', 'atas', 'bisa', 'jika', 'bila', 'maka', 'ataupun', 'tentang',
        ];

        $tokens = [];
        foreach ($words as $word) {
            if (mb_strlen($word) >= 3 && !in_array($word, $stopwords, true)) {
                $tokens[] = $word;
            }
        }

        return array_values(array_unique($tokens));
    }

    private function readSpreadsheetCell(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $coord): mixed
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

    private function normalizeComparableValue(mixed $value): string
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

    private function spreadsheetValuesMatch(string $expected, string $actual): bool
    {
        if ($expected === $actual) {
            return true;
        }

        $expNum = str_replace([' ', ','], ['', '.'], $expected);
        $actNum = str_replace([' ', ','], ['', '.'], $actual);

        if (is_numeric($expected) && is_numeric($actual)) {
            return abs((float) $expected - (float) $actual) <= 0.01;
        }

        if (is_numeric($expNum) && is_numeric($actNum)) {
            return abs((float) $expNum - (float) $actNum) <= 0.01;
        }

        return false;
    }

    private function extractTextFromPublicFile(string $relativePath): string
    {
        $fullPath = Storage::disk('public')->path($relativePath);
        $ext = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

        if ($ext === 'docx' && class_exists(\ZipArchive::class)) {
            $zip = new \ZipArchive();
            if ($zip->open($fullPath) === true) {
                $xml = $zip->getFromName('word/document.xml');
                $zip->close();
                if ($xml !== false) {
                    $xml = str_replace(['</w:p>', '</w:tr>', '</w:tc>'], ["\n", "\n", ' '], $xml);
                    return trim(html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8'));
                }
            }
        }

        if (in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullPath);
            $parts = [];
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                foreach ($sheet->toArray() as $row) {
                    $line = implode(' ', array_filter(array_map(fn($c) => trim((string) $c), $row)));
                    if ($line !== '') {
                        $parts[] = $line;
                    }
                }
            }
            return implode("\n", $parts);
        }

        if ($ext === 'txt') {
            return (string) @file_get_contents($fullPath);
        }

        if ($ext === 'pdf') {
            $raw = (string) @file_get_contents($fullPath);
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

            if (empty($chunks) && preg_match_all('/\/Title\s*\(([^)]+)\)/', $raw, $metaMatches)) {
                $chunks = $metaMatches[1];
            }

            return trim(implode(' ', $chunks));
        }

        return '';
    }

    private function extractReferenceTextOnce(string $relativePath): ?string
    {
        $ext = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'docx', 'txt'], true)) {
            return null;
        }

        $extracted = $this->extractor->extractFromPublicDisk($relativePath);
        $clean = $this->preprocessor->normalize($extracted['text']);

        return $clean !== '' ? $clean : null;
    }

    private function parseRubricInput(mixed $input): ?array
    {
        if (is_array($input)) {
            return !empty($input) ? $input : null;
        }

        if (is_string($input) && trim($input) !== '') {
            $decoded = json_decode(trim($input), true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function resolveAdminGradingType(
        string $type,
        ?string $requested,
        ?string $adminFilePath,
        ?string $answerKeyFilePath,
        ?array $rubricJson
    ): string {
        $valid = [
            PostTest::GRADING_AUTO_EXCEL,
            PostTest::GRADING_AUTO_AI_ESSAY,
            PostTest::GRADING_AUTO_AI_REPORT,
            PostTest::GRADING_MANUAL,
        ];

        if ($requested && in_array($requested, $valid, true)) {
            return $requested;
        }

        if ($type === 'essay') {
            return PostTest::GRADING_AUTO_AI_ESSAY;
        }

        if ($type === 'file_upload') {
            $ext = strtolower(pathinfo((string) ($answerKeyFilePath ?: $adminFilePath), PATHINFO_EXTENSION));
            if (in_array($ext, ['xlsx', 'xls', 'csv'], true) && $answerKeyFilePath) {
                return PostTest::GRADING_AUTO_EXCEL;
            }
            if (in_array($ext, ['pdf', 'docx', 'txt'], true) && ($answerKeyFilePath || !empty($rubricJson))) {
                return PostTest::GRADING_AUTO_AI_REPORT;
            }
        }

        return PostTest::GRADING_MANUAL;
    }

    private function formatFileSize(int $sizeBytes): string
    {
        return $sizeBytes >= 1048576
            ? round($sizeBytes / 1048576, 1) . ' MB'
            : round($sizeBytes / 1024, 0) . ' KB';
    }
}