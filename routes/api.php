<?php

use App\Http\Controllers\Api\AdminAppConfigController;
use App\Http\Controllers\Api\AdminGrammarRuleController;
use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CurriculumController;
use App\Http\Controllers\Api\ProgressPredictorController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\ThematicTopicController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\SystemController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VocabularyController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // System (public)
    Route::get('app-version', [SystemController::class, 'appVersion'])->name('api.app-version');

    // Payment callback (public webhook dari Duitku)
    Route::post('subscriptions/duitku-callback', [SubscriptionController::class, 'callback'])
        ->withoutMiddleware('auth:sanctum')
        ->name('api.subscriptions.callback');

    // Auth (public device registration)
    Route::post('auth/register-device', [AuthController::class, 'registerDevice'])->name('api.register-device');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/register', [AuthController::class, 'register'])->name('api.register');
        Route::post('auth/login', [AuthController::class, 'login'])
            ->middleware('throttle:login')
            ->name('api.login');
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.logout');

        Route::post('subscriptions/purchase', [SubscriptionController::class, 'purchase'])->name('api.subscriptions.purchase');
        Route::get('subscriptions/plans', [SubscriptionController::class, 'plans'])->name('api.subscriptions.plans');
        Route::get('subscriptions/payment-methods', [SubscriptionController::class, 'paymentMethods'])->name('api.subscriptions.payment-methods');

        Route::post('sessions/start', [SessionController::class, 'start'])->name('api.sessions.start');
        Route::post('sessions/evaluate-turn', [SessionController::class, 'evaluateTurn'])->name('api.sessions.evaluate-turn');
        Route::post('sessions/verify-repetition', [SessionController::class, 'verifyRepetition'])->name('api.sessions.verify-repetition');
        Route::post('sessions/complete', [SessionController::class, 'complete'])->name('api.sessions.complete');
        Route::get('sessions/history', [SessionController::class, 'history'])->name('api.sessions.history');
        Route::get('sessions/{id}', [SessionController::class, 'show'])->name('api.sessions.show');

        Route::post('assessment/evaluate', [AssessmentController::class, 'evaluate'])->name('api.assessment.evaluate');

        Route::get('curriculum', [CurriculumController::class, 'index'])->name('api.curriculum.index');
        Route::get('curriculum/lessons/{lessonId}/session', [CurriculumController::class, 'session'])->name('api.curriculum.session');
        Route::post('curriculum/lessons/{lessonId}/evaluate-question', [CurriculumController::class, 'evaluateQuestion'])->name('api.curriculum.evaluate-question');
        Route::post('curriculum/lessons/{lessonId}/complete', [CurriculumController::class, 'complete'])->name('api.curriculum.complete');

        Route::get('user/profile', [UserController::class, 'profile'])->name('api.user.profile');
        Route::get('user/stats', [UserController::class, 'stats'])->name('api.user.stats');
        Route::get('user/daily-progress', [UserController::class, 'dailyProgress'])->name('api.user.daily-progress');
        Route::get('user/progress-predictor', [ProgressPredictorController::class, 'index'])->name('api.user.progress-predictor');
        Route::get('thematic-topics', [ThematicTopicController::class, 'index'])->name('api.thematic-topics');

        Route::get('vocabulary', [VocabularyController::class, 'bank'])->name('api.vocabulary.bank');
        Route::get('vocabulary/learned', [VocabularyController::class, 'learned'])->name('api.vocabulary.learned');

        // Admin endpoints
        Route::middleware('admin')->prefix('admin')->group(function () {
            Route::post('grammar-rules', [AdminGrammarRuleController::class, 'store'])->name('api.admin.grammar-rules.store');
            Route::post('app-config', [AdminAppConfigController::class, 'update'])->name('api.admin.app-config.update');
        });
    });
});