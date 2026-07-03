<template>
  <VaCard class="flex flex-col">
    <VaCardTitle class="flex items-start justify-between">
      <h1 class="card-title text-secondary font-bold uppercase">Relatório de Faturação</h1>
      <div class="flex gap-2">
        <VaSelect v-slot="{ value }" v-model="selectedMonth" preset="small" :options="monthLabels" class="w-36">
          <span class="text-xs">{{ value }}</span>
        </VaSelect>
        <VaButton class="h-2" size="small" preset="primary" @click="exportAsCSV">Exportar</VaButton>
      </div>
    </VaCardTitle>
    <VaCardContent class="flex flex-col-reverse md:flex-row md:items-center justify-between gap-5 h-full">
      <section class="flex flex-col items-start w-full sm:w-1/3 md:w-2/5 lg:w-1/4 gap-2 md:gap-8 pl-4">
        <div>
          <p class="text-xl font-semibold">{{ formatMoney(totalRevenue) }}</p>
          <p class="whitespace-nowrap mt-2">Faturação (Últimos 6 meses)</p>
        </div>
        <div class="flex flex-col sm:flex-col gap-2 md:gap-8 w-full">
          <div>
            <div class="flex items-center">
              <span class="inline-block w-2 h-2 mr-2 rounded-full" :style="{ backgroundColor: '#154EC1' }"></span>
              <span class="text-secondary text-xs">Faturação no mês</span>
            </div>
            <div class="mt-2 text-xl font-semibold">{{ formatMoney(selectedMonthData?.total ?? 0) }}</div>
          </div>
          <div>
            <div class="flex items-center">
              <span class="inline-block w-2 h-2 mr-2 rounded-full" :style="{ backgroundColor: '#ffd54f' }"></span>
              <span class="text-secondary text-xs">Encomendas no mês</span>
            </div>
            <div class="mt-2 text-xl font-semibold">{{ selectedMonthData?.orders ?? 0 }} encomendas</div>
          </div>
        </div>
      </section>
      <RevenueReportChart
        class="w-full md:w-3/5 lg:w-3/4 h-full min-h-72 sm:min-h-32 pt-4"
        :by-month="revenue.by_month"
      />
    </VaCardContent>
  </VaCard>
</template>

<script lang="ts" setup>
import { ref, computed, watch } from 'vue'
import { VaCard } from 'vuestic-ui'
import RevenueReportChart from './RevenueReportChart.vue'
import { downloadAsCSV } from '../../../../services/toCSV'
import type { DashboardStats } from '../../../../stores/dashboard-store'

const { revenue } = defineProps<{
  revenue: DashboardStats['revenue']
}>()

const formatMoney = (amount: number) => {
  return new Intl.NumberFormat('pt-PT', {
    style: 'currency',
    currency: 'EUR',
  }).format(amount)
}

const monthLabels = computed(() => {
  return revenue.by_month.map((item) => item.label)
})

const selectedMonth = ref('')

watch(
  monthLabels,
  (newLabels) => {
    if (newLabels.length > 0 && !selectedMonth.value) {
      selectedMonth.value = newLabels[newLabels.length - 1]
    }
  },
  { immediate: true },
)

const selectedMonthData = computed(() => {
  return revenue.by_month.find((item) => item.label === selectedMonth.value)
})

const totalRevenue = computed(() => {
  return revenue.by_month.reduce((acc, item) => acc + item.total, 0)
})

const exportAsCSV = () => {
  const dataToExport = revenue.by_month.map((item) => ({
    Mês: item.label,
    Faturação: item.total,
    Encomendas: item.orders,
  }))
  downloadAsCSV(dataToExport, 'relatorio-faturacao')
}
</script>
