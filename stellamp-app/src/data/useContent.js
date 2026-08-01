// 内容来源：优先从 PHP 后台 API 读取，未配置时回退到静态 content.js
// 配置方式：在 .env 中设置 VITE_API_BASE（如 /api 或 https://api.stellamp.me）
// 不设置则使用内置静态文章，保证本地预览开箱即用。
import { ref } from 'vue'
import { blogPosts as staticPosts, articles as staticArticles } from './content'

const API_BASE = import.meta.env.VITE_API_BASE || ''

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
  const posts = ref(API_BASE ? [] : staticPosts.map(normalizeList))
  const loading = ref(false)
  const usingFallback = ref(!API_BASE)

  async function load() {
    if (!API_BASE) {
      posts.value = staticPosts.map(normalizeList)
      usingFallback.value = true
      return
    }
    loading.value = true
    try {
      const r = await fetch(`${API_BASE}/posts`)
      if (!r.ok) throw new Error('bad status')
      const data = await r.json()
      posts.value = data.map(normalizeList)
      usingFallback.value = false
    } catch (e) {
      posts.value = staticPosts.map(normalizeList)
      usingFallback.value = true
    } finally {
      loading.value = false
    }
  }

  return { posts, loading, usingFallback, load }
}

export function useArticle() {
  const article = ref(API_BASE ? null : staticArticles)
  const loading = ref(false)
  const notFound = ref(false)
  const usingFallback = ref(!API_BASE)

  async function load(slug) {
    if (!API_BASE) {
      const a = staticArticles[slug] || null
      article.value = a ? { ...a, blocks: normalizeBlocks(a.blocks) } : null
      notFound.value = !article.value
      usingFallback.value = true
      return
    }
    loading.value = true
    notFound.value = false
    try {
      const r = await fetch(`${API_BASE}/posts/${slug}`)
      if (r.status === 404) {
        article.value = null
        notFound.value = true
        loading.value = false
        return
      }
      if (!r.ok) throw new Error('bad status')
      const data = await r.json()
      article.value = { ...data, blocks: normalizeBlocks(data.blocks) }
      usingFallback.value = false
    } catch (e) {
      const a = staticArticles[slug] || null
      article.value = a ? { ...a, blocks: normalizeBlocks(a.blocks) } : null
      notFound.value = !article.value
      usingFallback.value = true
    } finally {
      loading.value = false
    }
  }

  return { article, loading, notFound, usingFallback, load }
}
