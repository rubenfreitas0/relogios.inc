const discountedProduct = {
	id: 9001,
	name: 'Cypress Discount Watch',
	slug: 'cypress-discount-watch',
	short_description: 'Relógio de teste com desconto',
	description: 'Relógio de teste com desconto',
	price: '200.00',
	discount_price: '150.00',
	stock: 10,
	is_active: true,
	is_featured: false,
	gender: 'homens',
	brand: { id: 1, name: 'Casio', slug: 'casio' },
	category: { id: 1, name: 'Clássicos', slug: 'classicos' },
	images: [],
	primary_image: undefined,
}

const regularProduct = {
	id: 9002,
	name: 'Cypress Regular Watch',
	slug: 'cypress-regular-watch',
	short_description: 'Relógio de teste sem desconto',
	description: 'Relógio de teste sem desconto',
	price: '99.00',
	discount_price: null,
	stock: 10,
	is_active: true,
	is_featured: false,
	gender: 'homens',
	brand: { id: 2, name: 'Seiko', slug: 'seiko' },
	category: { id: 1, name: 'Clássicos', slug: 'classicos' },
	images: [],
	primary_image: undefined,
}

describe('Discount Price — Catalog Listing', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')
		cy.intercept('GET', '/api/catalog/products*', {
			statusCode: 200,
			body: {
				data: [discountedProduct, regularProduct],
				meta: { current_page: 1, last_page: 1, per_page: 12, total: 2 },
			},
		}).as('fetchProducts')
		cy.visit('/homens')
		cy.wait('@fetchProducts')
	})

	it('shows the original price struck through and the discount price highlighted', () => {
		cy.get(`[data-test="product-card-homens-${discountedProduct.id}"]`).within(() => {
			cy.contains('€200,00').should('have.class', 'line-through')
			cy.contains('€150,00').should('be.visible')
		})
	})

	it('shows a single, non-discounted price for products without a discount', () => {
		cy.get(`[data-test="product-card-homens-${regularProduct.id}"]`).within(() => {
			cy.contains('€99,00').should('be.visible').and('not.have.class', 'line-through')
			cy.get('.line-through').should('not.exist')
		})
	})
})

describe('Discount Price — Product Detail Page', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')
		cy.intercept('GET', '/api/catalog/products/cypress-discount-watch', {
			statusCode: 200,
			body: { data: discountedProduct },
		}).as('fetchProduct')
		cy.intercept('GET', '/api/catalog/products/*/related', { data: [] })
		cy.visit('/homens/cypress-discount-watch')
		cy.wait('@fetchProduct')
	})

	it('shows both the discount price and the struck-through original price', () => {
		cy.contains('€150,00').should('be.visible')
		cy.contains('€200,00').should('have.class', 'line-through')
	})

	it('adding the discounted product to the cart uses the discount price in the summary', () => {
		// Guest/local cart: addToCart only optimistically updates local state
		// when there is no auth token, so the discount_price carried on the
		// product object should already flow straight into the cart display.
		cy.contains('adicionar ao carrinho', { matchCase: false }).click()

		cy.get('[data-test="cart-button"]').click()
		cy.contains('€150,00').should('be.visible')
		cy.contains('€200,00').should('have.class', 'line-through')
	})
})
