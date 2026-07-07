describe('Checkout — Email Verification Gate', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')

		// Register a brand new user, who is unverified by default
		const uniqueEmail = `unverified_e2e_${Date.now()}@relogios.inc`

		cy.request('POST', '/api/register', {
			firstname: 'Cypress',
			lastname: 'Unverified',
			email: uniqueEmail,
			password: 'CypressTest1!',
			password_confirmation: 'CypressTest1!',
		}).then((response) => {
			const token = response.body.token
			window.localStorage.setItem('auth_token', token)
		})

		cy.visit('/homens')
		cy.get('[data-test^="product-card-homens-"]').first().find('[data-test^="quick-add-"]').click()
		cy.get('[data-test="cart-bubble"]').should('contain', '1')
		cy.visit('/checkout')
	})

	it('disables the checkout button and shows the verification warning', () => {
		cy.contains('Deves verificar o teu email antes de efetuar a primeira compra.').should('be.visible')
		cy.get('[data-test="checkout-button"]').should('be.disabled')
	})

	it('shows the "não verificado" badge on the account profile tab', () => {
		cy.visit('/conta/perfil')
		cy.contains('Não verificado').should('be.visible')
	})

	it('resending the verification email shows a success message', () => {
		cy.intercept('POST', '/api/email/resend', {
			statusCode: 200,
			body: { message: 'Email de verificação reenviado.' },
		}).as('resend')

		cy.contains('button', 'Reenviar email de verificação').click()
		cy.wait('@resend')

		cy.contains('Email de verificação enviado!').should('be.visible')
	})

	it('shows an error message when resending fails', () => {
		cy.intercept('POST', '/api/email/resend', {
			statusCode: 500,
			body: { message: 'Erro ao enviar email.' },
		}).as('resendFail')

		cy.contains('button', 'Reenviar email de verificação').click()
		cy.wait('@resendFail')

		cy.contains('Erro ao enviar email.').should('be.visible')
	})

	it('blocks the purchase on the backend even if the button were force-clicked', () => {
		// Belt-and-braces: the API itself must reject checkout for unverified users,
		// not just the disabled button in the UI.
		cy.request('/api/shipping').then((shippingRes) => {
			const shippingMethodId = shippingRes.body[0].id

			cy.window().then((win) => {
				const token = win.localStorage.getItem('auth_token')
				cy.request({
					method: 'POST',
					url: '/api/orders',
					headers: { Authorization: `Bearer ${token}` },
					failOnStatusCode: false,
					body: {
						shipping_method_id: shippingMethodId,
						payment_method: 'credit_card',
						firstname: 'Cypress',
						lastname: 'Unverified',
						address_line1: 'Rua de Teste 1',
						city: 'Lisboa',
						postal_code: '1000-000',
						country: 'PT',
					},
				}).then((res) => {
					expect(res.status).to.eq(403)
				})
			})
		})
	})
})

describe('Verify Email Page', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')
	})

	it('shows an error immediately when the link is missing signature/expires', () => {
		cy.visit('/verificar-email/123')
		cy.contains('Erro na verificação').should('be.visible')
		cy.contains('Link de verificação inválido ou incompleto.').should('be.visible')
	})

	it('shows the success state and redirects to /checkout', () => {
		cy.intercept('GET', '/api/email/verify/*', {
			statusCode: 200,
			body: { message: 'Email verificado com sucesso! Já podes efetuar compras.' },
		}).as('verify')

		cy.visit('/verificar-email/123?signature=abc&expires=9999999999')
		cy.wait('@verify')

		cy.contains('Email verificado!').should('be.visible')
		cy.contains('Email verificado com sucesso! Já podes efetuar compras.').should('be.visible')
		cy.url({ timeout: 4000 }).should('include', '/checkout')
	})

	it('shows the error state for an invalid or expired link', () => {
		cy.intercept('GET', '/api/email/verify/*', {
			statusCode: 403,
			body: { message: 'Falha ao verificar email. O link pode ter expirado.' },
		}).as('verifyFail')

		cy.visit('/verificar-email/123?signature=abc&expires=1')
		cy.wait('@verifyFail')

		cy.contains('Erro na verificação').should('be.visible')
		cy.contains('Falha ao verificar email. O link pode ter expirado.').should('be.visible')
		cy.contains('a', 'Ir para a Loja').should('have.attr', 'href', '/')
	})
})
