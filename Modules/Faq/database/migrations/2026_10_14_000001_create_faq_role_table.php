<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('faq_role', function (Blueprint $table) {
            $table->unsignedBigInteger('faq_id');
            $table->uuid('role_id');

            $table->foreign('faq_id')->references('id')->on('faqs')->cascadeOnDelete();
            $table->foreign('role_id')->references('uuid')->on('roles')->cascadeOnDelete();

            $table->primary(['faq_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_role');
    }
};
