describe('Cart — Stock Limits', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')

		cy.request('POST', '/api/login', {
			email: 'admin@relogios.inc',
			password: 'password'
		}).then((response) => {
			const token = response.body.token
			window.localStorage.setItem('auth_token', token)
			cy.request({
				method: 'DELETE',
				url: '/api/cart',
				headers: { Authorization: `Bearer ${token}` }
			})
		})
	})

	it('does not add the item and shows an error toast when the backend rejects for insufficient stock', () => {
		cy.intercept('POST', '/api/cart', {
			statusCode: 422,
			body: { message: 'Stock insuficiente. Apenas existem 1 unidades disponíveis.' },
		}).as('addToCartFail')

		cy.visit('/homens')
		cy.get('[data-test="quick-add-homens-0"]').click()
		cy.wait('@addToCartFail')

		cy.get('[data-test="cart-error-toast"]').should('be.visible')
		cy.contains('Stock insuficiente. Apenas existem 1 unidades disponíveis.').should('be.visible')

		// The optimistic UI update must have been reverted — no phantom item in the cart
		cy.get('[data-test="cart-bubble"]').should('not.be.visible')
	})

	it('does not leave a stale item in the cart modal after a rejected add', () => {
		cy.intercept('POST', '/api/cart', {
			statusCode: 422,
			body: { message: 'Stock insuficiente. Apenas existem 1 unidades disponíveis.' },
		}).as('addToCartFail')

		cy.visit('/homens')
		cy.get('[data-test="quick-add-homens-0"]').click()
		cy.wait('@addToCartFail')

		cy.get('[data-test="cart-button"]').click()
		cy.get('[data-test="cart-checkout-button"]').should('not.exist')
	})

	it('recovers cleanly: a later successful add still works after a rejected attempt', () => {
		cy.intercept('POST', '/api/cart', {
			statusCode: 422,
			body: { message: 'Stock insuficiente. Apenas existem 1 unidades disponíveis.' },
		}).as('addToCartFail')

		cy.visit('/homens')
		cy.get('[data-test="quick-add-homens-0"]').click()
		cy.wait('@addToCartFail')
		cy.get('[data-test="cart-bubble"]').should('not.be.visible')

		// A later attempt that the backend accepts should work normally —
		// stubbed rather than relying on the real seeded product's current
		// stock level, which drifts as other tests/runs consume it.
		cy.intercept('POST', '/api/cart', {
			statusCode: 200,
			body: { message: 'Produto adicionado ao carrinho com sucesso.' },
		}).as('addToCartOk')
		// A successful add triggers a follow-up GET /api/cart to resync state —
		// stub it too, otherwise it hits the real (still empty) backend cart
		// and wipes out the optimistic item we just "added".
		cy.intercept('GET', '/api/cart', {
			statusCode: 200,
			body: {
				items: [
					{
						id: 1,
						quantity: 1,
						product: {
							id: 1,
							name: 'Stub Product',
							image: '',
							price: 10,
							discount_price: null,
						},
					},
				],
			},
		}).as('getCartOk')
		cy.get('[data-test="quick-add-homens-0"]').click()
		cy.wait('@addToCartOk')
		cy.wait('@getCartOk')
		cy.get('[data-test="cart-bubble"]').should('be.visible').and('contain', '1')
	})

	it('shows the same error toast from the product detail page', () => {
		cy.intercept('POST', '/api/cart', {
			statusCode: 422,
			body: { message: 'Stock insuficiente. Apenas existem 1 unidades disponíveis.' },
		}).as('addToCartFail')

		cy.visit('/homens')
		cy.get('[data-test^="product-card-homens-"]').first().find('a').first().click()

		cy.contains('adicionar ao carrinho', { matchCase: false }).click()
		cy.wait('@addToCartFail')

		cy.get('[data-test="cart-error-toast"]').should('be.visible')
		cy.get('[data-test="cart-bubble"]').should('not.be.visible')
	})
})
