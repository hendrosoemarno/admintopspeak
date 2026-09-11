<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentEvaluateRequest;
use App\Http\Resources\ApiResponse;
use App\Models\AssessmentLog;
use App\Services\IeltsToeflEvaluator;
use Illuminate\Http\JsonResponse;

class AssessmentController extends Controller
{
    public function __construct(private readonly IeltsToeflEvaluator $evaluator)
    {
    }

    public function evaluate(AssessmentEvaluateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->evaluator->evaluate($data);

        AssessmentLog::create([
            'user_id' => $request->user()->id,
            'test_type' => $data['test_type'],
            'task_type' => $data['task_type'],
            'prompt_question' => $data['prompt_question'],
            'user_transcript' => $data['user_transcript'],
            'duration_seconds' => $data['duration_seconds'] ?? null,
            'overall_score' => $result['scores']['overall_score'],
            's_total' => $result['fluency_matrix']['s_total'],
            'final_fluency' => $result['fluency_matrix']['final_fluency'],
            'is_on_topic' => $result['content_alignment']['is_on_topic'] ?? true,
            'raw_response_json' => $result,
        ]);

        return ApiResponse::success('Evaluasi selesai', $result, 200);
    }
}
