<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class JobFetchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'job_id' => ['required', 'string', 'regex:/^\d{1,20}$/'],
        ]);

        $jobId = $request->input('job_id');

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
        ])->timeout(10)->get("https://www.linkedin.com/jobs-guest/jobs/api/jobPosting/{$jobId}");

        if (! $response->successful()) {
            return response()->json(['error' => 'not_found'], 422);
        }

        $jsonLd = $this->extractJsonLd($response->body());

        if (! $jsonLd) {
            return response()->json(['error' => 'parse_failed'], 422);
        }

        $rawDescription = $jsonLd['description'] ?? null;

        return response()->json([
            'title' => isset($jsonLd['title']) ? trim((string) $jsonLd['title']) : null,
            'company' => isset($jsonLd['hiringOrganization']['name'])
                ? trim((string) $jsonLd['hiringOrganization']['name'])
                : null,
            'description' => $rawDescription !== null
                ? trim(strip_tags((string) $rawDescription))
                : null,
        ]);
    }

    private function extractJsonLd(string $html): ?array
    {
        if (! preg_match(
            '/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si',
            $html,
            $matches
        )) {
            return null;
        }

        $decoded = json_decode($matches[1], true);

        if (! is_array($decoded) || ($decoded['@type'] ?? null) !== 'JobPosting') {
            return null;
        }

        return $decoded;
    }
}
