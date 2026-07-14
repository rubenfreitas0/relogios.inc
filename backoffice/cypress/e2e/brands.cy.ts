describe('Gestão de Marcas', () => {
  const adminUser = {
    id: 1,
    firstname: 'Admin',
    lastname: 'Principal',
    email: 'admin@relogios.inc',
    role: 'admin',
  }

  const mockBrandsList = {
    data: [
      {
        id: 1,
        name: 'Casio',
        slug: 'casio',
        logo: 'https://picsum.photos/100',
        is_active: true,
        products_count: 12,
        created_at: '2026-01-01T12:00:00Z',
        updated_at: '2026-01-01T12:00:00Z',
      },
      {
        id: 2,
        name: 'Seiko',
        slug: 'seiko',
        logo: null,
        is_active: false,
        products_count: 0,
        created_at: '2026-01-02T12:00:00Z',
        updated_at: '2026-01-02T12:00:00Z',
      },
    ],
    meta: { current_page: 1, last_page: 1, per_page: 15, total: 2 },
  }

  const setupAdminSession = (win: Window) => {
    win.localStorage.setItem('backoffice_auth_token', 'mock-main-admin-token')
    win.localStorage.setItem('backoffice_auth_user', JSON.stringify(adminUser))
  }

  beforeEach(() => {
    cy.viewport(1400, 900)

    cy.intercept('GET', '**/api/admin/tickets*', { data: [], pagination: { page: 1, perPage: 5, total: 0 } })
    cy.intercept('GET', '**/api/admin/dashboard/stats*', {
      products: { total: 0, active: 0, out_of_stock: 0, low_stock: 0 },
      orders: { today: 0, this_month: 0, last_month: 0, pending_count: 0, by_status: {} },
      revenue: { this_month: 0, last_month: 0, by_month: [] },
      customers: { total: 0 },
      latest_orders: [],
    })

    cy.intercept('GET', '**/api/admin/brands*', mockBrandsList).as('getBrands')
  })

  it('deve listar marcas e permitir pesquisar e filtrar por estado', () => {
    cy.visit('/marcas', { onBeforeLoad: setupAdminSession })
    cy.wait('@getBrands')

    cy.contains('Marcas').should('be.visible')
    cy.get('table tbody tr').should('have.length', 2)
    cy.contains('table tbody tr', 'Casio').find('.va-badge').should('contain', 'Ativa')
    cy.contains('table tbody tr', 'Seiko').find('.va-badge').should('contain', 'Inativa')

    cy.intercept('GET', '**/api/admin/brands*', {
      data: [mockBrandsList.data[0]],
      meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
    }).as('searchBrands')
    cy.get('input[placeholder="Pesquisar por nome..."]').type('casio')
    cy.wait('@searchBrands').its('request.url').should('include', 'search=casio')

    cy.intercept('GET', '**/api/admin/brands*', mockBrandsList).as('clearSearch')
    cy.get('input[placeholder="Pesquisar por nome..."]').clear()
    cy.wait('@clearSearch')

    cy.intercept('GET', '**/api/admin/brands*', {
      data: [mockBrandsList.data[1]],
      meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
    }).as('filterInactive')
    cy.get('input[placeholder="Todos os estados"]').click({ force: true })
    cy.get('.va-select-option').contains('Inativas').click({ force: true })
    cy.wait('@filterInactive').its('request.url').should('include', 'is_active=false')
  })

  it('deve exigir logo ao criar uma nova marca e criar com sucesso após o upload', () => {
    cy.visit('/marcas', { onBeforeLoad: setupAdminSession })
    cy.wait('@getBrands')

    cy.contains('Nova Marca').click()
    cy.contains('.va-modal', 'Nova Marca').should('be.visible')

    cy.get('.va-modal input').first().type('Orient')
    cy.get('.va-modal').contains('button', 'Criar').click({ force: true })
    cy.contains('O logo é obrigatório para novas marcas.').should('be.visible')

    const dummyImage =
      'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
    cy.get('input[type="file"]').selectFile(
      {
        contents: Cypress.Buffer.from(dummyImage.split(',')[1], 'base64'),
        fileName: 'orient.png',
        mimeType: 'image/png',
      },
      { force: true },
    )

    cy.intercept('POST', '**/api/admin/brands', {
      statusCode: 201,
      body: {
        data: { id: 3, name: 'Orient', slug: 'orient', logo: 'https://picsum.photos/100', is_active: true },
      },
    }).as('createBrand')

    cy.contains('button', 'Criar').click({ force: true })
    cy.wait('@createBrand')
    cy.contains('Marca criada.').should('be.visible')
  })

  it('deve permitir editar uma marca existente', () => {
    cy.visit('/marcas', { onBeforeLoad: setupAdminSession })
    cy.wait('@getBrands')

    cy.contains('table tbody tr', 'Seiko').find('[title="Editar"]').click()
    cy.contains('.va-modal', 'Editar Marca').should('be.visible')
    cy.get('.va-modal input').first().clear().type('Seiko 5')

    cy.intercept('POST', '**/api/admin/brands/2*', {
      statusCode: 200,
      body: { data: { id: 2, name: 'Seiko 5', slug: 'seiko', logo: null, is_active: false } },
    }).as('updateBrand')

    cy.get('.va-modal').contains('button', 'Guardar').click({ force: true })
    cy.wait('@updateBrand')
    cy.contains('Marca atualizada.').should('be.visible')
  })

  it('deve permitir desativar uma marca ativa através do modal de confirmação', () => {
    cy.visit('/marcas', { onBeforeLoad: setupAdminSession })
    cy.wait('@getBrands')

    cy.contains('table tbody tr', 'Casio').find('[title="Desativar"]').click()
    cy.contains('.va-modal', 'Desativar Marca').should('be.visible')
    cy.contains('.va-modal', 'Casio').should('be.visible')

    cy.intercept('DELETE', '**/api/admin/brands/1', { statusCode: 200, body: { message: 'ok' } }).as('deactivateBrand')
    cy.get('.va-modal').contains('button', 'Desativar').click({ force: true })
    cy.wait('@deactivateBrand')
    cy.contains('Marca desativada.').should('be.visible')
  })
})
