<script setup>
import { useRouter } from 'vue-router'

const props = defineProps({
  meta: { type: String, required: true },
  title: { type: String, required: true },
  excerpt: { type: String, required: true },
  slug: { type: String, required: true },
  // mini = 首页三篇预览；full = 博客列表页
  variant: { type: String, default: 'full' }
})
const router = useRouter()
function open() {
  router.push(`/blog/${props.slug}`)
}
</script>

<template>
  <article class="post" :class="variant" @click="open">
    <p class="p-meta">{{ meta }}</p>
    <h3 class="p-title">{{ title }}</h3>
    <p class="p-excerpt">{{ excerpt }}</p>
    <span class="p-more">阅读 →</span>
  </article>
</template>

<style scoped>
/* 设计稿：博客条目无背景、无边框，仅纵向留白 */
.post {
  cursor: pointer;
  display: flex;
  flex-direction: column;
  transition: opacity 0.15s ease;
}
.post:hover { opacity: 0.82; }
.post:hover .p-title { color: var(--accent); }

.post.mini { padding: 22px 0; gap: 6px; }
.post.full { padding: 28px 0; gap: 8px; }

.p-meta {
  font-size: 13px;
  color: var(--text-4);
  font-family: var(--font-sans);
  margin: 0;
}
.p-title {
  font-family: var(--font-serif);
  font-weight: 500;
  line-height: 1.3;
  color: var(--text-1);
  margin: 0;
  transition: color 0.15s ease;
}
.post.mini .p-title { font-size: 18px; }
.post.full .p-title { font-size: 22px; }

.p-excerpt {
  font-size: 15px;
  line-height: 24px;
  color: var(--text-3);
  margin: 0;
  max-width: 760px;
}
.p-more {
  font-size: 14px;
  font-weight: 500;
  color: var(--accent);
  font-family: var(--font-sans);
  margin: 0;
}
</style>
