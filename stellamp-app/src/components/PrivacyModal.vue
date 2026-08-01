<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useGate } from '../composables/useGate'
import { gateQuestions } from '../data/content'

const router = useRouter()
const { modalOpen, closeModal, pass } = useGate()

// 每题选中的选项索引（null = 未选）
const selected = ref(gateQuestions.map(() => null))
const error = ref('')

function choose(qi, oi) {
  selected.value[qi] = selected.value[qi] === oi ? null : oi
  error.value = ''
}

function verify() {
  if (selected.value.some((s) => s === null)) {
    error.value = '请先回答全部三个问题。'
    return
  }
  const ok = gateQuestions.every((q, i) => q.options[selected.value[i]] === q.answer)
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
          这里收着我现实里的学校、任职与模联经历。只有愿意多了解我的人，才走得进来——回答下面三个只有熟人知道的小问题。
        </p>
        <span class="rule"></span>

        <div class="questions">
          <div v-for="(q, qi) in gateQuestions" :key="qi" class="q">
            <p class="q-text">{{ qi + 1 }}. {{ q.q }}</p>
            <div class="opts">
              <button
                v-for="(opt, oi) in q.options"
                :key="oi"
                class="opt"
                :class="{ 'opt-on': selected[qi] === oi }"
                @click="choose(qi, oi)"
              >
                {{ opt }}
              </button>
            </div>
          </div>
        </div>

        <p v-if="error" class="err">{{ error }}</p>

        <button class="btn-primary gate-btn" @click="verify">验证并进入幕后 →</button>
        <p class="note">提示：答案都是些生活里的小细节，不是密码。</p>
      </div>
    </div>
  </transition>
</template>

<style scoped>
.overlay {
  position: fixed;
  inset: 0;
  z-index: 100;
  background: rgba(12, 10, 16, 0.66);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}
.modal {
  position: relative;
  width: 560px;
  max-width: 100%;
  max-height: 88vh;
  overflow-y: auto;
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
.questions { display: flex; flex-direction: column; gap: 26px; }
.q-text {
  font-size: 16px;
  font-weight: 500;
  color: var(--text-1);
  font-family: var(--font-sans);
  margin: 0 0 12px;
}
.opts { display: flex; flex-wrap: wrap; gap: 10px; }
.opt {
  background: var(--surface);
  border: 1px solid var(--hairline);
  border-radius: var(--r-pill);
  padding: 9px 18px;
  font-size: 14px;
  color: var(--text-2);
  font-family: var(--font-sans);
  transition: border-color 0.15s ease, color 0.15s ease, background 0.15s ease;
}
.opt:hover { color: var(--text-1); border-color: var(--text-4); }
.opt-on {
  background: rgba(138, 123, 176, 0.16);
  border-color: var(--accent);
  color: var(--accent);
}
.err {
  color: #E2A0A0;
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
  font-family: var(--font-sans);
}
.modal-enter-active,
.modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from,
.modal-leave-to { opacity: 0; }
@media (max-width: 480px) {
  .modal { padding: 32px 22px 28px; }
}
</style>
