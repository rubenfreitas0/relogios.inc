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

    it('deve efetuar login com sucesso com credenciais corretas e guardar sessão', () => {
      cy.get('input[type="email"]').type('admin@relogios.inc')
      cy.get('input[type="password"]').type('password')
      cy.get('button').contains('Entrar').click()

      // Deve redirecionar para o dashboard
      cy.url().should('include', '/dashboard')
      cy.get('h1.page-title').should('contain', 'Dashboard')

      // Deve guardar o token no localStorage
      cy.window().then((window) => {
        expect(window.localStorage.getItem('backoffice_auth_token')).to.be.a('string')
      })
    })

    it('deve permitir navegar para a recuperação de password a partir do login', () => {
      cy.get('a').contains('Esqueceu-se da palavra-passe?').click()
      cy.url().should('include', '/auth/recover-password')
      cy.get('h1').should('contain', 'Recuperar palavra-passe')
    })
  })

  describe('Sessão e Proteção de Rotas', () => {
    it('deve redirecionar para login ao tentar aceder a rota protegida /dashboard sem sessão ativa', () => {
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

  describe('Recuperação e Reset de Password', () => {
    it('deve permitir solicitar link de recuperação de password', () => {
      cy.intercept('POST', '/api/forgot-password', {
        statusCode: 200,
        body: { message: 'Se o e-mail existir, enviámos o link de recuperação.' },
      }).as('forgotRequest')

      cy.visit('/auth/recover-password')
      cy.get('input[type="email"]').type('admin@relogios.inc')
      cy.get('button').contains('Enviar email').click()

      cy.wait('@forgotRequest')
      cy.get('.va-alert').should('contain', 'Email enviado com sucesso!')
    })

    it('deve apresentar formulário de reset de password se houver token na URL', () => {
      cy.intercept('POST', '/api/reset-password', {
        statusCode: 200,
        body: { message: 'A sua password foi alterada com sucesso!' },
      }).as('resetRequest')

      // Aceder à rota de reset com token e email na URL
      cy.visit('/reset-password?token=mock_token_123&email=admin@relogios.inc')

      // Deve carregar o formulário de definição de nova password
      cy.get('h1').should('contain', 'Definir nova palavra-passe')
      cy.get('input[readonly]').should('have.value', 'admin@relogios.inc')

      cy.get('input[name="password"]').type('nova_password_123')
      cy.get('input[name="password_confirmation"]').type('nova_password_123')

      cy.get('button').contains('Definir nova password').click()

      cy.wait('@resetRequest')
      cy.get('.va-alert').should('contain', 'alterada com sucesso')
    })
  })
})
