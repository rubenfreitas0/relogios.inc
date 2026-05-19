<template>
  <div>
    <h1 class="page-title font-bold">Encomenda #{{ orderNumber }}</h1>
    <VaCard>
      <VaCardContent>
        <p class="text-lg text-[var(--va-secondary)]">
          Detalhe de encomenda — será implementado na Fase 3.
        </p>
      </VaCardContent>
    </VaCard>
  </div>
</template>

<script lang="ts" setup>
defineProps<{ orderNumber: string }>()
</script>
