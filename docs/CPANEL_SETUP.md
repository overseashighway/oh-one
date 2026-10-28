# OH ONE — cPanel setup checklist

## Verified from screenshot
phpMyAdmin 5.2.3; MariaDB 11.4.13; PHP 8.4.27 **inside phpMyAdmin**. These do not establish the website PHP version or Composer/SSH availability.

## Required before installing Laravel
1. Confirm domain/subdomain and document root. Recommended: a dedicated subdomain whose web root points to Laravel's **public/** folder.
2. Confirm PHP CLI version, required extensions, Composer and Terminal/SSH availability.
3. Create a **new, empty** MySQL database and a dedicated least-privilege user in cPanel's database wizard. Do not reuse an existing website database.
4. Generate Laravel application using Composer in a safe non-public application directory. Merge these repository docs/migrations into the scaffold; do not run migrations until app configuration and backup are confirmed.
5. Configure .env only on hosting; never commit .env or passwords. Set APP_ENV=production, APP_DEBUG=false, APP_URL=https://your-domain.
6. Configure HTTPS, secure storage, scheduler/queue where available, and backups.
7. Run php artisan migrate only after verifying target database and reviewing migration list.

## Important
The repository currently contains documentation and starter migrations, **not a complete Laravel application**. Running artisan or visiting a live CRM URL will not work yet.

## Next user action
Provide only the intended subdomain (e.g. crm.example.com) and whether cPanel Terminal is available. Never send passwords or secrets in chat.
