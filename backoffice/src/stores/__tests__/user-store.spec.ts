import { setActivePinia, createPinia } from 'pinia'
import { useUserStore } from '../user-store'
import { useAuthStore } from '../auth-store'

vi.mock('../../services/api', () => ({
  default: { post: vi.fn() },
  authApi: { login: vi.fn(), logout: vi.fn() },
}))

describe('user-store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('returns empty values when there is no authenticated user', () => {
    const store = useUserStore()

    expect(store.userName).toBe('')
    expect(store.email).toBe('')
    expect(store.memberSince).toBe('')
    expect(store.pfp).toBe('')
    expect(store.is2FAEnabled).toBe(false)
  })

  it('bridges userName and email from the auth store', () => {
    const authStore = useAuthStore()
    authStore.user = {
      id: 1,
      firstname: 'Ana',
      lastname: 'Silva',
      email: 'ana@relogios.inc',
      role: 'admin',
    }

    const store = useUserStore()
    expect(store.userName).toBe('Ana Silva')
    expect(store.email).toBe('ana@relogios.inc')
  })

  it('formats memberSince from email_verified_at using pt-PT locale', () => {
    const authStore = useAuthStore()
    authStore.user = {
      id: 1,
      firstname: 'Ana',
      lastname: 'Silva',
      email: 'ana@relogios.inc',
      role: 'admin',
      email_verified_at: '2026-03-05T10:00:00Z',
    }

    const store = useUserStore()
    expect(store.memberSince).toBe(new Date('2026-03-05T10:00:00Z').toLocaleDateString('pt-PT'))
  })

  it('leaves memberSince empty when the email is not verified', () => {
    const authStore = useAuthStore()
    authStore.user = {
      id: 1,
      firstname: 'Ana',
      lastname: 'Silva',
      email: 'ana@relogios.inc',
      role: 'admin',
    }

    const store = useUserStore()
    expect(store.memberSince).toBe('')
  })

  it('exposes no-op stubs for 2FA and username change', () => {
    const store = useUserStore()
    expect(() => store.toggle2FA()).not.toThrow()
    expect(() => store.changeUserName('Novo Nome')).not.toThrow()
  })
})
