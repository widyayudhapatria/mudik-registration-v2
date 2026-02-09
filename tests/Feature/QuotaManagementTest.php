<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\DailyQuota;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class QuotaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        
        Cache::flush();
        
        $this->superAdmin = Admin::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_it_gets_today_quota(): void
    {
        $quota = DailyQuota::factory()->create([
            'date' => Carbon::today(),
            'quota' => 100,
            'used' => 20,
            'remaining' => 80,
        ]);

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->getJson('/cms/quotas/today');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'quota' => 100,
                    'used' => 20,
                    'remaining' => 80,
                ],
            ]);
    }

    public function test_it_returns_404_when_today_quota_not_set(): void
    {
        DailyQuota::whereDate('date', Carbon::today())->delete();
        Cache::flush();
        
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->getJson('/cms/quotas/today');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_it_sets_new_quota(): void
    {
        $date = Carbon::today()->addDay();

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->postJson('/cms/quotas', [
                'date' => $date->toDateString(),
                'quota' => 150,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'quota' => 150,
                    'used' => 0,
                    'remaining' => 150,
                ],
            ]);

        $this->assertDatabaseHas('daily_quotas', [
            'quota' => 150,
            'used' => 0,
        ]);
    }

    public function test_it_updates_existing_quota(): void
    {
        $quota = DailyQuota::factory()->create([
            'date' => Carbon::today()->addDays(2), // Gunakan tanggal berbeda
            'quota' => 100,
            'used' => 10,
        ]);

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->postJson('/cms/quotas', [
                'date' => $quota->date->toDateString(),
                'quota' => 200,
            ]);

        $response->assertStatus(200);

        $quota->refresh();
        $this->assertEquals(200, $quota->quota);
        $this->assertEquals(10, $quota->used);
        $this->assertEquals(190, $quota->remaining);
    }

    public function test_it_lists_quotas_with_date_range(): void
    {
        $startDate = '2026-02-10';
        $endDate = '2026-02-15';
        
        for ($i = 0; $i < 3; $i++) {
            DailyQuota::factory()->create([
                'date' => Carbon::parse($startDate)->addDays($i)->toDateString(),
                'quota' => 100 + ($i * 10),
            ]);
        }

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->getJson("/cms/quotas?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(3, 'data');
    }

    public function test_it_validates_required_fields_when_setting_quota(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->postJson('/cms/quotas', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date', 'quota']);
    }

    public function test_it_validates_quota_is_non_negative(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->postJson('/cms/quotas', [
                'date' => Carbon::today()->toDateString(),
                'quota' => -10,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quota']);
    }

    public function test_it_requires_authentication_for_quota_endpoints(): void
    {
        $response = $this->getJson('/cms/quotas/today');
        $response->assertStatus(401);

        $response = $this->getJson('/cms/quotas');
        $response->assertStatus(401);

        $response = $this->postJson('/cms/quotas', [
            'date' => Carbon::today()->toDateString(),
            'quota' => 100,
        ]);
        $response->assertStatus(401);
    }

    public function test_it_requires_permission_to_view_quota(): void
    {
        $validator = Admin::factory()->create([
            'role' => 'validator',
            'is_active' => true,
        ]);

        $response = $this->actingAs($validator, 'admin')
            ->getJson('/cms/quotas/today');

        $response->assertStatus(403);
    }

    public function test_it_requires_permission_to_modify_quota(): void
    {
        $validator = Admin::factory()->create([
            'role' => 'validator',
            'is_active' => true,
        ]);

        $response = $this->actingAs($validator, 'admin')
            ->postJson('/cms/quotas', [
                'date' => Carbon::today()->toDateString(),
                'quota' => 100,
            ]);

        $response->assertStatus(403);
    }

    public function test_it_clears_cache_after_setting_quota(): void
    {
        $date = Carbon::tomorrow(); 
        $cacheKey = "quota.{$date->toDateString()}";
        
        // Set cache
        Cache::put($cacheKey, ['test' => 'data'], 60);
        $this->assertNotNull(Cache::get($cacheKey));

        // Set quota (should clear cache via model observer)
        $this->actingAs($this->superAdmin, 'admin')
            ->postJson('/cms/quotas', [
                'date' => $date->toDateString(),
                'quota' => 100,
            ]);

        // Verify cache was cleared
        $this->assertNull(Cache::get($cacheKey));
    }
}