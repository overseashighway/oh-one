<?php
declare(strict_types=1);
function boot_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    if (PHP_SAPI !== 'cli' && !headers_sent()) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_name('ohone_session');
        session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
    }
    session_start();
}
function csrf_token(): string {
    boot_session();
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}
function csrf_check(): void {
    if (!hash_equals(csrf_token(), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(419); exit('Session expired. Refresh and retry.');
    }
}
function e(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function require_login(): array {
    boot_session();
    if (empty($_SESSION['uid'])) { header('Location: /login.php'); exit; }
    $db=require dirname(__DIR__).'/app/db.php';
    $s=$db->prepare('SELECT id,role,office_id FROM users WHERE id=? AND is_active=1 LIMIT 1');
    $s->execute([(int)$_SESSION['uid']]);
    $account=$s->fetch();
    if(!$account){$_SESSION=[];session_regenerate_id(true);header('Location: /login.php');exit;}
    $_SESSION['role']=$account['role'];$_SESSION['office_id']=$account['office_id'];
    return ['id'=>(int)$account['id'],'role'=>(string)$account['role'],'office_id'=>$account['office_id']];
}
function require_founder(): array {
    $u=require_login();
    if ($u['role'] !== 'founder') { http_response_code(403); exit('Forbidden'); }
    return $u;
}
