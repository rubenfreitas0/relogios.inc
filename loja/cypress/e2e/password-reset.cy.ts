describe('Password Reset — Forgot Password', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')
		cy.visit('/forgot-password')
	})

	it('renders the forgot password form', () => {
		cy.contains('Recuperar password').should('be.visible')
		cy.get('#forgot-email').should('be.visible')
		cy.contains('button', 'Enviar código').should('be.visible')
	})

	it('submitting a valid email shows the "check your email" success state', () => {
		cy.intercept('POST', '/api/forgot-password', {
			statusCode: 200,
			body: { message: 'Se o e-mail existir, enviámos o código de recuperação.' },
		}).as('forgotPassword')

		cy.get('#forgot-email').type('admin@relogios.inc')
		cy.contains('button', 'Enviar código').click()
		cy.wait('@forgotPassword')

		cy.contains('Verifica o teu email').should('be.visible')
		cy.contains('Se o e-mail existir, enviámos o código de recuperação.').should('be.visible')
	})

	it('redirects to /reset-password with the email pre-filled after requesting a code', () => {
		cy.intercept('POST', '/api/forgot-password', {
			statusCode: 200,
			body: { message: 'Se o e-mail existir, enviámos o código de recuperação.' },
		}).as('forgotPassword')

		cy.get('#forgot-email').type('admin@relogios.inc')
		cy.contains('button', 'Enviar código').click()
		cy.wait('@forgotPassword')

		cy.url({ timeout: 4000 }).should('include', '/reset-password')
		cy.url().should('include', 'email=admin%40relogios.inc')
	})

	it('shows a server error message when the request fails', () => {
		cy.intercept('POST', '/api/forgot-password', {
			statusCode: 500,
			body: { message: 'Erro ao enviar email.' },
		}).as('forgotPasswordError')

		cy.get('#forgot-email').type('admin@relogios.inc')
		cy.contains('button', 'Enviar código').click()
		cy.wait('@forgotPasswordError')

		cy.contains('Erro ao enviar email.').should('be.visible')
		// Should stay on the form, not show the success state
		cy.contains('Verifica o teu email').should('not.exist')
	})

	it('rate-limits repeated submissions client-side after 5 attempts in 30s', () => {
		// Respond with an error each time so the form stays visible between attempts
		cy.intercept('POST', '/api/forgot-password', {
			statusCode: 429,
			body: { message: 'Erro ao enviar email.' },
		}).as('forgotPasswordFail')

		cy.get('#forgot-email').type('admin@relogios.inc')

		// The first 5 clicks reach the API; the 6th is blocked client-side
		for (let i = 0; i < 6; i++) {
			cy.contains('button', /Enviar código|Bloqueado/).click({ force: true })
		}

		cy.contains(/Bloqueado \(\d+s\)/).should('be.visible')
		cy.contains(/Demasiadas tentativas/).should('be.visible')
		cy.get('@forgotPasswordFail.all').should('have.length', 5)
	})
})

describe('Password Reset — Reset Password Page', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')
	})

	it('pre-fills the email from the query string', () => {
		cy.visit('/reset-password?email=admin%40relogios.inc')
		cy.get('#reset-email').should('have.value', 'admin@relogios.inc')
	})

	it('renders the 6-digit code field and password fields', () => {
		cy.visit('/reset-password?email=admin%40relogios.inc')
		cy.get('#reset-token').should('be.visible').and('have.attr', 'maxlength', '6')
		cy.get('#reset-password').should('be.visible')
		cy.get('#reset-confirm').should('be.visible')
	})

	it('password strength indicator updates as the user types', () => {
		cy.visit('/reset-password?email=admin%40relogios.inc')
		cy.get('#reset-password').type('MyStr0ng!Pass')
		cy.contains('Forte').should('be.visible')
	})

	it('shows a mismatch error and disables submit when passwords differ', () => {
		cy.visit('/reset-password?email=admin%40relogios.inc')
		cy.get('#reset-token').type('123456')
		cy.get('#reset-password').type('Password1!')
		cy.get('#reset-confirm').type('PasswordDiferente1!')

		cy.contains('As passwords não coincidem.').should('be.visible')
		cy.get('button[type="submit"]').should('be.disabled')
	})

	it('shows an error for an invalid recovery code', () => {
		cy.intercept('POST', '/api/reset-password', {
			statusCode: 422,
			body: { message: 'Código de recuperação inválido.' },
		}).as('resetPassword')

		cy.visit('/reset-password?email=admin%40relogios.inc')
		cy.get('#reset-token').type('000000')
		cy.get('#reset-password').type('Password1!')
		cy.get('#reset-confirm').type('Password1!')
		cy.get('button[type="submit"]').click()
		cy.wait('@resetPassword')

		cy.contains('Código de recuperação inválido.').should('be.visible')
		cy.contains('Password alterada!').should('not.exist')
	})

	it('shows an error when the code has expired', () => {
		cy.intercept('POST', '/api/reset-password', {
			statusCode: 422,
			body: { message: 'O código expirou. Solicite um novo código.' },
		}).as('resetPasswordExpired')

		cy.visit('/reset-password?email=admin%40relogios.inc')
		cy.get('#reset-token').type('123456')
		cy.get('#reset-password').type('Password1!')
		cy.get('#reset-confirm').type('Password1!')
		cy.get('button[type="submit"]').click()
		cy.wait('@resetPasswordExpired')

		cy.contains('O código expirou. Solicite um novo código.').should('be.visible')
	})

	it('shows an error after exceeding the attempt limit', () => {
		cy.intercept('POST', '/api/reset-password', {
			statusCode: 422,
			body: { message: 'Excedeu o limite de tentativas. Solicite um novo código.' },
		}).as('resetPasswordLocked')

		cy.visit('/reset-password?email=admin%40relogios.inc')
		cy.get('#reset-token').type('999999')
		cy.get('#reset-password').type('Password1!')
		cy.get('#reset-confirm').type('Password1!')
		cy.get('button[type="submit"]').click()
		cy.wait('@resetPasswordLocked')

		cy.contains('Excedeu o limite de tentativas. Solicite um novo código.').should('be.visible')
	})

	it('shows the success screen and redirects to /login after a valid reset', () => {
		cy.intercept('POST', '/api/reset-password', {
			statusCode: 200,
			body: { message: 'A sua password foi alterada com sucesso!' },
		}).as('resetPasswordOk')

		cy.visit('/reset-password?email=admin%40relogios.inc')
		cy.get('#reset-token').type('123456')
		cy.get('#reset-password').type('Password1!')
		cy.get('#reset-confirm').type('Password1!')
		cy.get('button[type="submit"]').click()
		cy.wait('@resetPasswordOk')

		cy.contains('Password alterada!').should('be.visible')
		cy.contains('A sua password foi alterada com sucesso!').should('be.visible')
		cy.url({ timeout: 4000 }).should('include', '/login')
	})
})
