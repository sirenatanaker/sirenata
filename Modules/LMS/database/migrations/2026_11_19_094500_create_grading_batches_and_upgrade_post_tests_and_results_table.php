<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Penambahan kolom pada tabel post_tests (assignments)
        if (Schema::hasTable('post_tests')) {
            Schema::table('post_tests', function (Blueprint $table) {
                if (!Schema::hasColumn('post_tests', 'grading_type')) {
                    $table->string('grading_type', 30)->default('MANUAL')->after('type');
                }
                if (!Schema::hasColumn('post_tests', 'rubric_json')) {
                    $table->json('rubric_json')->nullable()->after('grading_type');
                }
                if (!Schema::hasColumn('post_tests', 'template_extracted_text')) {
                    $table->longText('template_extracted_text')->nullable()->after('admin_file_size');
                }
                if (!Schema::hasColumn('post_tests', 'key_extracted_text')) {
                    $table->longText('key_extracted_text')->nullable()->after('answer_key_file_size');
                }
                if (!Schema::hasColumn('post_tests', 'passing_grade')) {
                    $table->decimal('passing_grade', 5, 2)->nullable()->after('passing_score');
                }
                if (!Schema::hasColumn('post_tests', 'is_submission_locked')) {
                    $table->boolean('is_submission_locked')->default(false)->after('max_words');
                }
            });

            // Sinkronisasi awal passing_grade & grading_type pada data lama
            DB::table('post_tests')->whereNull('passing_grade')->update([
                'passing_grade' => DB::raw('COALESCE(passing_score, 70)'),
            ]);
        }

        // 2. Tabel baru grading_batches
        if (!Schema::hasTable('grading_batches')) {
            Schema::create('grading_batches', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('post_test_id')->constrained('post_tests')->cascadeOnDelete();
                $table->uuid('bimtek_event_id')->nullable()->index();
                $table->foreignUuid('triggered_by')->nullable()->constrained('users')->nullOnDelete();
                $table->json('rubric_snapshot_json')->nullable();
                $table->unsignedInteger('total_targets')->default(0);
                $table->unsignedInteger('processed_count')->default(0);
                $table->unsignedInteger('failed_count')->default(0);
                $table->unsignedTinyInteger('consecutive_429_count')->default(0);
                $table->string('status', 30)->default('RUNNING')->index();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. Penambahan kolom pada tabel post_test_results (submissions)
        if (Schema::hasTable('post_test_results')) {
            Schema::table('post_test_results', function (Blueprint $table) {
                if (!Schema::hasColumn('post_test_results', 'grading_batch_id')) {
                    $table->foreignUuid('grading_batch_id')->nullable()->after('post_test_id')->constrained('grading_batches')->nullOnDelete();
                }
                if (!Schema::hasColumn('post_test_results', 'extracted_text')) {
                    $table->longText('extracted_text')->nullable()->after('file_name');
                }
                if (!Schema::hasColumn('post_test_results', 'is_truncated')) {
                    $table->boolean('is_truncated')->default(false)->after('extracted_text');
                }
                if (!Schema::hasColumn('post_test_results', 'grading_status')) {
                    $table->string('grading_status', 30)->default('PENDING')->index()->after('status');
                }
                if (!Schema::hasColumn('post_test_results', 'ai_score')) {
                    $table->decimal('ai_score', 5, 2)->nullable()->after('score');
                }
                if (!Schema::hasColumn('post_test_results', 'final_score')) {
                    $table->decimal('final_score', 5, 2)->nullable()->after('ai_score');
                }
                if (!Schema::hasColumn('post_test_results', 'ai_feedback_json')) {
                    $table->json('ai_feedback_json')->nullable()->after('notes');
                }
                if (!Schema::hasColumn('post_test_results', 'needs_manual_review')) {
                    $table->boolean('needs_manual_review')->default(false)->index()->after('ai_feedback_json');
                }
                if (!Schema::hasColumn('post_test_results', 'retry_count')) {
                    $table->unsignedTinyInteger('retry_count')->default(0)->after('needs_manual_review');
                }
                if (!Schema::hasColumn('post_test_results', 'last_error_message')) {
                    $table->text('last_error_message')->nullable()->after('retry_count');
                }
                if (!Schema::hasColumn('post_test_results', 'graded_at')) {
                    $table->timestamp('graded_at')->nullable()->after('last_error_message');
                }
            });

            // Backfill status lama agar konsisten dengan arsitektur baru
            DB::table('post_test_results')
                ->where('status', 'graded')
                ->update([
                    'grading_status' => 'PUBLISHED',
                    'final_score'    => DB::raw('score'),
                    'graded_at'      => DB::raw('COALESCE(updated_at, completed_at)'),
                ]);

            DB::table('post_test_results')
                ->where(fn($q) => $q->where('status', 'submitted')->orWhereNull('status'))
                ->update([
                    'grading_status' => 'PENDING',
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('post_test_results')) {
            Schema::table('post_test_results', function (Blueprint $table) {
                if (Schema::hasColumn('post_test_results', 'grading_batch_id')) {
                    $table->dropConstrainedForeignId('grading_batch_id');
                }
                $cols = array_filter([
                    'extracted_text',
                    'is_truncated',
                    'grading_status',
                    'ai_score',
                    'final_score',
                    'ai_feedback_json',
                    'needs_manual_review',
                    'retry_count',
                    'last_error_message',
                    'graded_at',
                ], fn($col) => Schema::hasColumn('post_test_results', $col));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        Schema::dropIfExists('grading_batches');

        if (Schema::hasTable('post_tests')) {
            Schema::table('post_tests', function (Blueprint $table) {
                $cols = array_filter([
                    'grading_type',
                    'rubric_json',
                    'template_extracted_text',
                    'key_extracted_text',
                    'passing_grade',
                    'is_submission_locked',
                ], fn($col) => Schema::hasColumn('post_tests', $col));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
