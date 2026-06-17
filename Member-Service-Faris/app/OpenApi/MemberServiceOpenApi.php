<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Keanggotaan Service API',
    description: 'REST and GraphQL documentation for the IAE-T2 Keanggotaan / Member Service.'
)]
#[OA\Server(url: 'http://localhost:8001', description: 'Host/Postman access')]
#[OA\Server(url: 'http://member-service:8000', description: 'Docker network access')]
#[OA\SecurityScheme(
    securityScheme: 'iaeApiKey',
    type: 'apiKey',
    description: 'IAE service API key. Use NIM 102022400255 for this service.',
    name: 'X-IAE-KEY',
    in: 'header'
)]
#[OA\Schema(
    schema: 'Member',
    type: 'object',
    required: ['id', 'name', 'student_number', 'email', 'status', 'is_active', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso'),
        new OA\Property(property: 'student_number', type: 'string', example: 'SISWA-00001'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'budi@example.com'),
        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '08123456789'),
        new OA\Property(property: 'address', type: 'string', nullable: true, example: 'Jl. Merdeka No. 1'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'suspended'], example: 'active'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'MemberCreateRequest',
    type: 'object',
    required: ['name', 'student_number', 'email'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso'),
        new OA\Property(property: 'student_number', type: 'string', example: 'SISWA-00001'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'budi@example.com'),
        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '08123456789'),
        new OA\Property(property: 'address', type: 'string', nullable: true, example: 'Jl. Merdeka No. 1'),
    ]
)]
#[OA\Schema(
    schema: 'ResponseMeta',
    type: 'object',
    properties: [
        new OA\Property(property: 'service_name', type: 'string', example: 'Member-Service'),
        new OA\Property(property: 'api_version', type: 'string', example: 'v1'),
    ]
)]
#[OA\Schema(
    schema: 'MemberResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'message', type: 'string', example: 'Member retrieved successfully'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Member'),
        new OA\Property(property: 'meta', ref: '#/components/schemas/ResponseMeta'),
    ]
)]
#[OA\Schema(
    schema: 'MemberListResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'message', type: 'string', example: 'Members retrieved successfully'),
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Member')
        ),
        new OA\Property(
            property: 'meta',
            type: 'object',
            properties: [
                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                new OA\Property(property: 'per_page', type: 'integer', example: 10),
                new OA\Property(property: 'total', type: 'integer', example: 1),
                new OA\Property(property: 'last_page', type: 'integer', example: 1),
                new OA\Property(property: 'service_name', type: 'string', example: 'Member-Service'),
                new OA\Property(property: 'api_version', type: 'string', example: 'v1'),
            ]
        ),
    ]
)]
#[OA\Schema(
    schema: 'ErrorResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'error'),
        new OA\Property(property: 'message', type: 'string', example: 'Member not found'),
        new OA\Property(property: 'errors', nullable: true, example: null),
    ]
)]
#[OA\Schema(
    schema: 'GraphQLRequest',
    type: 'object',
    required: ['query'],
    properties: [
        new OA\Property(
            property: 'query',
            type: 'string',
            example: '{ members { id name student_number status is_active } }'
        ),
    ]
)]
class MemberServiceOpenApi
{
    #[OA\Get(
        path: '/api/v1/members',
        summary: 'List members',
        security: [['iaeApiKey' => []]],
        tags: ['Members'],
        parameters: [
            new OA\Parameter(
                name: 'per_page',
                description: 'Pagination size, default 10 and maximum 100.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 10)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Members retrieved successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/MemberListResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid or missing API key',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function listMembers(): void {}

    #[OA\Post(
        path: '/api/v1/members',
        summary: 'Create a member',
        security: [['iaeApiKey' => []]],
        tags: ['Members'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/MemberCreateRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Member created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/MemberResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid or missing API key',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation failed',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function createMember(): void {}

    #[OA\Get(
        path: '/api/v1/members/{id}',
        summary: 'Get member detail',
        security: [['iaeApiKey' => []]],
        tags: ['Members'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Member ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Member retrieved successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/MemberResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid or missing API key',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Member not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function getMember(): void {}

    #[OA\Post(
        path: '/graphql',
        summary: 'GraphQL member query endpoint',
        security: [['iaeApiKey' => []]],
        tags: ['GraphQL'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/GraphQLRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'GraphQL query executed'),
            new OA\Response(
                response: 401,
                description: 'Invalid or missing API key',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function graphql(): void {}
}
