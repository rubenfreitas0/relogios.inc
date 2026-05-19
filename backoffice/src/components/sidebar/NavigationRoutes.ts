export interface INavigationRoute {
  name: string
  displayName: string
  meta: { icon: string }
  children?: INavigationRoute[]
}

export default {
  root: {
    name: '/',
    displayName: 'Início',
  },
  routes: [
    {
      name: 'dashboard',
      displayName: 'Dashboard',
      meta: {
        icon: 'vuestic-iconset-dashboard',
      },
    },
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
  ] as INavigationRoute[],
}
