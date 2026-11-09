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
                if (!Schema::hasColumn('post_tests', 'type')) {
                    $table->string('type')->default('multiple_choice')->after('duration');
                }
                if (!Schema::hasColumn('post_tests', 'admin_file_path')) {
                    $table->string('admin_file_path')->nullable()->after('type');
                }
                if (!Schema::hasColumn('post_tests', 'admin_file_name')) {
                    $table->string('admin_file_name')->nullable()->after('admin_file_path');
                }
                if (!Schema::hasColumn('post_tests', 'admin_file_size')) {
                    $table->string('admin_file_size')->nullable()->after('admin_file_name');
                }
                if (!Schema::hasColumn('post_tests', 'instructions')) {
                    $table->text('instructions')->nullable()->after('admin_file_size');
                }
                if (!Schema::hasColumn('post_tests', 'max_words')) {
                    $table->integer('max_words')->nullable()->default(500)->after('instructions');
                }
            });
        }

        if (Schema::hasTable('post_test_results')) {
            Schema::table('post_test_results', function (Blueprint $table) {
                if (!Schema::hasColumn('post_test_results', 'answers')) {
                    $table->json('answers')->nullable()->after('score');
                }
                if (!Schema::hasColumn('post_test_results', 'file_path')) {
                    $table->string('file_path')->nullable()->after('answers');
                }
                if (!Schema::hasColumn('post_test_results', 'file_name')) {
                    $table->string('file_name')->nullable()->after('file_path');
                }
                if (!Schema::hasColumn('post_test_results', 'notes')) {
                    $table->text('notes')->nullable()->after('file_name');
                }
                if (!Schema::hasColumn('post_test_results', 'status')) {
                    $table->string('status')->default('completed')->after('is_passed');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('post_tests')) {
            Schema::table('post_tests', function (Blueprint $table) {
                $table->dropColumn(['type', 'admin_file_path', 'admin_file_name', 'admin_file_size', 'instructions', 'max_words']);
            });
        }

        if (Schema::hasTable('post_test_results')) {
            Schema::table('post_test_results', function (Blueprint $table) {
                $table->dropColumn(['answers', 'file_path', 'file_name', 'notes', 'status']);
            });
        }
    }
};
