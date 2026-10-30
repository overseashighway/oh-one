<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/security.php';
$u=require_login();
$db=require dirname(__DIR__).'/app/db.php';
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrf_check();
 $old=(string)($_POST['old']??'');
 $new=(string)($_POST['new']??'');
 $confirm=(string)($_POST['confirm']??'');
 $s=$db->prepare('SELECT password_hash FROM users WHERE id=? AND is_active=1');
 $s->execute([(int)$u['id']]);
 $hash=$s->fetchColumn();
 if(!$hash || !password_verify($old,(string)$hash))$error='Incorrect current password.';
 elseif(strlen($new)<12 || strlen($new)>128 || $new!==$confirm || $new===$old)$error='New password must be different, 12-128 characters, and match confirmation.';
 else{
  $s=$db->prepare('UPDATE users SET password_hash=? WHERE id=? AND is_active=1');
  $s->execute([password_hash($new,PASSWORD_DEFAULT),(int)$u['id']]);
  session_regenerate_id(true);
  $_SESSION['csrf']=bin2hex(random_bytes(32));
  $_SESSION['notice']='Password updated.';
  header('Location: /password.php');exit;
 }
}
boot_session();$notice=(string)($_SESSION['notice']??'');unset($_SESSION['notice']);
header('Content-Type: text/html; charset=utf-8');header('Cache-Control: no-store');
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>OH ONE Password</title><style>body{font:16px system-ui;background:#f3f6fb;color:#17253b;margin:0}main{max-width:480px;margin:40px auto;padding:30px;background:white;border-radius:12px}label{display:block;margin:16px 0}input{display:block;box-sizing:border-box;width:100%;padding:12px;border:1px solid #ccd5e0;border-radius:8px;margin-top:6px}button{background:#173d79;color:white;border:0;border-radius:8px;padding:12px 18px}.error{color:#a11}.ok{color:#176c37}</style></head><body><main><p><a href="/">← Dashboard</a></p><h1>Change Password</h1><?php if($error):?><p class="error"><?=e($error)?></p><?php endif;?><?php if($notice):?><p class="ok"><?=e($notice)?></p><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>Current password<input type="password" name="old" required autocomplete="current-password"></label><label>New password (12+ characters)<input type="password" name="new" required minlength="12" maxlength="128" autocomplete="new-password"></label><label>Confirm new password<input type="password" name="confirm" required minlength="12" maxlength="128" autocomplete="new-password"></label><button>Update Password</button></form></main></body></html>