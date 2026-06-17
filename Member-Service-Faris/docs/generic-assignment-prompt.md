# Generic IAE Assignment Prompt

Use this prompt for another team that wants to build its own service-based Assignment 2 project.

```text
We are building an IAE Assignment 2 service-based project.

Each team member must create their own separate GitHub repository and service. Do not make one monorepo unless the lecturer explicitly requires it. Each service must run with Docker and communicate with other services through HTTP endpoints, not by directly accessing another service's database.

Please help us design and implement this project.

Use Plan Mode first. Before implementation, ask follow-up questions to clarify:
- What is the project domain or business process?
- What services should exist in the system?
- Which service is my responsibility?
- What framework/language should my service use?
- What database should my service use?
- What resource/entity does my service own?
- What fields should the resource/entity have?
- What REST endpoints should my service expose?
- What GraphQL query should expose the same data as the REST API?
- How should services communicate with each other?
- Should services run on one laptop, different laptops, or a shared Docker network?
- What Docker service name and host port should my service use?
- What repository name should I use?
- What NIM/API key should protect my service?
- What API response format should all services follow?
- What testing requirements should we include?

After asking questions, create a clear implementation plan before writing code.

Assignment requirements:
- Each student owns one service repository.
- Repository must be created in the organization provided by the lecturer.
- Repository naming format should follow: NIM_Nama-Service.
- Each service must be independently runnable.
- Docker is required.
- Each service must have its own database and migrations/schema.
- Services must communicate through HTTP APIs.
- Do not query another service's database directly.
- REST endpoints must be versioned, for example:
  /api/v1/...
- REST API must include at least 3 functional endpoints, for example:
  GET /api/v1/items
  GET /api/v1/items/{id}
  POST /api/v1/items
- REST endpoints must return correct HTTP status codes such as 200, 201, 401, 404, and 422.
- All endpoints must use JSON.
- All endpoints must follow the Standard Integration Contract response format.
- All endpoints must be protected with an API key sent in the request header:
  X-IAE-KEY: <service-owner-nim>
- The service must provide interactive Swagger/OpenAPI documentation.
- Swagger UI should document every REST endpoint.
- The service must provide a GraphQL endpoint.
- The service must provide at least 1 GraphQL query that returns the same data as the REST API while allowing clients to choose fields.
- The service must provide a GraphQL Playground or GraphiQL page for testing.
- The repository must include an AI prompting/chat history file in .md format.

Use this JSON success response format from the Standard Integration Contract:
{
  "status": "success",
  "message": "Operation successful",
  "data": {},
  "meta": {
    "service_name": "Your-Service",
    "api_version": "v1"
  }
}

Use this JSON error response format:
{
  "status": "error",
  "message": "Detail pesan kesalahan...",
  "errors": null
}

Each repository must include:
- Dockerfile
- docker-compose.yml
- README.md
- .env.example
- API documentation
- Swagger/OpenAPI setup
- GraphQL schema/query setup
- database migrations/schema
- tests for REST endpoints
- tests for API key protection
- tests for Swagger/OpenAPI availability
- tests for GraphQL query availability
- AI chat history .md file

When planning, include:
- Service architecture
- REST API contracts
- GraphQL schema and query contract
- Database schema
- Docker setup
- Environment variables
- API key security
- Standard Integration Contract response format
- Swagger/OpenAPI documentation plan
- Inter-service communication flow
- Error handling
- Test plan
- Repository setup
- Final implementation checklist

My project idea is: [describe business process here].
My assigned service is: [service name here].
My NIM is: [NIM here].
My preferred framework is: [framework here].
```
