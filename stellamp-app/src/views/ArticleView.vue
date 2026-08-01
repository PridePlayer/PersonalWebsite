<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useArticle, usePosts } from '../data/useContent'

const props = defineProps({
  slug: { type: String, required: true }
})
const router = useRouter()

const { article, loading, notFound, load } = useArticle()
const { posts } = usePosts()

onMounted(() => load(props.slug))

const neighbors = computed(() => {
  const i = posts.value.findIndex((p) => p.slug === props.slug)
  return {
    prev: i > 0 ? posts.value[i - 1] : null,
    next: i >= 0 && i < posts.value.length - 1 ? posts.value[i + 1] : null
  }
})

function isComment(line) {
  return line.trim().startsWith('//')
}
function go(slug) {
  router.push(`/blog/${slug}`)
}
</script>

<template>
  <div class="article-shell">
  <div v-if="loading" class="site loading">加载中…</div>

  <div v-else-if="article" class="article-view">
    <!-- 返回条（含底部发丝线） -->
    <header class="page-backbar">
      <div class="site back-inner">
        <router-link to="/blog" class="back-link">← 返回博客</router-link>
      </div>
    </header>

    <article class="article-col">
      <div class="art-head">
        <p class="kicker">{{ article.kicker }}</p>
        <h1 class="art-title">{{ article.title }}</h1>
        <p class="art-dek">{{ article.dek }}</p>
        <p class="art-meta">{{ article.date }} · {{ article.readTime }}</p>
        <span class="rule"></span>
      </div>

      <figure v-if="article.cover !== undefined" class="art-cover">
        <img v-if="article.cover" :src="article.cover" :alt="article.title" class="art-cover-img" />
        <span v-else class="art-cover-label">{{ article.title }}</span>
      </figure>

      <p class="art-lead">{{ article.lead }}</p>

      <div class="blocks">
        <template v-for="(b, i) in article.blocks" :key="i">
          <h2 v-if="b.type === 'h2'" class="b-h2">{{ b.text }}</h2>
          <p v-else-if="b.type === 'p'" class="b-p">{{ b.text }}</p>
          <blockquote v-else-if="b.type === 'quote'" class="b-quote">
            <p class="b-quote-text">{{ b.text }}</p>
            <cite v-if="b.attr" class="b-quote-attr">{{ b.attr }}</cite>
          </blockquote>
          <template v-else-if="b.type === 'code'">
            <p v-if="b.lang" class="b-code-lang">{{ b.lang }}</p>
            <pre class="b-code"><code><span
              v-for="(line, li) in b.code"
              :key="li"
              :class="isComment(line) ? 'c-comment' : 'c-text'"
            >{{ line }}\n</span></code></pre>
          </template>
          <figure v-else-if="b.type === 'image'" class="b-figure">
            <img v-if="b.src" :src="b.src" :alt="b.label" class="b-image-img" />
            <div v-else class="b-image">
              <span class="b-image-label">{{ b.label }}</span>
            </div>
            <figcaption class="b-caption">{{ b.caption }}</figcaption>
          </figure>
        </template>
      </div>
    </article>

    <nav class="site art-foot">
      <button v-if="neighbors.prev" class="foot-link" @click="go(neighbors.prev.slug)">
        ← {{ neighbors.prev.title }}
      </button>
      <span v-else></span>
      <button v-if="neighbors.next" class="foot-link foot-right" @click="go(neighbors.next.slug)">
        {{ neighbors.next.title }} →
      </button>
    </nav>
  </div>

  <div v-else class="site missing">
    <router-link to="/blog" class="back-link">← 返回博客</router-link>
    <h1 class="h-page">{{ notFound ? '没有这篇文章' : '加载失败' }}</h1>
    <p class="sub">它可能还没写完，或者链接已经失效了。</p>
  </div>
  </div>
</template>

<style scoped>
.article-shell { display: block; }
.article-view { padding-bottom: 80px; }
.loading { padding-top: 96px; padding-bottom: 140px; color: var(--text-4); font-size: 15px; }
.back-link {
  display: inline-block;
  color: var(--backlink);
  font-size: 14px;
  font-family: var(--font-sans);
}
.back-link:hover { color: var(--text-2); }

