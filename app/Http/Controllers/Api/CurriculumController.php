<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteLessonRequest;
use App\Http\Requests\EvaluateQuestionRequest;
use App\Http\Resources\ApiResponse;
use App\Services\CurriculumService;
use Illuminate\Http\JsonResponse;

class CurriculumController extends Controller
{
    public function __construct(private readonly CurriculumService $curriculum)
    {
    }

    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $payload = $this->curriculum->curriculum($request->user());

        return ApiResponse::success('Kurikulum IELTS', $payload);
    }

    public function session(\Illuminate\Http\Request $request, int $lessonId): JsonResponse
    {
        $payload = $this->curriculum->session($request->user(), $lessonId);

        return ApiResponse::success('Sesi latihan dimulai', $payload);
    }

    public function evaluateQuestion(EvaluateQuestionRequest $request, int $lessonId): JsonResponse
    {
        $payload = $this->curriculum->evaluateQuestion(
            $request->user(),
            $lessonId,
            $request->validated(),
        );

        return ApiResponse::success('Evaluasi soal tersimpan', $payload);
    }

    public function complete(CompleteLessonRequest $request, int $lessonId): JsonResponse
    {
        $payload = $this->curriculum->complete(
            $request->user(),
            $lessonId,
            $request->validated('session_id'),
        );

        return ApiResponse::success('Evaluasi selesai', $payload);
    }
}