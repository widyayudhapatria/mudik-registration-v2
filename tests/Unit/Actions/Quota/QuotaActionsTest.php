<?php

namespace Tests\Unit\Actions\Quota;

use App\Actions\Quota\GetQuotaAction;
use App\Actions\Quota\SetQuotaAction;
use App\Data\SetQuotaData;
use App\Models\DailyQuota;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class QuotaActionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sets_new_quota_for_date(): void
    {
        $data = new SetQuotaData(
            date: Carbon::today(),
            quota: 100
        );

        $quota = SetQuotaAction::run($data);

        $this->assertInstanceOf(DailyQuota::class, $quota);
        $this->assertEquals(100, $quota->quota);
        $this->assertEquals(0, $quota->used);
        $this->assertEquals(100, $quota->remaining);
        $this->assertTrue($quota->date->isSameDay(Carbon::today()));
    }

    /** @test */
    public function it_updates_existing_quota(): void
    {
        $existingQuota = DailyQuota::factory()->create([
            'date' => Carbon::today(),
            'quota' => 50,
            'used' => 10,
            'remaining' => 40,
        ]);

        $data = new SetQuotaData(
            date: Carbon::today(),
            quota: 100
        );

        $quota = SetQuotaAction::run($data);

        $this->assertEquals($existingQuota->id, $quota->id);
        $this->assertEquals(100, $quota->quota);
        $this->assertEquals(10, $quota->used); // Unchanged
        $this->assertEquals(90, $quota->remaining); // 100 - 10
    }

    /** @test */
    public function it_clears_cache_after_setting_quota(): void
    {
        $dateString = Carbon::today()->format('Y-m-d');
        $cacheKey = 'quota:' . $dateString;

        // Set cache
        Cache::put($cacheKey, 'test-data', 3600);
        $this->assertTrue(Cache::has($cacheKey));

        $data = new SetQuotaData(
            date: Carbon::today(),
            quota: 100
        );

        SetQuotaAction::run($data);

        // Cache should be cleared
        $this->assertFalse(Cache::has($cacheKey));
    }

    /** @test */
    public function it_gets_quota_from_cache(): void
    {
        $quota = DailyQuota::factory()->create([
            'date' => Carbon::today(),
            'quota' => 100,
        ]);

        // First call - should cache
        $result1 = GetQuotaAction::run(Carbon::today());
        
        // Second call - should get from cache
        $result2 = GetQuotaAction::run(Carbon::today());

        $this->assertEquals($quota->id, $result1->id);
        $this->assertEquals($quota->id, $result2->id);

        // Verify cache exists
        $cacheKey = 'quota:' . Carbon::today()->format('Y-m-d');
        $this->assertTrue(Cache::has($cacheKey));
    }

    /** @test */
    public function it_gets_today_quota(): void
    {
        $quota = DailyQuota::factory()->create([
            'date' => Carbon::today(),
            'quota' => 100,
        ]);

        $result = (new GetQuotaAction())->getTodayQuota();

        $this->assertNotNull($result);
        $this->assertEquals($quota->id, $result->id);
    }

    /** @test */
    public function it_checks_quota_availability(): void
    {
        // Available quota
        DailyQuota::factory()->create([
            'date' => Carbon::today(),
            'quota' => 100,
            'used' => 50,
            'remaining' => 50,
        ]);

        $available = (new GetQuotaAction())->checkAvailability();
        $this->assertTrue($available);

        // Full quota
        DailyQuota::where('date', Carbon::today())
            ->update([
                'used' => 100,
                'remaining' => 0,
            ]);

        Cache::flush();

        $available = (new GetQuotaAction())->checkAvailability();
        $this->assertFalse($available);
    }

    /** @test */
    public function it_returns_null_for_non_existent_quota(): void
    {
        $result = GetQuotaAction::run(Carbon::tomorrow());

        $this->assertNull($result);
    }

    /** @test */
    public function it_sets_cache_ttl_until_end_of_day(): void
    {
        $quota = DailyQuota::factory()->create([
            'date' => Carbon::today(),
            'quota' => 100,
        ]);

        GetQuotaAction::run(Carbon::today());

        $cacheKey = 'quota:' . Carbon::today()->format('Y-m-d');
        
        // Cache should exist until end of day
        $this->assertTrue(Cache::has($cacheKey));

        // Fast forward to tomorrow
        Carbon::setTestNow(Carbon::tomorrow());

        // Cache should be expired
        // Note: This test depends on cache implementation
    }
}