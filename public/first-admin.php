<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/security.php';
boot_session();
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') { http_response_code(403); exit('HTTPS is required.'); }
$config = require dirname(__DIR__) . '/config.php';
$token = $config['setup_token'] ?? '';
if (!is_string($token) || strlen($token) < 32) { http_response_code(404); exit('Not found'); }
$provided = (string)($_GET['token'] ?? $_POST['token'] ?? '');
if (!hash_equals($token, $provided)) { http_response_code(404); exit('Not found'); }
$db = require dirname(__DIR__) . '/app/db.php';
if ((int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn() !== 0) { http_response_code(403); exit('Initial setup already completed. Delete this file from hosting.'); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim(strtolower((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    if (mb_strlen($name) < 2 || mb_strlen($name) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255 || strlen($password) < 14 || strlen($password) > 1024) {
        $error = 'Enter a valid name, email and password of at least 14 characters.';
    } else {
        try {
            $db->beginTransaction();
            // Guard concurrent setup requests with a table-level lock unavailable to ordinary shared-hosting users:
            // uniqueness on email and the setup-token requirement limit exposure; manually remove this file immediately.
            if ((int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn() !== 0) throw new RuntimeException('Setup already completed');
            $stmt = $db->prepare("INSERT INTO users (office_id, full_name, email, password_hash, role, is_active) VALUES (NULL, ?, ?, ?, 'founder', 1)");
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $db->commit();
            echo '<!doctype html><html><meta charset="utf-8"><title>OH ONE Setup Complete</title><body><h2>Founder created.</h2><p>Immediately delete public/first-admin.php and remove setup_token from config.php. Then open <a href="/login.php">Login</a>.</p></body></html>';
            exit;
        } catch (Throwable $ex) {
            if ($db->inTransaction()) $db->rollBack();
            $error = 'Setup could not complete. Verify database and try again.';
        }
    }
}
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="robots" content="noindex"><meta name="viewport" content="width=device-width,initial-scale=1"><title>OH ONE Founder Setup</title><style>body{font:16px system-ui;background:#eef3f9;margin:0;min-height:100vh;display:grid;place-items:center}main{background:white;border-radius:14px;padding:30px;width:min(420px,85vw)}input{box-sizing:border-box;width:100%;padding:12px;margin:6px 0 14px}button{padding:12px 20px;background:#123b72;color:white;border:0;border-radius:6px}.error{color:#a00}</style></head><body><main><h1>OH ONE — Founder Setup</h1><p>One-time installation. Use a unique strong password.</p><?php if($error):?><p class="error"><?=h($error)?></p><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="token" value="<?=h($provided)?>"><label>Full name<input name="name" maxlength="150" required></label><label>Email<input type="email" name="email" maxlength="255" required></label><label>Password (14+ characters)<input type="password" name="password" minlength="14" autocomplete="new-password" required></label><button>Create Founder</button></form></main></body></html>
