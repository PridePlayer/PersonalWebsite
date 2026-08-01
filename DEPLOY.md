# 部署说明（Stellamp）

本仓库有两种部署方式，任选其一。两种方式用的都是已经构建好的 `deploy/` 目录——它就是服务器「网站根目录」该有的样子，上传后**不需要再改任何目录结构**。

## 部署包结构（deploy/）

```
deploy/
├── index.html        ← 前端首页（Vue 打包产物）
├── assets/           ← 前端 JS / CSS
└── api/              ← PHP 博客后台
    ├── index.php
    ├── config.php    ← 部署后请改 admin_password
    ├── lib/markdown.php
    ├── .htaccess     ← Apache 重写（Nginx 见下）
    └── posts/        ← 你的 .md 文章（已含 5 篇示例）
```

> 接口已写死为 `/api`，所以 `deploy/` 放到网站根目录后，前端会自动从 `/api/posts` 拉文章。

---

## 方式一：直接打包上传（最快）

1. 把 `deploy/` 整个目录压缩成 zip（已为你生成 `stellamp-deploy.zip`，其中已包含隐藏文件 `.htaccess`）。
2. 在主机面板 / FTP 把 zip 解压到**网站根目录**（通常是 `public_html/` 或 `www/` 或 `htdocs/`），让 `index.html` 和 `api/` 都直接位于根目录下。
3. 打开服务器上的 `api/config.php`，把 `admin_password` 改成你自己的强密码。
4. 给 `api/posts/` 目录设写权限（`chmod 755` 或面板里点「写」）。
5. 强烈建议用 HTTPS 访问后台。

完成。打开你的域名就能看到站点；后台在 `你的域名/api/`。

---

## 方式二：GitHub + 服务器 git clone

1. 在 GitHub 新建仓库，把**整个仓库**（含 `deploy/`）推上去。
   ```bash
   git init            # 若尚未初始化
   git add .
   git commit -m "Stellamp site"
   git remote add origin <你的仓库地址>
   git push -u origin main
   ```
2. 在服务器上：
   ```bash
   git clone <你的仓库地址> stellamp
   ```
3. 把 `deploy/` 的内容放进网站根目录（二选一）：
   - 复制：`cp -r stellamp/deploy/. /path/to/public_html/`
   - 或将主机面板的「网站根目录 / 文档根」直接指向 `stellamp/deploy/`（最省事，零复制）。
4. 同样改 `deploy/api/config.php` 的密码、给 `deploy/api/posts/` 设写权限。

之后更新文章只需要在后台传 `.md`，不用再动仓库或重新部署。

---

## Nginx 额外配置（Apache 自带 .htaccess，无需此步）

在 server 块加一条，让 `/api/` 下的请求落到 PHP：

```nginx
location /api/ {
  try_files $uri $uri/ /api/index.php?$args;
}
```

---

## 以后怎么发文章（两种方式都通用）

1. 浏览器打开 `https://你的域名/api/` → 输密码登录。
2. 选一个 `.md` 文件上传 → 立即发布。
3. 回到网站刷新，新文章就出现在博客列表。

`.md` 顶部用 frontmatter 写元信息，下面是正文：

```markdown
---
title: 文章标题
date: 2026.08
tags: 随笔
excerpt: 一句话摘要。
lead: 开篇导语（可选）。
---

## 小标题
正文……
```
