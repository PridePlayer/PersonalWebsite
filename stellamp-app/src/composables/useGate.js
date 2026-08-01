import { reactive, toRefs } from 'vue'

// 单一全局门禁状态：是否已通过隐私验证、弹窗是否打开。
// 用 sessionStorage 维持「同一浏览会话内无需重复验证」。
const STORAGE_KEY = 'stellamp_gate_authed'

const state = reactive({
  authed: sessionStorage.getItem(STORAGE_KEY) === '1',
  modalOpen: false
})

function openModal() {
  state.modalOpen = true
}
function closeModal() {
  state.modalOpen = false
}
function pass() {
  state.authed = true
  state.modalOpen = false
  sessionStorage.setItem(STORAGE_KEY, '1')
}

export function useGate() {
  return {
    ...toRefs(state),
    openModal,
    closeModal,
    pass
  }
}
