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

if ($path === '/admin/upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_authed()) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => '未登录']);
        exit;
    }
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        send_json(['ok' => false, 'error' => '没有收到文件'], 400);
    }
    $name = $_FILES['file']['name'];
    // 仅允许 .md，并做安全化文件名
    if (!preg_match('/\.md$/i', $name)) {
        send_json(['ok' => false, 'error' => '只接受 .md 文件'], 400);
    }
    $slug = preg_replace('/[^A-Za-z0-9\-_]/', '-', pathinfo($name, PATHINFO_FILENAME));
    $slug = strtolower(trim($slug, '-'));
    if ($slug === '') $slug = 'post-' . time();
    $dest = $CONFIG['posts_dir'] . '/' . $slug . '.md';
    if (!move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
        send_json(['ok' => false, 'error' => '保存失败'], 500);
    }
    send_json(['ok' => true, 'slug' => $slug]);
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
    if (!$authed) {
        return <<<HTML
<!doctype html><html lang="zh-CN"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>后台登录 · {$title}</title>
<style>body{font-family:system-ui,sans-serif;background:#1B1820;color:#ECE9F0;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0}
.box{background:#262230;border:1px solid #322C3B;border-radius:12px;padding:32px 36px;width:340px}
h1{font-size:20px;margin:0 0 18px;font-weight:600}
input,button{width:100%;padding:11px 12px;border-radius:6px;border:1px solid #322C3B;background:#1B1820;color:#ECE9F0;font-size:14px;box-sizing:border-box}
button{background:#8A7BB0;border:none;color:#F4F1F8;font-weight:500;margin-top:12px;cursor:pointer}
.msg{color:#E2A0A0;font-size:13px;margin-top:10px;min-height:16px}</style></head>
<body><div class="box">
<h1>幕后 · 博客后台</h1>
<form id="f" method="post" action="?r=admin/login">
<input type="password" name="password" placeholder="后台密码" autofocus>
<button type="submit">登录</button>
</form><div class="msg" id="msg"></div>
<script>
document.getElementById('f').addEventListener('submit',async e=>{
  e.preventDefault();const fd=new FormData(e.target);
  const r=await fetch('?r=admin/login',{method:'POST',body:fd});const j=await r.json();
  if(j.ok){location.href='?r=admin';}else{document.getElementById('msg').textContent=j.error||'登录失败';}
});
</script></div></body></html>
HTML;
    }

    return <<<HTML
<!doctype html><html lang="zh-CN"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>博客后台 · {$title}</title>
<style>body{font-family:system-ui,sans-serif;background:#1B1820;color:#ECE9F0;margin:0;padding:40px}
.wrap{max-width:640px;margin:0 auto}
h1{font-size:22px;margin:0 0 6px}
p.sub{color:#9A93A8;font-size:14px;margin:0 0 24px}
.card{background:#262230;border:1px solid #322C3B;border-radius:12px;padding:24px;margin-bottom:20px}
label{display:block;font-size:13px;color:#B7AEC8;margin-bottom:8px}
input[type=file]{color:#ECE9F0;font-size:14px}
button{background:#8A7BB0;border:none;color:#F4F1F8;font-weight:500;padding:11px 18px;border-radius:6px;cursor:pointer;font-size:14px;margin-top:14px}
button.ghost{background:transparent;border:1px solid #322C3B;color:#B7AEC8}
.msg{font-size:13px;margin-top:12px;min-height:16px}
.msg.ok{color:#8FB7AE}.msg.err{color:#E2A0A0}
pre{background:#232320;border:1px solid #322C3B;border-radius:8px;padding:14px;overflow:auto;font-size:12px;color:#E6E2D8}
code{font-family:Consolas,Menlo,monospace}
a{color:#8A7BB0}</style></head>
<body><div class="wrap">
<h1>博客后台</h1>
<p class="sub">上传 .md 即发布。文件顶部用 frontmatter 写标题/日期/标签。</p>

<div class="card">
<label>上传文章（.md）</label>
<input type="file" id="file" accept=".md,text/markdown">
<button id="up">上传并发布</button>
<div class="msg" id="msg"></div>
</div>

<div class="card">
<label>frontmatter 示例</label>
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

<div class="card">
<button class="ghost" id="logout">退出登录</button>
<p class="sub" style="margin-top:14px">接口：<code>?r=posts</code> 列表 · <code>?r=posts/&lt;slug&gt;</code> 单篇</p>
</div>

<script>
const msg=document.getElementById('msg');
document.getElementById('up').addEventListener('click',async()=>{
  const f=document.getElementById('file').files[0];
  if(!f){msg.className='msg err';msg.textContent='请先选择 .md 文件';return;}
  const fd=new FormData();fd.append('file',f);
  msg.className='msg';msg.textContent='上传中…';
  const r=await fetch('?r=admin/upload',{method:'POST',body:fd});
  const j=await r.json();
  if(j.ok){msg.className='msg ok';msg.textContent='已发布，slug: '+j.slug;}
  else{msg.className='msg err';msg.textContent=j.error||'上传失败';}
});
document.getElementById('logout').addEventListener('click',async()=>{
  await fetch('?r=admin/logout',{method:'POST'});
  location.href='?r=admin';
});
</script>
</div></body></html>
HTML;
}
