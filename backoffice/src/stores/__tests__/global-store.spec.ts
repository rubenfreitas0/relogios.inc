import { setActivePinia, createPinia } from 'pinia'
import { useGlobalStore } from '../global-store'

describe('global-store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('starts with the sidebar expanded', () => {
    const store = useGlobalStore()
    expect(store.isSidebarMinimized).toBe(false)
  })

  it('toggles the sidebar state on each call', () => {
    const store = useGlobalStore()
    store.toggleSidebar()
    expect(store.isSidebarMinimized).toBe(true)
    store.toggleSidebar()
    expect(store.isSidebarMinimized).toBe(false)
  })
})
