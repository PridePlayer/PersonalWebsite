<script setup>
import { onMounted } from 'vue'
import { usePosts } from '../data/useContent'
import BlogPostRow from '../components/BlogPostRow.vue'

const { posts, load } = usePosts()
onMounted(load)
</script>

<template>
  <div class="blog-page">
    <!-- 返回条（含底部发丝线） -->
    <header class="page-backbar">
      <div class="site back-inner">
        <router-link to="/" class="back-link">← 返回 Stellamp</router-link>
      </div>
    </header>

    <div class="site blog-body">
      <div class="blog-title">
        <p class="kicker">BLOG · 写作与记录</p>
        <h1 class="h-page">博客</h1>
        <p class="sub blog-desc">平时写的一些东西——项目复盘、设计随笔、折腾记录。不定时更新，想到什么写什么。</p>
      </div>

      <div class="blog-list">
        <BlogPostRow
          v-for="p in posts"
          :key="p.slug"
          variant="full"
          :meta="p.meta"
          :title="p.title"
          :excerpt="p.excerpt"
          :slug="p.slug"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
.blog-title {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 60px 0 20px;
}
.blog-desc { max-width: 900px; margin: 0; }
.blog-list {
  display: flex;
  flex-direction: column;
  gap: 0;
  max-width: var(--content);
  padding: 20px 0 90px;
}
</style>
