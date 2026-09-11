<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use App\Services\UserDailyProgressService;
use App\Services\UserStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly UserStatsService $stats,
        private readonly UserDailyProgressService $dailyProgress,
    ) {
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $this->users->profileWithRelations($request->user());

        return ApiResponse::success('Profil pengguna', (new UserResource($user))->resolve());
    }

    public function stats(Request $request): JsonResponse
    {
        return ApiResponse::success('Statistik belajar', $this->stats->stats($request->user()));
    }

    public function dailyProgress(Request $request): JsonResponse
    {
        return ApiResponse::success('Progres harian', $this->dailyProgress->progress($request->user()));
    }
}