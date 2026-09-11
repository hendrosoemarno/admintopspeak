<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGrammarRuleRequest;
use App\Http\Resources\ApiResponse;
use App\Models\GrammarRule;
use Illuminate\Http\JsonResponse;

class AdminGrammarRuleController extends Controller
{
    public function store(StoreGrammarRuleRequest $request): JsonResponse
    {
        $rule = GrammarRule::create($request->validated());

        return ApiResponse::success('Grammar rule berhasil dibuat', $rule->toArray(), 201);
    }
}