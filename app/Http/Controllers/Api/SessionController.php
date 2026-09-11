<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteSessionRequest;
use App\Http\Requests\EvaluateTurnRequest;
use App\Http\Requests\StartSessionRequest;
use App\Http\Requests\VerifyRepetitionRequest;
use App\Http\Resources\ApiResponse;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function __construct(private readonly SessionService $sessions)
    {
    }

    public function start(StartSessionRequest $request): JsonResponse
    {
        $payload = $this->sessions->startSession(
            $request->user(),
            $request->validated('mode', 'ADAPTIVE'),
            $request->validated('topic_id'),
            $request->validated('lesson_id'),
        );

        return ApiResponse::success('Sesi latihan dimulai', $payload, 201);
    }

    public function evaluateTurn(EvaluateTurnRequest $request): JsonResponse
    {
        $payload = $this->sessions->evaluateTurn($request->user(), $request->validated());

        return ApiResponse::success('Turn dievaluasi', $payload);
    }

    public function verifyRepetition(VerifyRepetitionRequest $request): JsonResponse
    {
        $payload = $this->sessions->verifyRepetition($request->user(), $request->validated());

        return ApiResponse::success('Repetition diverifikasi', $payload);
    }

    public function complete(CompleteSessionRequest $request): JsonResponse
    {
        $payload = $this->sessions->completeSession($request->user(), $request->validated('session_id'));

        return ApiResponse::success('Sesi selesai', $payload);
    }

    public function history(Request $request): JsonResponse
    {
        $history = $this->sessions->history(
            $request->user(),
            (int) $request->integer('per_page', 10),
        );

        return ApiResponse::success('Riwayat sesi', $history);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $payload = $this->sessions->show($request->user(), $id);

        return ApiResponse::success('Detail sesi', $payload);
    }
}