import { defineStore } from 'pinia'

export const useNotificationsStore = defineStore('notifications', {
  state: () => {
    return {
      notifications: {
        newOrder: {
          name: 'Novas encomendas recebidas',
          isEnabled: true,
        },
        outOfStock: {
          name: 'Avisos de rutura de stock',
          isEnabled: true,
        },
        newTicket: {
          name: 'Novos tickets de suporte',
          isEnabled: false,
        },
        monthlyReports: {
          name: 'Relatórios de faturação mensais',
          isEnabled: true,
        },
      },
    }
  },
})
