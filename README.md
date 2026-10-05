# AI Course Finder

AI Course Finder is an early standalone prototype of an AI-assisted university Course Finder. The initial application will be developed with PHP, MySQL, and JavaScript, then migrated into a custom Drupal module after the standalone application is working.

This repository currently contains the initial project structure and a PDO connection for MySQL. Database design, business logic, recommendation and eligibility services, Gemini integration, and user interfaces have not yet been implemented.

## Database configuration

Set the local database name and credentials in `.env`:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=course_finder
DB_USER=root
DB_PASSWORD=
```

The connection is created by `config/database.php`, which returns a configured `PDO` instance. The database itself and its tables are not created automatically.
