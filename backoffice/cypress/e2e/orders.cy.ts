describe('Gestão de Encomendas', () => {
  beforeEach(() => {
    // Efetuar login antes de cada teste
    cy.visit('/auth/login')
    cy.get('input[type="email"]').type('admin@relogios.inc')
    cy.get('input[type="password"]').type('password')
    cy.get('button').contains('Entrar').click()
    cy.url().should('include', '/dashboard')

    // Ir para a página de encomendas
    cy.visit('/encomendas')
    cy.url().should('include', '/encomendas')
    cy.get('h1.page-title').should('contain', 'Encomendas')
  })

  it('deve listar encomendas e permitir ver detalhes', () => {
    cy.get('.va-data-table').should('be.visible')
    cy.get('.va-data-table tbody tr').should('have.length.at.least', 1)

    // Clicar na primeira encomenda da lista
    cy.get('.va-data-table tbody tr').first().click()

    // Validar redirecionamento para detalhes e esperar carregar
    cy.url().should('match', /\/encomendas\/[A-Za-z0-9-]+$/)
    cy.contains('Estado da Encomenda').should('be.visible')
    cy.get('h1.page-title').should('contain', 'Encomenda')
  })

  it('deve permitir atualizar o estado de uma encomenda', () => {
    // Navegar para a primeira encomenda
    cy.get('.va-data-table tbody tr').first().click()

    // Aguardar carregamento
    cy.contains('Estado da Encomenda').should('be.visible')

    // Clicar no select de estado
    cy.contains('.va-select', 'Novo estado').click()
    // Selecionar o estado "Em Processamento"
    cy.contains('.va-select-option, [role="option"]', 'Em Processamento').click()

    // Atualizar estado
    cy.get('button').contains('Atualizar').click()

    // Validar toast e estado atualizado na página
    cy.get('.va-toast').should('be.visible')
    cy.get('.va-badge').should('contain', 'Em Processamento')
  })
})
