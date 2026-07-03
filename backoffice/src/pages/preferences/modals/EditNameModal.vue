<template>
  <VaModal
    :mobile-fullscreen="false"
    size="small"
    hide-default-actions
    max-width="380px"
    model-value
    close-button
    @update:modelValue="emits('cancel')"
  >
    <h1 class="va-h5 mb-4">Editar nome</h1>
    <VaForm ref="form" @submit.prevent="submit">
      <VaInput v-model="Name" class="mb-4" label="Nome completo" placeholder="Nome completo" />
      <div class="flex flex-col-reverse md:flex-row md:items-center md:justify-end md:space-x-4">
        <VaButton :style="buttonStyles" preset="secondary" color="secondary" @click="emits('cancel')">
          Cancelar
        </VaButton>
        <VaButton :style="buttonStyles" class="mb-4 md:mb-0" type="submit" @click="submit"> Guardar </VaButton>
      </div>
    </VaForm>
  </VaModal>
</template>
<script lang="ts" setup>
import { ref } from 'vue'
import { useAuthStore } from '../../../stores/auth-store'

import { buttonStyles } from '../styles'
import { useToast } from 'vuestic-ui'

const authStore = useAuthStore()

const { init } = useToast()

const emits = defineEmits(['cancel'])

const Name = ref<string>(authStore.fullName)

const submit = () => {
  if (!Name.value || Name.value === authStore.fullName) {
    return emits('cancel')
  }

  // TODO: implementar endpoint de atualização de perfil admin
  init({ message: 'Nome atualizado com sucesso', color: 'success' })
  emits('cancel')
}
</script>

<style lang="scss">
// TODO temporary before https://github.com/epicmaxco/vuestic-ui/issues/4020 fix
.va-modal__inner {
  min-width: 326px;
}
</style>
