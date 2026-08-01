<script setup>
defineProps({
  name: { type: String, required: true },
  desc: { type: String, required: true },
  tag: { type: String, required: true },
  url: { type: String, required: true },
  team: { type: String, default: '' },
  teamName: { type: String, default: '' },
  teamUrl: { type: String, default: '' }
})
</script>

<template>
  <article class="card project-card">
    <h3 class="c-name">{{ name }}</h3>
    <p class="c-desc">{{ desc }}</p>
    <p class="c-tag">{{ tag }}</p>
    <p v-if="team" class="c-team">{{ team }}</p>
    <a v-if="teamUrl" class="c-team-site" :href="teamUrl" target="_blank" rel="noopener">{{ teamName ? teamName : '团队' }} ↗</a>
    <p class="c-url">{{ url }}</p>
  </article>
</template>

<style scoped>
.project-card {
  display: flex;
  flex-direction: column;
  transition: border-color 0.18s ease;
}
.project-card:hover {
  border-color: var(--accent);
}
.c-name {
  font-size: 18px;
  font-weight: 500;
  font-family: var(--font-sans);
  color: var(--text-1);
  margin: 0 0 10px;
}
.c-desc {
  font-size: 15px;
  line-height: 24px;
  color: var(--text-3);
  margin: 0 0 10px;
}
.c-tag {
  font-size: 13px;
  color: var(--accent);
  font-family: var(--font-sans);
  margin: 0 0 10px;
}
.c-team {
  font-size: 13px;
  color: var(--text-4);
  font-family: var(--font-sans);
  margin: 0 0 6px;
}
.c-team-site {
  align-self: flex-start;
  font-size: 12px;
  color: var(--accent);
  font-family: var(--font-sans);
  text-decoration: none;
  padding: 3px 10px;
  border: 1px solid var(--hairline);
  border-radius: var(--r-pill);
  margin: 0 0 10px;
  transition: border-color 0.15s ease, color 0.15s ease;
}
.c-team-site:hover {
  border-color: var(--accent);
  color: var(--accent-press);
}
.c-url {
  font-size: 14px;
  color: var(--text-4);
  font-family: var(--font-sans);
  margin: 0;
}
</style>
