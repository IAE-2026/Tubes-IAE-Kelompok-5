<?php

namespace Tests\Feature;

use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberApiTest extends TestCase
{
    use RefreshDatabase;

    private const IAE_API_KEY = '102022400255';

    public function test_member_can_be_created_with_student_profile_data(): void
    {
        $payload = [
            'name' => 'Budi Santoso',
            'student_number' => 'SISWA-00001',
            'email' => 'budi@example.com',
            'phone' => '08123456789',
            'address' => 'Jl. Merdeka No. 1',
        ];

        $response = $this->withIaeKey()->postJson('/api/v1/members', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Member created successfully')
            ->assertJsonPath('data.name', 'Budi Santoso')
            ->assertJsonPath('data.student_number', 'SISWA-00001')
            ->assertJsonPath('data.email', 'budi@example.com')
            ->assertJsonPath('data.status', Member::STATUS_ACTIVE)
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('meta.service_name', 'Member-Service')
            ->assertJsonPath('meta.api_version', 'v1');

        $this->assertDatabaseHas('members', [
            'student_number' => 'SISWA-00001',
            'email' => 'budi@example.com',
            'status' => Member::STATUS_ACTIVE,
        ]);
    }

    public function test_create_member_validates_required_and_unique_fields(): void
    {
        $this->withIaeKey()->postJson('/api/v1/members', [])
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonValidationErrors(['name', 'student_number', 'email']);

        Member::factory()->create([
            'student_number' => 'SISWA-00002',
            'email' => 'siti@example.com',
        ]);

        $this->withIaeKey()->postJson('/api/v1/members', [
            'name' => 'Siti Aminah',
            'student_number' => 'SISWA-00002',
            'email' => 'siti@example.com',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error')
            ->assertJsonValidationErrors(['student_number', 'email']);
    }

    public function test_members_can_be_listed_with_pagination(): void
    {
        Member::factory()->count(3)->create();

        $response = $this->withIaeKey()->getJson('/api/v1/members?per_page=2');

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Members retrieved successfully')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.service_name', 'Member-Service')
            ->assertJsonPath('meta.api_version', 'v1')
            ->assertJsonCount(2, 'data');
    }

    public function test_member_detail_includes_status_and_active_flag(): void
    {
        $member = Member::factory()->inactive()->create();

        $response = $this->withIaeKey()->getJson("/api/v1/members/{$member->id}");

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Member retrieved successfully')
            ->assertJsonPath('data.id', $member->id)
            ->assertJsonPath('data.status', Member::STATUS_INACTIVE)
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('meta.service_name', 'Member-Service')
            ->assertJsonPath('meta.api_version', 'v1');
    }

    public function test_unknown_member_returns_not_found_response(): void
    {
        $this->withIaeKey()->getJson('/api/v1/members/999')
            ->assertNotFound()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Member not found')
            ->assertJsonPath('errors', null);
    }

    public function test_api_key_header_is_required_for_member_endpoints(): void
    {
        $this->getJson('/api/v1/members')
            ->assertUnauthorized()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Invalid or missing API key')
            ->assertJsonPath('errors', null);
    }

    private function withIaeKey(): self
    {
        return $this->withHeader('X-IAE-KEY', self::IAE_API_KEY);
    }
}
