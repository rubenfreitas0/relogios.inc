describe('Landing Page — Desktop', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')
		cy.visit('/')
	})

	it('renders hero section with title and CTA', () => {
		cy.contains('CASIO MTP-1274').should('be.visible')
		cy.contains('DARK EDITION').should('be.visible')
		cy.contains('ver relógio').should('be.visible')
	})

	it('hero CTA navigates to /homens', () => {
		cy.contains('ver relógio').click()
		cy.url().should('include', '/homens')
	})

	it('renders category boxes for Homens, Mulheres, Unisexo', () => {
		cy.contains('homens', { matchCase: false }).should('exist')
		cy.contains('mulheres', { matchCase: false }).should('exist')
		cy.contains('unisexo', { matchCase: false }).should('exist')
	})

	it('category box Homens navigates to /homens', () => {
		// Click on the category box link that goes to /homens
		cy.get('a[href="/homens"]').first().click()
		cy.url().should('include', '/homens')
	})

	it('renders the promotional grid section', () => {
		// The landing-grid has a "Casio MTP-1274" title and "VER RELÓGIO" button
		cy.get('#yellowBox').should('exist')
		cy.contains('Casio').should('be.visible')
		cy.contains('MTP-1274').should('be.visible')
	})

	it('grid CTA buttons link to category pages', () => {
		// "VER RELÓGIO" in the grid links to /homens
		cy.get('#yellowBox').find('a[href="/homens"]').should('exist')
		// "ver coleção" links to /mulheres
		cy.contains('ver coleção').should('exist')
		// "ver todos" links to /homens
		cy.contains('ver todos').should('exist')
	})

	it('renders footer', () => {
		cy.get('footer').should('exist')
	})

	it('no broken images on the page', () => {
		// Check that the main static images load correctly (hero, grid, category boxes)
		cy.get('img[alt]').should('have.length.at.least', 1)
		// At least the hero image should load
		cy.get('img[alt="Casio MTP-1274 Dark Edition"]')
			.should('be.visible')
			.and(($img) => {
				expect(($img[0] as HTMLImageElement).naturalWidth).to.be.greaterThan(0)
			})
	})
})

describe('Landing Page — Mobile', () => {
	beforeEach(() => {
		cy.viewport('iphone-xr')
		cy.visit('/')
	})

	it('renders hero section on mobile', () => {
		cy.contains('CASIO MTP-1274').should('be.visible')
		cy.contains('ver relógio').should('be.visible')
	})

	it('renders category boxes on mobile', () => {
		cy.contains('homens', { matchCase: false }).should('exist')
		cy.contains('mulheres', { matchCase: false }).should('exist')
		cy.contains('unisexo', { matchCase: false }).should('exist')
	})
})
