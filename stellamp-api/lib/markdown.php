<?php
// 轻量 Markdown -> 结构化 block 解析器（无外部依赖）
// 输出格式与前端 ArticleView 期望的 blocks 一致：
//   {type:'h2'|'p'|'quote'|'code'|'image', ...}
// 同时解析 YAML 风格 frontmatter（title / date / tags / excerpt / lead）。

if (!function_exists('stellamp_parse_frontmatter')) {
    /**
     * 解析 frontmatter 与正文
     * @return array [meta:array, body:string]
     */
    function stellamp_parse_frontmatter(string $raw): array
    {
        $meta = [];
        $body = $raw;
        if (preg_match('/^---\s*\n(.*?)\n---\s*\n/su', $raw, $m)) {
            $fm = $m[1];
            $body = substr($raw, strlen($m[0]));
            foreach (explode("\n", $fm) as $line) {
                if (preg_match('/^([A-Za-z_]+):\s*(.*)$/u', $line, $mm)) {
                    $meta[strtolower($mm[1])] = trim($mm[2], " \"'\t");
                }
            }
        }
        return [$meta, $body];
    }

    /**
     * 行内 Markdown -> 纯文本（前端按纯文本渲染，去掉标记符号）
     */
    function stellamp_inline(string $text): string
    {
        // 行内代码 `x` -> x
        $text = preg_replace('/`([^`]+)`/u', '$1', $text);
        // 图片 ![alt](url) -> alt
        $text = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/u', '$1', $text);
        // 链接 [t](u) -> t
        $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/u', '$1', $text);
        // 粗体 **x** / __x__ -> x
        $text = preg_replace('/\*\*(.+?)\*\*/u', '$1', $text);
        $text = preg_replace('/__(.+?)__/u', '$1', $text);
        // 斜体 *x* / _x_ -> x
        $text = preg_replace('/\*(.+?)\*/u', '$1', $text);
        $text = preg_replace('/_(.+?)_/u', '$1', $text);
        return trim($text);
    }

    /**
     * 正文 -> blocks 数组
     */
    function stellamp_parse_blocks(string $body): array
    {
        $lines = preg_split('/\r\n|\n/u', $body);
        $lines = $lines === false ? [] : $lines;
        $n = count($lines);
        $blocks = [];
        $i = 0;

        while ($i < $n) {
            $raw = $lines[$i];
            $trim = trim($raw);

            if ($trim === '') { $i++; continue; }

            // 围栏代码块 ```
            if (preg_match('/^```(.*)$/u', $trim, $m)) {
                $lang = trim($m[1]);
                $code = [];
                $i++;
                while ($i < $n && trim($lines[$i]) !== '```') {
                    $code[] = $lines[$i];
                    $i++;
                }
                $i++; // 跳过结束 ```
                $blocks[] = ['type' => 'code', 'lang' => $lang, 'code' => implode("\n", $code)];
                continue;
            }

            // 标题 #..###### -> h2（设计稿只用单级子标题）
            if (preg_match('/^#{1,6}\s+(.*)$/u', $trim, $m)) {
                $blocks[] = ['type' => 'h2', 'text' => stellamp_inline(trim($m[1]))];
                $i++;
                continue;
            }

            // 引用 >
            if (strpos($trim, '>') === 0) {
                $q = [];
                while ($i < $n && strpos(trim($lines[$i]), '>') === 0) {
                    $q[] = ltrim(ltrim($lines[$i]), '>');
                    $i++;
                }
                // 最后一行以 — 开头视为署名
                $attr = null;
                $last = trim(end($q));
                if ($last !== '' && preg_match('/^[—–-]+/u', $last)) {
                    $attr = ltrim($last, '—–- ');
                    array_pop($q);
                }
                $blocks[] = [
                    'type' => 'quote',
                    'text' => stellamp_inline(implode("\n", $q)),
                    'attr' => $attr,
                ];
                continue;
            }

            // 独立图片 ![alt](url)
            if (preg_match('/^!\[([^\]]*)\]\(([^)]+)\)\s*$/u', $trim, $m)) {
                $blocks[] = ['type' => 'image', 'label' => $m[1], 'caption' => $m[1], 'src' => $m[2]];
                $i++;
                continue;
            }

            // 段落：收集到空行或块起点
            $para = [];
            while ($i < $n && trim($lines[$i]) !== ''
                   && !preg_match('/^```/u', trim($lines[$i]))
                   && !preg_match('/^#{1,6}\s+/u', trim($lines[$i]))
                   && strpos(trim($lines[$i]), '>') !== 0
                   && !preg_match('/^!\[/u', trim($lines[$i]))) {
                $para[] = $lines[$i];
                $i++;
            }
            $blocks[] = ['type' => 'p', 'text' => stellamp_inline(implode("\n", $para))];
        }

        return $blocks;
    }

    /**
     * 把短日期格式化成中文
     * 2026.02 -> 2026 年 2 月；2026-02-14 -> 2026 年 2 月 14 日
     */
    function stellamp_format_date(string $date): string
    {
        if (preg_match('/^(\d{4})\.(\d{1,2})$/u', $date, $m)) {
            return $m[1] . ' 年 ' . intval($m[2]) . ' 月';
        }
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/u', $date, $m)) {
            return $m[1] . ' 年 ' . intval($m[2]) . ' 月 ' . intval($m[3]) . ' 日';
        }
        return $date;
    }

    /**
     * 估算阅读时长（中文约 300 字/分钟）
     */
    function stellamp_read_time(string $text): string
    {
        $chars = mb_strlen(preg_replace('/\s+/u', '', $text));
        $min = max(1, intval(ceil($chars / 300)));
        return '约 ' . $min . ' 分钟阅读';
    }

    /**
     * 读取单篇文章，返回前端需要的完整结构
     */
    function stellamp_load_post(string $slug, string $postsDir): ?array
    {
        $file = $postsDir . '/' . $slug . '.md';
        if (!is_file($file)) return null;

        $raw = @file_get_contents($file);
        if ($raw === false) return null;

        [$meta, $body] = stellamp_parse_frontmatter($raw);
        $blocks = stellamp_parse_blocks($body);

        $tags = $meta['tags'] ?? '随笔';
        $date = $meta['date'] ?? '';
        $meta_line = ($date !== '' ? $date : '') . ' · ' . $tags;

        // 摘要 / 导语
        $excerpt = $meta['excerpt'] ?? '';
        $lead = $meta['lead'] ?? '';
        if ($excerpt === '' || $lead === '') {
            foreach ($blocks as $b) {
                if ($b['type'] === 'p' && $excerpt === '') { $excerpt = $b['text']; }
                if ($b['type'] === 'p' && $lead === '') { $lead = $b['text']; break; }
            }
        }

        return [
            'slug'      => $slug,
            'kicker'    => $meta_line,
            'title'     => $meta['title'] ?? $slug,
            'dek'       => $excerpt,
            'date'      => stellamp_format_date($date),
            'readTime'  => stellamp_read_time($body),
            'lead'      => $lead,
            'cover'     => $meta['cover'] ?? null,
            'blocks'    => $blocks,
        ];
    }

    /**
     * 列出所有文章（用于博客列表 / 首页）
     */
    function stellamp_list_posts(string $postsDir): array
    {
        if (!is_dir($postsDir)) return [];
        $out = [];
        foreach (glob($postsDir . '/*.md') as $file) {
            $slug = basename($file, '.md');
            $raw = @file_get_contents($file);
            if ($raw === false) continue;
            [$meta, $body] = stellamp_parse_frontmatter($raw);
            $tags = $meta['tags'] ?? '随笔';
            $date = $meta['date'] ?? '';
            $excerpt = $meta['excerpt'] ?? '';
            if ($excerpt === '') {
                $blocks = stellamp_parse_blocks($body);
                foreach ($blocks as $b) {
                    if ($b['type'] === 'p') { $excerpt = $b['text']; break; }
                }
            }
            $out[] = [
                'slug'    => $slug,
                'date'    => $date,
                'meta'    => ($date !== '' ? $date : '') . ' · ' . $tags,
                'title'   => $meta['title'] ?? $slug,
                'excerpt' => $excerpt,
            ];
        }
        // 按日期倒序（最新在前），日期相同则按 slug 倒序
        usort($out, function ($a, $b) {
            $d = strcmp($b['date'], $a['date']);
            return $d !== 0 ? $d : strcmp($b['slug'], $a['slug']);
        });
        return $out;
    }
}
