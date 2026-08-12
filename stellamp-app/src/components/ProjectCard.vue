<script setup>
import { computed } from 'vue'

const props = defineProps({
  no: { type: Number, default: 0 },
  name: { type: String, required: true },
  desc: { type: String, required: true },
  tag: { type: String, required: true },
  url: { type: String, required: true },
  team: { type: String, default: '' },
  teamName: { type: String, default: '' },
  teamUrl: { type: String, default: '' }
})

const noLabel = computed(() => String(props.no).padStart(2, '0'))

function openProject() {
  const u = props.url.startsWith('http://') || props.url.startsWith('https://')
    ? props.url
    : 'https://' + props.url
  window.open(u, '_blank', 'noopener')
}
</script>

<template>
  <article
    class="project-card"
    role="link"
    tabindex="0"
    @click="openProject"
    @keydown.enter="openProject"
  >
    <span class="c-no">{{ noLabel }}</span>
    <div class="c-body">
      <div class="c-head">
        <h3 class="c-name">{{ name }}</h3>
        <span class="c-tag">{{ tag }}</span>
      </div>
      <p class="c-desc">{{ desc }}</p>
      <div class="c-foot">
        <span class="c-url">{{ url }}</span>
        <a
          v-if="teamUrl"
          class="c-team-site"
          :href="teamUrl"
          target="_blank"
          rel="noopener"
          @click.stop
        >{{ teamName ? teamName : '团队' }} ↗</a>
      </div>
    </div>
    <span class="c-go" aria-hidden="true">↗</span>
  </article>
</template>

<style scoped>
/* 瑞士式编号横排条目：去边框，靠发丝线 + 编号建立秩序 */
.project-card {
  display: grid;
  grid-template-columns: 48px 1fr auto;
  gap: 0 28px;
  align-items: start;
  padding: 26px 16px;
  border-top: 1px solid var(--hairline);
  cursor: pointer;
}
.project-card:last-child {
  border-bottom: 1px solid var(--hairline);
}
.project-card:focus-visible {
  outline: 1px solid var(--accent);
  outline-offset: 3px;
}
.c-no {
  font-family: var(--font-sans);
  font-weight: 700;
  font-size: 13px;
  letter-spacing: 0.08em;
  color: var(--text-4);
  padding-top: 4px;
  transition: color 0.24s var(--ease-out);
}
.project-card:hover .c-no { color: var(--accent); }
.c-body { min-width: 0; }
.c-head {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  gap: 16px;
}
.c-name {
  font-size: 20px;
  font-weight: 700;
  letter-spacing: -0.01em;
  font-family: var(--font-sans);
  color: var(--text-1);
  margin: 0;
  transition: color 0.24s var(--ease-out);
}
.project-card:hover .c-name { color: var(--accent); }
.c-tag {
  font-size: 13px;
  color: var(--accent);
  font-family: var(--font-sans);
  white-space: nowrap;
  margin: 0;
}
.c-desc {
  font-size: 15px;
  line-height: 24px;
  color: var(--text-3);
  margin: 10px 0 14px;
  max-width: 720px;
}
.c-foot {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}
.c-url {
  font-size: 14px;
  color: var(--text-4);
  font-family: var(--font-sans);
  margin: 0;
  transition: color 0.24s var(--ease-out);
}
.project-card:hover .c-url { color: var(--accent); }
.c-team-site {
  font-size: 12px;
  color: var(--accent);
  font-family: var(--font-sans);
  text-decoration: none;
  padding: 3px 10px;
  border: 1px solid var(--hairline);
  border-radius: var(--r-pill);
  transition: border-color 0.24s var(--ease-out), color 0.24s var(--ease-out);
  white-space: nowrap;
}
.c-team-site:hover {
  border-color: var(--accent);
  color: var(--accent-press);
}
.c-go {
  font-size: 18px;
  line-height: 1;
  color: var(--accent);
  opacity: 0;
  transform: translateX(-4px);
  transition: opacity 0.24s var(--ease-out), transform 0.24s var(--ease-out);
  padding-top: 4px;
}
.project-card:hover .c-go { opacity: 1; transform: translateX(0); }

@media (max-width: 768px) {
  .project-card {
    grid-template-columns: 36px 1fr;
    gap: 0 18px;
  }
  .c-go { display: none; }
  .c-head { flex-direction: column; align-items: flex-start; gap: 4px; }
}
</style>
