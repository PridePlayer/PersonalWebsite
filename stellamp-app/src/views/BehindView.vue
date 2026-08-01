<script setup>
import { behind } from '../data/content'
</script>

<template>
  <div class="behind-view">
    <header class="page-backbar">
      <div class="site back-inner">
        <router-link to="/" class="back-link">← 返回 Stellamp</router-link>
      </div>
    </header>
    <div class="site behind-body">
      <div class="behind-title">
        <p class="kicker">幕后 · 现实生活经历</p>
        <h1 class="h-page">幕后</h1>
        <p class="lead">
          这些是现实里的经历——学校、任职、模联、兴趣与荣誉。仅对愿意多了解我的人开放。
        </p>
      </div>

    <!-- 教育经历 -->
    <section class="block">
      <h2 class="h-section">教育经历</h2>
      <ul class="resume">
        <li v-for="(e, i) in behind.education" :key="i" class="resume-row">
          <span class="r-title">{{ e.title }}</span>
          <span class="r-meta">{{ e.meta }}</span>
        </li>
      </ul>
    </section>

    <!-- 任职经历 -->
    <section class="block">
      <h2 class="h-section">任职经历</h2>
      <ul class="resume">
        <li v-for="(r, i) in behind.roles" :key="i" class="resume-row resume-role">
          <div class="r-head">
            <span class="r-title">{{ r.title }}</span>
            <span class="r-meta">{{ r.meta }}</span>
          </div>
          <p v-if="r.desc" class="r-desc">{{ r.desc }}</p>
        </li>
      </ul>
    </section>

    <!-- 模拟联合国 -->
    <section class="block">
      <h2 class="h-section">模拟联合国</h2>
      <ul class="resume">
        <li v-for="(m, i) in behind.mun" :key="i" class="resume-row">
          <span class="r-title">{{ m.title }}</span>
          <span class="r-meta">{{ m.meta }}</span>
        </li>
      </ul>
    </section>

    <!-- 技能与兴趣 -->
    <section class="block">
      <h2 class="h-section">技能与兴趣</h2>
      <div class="skills">
        <div v-for="(s, i) in behind.skills" :key="i" class="skill card">
          <p class="s-label">{{ s.label }}</p>
          <p class="s-content">{{ s.content }}</p>
        </div>
      </div>
    </section>

    <!-- 荣誉 -->
    <section class="block block-last">
      <h2 class="h-section">荣誉</h2>
      <ul class="honors">
        <li v-for="(h, i) in behind.honors" :key="i" class="honor card">
          <span class="h-award">{{ h.award }}</span>
          <span class="h-meta">{{ h.meta }}</span>
        </li>
      </ul>
    </section>
    </div>
  </div>
</template>

<style scoped>
.behind-body { padding-bottom: 100px; }
.behind-title {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 60px 0 20px;
}
.behind-title .lead { max-width: 640px; margin: 0; }

.block { margin-bottom: 56px; max-width: var(--content); }
.block-last { margin-bottom: 0; }

.resume { list-style: none; margin: 0; padding: 0; }
.resume-row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 24px;
  padding: 16px 0;
  border-bottom: 1px solid var(--hairline);
}
.resume-role { flex-direction: column; align-items: stretch; gap: 6px; }
.r-head { display: flex; align-items: baseline; justify-content: space-between; gap: 24px; }
.r-title {
  font-size: 17px;
  color: var(--text-1);
  font-family: var(--font-sans);
}
.r-meta {
  font-size: 14px;
  color: var(--text-4);
  font-family: var(--font-sans);
  white-space: nowrap;
}
.r-desc {
  font-size: 15px;
  line-height: 25px;
  color: var(--text-3);
  margin: 4px 0 0;
}

.skills {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}
.skill { padding: 20px 22px; }
.s-label {
  font-size: 14px;
  color: var(--accent);
  font-family: var(--font-sans);
  margin: 0 0 8px;
}
.s-content {
  font-size: 15px;
  line-height: 24px;
  color: var(--text-2);
  margin: 0;
}

.honors {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.honor {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 16px 20px;
}
.h-award {
  font-size: 16px;
  color: var(--text-1);
  font-family: var(--font-sans);
}
.h-meta {
  font-size: 13px;
  color: var(--text-4);
  font-family: var(--font-sans);
}

@media (max-width: 768px) {
  .skills { grid-template-columns: 1fr; }
  .honors { grid-template-columns: 1fr; }
  .resume-row { flex-direction: column; gap: 4px; }
  .r-head { flex-direction: column; gap: 4px; }
}
</style>
