<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/security.php';
$u=require_login();
$db=require dirname(__DIR__) . '/app/db.php';
if ($u['role'] === 'founder') {
    $offices=(int)$db->query('SELECT COUNT(*) FROM offices')->fetchColumn();
    $leads=(int)$db->query('SELECT COUNT(*) FROM leads')->fetchColumn();
} else {
    $stmt=$db->prepare('SELECT COUNT(*) FROM offices WHERE id = ?');
    $stmt->execute([(int)$u['office_id']]); $offices=(int)$stmt->fetchColumn();
    $stmt=$db->prepare('SELECT COUNT(*) FROM leads WHERE office_id = ?');
    $stmt->execute([(int)$u['office_id']]); $leads=(int)$stmt->fetchColumn();
}
header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>OH ONE Dashboard</title><style>body{font:16px system-ui;background:#f3f6fb;color:#17253b;margin:0}header{background:#102a53;color:white;padding:20px 5%}main{max-width:950px;margin:40px auto;padding:0 20px}.cards{display:flex;gap:18px;flex-wrap:wrap}.card{background:white;padding:25px;border-radius:14px;min-width:180px;box-shadow:0 4px 20px #14213d12}.count{font-size:36px;font-weight:750}button{padding:10px 20px;cursor:pointer}</style></head><body><header><strong>OH ONE</strong> · Overseas Highway CRM</header><main><h1>Dashboard</h1><p>Welcome. Signed in as <?=e($u['role'])?>.</p><div class="cards"><div class="card"><div>Offices</div><div class="count"><?=$offices?></div></div><div class="card"><div>Leads</div><div class="count"><?=$leads?></div></div></div><form method="post" action="/logout.php"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><p><button>Sign out</button></p></form></main></body></html>