.article-col {
  max-width: var(--art-col);
  margin: 0 auto;
}
.art-head {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding-top: 52px;
}
.art-title {
  font-family: var(--font-serif);
  font-weight: 600;
  font-size: 36px;
  line-height: 1.22;
  letter-spacing: -0.01em;
  color: var(--text-1);
  margin: 0;
}
.art-dek {
  font-size: 18px;
  line-height: 29px;
  color: var(--text-2);
  margin: 0;
}
.art-meta {
  font-size: 14px;
  color: var(--text-4);
  font-family: var(--font-sans);
  margin: 0;
}
.art-lead {
  font-size: 18px;
  line-height: 31px;
  color: var(--text-2);
  margin: 32px 0 8px;
}

.art-cover {
  margin: 40px 0 0;
  width: 100%;
  aspect-ratio: 700 / 380;
  background: var(--placeholder);
  border: 1px solid var(--hairline);
  border-radius: var(--r-4);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
.art-cover-img { width: 100%; height: 100%; object-fit: cover; display: block; }
.art-cover-label { color: var(--text-4); font-size: 14px; font-family: var(--font-sans); }

.blocks { margin-top: 16px; }
.b-h2 {
  font-family: var(--font-serif);
  font-weight: 600;
  font-size: 22px;
  line-height: 1.35;
  color: var(--text-1);
  margin: 36px 0 14px;
}
.b-p {
  font-size: 17px;
  line-height: 30px;
  color: var(--text-2);
  margin: 0 0 22px;
}
.b-quote {
  margin: 28px 0;
  padding: 6px 0 6px 22px;
  border-left: 3px solid var(--accent);
}
.b-quote-text {
  font-family: var(--font-serif);
  font-size: 19px;
  line-height: 30px;
  color: var(--text-1);
  margin: 0 0 8px;
  font-style: normal;
}
.b-quote-attr {
  font-size: 14px;
  color: var(--text-4);
  font-style: normal;
  font-family: var(--font-sans);
}
.b-code-lang {
  font-size: 12px;
  color: var(--text-4);
  font-family: var(--font-sans);
  margin: 24px 0 -8px;
  letter-spacing: 0.03em;
}
.b-code {
  background: var(--surface-code);
  border: 1px solid var(--hairline);
  border-radius: var(--r-4);
  padding: 20px 22px;
  overflow-x: auto;
  margin: 16px 0 24px;
  font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
  font-size: 14px;
  line-height: 24px;
}
.b-code code { white-space: pre; }
.c-comment { color: var(--code-comment); }
.c-text { color: var(--code-text); }
.b-figure { margin: 28px 0; }
.b-image,
.b-image-img {
  width: 100%;
  border: 1px solid var(--hairline);
  border-radius: var(--r-8);
}
.b-image {
  aspect-ratio: 16 / 9;
  background: var(--placeholder);
  display: flex;
  align-items: center;
  justify-content: center;
}
.b-image-label {
  color: var(--text-4);
  font-size: 14px;
  font-family: var(--font-sans);
}
.b-image-img { display: block; }
.b-caption {
  font-size: 13px;
  color: var(--text-4);
  margin-top: 10px;
  font-family: var(--font-sans);
}

.art-foot {
  display: flex;
  justify-content: space-between;
  gap: 20px;
  margin-top: 56px;
  padding-top: 28px;
  padding-bottom: 50px;
  border-top: 1px solid var(--hairline);
}
.foot-link {
  background: none;
  border: none;
  color: var(--accent);
  font-size: 15px;
  font-family: var(--font-sans);
  text-align: left;
  padding: 0;
}
.foot-link:hover { color: var(--accent-press); }
.foot-right { text-align: right; }
.missing { padding-top: 80px; padding-bottom: 120px; }
.missing .sub { margin-top: 14px; }

@media (max-width: 768px) {
  .art-title { font-size: 28px; }
  .article-col { padding: 0 24px; }
  .art-head { padding-top: 40px; }
  .art-foot { padding-left: 24px; padding-right: 24px; }
}
</style>
