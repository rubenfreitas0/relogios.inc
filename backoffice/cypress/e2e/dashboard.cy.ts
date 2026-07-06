describe('Dashboard Admin Panel', () => {
  const mockStatsTemplate = {
    products: {
      total: 50,
      active: 45,
      out_of_stock: 2,
      low_stock: 3,
    },
    orders: {
      today: 5,
      this_month: 35,
      last_month: 30,
      pending_count: 8,
      by_status: {
        pending: 8,
        processing: 12,
        shipped: 15,
      },
    },
    revenue: {
      this_month: 10000,
      last_month: 5000,
      by_month: [
        { month: '2026-02', label: 'Fev 2026', total: 4000, orders: 15 },
        { month: '2026-03', label: 'Mar 2026', total: 4500, orders: 18 },
        { month: '2026-04', label: 'Abr 2026', total: 4800, orders: 20 },
        { month: '2026-05', label: 'Mai 2026', total: 5200, orders: 22 },
        { month: '2026-06', label: 'Jun 2026', total: 5000, orders: 30 },
        { month: '2026-07', label: 'Jul 2026', total: 10000, orders: 35 },
      ],
    },
    customers: {
      total: 120,
    },
    latest_orders: [
      {
        order_number: 'ENC-2026-0001',
        customer_name: 'Maria Silva',
        customer_email: 'maria@example.com',
        status: {
          value: 'pending',
          label: 'Pendente',
        },
        payment_status: {
          value: 'paid',
          label: 'Paga',
        },
        total: 150.0,
        created_at: '2026-07-06T11:00:00Z',
      },
      {
        order_number: 'ENC-2026-0002',
        customer_name: 'João Santos',
        customer_email: 'joao@example.com',
        status: {
          value: 'processing',
          label: 'Em Processamento',
        },
        payment_status: {
          value: 'pending',
          label: 'Aguardando Pagamento',
        },
        total: 85.5,
        created_at: '2026-07-06T10:00:00Z',
      },
    ],
  }

  beforeEach(() => {
    cy.login()
  })

  it('deve calcular e apresentar crescimento positivo de faturação mensal corretamente', () => {
    // Cenário A: Faturação subiu (+100.0% vs mês anterior)
    const mockStats = JSON.parse(JSON.stringify(mockStatsTemplate))
    mockStats.revenue.this_month = 10000
    mockStats.revenue.last_month = 5000

    cy.intercept('GET', '/api/admin/dashboard/stats', mockStats).as('getStats')
    cy.visit('/dashboard')
    cy.wait('@getStats')

    // Verificar se o valor de faturação e badge de aumento estão corretos
    cy.contains('Faturação (Mês)')
      .parents('.va-card')
      .within(() => {
        cy.get('.text-lg.font-semibold')
          .invoke('text')
          .then((text) => {
            expect(text.replace(/\s/g, '')).to.contain('10000,00')
          })
        cy.get('.text-success').should('contain', '+100.0%')
      })
  })

  it('deve calcular e apresentar decréscimo de faturação mensal corretamente', () => {
    // Cenário B: Faturação desceu (-50.0% vs mês anterior)
    const mockStats = JSON.parse(JSON.stringify(mockStatsTemplate))
    mockStats.revenue.this_month = 2500
    mockStats.revenue.last_month = 5000

    cy.intercept('GET', '/api/admin/dashboard/stats', mockStats).as('getStats')
    cy.visit('/dashboard')
    cy.wait('@getStats')

    // Verificar se o valor de faturação e badge de decréscimo estão corretos
    cy.contains('Faturação (Mês)')
      .parents('.va-card')
      .within(() => {
        cy.get('.text-lg.font-semibold')
          .invoke('text')
          .then((text) => {
            expect(text.replace(/\s/g, '')).to.contain('2500,00')
          })
        cy.get('.text-red-600').should('contain', '-50.0%')
      })
  })

  it('deve apresentar contadores de produtos ativos, total clientes e encomendas pendentes corretamente', () => {
    // Cenário C: Contadores gerais
    cy.intercept('GET', '/api/admin/dashboard/stats', mockStatsTemplate).as('getStats')
    cy.visit('/dashboard')
    cy.wait('@getStats')

    // Verificar Produtos Ativos
    cy.contains('Produtos Ativos')
      .parents('.va-card')
      .within(() => {
        cy.get('.text-lg.font-semibold').should('contain', '45')
        cy.get('.text-secondary').should('contain', '50 no catálogo total')
      })

    // Verificar Total Clientes
    cy.contains('Total de Clientes')
      .parents('.va-card')
      .within(() => {
        cy.get('.text-lg.font-semibold').should('contain', '120')
        cy.get('.text-secondary').should('contain', 'Clientes registados')
      })

    // Verificar Encomendas Pendentes
    cy.contains('Encomendas Pendentes')
      .parents('.va-card')
      .within(() => {
        cy.get('.text-lg.font-semibold').should('contain', '8')
        cy.get('.text-secondary').should('contain', 'Pagas, a aguardar envio')
      })
  })

  it('deve apresentar o mapa de receitas por região', () => {
    cy.intercept('GET', '/api/admin/dashboard/stats', mockStatsTemplate).as('getStats')
    cy.visit('/dashboard')
    cy.wait('@getStats')

    cy.contains('Revenue by location').should('be.visible')
    cy.get('.dashboard-map').should('exist')
  })
})
