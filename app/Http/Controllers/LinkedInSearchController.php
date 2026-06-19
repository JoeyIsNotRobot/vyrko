<?php

namespace App\Http\Controllers;

use App\Http\Requests\LinkedInSearch\LinkedInSearchRequest;
use App\Services\LinkedInSearch\LinkedInBooleanQueryBuilder;
use App\Services\LinkedInSearch\LinkedInSearchInput;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class LinkedInSearchController extends Controller
{
    public function index(): View
    {
        return view('linkedin-search.index');
    }

    public function generate(
        LinkedInSearchRequest $request,
        LinkedInBooleanQueryBuilder $builder,
    ): JsonResponse {
        $input   = LinkedInSearchInput::fromArray($request->validated());
        $queries = $builder->build($input);

        return response()->json([
            'queries' => array_map(fn($q) => $q->toArray(), $queries),
        ]);
    }
}
