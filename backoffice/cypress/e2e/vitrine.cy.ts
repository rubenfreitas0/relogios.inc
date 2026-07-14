describe('Configuração da Vitrine', () => {
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

  const mockSettings = {
    hero_subtitle: 'nova coleção',
    hero_title: 'CASIO MTP-1274 \n COLECÇÃO PREMIUM',
    hero_description: 'Uma peça atemporal para o teu dia a dia.',
    hero_image: 'hero/casio.png',
    hero_link: '/homens',
    hero_button_text: 'ver relógio',
    grid_hero_title: 'Casio \nMTP-1274',
    grid_hero_description: 'Precisão japonesa num design atemporal.',
    grid_hero_image: 'grid/casio.png',
    grid_hero_link: '/homens/casio',
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

  it('deve carregar e apresentar as definições atuais da vitrine', () => {
    cy.intercept('GET', '**/api/admin/site-settings', mockSettings).as('getSettings')
    cy.visit('/vitrine', { onBeforeLoad: setupSession(mainAdminUser) })
    cy.wait('@getSettings')

    cy.contains('Configurar Vitrine').should('be.visible')
    cy.contains('.va-input-wrapper', 'Subtítulo / Etiqueta').find('input').should('have.value', 'nova coleção')
    cy.contains('.va-input-wrapper', 'Texto do Botão').find('input').should('have.value', 'ver relógio')
  })

  it('deve permitir editar e guardar as definições de texto da vitrine', () => {
    cy.intercept('GET', '**/api/admin/site-settings', mockSettings).as('getSettings')
    cy.visit('/vitrine', { onBeforeLoad: setupSession(mainAdminUser) })
    cy.wait('@getSettings')

    cy.contains('.va-input-wrapper', 'Subtítulo / Etiqueta').find('input').clear().type('coleção de verão')
    cy.contains('.va-input-wrapper', 'Descrição Curta')
      .first()
      .find('textarea')
      .clear()
      .type('Nova campanha de verão com peças exclusivas.')

    cy.intercept('PUT', '**/api/admin/site-settings', { statusCode: 200, body: { success: true } }).as('updateSettings')
    cy.intercept('GET', '**/api/admin/site-settings', mockSettings).as('reloadSettings')

    cy.contains('button', 'Guardar Alterações').click()
    cy.wait('@updateSettings')
      .its('request.body.settings')
      .should('deep.include', { hero_subtitle: 'coleção de verão' })
    cy.wait('@reloadSettings')
    cy.contains('Vitrine atualizada com sucesso!').should('be.visible')
  })

  it('deve permitir carregar uma nova imagem do banner principal antes de guardar', () => {
    cy.intercept('GET', '**/api/admin/site-settings', mockSettings).as('getSettings')
    cy.visit('/vitrine', { onBeforeLoad: setupSession(mainAdminUser) })
    cy.wait('@getSettings')

    const dummyImage =
      'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='

    cy.intercept('POST', '**/api/admin/site-settings/image', {
      statusCode: 200,
      body: { data: { path: 'hero/novo-banner.png' } },
    }).as('uploadImage')
    cy.intercept('PUT', '**/api/admin/site-settings', { statusCode: 200, body: { success: true } }).as('updateSettings')
    cy.intercept('GET', '**/api/admin/site-settings', mockSettings).as('reloadSettings')

    cy.contains('Banner Principal (Hero)')
      .parents('.va-card')
      .find('input[type="file"]')
      .selectFile(
        {
          contents: Cypress.Buffer.from(dummyImage.split(',')[1], 'base64'),
          fileName: 'novo-banner.png',
          mimeType: 'image/png',
        },
        { force: true },
      )

    cy.contains('button', 'Guardar Alterações').click()
    cy.wait('@uploadImage')
    cy.wait('@updateSettings')
    cy.contains('Vitrine atualizada com sucesso!').should('be.visible')
  })

  it('deve impedir o acesso de um administrador secundário e redirecionar para o dashboard', () => {
    cy.visit('/dashboard', { onBeforeLoad: setupSession(secondaryAdminUser) })
    cy.visit('/vitrine')
    cy.url().should('include', '/dashboard')
    cy.url().should('not.include', '/vitrine')
  })
})
