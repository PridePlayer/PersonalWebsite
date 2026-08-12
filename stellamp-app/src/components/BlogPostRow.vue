<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  meta: { type: String, required: true },
  title: { type: String, required: true },
  excerpt: { type: String, required: true },
  slug: { type: String, required: true },
  variant: { type: String, default: 'full' },
  index: { type: Number, default: 0 }
})
const router = useRouter()
const noLabel = computed(() => String(props.index).padStart(2, '0'))
function open() {
  router.push('/blog/' + props.slug)
}
</script>

<template>
  <article class="post" :class="variant" @click="open">
    <span class="p-no">{{ noLabel }}</span>
    <div class="p-body">
      <p class="p-meta">{{ meta }}</p>
      <h3 class="p-title">{{ title }}</h3>
      <p class="p-excerpt">{{ excerpt }}</p>
      <span class="p-more">阅读 →</span>
    </div>
  </article>
</template>

<style scoped>
.post {
  cursor: pointer;
  display: grid;
  grid-template-columns: 40px 1fr;
  gap: 24px;
  transition: opacity 0.15s ease;
}
.post:hover { opacity: 0.82; }
.post:hover .p-title { color: var(--accent); }

.post.mini { padding: 22px 0; }
.post.full { padding: 28px 0; }

.p-no {
  font-family: var(--font-sans);
  font-weight: 700;
  font-size: 13px;
  letter-spacing: 0.08em;
  color: var(--text-4);
  padding-top: 4px;
}
.p-body {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 0;
}
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
