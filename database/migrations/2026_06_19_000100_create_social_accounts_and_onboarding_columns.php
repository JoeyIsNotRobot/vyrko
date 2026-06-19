<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('password_set_at')->nullable()->after('password');
            $table->timestamp('onboarding_completed_at')->nullable()->after('ai_credits_balance');
        });

        DB::table('users')
            ->whereNotNull('password')
            ->update(['password_set_at' => now()]);

        Schema::table('candidate_profiles', function (Blueprint $table): void {
            $table->string('professional_area')->nullable()->after('target_seniority');
            $table->string('onboarding_source')->nullable()->after('professional_area');
        });

        Schema::create('social_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 40);
            $table->string('provider_user_id');
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->string('avatar_url')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->json('raw_profile')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_user_id']);
            $table->index(['user_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');

        Schema::table('candidate_profiles', function (Blueprint $table): void {
            $table->dropColumn(['professional_area', 'onboarding_source']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['password_set_at', 'onboarding_completed_at']);
        });
    }
};
