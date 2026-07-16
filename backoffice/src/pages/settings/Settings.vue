<template>
  <div class="flex flex-col space-y-6 md:space-y-4">
    <h1 class="page-title">Settings</h1>
    <div class="flex flex-col p-4 space-y-4 bg-backgroundSecondary rounded-lg">
      <h3 class="h3">Theme</h3>
      <ThemeSwitcher />
    </div>
  </div>
</template>
<script lang="ts" setup>
import ThemeSwitcher from './theme-switcher/ThemeSwitcher.vue'
</script>
