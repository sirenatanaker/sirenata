<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('siapkerja_token')->nullable()->change();
            $table->text('siapkerja_refresh_token')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('siapkerja_token')->nullable()->change();
            $table->string('siapkerja_refresh_token')->nullable()->change();
        });
    }
};
