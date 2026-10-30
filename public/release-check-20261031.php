<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Robots-Tag: noindex, nofollow');
$login = __DIR__ . '/login.php';
$source = is_file($login) ? file_get_contents($login) : '';
$version = is_string($source) && str_contains($source, 'OH ONE DESIGN VERSION: 2026-10-31');
?><!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>OH ONE Release Check</title><style>body{background:#0a111a;color:#fff;font:16px system-ui;margin:0;display:grid;min-height:100vh;place-items:center}.card{max-width:580px;margin:20px;padding:36px;border:1px solid #cba458;border-radius:18px;background:#151e29}h1{color:#edbf62}a{color:#edbf62}</style><div class="card"><h1>OH ONE · Release Check</h1><p>Release: <strong>2026-10-31 · Enterprise Black & Gold</strong></p><p>Landing source version: <strong><?= $version ? 'NEW VERSION PRESENT' : 'OLDER VERSION PRESENT' ?></strong></p><p><a href="/login.php?v=20261031">Open landing page (fresh URL)</a></p></div></html>