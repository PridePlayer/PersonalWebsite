<script setup>
import { computed, onMounted } from 'vue'
import { projects } from '../data/content'
import { usePosts } from '../data/useContent'
import ProjectCard from '../components/ProjectCard.vue'
import BlogPostRow from '../components/BlogPostRow.vue'

const { posts, load } = usePosts()
onMounted(load)

// 首页博客区只放最新三篇（mini 样式），其余引导去 /blog
const teaserPosts = computed(() => posts.value.slice(0, 3))
</script>

<template>
  <div class="home-view">
  <!-- Hero -->
  <section class="hero site">
    <p class="kicker">STELLAMP.ME — 线上创作集</p>
    <h1 class="hero-name">Stellamp</h1>
    <p class="hero-handle">@prideplayer</p>
    <span class="rule"></span>
    <p class="hero-identity">独立开发者 · 维基与同人宇宙的搭建者</p>
    <p class="hero-statement">
      在网络上写代码、做网站、搭百科——把喜欢的电影宇宙和深夜灵感，变成别人也能走进去的小世界。这里收录我这些年的线上作品与折腾。
    </p>
  </section>

  <!-- 01 关于 -->
  <section id="about" class="section site about">
    <p class="kicker">01 — 关于</p>
    <h2 class="h-page">关于</h2>
    <p class="body about-p">
      Stellamp 是我的网络身份，也是这个博客的名字。我在网络上写代码、做网站、搭百科，把喜欢的电影宇宙和零散的灵感，慢慢攒成别人也能走进去的小世界。
    </p>
    <p class="body about-p">
      这里收的，是我一个人从零做出来的线上作品——星空壁纸软件、流浪地球系列百科与同人站、歌词画报工具。现实里的我另有轨迹，但那一部分，我把它收进了幕后（一个需要答对几个小问题才能进入的页面）。比起把履历摊开，我更想先让你看见作品本身。
    </p>
  </section>

  <!-- 02 作品 -->
  <section id="projects" class="section site projects">
    <p class="kicker">02 — 作品 · 线上创作</p>
    <h2 class="h-page">线上作品</h2>
    <p class="sub projects-intro">以下是我独立开发与维护的线上项目——从工具软件到电影百科与同人站，都是一个人慢慢打磨出来的。</p>
    <div class="projects-grid">
      <ProjectCard
        v-for="p in projects"
        :key="p.name"
        :name="p.name"
        :desc="p.desc"
        :tag="p.tag"
        :url="p.url"
      />
    </div>
  </section>

  <!-- 03 博客 -->
  <section id="blog" class="section site blog">
    <p class="kicker">03 — 博客</p>
    <h2 class="h-page">博客</h2>
    <p class="sub blog-desc">平时写的一些东西——项目复盘、设计随笔、折腾记录。下面挑了几篇最近的。</p>
    <div class="blog-list">
      <BlogPostRow
        v-for="p in teaserPosts"
        :key="p.slug"
        variant="mini"
        :meta="p.meta"
        :title="p.title"
        :excerpt="p.excerpt"
        :slug="p.slug"
      />
    </div>
    <router-link to="/blog" class="btn-text more">阅读全部 →</router-link>
  </section>
  </div>
</template>

<style scoped>
.home-view { display: block; }
.hero {
  padding-top: 150px;
  padding-bottom: 130px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.hero-name {
  font-family: var(--font-serif);
  font-weight: 600;
  font-size: 66px;
  line-height: 1.05;
  color: var(--text-1);
  margin: 0;
}
.hero-handle {
  font-size: 16px;
  color: var(--text-3);
  font-family: var(--font-sans);
  margin: 0;
}
.hero-identity {
  font-size: 18px;
  font-weight: 500;
  color: var(--text-2);
  font-family: var(--font-sans);
  margin: 0;
}
.hero-statement {
  font-size: 17px;
  line-height: 30px;
  color: var(--text-3);
  max-width: 660px;
  margin: 0;
}
.about-p { max-width: 680px; margin: 0; }

.projects-intro,
.blog-desc { max-width: 760px; margin: 0; }

.projects-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  max-width: var(--content);
  margin-top: 0;
}
.blog-list {
  display: flex;
  flex-direction: column;
  gap: 0;
  max-width: var(--content);
}
.more { align-self: flex-start; font-size: 14px; }

@media (max-width: 768px) {
  .hero { padding-top: 96px; padding-bottom: 80px; }
  .hero-name { font-size: 48px; }
  .projects-grid { grid-template-columns: 1fr; }
}
@media (max-width: 480px) {
  .hero-name { font-size: 40px; }
}
</style>
