<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Dokumen Lampiran Banyak
        Schema::create('section_content_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('section_content_id')->constrained('section_contents')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();
        });

        // 2. Tabel Pivot Koleksi Perpustakaan Banyak
        Schema::create('section_content_libraries', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('section_content_id')->constrained('section_contents')->cascadeOnDelete();
            $table->foreignUuid('library_id')->constrained('libraries')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['section_content_id', 'library_id']);
        });

        // 3. Migrasikan data legacy yang sudah ada di section_contents jika ada
        $existingContents = DB::table('section_contents')->get();
        foreach ($existingContents as $content) {
            if (!empty($content->document)) {
                DB::table('section_content_documents')->insert([
                    'id'                 => (string) Str::uuid(),
                    'section_content_id' => $content->id,
                    'file_path'          => $content->document,
                    'file_name'          => basename($content->document),
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            if (!empty($content->library_id)) {
                DB::table('section_content_libraries')->insert([
                    'section_content_id' => $content->id,
                    'library_id'         => $content->library_id,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_content_libraries');
        Schema::dropIfExists('section_content_documents');
    }
};
