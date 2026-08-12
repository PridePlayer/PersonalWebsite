<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import SiteHeader from './components/SiteHeader.vue'
import SiteFooter from './components/SiteFooter.vue'
import PrivacyModal from './components/PrivacyModal.vue'
import Scene3D from './components/Scene3D.vue'

// 首页、博客相关页面（列表 / 文章）、幕后页 均显示统一导航头；
// 幕后页进入前需经隐私验证弹窗。
const route = useRoute()
const showNav = computed(() =>
  route.path === '/' ||
  route.path.startsWith('/blog') ||
  route.path.startsWith('/behind')
)
// 仅首页显示 Hero 3D 线框体
const isHome = computed(() => route.path === '/')
</script>

<template>
  <div class="bg-vlines" aria-hidden="true"></div>
  <Scene3D :active="isHome" />
  <!-- 内容壳：透明、z-index 高于背景层，确保背景(栅格+3D)透出 -->
  <div class="app-shell">
    <SiteHeader v-if="showNav" />
    <main class="view">
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>
    <SiteFooter />
    <PrivacyModal />
  </div>
</template>

<style>
/* 全屏竖向栏规线：fixed 背景层，贯穿整页滚动，置于白底之上、内容壳之下 */
.bg-vlines {
  position: fixed;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background-image: repeating-linear-gradient(
    to right,
    var(--vline) 0,
    var(--vline) 1px,
    transparent 1px,
    transparent var(--vline-gap)
  );
}
/* 内容壳：透明背景 + 高于背景层的层级，使背景透出且内容始终在上方 */
.app-shell {
  position: relative;
  z-index: 1;
}
.view {
  min-height: calc(100vh - 72px);
  padding-bottom: 0;
}
/* 路由转场：极轻的淡入 + 微小上移，自然不突兀、不喧宾夺主 */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.42s var(--ease-out), transform 0.42s var(--ease-out);
}
.fade-enter-from {
  opacity: 0;
  transform: translateY(8px);
}
.fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
@media (prefers-reduced-motion: reduce) {
  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.18s ease;
  }
  .fade-enter-from,
  .fade-leave-to {
    transform: none;
  }
}
</style>
