import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '../stores/auth-store'

import AuthLayout from '../layouts/AuthLayout.vue'
import AppLayout from '../layouts/AppLayout.vue'

import RouteViewComponent from '../layouts/RouterBypass.vue'

const routes: Array<RouteRecordRaw> = [
  {
    path: '/:pathMatch(.*)*',
    redirect: { name: 'dashboard' },
  },
  {
    name: 'admin',
    path: '/',
    component: AppLayout,
    redirect: { name: 'dashboard' },
    meta: { requiresAuth: true },
    children: [
      {
        name: 'dashboard',
        path: 'dashboard',
        component: () => import('../pages/admin/dashboard/Dashboard.vue'),
      },
      {
        name: 'reports',
        path: 'relatorios',
        component: () => import('../pages/reports/ReportsPage.vue'),
      },
      {
        name: 'tickets',
        path: 'tickets',
        component: () => import('../pages/tickets/TicketsPage.vue'),
      },
      // — Custom pages (relogios.inc) —
      {
        name: 'products',
        path: 'produtos',
        component: () => import('../pages/products/ProductsPage.vue'),
      },
      {
        name: 'product-create',
        path: 'produtos/novo',
        component: () => import('../pages/products/ProductForm.vue'),
      },
      {
        name: 'product-edit',
        path: 'produtos/:id/editar',
        component: () => import('../pages/products/ProductForm.vue'),
        props: true,
      },
      {
        name: 'orders',
        path: 'encomendas',
        component: () => import('../pages/orders/OrdersPage.vue'),
      },
      {
        name: 'order-detail',
        path: 'encomendas/:orderNumber',
        component: () => import('../pages/orders/OrderDetail.vue'),
        props: true,
      },
      {
        name: 'brands',
        path: 'marcas',
        component: () => import('../pages/brands/BrandsPage.vue'),
      },
      {
        name: 'categories',
        path: 'categorias',
        component: () => import('../pages/categories/CategoriesPage.vue'),
      },
      {
        name: 'shipping',
        path: 'envios',
        component: RouteViewComponent,
        redirect: { name: 'shipping-methods' },
        children: [
          {
            name: 'shipping-methods',
            path: 'metodos',
            component: () => import('../pages/shipping/ShippingMethodsPage.vue'),
          },
          {
            name: 'shipping-zones',
            path: 'zonas',
            component: () => import('../pages/shipping/ShippingZonesPage.vue'),
          },
        ],
      },
      // — Demo pages (Vuestic) —
      {
        name: 'settings',
        path: 'settings',
        component: () => import('../pages/settings/Settings.vue'),
      },
      {
        name: 'preferences',
        path: 'preferences',
        component: () => import('../pages/preferences/Preferences.vue'),
      },
      {
        name: 'users',
        path: 'users',
        component: () => import('../pages/users/UsersPage.vue'),
      },
    ],
  },
  {
    path: '/auth',
    component: AuthLayout,
    children: [
      {
        name: 'login',
        path: 'login',
        component: () => import('../pages/auth/Login.vue'),
      },
      {
        name: 'recover-password',
        path: 'recover-password',
        component: () => import('../pages/auth/RecoverPassword.vue'),
      },
      {
        path: '',
        redirect: { name: 'login' },
      },
    ],
  },
  {
    name: '404',
    path: '/404',
    component: () => import('../pages/404.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    }
    if (to.hash) {
      return { el: to.hash, behavior: 'smooth' }
    } else {
      window.scrollTo(0, 0)
    }
  },
  routes,
})

// — Navigation guard: proteger rotas admin —
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (!authStore.isAuthenticated) {
    authStore.init()
  }

  if (to.matched.some((record) => record.meta.requiresAuth)) {
    if (!authStore.isAuthenticated) {
      next({ name: 'login', query: { redirect: to.fullPath } })
      return
    }
  }

  if (to.name === 'login' && authStore.isAuthenticated) {
    next({ name: 'dashboard' })
    return
  }

  next()
})

export default router
