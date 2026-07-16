describe('Gestão de Categorias', () => {
  const adminUser = {
    id: 1,
    firstname: 'Admin',
    lastname: 'Principal',
    email: 'admin@relogios.inc',
    role: 'admin',
  }

  // Categorias reais: duas categorias-pai (group) com as suas subcategorias únicas.
  const mockCategoriesList = {
    data: [
      // Tipo de Relógios
      {
        id: 1,
        name: 'Clássicos',
        slug: 'classicos',
        group: 'tipo',
        is_active: true,
        products_count: 12,
        created_at: '2026-01-01T12:00:00Z',
        updated_at: '2026-01-01T12:00:00Z',
      },
      {
        id: 2,
        name: 'Desporto',
        slug: 'desporto',
        group: 'tipo',
        is_active: true,
        products_count: 8,
        created_at: '2026-01-01T12:00:00Z',
        updated_at: '2026-01-01T12:00:00Z',
      },
      {
        id: 3,
        name: 'Mergulho',
        slug: 'mergulho',
        group: 'tipo',
        is_active: false,
        products_count: 3,
        created_at: '2026-01-01T12:00:00Z',
        updated_at: '2026-01-01T12:00:00Z',
      },
      // Mecanismo
      {
        id: 4,
        name: 'Analógico',
        slug: 'analogico',
        group: 'mecanismo',
        is_active: true,
        products_count: 20,
        created_at: '2026-01-01T12:00:00Z',
        updated_at: '2026-01-01T12:00:00Z',
      },
      {
        id: 5,
        name: 'Digital',
        slug: 'digital',
        group: 'mecanismo',
        is_active: true,
        products_count: 6,
        created_at: '2026-01-01T12:00:00Z',
        updated_at: '2026-01-01T12:00:00Z',
      },
    ],
    meta: { current_page: 1, last_page: 1, per_page: 100, total: 5 },
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

    cy.intercept('GET', '**/api/admin/categories*', mockCategoriesList).as('getCategories')
  })

  it('deve agrupar as categorias em Tipo de Relógios e Mecanismo', () => {
    cy.visit('/categorias', { onBeforeLoad: setupAdminSession })
    cy.wait('@getCategories')

    cy.contains('h1', 'Categorias').should('be.visible')

    // As duas categorias-pai
    cy.contains('h2', 'Tipo de Relógios').should('be.visible')
    cy.contains('h2', 'Mecanismo').should('be.visible')

    // Subcategorias no grupo correto (única associação)
    cy.contains('.va-card', 'Tipo de Relógios').within(() => {
      cy.contains('td', 'Clássicos').should('exist')
      cy.contains('td', 'Desporto').should('exist')
      cy.contains('td', 'Mergulho').should('exist')
      cy.contains('td', 'Analógico').should('not.exist')
    })

    cy.contains('.va-card', 'Mecanismo').within(() => {
      cy.contains('td', 'Analógico').should('exist')
      cy.contains('td', 'Digital').should('exist')
      cy.contains('td', 'Clássicos').should('not.exist')
    })
  })

  it('deve pesquisar por nome e filtrar por estado (no cliente)', () => {
    cy.visit('/categorias', { onBeforeLoad: setupAdminSession })
    cy.wait('@getCategories')

    // Pesquisa
    cy.get('input[placeholder="Pesquisar por nome..."]').type('digital')
    cy.contains('td', 'Digital').should('exist')
    cy.contains('td', 'Clássicos').should('not.exist')
    cy.get('input[placeholder="Pesquisar por nome..."]').clear()

    // Filtro por estado inativo
    cy.get('input[placeholder="Todos os estados"]').click({ force: true })
    cy.get('.va-select-option').contains('Inativas').click({ force: true })
    cy.contains('td', 'Mergulho').should('exist')
    cy.contains('td', 'Clássicos').should('not.exist')
  })

  it('deve permitir editar o nome e o estado de uma subcategoria', () => {
    cy.visit('/categorias', { onBeforeLoad: setupAdminSession })
    cy.wait('@getCategories')

    cy.contains('table tbody tr', 'Mergulho').find('[title="Editar"]').click()
    cy.contains('.va-modal', 'Editar Categoria').should('be.visible')
    cy.get('.va-modal input').first().clear().type('Mergulho Pro')
    cy.get('.va-modal .va-switch').click({ force: true })

    cy.intercept('PUT', '**/api/admin/categories/3', {
      statusCode: 200,
      body: { data: { id: 3, name: 'Mergulho Pro', slug: 'mergulho', group: 'tipo', is_active: true } },
    }).as('updateCategory')

    cy.get('.va-modal').contains('button', 'Guardar').click({ force: true })
    cy.wait('@updateCategory').its('request.body').should('deep.include', { name: 'Mergulho Pro' })
    cy.contains('Categoria atualizada.').should('be.visible')
  })
})
