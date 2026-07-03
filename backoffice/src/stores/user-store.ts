import { defineStore } from 'pinia'
import { computed } from 'vue'
import { useAuthStore } from './auth-store'

/**
 * Store que expõe os dados do utilizador autenticado.
 * Funciona como bridge para os componentes de Preferences
 * que esperam o formato userName/email/etc.
 */
export const useUserStore = defineStore('user', () => {
  const authStore = useAuthStore()

  const userName = computed(() => authStore.fullName || '')
  const email = computed(() => authStore.user?.email || '')
  const memberSince = computed(() => {
    if (!authStore.user?.email_verified_at) return ''
    return new Date(authStore.user.email_verified_at).toLocaleDateString('pt-PT')
  })
  const pfp = computed(() => '')
  const is2FAEnabled = computed(() => false)

  function toggle2FA() {
    // TODO: implementar quando houver 2FA na API
  }

  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  function changeUserName(_newName: string) {
    // TODO: implementar quando houver endpoint de atualizar perfil admin
  }

  return {
    userName,
    email,
    memberSince,
    pfp,
    is2FAEnabled,
    toggle2FA,
    changeUserName,
  }
})
