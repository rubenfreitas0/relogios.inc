<template>
  <VaForm ref="form" @submit.prevent="submit">
    <h1 class="font-semibold text-4xl mb-6">Iniciar Sessão</h1>
    <VaInput
      v-model="formData.email"
      :rules="[validators.required, validators.email]"
      class="mb-4"
      label="Email"
      type="email"
    />
    <VaValue v-slot="isPasswordVisible" :default-value="false">
      <VaInput
        v-model="formData.password"
        :rules="[validators.required]"
        :type="isPasswordVisible.value ? 'text' : 'password'"
        class="mb-6"
        label="Senha"
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

    <div class="flex justify-center mt-4">
      <VaButton class="w-full" @click="submit"> Entrar</VaButton>
    </div>
  </VaForm>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useForm, useToast } from 'vuestic-ui'
import { validators } from '../../services/utils'
import { useAuthStore } from '../../stores/auth-store'

const { validate } = useForm('form')
const { push } = useRouter()
const { init } = useToast()
const authStore = useAuthStore()

const formData = reactive({
  email: '',
  password: '',
  keepLoggedIn: false,
})

const isLoading = ref(false)

const submit = async () => {
  if (!validate()) return

  isLoading.value = true
  const success = await authStore.login(formData.email, formData.password)
  isLoading.value = false

  if (success) {
    init({ message: 'Login efetuado com sucesso!', color: 'success' })
    push({ name: 'dashboard' })
  } else {
    init({ message: authStore.error || 'Credenciais inválidas.', color: 'danger' })
  }
}
</script>
