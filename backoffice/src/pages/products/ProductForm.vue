<template>
  <div>
    <h1 class="page-title font-bold">{{ isEdit ? 'Editar Produto' : 'Novo Produto' }}</h1>
    <VaCard>
      <VaCardContent>
        <p class="text-lg text-[var(--va-secondary)]">
          Formulário de produto — será implementado na Fase 2.
        </p>
      </VaCardContent>
    </VaCard>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const isEdit = computed(() => !!route.params.id)
</script>
