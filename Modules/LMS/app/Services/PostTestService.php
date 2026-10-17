<?php

namespace Modules\LMS\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\CourseSection;
use Modules\LMS\Models\PostTest;

class PostTestService
{
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
                'choices'        => $choices,
                'correct_choice' => $correctIndex,
            ];
        });

        return compact('course', 'section', 'postTest', 'formattedQuestions');
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

    public function storePostTestWithQuestions(array $data): array
    {
        DB::beginTransaction();
        try {
            $postTest = PostTest::create([
                'course_section_id' => $data['course_section_id'] ?? null,
                'course_id'         => $data['course_id'] ?? null,
                'title'             => $data['title'],
                'description'       => $data['description'] ?? null,
                'passing_score'     => $data['passing_score'] ?? 70,
                'duration'          => $data['duration'] ?? 30,
            ]);

            if (!empty($data['questions'])) {
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
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Post Test beserta soal berhasil disimpan',
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

    public function updatePostTestWithQuestions(PostTest $postTest, array $data): array
    {
        DB::beginTransaction();
        try {
            $postTest->update([
                'title'         => $data['title'],
                'description'   => $data['description'] ?? null,
                'passing_score' => $data['passing_score'] ?? 70,
                'duration'      => $data['duration'] ?? 30,
            ]);

            foreach ($postTest->questions as $question) {
                $question->choices()->delete();
                $question->delete();
            }

            if (!empty($data['questions'])) {
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
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Post Test berhasil diperbarui',
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
}