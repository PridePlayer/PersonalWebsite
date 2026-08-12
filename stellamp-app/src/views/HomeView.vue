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
  <!-- Hero：全宽浅色封面，左文右引两栏 -->
  <section class="hero">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="hero-inner site">
      <div class="hero-main">
        <p class="kicker">STELLAMP.ME</p>
        <h1 class="hero-name" v-reveal>Stellamp</h1>
        <p class="hero-handle">@prideplayer</p>
        <span class="rule"></span>
        <p class="hero-identity">开发者 · 制作者 · 写作者</p>
        <p class="hero-statement">
          人是寻求意义的动物，每个人都在追求生命的意义，从而人不能被单一定义，所有的兴趣所有的向往构成了人的本身。
        </p>
      </div>
      <aside class="hero-quote" v-reveal>
        <blockquote class="hq-inner">
          <p class="hq-line">我一直要活到我能够</p>
          <p class="hq-line">历数前生，你能够</p>
          <p class="hq-line">与我一同笑看，所以</p>
          <p class="hq-line">死与你我从不相干。</p>
          <cite class="hq-cite">——史铁生</cite>
        </blockquote>
      </aside>
    </div>
  </section>

  <!-- 01 关于 -->
  <section id="about" class="section site about">
    <div class="sec-index">
      <span class="sec-no">01</span>
      <span class="sec-tag">关于</span>
    </div>
    <div class="sec-main">
      <p class="body about-p">
        Stellamp 是这个博客的名字。写代码、做网站、写文章，收集灵感，构建世界。
      </p>
    </div>
  </section>

  <!-- 02 作品 -->
  <section id="projects" class="section site projects">
    <div class="sec-index">
      <span class="sec-no">02</span>
      <span class="sec-tag">作品</span>
    </div>
    <div class="sec-main">
      <p class="sub projects-intro">下面是这几年我做过的线上项目——工具、百科、同人站，以及两款可以亲自走进去玩的线上游戏。观星记 Starte 与 Lyrics Share 是与 zestela 团队共同开发的；United Earth Wiki 与 United Earth Government 则是我和 United Earth Team（UET）联合制作的。</p>
      <div class="projects-grid">
        <ProjectCard
          v-reveal="i + 1"
          v-for="(p, i) in projects"
          :key="p.name"
          :no="i + 1"
          :name="p.name"
          :desc="p.desc"
          :tag="p.tag"
          :url="p.url"
          :team="p.team"
          :team-name="p.teamName"
          :team-url="p.teamUrl"
        />
      </div>
    </div>
  </section>

  <!-- 03 博客 -->
  <section id="blog" class="section site blog">
    <div class="sec-index">
      <span class="sec-no">03</span>
      <span class="sec-tag">博客</span>
    </div>
    <div class="sec-main">
      <p class="sub blog-desc">平时写的一些东西——项目复盘、设计随笔、折腾记录。下面挑了几篇最近的。</p>
      <p v-if="apiMode && usingFallback" class="api-warn">{{ apiError }}</p>
      <div class="blog-list">
        <BlogPostRow
          v-reveal="i + 1"
          v-for="(p, i) in teaserPosts"
          :key="p.slug"
          variant="mini"
          :index="i + 1"
          :meta="p.meta"
          :title="p.title"
          :excerpt="p.excerpt"
          :slug="p.slug"
        />
      </div>
      <router-link to="/blog" class="btn-text more">阅读全部 →</router-link>
    </div>
  </section>
  </div>
</template>

<style scoped>
.home-view { display: block; }
.hero {
  position: relative;
  overflow: hidden;
  width: 100%;
  padding-top: 144px;
  padding-bottom: 120px;
}
.hero-inner {
  position: relative;
  z-index: 1;
  display: grid;
  grid-template-columns: 1.15fr 0.85fr;
  gap: 64px;
  align-items: center;
  text-align: left;
}
.hero-main {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 22px;
}
/* 瑞士国际主义：Hero 扁平纯白，不堆氛围光；背景透明以透出全局竖向规线 */
.hero-bg {
  position: absolute;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background: transparent;
}
.hero-name {
  font-family: var(--font-serif);
  font-weight: 700;
  font-size: clamp(48px, 7vw, 84px);
  line-height: 1.02;
  letter-spacing: -0.02em;
  color: var(--text-1);
  margin: 0;
}
.hero-handle {
  font-size: 16px;
  color: var(--text-3);
  font-family: var(--font-sans);
  margin: 0;
  letter-spacing: 0.02em;
}
.hero-identity {
  font-size: 18px;
  font-weight: 500;
  letter-spacing: 0.14em;
  color: var(--text-2);
  font-family: var(--font-sans);
  margin: 0;
}
.hero-statement {
  font-size: 17px;
  line-height: 31px;
  color: var(--text-3);
  max-width: 600px;
  margin: 0;
}
/* ---------- Hero 右侧 · 史铁生引言（大号衬线次主角） ---------- */
.hero-quote { align-self: center; }
.hq-inner {
  margin: 0;
  text-align: right;
  padding-left: 0;
  padding-right: 28px;
  border-left: none;
  border-right: 3px solid var(--accent);
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.hq-line {
  font-family: var(--font-serif);
  font-weight: 500;
  font-size: clamp(20px, 2.6vw, 28px);
  line-height: 1.45;
  letter-spacing: 0.01em;
  color: var(--text-1);
  margin: 0;
}
.hq-cite {
  font-family: var(--font-serif);
  font-style: normal;
  font-size: 16px;
  letter-spacing: 0.08em;
  color: var(--text-3);
  margin: 8px 0 0;
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
  display: block;
  max-width: var(--content);
  margin-top: 28px;
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
  color: #B4453E;
  background: rgba(180, 69, 62, 0.08);
  border: 1px solid rgba(180, 69, 62, 0.25);
  border-radius: 8px;
  padding: 10px 14px;
  max-width: var(--content);
  margin: 14px 0 0;
}

/* 进场统一交由 .reveal / v-reveal 处理 */

@media (max-width: 768px) {
  .hero { padding-top: 104px; padding-bottom: 88px; }
  .hero-inner { grid-template-columns: 1fr; gap: 44px; align-items: start; }
  .hero-quote { align-self: start; }
  /* 手机端引用恢复左对齐（桌面端为右对齐） */
  .hq-inner {
    text-align: left;
    padding-right: 0;
    padding-left: 28px;
    border-right: none;
    border-left: 3px solid var(--accent);
  }
  .hq-line { font-size: 24px; }
  .projects-grid { grid-template-columns: 1fr; margin-top: 24px; }
}
@media (max-width: 480px) {
  .hero { padding-top: 84px; padding-bottom: 72px; }
}
</style>
