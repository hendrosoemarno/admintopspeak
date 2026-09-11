<?php

namespace Tests\Feature;

use App\Enums\SubscriptionSource;
use App\Enums\SubscriptionStatus;
use App\Livewire\Admin\Subscriptions\Index as SubscriptionsIndex;
use App\Livewire\Admin\Users\Index as UsersIndex;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSessionQuotaLog;
use App\Models\UserSubscription;
use App\Services\Admin\ManualSubscriptionService;
use App\Services\Admin\SessionQuotaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSubscriptionManagementTest extends TestCase
{
    use RefreshDatabase;

    private function plan(array $overrides = []): SubscriptionPlan
    {
        return SubscriptionPlan::create(array_merge([
            'name' => 'Premium Bulanan',
            'type' => 'TIME',
            'duration_value' => 1,
            'duration_unit' => 'MONTH',
            'price_original' => 79000,
            'status' => 'ACTIVE',
        ], $overrides));
    }

    // ===================== MASTER PAKET =====================

    public function test_admin_can_create_time_plan(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.subscriptions.index'))->assertOk();

        Livewire::test(SubscriptionsIndex::class)
            ->call('openCreatePlan')
            ->set('plan_name', 'Premium Bulanan')
            ->set('plan_duration_value', 1)
            ->set('plan_duration_unit', 'MONTH')
            ->set('plan_price_original', 99000)
            ->set('plan_price_discount', 79000)
            ->set('plan_features', "Akses semua level\ntanpa iklan")
            ->call('savePlan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('subscription_plans', [
            'name' => 'Premium Bulanan',
            'type' => 'TIME',
            'duration_value' => 1,
            'duration_unit' => 'MONTH',
            'price_original' => '99000.00',
            'price_discount' => '79000.00',
        ]);

        $plan = SubscriptionPlan::where('name', 'Premium Bulanan')->first();
        $this->assertSame(['Akses semua level', 'tanpa iklan'], $plan->features);
    }

    public function test_admin_can_edit_plan(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = $this->plan();

        Livewire::actingAs($admin)->test(SubscriptionsIndex::class)
            ->call('openEditPlan', $plan->id)
            ->set('plan_name', 'Premium Tahunan')
            ->set('plan_duration_unit', 'YEAR')
            ->call('savePlan')
            ->assertHasNoErrors();

        $plan->refresh();
        $this->assertSame('Premium Tahunan', $plan->name);
        $this->assertSame('YEAR', $plan->duration_unit);
    }

    public function test_admin_can_delete_plan(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = $this->plan();

        Livewire::actingAs($admin)->test(SubscriptionsIndex::class)
            ->call('deletePlan', $plan->id);

        $this->assertDatabaseMissing('subscription_plans', ['id' => $plan->id]);
    }

    // ===================== LEVEL ACCESS =====================

    public function test_level_cefr_list_shows_all_six_levels(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)->test(SubscriptionsIndex::class)
            ->assertSee('Level CEFR')
            ->assertSee('A1')
            ->assertSee('Beginner (A1)')
            ->assertSee('A2')
            ->assertSee('Elementary (A2)')
            ->assertSee('B1')
            ->assertSee('Intermediate (B1)')
            ->assertSee('B2')
            ->assertSee('Upper-Intermediate (B2)')
            ->assertSee('C1')
            ->assertSee('Advanced (C1)')
            ->assertSee('C2')
            ->assertSee('Proficiency (C2)');
    }

    // ===================== MANUAL ASSIGN =====================

    public function test_assign_time_plan_activates_premium_and_creates_subscription(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['subscription_status' => SubscriptionStatus::FREE]);
        $plan = $this->plan();

        Livewire::actingAs($admin)->test(SubscriptionsIndex::class)
            ->call('openAssign', $user->id)
            ->set('assignPlanId', (string) $plan->id)
            ->call('assignPlan')
            ->assertHasNoErrors();

        $subscription = UserSubscription::where('user_id', $user->id)->firstOrFail();
        $this->assertTrue((bool) $subscription->is_active);
        $this->assertSame(SubscriptionSource::MANUAL, $subscription->source);
        $this->assertSame(SubscriptionStatus::PREMIUM_MONTHLY, $subscription->status);
        $this->assertSame(SubscriptionStatus::PREMIUM_MONTHLY, $user->fresh()->subscription_status);
        $this->assertNotNull($user->fresh()->subscription_expires_at);
        $this->assertTrue($user->fresh()->isPremiumActive());
    }

    public function test_assign_time_plan_honors_custom_expiry_and_note(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $plan = $this->plan(['name' => 'Premium Tahunan', 'duration_unit' => 'YEAR', 'price_original' => 599000]);

        $customExpiry = today()->addMonths(3);

        Livewire::actingAs($admin)->test(SubscriptionsIndex::class)
            ->call('openAssign', $user->id)
            ->set('assignPlanId', (string) $plan->id)
            ->set('assignExpiresAt', $customExpiry->toDateString())
            ->set('assignNote', 'kompensasi customer care')
            ->call('assignPlan')
            ->assertHasNoErrors();

        $subscription = UserSubscription::where('user_id', $user->id)->firstOrFail();
        $this->assertTrue($subscription->expires_at->isSameDay($customExpiry));
        $this->assertSame('kompensasi customer care', $subscription->admin_note);
        $this->assertSame(SubscriptionStatus::PREMIUM_YEARLY, $subscription->status);
        $this->assertSame(SubscriptionStatus::PREMIUM_YEARLY, $user->fresh()->subscription_status);
    }

    public function test_admin_can_change_expiry(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $plan = $this->plan();

        app(ManualSubscriptionService::class)->assignPlan($user, $plan);
        $subscription = UserSubscription::where('user_id', $user->id)->first();
        $newExpiry = Carbon::parse($subscription->expires_at)->addWeek();

        Livewire::actingAs($admin)->test(SubscriptionsIndex::class)
            ->call('openExpiry', $subscription->id)
            ->set('expiryAt', $newExpiry->format('Y-m-d\TH:i'))
            ->call('updateExpiry')
            ->assertHasNoErrors();

        $this->assertTrue($subscription->fresh()->expires_at->isSameDay($newExpiry));
        $this->assertTrue($user->fresh()->subscription_expires_at->isSameDay($newExpiry));
    }

    public function test_admin_can_cancel_subscription(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $plan = $this->plan();

        app(ManualSubscriptionService::class)->assignPlan($user, $plan);
        $subscription = UserSubscription::where('user_id', $user->id)->first();
        $this->assertTrue($user->fresh()->isPremiumActive());

        Livewire::actingAs($admin)->test(SubscriptionsIndex::class)
            ->call('cancelSubscription', $subscription->id)
            ->assertHasNoErrors();

        $this->assertFalse((bool) $subscription->fresh()->is_active);
        $this->assertFalse($user->fresh()->isPremiumActive());
        $this->assertSame(SubscriptionStatus::FREE, $user->fresh()->subscription_status);
    }

    // ===================== SESSION QUOTA ADJUSTER =====================

    public function test_admin_can_add_sessions_with_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['remaining_trial_sessions' => 5]);

        Livewire::actingAs($admin)->test(UsersIndex::class)
            ->call('openQuotaModal', $user->id)
            ->set('quotaOperation', 'add')
            ->set('quotaValue', 3)
            ->set('quotaReason', 'kompensasi downtime')
            ->call('applyQuota')
            ->assertHasNoErrors();

        $this->assertSame(8, $user->fresh()->remaining_trial_sessions);
        $this->assertDatabaseHas('user_session_quota_logs', [
            'user_id' => $user->id,
            'before_count' => 5,
            'after_count' => 8,
            'delta' => 3,
            'admin_id' => $admin->id,
            'reason' => 'kompensasi downtime',
        ]);
    }

    public function test_admin_can_subtract_sessions(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['remaining_trial_sessions' => 10]);

        Livewire::actingAs($admin)->test(UsersIndex::class)
            ->call('openQuotaModal', $user->id)
            ->set('quotaOperation', 'subtract')
            ->set('quotaValue', 4)
            ->set('quotaReason', 'koreksi admin')
            ->call('applyQuota')
            ->assertHasNoErrors();

        $this->assertSame(6, $user->fresh()->remaining_trial_sessions);
        $this->assertDatabaseHas('user_session_quota_logs', [
            'user_id' => $user->id,
            'before_count' => 10,
            'after_count' => 6,
            'delta' => -4,
        ]);
    }

    public function test_admin_can_set_sessions_exact(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['remaining_trial_sessions' => 3]);

        Livewire::actingAs($admin)->test(UsersIndex::class)
            ->call('openQuotaModal', $user->id)
            ->set('quotaOperation', 'set')
            ->set('quotaValue', 15)
            ->set('quotaReason', 'hadiah event')
            ->call('applyQuota')
            ->assertHasNoErrors();

        $this->assertSame(15, $user->fresh()->remaining_trial_sessions);
        $this->assertDatabaseHas('user_session_quota_logs', [
            'user_id' => $user->id,
            'before_count' => 3,
            'after_count' => 15,
            'delta' => 12,
        ]);
    }

    public function test_quota_adjust_requires_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['remaining_trial_sessions' => 5]);

        Livewire::actingAs($admin)->test(UsersIndex::class)
            ->call('openQuotaModal', $user->id)
            ->set('quotaOperation', 'add')
            ->set('quotaValue', 3)
            ->set('quotaReason', '')
            ->call('applyQuota')
            ->assertHasErrors('quotaReason');

        $this->assertSame(5, $user->fresh()->remaining_trial_sessions);
    }

    public function test_session_quota_service_subtract_never_goes_below_zero(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['remaining_trial_sessions' => 2]);

        app(SessionQuotaService::class)->add($user->id, -10, 'banyak koreksi', $admin->id);

        $this->assertSame(0, $user->fresh()->remaining_trial_sessions);
        $this->assertDatabaseHas('user_session_quota_logs', ['user_id' => $user->id, 'after_count' => 0]);
    }

    public function test_quota_logs_can_be_fetched_for_modal(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['remaining_trial_sessions' => 4]);

        UserSessionQuotaLog::create([
            'user_id' => $user->id,
            'admin_id' => $admin->id,
            'before_count' => 1,
            'after_count' => 4,
            'delta' => 3,
            'reason' => 'bonus',
        ]);

        $logs = Livewire::actingAs($admin)->test(UsersIndex::class)
            ->call('openLogModal', $user->id)
            ->call('quotaLogs', $user->id)
            ->assertReturned(fn ($found) => is_array($found) ? count($found) === 1 : $found->count() === 1);
    }

    // ===================== LEVEL EDIT (halaman user) =====================

    public function test_admin_can_change_user_cefr_level(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['current_cefr_level' => 'A1']);

        Livewire::actingAs($admin)->test(UsersIndex::class)
            ->call('startEditLevel', $user->id)
            ->set('levelField', 'B2')
            ->call('saveLevel')
            ->assertHasNoErrors();

        $this->assertSame('B2', $user->fresh()->current_cefr_level->value);

        $this->assertDatabaseHas('user_level_histories', [
            'user_id' => $user->id,
            'previous_level' => 'A1',
            'new_level' => 'B2',
        ]);
    }

    public function test_admin_changing_level_to_same_value_skips_history(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['current_cefr_level' => 'C1']);

        Livewire::actingAs($admin)->test(UsersIndex::class)
            ->call('startEditLevel', $user->id)
            ->set('levelField', 'C1')
            ->call('saveLevel')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('user_level_histories', 0);
    }

    public function test_user_level_edit_requires_valid_level(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['current_cefr_level' => 'A1']);

        Livewire::actingAs($admin)->test(UsersIndex::class)
            ->call('startEditLevel', $user->id)
            ->set('levelField', '')
            ->call('saveLevel')
            ->assertHasErrors('levelField');

        $this->assertSame('A1', $user->fresh()->current_cefr_level->value);
    }

    // ===================== ADJUST SISA MASA AKTIF (hari/bulan) =====================

    public function test_admin_can_add_days_to_active_subscription_expiry(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $plan = $this->plan();

        app(ManualSubscriptionService::class)->assignPlan($user, $plan);
        $subscription = UserSubscription::where('user_id', $user->id)->firstOrFail();
        $expected = (clone $subscription->expires_at)->addDays(3)->startOfSecond();

        Livewire::actingAs($admin)->test(UsersIndex::class)
            ->call('openDurationModal', $subscription->id)
            ->set('durationOperation', 'add')
            ->set('durationValue', 3)
            ->set('durationUnit', 'DAY')
            ->set('durationNote', 'perpanjangan kompensasi')
            ->call('adjustDuration')
            ->assertHasNoErrors();

        $subscription->refresh();
        $this->assertTrue($subscription->expires_at->startOfSecond()->equalTo($expected));
        $this->assertTrue($user->fresh()->subscription_expires_at->startOfSecond()->equalTo($expected));
        $this->assertTrue($user->fresh()->isPremiumActive());
        $this->assertSame('perpanjangan kompensasi', $subscription->admin_note);
    }

    public function test_admin_can_subtract_months_from_active_subscription_expiry(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $plan = $this->plan(['duration_value' => 6, 'duration_unit' => 'MONTH']);

        app(ManualSubscriptionService::class)->assignPlan($user, $plan);
        $subscription = UserSubscription::where('user_id', $user->id)->firstOrFail();
        $expected = (clone $subscription->expires_at)->subMonths(2)->startOfSecond();

        Livewire::actingAs($admin)->test(UsersIndex::class)
            ->call('openDurationModal', $subscription->id)
            ->set('durationOperation', 'subtract')
            ->set('durationValue', 2)
            ->set('durationUnit', 'MONTH')
            ->call('adjustDuration')
            ->assertHasNoErrors();

        $subscription->refresh();
        $this->assertTrue($subscription->expires_at->startOfSecond()->equalTo($expected));
        $this->assertTrue($user->fresh()->subscription_expires_at->startOfSecond()->equalTo($expected));
    }

    public function test_duration_adjust_requires_valid_input(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $plan = $this->plan();

        app(ManualSubscriptionService::class)->assignPlan($user, $plan);
        $subscription = UserSubscription::where('user_id', $user->id)->firstOrFail();
        $before = $subscription->expires_at->startOfSecond();

        Livewire::actingAs($admin)->test(UsersIndex::class)
            ->call('openDurationModal', $subscription->id)
            ->set('durationOperation', 'set')
            ->set('durationValue', 0)
            ->set('durationUnit', 'YEAR')
            ->call('adjustDuration')
            ->assertHasErrors(['durationOperation', 'durationValue', 'durationUnit']);

        $this->assertTrue($subscription->fresh()->expires_at->startOfSecond()->equalTo($before));
    }

    public function test_users_page_langganan_column_shows_plan_name_and_free_tier(): void
    {
        $admin = User::factory()->admin()->create();
        $premium = User::factory()->create(['name' => 'Nadia Premium']);
        $plan = $this->plan(['name' => 'Paket Harian Premium']);
        app(ManualSubscriptionService::class)->assignPlan($premium, $plan);

        User::factory()->create(['name' => 'Budi Free', 'subscription_status' => SubscriptionStatus::FREE]);

        Livewire::actingAs($admin)->test(UsersIndex::class)
            ->assertSee('Paket Harian Premium')
            ->assertSee('Budi Free')
            ->assertSee('Free Tier');
    }

    // ===================== LANGANAN EDIT (Free Tier / paket) =====================

    public function test_admin_can_change_user_subscription_with_time_plan(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['subscription_status' => SubscriptionStatus::FREE]);
        $plan = $this->plan(['name' => 'Premium Bulanan']);

        Livewire::actingAs($admin)->test(UsersIndex::class)
            ->call('startEditSub', $user->id)
            ->set('subPlanId', (string) $plan->id)
            ->set('subNote', 'ganti paket dari halaman user')
            ->call('saveSub')
            ->assertHasNoErrors();

        $subscription = UserSubscription::where('user_id', $user->id)->firstOrFail();
        $this->assertTrue((bool) $subscription->is_active);
        $this->assertSame(SubscriptionSource::MANUAL, $subscription->source);
        $this->assertSame(SubscriptionStatus::PREMIUM_MONTHLY, $user->fresh()->subscription_status);
        $this->assertTrue($user->fresh()->isPremiumActive());
        $this->assertSame('ganti paket dari halaman user', $subscription->admin_note);
    }

    public function test_admin_can_set_user_subscription_to_free_tier(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = $this->plan(['name' => 'Premium Bulanan']);
        $user = User::factory()->create(['subscription_status' => SubscriptionStatus::PREMIUM_MONTHLY]);
        UserSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'source' => SubscriptionSource::MANUAL,
            'status' => SubscriptionStatus::PREMIUM_MONTHLY,
            'started_at' => now(),
            'expires_at' => now()->addMonth(),
            'payment_status' => 'PAID',
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)->test(UsersIndex::class)
            ->call('startEditSub', $user->id)
            ->set('subPlanId', 'free')
            ->set('subNote', 'downgrade manual')
            ->call('saveSub')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertSame(SubscriptionStatus::FREE, $user->subscription_status);
        $this->assertFalse($user->isPremiumActive());
        $this->assertNull($user->subscription_expires_at);
        $this->assertFalse(UserSubscription::where('user_id', $user->id)->where('is_active', true)->exists());
        $this->assertSame('downgrade manual', UserSubscription::where('user_id', $user->id)->firstOrFail()->admin_note);
    }

    public function test_users_page_subscription_edit_requires_selection(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        Livewire::actingAs($admin)->test(UsersIndex::class)
            ->call('startEditSub', $user->id)
            ->set('subPlanId', '')
            ->call('saveSub')
            ->assertHasErrors('subPlanId');

        $this->assertSame(0, UserSubscription::count());
    }

    public function test_users_page_available_plans_computed_lists_all_active_plans_and_free_tier(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $this->plan(['name' => 'Premium Bulanan']);
        SubscriptionPlan::create([
            'name' => 'Paket Lama',
            'type' => 'TIME',
            'duration_value' => 3,
            'duration_unit' => 'MONTH',
            'price_original' => 50000,
            'status' => 'ARCHIVED',
        ]);
        SubscriptionPlan::create([
            'name' => 'Paket Hemat',
            'type' => 'TIME',
            'duration_value' => 6,
            'duration_unit' => 'MONTH',
            'price_original' => 150000,
            'status' => 'ACTIVE',
        ]);

        Livewire::actingAs($admin)->test(UsersIndex::class)
            ->call('startEditSub', $user->id)
            ->assertSee('Free Tier')
            ->assertSee('Premium Bulanan')
            ->assertSee('Paket Hemat')
            ->assertDontSee('Paket Lama');
    }

    // ===================== SORTING HALAMAN USERS =====================

    public function test_users_page_can_sort_by_name_asc_then_desc(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Alya']);
        User::factory()->create(['name' => 'Bima']);

        Livewire::actingAs($admin)->test(UsersIndex::class)
            ->assertSet('sortBy', 'id')
            ->assertSet('sortDir', 'desc')
            ->call('sortUsers', 'name')
            ->assertSet('sortBy', 'name')
            ->assertSet('sortDir', 'asc')
            ->assertSeeInOrder(['Alya', 'Bima'])
            ->call('sortUsers', 'name')
            ->assertSet('sortDir', 'desc')
            ->assertSeeInOrder(['Bima', 'Alya']);
    }

    public function test_users_page_sort_ignores_unknown_column(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)->test(UsersIndex::class)
            ->call('sortUsers', 'password')
            ->assertSet('sortBy', 'id')
            ->assertSet('sortDir', 'desc');
    }

    // ===================== STATUS DISPLAY & TAB =====================

    public function test_subscription_tab_renders_with_users(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'John Topspeak']);

        Livewire::actingAs($admin)->test(SubscriptionsIndex::class)
            ->call('setTab', 'subscriptions')
            ->assertSet('activeTab', 'subscriptions')
            ->assertSee('John Topspeak');
    }

    public function test_user_display_status_reflects_active_subscription(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $plan = $this->plan();

        app(ManualSubscriptionService::class)->assignPlan($user, $plan);

        $display = Livewire::actingAs($admin)->test(SubscriptionsIndex::class)
            ->call('userDisplayStatus', $user->fresh())
            ->assertReturned(fn ($val) => $val[0] === 'active' && $val[1] === 'Aktif');
    }

    public function test_user_display_status_reflects_free_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['subscription_status' => SubscriptionStatus::FREE]);

        $display = Livewire::actingAs($admin)->test(SubscriptionsIndex::class)
            ->call('userDisplayStatus', $user)
            ->assertReturned(fn ($val) => $val[0] === 'free' && $val[1] === 'Free');
    }
}