describe('Auth — Login', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')
		cy.visit('/login')
	})

	it('renders the login page with form elements', () => {
		cy.contains('Bem-vindo de volta').should('be.visible')
		cy.get('#login-email').should('be.visible')
		cy.get('#login-password').should('be.visible')
		cy.contains('Entrar').should('be.visible')
	})

	it('has link to register page', () => {
		cy.contains('Cria uma aqui').should('be.visible')
		cy.contains('Cria uma aqui').click()
		cy.url().should('include', '/register')
	})

	it('has link to forgot password page', () => {
		cy.contains('Esqueceste-te?').should('be.visible')
		cy.contains('Esqueceste-te?').click()
		cy.url().should('include', '/forgot-password')
	})

	it('login with valid credentials redirects to homepage', () => {
		cy.get('#login-email').type('admin@relogios.inc')
		cy.get('#login-password').type('password')
		cy.get('button[type="submit"]').click()

		// Should redirect to homepage
		cy.url().should('eq', Cypress.config().baseUrl + '/', { timeout: 10000 })

		// Auth token should be stored
		cy.window().then((win) => {
			expect(win.localStorage.getItem('auth_token')).to.not.be.null
		})
	})

	it('login with invalid credentials shows error', () => {
		cy.get('#login-email').type('utilizador@invalido.com')
		cy.get('#login-password').type('password_errada')
		cy.get('button[type="submit"]').click()

		// Should stay on login page and show error
		cy.url().should('include', '/login')
		cy.get('.text-red-400').should('be.visible')
	})

	it('password toggle visibility works', () => {
		cy.get('#login-password').type('minhapassword')
		cy.get('#login-password').should('have.attr', 'type', 'password')

		// Click eye icon to show password
		cy.get('#login-password').parent().find('button').click()
		cy.get('#login-password').should('have.attr', 'type', 'text')

		// Click again to hide
		cy.get('#login-password').parent().find('button').click()
		cy.get('#login-password').should('have.attr', 'type', 'password')
	})
})

describe('Auth — Register', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')
		cy.visit('/register')
	})

	it('renders the register page with form elements', () => {
		cy.contains('Criar conta').should('be.visible')
		cy.get('#reg-firstname').should('be.visible')
		cy.get('#reg-lastname').should('be.visible')
		cy.get('#reg-email').should('be.visible')
		cy.get('#reg-phone').should('be.visible')
		cy.get('#reg-password').should('be.visible')
		cy.get('#reg-confirm').should('be.visible')
	})

	it('has link to login page', () => {
		cy.contains('Entra aqui').should('be.visible')
		cy.contains('Entra aqui').click()
		cy.url().should('include', '/login')
	})

	it('shows branding section with benefits', () => {
		cy.contains('RELOGIOS').should('be.visible')
		cy.contains('Acesso antecipado a novas coleções').should('be.visible')
		cy.contains('Gestão simplificada de encomendas').should('be.visible')
		cy.contains('Moradas guardadas para compra rápida').should('be.visible')
	})

	it('password mismatch shows error', () => {
		cy.get('#reg-password').type('MinhaPassword1!')
		cy.get('#reg-confirm').type('PasswordDiferente')
		cy.contains('Não coincidem.').should('be.visible')
	})

	it('password strength indicator works', () => {
		// Weak password
		cy.get('#reg-password').type('abc')
		// Should not show a strong label

		// Clear and type strong password
		cy.get('#reg-password').clear().type('MyStr0ng!Pass')
		cy.contains('Forte').should('be.visible')
	})

	it('register with existing email shows error', () => {
		cy.get('#reg-firstname').type('Teste')
		cy.get('#reg-lastname').type('Existente')
		cy.get('#reg-email').type('admin@relogios.inc')
		cy.get('#reg-password').type('Password1!')
		cy.get('#reg-confirm').type('Password1!')
		cy.get('button[type="submit"]').click()

		// Should show error (email already taken)
		cy.get('.text-red-400', { timeout: 10000 }).should('be.visible')
	})

	it('register with valid new data succeeds', () => {
		// Generate a unique email to avoid conflicts
		const uniqueEmail = `teste_e2e_${Date.now()}@relogios.inc`

		cy.get('#reg-firstname').type('Cypress')
		cy.get('#reg-lastname').type('Teste')
		cy.get('#reg-email').type(uniqueEmail)
		cy.get('#reg-password').type('CypressTest1!')
		cy.get('#reg-confirm').type('CypressTest1!')
		cy.get('button[type="submit"]').click()

		// Should redirect to homepage on success
		cy.url().should('eq', Cypress.config().baseUrl + '/', { timeout: 10000 })
	})
})

describe('Auth — Logout', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')

		cy.request('POST', '/api/login', {
			email: 'admin@relogios.inc',
			password: 'password'
		}).then((response) => {
			const token = response.body.token
			window.localStorage.setItem('auth_token', token)
		})

		cy.visit('/')
	})

	it('token exists right after logging in', () => {
		cy.window().then((win) => {
			expect(win.localStorage.getItem('auth_token')).to.not.be.null
		})
	})

	it('clicking logout in the user menu clears the token and redirects home', () => {
		cy.get('[data-test="user-menu"] button').click()
		cy.get('[data-test="nav-logout"]').click()

		cy.url().should('eq', Cypress.config().baseUrl + '/')
		// .should() (not .then()) so Cypress retries until clearAuth() has
		// actually run — avoids a race right after the redirect.
		cy.window().should((win) => {
			expect(win.localStorage.getItem('auth_token')).to.be.null
		})

		// The nav should now offer to log in again instead of showing the user menu
		cy.get('[data-test="user-menu"]').should('not.exist')
	})

	it('after logging out, protected pages redirect back to /login', () => {
		cy.get('[data-test="user-menu"] button').click()
		cy.get('[data-test="nav-logout"]').click()
		cy.url().should('eq', Cypress.config().baseUrl + '/')

		cy.visit('/conta')
		cy.url().should('include', '/login')
	})
})

describe('Auth — Logout Mobile', () => {
	it('clicking logout in the mobile menu clears the token', () => {
		cy.viewport('iphone-xr')

		cy.request('POST', '/api/login', {
			email: 'admin@relogios.inc',
			password: 'password'
		}).then((response) => {
			const token = response.body.token
			window.localStorage.setItem('auth_token', token)
		})

		cy.visit('/')
		cy.get('[data-test="hamburger"]').click()
		cy.get('[data-test="mobile-nav-logout"]').click()

		cy.url().should('eq', Cypress.config().baseUrl + '/')
		cy.window().should((win) => {
			expect(win.localStorage.getItem('auth_token')).to.be.null
		})
	})
})

describe('Auth — Login Mobile', () => {
	beforeEach(() => {
		cy.viewport('iphone-xr')
		cy.visit('/login')
	})

	it('renders login form on mobile', () => {
		cy.contains('Bem-vindo de volta').should('be.visible')
		cy.get('#login-email').should('be.visible')
		cy.get('#login-password').should('be.visible')
	})

	it('login works on mobile', () => {
		cy.get('#login-email').type('admin@relogios.inc')
		cy.get('#login-password').type('password')
		cy.get('button[type="submit"]').click()
		cy.url().should('eq', Cypress.config().baseUrl + '/', { timeout: 10000 })
	})
})
