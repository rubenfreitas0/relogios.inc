import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '../services/api'
import api from '../services/api'

export interface AuthUser {
  id: number
  firstname: string
  lastname: string
  email: string
  role: string
  phone?: string
  email_verified_at?: string
}

export const useAuthStore = defineStore('auth', () => {
  // — State —
  const user = ref<AuthUser | null>(null)
  const token = ref<string | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // — Getters —
  const isAuthenticated = computed(() => !!token.value)
  const fullName = computed(() => (user.value ? `${user.value.firstname} ${user.value.lastname}` : ''))
  const initials = computed(() => {
    if (!user.value) return ''
    return (user.value.firstname[0] + user.value.lastname[0]).toUpperCase()
  })

  // — Actions —

  /**
   * Inicializar a store com dados persistidos no localStorage.
   */
  function init() {
    const storedToken = localStorage.getItem('backoffice_auth_token')
    const storedUser = localStorage.getItem('backoffice_auth_user')

    if (storedToken && storedUser) {
      token.value = storedToken
      try {
        user.value = JSON.parse(storedUser)
      } catch {
        logout()
      }
    } else {
      token.value = null
      user.value = null
    }
  }

  /**
   * Login com email e password.
   * Verifica que o utilizador é admin.
   */
  async function login(email: string, password: string): Promise<boolean> {
    loading.value = true
    error.value = null

    try {
      const response = await authApi.login({ email, password })
      const data = response.data

      // Verificar que é admin
      if (data.user.role !== 'admin') {
        error.value = 'Acesso restrito a administradores.'
        return false
      }

      // Guardar dados
      token.value = data.token
      user.value = data.user

      localStorage.setItem('backoffice_auth_token', data.token)
      localStorage.setItem('backoffice_auth_user', JSON.stringify(data.user))

      return true
    } catch (err: any) {
      if (err.response?.status === 401) {
        error.value = 'Email ou password incorretos.'
      } else if (err.response?.status === 403) {
        error.value = 'Conta desativada.'
      } else {
        error.value = 'Erro ao iniciar sessão. Tenta novamente.'
      }
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Logout — revogar token no servidor e limpar estado local.
   */
  async function logout() {
    const savedToken = token.value

    // Limpar localmente de forma síncrona para evitar loops de redirecionamento assíncronos
    token.value = null
    user.value = null
    localStorage.removeItem('backoffice_auth_token')
    localStorage.removeItem('backoffice_auth_user')

    if (savedToken) {
      try {
        await api.post('/logout', null, {
          headers: {
            Authorization: `Bearer ${savedToken}`,
          },
        })
      } catch {
        // Se falhar, ignorar (token pode já ter expirado)
      }
    }
  }

  return {
    // State
    user,
    token,
    loading,
    error,
    // Getters
    isAuthenticated,
    fullName,
    initials,
    // Actions
    init,
    login,
    logout,
  }
})
