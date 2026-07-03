describe('Autenticação do Backoffice', () => {
  beforeEach(() => {
    cy.visit('/auth/login')
  })

  it('deve mostrar erros de validação com campos vazios', () => {
    cy.get('button').contains('Entrar').click()
    cy.get('input[type="email"]').parents('.va-input-wrapper').should('contain', 'required')
    cy.get('input[type="password"]').parents('.va-input-wrapper').should('contain', 'required')
  })

  it('deve falhar com credenciais inválidas', () => {
    cy.get('input[type="email"]').type('invalido@relogios.inc')
    cy.get('input[type="password"]').type('errada123')
    cy.get('button').contains('Entrar').click()

    // O toast de erro ou mensagem de erro
    cy.get('.va-toast').should('be.visible')
  })

  it('deve efetuar login com sucesso com credenciais corretas', () => {
    cy.get('input[type="email"]').type('admin@relogios.inc')
    cy.get('input[type="password"]').type('password')
    cy.get('button').contains('Entrar').click()

    // Deve redirecionar para o dashboard
    cy.url().should('include', '/dashboard')
    cy.get('h1.page-title').should('contain', 'Dashboard')
  })
})
