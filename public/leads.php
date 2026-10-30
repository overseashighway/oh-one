<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/security.php';
$u=require_login();
$db=require dirname(__DIR__) . '/app/db.php';
$isFounder=$u['role']==='founder';
$officeId=(int)($u['office_id'] ?? 0);
if (!$isFounder && $officeId<=0) { http_response_code(403); exit('Office access is not configured.'); }
$error=''; $success='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 csrf_check();
 $action=(string)($_POST['action']??'');
 if ($action==='office' && $isFounder) {
  $name=trim((string)($_POST['name']??'')); $code=strtoupper(trim((string)($_POST['code']??'')));
  $country=strtoupper(trim((string)($_POST['country']??''))); $city=trim((string)($_POST['city']??''));
  if (mb_strlen($name)<2 || mb_strlen($name)>150 || !preg_match('/^[A-Z0-9_-]{2,30}$/',$code) || !preg_match('/^[A-Z]{2}$/',$country) || mb_strlen($city)>100) $error='Check office details.';
  else { try { $s=$db->prepare('INSERT INTO offices (name,code,country_code,city) VALUES (?,?,?,?)');$s->execute([$name,$code,$country,$city?:null]);$success='Office added.'; } catch(PDOException $ex){$error='Could not add office. Check that the code is unique.';} }
 } elseif ($action==='lead' && $isFounder) {
  $name=trim((string)($_POST['name']??'')); $email=trim((string)($_POST['email']??'')); $phone=trim((string)($_POST['phone']??''));
  $service=(string)($_POST['service']??''); $source=trim((string)($_POST['source']??''));$notes=trim((string)($_POST['notes']??''));
  $target=$isFounder?(int)($_POST['office_id']??0):$officeId;
  $allowed=['Education','Migration','Travel','Investment','Other'];
  if (mb_strlen($name)<2 || mb_strlen($name)>200 || ($email!=='' && (!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>255)) || strlen($phone)>50 || !in_array($service,$allowed,true) || mb_strlen($source)>80 || mb_strlen($notes)>5000 || $target<=0) $error='Check lead details.';
  else {
   $s=$db->prepare('SELECT id FROM offices WHERE id=? AND is_active=1');$s->execute([$target]);
   if (!$s->fetch()) $error='Choose an active office.';
   else { $s=$db->prepare("INSERT INTO leads (office_id,full_name,email,phone,service_interest,status,source,notes) VALUES (?,?,?,?,?,'new',?,?)");$s->execute([$target,$name,$email?:null,$phone?:null,$service,$source?:null,$notes?:null]);$success='Lead added.'; }
  }
 } elseif ($action==='status') {
  $id=(int)($_POST['lead_id']??0);$status=(string)($_POST['status']??'');
  $allowed=['new','contacted','qualified','application','converted','lost'];
  if ($id<=0 || !in_array($status,$allowed,true)) $error='Invalid status.';
  else {
   if ($isFounder) {$s=$db->prepare('UPDATE leads SET status=? WHERE id=?');$s->execute([$status,$id]);}
   else {$s=$db->prepare('UPDATE leads SET status=? WHERE id=? AND office_id=? AND id IN (SELECT lead_id FROM lead_assignments WHERE user_id=?)');$s->execute([$status,$id,$officeId,(int)$u['id']]);}
   $success=$s->rowCount()?'Status updated.':'No change or access denied.';
  }
 } else $error='Action not allowed.';
}
$offices=$isFounder?$db->query('SELECT id,name,code FROM offices WHERE is_active=1 ORDER BY name')->fetchAll():[];
if ($isFounder) $rows=$db->query('SELECT l.id,l.full_name,l.email,l.phone,l.service_interest,l.status,l.source,l.created_at,o.name AS office_name FROM leads l JOIN offices o ON o.id=l.office_id ORDER BY l.id DESC LIMIT 100')->fetchAll();
else {$s=$db->prepare('SELECT l.id,l.full_name,l.email,l.phone,l.service_interest,l.status,l.source,l.created_at,o.name AS office_name FROM leads l JOIN offices o ON o.id=l.office_id WHERE l.office_id=? AND l.id IN (SELECT lead_id FROM lead_assignments WHERE user_id=?) ORDER BY l.id DESC LIMIT 100');$s->execute([$officeId,(int)$u['id']]);$rows=$s->fetchAll();}
if($_SERVER['REQUEST_METHOD']==='POST' && $error===''){boot_session();$_SESSION['leads_notice']=$success;header('Location: /leads.php',true,303);exit;}
boot_session();if(isset($_SESSION['leads_notice'])){$success=(string)$_SESSION['leads_notice'];unset($_SESSION['leads_notice']);}
$statuses=['new','contacted','qualified','application','converted','lost'];
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>OH ONE · Leads</title><style>
*{box-sizing:border-box}body{margin:0;font:15px system-ui;background:#f2f5fa;color:#192d48}header{background:#102b53;color:#fff;padding:18px 5%;display:flex;justify-content:space-between}a{color:inherit}main{max-width:1150px;margin:30px auto;padding:0 18px}section{background:white;border-radius:12px;padding:22px;margin:20px 0;box-shadow:0 3px 15px #132d5010}h1{margin:0 0 12px}h2{font-size:19px}.fields{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:12px}label{display:block;font-weight:600}input,select,textarea{display:block;width:100%;padding:10px;border:1px solid #c6d0de;border-radius:7px;margin-top:5px;font:inherit}button{background:#173d79;color:white;border:0;border-radius:7px;padding:10px 16px;cursor:pointer;margin-top:12px}.notice{padding:12px;background:#e8f4ea}.error{padding:12px;background:#fce8e8}.scroll{overflow:auto}table{width:100%;border-collapse:collapse;white-space:nowrap}th,td{text-align:left;padding:11px;border-bottom:1px solid #e7ecf3}th{background:#f6f8fc}td form{display:flex;align-items:center;gap:6px}td select{min-width:135px;margin:0}td button{margin:0}small{color:#64748b}.toolbar{display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin:12px 0}.toolbar input,.toolbar select{width:auto;min-width:190px;margin:0}.navlinks{display:flex;gap:14px;flex-wrap:wrap}
</style></head><body><header><strong>OH ONE · Lead Management</strong><nav class="navlinks"><a href="/">Dashboard</a><a href="/workflow.php">Workflow &amp; Tasks</a><?php if($isFounder):?><a href="/staff.php">Staff</a><?php endif;?></nav></header><main><h1>Leads</h1><p>Record enquiries and track progress. Showing the 100 most recent leads.</p>
<?php if($error):?><p class="error"><?=e($error)?></p><?php endif;?><?php if($success):?><p class="notice"><?=e($success)?></p><?php endif;?>
<?php if($isFounder && !$offices):?><section><h2>First: Add an office</h2><p>You need an office before you can add leads.</p></section><?php endif;?>
<?php if($isFounder):?><section><h2>Add Office</h2><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="office"><div class="fields"><label>Office name<input name="name" required maxlength="150" placeholder="Malaysia HQ"></label><label>Unique code<input name="code" required maxlength="30" placeholder="MY-HQ"></label><label>Country (2-letter code)<input name="country" required maxlength="2" placeholder="MY"></label><label>City<input name="city" maxlength="100" placeholder="Kuala Lumpur"></label></div><button>Add office</button></form></section><?php endif;?>
<?php if($isFounder):?><section><h2>Add Lead</h2><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="lead"><div class="fields">
<label>Full name<input name="name" required maxlength="200"></label><label>Email<input name="email" type="email" maxlength="255"></label><label>Phone<input name="phone" maxlength="50"></label>
<?php if($isFounder):?><label>Office<select name="office_id" required><option value="">Choose office</option><?php foreach($offices as $o):?><option value="<?=(int)$o['id']?>"><?=e($o['name'])?> (<?=e($o['code'])?>)</option><?php endforeach;?></select></label><?php endif;?>
<label>Service<select name="service" required><option>Education</option><option>Migration</option><option>Travel</option><option>Investment</option><option>Other</option></select></label><label>Lead source<input name="source" maxlength="80" placeholder="Facebook, Referral, WhatsApp"></label></div><label>Notes<textarea name="notes" maxlength="5000" rows="2"></textarea></label><button>Add lead</button></form></section><?php endif;?>
<section><h2>Recent Leads</h2><div class="toolbar"><label for="lead-search">Search leads <input id="lead-search" type="search" placeholder="Name, phone, email, source"></label><label for="lead-status-filter">Filter status <select id="lead-status-filter"><option value="">All statuses</option><?php foreach($statuses as $st):?><option value="<?=e($st)?>"><?=e(ucfirst($st))?></option><?php endforeach;?></select></label><span id="lead-visible-count" aria-live="polite"></span></div><p><small>Search and filter the 100 most recent leads displayed on this page.</small></p><div class="scroll"><table id="lead-table"><thead><tr><th>ID</th><th>Name</th><th>Office</th><th>Service</th><th>Email</th><th>Phone</th><th>Source</th><th>Created</th><th>Status</th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td><?=(int)$r['id']?></td><td><?=e($r['full_name'])?></td><td><?=e($r['office_name'])?></td><td><?=e($r['service_interest'])?></td><td><?=e($r['email'])?></td><td><?=e($r['phone'])?></td><td><?=e($r['source'])?></td><td><?=e($r['created_at'])?></td><td><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="status"><input type="hidden" name="lead_id" value="<?=(int)$r['id']?>"><select name="status"><?php foreach($statuses as $status):?><option value="<?=e($status)?>" <?=$r['status']===$status?'selected':''?>><?=e(ucfirst($status))?></option><?php endforeach;?></select><button>Save</button></form></td></tr><?php endforeach;?>
<?php if(!$rows):?><tr><td colspan="9">No leads yet.</td></tr><?php endif;?></tbody></table></div></section><script>
(function(){
 const q=document.getElementById('lead-search'), status=document.getElementById('lead-status-filter'), table=document.getElementById('lead-table'), count=document.getElementById('lead-visible-count');
 if(!q||!status||!table)return;
 const rows=Array.from(table.tBodies[0].rows).filter(row=>row.cells.length===9 && row.cells[0].textContent.trim()!=='');
 function apply(){
  const term=q.value.trim().toLocaleLowerCase(), st=status.value;
  let visible=0;
  for(const row of rows){
   const matchesText=Array.from(row.cells).slice(0,8).some(cell=>cell.textContent.toLocaleLowerCase().includes(term));
   const matchesStatus=!st || row.querySelector('select[name="status"]')?.value===st;
   row.hidden=!(matchesText&&matchesStatus);
   if(!row.hidden)visible++;
  }
  count.textContent=visible+' of '+rows.length+' leads';
 }
 q.addEventListener('input',apply);status.addEventListener('change',apply);apply();
})();
</script><p><small>OH ONE Phase 2 · Basic lead management. Do not upload sensitive documents here.</small></p></main></body></html>