<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resume_versions', function (Blueprint $table): void {
            $table->text('cover_letter_text')->nullable()->after('plain_text');
        });
    }

    public function down(): void
    {
        Schema::table('resume_versions', function (Blueprint $table): void {
            $table->dropColumn('cover_letter_text');
        });
    }
};
