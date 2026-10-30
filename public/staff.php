<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/security.php';
$u=require_founder();$db=require dirname(__DIR__).'/app/db.php';
$error='';$success='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrf_check();
 $action=(string)($_POST['action']??'');
 try{
  if($action==='create'){
   $name=trim((string)($_POST['name']??''));$email=strtolower(trim((string)($_POST['email']??'')));
   $password=(string)($_POST['password']??'');$office=(int)($_POST['office']??0);
   if(mb_strlen($name)<2||mb_strlen($name)>150||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>255||strlen($password)<12||strlen($password)>128||$office<1)throw new RuntimeException('Enter valid name, email, office and a password of at least 12 characters.');
   $s=$db->prepare('SELECT id FROM offices WHERE id=? AND is_active=1');$s->execute([$office]);
   if(!$s->fetch())throw new RuntimeException('Office not active.');
   $s=$db->prepare("INSERT INTO users (office_id,full_name,email,password_hash,role,is_active) VALUES (?,?,?,?,'staff',1)");
   $s->execute([$office,$name,$email,password_hash($password,PASSWORD_DEFAULT)]);$success='Staff account created. Share credentials securely and ask staff to change their password.';
  }elseif($action==='toggle'){
   $id=(int)($_POST['id']??0);$active=(int)($_POST['active']??-1);
   if($id<1||!in_array($active,[0,1],true))throw new RuntimeException('Invalid request.');
   $s=$db->prepare("UPDATE users SET is_active=? WHERE id=? AND role='staff'");$s->execute([$active,$id]);
   $success=$s->rowCount()?'Staff status updated.':'No change.';
  }else throw new RuntimeException('Invalid action.');
 }catch(RuntimeException $ex){$error=$ex->getMessage();}
 catch(PDOException $ex){$error='Could not save. Check whether this email already exists.';}
}
$offices=$db->query('SELECT id,name FROM offices WHERE is_active=1 ORDER BY name')->fetchAll();
$staff=$db->query("SELECT u.id,u.full_name,u.email,u.is_active,o.name AS office_name FROM users u LEFT JOIN offices o ON o.id=u.office_id WHERE u.role='staff' ORDER BY u.id DESC LIMIT 200")->fetchAll();
header('Content-Type: text/html; charset=utf-8');header('Cache-Control: no-store');
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>OH ONE Staff</title><style>
*{box-sizing:border-box}body{margin:0;background:#f3f6fb;color:#17253b;font:15px system-ui}header{background:#102a53;color:#fff;padding:18px 5%;display:flex;justify-content:space-between}header a{color:white}main{max-width:1050px;margin:30px auto;padding:0 18px}section{background:white;border-radius:12px;padding:24px;margin:20px 0;box-shadow:0 4px 18px #14213d10}.fields{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px}label{display:block;font-weight:600}input,select{display:block;width:100%;padding:11px;margin-top:5px;border:1px solid #cbd5e1;border-radius:7px;font:inherit}button{background:#173d79;color:white;border:0;border-radius:7px;padding:10px 15px;cursor:pointer;margin-top:12px}.scroll{overflow:auto}table{width:100%;border-collapse:collapse;white-space:nowrap}th,td{text-align:left;padding:12px;border-bottom:1px solid #e8edf5}.ok{background:#e8f5e9;padding:12px}.err{background:#fce8e8;padding:12px}.muted{color:#64748b}td button{margin:0}
</style></head><body><header><strong>OH ONE · Staff Management</strong><a href="/">Dashboard</a></header><main><h1>Staff Accounts</h1><p class="muted">Founder only · Staff are limited to their assigned office.</p><?php if($error):?><p class="err"><?=e($error)?></p><?php endif;?><?php if($success):?><p class="ok"><?=e($success)?></p><?php endif;?>
<section><h2>Create Staff Account</h2><form method="post" autocomplete="off"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="create"><div class="fields"><label>Full name<input name="name" required maxlength="150"></label><label>Email<input type="email" name="email" required maxlength="255"></label><label>Office<select name="office" required><option value="">Select office</option><?php foreach($offices as $o):?><option value="<?=(int)$o['id']?>"><?=e($o['name'])?></option><?php endforeach;?></select></label><label>Temporary password (12+ characters)<input type="password" name="password" required minlength="12" maxlength="128" autocomplete="new-password"></label></div><button>Create Staff</button></form><p class="muted">Use a unique temporary password. Do not share it in public chats. Password reset/change workflow is not yet implemented.</p></section>
<section><h2>Staff Directory</h2><div class="scroll"><table><thead><tr><th>Name</th><th>Email</th><th>Office</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach($staff as $s):?><tr><td><?=e($s['full_name'])?></td><td><?=e($s['email'])?></td><td><?=e($s['office_name'])?></td><td><?=$s['is_active']?'Active':'Inactive'?></td><td><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?=(int)$s['id']?>"><input type="hidden" name="active" value="<?=$s['is_active']?'0':'1'?>"><button><?=$s['is_active']?'Deactivate':'Activate'?></button></form></td></tr><?php endforeach;?><?php if(!$staff):?><tr><td colspan="5">No staff accounts yet.</td></tr><?php endif;?></tbody></table></div></section><p><a href="/workflow.php">Open Workflow →</a></p></main></body></html>