// ***********************************************************
// This example support/e2e.ts is processed and
// loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************

// Import commands.js using ES2015 syntax:
import './commands'

// Ignore uncaught exceptions from the application under test
Cypress.on('uncaught:exception', () => false)

// O Vite faz a otimização de dependências (pre-bundling) só na primeira vez
// que o dev server recebe um pedido depois de arrancar, o que pode demorar
// bastante mais do que o defaultCommandTimeout. Este "aquecimento" paga esse
// custo uma única vez antes dos testes cronometrados começarem, em vez de
// forçarmos um timeout enorme em todos os cy.get()/cy.wait() da suite.
before(() => {
  cy.visit('/auth/login', { timeout: 60000 })
})
