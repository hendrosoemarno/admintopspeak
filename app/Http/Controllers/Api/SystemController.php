<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\AppConfigurationResource;
use App\Models\AppConfiguration;
use Illuminate\Http\JsonResponse;

class SystemController extends Controller
{
    public function appVersion(): JsonResponse
    {
        $config = AppConfiguration::latest()->first() ?? new AppConfiguration;

        return ApiResponse::success('Versi aplikasi', (new AppConfigurationResource($config))->resolve());
    }
}