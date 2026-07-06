<template>
  <VaModal
    max-width="530px"
    :mobile-fullscreen="false"
    hide-default-actions
    model-value
    close-button
    @update:modelValue="emits('cancel')"
  >
    <h1 class="va-h5 mb-4">Alterar palavra-passe</h1>
    <VaForm ref="form" class="space-y-6" @submit.prevent="submit">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <VaInput
          v-model="oldPassword"
          :rules="oldPasswordRules"
          label="Palavra-passe atual"
          placeholder="Palavra-passe atual"
          required-mark
          type="password"
        />
        <div class="hidden md:block" />
        <VaInput
          v-model="newPassword"
          :rules="newPasswordRules"
          label="Nova palavra-passe"
          placeholder="Nova palavra-passe"
          required-mark
          type="password"
        />
        <VaInput
          v-model="repeatNewPassword"
          :rules="repeatNewPasswordRules"
          label="Confirmar nova palavra-passe"
          placeholder="Confirmar nova palavra-passe"
          required-mark
          type="password"
        />
      </div>
      <div class="flex flex-col space-y-2">
        <div class="flex space-x-2 items-center">
          <div>
            <VaIcon :name="newPassword?.length! >= 8 ? 'mso-check' : 'mso-close'" color="secondary" size="20px" />
          </div>
          <p>Mínimo de 8 caracteres</p>
        </div>
        <div class="flex space-x-2 items-center">
          <div>
            <VaIcon :name="new Set(newPassword).size >= 6 ? 'mso-check' : 'mso-close'" color="secondary" size="20px" />
          </div>
          <p>Mínimo de 6 caracteres únicos</p>
        </div>
      </div>
      <div class="flex flex-col-reverse md:justify-end md:flex-row md:space-x-4">
        <VaButton :style="buttonStyles" preset="secondary" color="secondary" @click="emits('cancel')">
          Cancelar
        </VaButton>
        <VaButton :style="buttonStyles" class="mb-4 md:mb-0" type="submit" @click="submit"> Atualizar </VaButton>
      </div>
    </VaForm>
  </VaModal>
</template>
<script lang="ts" setup>
import { ref } from 'vue'
import { useForm, useToast } from 'vuestic-ui'

import { buttonStyles } from '../styles'

const oldPassword = ref<string>()
const newPassword = ref<string>()
const repeatNewPassword = ref<string>()

const { validate } = useForm('form')
const { init } = useToast()

const emits = defineEmits(['cancel'])

const submit = () => {
  if (validate()) {
    init({ message: 'Palavra-passe alterada com sucesso', color: 'success' })
    emits('cancel')
  }
}

const oldPasswordRules = [(v: string) => !!v || 'A palavra-passe atual é obrigatória']

const newPasswordRules = [
  (v: string) => !!v || 'A nova palavra-passe é obrigatória',
  (v: string) => v?.length >= 8 || 'Mínimo de 8 caracteres',
  (v: string) => new Set(v).size >= 6 || 'Mínimo de 6 caracteres únicos',
  (v: string) => v !== oldPassword.value || 'A nova palavra-passe não pode ser igual à anterior',
]

const repeatNewPasswordRules = [
  (v: string) => !!v || 'Confirme a nova palavra-passe',
  (v: string) => v === newPassword.value || 'As palavras-passe não coincidem',
]
</script>

<style lang="scss">
// TODO temporary before https://github.com/epicmaxco/vuestic-ui/issues/4020 fix
.va-modal__inner {
  min-width: 326px;
}
</style>
