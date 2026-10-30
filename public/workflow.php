<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/security.php';
$u=require_login(); $db=require dirname(__DIR__).'/app/db.php';
$founder=$u['role']==='founder'; $uid=(int)$u['id'];$office=(int)($u['office_id']??0);
if(!$founder && !$office){http_response_code(403);exit('Office not assigned');}
function wfdate(string $v):?string {if($v==='')return null; $d=DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$v);return $d&&$d->format('Y-m-d\TH:i')===$v?$d->format('Y-m-d H:i:s'):null;}
function canlead(PDO $db,int $id,bool $founder,int $office,int $uid=0):?array {$s=$db->prepare('SELECT id,office_id,full_name FROM leads WHERE id=?'.($founder?'':' AND office_id=? AND id IN (SELECT lead_id FROM lead_assignments WHERE user_id=?)'));$s->execute($founder?[$id]:[$id,$office,$uid]);return $s->fetch()?:null;}
function canuser(PDO $db,int $id,int $office):bool {$s=$db->prepare('SELECT id FROM users WHERE id=? AND office_id=? AND is_active=1');$s->execute([$id,$office]);return(bool)$s->fetch();}
$error='';$ok='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrf_check();$action=(string)($_POST['action']??'');
 try {
  if($action==='assign'){
   if(!$founder)throw new RuntimeException('Founder only.');
   $lead=canlead($db,(int)($_POST['lead']??0),true,0);$assignee=(int)($_POST['assignee']??0);
   if(!$lead||!canuser($db,$assignee,(int)$lead['office_id']))throw new RuntimeException('Choose a staff member in the same office.');
   $s=$db->prepare('INSERT INTO lead_assignments (lead_id,user_id,assigned_by) VALUES (?,?,?) ON DUPLICATE KEY UPDATE user_id=VALUES(user_id),assigned_by=VALUES(assigned_by),assigned_at=CURRENT_TIMESTAMP');
   $s->execute([(int)$lead['id'],$assignee,$uid]);$ok='Lead assigned.';
  }elseif($action==='followup'){
   $lead=canlead($db,(int)($_POST['lead']??0),$founder,$office,$uid);
   $method=(string)($_POST['method']??'');$outcome=(string)($_POST['outcome']??'');$note=trim((string)($_POST['note']??''));
   $raw=(string)($_POST['next']??'');$next=wfdate($raw);
   if(!$lead||!in_array($method,['Call','WhatsApp','Email','Meeting'],true)||!in_array($outcome,['Pending','Connected','No answer','Completed'],true)||mb_strlen($note)<2||mb_strlen($note)>3000||($raw!==''&&!$next))throw new RuntimeException('Check follow-up details.');
   $s=$db->prepare('INSERT INTO lead_followups (lead_id,user_id,contact_method,outcome,note,next_followup_at) VALUES (?,?,?,?,?,?)');
   $s->execute([(int)$lead['id'],$uid,$method,$outcome,$note,$next]);$ok='Follow-up saved.';
  }elseif($action==='task'){
   $leadId=(int)($_POST['lead']??0);$lead=$leadId?canlead($db,$leadId,$founder,$office,$uid):null;
   $targetOffice=$lead?(int)$lead['office_id']:($founder?(int)($_POST['office']??0):$office);
   $assignee=(int)($_POST['assignee']??0);$title=trim((string)($_POST['title']??''));$details=trim((string)($_POST['details']??''));
   $priority=(string)($_POST['priority']??'');$raw=(string)($_POST['due']??'');$due=wfdate($raw);
   if(($leadId&&!$lead)||!canuser($db,$assignee,$targetOffice)||mb_strlen($title)<3||mb_strlen($title)>200||mb_strlen($details)>5000||!in_array($priority,['low','normal','high'],true)||($raw!==''&&!$due))throw new RuntimeException('Check task details, office and assignee.');
   $s=$db->prepare('INSERT INTO crm_tasks (lead_id,office_id,assigned_to,created_by,title,details,priority,due_at) VALUES (?,?,?,?,?,?,?,?)');
   $s->execute([$leadId?:null,$targetOffice,$assignee,$uid,$title,$details?:null,$priority,$due]);$ok='Task created.';
  }elseif($action==='taskstatus'){
   $id=(int)($_POST['id']??0);$status=(string)($_POST['status']??'');
   if($id<1||!in_array($status,['open','in_progress','done'],true))throw new RuntimeException('Invalid task.');
   $s=$db->prepare('UPDATE crm_tasks SET status=? WHERE id=?'.($founder?'':' AND assigned_to=? AND office_id=?'));
   $s->execute($founder?[$status,$id]:[$status,$id,$uid,$office]);$ok=$s->rowCount()?'Task updated.':'No change or permission denied.';
  }else throw new RuntimeException('Action not allowed.');
 }catch(RuntimeException $ex){$error=$ex->getMessage();}
 catch(PDOException $ex){$error='Database operation failed. Verify Phase 3 migration.';}
}
if($founder){
 $leads=$db->query('SELECT id,office_id,full_name FROM leads ORDER BY id DESC LIMIT 300')->fetchAll();
 $staff=$db->query("SELECT id,office_id,full_name FROM users WHERE is_active=1 AND office_id IS NOT NULL ORDER BY full_name")->fetchAll();
 $offices=$db->query('SELECT id,name FROM offices WHERE is_active=1 ORDER BY name')->fetchAll();
 $assignments=$db->query('SELECT l.full_name AS lead_name,u.full_name AS staff_name,o.name AS office_name FROM lead_assignments a JOIN leads l ON l.id=a.lead_id JOIN users u ON u.id=a.user_id JOIN offices o ON o.id=l.office_id ORDER BY a.assigned_at DESC LIMIT 40')->fetchAll();
 $followups=$db->query('SELECT l.full_name AS lead_name,f.contact_method,f.outcome,f.note,f.next_followup_at,f.created_at FROM lead_followups f JOIN leads l ON l.id=f.lead_id ORDER BY f.id DESC LIMIT 40')->fetchAll();
 $tasks=$db->query('SELECT t.id,t.title,t.status,t.priority,t.due_at,u.full_name AS assignee FROM crm_tasks t JOIN users u ON u.id=t.assigned_to ORDER BY (t.status="done"),t.due_at IS NULL,t.due_at,t.id DESC LIMIT 80')->fetchAll();
}else{
 $s=$db->prepare('SELECT id,office_id,full_name FROM leads WHERE office_id=? ORDER BY id DESC LIMIT 300');$s->execute([$office]);$leads=$s->fetchAll();
 $s=$db->prepare('SELECT id,office_id,full_name FROM users WHERE is_active=1 AND office_id=? ORDER BY full_name');$s->execute([$office]);$staff=$s->fetchAll();
 $offices=[];
 $s=$db->prepare('SELECT l.full_name AS lead_name,u.full_name AS staff_name,o.name AS office_name FROM lead_assignments a JOIN leads l ON l.id=a.lead_id JOIN users u ON u.id=a.user_id JOIN offices o ON o.id=l.office_id WHERE l.office_id=? ORDER BY a.assigned_at DESC LIMIT 40');$s->execute([$office]);$assignments=$s->fetchAll();
 $s=$db->prepare('SELECT l.full_name AS lead_name,f.contact_method,f.outcome,f.note,f.next_followup_at,f.created_at FROM lead_followups f JOIN leads l ON l.id=f.lead_id WHERE l.office_id=? ORDER BY f.id DESC LIMIT 40');$s->execute([$office]);$followups=$s->fetchAll();
 $s=$db->prepare('SELECT t.id,t.title,t.status,t.priority,t.due_at,u.full_name AS assignee FROM crm_tasks t JOIN users u ON u.id=t.assigned_to WHERE t.office_id=? AND (t.assigned_to=? OR t.created_by=?) ORDER BY (t.status="done"),t.due_at IS NULL,t.due_at,t.id DESC LIMIT 80');$s->execute([$office,$uid,$uid]);$tasks=$s->fetchAll();
}
header('Content-Type: text/html; charset=utf-8');header('Cache-Control: no-store');
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>OH ONE Workflow</title><style>
*{box-sizing:border-box}body{font:15px system-ui;background:#f2f5fa;color:#172b46;margin:0}header{background:#102b53;color:white;padding:18px 5%;display:flex;justify-content:space-between}header a{color:white}main{max-width:1150px;margin:28px auto;padding:0 16px}section{background:white;border-radius:12px;padding:22px;margin:18px 0;box-shadow:0 3px 18px #15294312}h1{margin-bottom:6px}h2{font-size:20px}.fields{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}label{display:block;font-weight:600}input,select,textarea{width:100%;display:block;border:1px solid #cad3e1;border-radius:7px;padding:10px;margin-top:6px;font:inherit}button{border:0;border-radius:7px;background:#183e79;color:white;padding:10px 16px;margin-top:12px;cursor:pointer}.scroll{overflow:auto}table{border-collapse:collapse;width:100%;white-space:nowrap}td,th{border-bottom:1px solid #e8edf5;text-align:left;padding:10px}td.note{white-space:normal;min-width:180px;max-width:340px}.ok{background:#e8f5e9;padding:12px}.err{background:#fce7e7;padding:12px}.muted{color:#66748b}td form{display:flex;gap:6px;align-items:center}td form button{margin:0}
</style></head><body><header><strong>OH ONE · Workflow</strong><a href="/">Dashboard</a></header><main><h1>Assignment, Follow-ups & Tasks</h1><p class="muted">Phase 3 · Office-scoped workflow</p>
<?php if($error):?><p class="err"><?=e($error)?></p><?php endif;?><?php if($ok):?><p class="ok"><?=e($ok)?></p><?php endif;?>
<?php if(!$staff):?><section><strong>No active office staff accounts found.</strong><p>Create staff users with an office assignment before using assignments or tasks. Founder login alone does not count as office staff.</p></section><?php endif;?>
<?php if($founder):?><section><h2>Assign Lead to Staff</h2><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="assign"><div class="fields"><label>Lead<select name="lead" required><option value="">Choose</option><?php foreach($leads as $l):?><option value="<?=(int)$l['id']?>">#<?=(int)$l['id']?> <?=e($l['full_name'])?></option><?php endforeach;?></select></label><label>Staff (must be in lead office)<select name="assignee" required><option value="">Choose</option><?php foreach($staff as $s):?><option value="<?=(int)$s['id']?>"><?=e($s['full_name'])?> · Office #<?=(int)$s['office_id']?></option><?php endforeach;?></select></label></div><button>Assign Lead</button></form></section><?php endif;?>
<section><h2>Log Follow-up</h2><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="followup"><div class="fields"><label>Lead<select name="lead" required><option value="">Choose</option><?php foreach($leads as $l):?><option value="<?=(int)$l['id']?>">#<?=(int)$l['id']?> <?=e($l['full_name'])?></option><?php endforeach;?></select></label><label>Method<select name="method"><option>Call</option><option>WhatsApp</option><option>Email</option><option>Meeting</option></select></label><label>Outcome<select name="outcome"><option>Pending</option><option>Connected</option><option>No answer</option><option>Completed</option></select></label><label>Next follow-up (server local time)<input type="datetime-local" name="next"></label></div><label>Notes<textarea name="note" maxlength="3000" required rows="2"></textarea></label><button>Save Follow-up</button></form></section>
<section><h2>Create Task</h2><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="task"><div class="fields"><label>Title<input name="title" maxlength="200" required></label><label>Assign to<select name="assignee" required><option value="">Choose</option><?php foreach($staff as $s):?><option value="<?=(int)$s['id']?>"><?=e($s['full_name'])?> · Office #<?=(int)$s['office_id']?></option><?php endforeach;?></select></label><label>Related lead (optional)<select name="lead"><option value="0">No lead</option><?php foreach($leads as $l):?><option value="<?=(int)$l['id']?>">#<?=(int)$l['id']?> <?=e($l['full_name'])?></option><?php endforeach;?></select></label>
<?php if($founder):?><label>Office (for tasks without lead)<select name="office"><option value="">Choose</option><?php foreach($offices as $o):?><option value="<?=(int)$o['id']?>"><?=e($o['name'])?></option><?php endforeach;?></select></label><?php endif;?>
<label>Priority<select name="priority"><option value="normal">Normal</option><option value="high">High</option><option value="low">Low</option></select></label><label>Due (server local time)<input type="datetime-local" name="due"></label></div><label>Details<textarea name="details" maxlength="5000" rows="2"></textarea></label><button>Create Task</button></form></section>
<section><h2>Tasks</h2><div class="scroll"><table><thead><tr><th>Title</th><th>Assigned to</th><th>Priority</th><th>Due</th><th>Status</th></tr></thead><tbody><?php foreach($tasks as $t):?><tr><td><?=e($t['title'])?></td><td><?=e($t['assignee'])?></td><td><?=e($t['priority'])?></td><td><?=e($t['due_at'])?></td><td><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="taskstatus"><input type="hidden" name="id" value="<?=(int)$t['id']?>"><select name="status"><?php foreach(['open','in_progress','done'] as $v):?><option value="<?=e($v)?>" <?=$t['status']===$v?'selected':''?>><?=e($v)?></option><?php endforeach;?></select><button>Save</button></form></td></tr><?php endforeach;?><?php if(!$tasks):?><tr><td colspan="5">No tasks yet.</td></tr><?php endif;?></tbody></table></div></section>
<section><h2>Lead Assignments</h2><?php foreach($assignments as $a):?><p><?=e($a['lead_name'])?> → <?=e($a['staff_name'])?> (<?=e($a['office_name'])?>)</p><?php endforeach;?><?php if(!$assignments):?><p class="muted">No assignments yet.</p><?php endif;?></section>
<section><h2>Follow-up History</h2><div class="scroll"><table><thead><tr><th>Lead</th><th>Method</th><th>Outcome</th><th>Note</th><th>Next</th><th>Logged</th></tr></thead><tbody><?php foreach($followups as $f):?><tr><td><?=e($f['lead_name'])?></td><td><?=e($f['contact_method'])?></td><td><?=e($f['outcome'])?></td><td class="note"><?=e($f['note'])?></td><td><?=e($f['next_followup_at'])?></td><td><?=e($f['created_at'])?></td></tr><?php endforeach;?><?php if(!$followups):?><tr><td colspan="6">No follow-ups yet.</td></tr><?php endif;?></tbody></table></div></section></main></body></html>