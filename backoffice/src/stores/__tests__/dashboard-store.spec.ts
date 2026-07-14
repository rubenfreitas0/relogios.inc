import { setActivePinia, createPinia } from 'pinia'
import { useDashboardStore } from '../dashboard-store'
import { dashboardApi } from '../../services/api'

vi.mock('../../services/api', () => ({
  dashboardApi: { stats: vi.fn() },
}))

const mockedApi = vi.mocked(dashboardApi)

describe('dashboard-store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('stores the raw response payload as stats on success', async () => {
    const stats = {
      products: { total: 10, active: 8, out_of_stock: 1, low_stock: 2 },
      orders: { today: 1, this_month: 5, last_month: 3, pending_count: 2, by_status: {} },
      revenue: { this_month: 100, last_month: 90, by_month: [] },
      customers: { total: 20 },
      latest_orders: [],
    }
    mockedApi.stats.mockResolvedValue({ data: stats } as any)

    const store = useDashboardStore()
    await store.fetchStats()

    expect(store.stats).toEqual(stats)
    expect(store.loading).toBe(false)
    expect(store.error).toBeNull()
  })

  it('sets a default error message on failure', async () => {
    mockedApi.stats.mockRejectedValue(new Error('boom'))

    const store = useDashboardStore()
    await store.fetchStats()

    expect(store.error).toBe('Erro ao carregar estatísticas do dashboard.')
    expect(store.stats).toBeNull()
  })

  it('uses the API-provided message when available', async () => {
    mockedApi.stats.mockRejectedValue({ response: { data: { message: 'Sem permissões' } } })

    const store = useDashboardStore()
    await store.fetchStats()

    expect(store.error).toBe('Sem permissões')
  })

  it('sets loading true while the request is in flight', async () => {
    let resolveRequest: (value: unknown) => void
    mockedApi.stats.mockReturnValue(
      new Promise((resolve) => {
        resolveRequest = resolve
      }) as any,
    )

    const store = useDashboardStore()
    const promise = store.fetchStats()
    expect(store.loading).toBe(true)

    resolveRequest!({ data: {} })
    await promise

    expect(store.loading).toBe(false)
  })
})
