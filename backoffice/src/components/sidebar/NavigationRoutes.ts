export interface INavigationRoute {
  name: string
  displayName: string
  meta: { icon: string }
  children?: INavigationRoute[]
}

export default {
  root: {
    name: '/',
    displayName: 'navigationRoutes.home',
  },
  routes: [
    {
      name: 'dashboard',
      displayName: 'menu.dashboard',
      meta: {
        icon: 'vuestic-iconset-dashboard',
      },
    },
    // — Relogios.inc custom pages —
    {
      name: 'products',
      displayName: 'Produtos',
      meta: {
        icon: 'watch',
      },
    },
    {
      name: 'orders',
      displayName: 'Encomendas',
      meta: {
        icon: 'shopping_cart',
      },
    },
    {
      name: 'brands',
      displayName: 'Marcas',
      meta: {
        icon: 'sell',
      },
    },
    {
      name: 'categories',
      displayName: 'Categorias',
      meta: {
        icon: 'category',
      },
    },
    {
      name: 'shipping',
      displayName: 'Envios',
      meta: {
        icon: 'local_shipping',
      },
      children: [
        {
          name: 'shipping-methods',
          displayName: 'Métodos',
        },
        {
          name: 'shipping-zones',
          displayName: 'Zonas',
        },
      ],
    },
    {
      name: 'users',
      displayName: 'menu.users',
      meta: {
        icon: 'group',
      },
    },
    {
      name: 'payments',
      displayName: 'menu.payments',
      meta: {
        icon: 'credit_card',
      },
      children: [
        {
          name: 'payment-methods',
          displayName: 'menu.payment-methods',
        },
        {
          name: 'pricing-plans',
          displayName: 'menu.pricing-plans',
        },
        {
          name: 'billing',
          displayName: 'menu.billing',
        },
      ],
    },
    {
      name: 'preferences',
      displayName: 'menu.preferences',
      meta: {
        icon: 'manage_accounts',
      },
    },
    {
      name: 'settings',
      displayName: 'menu.settings',
      meta: {
        icon: 'settings',
      },
    },
  ] as INavigationRoute[],
}
