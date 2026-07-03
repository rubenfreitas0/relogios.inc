export interface INavigationRoute {
  name: string
  displayName: string
  meta?: { icon: string }
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
    {
      name: 'users',
      displayName: 'menu.users',
      meta: {
        icon: 'group',
      },
    },
    {
      name: 'products',
      displayName: 'menu.products',
      meta: {
        icon: 'watch',
      },
    },
    {
      name: 'orders',
      displayName: 'menu.orders',
      meta: {
        icon: 'shopping_cart',
      },
    },
    {
      name: 'brands',
      displayName: 'menu.brands',
      meta: {
        icon: 'sell',
      },
    },
    {
      name: 'categories',
      displayName: 'menu.categories',
      meta: {
        icon: 'category',
      },
    },
    {
      name: 'shipping',
      displayName: 'menu.shipping',
      meta: {
        icon: 'local_shipping',
      },
      children: [
        {
          name: 'shipping-methods',
          displayName: 'menu.shippingMethods',
        },
        {
          name: 'shipping-zones',
          displayName: 'menu.shippingZones',
        },
      ],
    },
    {
      name: 'reports',
      displayName: 'menu.reports',
      meta: {
        icon: 'bar_chart',
      },
    },
    {
      name: 'tickets',
      displayName: 'menu.tickets',
      meta: {
        icon: 'forum',
      },
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
