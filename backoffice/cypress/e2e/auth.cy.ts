describe('Autenticação do Backoffice', () => {
  describe('Login e Validações', () => {
    beforeEach(() => {
      cy.visit('/auth/login')
    })

    it('deve mostrar erros de validação com campos vazios', () => {
      cy.get('button').contains('Entrar').click()
      cy.get('input[type="email"]').parents('.va-input-wrapper').should('contain', 'obrigatório')
      cy.get('input[type="password"]').parents('.va-input-wrapper').should('contain', 'obrigatório')
    })

    it('deve falhar com credenciais inválidas', () => {
      cy.get('input[type="email"]').type('invalido@relogios.inc')
      cy.get('input[type="password"]').type('errada123')
      cy.get('button').contains('Entrar').click()

      // O toast de erro ou mensagem de erro
      cy.get('.va-toast').should('be.visible')
    })
  })

  describe('Sessão e Proteção de Rotas', () => {
    it('deve redirecionar para login ao tentar aceder a rota protegida /dashboard sem sessão activa', () => {
      // Limpar localStorage para garantir que não há sessão ativa
      cy.clearLocalStorage()
      cy.visit('/dashboard')

      cy.url().should('include', '/auth/login')
      cy.get('h1').should('contain', 'Iniciar Sessão')
    })

    it('deve efetuar logout, limpar sessão e redirecionar para login impedindo acesso subsequente', () => {
      // Login primeiro
      cy.login()

      cy.visit('/dashboard')
      cy.get('.profile-dropdown__avatar').click() // Abrir dropdown do perfil
      cy.contains('Terminar sessão').click() // Clicar no botão de logout

      cy.url().should('include', '/auth/login')

      // Tentar entrar de novo sem login deve falhar e redirecionar
      cy.visit('/dashboard')
      cy.url().should('include', '/auth/login')
    })
  })
})
