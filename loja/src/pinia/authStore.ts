import { defineStore } from 'pinia'
import { ref } from 'vue'

const API_BASE = '/api'

export interface AuthUser {
	id: number
	firstname: string
	lastname: string
	email: string
	phone: string | null
	is_admin: boolean
	email_verified_at: string | null
}

export const useAuthStore = defineStore('auth', () => {
	const user = ref<AuthUser | null>(null)
	const token = ref<string | null>(localStorage.getItem('auth_token'))
	const isLoading = ref(false)
	const error = ref<string | null>(null)
	const successMessage = ref<string | null>(null)

	function clearMessages() {
		error.value = null
		successMessage.value = null
	}

	function setToken(t: string) {
		token.value = t
		localStorage.setItem('auth_token', t)
	}

	function clearAuth() {
		user.value = null
		token.value = null
		localStorage.removeItem('auth_token')
	}

	async function login(email: string, password: string): Promise<boolean> {
		clearMessages()
		isLoading.value = true
		try {
			const res = await fetch(`${API_BASE}/login`, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
				body: JSON.stringify({ email, password }),
			})
			const data = await res.json()
			if (!res.ok) {
				error.value = data.message ?? 'Erro ao fazer login.'
				return false
			}
			setToken(data.token)
			user.value = data.user
			return true
		} catch {
			error.value = 'Sem ligação ao servidor. Tenta novamente.'
			return false
		} finally {
			isLoading.value = false
		}
	}

	async function register(payload: {
		firstname: string
		lastname: string
		email: string
		password: string
		password_confirmation: string
		phone?: string
	}): Promise<boolean> {
		clearMessages()
		isLoading.value = true
		try {
			const res = await fetch(`${API_BASE}/register`, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
				body: JSON.stringify(payload),
			})
			const data = await res.json()
			if (!res.ok) {
				// Laravel devolve erros de validação em data.errors
				if (data.errors) {
					const firstError = Object.values(data.errors as Record<string, string[]>)[0]
					error.value = Array.isArray(firstError) ? firstError[0] : String(firstError)
				} else {
					error.value = data.message ?? 'Erro ao criar conta.'
				}
				return false
			}
			setToken(data.token)
			user.value = data.user
			successMessage.value = data.message ?? 'Conta criada com sucesso!'
			return true
		} catch {
			error.value = 'Sem ligação ao servidor. Tenta novamente.'
			return false
		} finally {
			isLoading.value = false
		}
	}

	async function forgotPassword(email: string): Promise<boolean> {
		clearMessages()
		isLoading.value = true
		try {
			const res = await fetch(`${API_BASE}/forgot-password`, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
				body: JSON.stringify({ email }),
			})
			const data = await res.json()
			if (!res.ok) {
				error.value = data.message ?? 'Erro ao enviar email.'
				return false
			}
			successMessage.value = data.message ?? 'Se o e-mail existir, enviámos o link de recuperação.'
			return true
		} catch {
			error.value = 'Sem ligação ao servidor. Tenta novamente.'
			return false
		} finally {
			isLoading.value = false
		}
	}

	async function resetPassword(payload: {
		email: string
		token: string
		password: string
		password_confirmation: string
	}): Promise<boolean> {
		clearMessages()
		isLoading.value = true
		try {
			const res = await fetch(`${API_BASE}/reset-password`, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
				body: JSON.stringify(payload),
			})
			const data = await res.json()
			if (!res.ok) {
				if (data.errors) {
					const firstError = Object.values(data.errors as Record<string, string[]>)[0]
					error.value = Array.isArray(firstError) ? firstError[0] : String(firstError)
				} else {
					error.value = data.message ?? 'Erro ao redefinir password.'
				}
				return false
			}
			successMessage.value = data.message ?? 'Password alterada com sucesso!'
			return true
		} catch {
			error.value = 'Sem ligação ao servidor. Tenta novamente.'
			return false
		} finally {
			isLoading.value = false
		}
	}

	async function logout(): Promise<void> {
		if (!token.value) return
		try {
			await fetch(`${API_BASE}/logout`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					Accept: 'application/json',
					Authorization: `Bearer ${token.value}`,
				},
			})
		} finally {
			clearAuth()
		}
	}

	async function fetchUser(): Promise<void> {
		if (!token.value) return
		try {
			const res = await fetch(`${API_BASE}/user`, {
				headers: {
					Accept: 'application/json',
					Authorization: `Bearer ${token.value}`,
				},
			})
			if (res.ok) {
				user.value = await res.json()
			} else {
				clearAuth()
			}
		} catch {
			// silently fail
		}
	}

	return {
		user,
		token,
		isLoading,
		error,
		successMessage,
		clearMessages,
		login,
		register,
		forgotPassword,
		resetPassword,
		logout,
		fetchUser,
	}
})
