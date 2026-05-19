<template>
  <VaLayout v-if="breakpoint.lgUp" class="h-screen bg-[var(--va-background-secondary)]">
    <template #left>
      <div
        class="h-full flex flex-col items-center justify-center"
        style="width: 35vw; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%)"
      >
        <span class="text-white text-3xl font-black tracking-tight mb-2">RELOGIOS.inc</span>
        <span class="text-white/60 text-sm">Painel de Administração</span>
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
          <div class="flex flex-col items-start">
            <RouterLink class="py-4" to="/" aria-label="Ir para o início">
              <span class="text-2xl font-black tracking-tight">RELOGIOS.inc</span>
            </RouterLink>
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
