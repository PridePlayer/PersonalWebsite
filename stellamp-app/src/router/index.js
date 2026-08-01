import { createRouter, createWebHashHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import BlogView from '../views/BlogView.vue'
import ArticleView from '../views/ArticleView.vue'
import BehindView from '../views/BehindView.vue'
import { useGate } from '../composables/useGate'

const routes = [
  { path: '/', name: 'home', component: HomeView },
  { path: '/blog', name: 'blog', component: BlogView },
  { path: '/blog/:slug', name: 'article', component: ArticleView, props: true },
  { path: '/behind', name: 'behind', component: BehindView },
  { path: '/:pathMatch(.*)*', redirect: '/' }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  }
})

// 门禁：未通过隐私验证直接访问 /behind 时，弹出门禁并退回首页。
router.beforeEach((to) => {
  if (to.name === 'behind') {
    const { authed, openModal } = useGate()
    if (!authed.value) {
      openModal()
      return { name: 'home' }
    }
  }
  return true
})

export default router
