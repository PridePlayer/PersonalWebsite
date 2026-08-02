<script setup>
import { computed, onMounted } from 'vue'
import { projects } from '../data/content'
import { usePosts } from '../data/useContent'
import ProjectCard from '../components/ProjectCard.vue'
import BlogPostRow from '../components/BlogPostRow.vue'

const { posts, loading, usingFallback, apiError, apiMode, load } = usePosts()
onMounted(load)

// 首页博客区只放最新三篇（mini 样式），其余引导去 /blog
const teaserPosts = computed(() => posts.value.slice(0, 3))
</script>

<template>
  <div class="home-view">
  <!-- Hero：全宽背景，内容居中 -->
  <section class="hero">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="hero-inner site">
      <p class="kicker">STELLAMP.ME — 线上创作集</p>
      <h1 class="hero-name">Stellamp</h1>
      <p class="hero-handle">@prideplayer</p>
      <span class="rule"></span>
      <p class="hero-identity">独立开发者 · 维基与同人宇宙的搭建者</p>
      <p class="hero-statement">
        在网络上写代码、做网站、搭百科——把喜欢的电影宇宙和深夜灵感，变成别人也能走进去的小世界。这里收录我这些年的线上作品与折腾。
      </p>
    </div>
  </section>

  <!-- 01 关于 -->
  <section id="about" class="section site about">
    <p class="kicker">01 — 关于</p>
    <h2 class="h-page">关于</h2>
    <span class="title-rule"></span>
    <p class="body about-p">
      Stellamp 是我的网络身份，也是这个博客的名字。我在网络上写代码、做网站、搭百科，把喜欢的电影宇宙和零散的灵感，慢慢攒成别人也能走进去的小世界。
    </p>
    <p class="body about-p">
      这里收的，是我这些年做过的线上作品——星空壁纸软件、流浪地球系列百科与同人站、歌词画报工具，其中有独立完成的，也有和团队一起做的。现实里的我另有轨迹，但那一部分，我把它收进了幕后（一个需要答对几个小问题才能进入的页面）。比起把履历摊开，我更想先让你看见作品本身。
    </p>
  </section>

  <!-- 02 作品 -->
  <section id="projects" class="section site projects">
    <p class="kicker">02 — 作品 · 线上创作</p>
    <h2 class="h-page">线上作品</h2>
    <span class="title-rule"></span>
    <p class="sub projects-intro">下面是这几年我做过的线上项目——工具、百科与同人站。观星记 Starte 与 Lyrics Share 是与 zestela 团队共同开发的；United Earth Wiki 与 United Earth Government 则是我和 United Earth Team（UET）联合制作的。</p>
    <div class="projects-grid">
      <ProjectCard
        v-for="p in projects"
        :key="p.name"
        :name="p.name"
        :desc="p.desc"
        :tag="p.tag"
        :url="p.url"
        :team="p.team"
        :team-name="p.teamName"
        :team-url="p.teamUrl"
      />
    </div>
  </section>

  <!-- 03 博客 -->
  <section id="blog" class="section site blog">
    <p class="kicker">03 — 博客</p>
    <h2 class="h-page">博客</h2>
    <span class="title-rule"></span>
    <p class="sub blog-desc">平时写的一些东西——项目复盘、设计随笔、折腾记录。下面挑了几篇最近的。</p>
    <p v-if="apiMode && usingFallback" class="api-warn">{{ apiError }}</p>
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
  position: relative;
  overflow: hidden;
  width: 100%;
  padding-top: 120px;
  padding-bottom: 104px;
}
.hero-inner {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  gap: 20px;
}
/* 极淡星空渐变 + 噪点背景（仅 Hero） */
.hero-bg {
  position: absolute;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background:
    radial-gradient(1.2px 1.2px at 18% 28%, rgba(236,233,240,0.55), transparent 60%),
    radial-gradient(1px 1px at 72% 18%, rgba(236,233,240,0.45), transparent 60%),
    radial-gradient(1.4px 1.4px at 38% 62%, rgba(236,233,240,0.40), transparent 60%),
    radial-gradient(1px 1px at 84% 52%, rgba(236,233,240,0.35), transparent 60%),
    radial-gradient(1px 1px at 54% 40%, rgba(236,233,240,0.30), transparent 60%),
    radial-gradient(1.2px 1.2px at 28% 80%, rgba(236,233,240,0.30), transparent 60%),
    /* 雾紫环境光：横贯整幅宽度的线性渐变，顶部左右都铺到，向下淡出 */
    linear-gradient(180deg, rgba(138,123,176,0.13) 0%, rgba(138,123,176,0.04) 38%, transparent 72%);
}
.hero-bg::after {
  content: "";
  position: absolute;
  inset: 0;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.6'/%3E%3C/svg%3E");
  opacity: 0.035;
  mix-blend-mode: screen;
}
.hero-name {
  font-family: var(--font-serif);
  font-weight: 600;
  font-size: 46px;
  line-height: 1.08;
  letter-spacing: -0.01em;
  color: var(--text-1);
  margin: 0;
  animation: titleIn 0.3s ease both;
}
.hero-handle {
  font-size: 16px;
  color: var(--text-3);
  font-family: var(--font-sans);
  margin: 0;
}
.hero-identity {
  font-size: 16px;
  font-weight: 500;
  color: var(--text-2);
  font-family: var(--font-sans);
  margin: 0;
}
.hero-statement {
  font-size: 16px;
  line-height: 29px;
  color: var(--text-3);
  max-width: 660px;
  margin: 0;
}
/* 标题下更轻的发丝分隔 */
.title-rule {
  display: block;
  width: 100%;
  max-width: var(--content);
  height: 1px;
  background: var(--hairline);
  opacity: 0.55;
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

/* 后台接口未连通的提示（仅在部署版且接口失败时显示） */
.api-warn {
  font-size: 13px;
  line-height: 22px;
  color: #E2A0A0;
  background: rgba(226, 160, 160, 0.08);
  border: 1px solid rgba(226, 160, 160, 0.25);
  border-radius: 8px;
  padding: 10px 14px;
  max-width: var(--content);
  margin: 14px 0 0;
}

/* 区块标题进场微位移淡入 */
.h-page { animation: titleIn 0.3s ease both; }

@media (prefers-reduced-motion: reduce) {
  .hero-name, .h-page { animation: none; }
}

@media (max-width: 768px) {
  .hero { padding-top: 80px; padding-bottom: 68px; }
  .hero-name { font-size: 38px; }
  .projects-grid { grid-template-columns: 1fr; }
}
@media (max-width: 480px) {
  .hero-name { font-size: 32px; }
}
</style>
