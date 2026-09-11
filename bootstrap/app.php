<?php

use App\Exceptions\AssessmentEvaluationUnavailableException;
use App\Exceptions\GrammarEvaluationUnavailableException;
use App\Exceptions\LessonEvaluationUnavailableException;
use App\Exceptions\LessonNotFoundException;
use App\Exceptions\NoQuestionAvailableException;
use App\Exceptions\PaywallRequiredException;
use App\Exceptions\RepetitionNotPendingException;
use App\Exceptions\SessionNotFoundException;
use App\Http\Resources\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error('Silakan masuk terlebih dahulu.', 401);
            }
        });

        $exceptions->render(function (Illuminate\Http\Exceptions\ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Terlalu banyak percobaan, coba lagi nanti.', 429);
            }
        });

        $exceptions->render(function (PaywallRequiredException $e, Request $request) {
            return ApiResponse::error($e->getMessage(), 402, [
                'is_paywalled' => true,
                'remaining_trial_sessions' => $request->user()?->remaining_trial_sessions ?? 0,
                'subscription_status' => $request->user()?->subscription_status->value ?? 'FREE',
            ]);
        });

        $exceptions->render(function (SessionNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        });

        $exceptions->render(function (NoQuestionAvailableException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        });

        $exceptions->render(function (LessonNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        });

        $exceptions->render(function (RepetitionNotPendingException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        });

        $exceptions->render(function (GrammarEvaluationUnavailableException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        });

        $exceptions->render(function (AssessmentEvaluationUnavailableException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        });

        $exceptions->render(function (LessonEvaluationUnavailableException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        });

        $exceptions->render(function (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        });

        $exceptions->render(function (Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                $firstMessage = collect($e->errors())->flatten()->first() ?? 'Data yang dikirim tidak valid.';

                return ApiResponse::error($firstMessage, 422, $e->errors());
            }
        });

        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Forbidden: only admin can access this resource.', 403);
            }
        });
    })->create();