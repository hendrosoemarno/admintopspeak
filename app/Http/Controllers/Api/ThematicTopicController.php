<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\ThematicTopicResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\ThematicTopic;

class ThematicTopicController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $topics = $request->boolean('include_inactive', false)
            ? ThematicTopic::latest()->get()
            : ThematicTopic::where('is_active', true)->latest()->get();

        $payload = $topics
            ->map(fn (ThematicTopic $topic) => (new ThematicTopicResource($topic))->resolve())
            ->values();

        return ApiResponse::success('Topik tematik', $payload);
    }
}