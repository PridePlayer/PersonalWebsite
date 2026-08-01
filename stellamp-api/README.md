# Stellamp 博客后台（PHP）

一个零依赖的轻量 PHP 后台，用于管理 Markdown 博客文章：上传 `.md` → 自动解析 frontmatter 与正文 → 提供给前端 Vue 应用消费。

## 目录结构

```
stellamp-api/
├── index.php        # 前端控制器：API 路由 + 后台页面
├── config.php       # 配置（文章目录、后台密码）
├── lib/markdown.php # 自写 Markdown + frontmatter 解析器（无外部依赖）
├── .htaccess        # Apache 重写（美观 URL）
└── posts/           # .md 文章存放处（部署时保证可写）
    ├── starte.md
    ├── ue-wiki.md
    └── ...
```

## 部署步骤

1. **上传到服务器**：把整个 `stellamp-api/` 放到站点下某个目录，例如 `https://stellamp.me/api/`。
2. **改密码**：打开 `config.php`，把 `admin_password` 改成你自己的强密码。
3. **保证可写**：让 `posts/` 目录对 PHP 进程可写（`chmod 755` 或按主机面板设写权限）。
4. **（Apache）重写**：`.htaccess` 已内置；如果用 Nginx，加一条：
   ```nginx
   location /api/ {
     try_files $uri $uri/ /api/index.php?$args;
   }
   ```
   若不想配重写，也可直接用 `?r=posts` 这种 query 形式（前端 API 地址相应设置即可）。
5. **（HTTPS）强烈建议**：后台涉及密码，请务必在 HTTPS 下访问。

## 写文章

每篇文章是一个 `.md` 文件，顶部用 frontmatter 写元信息：

```markdown
---
title: 文章标题
date: 2026.02          # 列表里的「日期 · 标签」会用到
tags: 技术              # 主标签
excerpt: 一句话摘要，也会作为博客列表里的简介。
lead: 开篇导语（可选，不写则取正文第一段）。
---

正文用标准 Markdown：## 小标题、> 引用、**粗体**、行内 `code`、

```js
代码块（围栏 ``` 包裹）
```

![配图说明](https://你的图床/xxx.png)
```

- `date` 写成 `2026.02` 或 `2026-02-14` 都能被识别并格式化。
- 文章 `slug` = 文件名（不含 `.md`），例如 `starte.md` → `https://stellamp.me/blog/starte`。

## 发布方式

登录后台：直接访问 `https://你的站/api/`（目录首页即后台），或 `https://你的站/api/admin`，或显式 `https://你的站/api/?r=admin`。

1. 输入后台密码登录。
2. 选一个 `.md` 文件上传，立即发布。
3. 前端刷新即可看到新文章（前端通过 `?r=posts` 列表、`?r=posts/<slug>` 单篇 实时获取）。

## 前端如何连上后台

在 Vue 项目 `stellamp-app/` 里创建 `.env`（参考 `.env.example`）：

```
VITE_API_BASE=/api
```

然后重新 `npm run build` 并部署 `dist/`。前端会改为从 `/posts` 等接口拉取文章（请求形如 `/api/posts`、`/api/posts/<slug>`，由后台重写映射到 `/posts` 路由）；
**不设置 `VITE_API_BASE` 时使用内置静态文章**，本地预览开箱即用。

## 安全提示

- 当前后台是「单密码 + session」的极简方案，适合个人低频使用。
- 务必改默认密码、务必用 HTTPS。
- 若担心，可加：登录失败限流、文章审核、或把 `posts/` 移到 web 根目录之外。
- 想用更成熟的 Markdown 引擎，可把 `lib/markdown.php` 换成 [Parsedown](https://github.com/erusev/parsedown)（API 输出结构保持不变即可）。
