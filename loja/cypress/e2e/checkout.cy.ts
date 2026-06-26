describe('Desktop: Empty cart - customer', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')
	})
	it('wont see checkout button in cart.', () => {
		cy.visit('/')
		cy.get('[data-test="cart-button"]').click()
		cy.get('[data-test="cart-checkout-button"]').should('not.exist')
	})

	it('gets alert upon pressing pay in checkout without cart items.', () => {
		cy.visit('/checkout')
		cy.get('[data-test="checkout-button"]').click()
		cy.on('window:alert', (str) => {
			expect(str).to.equal('O carrinho está vazio!')
		})
	})
})

describe('Desktop: Filled cart - customer', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')
		cy.intercept('GET', '/api/shipping/calculate*').as('calculateShipping')
		cy.intercept('GET', '/api/shipping/calculate*country=DE*').as('calculateShippingDE')
		
		// Efetuar login programático com o utilizador seeded
		cy.request('POST', '/api/login', {
			email: 'admin@relogios.inc',
			password: 'password'
		}).then((response) => {
			const token = response.body.token
			window.localStorage.setItem('auth_token', token)
			cy.request({
				method: 'DELETE',
				url: '/api/cart',
				headers: {
					Authorization: `Bearer ${token}`
				}
			})
		})

		cy.visit('/homens')
		cy.contains('.group', 'Submariner').find('[data-test^="quick-add-"]').click()
		cy.contains('.group', 'Submariner').find('[data-test^="quick-add-"]').click()
		cy.get('[data-test="nav-unisexo"]').click()
		cy.contains('.group', 'F-91W').find('[data-test^="quick-add-"]').click()
		cy.get('[data-test="cart-bubble"]').contains(3)
		cy.get('[data-test="cart-button"]').click()
		cy.get('[data-test="cart-checkout-button"]').click()
		cy.get('[data-test="checkout-summary"]')
			.children()
			.should('contain', 'Submariner')
			.and('contain', 'F-91W')
			.and('be.visible')
	})
	it('doesnt see "Required" labels upon first checkout visit.', () => {
		cy.url().should('include', '/checkout')
		cy.get('[data-test="text-input-name"]')
			.contains('Required.')
			.should('not.be.visible')
		cy.get('[data-test="text-input-email"]')
			.contains('Required.')
			.should('not.be.visible')
		cy.get('[data-test="text-input-phone"]')
			.contains('Required.')
			.should('not.be.visible')
		cy.get('[data-test="text-input-address"]')
			.contains('Required.')
			.should('not.be.visible')
		cy.get('[data-test="text-input-zip"]')
			.contains('Required.')
			.should('not.be.visible')
		cy.get('[data-test="text-input-city"]')
			.contains('Required.')
			.should('not.be.visible')
		cy.get('[data-test="text-input-country"]')
			.contains('Required.')
			.should('not.be.visible')
	})
	it('types in correct data, pays and goes back to home with button', () => {
		cy.url().should('include', '/checkout')
		cy.get('[data-test="text-input-field-name"]').clear().type('Steven Bruben')
		cy.get('[data-test="text-input-field-email"]').clear().type('steve@bruben.com')
		cy.get('[data-test="text-input-field-address"]').clear().type('Alexanderplatz 12')
		cy.get('[data-test="text-input-field-zip"]').clear().type('10178')
		cy.get('[data-test="text-input-field-city"]').clear().type('Berlin')
		cy.get('[data-test="text-input-field-country"]').clear().type('DE')
		cy.wait('@calculateShippingDE')
		cy.get('[data-test="form-button-cash"]').click()
		cy.get('[data-test="form-text-area"]').type('Please send some stickers')
		cy.get('[data-test="checkout-button"]').click()
		cy.get('[data-test="checkout-success-modal"]')
			.children()
			.should('contain', 'Submariner')
			.and('contain', 'and 1 other item')
			.and('be.visible')
		cy.get('[data-test="checkout-success-modal-button"]').click()
		cy.url().should('eq', 'http://localhost:5173/')
	})
	it('doesnt fill form, clicks pay without success and sees required fields.', () => {
		cy.url().should('include', '/checkout')
		// Limpar o formulário (pode ter vindo com dados da morada default do utilizador)
		cy.get('[data-test="text-input-field-name"]').clear()
		cy.get('[data-test="text-input-field-email"]').clear()
		cy.get('[data-test="text-input-field-address"]').clear()
		cy.get('[data-test="text-input-field-zip"]').clear()
		cy.get('[data-test="text-input-field-city"]').clear()
		cy.get('[data-test="text-input-field-country"]').clear()
		
		cy.get('[data-test="checkout-button"]').click()
		cy.get('[data-test="checkout-success-modal"]').should('not.exist')
		cy.get('[data-test="text-input-name"]')
			.contains('Required.')
			.should('be.visible')
		cy.get('[data-test="text-input-email"]')
			.contains('Required.')
			.should('be.visible')
		cy.get('[data-test="text-input-phone"]')
			.contains('Required.')
			.should('not.be.visible')
		cy.get('[data-test="text-input-address"]')
			.contains('Required.')
			.should('be.visible')
		cy.get('[data-test="text-input-zip"]')
			.contains('Required.')
			.should('be.visible')
		cy.get('[data-test="text-input-city"]')
			.contains('Required.')
			.should('be.visible')
		cy.get('[data-test="text-input-country"]')
			.contains('Required.')
			.should('be.visible')
	})
	it('types in partially correct and some missing data, attempts to pay without success, fixes input and succeeds.', () => {
		cy.url().should('include', '/checkout')
		cy.get('[data-test="text-input-field-name"]').clear().type('Steven Bruben')
		cy.get('[data-test="text-input-field-email"]').clear().type('steve@bruben')
		cy.get('[data-test="text-input-field-address"]').clear().type('Alexanderplatz 12')
		cy.get('[data-test="text-input-field-zip"]').clear().type('10178')
		cy.get('[data-test="text-input-field-country"]').clear().type('10') // Fails /^[a-zA-Z]{2}$/
		cy.get('[data-test="form-button-emoney"]').click()
		cy.get('[data-test="form-text-area"]').type('Please send some stickers')
		cy.get('[data-test="checkout-button"]').click()
		cy.get('[data-test="checkout-success-modal"]').should('not.exist')

		cy.get('[data-test="text-input-name"]')
			.contains('Required.')
			.should('not.be.visible')

		cy.get('[data-test="text-input-email"]')
			.contains('Email inválido.')
			.should('be.visible')
		cy.get('[data-test="text-input-email"]')
			.contains('Required.')
			.should('not.be.visible')

		cy.get('[data-test="text-input-phone"]')
			.contains('Required.')
			.should('not.be.visible')

		cy.get('[data-test="text-input-address"]')
			.contains('Required.')
			.should('not.be.visible')

		cy.get('[data-test="text-input-zip"]')
			.contains('Required.')
			.should('not.be.visible')

		cy.get('[data-test="text-input-city"]')
			.contains('Required.')
			.should('be.visible')

		cy.get('[data-test="text-input-country"]')
			.contains('Código de 2 letras (ex: PT, ES, DE).')
			.should('be.visible')

		cy.get('[data-test="text-input-country"]')
			.contains('Required.')
			.should('not.be.visible')

		cy.get('[data-test="text-input-field-email"]')
			.clear()
			.type('steve@bruben.com')
		cy.get('[data-test="text-input-field-city"]').type('Berlin')
		cy.get('[data-test="text-input-field-country"]').clear().type('DE')
		cy.wait('@calculateShippingDE')
		cy.get('[data-test="checkout-button"]').click()

		cy.get('[data-test="checkout-success-modal"]')
			.children()
			.should('contain', 'Submariner')
			.and('contain', 'and 1 other item')
			.and('be.visible')
		cy.get('[data-test="checkout-success-modal-button"]').click()

		cy.url().should('eq', 'http://localhost:5173/')
	})
})

