<template>
  <VaForm ref="form" class="space-y-6" @submit.prevent="submit">
    <div class="space-y-4">
      <VaInput
        v-model="formData.email"
        name="email"
        autocomplete="username"
        :rules="[validators.required, validators.email]"
        label="Email"
        type="email"
        placeholder="admin@relogios.inc"
        class="simple-input"
      />

      <VaValue v-slot="isPasswordVisible" :default-value="false">
        <VaInput
          v-model="formData.password"
          name="password"
          autocomplete="current-password"
          :rules="[validators.required]"
          :type="isPasswordVisible.value ? 'text' : 'password'"
          label="Password"
          class="simple-input"
          @clickAppendInner.stop="isPasswordVisible.value = !isPasswordVisible.value"
        >
          <template #appendInner>
            <VaIcon
              :name="isPasswordVisible.value ? 'mso-visibility_off' : 'mso-visibility'"
              class="cursor-pointer"
              color="primary"
            />
          </template>
        </VaInput>
      </VaValue>
    </div>

    <VaAlert v-if="authStore.error" color="danger" class="mt-4" dense>
      {{ authStore.error }}
    </VaAlert>

    <div class="flex justify-center pt-2">
      <button
        type="submit"
        :disabled="authStore.loading || isRateLimited"
        class="w-full h-11 bg-black text-white font-bold rounded-lg hover:bg-black/90 active:scale-[0.99] transition-all flex items-center justify-center"
      >
        <span v-if="authStore.loading" class="animate-spin mr-2">...</span>
        {{ isRateLimited ? `Bloqueado (${cooldownSeconds}s)` : 'Entrar' }}
      </button>
    </div>

    <!-- Dev Helper -->
    <div v-if="isDev" class="mt-6 pt-4 border-t border-gray-200 flex justify-center">
      <button
        type="button"
        class="text-xs text-gray-500 hover:text-black transition duration-200 flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200"
        @click="injectTestData"
      >
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        Preencher dados de teste <span class="text-gray-400 text-[10px] font-mono ml-1">(Ctrl+Shift+Y)</span>
      </button>
    </div>
  </VaForm>
</template>

<script lang="ts" setup>
import { reactive, ref, computed, onMounted, onUnmounted } from 'vue'
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

const isDev = import.meta.env.DEV

// Rate limiting state
const attempts = ref<number[]>([])
const cooldownSeconds = ref(0)
let cooldownTimer: number | null = null

const isRateLimited = computed(() => cooldownSeconds.value > 0)

function checkRateLimit(): boolean {
  const now = Date.now()
  attempts.value = attempts.value.filter((t) => now - t < 30000)

  if (attempts.value.length >= 5) {
    const earliest = attempts.value[0]
    cooldownSeconds.value = Math.ceil((30000 - (now - earliest)) / 1000)
    authStore.error = `Demasiadas tentativas de login. Aguarde ${cooldownSeconds.value}s.`

    if (cooldownTimer) clearInterval(cooldownTimer)
    cooldownTimer = window.setInterval(() => {
      if (cooldownSeconds.value > 1) {
        cooldownSeconds.value--
        authStore.error = `Demasiadas tentativas de login. Aguarde ${cooldownSeconds.value}s.`
      } else {
        cooldownSeconds.value = 0
        authStore.error = ''
        if (cooldownTimer) {
          clearInterval(cooldownTimer)
          cooldownTimer = null
        }
      }
    }, 1000)
    return true
  }

  attempts.value.push(now)
  return false
}

function injectTestData() {
  formData.email = 'admin@relogios.inc'
  formData.password = 'password'
}

function handleKeyDown(e: KeyboardEvent) {
  if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'y') {
    e.preventDefault()
    injectTestData()
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown)
  if (cooldownTimer) clearInterval(cooldownTimer)
})

const submit = async () => {
  if (isRateLimited.value) return

  const emailInput = document.querySelector('.va-input-wrapper input[type="email"]') as HTMLInputElement | null
  const passwordInput = document.querySelector('.va-input-wrapper input[type="password"]') as HTMLInputElement | null

  if (emailInput && emailInput.value && !formData.email) {
    formData.email = emailInput.value
  }
  if (passwordInput && passwordInput.value && !formData.password) {
    formData.password = passwordInput.value
  }

  if (checkRateLimit()) return

  if (!validate()) return

  const success = await authStore.login(formData.email, formData.password)

  if (success) {
    const redirect = (route.query.redirect as string) || '/dashboard'
    router.push(redirect)
  }
}
</script>

<style lang="scss" scoped>
.simple-input {
  :deep(.va-input-wrapper) {
    --va-input-wrapper-background: #ffffff !important;
    --va-input-wrapper-border-color: #d1d5db !important;
    --va-input-text-color: #000000 !important;
    --va-input-placeholder-color: #9ca3af !important;
    border-radius: 8px !important;
    border-width: 1px !important;
    border-style: solid !important;
    transition: border-color 0.2s ease !important;

    &:hover {
      --va-input-wrapper-border-color: #9ca3af !important;
    }

    &.va-input-wrapper--focused {
      --va-input-wrapper-border-color: #ffc700 !important;
      box-shadow: 0 0 0 2px rgba(255, 199, 0, 0.2) !important;
    }
  }

  :deep(.va-input-label) {
    color: #000000 !important;
    font-weight: 600 !important;
    font-size: 11px !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
  }
}
</style>
