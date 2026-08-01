<script setup>
import { socials } from '../data/content'
</script>

<template>
  <footer class="site-footer">
    <div class="site footer-inner">
      <span class="footer-left">© 2026 Stellamp</span>
      <div class="footer-socials">
        <a :href="socials.githubUrl" target="_blank" rel="noopener" class="social">{{ socials.github }}</a>
        <a :href="socials.bilibiliUrl" target="_blank" rel="noopener" class="social">{{ socials.bilibili }}</a>
        <a href="mailto:xubingqing07@foxmail.com" class="social social-email">{{ socials.email }}</a>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.site-footer {
  margin-top: 0;
  border-top: 1px solid var(--hairline);
}
.footer-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 40px;
  padding-bottom: 50px;
  flex-wrap: wrap;
  gap: 20px;
}
.footer-left {
  color: var(--text-4);
  font-size: 14px;
  font-family: var(--font-sans);
}
.footer-socials { display: flex; gap: 24px; align-items: center; }
.social { color: var(--accent); font-size: 14px; font-weight: 500; }
.social:hover { color: var(--accent-press); }
.social-email { color: var(--text-4); font-weight: 400; }
.social-email:hover { color: var(--text-2); }
@media (max-width: 480px) {
  .footer-socials { gap: 16px; flex-wrap: wrap; }
  .social, .social-email { font-size: 13px; }
}
</style>
