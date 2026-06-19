<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithCareerState;
use App\Http\Requests\Import\ResumeImportRequest;
use App\Services\Import\ResumeImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class ResumeImportController extends Controller
{
    use RespondsWithCareerState;

    public function __invoke(ResumeImportRequest $request, ResumeImportService $service): RedirectResponse|JsonResponse
    {
        try {
            $service->importUploadedFile($request->user(), $request->file('resume'));
        } catch (RuntimeException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            return back()->withErrors(['resume' => $exception->getMessage()]);
        }

        return $this->careerResponse($request, __('messages.career.imported'));
    }
}
