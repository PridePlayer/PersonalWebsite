// v-reveal · 滚动进场指令（极轻、自然、可降级）
// 元素进入视口时加 .is-visible，触发 .reveal 的淡入上移动画。
// 系统开启「减少动态」时直接显示，不做任何动画。
const reveal = {
  mounted(el, binding) {
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (reduce) {
      el.classList.add('is-visible')
      return
    }
    el.classList.add('reveal')
    if (binding.value !== undefined && binding.value !== null) {
      el.style.setProperty('--i', String(binding.value))
    }
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          el.classList.add('is-visible')
          io.unobserve(el)
        }
      })
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' })
    io.observe(el)
    el._revealIO = io
  },
  unmounted(el) {
    if (el._revealIO) {
      el._revealIO.disconnect()
      delete el._revealIO
    }
  }
}
export default reveal
