describe('Gestão de Tickets de Suporte', () => {
  const adminUser = {
    id: 1,
    firstname: 'Admin',
    lastname: 'Principal',
    email: 'admin@relogios.inc',
    role: 'admin',
  }

  const mockUser = {
    id: 10,
    firstname: 'Maria',
    lastname: 'Silva',
    email: 'maria@example.com',
    phone: '912345678',
    addresses: [
      {
        id: 1,
        street: 'Rua das Flores 123',
        city: 'Lisboa',
        postal_code: '1000-001',
        country: 'Portugal',
        is_default: true,
      },
    ],
    orders: [
      {
        id: 1,
        order_number: 'ENC-2026-0001',
        total: '150.00',
        status: 'shipped',
        payment_status: 'paid',
        created_at: '2026-06-01T12:00:00Z',
      },
    ],
  }

  const mockTicketsList = {
    data: [
      {
        id: 1,
        subject: 'devolucao',
        message: 'Gostaria de devolver o relógio pois chegou riscado.',
        status: 'open',
        type: 'support',
        created_at: '2026-07-01T09:00:00Z',
        updated_at: '2026-07-01T09:00:00Z',
        user: mockUser,
      },
      {
        id: 2,
        subject: 'duvida',
        message: 'O relógio é resistente à água até quantos metros?',
        status: 'closed',
        type: 'support',
        created_at: '2026-06-20T09:00:00Z',
        updated_at: '2026-06-21T09:00:00Z',
        user: { ...mockUser, id: 11, firstname: 'João', lastname: 'Costa', addresses: [], orders: [] },
      },
    ],
    meta: { current_page: 1, last_page: 1, per_page: 10, total: 2 },
  }

  const setupAdminSession = (win: Window) => {
    win.localStorage.setItem('backoffice_auth_token', 'mock-main-admin-token')
    win.localStorage.setItem('backoffice_auth_user', JSON.stringify(adminUser))
  }

  beforeEach(() => {
    cy.viewport(1400, 900)

    cy.intercept('GET', '**/api/admin/dashboard/stats*', {
      products: { total: 0, active: 0, out_of_stock: 0, low_stock: 0 },
      orders: { today: 0, this_month: 0, last_month: 0, pending_count: 0, by_status: {} },
      revenue: { this_month: 0, last_month: 0, by_month: [] },
      customers: { total: 0 },
      latest_orders: [],
    })

    cy.intercept('GET', '**/api/admin/tickets*', mockTicketsList).as('getTickets')
  })

  it('deve listar tickets e permitir pesquisar e filtrar por estado e assunto', () => {
    cy.visit('/tickets', { onBeforeLoad: setupAdminSession })
    cy.wait('@getTickets')

    cy.contains('Tickets de Suporte').should('be.visible')
    cy.get('.va-data-table tbody tr').should('have.length', 2)
    cy.contains('.va-data-table tbody tr', 'Maria Silva').find('.va-badge').should('contain', 'Aberto')
    cy.contains('.va-data-table tbody tr', 'João Costa').find('.va-badge').should('contain', 'Fechado')

    cy.intercept('GET', '**/api/admin/tickets?*search=maria*').as('searchTickets')
    cy.get('input[placeholder="Pesquisar por utilizador, assunto..."]').type('maria')
    cy.wait('@searchTickets')

    cy.intercept('GET', '**/api/admin/tickets*', mockTicketsList).as('clearSearch')
    cy.get('input[placeholder="Pesquisar por utilizador, assunto..."]').clear()
    cy.wait('@clearSearch')

    cy.intercept('GET', '**/api/admin/tickets*', {
      data: [mockTicketsList.data[0]],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 1 },
    }).as('filterOpen')
    cy.get('input[placeholder="Todos os estados"]').click({ force: true })
    cy.get('.va-select-option').contains('Abertos').click({ force: true })
    cy.wait('@filterOpen').its('request.url').should('include', 'status=open')

    cy.intercept('GET', '**/api/admin/tickets*', mockTicketsList).as('clearStatus')
    cy.contains('.va-select', 'Abertos').find('.va-input-wrapper__clear-icon, .va-icon').first().click({ force: true })
    cy.wait('@clearStatus')

    cy.intercept('GET', '**/api/admin/tickets*', {
      data: [mockTicketsList.data[1]],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 1 },
    }).as('filterSubject')
    cy.get('input[placeholder="Todos os assuntos"]').click({ force: true })
    cy.get('.va-select-option').contains('Dúvida sobre Produto').click({ force: true })
    cy.wait('@filterSubject').its('request.url').should('include', 'subject=duvida')
  })

  it('deve mostrar os detalhes completos do ticket, incluindo cliente, moradas e histórico de encomendas', () => {
    cy.visit('/tickets', { onBeforeLoad: setupAdminSession })
    cy.wait('@getTickets')

    cy.contains('.va-data-table tbody tr', 'Maria Silva').find('[title="Ver mensagem"]').click()
    cy.contains('Detalhes do Ticket de Suporte').should('be.visible')

    cy.contains('#1').should('be.visible')
    cy.contains('Devolução de Artigo').should('be.visible')
    cy.contains('Gostaria de devolver o relógio pois chegou riscado.').should('be.visible')
    cy.contains('Maria Silva').should('be.visible')
    cy.contains('maria@example.com').should('be.visible')
    cy.contains('912345678').should('be.visible')
    cy.contains('Rua das Flores 123, Lisboa').should('be.visible')
    cy.contains('ENC-2026-0001').should('be.visible')
    cy.contains('€150.00').should('be.visible')
  })

  it('deve fechar e reabrir um ticket a partir da tabela', () => {
    cy.visit('/tickets', { onBeforeLoad: setupAdminSession })
    cy.wait('@getTickets')

    cy.intercept('PATCH', '**/api/admin/tickets/1/status', {
      statusCode: 200,
      body: { data: { ...mockTicketsList.data[0], status: 'closed' } },
    }).as('closeTicket')

    cy.contains('.va-data-table tbody tr', 'Maria Silva').find('[title="Fechar ticket"]').click()
    cy.wait('@closeTicket').its('request.body').should('deep.equal', { status: 'closed' })
    cy.contains('Ticket fechado/resolvido.').should('be.visible')

    cy.intercept('PATCH', '**/api/admin/tickets/2/status', {
      statusCode: 200,
      body: { data: { ...mockTicketsList.data[1], status: 'open' } },
    }).as('reopenTicket')

    cy.contains('.va-data-table tbody tr', 'João Costa').find('[title="Reabrir ticket"]').click()
    cy.wait('@reopenTicket').its('request.body').should('deep.equal', { status: 'open' })
    cy.contains('Ticket reaberto.').should('be.visible')
  })

  it('deve permitir marcar como resolvido diretamente a partir do modal de detalhes', () => {
    cy.visit('/tickets', { onBeforeLoad: setupAdminSession })
    cy.wait('@getTickets')

    cy.contains('.va-data-table tbody tr', 'Maria Silva').find('[title="Ver mensagem"]').click()
    cy.contains('Detalhes do Ticket de Suporte').should('be.visible')

    cy.intercept('PATCH', '**/api/admin/tickets/1/status', {
      statusCode: 200,
      body: { data: { ...mockTicketsList.data[0], status: 'closed' } },
    }).as('resolveTicket')

    cy.get('.va-modal').contains('button', 'Marcar como Resolvido').click({ force: true })
    cy.wait('@resolveTicket')
    cy.contains('Ticket fechado/resolvido.').should('be.visible')
  })

  it('deve eliminar um ticket após confirmação', () => {
    cy.visit('/tickets', { onBeforeLoad: setupAdminSession })
    cy.wait('@getTickets')

    cy.contains('.va-data-table tbody tr', 'João Costa').find('[title="Eliminar ticket"]').click()
    cy.contains('.va-modal', 'Eliminar Ticket de Suporte').should('be.visible')
    cy.contains('.va-modal', '#2').should('be.visible')

    cy.intercept('DELETE', '**/api/admin/tickets/2', { statusCode: 200, body: {} }).as('deleteTicket')
    cy.get('.va-modal').contains('button', 'Eliminar').click({ force: true })
    cy.wait('@deleteTicket')
    cy.contains('Ticket de suporte eliminado.').should('be.visible')
    cy.contains('.va-data-table tbody tr', 'João Costa').should('not.exist')
  })

  it('deve atualizar a lista automaticamente através do polling periódico', () => {
    cy.clock(Date.now(), ['setInterval', 'clearInterval'])
    cy.visit('/tickets', { onBeforeLoad: setupAdminSession })
    cy.wait('@getTickets')

    cy.intercept('GET', '**/api/admin/tickets*', {
      data: [
        ...mockTicketsList.data,
        {
          id: 3,
          subject: 'garantia',
          message: 'Novo ticket recebido em tempo real.',
          status: 'open',
          type: 'support',
          created_at: '2026-07-13T09:00:00Z',
          updated_at: '2026-07-13T09:00:00Z',
          user: { ...mockUser, id: 12, firstname: 'Ana', lastname: 'Pereira', addresses: [], orders: [] },
        },
      ],
      meta: { current_page: 1, last_page: 1, per_page: 10, total: 3 },
    }).as('pollTickets')

    cy.tick(20000)
    cy.wait('@pollTickets')
    cy.contains('.va-data-table tbody tr', 'Ana Pereira').should('be.visible')
  })
})
