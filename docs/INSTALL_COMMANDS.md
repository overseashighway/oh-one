# OH ONE — Laravel installation commands (not yet executed)

**Important:** Current repository is a starter, not a complete Laravel installation. Run these only after confirming Composer and PHP CLI and choosing a safe working directory.

## Check hosting
```bash
php -v
composer --version
php -m
```

## Installation strategy
Do not run `composer create-project` over the existing nonempty Git repository. Instead create a clean Laravel skeleton in a temporary sibling directory, then integrate the repository's documentation and migrations carefully under version control. Confirm Laravel compatibility with the actual PHP CLI version before installation.

## Production requirements
- Dedicated subdomain with document root pointing to Laravel `public/` only.
- Separate MySQL database/user created through cPanel.
- Real `.env` only on server; do not commit it.
- HTTPS, `APP_DEBUG=false`, `APP_ENV=production`, secure `APP_KEY`, backup and restricted file permissions.
- Run migrations only after reviewing and backing up the target database.

## Status
Commands are instructions only. No hosting commands executed, database created, or Laravel deployed by ChatGPT.
