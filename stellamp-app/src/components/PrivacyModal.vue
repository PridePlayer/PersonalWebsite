<script setup>
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useGate } from '../composables/useGate'
import { gateBank } from '../data/content'

const router = useRouter()
const { modalOpen, closeModal, pass } = useGate()

// 弹窗每次打开从简历题库随机抽 3 题，每题再从 fake 池随机抽 3 个干扰项 + 答案打乱
const questions = ref([])
const selected = ref([])
const error = ref('')

function shuffle(arr) {
  const a = arr.slice()
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[a[i], a[j]] = [a[j], a[i]]
  }
  return a
}

// 从数组里随机不重复地取 n 个
function sample(arr, n) {
  const pool = arr.slice()
  const out = []
  while (out.length < n && pool.length) {
    out.push(pool.splice(Math.floor(Math.random() * pool.length), 1)[0])
  }
  return out
}

function buildQuestions() {
  questions.value = shuffle(gateBank)
    .slice(0, 3)
    .map((item) => ({
      q: item.q,
      answer: item.answer,
      options: shuffle([item.answer, ...sample(item.fake, 3)])
    }))
  selected.value = questions.value.map(() => null)
  error.value = ''
}

watch(modalOpen, (open) => { if (open) buildQuestions() }, { immediate: true })

function choose(qi, oi) {
  selected.value[qi] = selected.value[qi] === oi ? null : oi
  error.value = ''
}

function verify() {
  if (selected.value.some((s) => s === null)) {
    error.value = '请先回答全部三道问题。'
    return
  }
  const ok = questions.value.every((q, i) => q.options[selected.value[i]] === q.answer)
  if (!ok) {
    error.value = '有地方不对，再想想这些只有熟人才知道的事？'
    return
  }
  pass()
  router.push('/behind')
}
</script>

<template>
  <transition name="modal">
    <div v-if="modalOpen" class="overlay" @click.self="closeModal">
      <div class="modal card" role="dialog" aria-modal="true">
        <button class="close" aria-label="关闭" @click="closeModal">×</button>

        <p class="kicker">PRIVATE · 仅熟人</p>
        <h2 class="h-modal">幕后 · 现实生活经历</h2>
        <p class="modal-desc">
          这里收着我现实里的学校、职务与模联经历。只有愿意多了解我的人，才走得进来——每次打开会从我简历里随机抽三道小问题，答案都是生活里的小细节。
        </p>
        <span class="rule"></span>

        <div class="gate-qs">
          <div v-for="(q, qi) in questions" :key="qi" class="gate-q">
            <div class="gate-no">{{ String(qi + 1).padStart(2, '0') }}</div>
            <div class="gate-body">
              <p class="gate-qtext">{{ q.q }}</p>
              <div class="gate-opts">
                <button
                  v-for="(opt, oi) in q.options"
                  :key="oi"
                  class="gate-opt"
                  :class="{ on: selected[qi] === oi }"
                  @click="choose(qi, oi)"
                >
                  <span class="gate-key">{{ String.fromCharCode(65 + oi) }}</span>
                  <span class="gate-opt-text">{{ opt }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <p v-if="error" class="err">{{ error }}</p>

        <button class="btn-primary gate-btn" @click="verify">验证并进入幕后 →</button>
        <p class="note">提示：每回抽到的三题都不一样，不是密码，是只有熟人知道的事。</p>
      </div>
    </div>
  </transition>
</template>

<style scoped>
.overlay {
  position: fixed;
  inset: 0;
  z-index: 100;
  background: rgba(17, 17, 17, 0.34);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}
.modal {
  position: relative;
  width: 600px;
  max-width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  background: var(--surface);
  border: 1px solid var(--hairline);
  box-shadow: 0 24px 64px rgba(17, 17, 17, 0.16);
  border-radius: var(--r-12);
  padding: 40px 44px 36px;
}
.close {
  position: absolute;
  top: 18px;
  right: 20px;
  background: none;
  border: none;
  color: var(--text-4);
  font-size: 26px;
  line-height: 1;
  padding: 0;
}
.close:hover { color: var(--text-1); }
.modal-desc {
  font-size: 15px;
  line-height: 25px;
  color: var(--text-3);
  margin: 14px 0 24px;
}
.gate-qs { display: block; }
.gate-q {
  display: grid;
  grid-template-columns: 52px 1fr;
  gap: 0 20px;
  padding: 22px 0;
  border-top: 1px solid var(--hairline);
}
.gate-q:last-of-type { border-bottom: 1px solid var(--hairline); }
.gate-no {
  font-family: var(--font-sans);
  font-weight: 700;
  font-size: 22px;
  letter-spacing: 0.06em;
  color: var(--accent);
  line-height: 1.4;
}
.gate-qtext {
  font-size: 16px;
  font-weight: 500;
  color: var(--text-1);
  font-family: var(--font-sans);
  margin: 0 0 14px;
}
.gate-opts {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}
.gate-opt {
  display: flex;
  align-items: center;
  gap: 10px;
  background: var(--surface);
  border: 1px solid var(--hairline);
  border-radius: var(--r-4);
  padding: 12px 14px;
  font-size: 14px;
  color: var(--text-2);
  font-family: var(--font-sans);
  text-align: left;
  cursor: pointer;
  transition: border-color 0.15s ease, color 0.15s ease, background 0.15s ease;
}
.gate-opt:hover { border-color: var(--text-4); color: var(--text-1); }
.gate-opt.on {
  border-color: var(--accent);
  color: var(--accent);
  background: rgba(59, 111, 181, 0.08);
}
.gate-key {
  font-weight: 700;
  font-size: 12px;
  color: var(--text-4);
}
.gate-opt.on .gate-key { color: var(--accent); }
.err {
  color: #C0564E;
  font-size: 14px;
  margin: 22px 0 0;
  font-family: var(--font-sans);
}
.gate-btn {
  width: 100%;
  justify-content: center;
  margin-top: 22px;
}
.note {
  font-size: 13px;
  color: var(--text-4);
  margin: 14px 0 0;
  text-align: center;
  font-family: sans-serif;
}
.modal-enter-active,
.modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from,
.modal-leave-to { opacity: 0; }
@media (max-width: 520px) {
  .modal { padding: 28px 20px 26px; }
  .gate-q { grid-template-columns: 36px 1fr; gap: 0 12px; }
  .gate-opts { grid-template-columns: 1fr; }
}
</style>
