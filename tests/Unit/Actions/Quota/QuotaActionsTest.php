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

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set Carbon test time
        Carbon::setTestNow(Carbon::parse('2026-02-09 14:56:28'));
        
        // Clear cache before each test
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Cache::flush();
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** @test */
    public function it_sets_new_quota_for_date()
    {
        $data = new SetQuotaData(
            date: Carbon::today(),
            quota: 100
        );

        $result = SetQuotaAction::run($data);

        $this->assertNotNull($result);
        $this->assertEquals(100, $result->quota);
        $this->assertEquals(0, $result->used);
        $this->assertEquals(100, $result->remaining);
        
        $this->assertDatabaseHas('daily_quotas', [
            'quota' => 100,
            'used' => 0,
            'remaining' => 100,
        ]);
        
        $count = DailyQuota::whereDate('date', Carbon::today())->count();
        $this->assertEquals(1, $count);
    }

    /** @test */
    public function it_updates_existing_quota()
    {
        $quota = DailyQuota::create([
            'date' => Carbon::today()->format('Y-m-d'),
            'quota' => 50,
            'used' => 10,
            'remaining' => 40,
        ]);

        $data = new SetQuotaData(
            date: Carbon::today(),
            quota: 100
        );

        $result = SetQuotaAction::run($data);

        $this->assertNotNull($result);
        $this->assertEquals($quota->id, $result->id);
        $this->assertEquals(100, $result->quota);
        $this->assertEquals(10, $result->used);
        $this->assertEquals(90, $result->remaining);
        
        $count = DailyQuota::whereDate('date', Carbon::today())->count();
        $this->assertEquals(1, $count);
    }

    /** @test */
    public function it_clears_cache_after_setting_quota()
    {
        $cacheKey = 'quota:' . Carbon::today()->format('Y-m-d');
        
        Cache::put($cacheKey, 'test_value', 3600);
        $this->assertTrue(Cache::has($cacheKey));

        $data = new SetQuotaData(
            date: Carbon::today(),
            quota: 100
        );
        SetQuotaAction::run($data);

        $this->assertFalse(Cache::has($cacheKey));
    }

    /** @test */
    public function it_gets_quota_from_cache()
    {
        $quota = DailyQuota::create([
            'date' => Carbon::today()->format('Y-m-d'),
            'quota' => 100,
            'used' => 0,
            'remaining' => 100,
        ]);

        // First call - should cache the result
        $result1 = GetQuotaAction::run(Carbon::today());

        $this->assertNotNull($result1, 'First call should return quota');
        $this->assertEquals($quota->id, $result1->id);

        // Verify cache was created
        $cacheKey = 'quota:' . Carbon::today()->format('Y-m-d');
        $this->assertTrue(Cache::has($cacheKey), 'Cache should exist after first call');

        // Get cached value to verify it's the model
        $cachedValue = Cache::get($cacheKey);
        $this->assertNotNull($cachedValue, 'Cached value should not be null');
        $this->assertInstanceOf(DailyQuota::class, $cachedValue, 'Cached value should be DailyQuota instance');

        // Delete from database to prove cache works
        DailyQuota::where('id', $quota->id)->delete();

        // Verify record is gone from DB
        $dbCheck = DailyQuota::find($quota->id);
        $this->assertNull($dbCheck, 'Record should be deleted from database');

        // Second call - should get from cache (not from DB)
        $result2 = GetQuotaAction::run(Carbon::today());

        $this->assertNotNull($result2, 'Second call should return cached quota');
        $this->assertEquals($quota->id, $result2->id);
    }

    /** @test */
    public function it_gets_today_quota()
    {
        $quota = DailyQuota::create([
            'date' => Carbon::today()->format('Y-m-d'),
            'quota' => 100,
            'used' => 0,
            'remaining' => 100,
        ]);

        $result = (new GetQuotaAction())->getTodayQuota();

        $this->assertNotNull($result);
        $this->assertEquals($quota->id, $result->id);
    }

    /** @test */
    public function it_checks_quota_availability()
    {
        DailyQuota::create([
            'date' => Carbon::today()->format('Y-m-d'),
            'quota' => 100,
            'used' => 50,
            'remaining' => 50,
        ]);

        $available = (new GetQuotaAction())->checkAvailability();
        $this->assertTrue($available);

        DailyQuota::whereDate('date', Carbon::today())
            ->update([
                'used' => 100,
                'remaining' => 0,
            ]);

        Cache::forget('quota:' . Carbon::today()->format('Y-m-d'));

        $notAvailable = (new GetQuotaAction())->checkAvailability();
        $this->assertFalse($notAvailable);
    }

    /** @test */
    public function it_returns_null_for_non_existent_quota()
    {
        $result = GetQuotaAction::run(Carbon::parse('2025-01-01'));
        
        $this->assertNull($result);
    }

    /** @test */
    public function it_sets_cache_ttl_until_end_of_day()
    {
        // Set current time to middle of day
        Carbon::setTestNow(Carbon::parse('2026-02-09 14:00:00'));

        $quota = DailyQuota::create([
            'date' => Carbon::today()->format('Y-m-d'),
            'quota' => 100,
            'used' => 0,
            'remaining' => 100,
        ]);

        // Get quota (should cache it)
        $result = GetQuotaAction::run(Carbon::today());
        
        $this->assertNotNull($result, 'GetQuota should return result');

        $cacheKey = 'quota:' . Carbon::today()->format('Y-m-d');

        // Cache should exist immediately after call
        $this->assertTrue(Cache::has($cacheKey), 'Cache should exist after GetQuota call');

        // Verify cached value is correct
        $cachedValue = Cache::get($cacheKey);
        $this->assertNotNull($cachedValue, 'Cached value should not be null');
        $this->assertEquals($quota->id, $cachedValue->id, 'Cached ID should match');

        // Fast forward to just before end of day (should still exist)
        Carbon::setTestNow(Carbon::parse('2026-02-09 23:59:58'));
        
        $this->assertTrue(
            Cache::has($cacheKey), 
            'Cache should exist before end of day. Current time: ' . Carbon::now()->toDateTimeString()
        );

        // Fast forward past midnight (cache should be expired)
        Carbon::setTestNow(Carbon::parse('2026-02-10 00:00:01'));
        
        $this->assertFalse(
            Cache::has($cacheKey), 
            'Cache should be expired after midnight. Current time: ' . Carbon::now()->toDateTimeString()
        );
    }
}