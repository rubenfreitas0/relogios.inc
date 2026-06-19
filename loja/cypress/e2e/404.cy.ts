describe('404 Page', () => {
	beforeEach(() => {
		cy.viewport('macbook-15')
	})

	it('shows 404 page for non-existent route', () => {
		cy.visit('/pagina-que-nao-existe', { failOnStatusCode: false })
		cy.contains('404').should('be.visible')
		cy.contains('A página que procura não existe ou foi movida').should('be.visible')
	})

	it('shows 404 page on explicit /404 route', () => {
		cy.visit('/404')
		cy.contains('404').should('be.visible')
	})

	it('navigates back to homepage via button', () => {
		cy.visit('/rota-invalida-xyz', { failOnStatusCode: false })
		cy.contains('404').should('be.visible')
		cy.contains('Voltar ao Início').click()
		cy.url().should('eq', Cypress.config().baseUrl + '/')
	})
})

describe('404 Page — Mobile', () => {
	beforeEach(() => {
		cy.viewport('iphone-xr')
	})

	it('shows 404 page on mobile for non-existent route', () => {
		cy.visit('/abc-xyz-123', { failOnStatusCode: false })
		cy.contains('404').should('be.visible')
		cy.contains('Voltar ao Início').should('be.visible')
	})
})
