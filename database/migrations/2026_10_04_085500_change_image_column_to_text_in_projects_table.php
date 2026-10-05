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
        Schema::table('projects', function (Blueprint $table) {
            // Drop string and replace with json/text to support multiple images
            // In SQLite, change() can be tricky. A simpler way is using change() if doctrine/dbal is installed, 
            // but for safety in older Laravel/SQLite we can just change to json.
            $table->text('image')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('image')->nullable()->change();
        });
    }
};
