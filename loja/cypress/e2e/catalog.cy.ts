describe('Catalog — Desktop: Homens', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')
		cy.visit('/homens')
	})

	it('displays the category header', () => {
		cy.contains('Homens').should('be.visible')
		cy.contains('Coleção').should('be.visible')
	})

	it('loads and displays products from the API', () => {
		// Wait for at least one product card to appear
		cy.get('[data-test^="product-card-homens-"]').should('have.length.at.least', 1)
	})

	it('each product card shows brand, name, and price', () => {
		cy.get('[data-test^="product-card-homens-"]').first().within(() => {
			// Brand name (e.g. "Rolex", "Omega", "Tag Heuer")
			cy.get('h2').should('not.be.empty')
			// Product name
			cy.get('h3').should('not.be.empty')
			// Price (contains €)
			cy.contains('€').should('be.visible')
		})
	})

	it('clicking a product card navigates to product detail page', () => {
		cy.get('[data-test^="product-card-homens-"]').first().find('a').first().click()
		cy.url().should('match', /\/homens\/[\w-]+/)
	})

	it('quick-add button adds item to cart', () => {
		cy.get('[data-test="quick-add-homens-0"]').should('exist')
		cy.get('[data-test="quick-add-homens-0"]').click()
		cy.get('[data-test="cart-bubble"]').should('be.visible').and('contain', '1')
	})

	it('results counter is visible and shows count', () => {
		cy.contains(/\d+ resultado/).should('be.visible')
	})
})

describe('Catalog — Desktop: Mulheres', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')
		cy.visit('/mulheres')
	})

	it('loads and displays products for mulheres', () => {
		cy.contains('Mulheres').should('be.visible')
		cy.get('[data-test^="product-card-mulheres-"]').should('have.length.at.least', 1)
	})

	it('clicking a product card navigates to product detail', () => {
		cy.get('[data-test^="product-card-mulheres-"]').first().find('a').first().click()
		cy.url().should('match', /\/mulheres\/[\w-]+/)
	})
})

describe('Catalog — Desktop: Unisexo', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')
		cy.visit('/unisexo')
	})

	it('loads and displays products for unisexo', () => {
		cy.contains('Unisexo').should('be.visible')
		cy.get('[data-test^="product-card-unisexo-"]').should('have.length.at.least', 1)
	})
})

describe('Catalog — Desktop: Filters', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')
		cy.intercept('GET', '/api/catalog/products*').as('fetchProducts')
		cy.visit('/homens')
		cy.wait('@fetchProducts')
	})

	it('shows filter sidebar with brands and price ranges', () => {
		cy.contains('Filtros').should('be.visible')
		cy.contains('Marca').should('be.visible')
		cy.contains('Gama de Preço').should('be.visible')
	})

	it('filtering by brand updates the results', () => {
		// Get initial count
		cy.contains(/\d+ resultado/).invoke('text').then((initialText) => {
			// Click on a brand filter (e.g. "Rolex")
			cy.contains('label', 'Rolex').click()
			cy.wait('@fetchProducts')

			// The filter chip should appear
			cy.contains('Rolex').should('be.visible')
		})
	})

	it('clearing filters restores all products', () => {
		// Apply a filter
		cy.contains('label', 'Rolex').click()
		cy.wait('@fetchProducts')

		// Clear all filters
		cy.contains('Limpar').click()
		cy.wait('@fetchProducts')

		// No active filter chips
		cy.contains('Sem filtros ativos').should('be.visible')
	})

	it('filtering by price range works', () => {
		// Click on a price range
		cy.contains('button', 'Até €100').click()
		cy.wait('@fetchProducts')

		// Price range chip should appear
		cy.contains('Até €100').should('be.visible')
	})

	it('removing a filter chip updates results', () => {
		// Apply brand filter
		cy.contains('label', 'Rolex').click()
		cy.wait('@fetchProducts')

		// Remove the chip by clicking the × button
		cy.get('.rounded-full').contains('Rolex').parent().find('button').click()
		cy.wait('@fetchProducts')

		cy.contains('Sem filtros ativos').should('be.visible')
	})
})

describe('Catalog — Mobile', () => {
	beforeEach(() => {
		cy.viewport('iphone-xr')
		cy.visit('/homens')
	})

	it('loads and displays products on mobile', () => {
		cy.get('[data-test^="product-card-homens-"]').should('have.length.at.least', 1)
	})

	it('filters sidebar is hidden on mobile', () => {
		// The sidebar uses lg:flex, so it should be hidden
		cy.contains('Filtros').should('not.be.visible')
	})

	it('quick-add button works on mobile', () => {
		cy.get('[data-test="quick-add-homens-0"]').click()
		cy.get('[data-test="cart-bubble"]').should('be.visible')
	})
})
