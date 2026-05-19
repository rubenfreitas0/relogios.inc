<template>
  <VaForm ref="form" @submit.prevent="submit">
    <h1 class="font-semibold text-4xl mb-2">Backoffice</h1>
    <p class="text-base mb-6 leading-5 text-[var(--va-secondary)]">
      Inicia sessão para gerir a loja.
    </p>

    <VaInput
      v-model="formData.email"
      :rules="[validators.required, validators.email]"
      class="mb-4"
      label="Email"
      type="email"
      placeholder="admin@relogios.inc"
    />

    <VaValue v-slot="isPasswordVisible" :default-value="false">
      <VaInput
        v-model="formData.password"
        :rules="[validators.required]"
        :type="isPasswordVisible.value ? 'text' : 'password'"
        class="mb-4"
        label="Password"
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

    <VaAlert
      v-if="authStore.error"
      color="danger"
      class="mb-4"
      dense
    >
      {{ authStore.error }}
    </VaAlert>

    <div class="flex justify-center mt-2">
      <VaButton
        class="w-full"
        :loading="authStore.loading"
        :disabled="authStore.loading"
        @click="submit"
      >
        Entrar
      </VaButton>
    </div>
  </VaForm>
</template>

<script lang="ts" setup>
import { reactive } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useForm } from 'vuestic-ui'
import { useAuthStore } from '../../stores/auth-store'
import { validators } from '../../services/utils'

const { validate } = useForm('form')
const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const formData = reactive({
  email: '',
  password: '',
})

const submit = async () => {
  if (!validate()) return

  const success = await authStore.login(formData.email, formData.password)

  if (success) {
    const redirect = (route.query.redirect as string) || '/dashboard'
    router.push(redirect)
  }
}
</script>
