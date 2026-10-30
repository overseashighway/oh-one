<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/security.php';
$u=require_login();
$db=require dirname(__DIR__) . '/app/db.php';
$founder=$u['role']==='founder';
$officeId=(int)($u['office_id']??0);
if (!$founder && $officeId<=0) { http_response_code(403); exit('Office access is not configured.'); }
if ($founder) {
 $offices=(int)$db->query('SELECT COUNT(*) FROM offices')->fetchColumn();
 $leads=(int)$db->query('SELECT COUNT(*) FROM leads')->fetchColumn();
 $statusRows=$db->query('SELECT status, COUNT(*) AS total FROM leads GROUP BY status')->fetchAll();
 $officeRows=$db->query('SELECT o.name, COUNT(l.id) AS total FROM offices o LEFT JOIN leads l ON l.office_id=o.id GROUP BY o.id,o.name ORDER BY total DESC,o.name LIMIT 20')->fetchAll();
 $recent=$db->query('SELECT l.full_name,l.status,l.service_interest,l.created_at,o.name AS office_name FROM leads l JOIN offices o ON o.id=l.office_id ORDER BY l.id DESC LIMIT 8')->fetchAll();
} else {
 $s=$db->prepare('SELECT COUNT(*) FROM offices WHERE id=?');$s->execute([$officeId]);$offices=(int)$s->fetchColumn();
 $s=$db->prepare('SELECT COUNT(*) FROM leads WHERE office_id=? AND id IN (SELECT lead_id FROM lead_assignments WHERE user_id=?)');$s->execute([$officeId,(int)$u['id']]);$leads=(int)$s->fetchColumn();
 $s=$db->prepare('SELECT status,COUNT(*) AS total FROM leads WHERE office_id=? AND id IN (SELECT lead_id FROM lead_assignments WHERE user_id=?) GROUP BY status');$s->execute([$officeId,(int)$u['id']]);$statusRows=$s->fetchAll();
 $s=$db->prepare('SELECT o.name,COUNT(l.id) AS total FROM offices o LEFT JOIN leads l ON l.office_id=o.id WHERE o.id=? AND (l.id IS NULL OR l.id IN (SELECT lead_id FROM lead_assignments WHERE user_id=?)) GROUP BY o.id,o.name');$s->execute([$officeId,(int)$u['id']]);$officeRows=$s->fetchAll();
 $s=$db->prepare('SELECT l.full_name,l.status,l.service_interest,l.created_at,o.name AS office_name FROM leads l JOIN offices o ON o.id=l.office_id WHERE l.office_id=? AND l.id IN (SELECT lead_id FROM lead_assignments WHERE user_id=?) ORDER BY l.id DESC LIMIT 8');$s->execute([$officeId,(int)$u['id']]);$recent=$s->fetchAll();
}
$statuses=['new'=>0,'contacted'=>0,'qualified'=>0,'application'=>0,'converted'=>0,'lost'=>0];
foreach($statusRows as $r) { if(array_key_exists($r['status'],$statuses)) $statuses[$r['status']]=(int)$r['total']; }
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>OH ONE · Dashboard</title><style>
*{box-sizing:border-box}body{font:15px system-ui,-apple-system,sans-serif;background:#f3f6fb;color:#17253b;margin:0}header{background:#102a53;color:white;padding:18px 5%;display:flex;justify-content:space-between;align-items:center;gap:12px}header a{color:white}main{max-width:1150px;margin:32px auto;padding:0 20px}h1{font-size:29px;margin-bottom:4px}h2{font-size:19px;margin-top:0}.muted{color:#66748b}.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(155px,1fr));gap:14px;margin:24px 0}.card,section{background:white;border-radius:13px;padding:22px;box-shadow:0 4px 20px #14213d0c}.count{font-size:32px;font-weight:750;margin-top:8px}.cols{display:grid;grid-template-columns:1fr 1fr;gap:18px}section{margin-bottom:18px}.row{display:flex;justify-content:space-between;border-bottom:1px solid #e7ecf4;padding:11px 0;gap:10px}.row:last-child{border:0}.scroll{overflow-x:auto}table{border-collapse:collapse;width:100%;white-space:nowrap}td,th{text-align:left;padding:12px;border-bottom:1px solid #e7ecf4}th{color:#65748c;font-size:13px}a.primary{display:inline-block;background:#173d79;color:white;text-decoration:none;padding:12px 18px;border-radius:8px}button{background:transparent;border:1px solid #d5ddeb;border-radius:7px;padding:8px 12px;cursor:pointer}@media(max-width:720px){.cols{grid-template-columns:1fr}}
</style></head><body><header><strong>OH ONE · Overseas Highway</strong><a href="/leads.php">Lead Management</a></header><main><h1><?= $founder?'Founder Dashboard':'Office Dashboard' ?></h1><p class="muted">Signed in as <?=e($u['role'])?> · Live database overview</p>
<div class="cards"><div class="card"><div>Offices</div><div class="count"><?=$offices?></div></div><div class="card"><div>Total Leads</div><div class="count"><?=$leads?></div></div><div class="card"><div>New Leads</div><div class="count"><?=$statuses['new']?></div></div><div class="card"><div>Converted</div><div class="count"><?=$statuses['converted']?></div></div></div>
<p><a class="primary" href="/leads.php">Manage Leads &amp; Offices →</a> <a class="primary" href="/workflow.php">Assignments, Follow-ups &amp; Tasks →</a> <?php if($founder):?><a class="primary" href="/staff.php">Manage Staff →</a><?php endif;?></p>
<div class="cols"><section><h2>Lead Pipeline</h2><?php foreach($statuses as $status=>$count):?><div class="row"><span><?=e(ucfirst($status))?></span><strong><?=$count?></strong></div><?php endforeach;?></section><section><h2>Office Performance</h2><?php foreach($officeRows as $r):?><div class="row"><span><?=e($r['name'])?></span><strong><?=(int)$r['total']?> leads</strong></div><?php endforeach;?><?php if(!$officeRows):?><p class="muted">No offices yet. Add one in Lead Management.</p><?php endif;?></section></div>
<section><h2>Recent Leads</h2><div class="scroll"><table><thead><tr><th>Name</th><th>Office</th><th>Service</th><th>Status</th><th>Created</th></tr></thead><tbody><?php foreach($recent as $r):?><tr><td><?=e($r['full_name'])?></td><td><?=e($r['office_name'])?></td><td><?=e($r['service_interest'])?></td><td><?=e(ucfirst($r['status']))?></td><td><?=e($r['created_at'])?></td></tr><?php endforeach;?><?php if(!$recent):?><tr><td colspan="5">No leads yet.</td></tr><?php endif;?></tbody></table></div></section>
<form method="post" action="/logout.php"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><button type="submit">Sign out</button></form></main></body></html>
