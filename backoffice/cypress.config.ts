import { defineConfig } from 'cypress'

export default defineConfig({
  e2e: {
    baseUrl: 'http://localhost:5174',
    defaultCommandTimeout: 10000,
    pageLoadTimeout: 20000,
    requestTimeout: 10000,
    responseTimeout: 10000,
    setupNodeEvents() {
      // implement node event listeners here
    },
  },

  component: {
    devServer: {
      framework: 'vue',
      bundler: 'vite',
    },
  },
})
