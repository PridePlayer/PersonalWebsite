<?php
// Stellamp 博客后台 · 前端控制器
// 路由：
//   GET  /posts          文章列表 (JSON)
//   GET  /posts/<slug>   单篇文章 (JSON)
//   GET  /admin              后台页（登录 + 上传）
//   POST /admin/login        登录
//   POST /admin/upload       上传 .md（需已登录）
//
// 部署：把本目录放到站点下（如 /api/），配置好重写使 /api/xxx 落到 index.php。
// 没有重写也能用：/api/index.php?r=posts 这种形式同样支持。

require_once __DIR__ . '/lib/markdown.php';

$CONFIG = require __DIR__ . '/config.php';
session_start();

// ---------- 路由解析 ----------
function route_path(): string
{
    if (isset($_GET['r']) && $_GET['r'] !== '') {
        return '/' . ltrim($_GET['r'], '/');
    }
    $req = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    // 去掉脚本自身路径，得到相对路由
    if ($script !== '' && strpos($req, $script) === 0) {
        $req = substr($req, strlen($script));
    } else {
        $dir = dirname($script);
        if ($dir !== '/' && $dir !== '' && strpos($req, $dir) === 0) {
            $req = substr($req, strlen($dir));
        }
    }
    // 去掉可能的 /index.php 后缀（PATH_INFO 风格）
    $req = preg_replace('#/index\.php$#', '', $req);
    return $req === '' ? '/' : $req;
}

function send_json($data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function is_authed(): bool
{
    return ($_SESSION['stellamp_authed'] ?? false) === true;
}

// ---------- 接口 ----------
$path = route_path();

// 全局兜底：接口请求若发生未捕获异常/致命错误，返回 JSON 而非裸 PHP 错误页，
// 否则前端会把 HTML 当 JSON 解析而报错并卡在「上传中」。
$is_api = !($path === '/admin' || $path === '/');
set_exception_handler(function ($e) use (&$is_api) {
    if (!$is_api || headers_sent()) return;
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => '服务器错误：' . $e->getMessage()]);
});
register_shutdown_function(function () use (&$is_api) {
    if (!$is_api || headers_sent()) return;
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_COMPILE_ERROR], true)) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => '服务器致命错误：' . $err['message']]);
    }
});

if ($path === '/posts' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    send_json(stellamp_list_posts($CONFIG['posts_dir']));
}

if (preg_match('#^/posts/([\w\-]+)$#u', $path, $m) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $post = stellamp_load_post($m[1], $CONFIG['posts_dir']);
    if ($post === null) send_json(['error' => 'not found'], 404);
    send_json($post);
}

if ($path === '/admin/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw = $_POST['password'] ?? '';
    if (hash_equals((string)$CONFIG['admin_password'], (string)$pw)) {
        $_SESSION['stellamp_authed'] = true;
        send_json(['ok' => true]);
    }
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => '密码错误']);
    exit;
}

if ($path === '/admin/logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    session_destroy();
    send_json(['ok' => true]);
}

