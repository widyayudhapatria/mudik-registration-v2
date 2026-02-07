<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DailyQuota;
use App\Models\FormLink;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_shows_registration_form_with_valid_signed_url(): void
    {
        $formLink = FormLink::factory()->create([
            'status' => 'pending',
            'expired_at' => Carbon::now()->addDays(3),
        ]);

        $url = \URL::temporarySignedRoute(
            'registration.form',
            $formLink->expired_at,
            ['token' => $formLink->token]
        );

        $response = $this->get($url);

        $response->assertStatus(200);
        $response->assertViewIs('public.registration.form');
        $response->assertViewHas('formLink');
    }

    /** @test */
    public function it_rejects_invalid_signature(): void
    {
        $formLink = FormLink::factory()->create();

        $response = $this->get("/public/register/{$formLink->token}");

        $response->assertStatus(403); // Invalid signature
    }

    /** @test */
    public function it_submits_registration_successfully(): void
    {
        $formLink = FormLink::factory()->create([
            'status' => 'pending',
            'expired_at' => Carbon::now()->addDays(3),
        ]);

        DailyQuota::factory()->create([
            'date' => Carbon::today(),
            'quota' => 100,
            'used' => 0,
            'remaining' => 100,
        ]);

        $url = \URL::temporarySignedRoute(
            'registration.submit',
            $formLink->expired_at,
            ['token' => $formLink->token]
        );

        $response = $this->postJson($url, [
            'representative_name' => 'Ahmad Santoso',
            'representative_nik' => '3201234567890123',
            'representative_birth_date' => '1985-01-15',
            'family_count' => 2,
            'kk_number' => '3201234567890456',
            'kk_document' => UploadedFile::fake()->image('kk.jpg'),
            'has_child_under_4' => false,
            'participants' => [
                [
                    'full_name' => 'Ahmad Santoso',
                    'nik_kia' => '3201234567890123',
                    'birth_date' => '1985-01-15',
                ],
                [
                    'full_name' => 'Siti Aminah',
                    'nik_kia' => '3201234567890789',
                    'birth_date' => '1987-03-20',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('registrations', [
            'form_link_id' => $formLink->id,
            'representative_nik' => '3201234567890123',
            'kk_number' => '3201234567890456',
        ]);

        $this->assertDatabaseHas('participants', [
            'full_name' => 'Ahmad Santoso',
            'nik_kia' => '3201234567890123',
        ]);

        $this->assertDatabaseHas('participants', [
            'full_name' => 'Siti Aminah',
            'nik_kia' => '3201234567890789',
        ]);

        // Verify quota decreased
        $quota = DailyQuota::where('date', Carbon::today())->first();
        $this->assertEquals(1, $quota->used);
        $this->assertEquals(99, $quota->remaining);
    }

    /** @test */
    public function it_rejects_duplicate_kk_number(): void
    {
        $existingFormLink = FormLink::factory()->create(['status' => 'submitted']);
        \App\Models\Registration::factory()->create([
            'form_link_id' => $existingFormLink->id,
            'kk_number' => '3201234567890456',
        ]);

        $formLink = FormLink::factory()->create([
            'status' => 'pending',
            'expired_at' => Carbon::now()->addDays(3),
        ]);

        DailyQuota::factory()->create([
            'date' => Carbon::today(),
            'quota' => 100,
            'remaining' => 100,
        ]);

        $url = \URL::temporarySignedRoute(
            'registration.submit',
            $formLink->expired_at,
            ['token' => $formLink->token]
        );

        $response = $this->postJson($url, [
            'representative_name' => 'Test User',
            'representative_nik' => '1234567890123456',
            'representative_birth_date' => '1990-01-01',
            'family_count' => 1,
            'kk_number' => '3201234567890456', // Duplicate
            'kk_document' => UploadedFile::fake()->image('kk.jpg'),
            'has_child_under_4' => false,
            'participants' => [
                [
                    'full_name' => 'Test User',
                    'nik_kia' => '1234567890123456',
                    'birth_date' => '1990-01-01',
                ],
            ],
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error_code' => 'DUPLICATE_KK',
            ]);
    }

    /** @test */
    public function it_rejects_registration_when_quota_full(): void
    {
        $formLink = FormLink::factory()->create([
            'status' => 'pending',
            'expired_at' => Carbon::now()->addDays(3),
        ]);

        DailyQuota::factory()->create([
            'date' => Carbon::today(),
            'quota' => 10,
            'used' => 10,
            'remaining' => 0,
        ]);

        $url = \URL::temporarySignedRoute(
            'registration.submit',
            $formLink->expired_at,
            ['token' => $formLink->token]
        );

        $response = $this->postJson($url, [
            'representative_name' => 'Test User',
            'representative_nik' => '1234567890123456',
            'representative_birth_date' => '1990-01-01',
            'family_count' => 1,
            'kk_number' => '1234567890123456',
            'kk_document' => UploadedFile::fake()->image('kk.jpg'),
            'has_child_under_4' => false,
            'participants' => [
                [
                    'full_name' => 'Test User',
                    'nik_kia' => '1234567890123456',
                    'birth_date' => '1990-01-01',
                ],
            ],
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error_code' => 'QUOTA_FULL',
            ]);
    }

    /** @test */
    public function it_validates_required_fields(): void
    {
        $formLink = FormLink::factory()->create([
            'status' => 'pending',
            'expired_at' => Carbon::now()->addDays(3),
        ]);

        $url = \URL::temporarySignedRoute(
            'registration.submit',
            $formLink->expired_at,
            ['token' => $formLink->token]
        );

        $response = $this->postJson($url, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'representative_name',
                'representative_nik',
                'representative_birth_date',
                'family_count',
                'kk_number',
                'kk_document',
                'participants',
            ]);
    }

    /** @test */
    public function it_uploads_kk_document(): void
    {
        $formLink = FormLink::factory()->create([
            'status' => 'pending',
            'expired_at' => Carbon::now()->addDays(3),
        ]);

        DailyQuota::factory()->create([
            'date' => Carbon::today(),
            'quota' => 100,
            'remaining' => 100,
        ]);

        $url = \URL::temporarySignedRoute(
            'registration.submit',
            $formLink->expired_at,
            ['token' => $formLink->token]
        );

        $file = UploadedFile::fake()->image('kk.jpg', 1000, 1000)->size(1024); // 1MB

        $response = $this->postJson($url, [
            'representative_name' => 'Ahmad Santoso',
            'representative_nik' => '3201234567890123',
            'representative_birth_date' => '1985-01-15',
            'family_count' => 1,
            'kk_number' => '3201234567890456',
            'kk_document' => $file,
            'has_child_under_4' => false,
            'participants' => [
                [
                    'full_name' => 'Ahmad Santoso',
                    'nik_kia' => '3201234567890123',
                    'birth_date' => '1985-01-15',
                ],
            ],
        ]);

        $response->assertStatus(200);

        // Verify file was uploaded
        $registration = \App\Models\Registration::where('form_link_id', $formLink->id)->first();
        Storage::disk('public')->assertExists($registration->kk_document_path);
    }
}