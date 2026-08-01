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
        <router-link to="/blog">博客</router-link>
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
  background: rgba(27, 24, 32, 0.85);
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
  font-weight: 600;
  font-size: 20px;
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
  color: var(--text-3);
  font-size: 15px;
  font-family: var(--font-sans);
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
}
.menu a:hover,
.link-btn:hover { color: var(--text-1); }
.menu a.router-link-exact-active { color: var(--text-1); }
.gate-link {
  color: var(--text-3);
  font-size: 15px;
  font-family: var(--font-sans);
  background: none;
  border: none;
  padding: 0;
}
.gate-link:hover { color: var(--text-1); }
@media (max-width: 480px) {
  .menu { gap: 18px; }
  .menu a, .link-btn, .gate-link { font-size: 14px; }
}
</style>
