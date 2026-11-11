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
        Schema::table('bimtek_events', function (Blueprint $table) {
            $table->integer('quota')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bimtek_events', function (Blueprint $table) {
            $table->integer('quota')->default(100)->nullable(false)->change();
        });
    }
};
