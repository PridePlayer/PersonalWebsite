<script setup>
// ============================================================
//  Scene3D · 共享轻量 3D 画布（无第三方库）
//  单实例 fixed 全屏 canvas，用 2D canvas + 手动 3D 投影实现：
//   · Hero 蓝色线框多面体（仅首页 + 未深滚时淡入，离开/下滚淡出）
//  降级：prefers-reduced-motion 或 移动端/粗指针 → 完全不启动绘制
// ============================================================
import { ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  // 是否处于首页（决定 Hero 线框体是否显示/淡入）
  active: { type: Boolean, default: false }
})

const canvasRef = ref(null)
let ctx = null
let raf = 0
let enabled = true

// 视口尺寸（CSS px）与设备像素比
let W = 0, H = 0, dpr = 1

// 交互状态：鼠标视差（目标值 -> 平滑值）
const mouse = { x: 0, y: 0, tx: 0, ty: 0 }
let scrollY = 0

// Hero 线框体淡入淡出与自转
let polyAlpha = 0
let polyRotY = 0
const POLY_ROT_X = 0.42

// 用户随鼠标水平位置控制 Hero 线框体缩放（不占用滚轮，避免与页面滚动冲突）
let zoom = 1
// 时间累加器：用于极轻的「呼吸」缩放
let clock = 0

// 十二面体（icosahedron）顶点 + 边
const T = (1 + Math.sqrt(5)) / 2
const baseVerts = [
  [-1, T, 0], [1, T, 0], [-1, -T, 0], [1, -T, 0],
  [0, -1, T], [0, 1, T], [0, -1, -T], [0, 1, -T],
  [T, 0, -1], [T, 0, 1], [-T, 0, -1], [-T, 0, 1]
]
const baseEdges = [
  [0, 1], [0, 5], [0, 7], [0, 10], [0, 11],
  [1, 5], [1, 7], [1, 8], [1, 9],
  [2, 3], [2, 4], [2, 6], [2, 10], [2, 11],
  [3, 4], [3, 6], [3, 8], [3, 9],
  [4, 5], [4, 9], [4, 11],
  [5, 9], [5, 11],
  [6, 7], [6, 8], [6, 10],
  [7, 8], [7, 10],
  [8, 9], [10, 11]
]

// 降级判定
const mqReduce = window.matchMedia('(prefers-reduced-motion: reduce)')
const mqMobile = window.matchMedia('(max-width: 768px), (pointer: coarse)')
function computeEnabled() {
  return !mqReduce.matches && !mqMobile.matches
}

function resize() {
  const c = canvasRef.value
  if (!c) return
  dpr = Math.min(window.devicePixelRatio || 1, 2)
  W = window.innerWidth
  H = window.innerHeight
  c.width = Math.floor(W * dpr)
  c.height = Math.floor(H * dpr)
  c.style.width = W + 'px'
  c.style.height = H + 'px'
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0)
}

// 透视投影：z 越大越远 -> 缩放越小
function project(x, y, z, cx, cy, focal) {
  const s = focal / (focal + z)
  return { x: cx + x * s, y: cy - y * s, s }
}

// 以 (cx,cy) 为中心施加缩放（鼠标缩放 Hero 用）
function zoomed(pr, cx, cy, z) {
  return { x: cx + (pr.x - cx) * z, y: cy + (pr.y - cy) * z, s: pr.s * z }
}

