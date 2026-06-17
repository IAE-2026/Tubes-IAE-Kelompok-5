<?php

namespace Tests\Feature;

use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentationAndGraphqlTest extends TestCase
{
    use RefreshDatabase;

    private const IAE_API_KEY = '102022400255';

    public function test_swagger_ui_and_openapi_json_are_available(): void
    {
        $this->get('/api/documentation')
            ->assertOk()
            ->assertSee('SwaggerUIBundle', false);

        $this->getJson('/docs')
            ->assertOk()
            ->assertJsonPath('info.title', 'Keanggotaan Service API')
            ->assertJsonPath('paths./api/v1/members.get.summary', 'List members')
            ->assertJsonPath('paths./api/v1/members.post.summary', 'Create a member')
            ->assertJsonPath('paths./api/v1/members/{id}.get.summary', 'Get member detail')
            ->assertJsonPath('paths./graphql.post.summary', 'GraphQL member query endpoint');
    }

    public function test_graphql_member_query_returns_selected_fields(): void
    {
        $member = Member::factory()->inactive()->create([
            'name' => 'Dewi Lestari',
            'student_number' => 'SISWA-00010',
        ]);

        $response = $this->withIaeKey()->postJson('/graphql', [
            'query' => <<<'GRAPHQL'
                query ($id: ID!) {
                    member(id: $id) {
                        id
                        name
                        student_number
                        status
                        is_active
                    }
                }
                GRAPHQL,
            'variables' => [
                'id' => (string) $member->id,
            ],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.member.id', (string) $member->id)
            ->assertJsonPath('data.member.name', 'Dewi Lestari')
            ->assertJsonPath('data.member.student_number', 'SISWA-00010')
            ->assertJsonPath('data.member.status', Member::STATUS_INACTIVE)
            ->assertJsonPath('data.member.is_active', false);
    }

    public function test_graphql_members_query_can_return_collection_data(): void
    {
        Member::factory()->create([
            'name' => 'Budi Santoso',
            'student_number' => 'SISWA-00011',
        ]);

        $response = $this->withIaeKey()->postJson('/graphql', [
            'query' => <<<'GRAPHQL'
                {
                    members {
                        name
                        student_number
                        status
                    }
                }
                GRAPHQL,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.members.0.name', 'Budi Santoso')
            ->assertJsonPath('data.members.0.student_number', 'SISWA-00011')
            ->assertJsonPath('data.members.0.status', Member::STATUS_ACTIVE);
    }

    public function test_graphql_requires_iae_api_key(): void
    {
        $this->postJson('/graphql', [
            'query' => '{ members { id } }',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Invalid or missing API key');
    }

    public function test_graphql_playground_is_available(): void
    {
        $this->get('/graphql-playground')
            ->assertOk()
            ->assertSee('GraphiQL', false)
            ->assertSee('X-IAE-KEY', false);
    }

    private function withIaeKey(): static
    {
        return $this->withHeader('X-IAE-KEY', self::IAE_API_KEY);
    }
}
