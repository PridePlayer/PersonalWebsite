<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import SiteHeader from './components/SiteHeader.vue'
import SiteFooter from './components/SiteFooter.vue'
import PrivacyModal from './components/PrivacyModal.vue'

// 首页、博客相关页面（列表 / 文章）、幕后页 均显示统一导航头；
// 幕后页进入前需经隐私验证弹窗。
const route = useRoute()
const showNav = computed(() =>
  route.path === '/' ||
  route.path.startsWith('/blog') ||
  route.path.startsWith('/behind')
)
</script>

<template>
  <div class="bg-vlines" aria-hidden="true"></div>
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
</template>

<style>
/* 全屏竖向栏规线：fixed 背景层，贯穿整页滚动，z-index:-1 沉到内容之下 */
.bg-vlines {
  position: fixed;
  inset: 0;
  z-index: -1;
  pointer-events: none;
  background-image: repeating-linear-gradient(
    to right,
    var(--vline) 0,
    var(--vline) 1px,
    transparent 1px,
    transparent var(--vline-gap)
  );
}
.view {
  min-height: calc(100vh - 72px);
  padding-bottom: 0;
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.18s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
