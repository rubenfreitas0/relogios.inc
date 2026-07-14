import { setActivePinia, createPinia } from 'pinia'
import { useUsersStore } from '../users'
import { addUser, getUsers, removeUser, updateUser, uploadAvatar } from '../../data/pages/users'

vi.mock('../../data/pages/users', () => ({
  getUsers: vi.fn(),
  addUser: vi.fn(),
  updateUser: vi.fn(),
  removeUser: vi.fn(),
  uploadAvatar: vi.fn(),
}))

const mockedGetUsers = vi.mocked(getUsers)
const mockedAddUser = vi.mocked(addUser)
const mockedUpdateUser = vi.mocked(updateUser)
const mockedRemoveUser = vi.mocked(removeUser)
const mockedUploadAvatar = vi.mocked(uploadAvatar)

function makeUser(overrides: Partial<{ id: number; fullname: string }> = {}) {
  return { id: 1, fullname: 'Ana Silva', ...overrides } as any
}

describe('users-store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  describe('getAll', () => {
    it('populates items and pagination from the response', async () => {
      mockedGetUsers.mockResolvedValue({
        data: [makeUser()],
        pagination: { page: 1, perPage: 10, total: 1 },
      } as any)

      const store = useUsersStore()
      await store.getAll({})

      expect(store.items).toEqual([makeUser()])
      expect(store.pagination).toEqual({ page: 1, perPage: 10, total: 1 })
    })

    it('forwards filters, sorting and pagination options to getUsers', async () => {
      mockedGetUsers.mockResolvedValue({ data: [], pagination: { page: 2, perPage: 5, total: 0 } } as any)

      const store = useUsersStore()
      await store.getAll({
        filters: { search: 'ana' },
        sorting: { sortBy: 'fullname', sortingOrder: 'asc' },
        pagination: { page: 2, perPage: 5, total: 0 },
      })

      expect(mockedGetUsers).toHaveBeenCalledWith({
        search: 'ana',
        sortBy: 'fullname',
        sortingOrder: 'asc',
        page: 2,
        perPage: 5,
        total: 0,
      })
    })
  })

  describe('add', () => {
    it('prepends the new user to items and returns it', async () => {
      const newUser = makeUser({ id: 2, fullname: 'Novo Utilizador' })
      mockedAddUser.mockResolvedValue([newUser] as any)

      const store = useUsersStore()
      store.items.push(makeUser({ id: 1 }))
      const result = await store.add(newUser)

      expect(result).toEqual(newUser)
      expect(store.items[0]).toEqual(newUser)
      expect(store.items).toHaveLength(2)
    })
  })

  describe('update', () => {
    it('replaces the matching item in place', async () => {
      const updated = makeUser({ id: 1, fullname: 'Ana Atualizada' })
      mockedUpdateUser.mockResolvedValue([updated] as any)

      const store = useUsersStore()
      store.items.push(makeUser({ id: 1 }), makeUser({ id: 2 }))
      const result = await store.update(updated)

      expect(result).toEqual(updated)
      expect(store.items[0]).toEqual(updated)
      expect(store.items[1]).toEqual(makeUser({ id: 2 }))
    })
  })

  describe('remove', () => {
    it('removes the item when the API confirms deletion', async () => {
      mockedRemoveUser.mockResolvedValue(true)

      const store = useUsersStore()
      store.items.push(makeUser({ id: 1 }), makeUser({ id: 2 }))
      await store.remove(makeUser({ id: 1 }))

      expect(store.items.map((u) => u.id)).toEqual([2])
    })

    it('keeps the item when the API reports failure', async () => {
      mockedRemoveUser.mockResolvedValue(false)

      const store = useUsersStore()
      store.items.push(makeUser({ id: 1 }))
      await store.remove(makeUser({ id: 1 }))

      expect(store.items).toHaveLength(1)
    })
  })

  describe('uploadAvatar', () => {
    it('delegates to the uploadAvatar data function', async () => {
      mockedUploadAvatar.mockResolvedValue({ publicUrl: 'https://example.com/a.png' })

      const store = useUsersStore()
      const formData = new FormData()
      const result = await store.uploadAvatar(formData)

      expect(mockedUploadAvatar).toHaveBeenCalledWith(formData)
      expect(result).toEqual({ publicUrl: 'https://example.com/a.png' })
    })
  })
})