// 把 $_FILES['file']（单文件 或 name="file[]" 的多文件）规范成统一数组
function normalize_uploaded_files($raw): array
{
    if ($raw === null || !isset($raw['name'])) return [];
    if (!is_array($raw['name'])) {
        if (($raw['name'] ?? '') === '' || ($raw['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return [];
        return [$raw];
    }
    $out = [];
    foreach ($raw['name'] as $i => $_) {
        $out[] = [
            'name'     => $raw['name'][$i] ?? '',
            'type'     => $raw['type'][$i] ?? '',
            'tmp_name' => $raw['tmp_name'][$i] ?? '',
            'error'    => $raw['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size'     => $raw['size'][$i] ?? 0,
        ];
    }
    return $out;
}

if ($path === '/admin/upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_authed()) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => '未登录']);
        exit;
    }
    $files = normalize_uploaded_files($_FILES['file'] ?? null);
    if (count($files) === 0) {
        send_json(['ok' => false, 'error' => '没有收到文件（可能超过服务器上传大小限制 post_max_size / upload_max_filesize，或多文件数量上限）'], 400);
    }
    $dir = $CONFIG['posts_dir'];
    if (!is_dir($dir) || !is_writable($dir)) {
        send_json(['ok' => false, 'error' => '服务器 posts 目录不可写，请在服务器执行：chmod -R 755 api/posts'], 500);
    }
    $results = [];
    foreach ($files as $f) {
        if (($f['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $results[] = ['name' => $f['name'], 'ok' => false, 'error' => '上传错误（错误码 ' . $f['error'] . '）'];
            continue;
        }
        if (!preg_match('/\.md$/i', $f['name'])) {
            $results[] = ['name' => $f['name'], 'ok' => false, 'error' => '只接受 .md 文件'];
            continue;
        }
        $slug = preg_replace('/[^A-Za-z0-9\-_]/', '-', pathinfo($f['name'], PATHINFO_FILENAME));
        $slug = strtolower(trim($slug, '-'));
        if ($slug === '') $slug = 'post-' . time() . '-' . count($results);
        $dest = $dir . '/' . $slug . '.md';
        if (!@move_uploaded_file($f['tmp_name'], $dest)) {
            $results[] = ['name' => $f['name'], 'ok' => false, 'error' => '保存失败：move_uploaded_file 被拒绝（检查 posts 目录权限与安全策略）'];
            continue;
        }
        $results[] = ['name' => $f['name'], 'ok' => true, 'slug' => $slug];
    }
    $published = 0;
    foreach ($results as $r) { if ($r['ok']) $published++; }
    send_json(['ok' => $published > 0, 'total' => count($results), 'published' => $published, 'results' => $results]);
}

if ($path === '/admin' || $path === '/') {
    echo render_admin($CONFIG, is_authed());
    exit;
}

// 404
http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['error' => 'not found']);
exit;

// ---------- 后台页面 ----------
function render_admin(array $config, bool $authed): string
{
    $title = htmlspecialchars($config['site_title'], ENT_QUOTES, 'UTF-8');
    $font_stack = '"Helvetica Neue", Helvetica, Arial, "Source Han Sans SC", "思源黑体", "Noto Sans SC", system-ui, sans-serif';

    if (!$authed) {
        return <<<HTML
<!doctype html><html lang="zh-CN"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>后台登录 · {$title}</title>
<style>
:root{--bg:#1B1820;--card:#262230;--line:#322C3B;--accent:#8A7BB0;--t1:#ECE9F0;--t3:#9A93A8;--sans:{$font_stack}}
*{box-sizing:border-box}
body{font-family:var(--sans);background:var(--bg);color:var(--t1);display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0}
.box{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:38px 40px;width:360px}
.brand{font-family:var(--sans);font-weight:700;font-size:24px;letter-spacing:.02em;color:var(--t1);margin:0 0 6px}
.kicker{font-size:12px;letter-spacing:.18em;text-transform:uppercase;color:var(--t3);margin:0 0 24px}
h1{font-size:16px;font-weight:500;margin:0 0 18px;color:var(--t1)}
input,button{width:100%;padding:12px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--t1);font-size:14px;font-family:var(--sans)}
input::placeholder{color:var(--t3)}
button{background:var(--accent);border:none;color:#F4F1F8;font-weight:500;margin-top:14px;cursor:pointer;transition:opacity .2s}
button:hover{opacity:.88}
.msg{color:#E2A0A0;font-size:13px;margin-top:12px;min-height:16px}</style></head>
<body><div class="box">
<div class="brand">Stellamp</div>
<p class="kicker">幕后 · 博客后台</p>
<h1>输入密码以进入</h1>
<form id="f" method="post" action="?r=admin/login">
<input type="password" name="password" placeholder="后台密码" autofocus>
<button type="submit">登 录</button>
</form><div class="msg" id="msg"></div>
<script>
document.getElementById('f').addEventListener('submit',async e=>{
  e.preventDefault();
  try{
    const fd=new FormData(e.target);
    const r=await fetch('?r=admin/login',{method:'POST',body:fd});
    const text=await r.text();
    let j; try{ j=JSON.parse(text); }catch(_){ document.getElementById('msg').textContent='服务器返回异常：'+text.slice(0,200); return; }
    if(j.ok){location.href='?r=admin';}else{document.getElementById('msg').textContent=j.error||'登录失败';}
  }catch(err){ document.getElementById('msg').textContent='网络错误：'+err.message; }
});
</script></div></body></html>
HTML;
    }

    return <<<HTML
<!doctype html><html lang="zh-CN"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>博客后台 · {$title}</title>
<style>
:root{--bg:#1B1820;--card:#262230;--line:#322C3B;--accent:#8A7BB0;--t1:#ECE9F0;--t2:#B7AEC8;--t3:#9A93A8;--sans:{$font_stack}}
*{box-sizing:border-box}
body{font-family:var(--sans);background:var(--bg);color:var(--t1);margin:0;line-height:1.6}
.topbar{position:sticky;top:0;z-index:50;height:64px;background:rgba(27,24,32,.82);backdrop-filter:blur(8px);border-bottom:1px solid var(--line);display:flex;align-items:center;padding:0 28px}
.brand{font-family:var(--sans);font-weight:700;font-size:20px;letter-spacing:.02em;color:var(--t1)}
.topbar .tag{margin-left:14px;font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:var(--t3)}
.wrap{max-width:680px;margin:0 auto;padding:48px 28px 64px}
h1{font-family:var(--sans);font-weight:700;font-size:26px;margin:0 0 6px;letter-spacing:.01em}
p.lead{color:var(--t2);font-size:14px;margin:0 0 36px}
.sec{border-top:1px solid var(--line);padding:30px 0}
.no{font-size:12px;letter-spacing:.16em;color:var(--accent);text-transform:uppercase;margin-bottom:16px}
.sec h2{font-family:var(--sans);font-weight:600;font-size:18px;margin:0 0 16px}
.card{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:22px}
label{display:block;font-size:13px;color:var(--t2);margin-bottom:10px}
input[type=file]{display:block;width:100%;color:var(--t1);font-size:14px;font-family:var(--sans);padding:8px 0}
input[type=file]::file-selector-button{font-family:var(--sans);background:var(--bg);color:var(--t1);border:1px solid var(--line);border-radius:8px;padding:9px 14px;margin-right:12px;cursor:pointer}
button{background:var(--accent);border:none;color:#F4F1F8;font-weight:500;padding:12px 22px;border-radius:8px;cursor:pointer;font-size:14px;font-family:var(--sans);transition:opacity .2s}
button:hover{opacity:.88}
button.ghost{background:transparent;border:1px solid var(--line);color:var(--t2)}
.actions{margin-top:18px;display:flex;gap:12px;align-items:center}
.msg{font-size:13px;margin-top:14px;min-height:18px}
.msg.ok{color:#8FB7AE}.msg.err{color:#E2A0A0}
#list{margin-top:16px;display:flex;flex-direction:column;gap:8px}
.row{border:1px solid var(--line);border-radius:8px;padding:10px 14px;font-size:13px;color:var(--t2);background:var(--bg)}
.row.ok{color:#8FB7AE;border-color:#3a4a44}
.row.err{color:#E2A0A0;border-color:#4a3a3a}
pre{background:#211E27;border:1px solid var(--line);border-radius:10px;padding:16px;overflow:auto;font-size:12px;color:#E6E2D8;font-family:Consolas,Menlo,monospace;line-height:1.7}
code{font-family:inherit}
.hint{color:var(--t3);font-size:13px;margin-top:14px}
</style></head>
<body>
<header class="topbar"><div class="brand">Stellamp</div><div class="tag">幕后 · 博客后台</div></header>
<div class="wrap">
<h1>博客后台</h1>
<p class="lead">可一次性上传多篇 .md 文章，立即发布。文件顶部用 frontmatter 写标题 / 日期 / 标签。</p>

<section class="sec">
<div class="no">01 — 上传</div>
<div class="card">
<label>选择 .md 文章（可多选）</label>
<input type="file" id="file" name="file[]" accept=".md,text/markdown" multiple>
<div id="list"></div>
<div class="actions">
<button id="up">上传并发布</button>
</div>
<div class="msg" id="msg"></div>
</div>
</section>

<section class="sec">
<div class="no">02 — frontmatter 示例</div>
<div class="card">
<pre><code>---
title: 文章标题
date: 2026.02
tags: 技术
excerpt: 一句话摘要，也会作为列表里的简介。
lead: 开篇导语（可选，不写则取正文首段）。
---

正文用标准 Markdown 写：## 小标题、> 引用、**粗体**、
```js
代码块
```
![配图说明](https://...)
</code></pre>
</div>
</section>

<section class="sec">
<div class="no">03 — 退出</div>
<div class="card">
<button class="ghost" id="logout">退出登录</button>
<p class="hint">接口：<code>?r=posts</code> 列表 · <code>?r=posts/&lt;slug&gt;</code> 单篇</p>
</div>
</section>

<script>
const input=document.getElementById('file');
const msg=document.getElementById('msg');
const list=document.getElementById('list');
function renderList(){
  list.innerHTML='';
  const files=input.files;
  if(files.length===0) return;
  for(const f of files){
    const row=document.createElement('div'); row.className='row';
    row.textContent=f.name+'  ·  '+(f.size/1024).toFixed(1)+' KB';
    list.appendChild(row);
  }
}
input.addEventListener('change',renderList);
document.getElementById('up').addEventListener('click',async()=>{
  const files=input.files;
  if(files.length===0){msg.className='msg err';msg.textContent='请先选择至少一个 .md 文件';return;}
  const fd=new FormData();
  for(const f of files){ fd.append('file[]', f); }
  msg.className='msg';msg.textContent='上传中…（'+files.length+' 个文件）';
  try{
    const r=await fetch('?r=admin/upload',{method:'POST',body:fd});
    const text=await r.text();
    let j; try{ j=JSON.parse(text); }catch(_){ msg.className='msg err'; msg.textContent='服务器返回非 JSON：'+text.slice(0,300); return; }
    if(j.results){
      msg.className=(j.published>0)?'msg ok':'msg err';
      msg.textContent='已发布 '+j.published+' / '+j.total+' 篇';
      list.innerHTML='';
      j.results.forEach(function(res){
        const row=document.createElement('div');
        row.className='row '+(res.ok?'ok':'err');
        row.textContent=(res.ok?'✓ ':'✗ ')+res.name+(res.ok?(' → '+res.slug):(' ：'+res.error));
        list.appendChild(row);
      });
    } else if(j.ok){ msg.className='msg ok'; msg.textContent='已发布，slug: '+(j.slug||''); }
    else { msg.className='msg err'; msg.textContent=j.error||('上传失败（HTTP '+r.status+'）'); }
  }catch(err){ msg.className='msg err'; msg.textContent='网络错误：'+err.message; }
});
document.getElementById('logout').addEventListener('click',async()=>{
  try{ await fetch('?r=admin/logout',{method:'POST'}); }catch(_){}
  location.href='?r=admin';
});
</script>
</div></body></html>
HTML;
}
