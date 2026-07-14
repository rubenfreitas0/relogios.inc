describe('Gestão de Envios', () => {
  const adminUser = {
    id: 1,
    firstname: 'Admin',
    lastname: 'Principal',
    email: 'admin@relogios.inc',
    role: 'admin',
  }

  const mockZonesList = {
    data: [
      {
        id: 1,
        name: 'Portugal Continental',
        is_active: true,
        countries: [{ id: 1, country_code: 'PT' }],
        shipping_methods_count: 2,
        created_at: '2026-01-01T12:00:00Z',
        updated_at: '2026-01-01T12:00:00Z',
      },
      {
        id: 2,
        name: 'Europa Central',
        is_active: false,
        countries: [
          { id: 2, country_code: 'ES' },
          { id: 3, country_code: 'FR' },
        ],
        shipping_methods_count: 0,
        created_at: '2026-01-01T12:00:00Z',
        updated_at: '2026-01-01T12:00:00Z',
      },
    ],
    meta: { current_page: 1, last_page: 1, per_page: 15, total: 2 },
  }

  const mockMethodsList = {
    data: [
      {
        id: 1,
        name: 'CTT Expresso',
        carrier: 'CTT',
        price: '4.99',
        min_weight: '0.000',
        max_weight: '2.000',
        estimated_days: '24-48 horas',
        is_active: true,
        shipping_zone_id: 1,
        shipping_zone: { id: 1, name: 'Portugal Continental' },
        created_at: '2026-01-01T12:00:00Z',
        updated_at: '2026-01-01T12:00:00Z',
      },
      {
        id: 2,
        name: 'DHL Internacional',
        carrier: 'DHL',
        price: '19.90',
        min_weight: '0.000',
        max_weight: '10.000',
        estimated_days: '3-5 dias úteis',
        is_active: true,
        shipping_zone_id: 2,
        shipping_zone: { id: 2, name: 'Europa Central' },
        created_at: '2026-01-01T12:00:00Z',
        updated_at: '2026-01-01T12:00:00Z',
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

    cy.intercept('GET', '**/api/admin/shipping-zones*', mockZonesList).as('getZones')
    cy.intercept('GET', '**/api/admin/shipping-methods*', mockMethodsList).as('getMethods')
  })

  describe('Zonas de Envio', () => {
    it('deve listar zonas, pesquisar e filtrar por estado', () => {
      cy.visit('/envios/zonas', { onBeforeLoad: setupAdminSession })
      cy.wait('@getZones')

      cy.contains('Zonas de Envio').should('be.visible')
      cy.get('table tbody tr').should('have.length', 2)
      cy.contains('table tbody tr', 'Europa Central').should('contain', 'ES').and('contain', 'FR')

      cy.intercept('GET', '**/api/admin/shipping-zones*', {
        data: [mockZonesList.data[0]],
        meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
      }).as('searchZones')
      cy.get('input[placeholder="Pesquisar por nome..."]').type('Portugal')
      cy.wait('@searchZones').its('request.url').should('include', 'search=Portugal')

      cy.intercept('GET', '**/api/admin/shipping-zones*', {
        data: [mockZonesList.data[1]],
        meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
      }).as('filterInactive')
      cy.get('input[placeholder="Pesquisar por nome..."]').clear()
      cy.get('input[placeholder="Todos os estados"]').click({ force: true })
      cy.get('.va-select-option').contains('Inativas').click({ force: true })
      cy.wait('@filterInactive').its('request.url').should('include', 'is_active=false')
    })

    it('deve criar uma nova zona de envio com múltiplos países', () => {
      cy.visit('/envios/zonas', { onBeforeLoad: setupAdminSession })
      cy.wait('@getZones')

      cy.contains('Nova Zona').click()
      cy.contains('.va-modal', 'Nova Zona').should('be.visible')
      cy.get('.va-modal input').first().type('Nórdicos')

      cy.get('.va-modal').contains('.va-select', 'Países').click()
      cy.get('.va-select-option').contains('Finlândia (FI)').click({ force: true })
      cy.get('.va-select-option').contains('Suécia (SE)').click({ force: true })
      cy.get('.va-select-option').contains('Dinamarca (DK)').click({ force: true })
      cy.get('.va-modal input').first().click()

      cy.intercept('POST', '**/api/admin/shipping-zones', {
        statusCode: 201,
        body: { data: { id: 3, name: 'Nórdicos', is_active: true, countries: [] } },
      }).as('createZone')

      cy.get('.va-modal')
        .contains('button', /^Criar$/)
        .click({ force: true })
      cy.wait('@createZone')
        .its('request.body')
        .should('deep.include', { name: 'Nórdicos', countries: ['FI', 'SE', 'DK'] })
      cy.contains('Zona de envio criada.').should('be.visible')
    })

    it('deve permitir editar e eliminar uma zona de envio', () => {
      cy.visit('/envios/zonas', { onBeforeLoad: setupAdminSession })
      cy.wait('@getZones')

      cy.contains('table tbody tr', 'Europa Central').find('[title="Editar"]').click()
      cy.get('.va-modal input').first().clear().type('Europa Ocidental')
      cy.intercept('PUT', '**/api/admin/shipping-zones/2', {
        statusCode: 200,
        body: { data: { id: 2, name: 'Europa Ocidental', is_active: false, countries: [] } },
      }).as('updateZone')
      cy.get('.va-modal').contains('button', 'Guardar').click({ force: true })
      cy.wait('@updateZone')
      cy.contains('Zona de envio atualizada.').should('be.visible')

      cy.contains('table tbody tr', 'Portugal Continental').find('[title="Eliminar"]').click()
      cy.contains('.va-modal', 'Eliminar Zona de Envio').should('be.visible')
      cy.intercept('DELETE', '**/api/admin/shipping-zones/1', { statusCode: 200, body: {} }).as('deleteZone')
      cy.get('.va-modal').contains('button', 'Eliminar').click({ force: true })
      cy.wait('@deleteZone')
      cy.contains('Zona de envio eliminada.').should('be.visible')
    })
  })

  describe('Métodos de Envio', () => {
    it('deve listar métodos, filtrar por zona e por estado', () => {
      cy.visit('/envios/metodos', { onBeforeLoad: setupAdminSession })
      cy.wait(['@getMethods', '@getZones'])

      cy.contains('Métodos de Envio').should('be.visible')
      cy.get('table tbody tr').should('have.length', 2)
      cy.contains('table tbody tr', 'CTT Expresso').should('contain', 'Portugal Continental')

      cy.intercept('GET', '**/api/admin/shipping-methods*', {
        data: [mockMethodsList.data[1]],
        meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
      }).as('filterByZone')
      cy.get('input[placeholder="Filtrar por zona"]').click({ force: true })
      cy.get('.va-select-option').contains('Europa Central').click({ force: true })
      cy.wait('@filterByZone').its('request.url').should('include', 'shipping_zone_id=2')

      cy.intercept('GET', '**/api/admin/shipping-methods*', mockMethodsList).as('clearZoneFilter')
      cy.contains('.va-select', 'Europa Central')
        .find('.va-input-wrapper__clear-icon, .va-icon')
        .first()
        .click({ force: true })
      cy.wait('@clearZoneFilter')
    })

    it('deve criar um novo método de envio completo', () => {
      cy.visit('/envios/metodos', { onBeforeLoad: setupAdminSession })
      cy.wait(['@getMethods', '@getZones'])

      cy.contains('Novo Método').click()
      cy.contains('.va-modal', 'Novo Método').should('be.visible')

      cy.contains('.va-input-wrapper', 'Nome do Serviço').find('input').type('CTT Registado')
      cy.contains('.va-input-wrapper', 'Transportadora').find('input').type('CTT')
      cy.contains('.va-input-wrapper', 'Preço (€)').find('input').type('6.5')
      cy.contains('.va-input-wrapper', 'Peso Mín (kg)').find('input').type('0')
      cy.contains('.va-input-wrapper', 'Peso Máx (kg)').find('input').type('3')
      cy.contains('.va-input-wrapper', 'Prazo Estimado').find('input').type('24 horas')

      cy.get('.va-modal').contains('.va-select', 'Zona de Envio').click()
      cy.get('.va-select-option').contains('Portugal Continental').click({ force: true })

      cy.intercept('POST', '**/api/admin/shipping-methods', {
        statusCode: 201,
        body: {
          data: {
            id: 3,
            name: 'CTT Registado',
            carrier: 'CTT',
            price: '6.50',
            is_active: true,
            shipping_zone: { id: 1, name: 'Portugal Continental' },
          },
        },
      }).as('createMethod')

      cy.get('.va-modal')
        .contains('button', /^Criar$/)
        .click({ force: true })
      cy.wait('@createMethod')
      cy.contains('Método de envio criado.').should('be.visible')
    })

    it('deve permitir editar e eliminar um método de envio', () => {
      cy.visit('/envios/metodos', { onBeforeLoad: setupAdminSession })
      cy.wait(['@getMethods', '@getZones'])

      cy.contains('table tbody tr', 'DHL Internacional').find('[title="Editar"]').click()
      cy.contains('.va-input-wrapper', 'Preço (€)').find('input').clear().type('24.90')

      cy.intercept('PUT', '**/api/admin/shipping-methods/2', {
        statusCode: 200,
        body: { data: { id: 2, name: 'DHL Internacional', carrier: 'DHL', price: '24.90', is_active: true } },
      }).as('updateMethod')
      cy.get('.va-modal').contains('button', 'Guardar').click({ force: true })
      cy.wait('@updateMethod').its('request.body.price').should('eq', 24.9)
      cy.contains('Método de envio atualizado.').should('be.visible')

      cy.contains('table tbody tr', 'CTT Expresso').find('[title="Eliminar"]').click()
      cy.contains('.va-modal', 'Eliminar Método de Envio').should('be.visible')
      cy.intercept('DELETE', '**/api/admin/shipping-methods/1', { statusCode: 200, body: {} }).as('deleteMethod')
      cy.get('.va-modal').contains('button', 'Eliminar').click({ force: true })
      cy.wait('@deleteMethod')
      cy.contains('Método de envio eliminado.').should('be.visible')
    })
  })
})
