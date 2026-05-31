# Agent Guide

- Inspect current code before modifying.
- Follow `jooservices/dto` DTO style: strict types, typed readonly DTOs, constructor promotion, explicit payload methods.
- Do not add HTTP APIs, routes, controllers, FormRequests, API resources, jobs, queues, events, listeners, logs, audit history, sync history, or UI.
- Keep models thin and put local database access in repositories/services.
- Do not use mocks or fake WordPress SDK responses in tests.
- Use Faker for generated test data when data variety matters.
- Run validation before reporting done and include exact results.
- Stop and ask if requirements are unclear, conflicting, missing, or impossible.