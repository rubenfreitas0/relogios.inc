<template>
  <VaLayout v-if="breakpoint.lgUp" class="h-screen bg-[var(--va-background-secondary)]">
    <template #left>
      <div
        class="bg-[#0f0f11] h-full flex flex-col items-center justify-center border-r border-white/5 select-none"
        style="width: 35vw"
      >
        <span class="text-4xl font-black tracking-[0.15em] text-white"
          >RELOGIOS<span class="text-[#FFC700]">.inc</span></span
        >
        <span class="text-[0.7rem] text-white/35 uppercase tracking-[0.25em] mt-3">Painel de Controlo</span>
      </div>
    </template>
    <template #content>
      <main class="h-full flex items-center justify-center mx-auto max-w-[420px]">
        <RouterView />
      </main>
    </template>
  </VaLayout>

  <VaLayout v-else class="h-screen bg-[var(--va-background-secondary)]">
    <template #content>
      <div class="p-4">
        <main class="h-full flex flex-row items-center justify-start mx-auto max-w-[420px]">
          <div class="flex flex-col items-start w-full">
            <div class="py-6 flex flex-col items-start select-none">
              <span class="text-2xl font-black tracking-[0.12em] text-black dark:text-white"
                >RELOGIOS<span class="text-[#FFC700]">.inc</span></span
              >
              <span class="text-[0.6rem] text-black/40 dark:text-white/35 uppercase tracking-[0.2em] mt-1"
                >Painel de Controlo</span
              >
            </div>
            <RouterView />
          </div>
        </main>
      </div>
    </template>
  </VaLayout>
</template>

<script lang="ts" setup>
import { useBreakpoint } from 'vuestic-ui'

const breakpoint = useBreakpoint()
</script>
