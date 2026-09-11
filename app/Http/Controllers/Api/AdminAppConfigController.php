<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAppConfigRequest;
use App\Http\Resources\ApiResponse;
use App\Models\AppConfiguration;
use Illuminate\Http\JsonResponse;

class AdminAppConfigController extends Controller
{
    public function update(UpdateAppConfigRequest $request): JsonResponse
    {
        $config = AppConfiguration::first() ?? new AppConfiguration;

        $config->fill($request->validated())->save();

        return ApiResponse::success('Konfigurasi aplikasi diperbarui', $config->fresh());
    }
}