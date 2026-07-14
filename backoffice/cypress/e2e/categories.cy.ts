describe('Gestão de Categorias', () => {
  const adminUser = {
    id: 1,
    firstname: 'Admin',
    lastname: 'Principal',
    email: 'admin@relogios.inc',
    role: 'admin',
  }

  const mockCategoriesList = {
    data: [
      {
        id: 1,
        name: 'Cor',
        slug: 'cor',
        is_active: true,
        products_count: 40,
        created_at: '2026-01-01T12:00:00Z',
        updated_at: '2026-01-01T12:00:00Z',
      },
      {
        id: 2,
        name: 'Gama de Preço',
        slug: 'gama-de-preco',
        is_active: true,
        products_count: 40,
        created_at: '2026-01-01T12:00:00Z',
        updated_at: '2026-01-01T12:00:00Z',
      },
      {
        id: 3,
        name: 'Sexo',
        slug: 'sexo',
        is_active: false,
        products_count: 5,
        created_at: '2026-01-01T12:00:00Z',
        updated_at: '2026-01-01T12:00:00Z',
      },
    ],
    meta: { current_page: 1, last_page: 1, per_page: 15, total: 3 },
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

  it('deve listar categorias e permitir pesquisar e filtrar por estado', () => {
    cy.visit('/categorias', { onBeforeLoad: setupAdminSession })
    cy.wait('@getCategories')

    cy.contains('Categorias').should('be.visible')
    cy.get('table tbody tr').should('have.length', 3)

    cy.intercept('GET', '**/api/admin/categories*', {
      data: [mockCategoriesList.data[0]],
      meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
    }).as('searchCategories')
    cy.get('input[placeholder="Pesquisar por nome..."]').type('cor')
    cy.wait('@searchCategories').its('request.url').should('include', 'search=cor')

    cy.intercept('GET', '**/api/admin/categories*', {
      data: [mockCategoriesList.data[2]],
      meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
    }).as('filterInactive')
    cy.get('input[placeholder="Pesquisar por nome..."]').clear()
    cy.get('input[placeholder="Todos os estados"]').click({ force: true })
    cy.get('.va-select-option').contains('Inativas').click({ force: true })
    cy.wait('@filterInactive').its('request.url').should('include', 'is_active=false')
  })

  it('deve permitir editar o nome e o estado de uma categoria', () => {
    cy.visit('/categorias', { onBeforeLoad: setupAdminSession })
    cy.wait('@getCategories')

    cy.contains('table tbody tr', 'Sexo').find('[title="Editar"]').click()
    cy.contains('.va-modal', 'Editar Categoria').should('be.visible')
    cy.get('.va-modal input').first().clear().type('Género Biológico')
    cy.get('.va-modal .va-switch').click({ force: true })

    cy.intercept('PUT', '**/api/admin/categories/3', {
      statusCode: 200,
      body: { data: { id: 3, name: 'Género Biológico', slug: 'sexo', is_active: true } },
    }).as('updateCategory')

    cy.get('.va-modal').contains('button', 'Guardar').click({ force: true })
    cy.wait('@updateCategory').its('request.body').should('deep.include', { name: 'Género Biológico' })
    cy.contains('Categoria atualizada.').should('be.visible')
  })

  it('deve gerir itens de cor: listar, criar com validação de hex, editar e eliminar', () => {
    cy.visit('/categorias', { onBeforeLoad: setupAdminSession })
    cy.wait('@getCategories')

    cy.contains('table tbody tr', 'Cor').find('[title="Gerir Itens"]').click()
    cy.contains('Gerir Itens: Cor').should('be.visible')
    cy.get('table tbody tr').should('have.length.at.least', 5)
    cy.contains('table tbody tr', 'Preto').should('contain', '#1a1a1a')

    // Criar novo item de cor com validação de formato hexadecimal
    cy.contains('Novo Item').click()
    cy.contains('.va-modal', 'Novo Item').should('be.visible')
    cy.get('.va-modal input').first().type('Vermelho')
    cy.get('.va-modal input').eq(1).clear().type('not-a-hex')
    cy.get('.va-modal')
      .contains('button', /^Criar$/)
      .click({ force: true })
    cy.contains('Código hexadecimal inválido (ex: #ffffff)').should('be.visible')

    cy.get('.va-modal input').eq(1).clear().type('#ff0000')
    cy.get('.va-modal')
      .contains('button', /^Criar$/)
      .click({ force: true })
    cy.contains('Item criado com sucesso.').should('be.visible')
    cy.contains('table tbody tr', 'Vermelho').should('contain', '#ff0000')

    // Editar o item recém-criado
    cy.contains('table tbody tr', 'Vermelho').find('[title="Editar"]').click()
    cy.get('.va-modal input').first().clear().type('Vermelho Ferrari')
    cy.get('.va-modal')
      .contains('button', /^Guardar$/)
      .click({ force: true })
    cy.contains('Item atualizado com sucesso.').should('be.visible')
    cy.contains('table tbody tr', 'Vermelho Ferrari').should('be.visible')

    // Eliminar o item
    cy.contains('table tbody tr', 'Vermelho Ferrari').find('[title="Eliminar"]').click()
    cy.contains('.va-modal', 'Eliminar Item').should('be.visible')
    cy.get('.va-modal').contains('button', 'Eliminar').click({ force: true })
    cy.contains('Item eliminado.').should('be.visible')
    cy.contains('table tbody tr', 'Vermelho Ferrari').should('not.exist')

    cy.contains('Voltar para Categorias').click()
    cy.contains('h1', 'Categorias').should('be.visible')
  })

  it('deve gerir itens de gama de preço com validação de valores mínimos e máximos', () => {
    cy.visit('/categorias', { onBeforeLoad: setupAdminSession })
    cy.wait('@getCategories')

    cy.contains('table tbody tr', 'Gama de Preço').find('[title="Gerir Itens"]').click()
    cy.contains('Gerir Itens: Gama de Preço').should('be.visible')

    cy.contains('Novo Item').click()
    cy.get('.va-modal input').first().type('€500 – €1000')
    cy.contains('.va-input-wrapper', 'Preço Mínimo (€)').find('input').clear().type('500')
    cy.contains('.va-input-wrapper', 'Preço Máximo (€)').find('input').clear().type('1000')
    cy.get('.va-modal')
      .contains('button', /^Criar$/)
      .click({ force: true })

    cy.contains('Item criado com sucesso.').should('be.visible')
    cy.contains('table tbody tr', '€500 – €1000').should('be.visible')
  })

  it('deve gerar automaticamente o slug ao criar um item de sexo a partir do nome', () => {
    cy.visit('/categorias', { onBeforeLoad: setupAdminSession })
    cy.wait('@getCategories')

    cy.contains('table tbody tr', 'Sexo').find('[title="Gerir Itens"]').click()
    cy.contains('Gerir Itens: Sexo').should('be.visible')

    cy.contains('Novo Item').click()
    cy.get('.va-modal input').first().type('Não Binário')

    cy.contains('.va-input-wrapper', 'Slug').find('input').invoke('val').should('eq', 'nao-binario')
  })
})
