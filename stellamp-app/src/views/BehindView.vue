<script setup>
import { behind } from '../data/content'
import Breadcrumb from '../components/Breadcrumb.vue'
</script>

<template>
  <div class="behind-view">
    <Breadcrumb current="幕后" />

    <section class="section site behind-head" v-reveal>
      <div class="sec-index">
        <span class="sec-no">00</span>
        <span class="sec-tag">幕后</span>
      </div>
      <div class="sec-main">
        <p class="lead behind-lead">这些是现实里的经历——学校、任职、模联、兴趣与荣誉。仅对愿意多了解我的人开放。</p>
      </div>
    </section>

    <!-- 教育经历 -->
    <section class="section site block compact" v-reveal>
      <div class="sec-index">
        <span class="sec-no">01</span>
        <span class="sec-tag">教育经历</span>
      </div>
      <div class="sec-main">
        <ul class="resume">
          <li v-for="(e, i) in behind.education" :key="i" class="resume-row">
            <span class="r-title">{{ e.title }}</span>
            <span class="r-meta">{{ e.meta }}</span>
          </li>
        </ul>
      </div>
    </section>

    <!-- 任职经历 -->
    <section class="section site block compact" v-reveal>
      <div class="sec-index">
        <span class="sec-no">02</span>
        <span class="sec-tag">任职经历</span>
      </div>
      <div class="sec-main">
        <ul class="resume">
          <li v-for="(r, i) in behind.roles" :key="i" class="resume-row resume-role">
            <div class="r-head">
              <span class="r-title">{{ r.title }}</span>
              <span class="r-meta">{{ r.meta }}</span>
            </div>
            <p v-if="r.desc" class="r-desc">{{ r.desc }}</p>
          </li>
        </ul>
      </div>
    </section>

    <!-- 模拟联合国 -->
    <section class="section site block compact" v-reveal>
      <div class="sec-index">
        <span class="sec-no">03</span>
        <span class="sec-tag">模拟联合国</span>
      </div>
      <div class="sec-main">
        <ul class="resume">
          <li v-for="(m, i) in behind.mun" :key="i" class="resume-row">
            <span class="r-title">{{ m.title }}</span>
            <span class="r-meta">{{ m.meta }}</span>
          </li>
        </ul>
      </div>
    </section>

    <!-- 技能与兴趣 -->
    <section class="section site block compact" v-reveal>
      <div class="sec-index">
        <span class="sec-no">04</span>
        <span class="sec-tag">技能与兴趣</span>
      </div>
      <div class="sec-main">
        <div class="skills">
          <div v-for="(s, i) in behind.skills" :key="i" class="skill card">
            <p class="s-label">{{ s.label }}</p>
            <p class="s-content">{{ s.content }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- 荣誉 -->
    <section class="section site block compact block-last">
      <div class="sec-index">
        <span class="sec-no">05</span>
        <span class="sec-tag">荣誉</span>
      </div>
      <div class="sec-main">
        <ul class="honors">
          <li v-for="(h, i) in behind.honors" :key="i" class="honor card">
            <span class="h-award">{{ h.award }}</span>
            <span class="h-meta">{{ h.meta }}</span>
          </li>
        </ul>
      </div>
    </section>
  </div>
</template>

<style scoped>
.behind-lead { max-width: 640px; margin: 0; }

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
