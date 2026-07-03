<template>
  <VaForm ref="passwordForm" @submit.prevent="submit">
    <h1 class="font-semibold text-4xl mb-4">Recuperar palavra-passe</h1>
    <p class="text-base mb-4 leading-5">
      Introduz o teu email e enviaremos instruções para redefinir a tua palavra-passe.
    </p>

    <VaAlert v-if="successMessage" color="success" class="mb-4">
      {{ successMessage }}
    </VaAlert>

    <VaAlert v-if="errorMessage" color="danger" class="mb-4">
      {{ errorMessage }}
    </VaAlert>

    <VaInput v-model="email" :rules="[validators.required, validators.email]" class="mb-4" label="Email" type="email" />
    <VaButton class="w-full mb-2" :loading="isLoading" @click="submit">Enviar email</VaButton>
    <VaButton :to="{ name: 'login' }" class="w-full" preset="secondary">Voltar ao login</VaButton>
  </VaForm>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { useForm } from 'vuestic-ui'
import { validators } from '../../services/utils'
import api from '../../services/api'

const email = ref('')
const form = useForm('passwordForm')
const isLoading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const submit = async () => {
  if (!form.validate()) return

  isLoading.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    await api.post('/forgot-password', { email: email.value })
    successMessage.value = 'Email enviado com sucesso! Verifica a tua caixa de entrada.'
  } catch (err: any) {
    if (err.response?.status === 422) {
      errorMessage.value = 'Email não encontrado.'
    } else {
      errorMessage.value = 'Erro ao enviar email. Tenta novamente.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>
