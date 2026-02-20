<?php

namespace Tests\Unit\Actions\Email;

use App\Actions\Email\SubmitEmailAction;
use App\Data\SubmitEmailData;
use App\Enums\ErrorCode;
use App\Enums\FormLinkStatus;
use App\Exceptions\MudikException;
use App\Models\FormLink;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmitEmailActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_new_form_link_for_new_email(): void
    {
        $data = new SubmitEmailData(email: 'test@example.com');

        $formLink = SubmitEmailAction::run($data);

        $this->assertInstanceOf(FormLink::class, $formLink);
        $this->assertEquals('test@example.com', $formLink->email);
        $this->assertEquals(FormLinkStatus::Pending->value, $formLink->status);
        $this->assertNotNull($formLink->token);
        $this->assertNotNull($formLink->expired_at);
        $this->assertEquals(0, $formLink->resend_count);
    }

    /** @test */
    public function it_throws_exception_for_submitted_email(): void
    {
        // Create existing form link with submitted status
        FormLink::factory()->create([
            'email' => 'test@example.com',
            'status' => FormLinkStatus::Submitted->value,
        ]);

        $data = new SubmitEmailData(email: 'test@example.com');

        $this->expectException(MudikException::class);
        $this->expectExceptionMessage(ErrorCode::EmailExists->getMessage());

        SubmitEmailAction::run($data);
    }

    /** @test */
    public function it_throws_exception_for_approved_email(): void
    {
        FormLink::factory()->create([
            'email' => 'test@example.com',
            'status' => FormLinkStatus::Approved->value,
        ]);

        $data = new SubmitEmailData(email: 'test@example.com');

        $this->expectException(MudikException::class);

        SubmitEmailAction::run($data);
    }

    /** @test */
    public function it_recreates_link_for_rejected_email(): void
    {
        $oldFormLink = FormLink::factory()->create([
            'email' => 'test@example.com',
            'status' => FormLinkStatus::Rejected->value,
            'token' => 'old-token',
            'resend_count' => 1,
        ]);

        $data = new SubmitEmailData(email: 'test@example.com');

        $formLink = SubmitEmailAction::run($data);

        $this->assertEquals($oldFormLink->id, $formLink->id);
        $this->assertNotEquals('old-token', $formLink->token);
        $this->assertEquals(FormLinkStatus::Pending->value, $formLink->status);
        $this->assertEquals(2, $formLink->resend_count);
        $this->assertNull($formLink->used_at);
    }

    /** @test */
    public function it_recreates_link_for_expired_email(): void
    {
        $oldFormLink = FormLink::factory()->create([
            'email' => 'test@example.com',
            'status' => FormLinkStatus::Pending->value,
            'expired_at' => Carbon::now()->subDays(1),
            'token' => 'old-token',
        ]);

        $data = new SubmitEmailData(email: 'test@example.com');

        $formLink = SubmitEmailAction::run($data);

        $this->assertEquals($oldFormLink->id, $formLink->id);
        $this->assertNotEquals('old-token', $formLink->token);
        $this->assertTrue($formLink->expired_at->isFuture());
    }

    /** @test */
    public function it_sets_correct_expiry_date(): void
    {
        config(['mudik.form_link_expiry_days' => 3]);

        $data = new SubmitEmailData(email: 'test@example.com');

        $formLink = SubmitEmailAction::run($data);

        $expectedExpiry = Carbon::now()->addDays(3);

        $this->assertTrue(
            $formLink->expired_at->isSameDay($expectedExpiry)
        );
    }

    /** @test */
    public function it_generates_unique_token(): void
    {
        $data1 = new SubmitEmailData(email: 'test1@example.com');
        $data2 = new SubmitEmailData(email: 'test2@example.com');

        $formLink1 = SubmitEmailAction::run($data1);
        $formLink2 = SubmitEmailAction::run($data2);

        $this->assertNotEquals($formLink1->token, $formLink2->token);
        $this->assertEquals(64, strlen($formLink1->token));
        $this->assertEquals(64, strlen($formLink2->token));
    }
}