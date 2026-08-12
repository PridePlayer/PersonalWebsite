<script setup>
import { useRoute, useRouter } from 'vue-router'
import { useGate } from '../composables/useGate'

const route = useRoute()
const router = useRouter()
const { openModal } = useGate()

// 关于 / 作品 是首页内的分区：在首页则平滑滚动，在子页则先回首页再滚。
function goSection(id) {
  if (route.path === '/') {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' })
  } else {
    router.push('/').then(() => {
      setTimeout(() => document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' }), 320)
    })
  }
}

function goBehind() {
  // 点「幕后」：打开隐私验证弹窗（通过后由 App 跳转到 /behind）
  openModal()
}
</script>

<template>
  <header class="topbar">
    <div class="topbar-inner site">
      <router-link to="/" class="brand">Stellamp</router-link>
      <nav class="menu">
        <button class="link-btn" @click="goSection('about')">关于</button>
        <button class="link-btn" @click="goSection('projects')">作品</button>
        <router-link to="/blog" class="link-btn">博客</router-link>
        <button class="gate-link" @click="goBehind">幕后</button>
      </nav>
    </div>
  </header>
</template>

<style scoped>
.topbar {
  position: sticky;
  top: 0;
  z-index: 50;
  height: 72px;
  background: rgba(255, 255, 255, 0.82);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid var(--hairline);
}
.topbar-inner {
  height: 72px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.brand {
  font-family: var(--font-serif);
  font-weight: 700;
  font-size: 20px;
  letter-spacing: -0.01em;
  color: var(--text-1);
}
.brand:hover { color: var(--text-1); }
.menu {
  display: flex;
  gap: 32px;
  align-items: center;
}
.menu a,
.link-btn {
  position: relative;
  color: var(--text-3);
  font-size: 15px;
  font-family: var(--font-sans);
  letter-spacing: 0.02em;
  background: none;
  /* 彻底重置 <button> 原生外观，否则上/左/右会露出浏览器默认黑框 */
  appearance: none;
  -webkit-appearance: none;
  border: 0;
  border-bottom: 2px solid transparent;
  padding: 0 0 4px;
  line-height: inherit;
  cursor: pointer;
}
.menu a:hover,
.link-btn:hover { color: var(--text-1); }
.menu a.router-link-exact-active { color: var(--text-1); border-bottom: 2px solid var(--accent); }
.gate-link {
  position: relative;
  color: var(--text-3);
  font-size: 15px;
  font-family: var(--font-sans);
  letter-spacing: 0.02em;
  background: none;
  appearance: none;
  -webkit-appearance: none;
  border: 0;
  border-bottom: 2px solid transparent;
  padding: 0 0 4px;
  line-height: inherit;
}
.gate-link:hover { color: var(--text-1); }
/* 导航下划线微交互：hover 时由左展开，极轻、不喧宾夺主 */
.menu a::after,
.link-btn::after,
.gate-link::after {
  content: '';
  position: absolute;
  left: 0;
  right: 0;
  bottom: -1px;
  height: 1px;
  background: var(--accent);
  transform: scaleX(0);
  transform-origin: left center;
  transition: transform 0.24s var(--ease-out);
}
.menu a:hover::after,
.link-btn:hover::after,
.gate-link:hover::after {
  transform: scaleX(1);
}
@media (max-width: 480px) {
  .menu { gap: 18px; }
  .menu a, .link-btn, .gate-link { font-size: 14px; }
}
</style>
