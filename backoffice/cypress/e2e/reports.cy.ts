describe('Relatórios de Vendas', () => {
  const mainAdminUser = {
    id: 1,
    firstname: 'Admin',
    lastname: 'Principal',
    email: 'admin@relogios.inc',
    role: 'admin',
  }

  const secondaryAdminUser = {
    id: 3,
    firstname: 'Admin',
    lastname: 'Secundário',
    email: 'secundario@relogios.inc',
    role: 'admin',
  }

  const mockReports = {
    yearly: [
      { year: 2024, revenue: 40000, orders: 150, growth: 0, average_ticket: 266.67 },
      { year: 2025, revenue: 60000, orders: 220, growth: 50.0, average_ticket: 272.73 },
      { year: 2026, revenue: 90000, orders: 300, growth: 50.0, average_ticket: 300.0 },
    ],
    monthly: [
      {
        year: 2026,
        month: 6,
        month_name: 'Junho',
        label: 'Jun 2026',
        revenue: 5000,
        orders: 30,
        average_ticket: 166.67,
      },
      {
        year: 2026,
        month: 7,
        month_name: 'Julho',
        label: 'Jul 2026',
        revenue: 10000,
        orders: 35,
        average_ticket: 285.71,
      },
    ],
    summary: {
      total_revenue_since_start: 190000,
      total_orders_since_start: 670,
      average_annual_growth: 33.3,
    },
  }

  const setupSession = (user: typeof mainAdminUser) => (win: Window) => {
    win.localStorage.setItem('backoffice_auth_token', 'mock-admin-token')
    win.localStorage.setItem('backoffice_auth_user', JSON.stringify(user))
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
  })

  it('deve calcular e apresentar os totais do período com base nos dados anuais', () => {
    cy.intercept('GET', '**/api/admin/reports', mockReports).as('getReports')
    cy.visit('/relatorios', { onBeforeLoad: setupSession(mainAdminUser) })
    cy.wait('@getReports')

    cy.contains('Relatórios de Vendas').should('be.visible')

    // Total do período: 40000 + 60000 + 90000 = 190000
    cy.contains('Faturação Total (Período)')
      .parents('.va-card')
      .find('.text-2xl')
      .invoke('text')
      .then((text) => {
        expect(text.replace(/\s/g, '')).to.contain('190000,00')
      })

    // Volume de encomendas: 150 + 220 + 300 = 670
    cy.contains('Volume de Encomendas').parents('.va-card').find('.text-2xl').should('contain', '670')

    // Ticket médio global: 190000 / 670 ≈ 283.58
    cy.contains('Ticket Médio Global')
      .parents('.va-card')
      .find('.text-2xl')
      .invoke('text')
      .then((text) => {
        expect(text.replace(/\s/g, '')).to.contain('283,58')
      })
  })

  it('deve recalcular os totais ao restringir o intervalo de anos, sem novo pedido à API', () => {
    cy.intercept('GET', '**/api/admin/reports', mockReports).as('getReports')
    cy.visit('/relatorios', { onBeforeLoad: setupSession(mainAdminUser) })
    cy.wait('@getReports')

    // Restringir o filtro para excluir 2024 (apenas 2025 e 2026: 60000 + 90000 = 150000)
    cy.get('.va-select').first().click()
    cy.get('.va-select-option').contains('2025').click({ force: true })

    cy.contains('Faturação Total (Período)')
      .parents('.va-card')
      .find('.text-2xl')
      .invoke('text')
      .then((text) => {
        expect(text.replace(/\s/g, '')).to.contain('150000,00')
      })

    cy.contains('Volume de Encomendas').parents('.va-card').find('.text-2xl').should('contain', '520')
  })

  it('deve apresentar uma mensagem de erro quando o pedido de relatórios falha', () => {
    cy.intercept('GET', '**/api/admin/reports', { statusCode: 500, body: { message: 'Erro interno do servidor.' } }).as(
      'getReportsError',
    )
    cy.visit('/relatorios', { onBeforeLoad: setupSession(mainAdminUser) })
    cy.wait('@getReportsError')

    cy.contains('Erro interno do servidor.').should('be.visible')
  })

  it('deve impedir o acesso de um administrador secundário e redirecionar para o dashboard', () => {
    cy.visit('/dashboard', { onBeforeLoad: setupSession(secondaryAdminUser) })
    cy.visit('/relatorios')
    cy.url().should('include', '/dashboard')
    cy.url().should('not.include', '/relatorios')
  })
})