function frame() {
  if (!enabled) { raf = 0; return }
  const c = canvasRef.value
  if (!c || !ctx) { raf = 0; return }

  // 平滑鼠标视差
  mouse.x += (mouse.tx - mouse.x) * 0.05
  mouse.y += (mouse.ty - mouse.y) * 0.05

  clock += 0.016
  // 鼠标水平位置控制 Hero 线框体缩放（极轻，左/右 = 微推远/拉近），平滑跟随
  const zoomTarget = Math.max(0.82, Math.min(1.18, 1 + mouse.tx * 0.18))
  zoom += (zoomTarget - zoom) * 0.04

  ctx.clearRect(0, 0, W, H)

  const focal = 620

  // ---------- Hero 蓝色线框多面体 ----------
  // 仅首页且未滚过半屏时淡入；路由离开或下滚淡出
  let target = 0
  if (props.active) {
    const t = 1 - scrollY / (H * 0.55)
    target = Math.max(0, Math.min(1, t))
  }
    polyAlpha += (target - polyAlpha) * 0.06
  if (polyAlpha > 0.002) {
    polyRotY += 0.0022
    const cx = W * 0.72
    const cy = H * 0.30 - scrollY * 0.02
    const breath = 1 + 0.02 * Math.sin(clock * 0.5)
    const scale = Math.min(W, H) * 0.18 * breath
    const cY = Math.cos(polyRotY), sY = Math.sin(polyRotY)
    const cX = Math.cos(POLY_ROT_X), sX = Math.sin(POLY_ROT_X)
    const pts = baseVerts.map(([vx, vy, vz]) => {
      const x = vx * scale, y = vy * scale, z = vz * scale
      const x1 = x * cY - z * sY
      const z1 = x * sY + z * cY
      const y1 = y * cX - z1 * sX
      const z2 = y * sX + z1 * cX
      return zoomed(project(x1, y1, z2, cx, cy, focal), cx, cy, zoom)
    })
    ctx.lineWidth = 1
    ctx.strokeStyle = `rgba(59,111,181,${0.42 * polyAlpha})`
    ctx.beginPath()
    for (let i = 0; i < baseEdges.length; i++) {
      const a = pts[baseEdges[i][0]]
      const b = pts[baseEdges[i][1]]
      ctx.moveTo(a.x, a.y)
      ctx.lineTo(b.x, b.y)
    }
    ctx.stroke()
  }

  raf = requestAnimationFrame(frame)
}

function start() {
  if (raf || !enabled) return
  raf = requestAnimationFrame(frame)
}
function stop() {
  if (raf) { cancelAnimationFrame(raf); raf = 0 }
}

function onMove(e) {
  mouse.tx = (e.clientX / W) * 2 - 1
  mouse.ty = (e.clientY / H) * 2 - 1
}
function onScroll() { scrollY = window.scrollY || window.pageYOffset || 0 }
function onVisibility() { if (document.hidden) stop(); else start() }
function onReduceChange() {
  enabled = computeEnabled()
  if (enabled) { resize(); start() }
  else { stop(); if (ctx) ctx.clearRect(0, 0, W, H) }
}

onMounted(() => {
  const c = canvasRef.value
  ctx = c.getContext('2d')
  enabled = computeEnabled()
  resize()
  if (enabled) start()
  window.addEventListener('resize', resize)
  window.addEventListener('mousemove', onMove, { passive: true })
  window.addEventListener('scroll', onScroll, { passive: true })
  document.addEventListener('visibilitychange', onVisibility)
  mqReduce.addEventListener('change', onReduceChange)
  mqMobile.addEventListener('change', onReduceChange)
})

onBeforeUnmount(() => {
  stop()
  window.removeEventListener('resize', resize)
  window.removeEventListener('mousemove', onMove)
  window.removeEventListener('scroll', onScroll)
  document.removeEventListener('visibilitychange', onVisibility)
  mqReduce.removeEventListener('change', onReduceChange)
  mqMobile.removeEventListener('change', onReduceChange)
})
</script>

<template>
  <canvas ref="canvasRef" class="scene-3d" aria-hidden="true"></canvas>
</template>

<style scoped>
.scene-3d {
  position: fixed;
  inset: 0;
  z-index: 0;           /* 置于白底之上、内容壳(z-index:1)之下，确保背景可见 */
  pointer-events: none; /* 永不拦截点击/滚动 */
  display: block;
}
</style>
