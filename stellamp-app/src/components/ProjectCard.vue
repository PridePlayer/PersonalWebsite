<script setup>
defineProps({
  name: { type: String, required: true },
  desc: { type: String, required: true },
  tag: { type: String, required: true },
  url: { type: String, required: true }
})
</script>

<template>
  <article class="card project-card">
    <h3 class="c-name">{{ name }}</h3>
    <p class="c-desc">{{ desc }}</p>
    <p class="c-tag">{{ tag }}</p>
    <p class="c-url">{{ url }}</p>
  </article>
</template>

<style scoped>
.project-card { display: flex; flex-direction: column; }
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
.c-url {
  font-size: 14px;
  color: var(--text-4);
  font-family: var(--font-sans);
  margin: 0;
}
</style>
