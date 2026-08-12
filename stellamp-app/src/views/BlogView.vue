<script setup>
import { onMounted } from 'vue'
import { usePosts } from '../data/useContent'
import BlogPostRow from '../components/BlogPostRow.vue'
import Breadcrumb from '../components/Breadcrumb.vue'

const { posts, load } = usePosts()
onMounted(load)
</script>

<template>
  <div class="blog-page">
    <Breadcrumb current="博客" />

    <section class="section site">
      <div class="sec-index">
        <span class="sec-no">01</span>
        <span class="sec-tag">博客</span>
      </div>
      <div class="sec-main">
        <p class="sub blog-desc">平时写的一些东西——项目复盘、设计随笔、折腾记录。不定时更新，想到什么写什么。</p>
        <div class="blog-list">
          <BlogPostRow
            v-reveal="i + 1"
            v-for="(p, i) in posts"
            :key="p.slug"
            variant="full"
            :index="i + 1"
            :meta="p.meta"
            :title="p.title"
            :excerpt="p.excerpt"
            :slug="p.slug"
          />
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.blog-desc { max-width: 760px; margin: 0 0 8px; }
.blog-list {
  display: flex;
  flex-direction: column;
  gap: 0;
  padding: 20px 0 90px;
}
</style>
