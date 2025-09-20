# SpeakUp

**SpeakUp** is a modern web application designed to connect students with language teachers for both in-person and online lessons. The platform allows users to search for teachers, book lessons, manage their schedules, and communicate effectively — all in one place.

## Completed features

- Project structure with Symfony 6 (backend) and Docker setup (PHP, MySQL, phpMyAdmin, Redis, Mailpit)
- Database design with Doctrine and migrations
- User registration & login with email verification (Symfony Security)
- CI/CD configured with GitHub Actions
- Entities: User (Student/Teacher), Language
- Environment configuration
- PHPUnit test setup

## In Progress

- Basic styling with Tailwind CSS
- Implementation of JWT-based authentication
- React CRUD API for managing lessons (create/edit/book)
- Availability management logic (backend)

## Planned Features 

- Teacher/lessons search with filters (language, price, availability)
- Messaging system between students and teachers
- Booking calendar with interactive time slots
- Teacher rating and lesson review system
- Payment flow for lesson booking
- Email & SMS notifications for lesson reminders
- Google/Facebook login (OAuth)
- Admin dashboard for user/content management
- Google Calendar integration
- Real-time updates (WebSockets or Firebase)
- Google Maps integration for local lessons
- Advanced filters and sorting (price sliders, rating, availability)
- Full test coverage: PHPUnit + React Testing Library / Cypress
- Production deployment setup

## Technologies


- **Backend**: PHP 8.2, Symfony 6, Doctrine ORM
- **Frontend**: React.js (in progress), Webpack Encore, Tailwind CSS
- **Database**: MySQL
- **Cache / Storage:** Redis (planned)
- **Authentication**: Symfony Security, OAuth (planned)
- **Containerization**: Docker + Docker Compose
- **Testing**: PHPUnit (backend), React Testing Library / Cypress (frontend, planned)
- **CI/CD**: GitHub Actions

## Getting Started

To run the application locally:

```bash
docker-compose up --build
```

## Author

Created by [**Anna Strzewiczek**](https://www.linkedin.com/in/anna-strzewiczek)

This project is part of a full-stack portfolio and designed for job applications (PHP Developer/Fullstack Developer).


Work in progress — feedback and contributions welcome! 😊
