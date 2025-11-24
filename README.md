# SpeakUp

**SpeakUp** is a full-stack web application that connects students with language teachers for online and in-person lessons. The platform focuses on making it easy to discover the right tutor, book lessons seamlessly, and communicate reliably — helping learners stay engaged throughout their language-learning journey.
This repository demonstrates a full-stack architecture following real production patterns and modern development practices.

## Current Capabilities
- **User onboarding**: Registration and login with custom email verification flow (token generation, expiration, event-driven email sending).
- **Domain model**: Dedicated `UserType` enum with Doctrine inheritance for `Student` / `Teacher`.
- **Email delivery**: Centralized email service with Twig templates + unit test coverage.
- **Authentication**: JWT authentication (Lexik JWT) configured for stateless API auth.
- **API design**: RESTful API endpoints with DTOs, validation, and structured error handling.
- **Event system**: Symfony Event Dispatcher for decoupled architecture (e.g., email-on-registration event).
- **Database**: MySQL with Doctrine ORM, migrations for schema versioning.
- **Frontend foundation**: React setup via Webpack Encore with Tailwind-ready styling and HMR.
- **DevOps**: Docker Compose stack with PHP, MySQL, phpMyAdmin, Redis, and Mailpit; GitHub Actions configured for CI running PHPUnit tests.

## Roadmap Highlights
- Lesson lifecycle: create/edit/book lessons with availability management and calendar view.
- Discovery: search and filters by language, price, availability, rating, and location.
- Engagement: messaging between students and teachers, notifications, and OAuth (Google/Facebook) logins.
- Payments & trust: booking payments, reviews, and ratings.
- Observability & quality: expanded automated tests (PHPUnit + React Testing Library/Cypress) and production-ready monitoring.

## In Progress
- Password reset flow with email notifications
- RabbitMQ integration for asynchronous email sending
- React CRUD API for managing lessons (create/edit/book)

## Project Structure
- `src/` – Symfony application (controllers, services, enums, listeners, handlers, DTOs).
- `assets/` – React application (components, hooks, and styles).
- `templates/` – Twig templates for system emails and layouts.
- `migrations/` – Doctrine migrations.
- `tests/` – PHPUnit tests (unit + integration).

## Tech Stack
- **Backend**: PHP 8.1/8.2, Symfony 6, Doctrine ORM, Lexik JWT Authentication Bundle, Symfony Mailer.
- **Frontend**: React 19, Webpack Encore, Tailwind CSS.
- **Database**: MySQL.
- **Tooling**: Docker + Docker Compose, GitHub Actions CI, PHPUnit.
- **Planned**:
  - RabbitMQ for asynchronous tasks (e.g., email sending).
  - OAuth logins (Google/Facebook).
  - React Testing Library and Cypress for frontend tests.
  - Redis for caching.

## Getting Started (Docker)

To run the application locally:

```bash
docker-compose up --build
```
Then access the following services in your browser:
- App: http://speak-up.localhost
- Mailpit (test inbox): http://speak-up.localhost:8025
- phpMyAdmin: http://speak-up.localhost:8080

Default database credentials are in the .env.test file.

## Testing
- Backend: `php bin/phpunit` (unit and integration tests under `tests/`).
  Exclude legacy tests with:
```bash
php bin/phpunit --exclude-group legacy
```
- Frontend: React Testing Library and Cypress planned.

## CI/CD
GitHub Actions run PHPUnit on every pull request to maintain code reliability and ensure smooth integration.
The Docker-based stack and environment variables support future deployment to staging/production.

## Author
Created by [**Anna Strzewiczek**](https://www.linkedin.com/in/anna-strzewiczek)

This project is part of a full-stack portfolio and designed for job applications (PHP Developer/Fullstack Developer).

Work in progress — feedback and contributions welcome! 😊