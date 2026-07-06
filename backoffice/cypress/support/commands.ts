/// <reference types="cypress" />
// ***********************************************
// This example commands.ts shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************

/* eslint-disable @typescript-eslint/no-namespace */
declare global {
  namespace Cypress {
    interface Chainable<Subject = any> {
      login(email?: string, password?: string): Chainable<Subject>
    }
  }
}
export {}

Cypress.Commands.add('login', (email = 'admin@relogios.inc', password = 'password') => {
  cy.session([email, password], () => {
    cy.visit('/auth/login')
    cy.get('input[type="email"]').type(email)
    cy.get('input[type="password"]').type(password)
    cy.get('button').contains('Entrar').click()
    cy.url().should('include', '/dashboard')
  })
})
