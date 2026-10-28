# OH ONE — Phase 3: Login & dashboard (development only)

## State
Code is committed to GitHub, but is NOT deployed to cPanel. No founder account exists yet. The website is not ready for use.

## Critical deployment structure
The domain document root must be the repository's `public/` folder (or a separately deployed public folder with matching paths). The `app/`, `database/`, `docs/`, and `config.php` files must remain OUTSIDE web-accessible document root.

The existing cPanel Git clone path is `/home/overnzbk/repositories/oh-one`, while the domain document root was reported as `/home/overnzbk/crm.overseashighwaystudy.com`. DO NOT copy the entire repository into that domain root.

## Configuration
Copy `app/config.example.php` to `config.php` at the repository root (not the public folder). Fill in database credentials only on the server. Never commit this file. Add `/config.php` to .gitignore before local changes. HTTPS certificate must be active before login.

## Founder bootstrap
No insecure web installer is provided. A founder password hash must be generated securely on a trusted PHP environment, then the founder record inserted using a parameterized process. Do not use a plaintext password in SQL or GitHub.

## Before production
- Verify PHP CLI/web version, PDO MySQL extension, correct document root, and HTTPS.
- Add server-side login rate limiting, security logging, and stronger role/office authorization.
- Add secure founder provisioning and test sessions.
- Restrict production access until these are completed.
- Reconcile existing raw SQL tables with Laravel migrations if Laravel is later adopted.
