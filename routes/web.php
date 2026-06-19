<?php

use App\Http\Controllers\Account\EmailChangeController;
use App\Http\Controllers\AccountConnectionsController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\CandidateAchievementController;
use App\Http\Controllers\CandidateCertificationController;
use App\Http\Controllers\CandidateEducationController;
use App\Http\Controllers\CandidateExperienceController;
use App\Http\Controllers\CandidateLanguageController;
use App\Http\Controllers\CandidateProjectController;
use App\Http\Controllers\CandidateSkillController;
use App\Http\Controllers\CareerProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobAnalysisController;
use App\Http\Controllers\JobPostController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\LinkedinAnalysisController;
use App\Http\Controllers\LinkedinProfileController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\OnboardingImportController;
use App\Http\Controllers\ResumeImportController;
use App\Http\Controllers\ResumeVersionController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

Route::get('/terms', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/data-consent', [LegalController::class, 'dataConsent'])->name('legal.data-consent');
Route::get('/legal/social-data', [LegalController::class, 'socialData'])->name('legal.social-data');
Route::get('/locale/{locale}', LocaleController::class)->name('locale.switch');
Route::get('/account/email/confirm/{user}/{token}', EmailChangeController::class)->middleware('signed')->name('account.email.confirm');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::get('/auth/{provider}/consent', [SocialAuthController::class, 'consentCreate'])->name('auth.social.consent');
Route::post('/auth/{provider}/consent', [SocialAuthController::class, 'consentStore'])->name('auth.social.consent.store');
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('auth.social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('auth.social.callback');
Route::get('/auth/{provider}/email', [SocialAuthController::class, 'emailCreate'])->name('auth.social.email.create');
Route::post('/auth/{provider}/email', [SocialAuthController::class, 'emailStore'])->name('auth.social.email.store');

Route::middleware('auth')->group(function (): void {
    Route::get('/email/verify', fn (Request $request) => view('auth.verify-email', ['email' => $request->user()->email]))
        ->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('onboarding.index')->with('status', app()->getLocale() === 'en'
            ? 'Email verified. You can continue.'
            : 'E-mail confirmado. Você já pode continuar.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', app()->getLocale() === 'en'
            ? 'Verification link sent.'
            : 'Link de confirmação reenviado.');
    })->middleware('throttle:6,1')->name('verification.send');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/account', [AccountConnectionsController::class, 'index'])->name('account.index');
    Route::get('/account/connections', [AccountConnectionsController::class, 'index'])->name('account.connections');
    Route::put('/account/profile', [AccountConnectionsController::class, 'updateProfile'])->name('account.profile.update');
    Route::put('/account/email', [AccountConnectionsController::class, 'updateEmail'])->name('account.email.update');
    Route::put('/account/password', [AccountConnectionsController::class, 'updatePassword'])->name('account.password.update');
    Route::post('/account/email/resend', [AccountConnectionsController::class, 'resendVerification'])->name('account.email.resend');
    Route::post('/account/social/{provider}/disconnect', [AccountConnectionsController::class, 'disconnect'])->name('account.social.disconnect');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
    Route::get('/onboarding/import', [OnboardingImportController::class, 'index'])->name('onboarding.import');
    Route::post('/onboarding/import/text', [OnboardingImportController::class, 'storeText'])->name('onboarding.import.text');

    Route::get('/career', [CareerProfileController::class, 'index'])->name('career.index');
    Route::get('/career/profile/edit', [CareerProfileController::class, 'edit'])->name('career.profile.edit');
    Route::put('/career/profile', [CareerProfileController::class, 'update'])->name('career.profile.update');
    Route::post('/career/import', ResumeImportController::class)->name('career.import');

    Route::resource('experiences', CandidateExperienceController::class)->except('show');
    Route::resource('achievements', CandidateAchievementController::class)->except('show');
    Route::resource('skills', CandidateSkillController::class)->except('show');
    Route::resource('projects', CandidateProjectController::class)->except('show');
    Route::resource('educations', CandidateEducationController::class)->except('show');
    Route::resource('certifications', CandidateCertificationController::class)->except('show');
    Route::resource('languages', CandidateLanguageController::class)->except('show');

    Route::resource('jobs', JobPostController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->parameters(['jobs' => 'jobPost']);
    Route::post('/jobs/{jobPost}/analyze', JobAnalysisController::class)->name('jobs.analyze');
    Route::post('/jobs/{jobPost}/generate-resume', [ResumeVersionController::class, 'storeForJob'])->name('jobs.generate-resume');

    Route::resource('resumes', ResumeVersionController::class)
        ->only(['index', 'show'])
        ->parameters(['resumes' => 'resumeVersion']);
    Route::get('/resumes/{resumeVersion}/templates', [ResumeVersionController::class, 'templates'])->name('resumes.templates');
    Route::get('/resumes/{resumeVersion}/preview/{template}', [ResumeVersionController::class, 'preview'])->name('resumes.preview');
    Route::get('/resumes/{resumeVersion}/print/{template}', [ResumeVersionController::class, 'print'])->name('resumes.print');
    Route::post('/resumes/{resumeVersion}/run-ats-check', [ResumeVersionController::class, 'runAtsCheck'])->name('resumes.run-ats-check');

    Route::get('/linkedin', [LinkedinProfileController::class, 'index'])->name('linkedin.index');
    Route::post('/linkedin/profile', [LinkedinProfileController::class, 'store'])->name('linkedin.profile.store');
    Route::post('/linkedin/analyze', LinkedinAnalysisController::class)->name('linkedin.analyze');
});
