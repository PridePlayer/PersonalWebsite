import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// base './' keeps asset paths relative so the built dist/ works when served
// from any sub-path or opened through a static file server.
export default defineConfig({
  plugins: [vue()],
  base: './'
})
