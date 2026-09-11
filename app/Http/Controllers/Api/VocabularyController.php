<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Services\VocabularyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VocabularyController extends Controller
{
    public function __construct(private readonly VocabularyService $vocabulary)
    {
    }

    public function bank(Request $request): JsonResponse
    {
        $perPage = min(50, max(1, $request->integer('per_page', 20)));

        $payload = $this->vocabulary->bank([
            'q' => $request->string('q')->toString(),
            'cefr_level' => $request->string('cefr_level')->toString(),
            'topic_category' => $request->string('topic_category')->toString(),
        ], $perPage);

        return ApiResponse::success('Bank kosakata', $payload);
    }

    public function learned(Request $request): JsonResponse
    {
        return ApiResponse::success(
            'Frasa yang telah dipelajari',
            $this->vocabulary->learned($request->user())->all(),
        );
    }
}