<script setup>
defineProps({
  current: { type: String, required: true }
})
</script>

<template>
  <nav class="crumb site" aria-label="面包屑">
    <router-link to="/" class="c-home">首页</router-link>
    <span class="c-sep">/</span>
    <span class="c-cur" aria-current="page">{{ current }}</span>
  </nav>
</template>

<style scoped>
.crumb {
  display: flex;
  align-items: center;
  gap: 10px;
  padding-top: 28px;
  padding-bottom: 4px;
  font-family: var(--font-sans);
  font-size: 13px;
  letter-spacing: 0.02em;
}
.c-home {
  color: var(--text-3);
}
.c-home:hover { color: var(--text-1); }
.c-sep {
  color: var(--text-4);
}
.c-cur {
  color: var(--accent);
  font-weight: 500;
}
@media (max-width: 768px) {
  .crumb { padding-top: 22px; }
}
</style>
