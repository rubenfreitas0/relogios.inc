<template>
  <div class="w-full">
    <!-- State 1: Forgot Password Form -->
    <VaForm v-if="!isResetMode" ref="passwordForm" @submit.prevent="submitForgot">
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

      <VaInput
        v-model="email"
        :rules="[validators.required, validators.email]"
        class="mb-4"
        label="Email"
        type="email"
      />
      <VaButton class="w-full mb-2" :loading="isLoading" @click="submitForgot">Enviar email</VaButton>
      <VaButton :to="{ name: 'login' }" class="w-full" preset="secondary">Voltar ao login</VaButton>
    </VaForm>

    <!-- State 2: Reset Password Form -->
    <VaForm v-else ref="resetForm" @submit.prevent="submitReset">
      <h1 class="font-semibold text-4xl mb-4">Definir nova palavra-passe</h1>
      <p class="text-base mb-4 leading-5">Preenche os campos abaixo para guardares a tua nova palavra-passe.</p>

      <VaAlert v-if="successMessage" color="success" class="mb-4">
        {{ successMessage }}
      </VaAlert>

      <VaAlert v-if="errorMessage" color="danger" class="mb-4">
        {{ errorMessage }}
      </VaAlert>

      <!-- Email (read-only) -->
      <VaInput v-model="email" class="mb-4" label="Email" type="email" readonly />

      <!-- Password -->
      <VaValue v-slot="isPasswordVisible" :default-value="false">
        <VaInput
          v-model="password"
          name="password"
          :rules="[validators.required, (v) => (v && v.length >= 8) || 'Mínimo 8 caracteres']"
          :type="isPasswordVisible.value ? 'text' : 'password'"
          class="mb-4"
          label="Nova Password"
          @clickAppendInner.stop="isPasswordVisible.value = !isPasswordVisible.value"
        >
          <template #appendInner>
            <VaIcon
              :name="isPasswordVisible.value ? 'mso-visibility_off' : 'mso-visibility'"
              class="cursor-pointer"
              color="secondary"
            />
          </template>
        </VaInput>
      </VaValue>

      <!-- Confirm Password -->
      <VaValue v-slot="isConfirmPasswordVisible" :default-value="false">
        <VaInput
          v-model="passwordConfirmation"
          name="password_confirmation"
          :rules="[validators.required, (v) => v === password || 'As passwords não coincidem']"
          :type="isConfirmPasswordVisible.value ? 'text' : 'password'"
          class="mb-4"
          label="Confirmar Password"
          @clickAppendInner.stop="isConfirmPasswordVisible.value = !isConfirmPasswordVisible.value"
        >
          <template #appendInner>
            <VaIcon
              :name="isConfirmPasswordVisible.value ? 'mso-visibility_off' : 'mso-visibility'"
              class="cursor-pointer"
              color="secondary"
            />
          </template>
        </VaInput>
      </VaValue>

      <VaButton class="w-full mb-2" :loading="isLoading" @click="submitReset">Definir nova password</VaButton>
      <VaButton :to="{ name: 'login' }" class="w-full" preset="secondary">Voltar ao login</VaButton>
    </VaForm>
  </div>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm, useToast } from 'vuestic-ui'
import { validators } from '../../services/utils'
import api from '../../services/api'

const route = useRoute()
const router = useRouter()
const { init } = useToast()

const email = ref('')
const token = ref('')
const password = ref('')
const passwordConfirmation = ref('')

const isResetMode = computed(() => !!token.value)

const passwordForm = useForm('passwordForm')
const resetForm = useForm('resetForm')

const isLoading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

onMounted(() => {
  token.value = (route.query.token as string) ?? ''
  email.value = (route.query.email as string) ?? ''
})

const submitForgot = async () => {
  if (!passwordForm.validate()) return

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

const submitReset = async () => {
  if (!resetForm.validate()) return

  isLoading.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    await api.post('/reset-password', {
      email: email.value,
      token: token.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    successMessage.value = 'A tua password foi alterada com sucesso! A redirecionar para o login...'
    init({ message: 'Password alterada com sucesso!', color: 'success' })
    setTimeout(() => {
      router.push({ name: 'login' })
    }, 2000)
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Erro ao redefinir password. Tenta novamente.'
  } finally {
    isLoading.value = false
  }
}
</script>
