<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Permissions\MudikPermissions;
use App\Models\Admin;
use App\Models\DailyQuota;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = Admin::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // Grant permissions
        // $this->superAdmin->givePermissionTo([
        //     MudikPermissions::ViewQuota->value,
        //     MudikPermissions::ModifyQuota->value,
        // ]);
    }

    /** @test */
    public function it_gets_today_quota(): void
    {
        $quota = DailyQuota::factory()->create([
            'date' => Carbon::today(),
            'quota' => 100,
            'used' => 25,
            'remaining' => 75,
        ]);

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->getJson('/cms/quotas/today');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $quota->id,
                    'quota' => 100,
                    'used' => 25,
                    'remaining' => 75,
                ],
            ]);
    }

    /** @test */
    public function it_returns_404_when_today_quota_not_set(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->getJson('/cms/quotas/today');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function it_sets_new_quota(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->postJson('/cms/quotas', [
                'date' => Carbon::tomorrow()->format('Y-m-d'),
                'quota' => 150,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Kuota berhasil di-set',
            ]);

        $this->assertDatabaseHas('daily_quotas', [
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'quota' => 150,
            'used' => 0,
            'remaining' => 150,
        ]);
    }

    /** @test */
    public function it_updates_existing_quota(): void
    {
        $existingQuota = DailyQuota::factory()->create([
            'date' => Carbon::today(),
            'quota' => 100,
            'used' => 50,
            'remaining' => 50,
        ]);

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->postJson('/cms/quotas', [
                'date' => Carbon::today()->format('Y-m-d'),
                'quota' => 200,
            ]);

        $response->assertStatus(200);

        $quota = DailyQuota::find($existingQuota->id);
        
        $this->assertEquals(200, $quota->quota);
        $this->assertEquals(50, $quota->used); // Unchanged
        $this->assertEquals(150, $quota->remaining); // 200 - 50
    }

    /** @test */
    public function it_lists_quotas_with_date_range(): void
    {
        DailyQuota::factory()->count(5)->create([
            'date' => Carbon::today()->addDays(fn($i) => $i),
        ]);

        $startDate = Carbon::today()->format('Y-m-d');
        $endDate = Carbon::today()->addDays(4)->format('Y-m-d');

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->getJson("/cms/quotas?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function it_validates_required_fields_when_setting_quota(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->postJson('/cms/quotas', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date', 'quota']);
    }

    /** @test */
    public function it_validates_quota_is_non_negative(): void
    {
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->postJson('/cms/quotas', [
                'date' => Carbon::today()->format('Y-m-d'),
                'quota' => -10,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quota']);
    }

    /** @test */
    public function it_requires_authentication_for_quota_endpoints(): void
    {
        $response = $this->getJson('/cms/quotas/today');
        $response->assertStatus(401);

        $response = $this->postJson('/cms/quotas', [
            'date' => Carbon::today()->format('Y-m-d'),
            'quota' => 100,
        ]);
        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_permission_to_view_quota(): void
    {
        $adminWithoutPermission = Admin::factory()->create([
            'role' => 'scanner',
            'is_active' => true,
        ]);

        $response = $this->actingAs($adminWithoutPermission, 'admin')
            ->getJson('/cms/quotas/today');

        $response->assertStatus(403);
    }

    /** @test */
    public function it_requires_permission_to_modify_quota(): void
    {
        $adminWithoutPermission = Admin::factory()->create([
            'role' => 'validator',
            'is_active' => true,
        ]);

        $response = $this->actingAs($adminWithoutPermission, 'admin')
            ->postJson('/cms/quotas', [
                'date' => Carbon::today()->format('Y-m-d'),
                'quota' => 100,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_clears_cache_after_setting_quota(): void
    {
        $dateString = Carbon::today()->format('Y-m-d');
        $cacheKey = 'quota:' . $dateString;

        // Set initial cache
        \Cache::put($cacheKey, 'test-data', 3600);
        $this->assertTrue(\Cache::has($cacheKey));

        // Set quota
        $this->actingAs($this->superAdmin, 'admin')
            ->postJson('/cms/quotas', [
                'date' => $dateString,
                'quota' => 100,
            ]);

        // Cache should be cleared
        $this->assertFalse(\Cache::has($cacheKey));
    }
}