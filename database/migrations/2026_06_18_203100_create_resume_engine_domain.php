<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('plan_name')->default('free');
            $table->unsignedInteger('monthly_resume_generations_limit')->default(5);
            $table->unsignedInteger('monthly_job_analysis_limit')->default(10);
            $table->unsignedInteger('monthly_linkedin_analysis_limit')->default(2);
            $table->unsignedInteger('ai_credits_balance')->default(0);
        });

        Schema::create('candidate_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('headline')->nullable();
            $table->string('location_city')->nullable();
            $table->string('location_state')->nullable();
            $table->string('location_country')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->text('summary')->nullable();
            $table->string('target_role')->nullable();
            $table->string('target_seniority')->nullable();
            $table->string('preferred_language', 8)->default('pt_BR');
            $table->timestamps();
        });

        Schema::create('candidate_experiences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('role_title');
            $table->string('employment_type')->nullable();
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->json('responsibilities')->nullable();
            $table->json('technologies')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'sort_order']);
        });

        Schema::create('candidate_achievements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_experience_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('impact_metric')->nullable();
            $table->json('evidence_tags')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'sort_order']);
        });

        Schema::create('candidate_skills', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->default('other');
            $table->string('proficiency_level')->nullable();
            $table->unsignedInteger('years_of_experience')->nullable();
            $table->text('evidence_notes')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'category']);
        });

        Schema::create('candidate_projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('role')->nullable();
            $table->json('technologies')->nullable();
            $table->string('url')->nullable();
            $table->string('repository_url')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->json('highlights')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });

        Schema::create('candidate_educations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('institution');
            $table->string('degree');
            $table->string('field_of_study')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });

        Schema::create('candidate_certifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('issuer')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('credential_url')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });

        Schema::create('candidate_languages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('language');
            $table->string('proficiency');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });

        Schema::create('job_posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('company_name')->nullable();
            $table->longText('job_description');
            $table->string('target_language', 8)->default('pt_BR');
            $table->string('resume_type')->default('tech');
            $table->text('notes')->nullable();
            $table->json('parsed_requirements')->nullable();
            $table->json('parsed_keywords')->nullable();
            $table->json('parsed_responsibilities')->nullable();
            $table->string('parsed_seniority')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });

        Schema::create('job_match_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_post_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('overall_score')->default(0);
            $table->unsignedTinyInteger('technical_score')->default(0);
            $table->unsignedTinyInteger('experience_score')->default(0);
            $table->unsignedTinyInteger('seniority_score')->default(0);
            $table->unsignedTinyInteger('keyword_score')->default(0);
            $table->unsignedTinyInteger('ats_format_score')->default(0);
            $table->unsignedTinyInteger('human_readability_score')->default(0);
            $table->json('strengths')->nullable();
            $table->json('gaps')->nullable();
            $table->json('warnings')->nullable();
            $table->json('recommendations')->nullable();
            $table->json('evidence_map')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'job_post_id']);
        });

        Schema::create('resume_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_post_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_match_report_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('language', 8)->default('pt_BR');
            $table->string('resume_type')->default('tech');
            $table->json('content');
            $table->longText('plain_text')->nullable();
            $table->string('status')->default('draft');
            $table->json('ats_checklist')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });

        Schema::create('linkedin_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('headline')->nullable();
            $table->text('about')->nullable();
            $table->longText('experiences_text')->nullable();
            $table->longText('skills_text')->nullable();
            $table->longText('raw_text')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });

        Schema::create('linkedin_analysis_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('linkedin_profile_id')->constrained()->cascadeOnDelete();
            $table->string('target_role')->nullable();
            $table->string('target_language', 8)->default('pt_BR');
            $table->unsignedTinyInteger('score')->default(0);
            $table->json('strengths')->nullable();
            $table->json('weaknesses')->nullable();
            $table->json('recommendations')->nullable();
            $table->string('rewritten_headline')->nullable();
            $table->text('rewritten_about')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });

        Schema::create('usage_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('feature');
            $table->string('status')->default('consumed');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'feature', 'created_at']);
        });

        Schema::create('ai_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('feature');
            $table->string('provider')->default('fake');
            $table->string('model')->nullable();
            $table->string('prompt_hash');
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->decimal('cost_estimate', 10, 4)->nullable();
            $table->string('status')->default('succeeded');
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['user_id', 'feature', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_runs');
        Schema::dropIfExists('usage_logs');
        Schema::dropIfExists('linkedin_analysis_reports');
        Schema::dropIfExists('linkedin_profiles');
        Schema::dropIfExists('resume_versions');
        Schema::dropIfExists('job_match_reports');
        Schema::dropIfExists('job_posts');
        Schema::dropIfExists('candidate_languages');
        Schema::dropIfExists('candidate_certifications');
        Schema::dropIfExists('candidate_educations');
        Schema::dropIfExists('candidate_projects');
        Schema::dropIfExists('candidate_skills');
        Schema::dropIfExists('candidate_achievements');
        Schema::dropIfExists('candidate_experiences');
        Schema::dropIfExists('candidate_profiles');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'plan_name',
                'monthly_resume_generations_limit',
                'monthly_job_analysis_limit',
                'monthly_linkedin_analysis_limit',
                'ai_credits_balance',
            ]);
        });
    }
};
