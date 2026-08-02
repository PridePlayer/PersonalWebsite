// 内容来源：优先从 PHP 后台 API 读取，未配置时回退到静态 content.js
// 配置方式：在 .env 中设置 VITE_API_BASE（如 /api 或 https://api.stellamp.me）
// 不设置则使用内置静态文章，保证本地预览开箱即用。
//
// 注意：前端统一用「查询串」形式请求（/api/index.php?r=posts），
// 这样不需要服务器配置任何 URL 重写（.htaccess / Nginx try_files）也能工作。
import { ref } from 'vue'
import { blogPosts as staticPosts, articles as staticArticles } from './content'

const API_BASE = import.meta.env.VITE_API_BASE || ''
const API_MODE = !!API_BASE

// 统一拼接口地址：/api/index.php?r=posts  /api/index.php?r=posts/<slug>
function apiUrl(path) {
  return `${API_BASE}/index.php?r=${path}`
}

function normalizeList(item) {
  return {
    slug: item.slug,
    meta: item.meta,
    title: item.title,
    excerpt: item.excerpt
  }
}

function normalizeBlocks(blocks) {
  if (!Array.isArray(blocks)) return []
  return blocks.map((b) => {
    if (b.type === 'quote') {
      return { type: 'quote', text: b.text, attr: b.attr ?? null }
    }
    if (b.type === 'code') {
      const code = Array.isArray(b.code) ? b.code : String(b.code).split('\n')
      return { type: 'code', lang: b.lang || '', code }
    }
    if (b.type === 'image') {
      return { type: 'image', label: b.label, caption: b.caption, src: b.src || '' }
    }
    return { type: b.type, text: b.text }
  })
}

export function usePosts() {
  const posts = ref(API_MODE ? [] : staticPosts.map(normalizeList))
  const loading = ref(false)
  const usingFallback = ref(!API_MODE)
  const apiError = ref('')

  async function load() {
    if (!API_MODE) {
      posts.value = staticPosts.map(normalizeList)
      usingFallback.value = true
      return
    }
    loading.value = true
    apiError.value = ''
    try {
      const r = await fetch(apiUrl('posts'))
      if (!r.ok) throw new Error('HTTP ' + r.status)
      const data = await r.json()
      posts.value = data.map(normalizeList)
      usingFallback.value = false
    } catch (e) {
      // 接口不通时仍展示内置示例文章，但明确标注「非实时」，让用户知道后台没连上
      posts.value = staticPosts.map(normalizeList)
      usingFallback.value = true
      apiError.value = '后台接口未连通，当前显示的是内置示例文章（请检查服务器 /api 是否可访问）'
    } finally {
      loading.value = false
    }
  }

  return { posts, loading, usingFallback, apiError, apiMode: API_MODE, load }
}

export function useArticle() {
  const article = ref(API_MODE ? null : staticArticles)
  const loading = ref(false)
  const notFound = ref(false)
  const usingFallback = ref(!API_MODE)
  const apiError = ref('')

  async function load(slug) {
    if (!API_MODE) {
      const a = staticArticles[slug] || null
      article.value = a ? { ...a, blocks: normalizeBlocks(a.blocks) } : null
      notFound.value = !article.value
      usingFallback.value = true
      return
    }
    loading.value = true
    notFound.value = false
    apiError.value = ''
    try {
      const r = await fetch(apiUrl('posts/' + slug))
      if (r.status === 404) {
        article.value = null
        notFound.value = true
        loading.value = false
        return
      }
      if (!r.ok) throw new Error('HTTP ' + r.status)
      const data = await r.json()
      article.value = { ...data, blocks: normalizeBlocks(data.blocks) }
      usingFallback.value = false
    } catch (e) {
      const a = staticArticles[slug] || null
      article.value = a ? { ...a, blocks: normalizeBlocks(a.blocks) } : null
      notFound.value = !article.value
      usingFallback.value = true
      apiError.value = '后台接口未连通，当前显示的是内置示例文章（请检查服务器 /api 是否可访问）'
    } finally {
      loading.value = false
    }
  }

  return { article, loading, notFound, usingFallback, apiError, apiMode: API_MODE, load }
}
