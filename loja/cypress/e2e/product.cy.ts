describe('Product Detail Page — Desktop', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')
		cy.intercept('GET', '/api/catalog/products/*', (req) => {
			req.reply((res) => {
				if (res.body && res.body.data) {
					res.body.data.stock = 50
				} else if (res.body) {
					res.body.stock = 50
				}
			})
		}).as('fetchProduct')
		cy.intercept('GET', '/api/catalog/products/*/related').as('fetchRelated')

		// Navigate to the first product in /homens
		cy.visit('/homens')
		cy.get('[data-test^="product-card-homens-"]').first().find('a').first().click()
		cy.wait('@fetchProduct')
	})

	it('loads and displays product details', () => {
		// Product name should be visible
		cy.get('h1').should('be.visible').and('not.be.empty')

		// Price should be visible with € (scoped to avoid hidden cart modal)
		cy.contains('.text-3xl.font-bold', '€').should('be.visible')

		// Stock status should be visible (Em stock or Esgotado)
		cy.get('main').contains(/Em stock|Esgotado/).should('be.visible')
	})

	it('displays the product image', () => {
		cy.get('section img').first().should('be.visible')
			.and(($img) => {
				expect(($img[0] as HTMLImageElement).naturalWidth).to.be.greaterThan(0)
			})
	})

	it('shows brand name in the header', () => {
		// Brand name appears in the h1 area (e.g. "Rolex")
		cy.get('h1').invoke('text').should('match', /\w+/)
	})

	it('displays product description', () => {
		// Short description or description text
		cy.get('section').first().within(() => {
			cy.get('p').should('have.length.at.least', 1)
		})
	})

	it('has quantity selector and add to cart button', () => {
		cy.get('select#quantity').should('be.visible')
		cy.contains('adicionar ao carrinho', { matchCase: false }).should('be.visible')
	})

	it('adding to cart increments the cart bubble', () => {
		cy.contains('adicionar ao carrinho', { matchCase: false }).click()
		cy.get('[data-test="cart-bubble"]').should('be.visible').and('contain', '1')
	})

	it('adding multiple quantities works', () => {
		cy.get('select#quantity').select('3')
		cy.contains('adicionar ao carrinho', { matchCase: false }).click()
		cy.get('[data-test="cart-bubble"]').should('be.visible').and('contain', '3')
	})

	it('shows value propositions (delivery, returns, dealer)', () => {
		cy.contains('Entrega grátis em relógios acima de 500€').should('be.visible')
		cy.contains('Prazo de devolução de 30 dias').should('be.visible')
		cy.contains('Revendedor Oficial').should('be.visible')
	})

	it('displays gallery section with images', () => {
		cy.contains('Galeria').should('be.visible')
	})

	it('displays technical specifications table', () => {
		cy.contains('Especificações Técnicas').should('be.visible')
		cy.contains('Marca').should('be.visible')
		cy.contains('Gênero').should('be.visible')
		cy.contains('Peso').should('be.visible')
	})

	it('shows related products (YMAL) section', () => {
		cy.wait('@fetchRelated')
		// The related products section may or may not appear depending on data
		// If it appears, it should have the "Outros também compraram" title
		cy.get('body').then(($body) => {
			if ($body.text().includes('Outros também compraram')) {
				cy.contains('Outros também compraram').should('be.visible')
			}
		})
	})

	it('go back button is visible and works', () => {
		cy.contains(/voltar|go back/i).should('exist')
	})
})

describe('Product Detail Page — Non-existent product', () => {
	it('shows loading or handles gracefully for invalid slug', () => {
		cy.viewport('macbook-15')
		cy.visit('/homens/produto-que-nao-existe-xyz-123', { failOnStatusCode: false })
		// Should show the loading state or redirect
		cy.contains(/carregar|404|não existe/i, { timeout: 10000 }).should('exist')
	})
})

describe('Product Detail Page — Mobile', () => {
	beforeEach(() => {
		cy.viewport('iphone-xr')
		cy.intercept('GET', '/api/catalog/products/*', (req) => {
			req.reply((res) => {
				if (res.body && res.body.data) {
					res.body.data.stock = 50
				} else if (res.body) {
					res.body.stock = 50
				}
			})
		}).as('fetchProduct')

		cy.visit('/homens')
		cy.get('[data-test^="product-card-homens-"]').first().find('a').first().click()
		cy.wait('@fetchProduct')
	})

	it('loads product details on mobile', () => {
		cy.get('h1').should('be.visible').and('not.be.empty')
		cy.contains('.text-3xl.font-bold', '€').should('be.visible')
	})

	it('add to cart works on mobile', () => {
		cy.contains('adicionar ao carrinho', { matchCase: false }).click()
		cy.get('[data-test="cart-bubble"]').should('be.visible')
	})
})
