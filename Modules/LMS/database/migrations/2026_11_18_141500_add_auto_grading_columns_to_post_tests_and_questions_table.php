<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('post_tests')) {
            Schema::table('post_tests', function (Blueprint $table) {
                if (!Schema::hasColumn('post_tests', 'answer_key_file_path')) {
                    $table->string('answer_key_file_path')->nullable()->after('admin_file_size');
                }
                if (!Schema::hasColumn('post_tests', 'answer_key_file_name')) {
                    $table->string('answer_key_file_name')->nullable()->after('answer_key_file_path');
                }
                if (!Schema::hasColumn('post_tests', 'answer_key_file_size')) {
                    $table->string('answer_key_file_size')->nullable()->after('answer_key_file_name');
                }
            });
        }

        if (Schema::hasTable('post_test_questions')) {
            Schema::table('post_test_questions', function (Blueprint $table) {
                if (!Schema::hasColumn('post_test_questions', 'answer_key')) {
                    $table->text('answer_key')->nullable()->after('question');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('post_tests')) {
            Schema::table('post_tests', function (Blueprint $table) {
                $cols = array_filter(
                    ['answer_key_file_path', 'answer_key_file_name', 'answer_key_file_size'],
                    fn($col) => Schema::hasColumn('post_tests', $col)
                );
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('post_test_questions')) {
            Schema::table('post_test_questions', function (Blueprint $table) {
                if (Schema::hasColumn('post_test_questions', 'answer_key')) {
                    $table->dropColumn('answer_key');
                }
            });
        }
    }
};
