# Core PHP deployment plan (Phase 2)

The owner confirmed the Phase 1 `offices` and `leads` SQL tables in phpMyAdmin. No web application is deployed yet.

## Architecture decision
Because no cPanel Terminal is available, start with a minimal PHP + PDO implementation, without Composer dependencies. This is a deliberate alternative to the earlier Laravel direction. The Laravel migrations remain for historical planning only; do not run them over existing SQL tables.

## Security rules
- Keep `app/` and `config.php` outside the domain document root.
- Only files in a dedicated `public/` directory should be web-accessible.
- Never commit `config.php`, passwords, API keys, or client records.
- Never expose error details to end users.
- Use password_hash/password_verify, sessions with secure cookies, CSRF tokens and server-side role/office authorization before handling real data.
- Avoid using any default admin password or public setup script.

## Current status
Phase 2 SQL for users and PDO connection helper are committed. These files alone are NOT a functional login system. No database import or deployment has been performed for Phase 2.

## Next action
Import phase2_auth.sql into the dedicated OH ONE database, then confirm the `users` table exists. Next, implement a secure first-admin bootstrap and authentication endpoints before deploying.
