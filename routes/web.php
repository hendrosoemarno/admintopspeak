<?php

use App\Http\Controllers\Admin\AuthController;
use App\Livewire\Admin\AppConfig\Edit as AppConfigEdit;
use App\Livewire\Admin\Auth\Login as AdminLogin;
use App\Livewire\Admin\ConversationLogs\Index as ConversationLogsIndex;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\DataImport\Index as DataImportIndex;
use App\Livewire\Admin\DataTransformation\Index as DataTransformationIndex;
use App\Livewire\Admin\FillerWords\Index as FillerWordsIndex;
use App\Livewire\Admin\GrammarRules\Index as GrammarRulesIndex;
use App\Livewire\Admin\IeltsCurriculum\Index as IeltsCurriculumIndex;
use App\Livewire\Admin\PaymentGateway\Settings as PaymentGatewaySettings;
use App\Livewire\Admin\PaymentTest\Index as PaymentTestIndex;
use App\Livewire\Admin\PendingRules\Index as PendingRulesIndex;
use App\Livewire\Admin\QuestionBanks\Index as QuestionBanksIndex;
use App\Livewire\Admin\Subscriptions\Index as SubscriptionsIndex;
use App\Livewire\Admin\TestChatbot\Index as TestChatbotIndex;
use App\Livewire\Admin\ThematicTopics\Index as ThematicTopicsIndex;
use App\Livewire\Admin\Users\Index as UsersIndex;
use App\Livewire\Admin\Users\Show as UserShow;
use App\Livewire\Admin\VocabularyBank\Index as VocabularyBankIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.dashboard'));

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', AdminLogin::class)->name('login');
    });

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', Dashboard::class)->name('dashboard');
        Route::get('/users', UsersIndex::class)->name('users.index');
        Route::get('/users/{user}', UserShow::class)->name('users.show');
        Route::get('/question-banks', QuestionBanksIndex::class)->name('question-banks.index');
        Route::get('/grammar-rules', GrammarRulesIndex::class)->name('grammar-rules.index');
        Route::get('/ielts-curriculum', IeltsCurriculumIndex::class)->name('ielts-curriculum.index');
        Route::get('/grammar-rules/pending', PendingRulesIndex::class)->name('pending-rules.index');
        Route::get('/thematic-topics', ThematicTopicsIndex::class)->name('thematic-topics.index');
        Route::get('/vocabulary', VocabularyBankIndex::class)->name('vocabulary.index');
        Route::get('/filler-words', FillerWordsIndex::class)->name('filler-words.index');
        Route::get('/data-transformation', DataTransformationIndex::class)->name('data-transformation.index');
        Route::get('/conversation-logs', ConversationLogsIndex::class)->name('conversation-logs.index');
        Route::get('/subscriptions', SubscriptionsIndex::class)->name('subscriptions.index');
        Route::get('/payment-gateway', PaymentGatewaySettings::class)->name('payment-gateway.settings');
        Route::get('/payment-test', PaymentTestIndex::class)->name('payment-test.index');
        Route::get('/app-config', AppConfigEdit::class)->name('app-config.index');
        Route::get('/import', DataImportIndex::class)->name('data-import.index');
        Route::get('/test-chatbot', TestChatbotIndex::class)->name('test-chatbot.index');
    });
});