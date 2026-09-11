<?php

namespace Tests\Feature\Api;

use App\Enums\LessonProgressStatus;
use App\Enums\SessionMode;
use App\Models\ConversationSession;
use App\Models\Lesson;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserLessonProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserDailyProgressTest extends TestCase
{
    use RefreshDatabase;

    private const TIMEZONE = 'Asia/Jakarta';

    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unit::create([
            'unit_number' => 1,
            'title' => 'Home & Family',
            'part' => 1,
            'outcome' => 'Menjawab pertanyaan pribadi tentang rumah dan keluarga.',
        ]);
    }

    private function utcTimestamp(int $daysAgo, string $time = '08:00:00'): string
    {
        return Carbon::parse(now(self::TIMEZONE)->subDays($daysAgo)->toDateString().' '.$time, self::TIMEZONE)
            ->setTimezone('UTC')
            ->format('Y-m-d H:i:s');
    }

    private function completedSession(int $userId, SessionMode $mode, int $daysAgo): void
    {
        ConversationSession::create([
            'user_id' => $userId,
            'mode' => $mode,
            'start_level' => 'A1',
            'current_level' => 'A2',
            'total_turns_planned' => 5,
            'status' => 'COMPLETED',
            'completed_at' => $this->utcTimestamp($daysAgo),
        ]);
    }

    private function passedLesson(int $userId, int $daysAgo): void
    {
        $lesson = Lesson::create([
            'unit_id' => $this->unit->id,
            'lesson_number' => $daysAgo,
            'title' => "Lesson {$daysAgo}",
            'difficulty' => 'Easy',
        ]);

        $progress = UserLessonProgress::create([
            'user_id' => $userId,
            'lesson_id' => $lesson->id,
            'status' => LessonProgressStatus::PASSED,
        ]);
        $progress->created_at = $this->utcTimestamp($daysAgo, '10:00:00');
        $progress->save();
    }

    public function test_daily_progress_is_zero_for_new_user(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('api/v1/user/daily-progress')
            ->assertOk()
            ->assertJsonPath('data.adaptive_sessions', 0)
            ->assertJsonPath('data.thematic_sessions', 0)
            ->assertJsonPath('data.exam_lessons', 0)
            ->assertJsonPath('data.streak_days', 0)
            ->assertJsonPath('data.date', now(self::TIMEZONE)->toDateString())
            ->assertJsonCount(7, 'data.last_7_days');

        foreach ($response->json('data.last_7_days') as $day) {
            $this->assertSame(false, $day['completed']);
            $this->assertNotEmpty($day['date']);
        }
    }

    public function test_counts_today_sessions_and_lessons(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->completedSession($user->id, SessionMode::ADAPTIVE, 0);
        $this->completedSession($user->id, SessionMode::THEMATIC, 0);
        $this->completedSession($user->id, SessionMode::THEMATIC, 0);
        $this->completedSession($user->id, SessionMode::ADAPTIVE, 1); // kemarin, tidak dihitung
        $this->completedSession($user->id, SessionMode::IELTS_SPEAKING, 0); // bukan kategori, tetap "hari penuh"
        $this->passedLesson($user->id, 0);

        $this->getJson('api/v1/user/daily-progress')
            ->assertOk()
            ->assertJsonPath('data.adaptive_sessions', 1)
            ->assertJsonPath('data.thematic_sessions', 2)
            ->assertJsonPath('data.exam_lessons', 1)
            ->assertJsonPath('data.streak_days', 2);
    }

    public function test_streak_spans_consecutive_days(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->completedSession($user->id, SessionMode::ADAPTIVE, 0);
        $this->completedSession($user->id, SessionMode::THEMATIC, 1);
        $this->passedLesson($user->id, 2);

        $response = $this->getJson('api/v1/user/daily-progress')
            ->assertOk()
            ->assertJsonPath('data.streak_days', 3);

        $completed = collect($response->json('data.last_7_days'))
            ->where('completed', true)
            ->pluck('date')
            ->all();

        $this->assertSame([
            now(self::TIMEZONE)->subDays(2)->toDateString(),
            now(self::TIMEZONE)->subDay()->toDateString(),
            now(self::TIMEZONE)->toDateString(),
        ], $completed);
    }

    public function test_streak_counted_from_yesterday_when_today_empty(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->completedSession($user->id, SessionMode::ADAPTIVE, 1);
        $this->completedSession($user->id, SessionMode::ADAPTIVE, 2);

        $this->getJson('api/v1/user/daily-progress')
            ->assertOk()
            ->assertJsonPath('data.streak_days', 2);
    }

    public function test_streak_zero_when_recent_activity_is_old_and_lessons_break_gap(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->passedLesson($user->id, 3);
        $this->completedSession($user->id, SessionMode::THEMATIC, 4);

        $this->getJson('api/v1/user/daily-progress')
            ->assertOk()
            ->assertJsonPath('data.streak_days', 0);
    }

    public function test_daily_progress_requires_authentication(): void
    {
        $this->getJson('api/v1/user/daily-progress')->assertUnauthorized();
    }
}