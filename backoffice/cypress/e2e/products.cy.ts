describe('Gestão de Produtos', () => {
  beforeEach(() => {
    // Efetuar login antes de cada teste
    cy.visit('/auth/login')
    cy.get('input[type="email"]').type('admin@relogios.inc')
    cy.get('input[type="password"]').type('password')
    cy.get('button').contains('Entrar').click()
    cy.url().should('include', '/dashboard')

    // Ir para a página de produtos
    cy.visit('/produtos')
    cy.url().should('include', '/produtos')
    cy.get('h1.page-title').should('contain', 'Produtos')
  })

  it('deve listar produtos existentes na tabela', () => {
    cy.get('.va-data-table').should('be.visible')
    cy.get('.va-data-table tbody tr').should('have.length.at.least', 1)
  })

  it('deve conseguir criar um novo produto com sucesso', () => {
    const uniqueName = `Relógio Teste Cypress ${Date.now()}`

    cy.contains('Novo Produto').click()
    cy.url().should('include', '/produtos/novo')
    cy.contains('Informações Gerais').should('be.visible')

    // Informações gerais
    cy.contains('.va-input-wrapper', 'Nome do produto').find('input').type(uniqueName)
    cy.contains('.va-input-wrapper', 'Descrição curta').find('input').type('Breve descrição de teste')
    cy.contains('.va-input-wrapper', 'Descrição completa')
      .find('textarea')
      .type('Descrição completa detalhada do produto de teste.')

    // Preço e Stock
    cy.contains('.va-input-wrapper', 'Preço (€)').find('input').type('399.99')
    cy.contains('.va-input-wrapper', 'Stock').find('input').clear().type('15')

    // Classificações (VaSelects)
    cy.contains('.va-select', 'Marca').click()
    cy.contains('.va-select-option, [role="option"]', 'Casio').click()

    cy.contains('.va-select', 'Categoria').click()
    cy.contains('.va-select-option, [role="option"]', 'Mergulho').click()

    cy.contains('.va-select', 'Género').click()
    cy.contains('.va-select-option, [role="option"]', 'Masculino').click()

    // Submeter
    cy.get('button').contains('Criar Produto').click()

    // Validar redirecionamento e toast
    cy.url().should('match', /\/produtos$/)
    cy.get('.va-toast').should('be.visible')

    // Pesquisar produto criado
    cy.get('input[placeholder="Pesquisar..."]').type(uniqueName)
    cy.wait(600) // Debounce do input
    cy.get('.va-data-table tbody').should('contain', uniqueName)
  })
})