describe('Mobile: Empty cart - customer', () => {
	beforeEach(() => {
		cy.viewport('iphone-7')
	})
	it('wont see checkout button in cart.', () => {
		cy.visit('/')
		cy.get('[data-test="cart-button"]').click()
		cy.get('[data-test="cart-checkout-button"]').should('not.exist')
	})

	it('gets alert upon pressing pay in checkout without cart items.', () => {
		cy.visit('/checkout')
		cy.get('[data-test="checkout-button"]').click()
		cy.on('window:alert', (str) => {
			expect(str).to.equal('O carrinho está vazio!')
		})
	})
})

describe('Mobile: Filled cart - customer', () => {
	beforeEach(() => {
		cy.viewport('iphone-7')
		cy.intercept('GET', '/api/shipping/calculate*').as('calculateShipping')
		cy.intercept('GET', '/api/shipping/calculate*country=DE*').as('calculateShippingDE')
		
		// Efetuar login programático com o utilizador seeded
		cy.request('POST', '/api/login', {
			email: 'admin@relogios.inc',
			password: 'password'
		}).then((response) => {
			const token = response.body.token
			window.localStorage.setItem('auth_token', token)
			cy.request({
				method: 'DELETE',
				url: '/api/cart',
				headers: {
					Authorization: `Bearer ${token}`
				}
			})
		})

		cy.visit('/homens')
		cy.contains('.group', 'Submariner').find('[data-test^="quick-add-"]').click()
		cy.contains('.group', 'Submariner').find('[data-test^="quick-add-"]').click()
		cy.get('[data-test="hamburger"]').click()
		cy.get('[data-test="mobile-nav-unisexo"]').click()
		cy.contains('.group', 'F-91W').find('[data-test^="quick-add-"]').click()
		cy.get('[data-test="cart-bubble"]').contains(3)
		cy.get('[data-test="cart-button"]').click()
		cy.get('[data-test="cart-checkout-button"]').click()
		cy.get('[data-test="checkout-summary"]')
			.children()
			.should('contain', 'Submariner')
			.and('contain', 'F-91W')
			.and('be.visible')
	})
	it('doesnt see "Required" labels upon first checkout visit.', () => {
		cy.url().should('include', '/checkout')
		cy.get('[data-test="text-input-name"]')
			.contains('Required.')
			.should('not.be.visible')
		cy.get('[data-test="text-input-email"]')
			.contains('Required.')
			.should('not.be.visible')
		cy.get('[data-test="text-input-phone"]')
			.contains('Required.')
			.should('not.be.visible')
		cy.get('[data-test="text-input-address"]')
			.contains('Required.')
			.should('not.be.visible')
		cy.get('[data-test="text-input-zip"]')
			.contains('Required.')
			.should('not.be.visible')
		cy.get('[data-test="text-input-city"]')
			.contains('Required.')
			.should('not.be.visible')
		cy.get('[data-test="text-input-country"]')
			.contains('Required.')
			.should('not.be.visible')
	})
	it('types in correct data, pays and goes back to home with button', () => {
		cy.url().should('include', '/checkout')
		cy.get('[data-test="text-input-field-name"]').clear().type('Steven Bruben')
		cy.get('[data-test="text-input-field-email"]').clear().type('steve@bruben.com')
		cy.get('[data-test="text-input-field-address"]').clear().type('Alexanderplatz 12')
		cy.get('[data-test="text-input-field-zip"]').clear().type('10178')
		cy.get('[data-test="text-input-field-city"]').clear().type('Berlin')
		cy.get('[data-test="text-input-field-country"]').clear().type('DE')
		cy.wait('@calculateShippingDE')
		cy.get('[data-test="form-button-cash"]').click()
		cy.get('[data-test="form-text-area"]').type('Please send some stickers')
		cy.get('[data-test="checkout-button"]').click()
		cy.get('[data-test="checkout-success-modal"]')
			.children()
			.should('contain', 'Submariner')
			.and('contain', 'and 1 other item')
			.and('be.visible')
		cy.get('[data-test="checkout-success-modal-button"]').click()
		cy.url().should('eq', 'http://localhost:5173/')
	})
	it('doesnt fill form, clicks pay without success and sees required fields.', () => {
		cy.url().should('include', '/checkout')
		// Limpar o formulário
		cy.get('[data-test="text-input-field-name"]').clear()
		cy.get('[data-test="text-input-field-email"]').clear()
		cy.get('[data-test="text-input-field-address"]').clear()
		cy.get('[data-test="text-input-field-zip"]').clear()
		cy.get('[data-test="text-input-field-city"]').clear()
		cy.get('[data-test="text-input-field-country"]').clear()

		cy.get('[data-test="checkout-button"]').click()
		cy.url().should('include', '/checkout')
		cy.get('[data-test="checkout-success-modal"]').should('not.exist')
		cy.get('[data-test="text-input-name"]')
			.contains('Required.')
			.should('be.visible')
		cy.get('[data-test="text-input-email"]')
			.contains('Required.')
			.should('be.visible')
		cy.get('[data-test="text-input-phone"]')
			.contains('Required.')
			.should('not.be.visible')
		cy.get('[data-test="text-input-address"]')
			.contains('Required.')
			.should('be.visible')
		cy.get('[data-test="text-input-zip"]')
			.contains('Required.')
			.should('be.visible')
		cy.get('[data-test="text-input-city"]')
			.contains('Required.')
			.should('be.visible')
		cy.get('[data-test="text-input-country"]')
			.contains('Required.')
			.should('be.visible')
	})
	it('types in partially correct and some missing data, attempts to pay without success, fixes input and succeeds.', () => {
		cy.url().should('include', '/checkout')
		cy.get('[data-test="text-input-field-name"]').clear().type('Steven Bruben')
		cy.get('[data-test="text-input-field-email"]').clear().type('steve@bruben')
		cy.get('[data-test="text-input-field-address"]').clear().type('Alexanderplatz 12')
		cy.get('[data-test="text-input-field-zip"]').clear().type('10178')
		cy.get('[data-test="text-input-field-country"]').clear().type('10') // Fails /^[a-zA-Z]{2}$/
		cy.get('[data-test="form-button-emoney"]').click()
		cy.get('[data-test="form-text-area"]').type('Please send some stickers')
		cy.get('[data-test="checkout-button"]').click()
		cy.get('[data-test="checkout-success-modal"]').should('not.exist')

		cy.get('[data-test="text-input-name"]')
			.contains('Required.')
			.should('not.be.visible')

		cy.get('[data-test="text-input-email"]')
			.contains('Email inválido.')
			.should('be.visible')
		cy.get('[data-test="text-input-email"]')
			.contains('Required.')
			.should('not.be.visible')

		cy.get('[data-test="text-input-phone"]')
			.contains('Required.')
			.should('not.be.visible')

		cy.get('[data-test="text-input-address"]')
			.contains('Required.')
			.should('not.be.visible')

		cy.get('[data-test="text-input-zip"]')
			.contains('Required.')
			.should('not.be.visible')

		cy.get('[data-test="text-input-city"]')
			.contains('Required.')
			.should('be.visible')

		cy.get('[data-test="text-input-country"]')
			.contains('Código de 2 letras (ex: PT, ES, DE).')
			.should('be.visible')

		cy.get('[data-test="text-input-country"]')
			.contains('Required.')
			.should('not.be.visible')

		cy.get('[data-test="text-input-field-email"]')
			.clear()
			.type('steve@bruben.com')
		cy.get('[data-test="text-input-field-city"]').type('Berlin')
		cy.get('[data-test="text-input-field-country"]').clear().type('DE')
		cy.wait('@calculateShippingDE')
		cy.get('[data-test="checkout-button"]').click()

		cy.get('[data-test="checkout-success-modal"]')
			.children()
			.should('contain', 'Submariner')
			.and('contain', 'and 1 other item')
			.and('be.visible')
		cy.get('[data-test="checkout-success-modal-button"]').click()

		cy.url().should('eq', 'http://localhost:5173/')
	})
})
