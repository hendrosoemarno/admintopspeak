<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Services\ProgressPredictorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressPredictorController extends Controller
{
    public function __construct(private readonly ProgressPredictorService $predictor)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success('Prediksi progres IELTS', $this->predictor->predictor($request->user()));
    }
}