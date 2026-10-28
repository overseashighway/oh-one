<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/security.php';
boot_session();
if (!empty($_SESSION['uid'])) { header('Location: /'); exit; }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    $email=trim(strtolower((string)($_POST['email'] ?? '')));
    $password=(string)($_POST['password'] ?? '');
    if (strlen($email)>255 || strlen($password)>1024) {
        $error='Invalid email or password.';
    } else {
        $db=require dirname(__DIR__) . '/app/db.php';
        $stmt=$db->prepare('SELECT id, password_hash, role, office_id FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$email]); $row=$stmt->fetch();
        if ($row && password_verify($password, $row['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['uid']=(int)$row['id']; $_SESSION['role']=$row['role']; $_SESSION['office_id']=$row['office_id'];
            $_SESSION['csrf']=bin2hex(random_bytes(32));
            header('Location: /'); exit;
        }
        $error='Invalid email or password.';
        usleep(250000);
    }
}
header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>OH ONE Login</title><style>body{font:16px system-ui;background:#edf2f9;display:grid;place-items:center;min-height:100vh;margin:0}.panel{background:white;padding:36px;border-radius:18px;width:min(360px,85vw);box-shadow:0 10px 40px #12243b15}label{display:block;margin:14px 0 5px}input{box-sizing:border-box;width:100%;padding:12px;border:1px solid #bac6d5;border-radius:8px}button{margin-top:22px;background:#163d77;color:white;border:0;padding:12px;width:100%;border-radius:8px;cursor:pointer}.error{color:#9d1b1b}</style></head><body><main class="panel"><h1>OH ONE</h1><p>Overseas Highway CRM</p><?php if($error):?><p class="error"><?=e($error)?></p><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label for="email">Email</label><input id="email" type="email" name="email" autocomplete="username" required><label for="password">Password</label><input id="password" type="password" name="password" autocomplete="current-password" required><button type="submit">Sign in</button></form></main></body></html>
