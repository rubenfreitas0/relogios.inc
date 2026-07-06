import { type Preview, setup } from '@storybook/vue3'
import { createPinia } from 'pinia'
import { createHead } from '@unhead/vue'
import type { App } from 'vue'
import '../src/style.css'

const pinia = createPinia()
const head = createHead()

setup((app: App) => {
  app.use(pinia)
  app.use(head)
})

const preview: Preview = {
  parameters: {
    actions: { argTypesRegex: '^on[A-Z].*' },
    controls: {
      matchers: {
        color: /(background|color)$/i,
        date: /Date$/,
      },
    },
  },
}

export default preview
