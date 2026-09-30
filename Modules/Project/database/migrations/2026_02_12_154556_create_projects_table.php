<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration')->comment('Duration in months');
            
            $table->foreignUuid('team_leader')->nullable()->constrained('users');
            $table->json('team_members')->nullable();
            
            $table->string('type')->comment('Nasional, Provinsi, Kab/Kota');
            
            $table->string('status')->nullable()->default('Draft'); 

            $table->string('sk_document')->nullable()->comment('Path penyimpanan file Dokumen SK (PDF)');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_prerequisite_active')->default(false)->comment('Apakah prasyarat kursus aktif?');
            $table->uuid('prerequisite_course_id')->nullable()->comment('ID dari tabel courses LMS');
            $table->json('prerequisite_course_ids')->nullable()->comment('Daftar ID kursus prasyarat LMS');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};