<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import SiteHeader from './components/SiteHeader.vue'
import SiteFooter from './components/SiteFooter.vue'
import PrivacyModal from './components/PrivacyModal.vue'

// 设计稿：首页用导航头（含品牌/关于/作品/博客/幕后）；
// 博客、文章、幕后页用各自的「返回条」，不再显示导航头。
const route = useRoute()
const showNav = computed(() => route.path === '/')
</script>

<template>
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
