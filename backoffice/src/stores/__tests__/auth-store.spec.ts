import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '../auth-store'
import api, { authApi } from '../../services/api'

vi.mock('../../services/api', () => ({
  default: { post: vi.fn() },
  authApi: { login: vi.fn(), logout: vi.fn() },
}))

const mockedApi = vi.mocked(api, true)
const mockedAuthApi = vi.mocked(authApi)

const adminUser = {
  id: 1,
  firstname: 'Ana',
  lastname: 'Silva',
  email: 'ana@relogios.inc',
  role: 'admin',
}

describe('auth-store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    localStorage.clear()
  })

  describe('init', () => {
    it('leaves state empty when nothing is persisted', () => {
      const store = useAuthStore()
      store.init()

      expect(store.token).toBeNull()
      expect(store.user).toBeNull()
      expect(store.isAuthenticated).toBe(false)
    })

    it('restores token and user from localStorage', () => {
      localStorage.setItem('backoffice_auth_token', 'tok-123')
      localStorage.setItem('backoffice_auth_user', JSON.stringify(adminUser))

      const store = useAuthStore()
      store.init()

      expect(store.token).toBe('tok-123')
      expect(store.user).toEqual(adminUser)
      expect(store.isAuthenticated).toBe(true)
    })

    it('clears state when the persisted user is corrupt JSON', () => {
      localStorage.setItem('backoffice_auth_token', 'tok-123')
      localStorage.setItem('backoffice_auth_user', '{not-json')

      const store = useAuthStore()
      store.init()

      expect(store.token).toBeNull()
      expect(localStorage.getItem('backoffice_auth_token')).toBeNull()
      expect(localStorage.getItem('backoffice_auth_user')).toBeNull()
    })
  })

  describe('login', () => {
    it('stores token and user, and persists them for an admin', async () => {
      mockedAuthApi.login.mockResolvedValue({ data: { token: 'tok-abc', user: adminUser } } as any)

      const store = useAuthStore()
      const result = await store.login('ana@relogios.inc', 'secret')

      expect(result).toBe(true)
      expect(store.token).toBe('tok-abc')
      expect(store.user).toEqual(adminUser)
      expect(localStorage.getItem('backoffice_auth_token')).toBe('tok-abc')
      expect(JSON.parse(localStorage.getItem('backoffice_auth_user')!)).toEqual(adminUser)
      expect(store.loading).toBe(false)
      expect(store.error).toBeNull()
    })

    it('rejects non-admin users without persisting anything', async () => {
      mockedAuthApi.login.mockResolvedValue({
        data: { token: 'tok-abc', user: { ...adminUser, role: 'customer' } },
      } as any)

      const store = useAuthStore()
      const result = await store.login('ana@relogios.inc', 'secret')

      expect(result).toBe(false)
      expect(store.error).toBe('Acesso restrito a administradores.')
      expect(store.token).toBeNull()
      expect(localStorage.getItem('backoffice_auth_token')).toBeNull()
    })

    it('reports invalid credentials on 401', async () => {
      mockedAuthApi.login.mockRejectedValue({ response: { status: 401 } })

      const store = useAuthStore()
      const result = await store.login('ana@relogios.inc', 'wrong')

      expect(result).toBe(false)
      expect(store.error).toBe('Email ou password incorretos.')
    })

    it('reports disabled account on 403', async () => {
      mockedAuthApi.login.mockRejectedValue({ response: { status: 403 } })

      const store = useAuthStore()
      const result = await store.login('ana@relogios.inc', 'secret')

      expect(result).toBe(false)
      expect(store.error).toBe('Conta desativada.')
    })

    it('falls back to a generic message for other errors', async () => {
      mockedAuthApi.login.mockRejectedValue(new Error('network down'))

      const store = useAuthStore()
      const result = await store.login('ana@relogios.inc', 'secret')

      expect(result).toBe(false)
      expect(store.error).toBe('Erro ao iniciar sessão. Tenta novamente.')
      expect(store.loading).toBe(false)
    })
  })

  describe('logout', () => {
    it('clears local state synchronously and revokes the token on the server', async () => {
      mockedAuthApi.login.mockResolvedValue({ data: { token: 'tok-abc', user: adminUser } } as any)
      mockedApi.post.mockResolvedValue({} as any)

      const store = useAuthStore()
      await store.login('ana@relogios.inc', 'secret')

      const logoutPromise = store.logout()
      // State clears immediately, before the network call resolves
      expect(store.token).toBeNull()
      expect(store.user).toBeNull()
      expect(localStorage.getItem('backoffice_auth_token')).toBeNull()

      await logoutPromise
      expect(mockedApi.post).toHaveBeenCalledWith('/logout', null, {
        headers: { Authorization: 'Bearer tok-abc' },
      })
    })

    it('does not call the API when there is no token', async () => {
      const store = useAuthStore()
      await store.logout()

      expect(mockedApi.post).not.toHaveBeenCalled()
    })

    it('swallows server errors during revocation', async () => {
      mockedAuthApi.login.mockResolvedValue({ data: { token: 'tok-abc', user: adminUser } } as any)
      mockedApi.post.mockRejectedValue(new Error('already expired'))

      const store = useAuthStore()
      await store.login('ana@relogios.inc', 'secret')

      await expect(store.logout()).resolves.toBeUndefined()
    })
  })

  describe('getters', () => {
    it('computes fullName and initials from the current user', async () => {
      mockedAuthApi.login.mockResolvedValue({ data: { token: 'tok-abc', user: adminUser } } as any)

      const store = useAuthStore()
      await store.login('ana@relogios.inc', 'secret')

      expect(store.fullName).toBe('Ana Silva')
      expect(store.initials).toBe('AS')
    })

    it('returns empty strings when there is no user', () => {
      const store = useAuthStore()
      expect(store.fullName).toBe('')
      expect(store.initials).toBe('')
    })
  })
})
