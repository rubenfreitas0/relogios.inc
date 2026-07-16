<template>
  <div class="reports-page">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <h1 class="page-title font-bold text-2xl">Relatórios de Vendas</h1>
      <div class="flex items-center gap-2">
        <span class="text-sm text-secondary">Período:</span>
        <VaSelect v-model="startYear" :options="availableYears" class="w-24" @update:modelValue="applyFilters" />
        <span class="text-secondary">até</span>
        <VaSelect v-model="endYear" :options="availableYears" class="w-24" @update:modelValue="applyFilters" />
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-24">
      <VaProgressCircle indeterminate size="large" />
    </div>

    <div v-else-if="error" class="mb-4">
      <VaAlert color="danger" closeable>
        {{ error }}
      </VaAlert>
    </div>

    <div v-else class="space-y-6">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <VaCard>
          <VaCardContent class="flex flex-col p-4">
            <span class="text-xs text-secondary uppercase font-bold tracking-wider mb-1"
              >Faturação Total (Período)</span
            >
            <span class="text-2xl font-bold text-primary mb-1">{{ formatMoney(filteredSummary.totalRevenue) }}</span>
            <span class="text-xs text-success">Desde {{ startYear }}</span>
          </VaCardContent>
        </VaCard>

        <VaCard>
          <VaCardContent class="flex flex-col p-4">
            <span class="text-xs text-secondary uppercase font-bold tracking-wider mb-1">Volume de Encomendas</span>
            <span class="text-2xl font-bold text-primary mb-1">{{ formatNumber(filteredSummary.totalOrders) }}</span>
            <span class="text-xs text-success">Total processado</span>
          </VaCardContent>
        </VaCard>

        <VaCard>
          <VaCardContent class="flex flex-col p-4">
            <span class="text-xs text-secondary uppercase font-bold tracking-wider mb-1">Crescimento Médio Anual</span>
            <span class="text-2xl font-bold text-primary mb-1">{{ filteredSummary.avgGrowth }}%</span>
            <span class="text-xs text-success">+15% objetivo anual</span>
          </VaCardContent>
        </VaCard>

        <VaCard>
          <VaCardContent class="flex flex-col p-4">
            <span class="text-xs text-secondary uppercase font-bold tracking-wider mb-1">Ticket Médio Global</span>
            <span class="text-2xl font-bold text-primary mb-1">{{ formatMoney(filteredSummary.avgTicket) }}</span>
            <span class="text-xs text-success">Por encomenda</span>
          </VaCardContent>
        </VaCard>
      </div>

      <!-- Yearly Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <VaCard class="p-4 min-h-[350px]">
          <h2 class="text-md font-bold mb-4 text-secondary">Evolução de Faturação Anual (€)</h2>
          <div class="relative h-[250px] w-full">
            <canvas ref="revenueChartRef"></canvas>
          </div>
        </VaCard>

        <VaCard class="p-4 min-h-[350px]">
          <h2 class="text-md font-bold mb-4 text-secondary">Volume de Encomendas por Ano</h2>
          <div class="relative h-[250px] w-full">
            <canvas ref="ordersChartRef"></canvas>
          </div>
        </VaCard>
      </div>

      <!-- Seasonality Chart -->
      <VaCard class="p-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
          <h2 class="text-md font-bold text-secondary">Sazonalidade Mensal Recente (Evolução por Mês)</h2>
          <div class="flex items-center gap-2">
            <span class="text-sm text-secondary">Filtrar Ano:</span>
            <VaSelect
              v-model="selectedMonthlyYear"
              :options="availableMonthlyYears"
              class="w-32"
              @update:modelValue="renderMonthlyChart"
            />
          </div>
        </div>
        <div class="relative h-[280px] w-full">
          <canvas ref="monthlyChartRef"></canvas>
        </div>
      </VaCard>

      <!-- Yearly Data Table -->
      <VaCard>
        <VaCardContent class="p-4">
          <h2 class="text-md font-bold mb-4 text-secondary">Resumo Anual Detalhado</h2>
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-[var(--va-background-element)] text-secondary text-xs uppercase font-bold">
                  <th class="py-3 px-4">Ano</th>
                  <th class="py-3 px-4 text-right">Faturação</th>
                  <th class="py-3 px-4 text-right">Encomendas</th>
                  <th class="py-3 px-4 text-right">Crescimento</th>
                  <th class="py-3 px-4 text-right">Ticket Médio</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="row in filteredYearlyData"
                  :key="row.year"
                  class="border-b border-[var(--va-background-element)] hover:bg-[var(--va-background-element)] text-sm transition-colors"
                >
                  <td class="py-3 px-4 font-semibold">{{ row.year }}</td>
                  <td class="py-3 px-4 text-right font-mono">{{ formatMoney(row.revenue) }}</td>
                  <td class="py-3 px-4 text-right font-mono">{{ formatNumber(row.orders) }}</td>
                  <td class="py-3 px-4 text-right font-mono">
                    <span :class="row.growth >= 0 ? 'text-success' : 'text-danger'">
                      {{ row.growth > 0 ? '+' : '' }}{{ row.growth }}%
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right font-mono">{{ formatMoney(row.average_ticket) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </VaCardContent>
      </VaCard>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, onMounted, computed, nextTick } from 'vue'
import { reportsApi } from '../../services/api'
import { Chart, registerables } from 'chart.js'
import { useColors } from 'vuestic-ui'

Chart.register(...registerables)

interface YearlyReportItem {
  year: number
  revenue: number
  orders: number
  growth: number
  average_ticket: number
}

interface MonthlyReportItem {
  year: number
  month: number
  month_name: string
  label: string
  revenue: number
  orders: number
  average_ticket: number
}

interface SummaryData {
  total_revenue_since_start: number
  total_orders_since_start: number
  average_annual_growth: number
}

interface ReportsResponse {
  yearly: YearlyReportItem[]
  monthly: MonthlyReportItem[]
  summary: SummaryData
}

const { getColor } = useColors()

const loading = ref(true)
const error = ref<string | null>(null)

// Raw data from API
const yearlyData = ref<YearlyReportItem[]>([])
const monthlyData = ref<MonthlyReportItem[]>([])
const apiSummary = ref<SummaryData | null>(null)

// Filter years
const startYear = ref(2013)
const endYear = ref(new Date().getFullYear())
const availableYears = ref<number[]>([])

// Monthly year filter
const selectedMonthlyYear = ref<number>(new Date().getFullYear())

// Canvas references
const revenueChartRef = ref<HTMLCanvasElement | null>(null)
const ordersChartRef = ref<HTMLCanvasElement | null>(null)
const monthlyChartRef = ref<HTMLCanvasElement | null>(null)

// Chart instances
let revenueChartInstance: Chart | null = null
let ordersChartInstance: Chart | null = null
let monthlyChartInstance: Chart | null = null

const formatMoney = (amount: number) => {
  return new Intl.NumberFormat('pt-PT', {
    style: 'currency',
    currency: 'EUR',
  }).format(amount)
}

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('pt-PT').format(num)
}

// Filtered lists
const filteredYearlyData = computed(() => {
  return yearlyData.value.filter(
    (item) => Number(item.year) >= Number(startYear.value) && Number(item.year) <= Number(endYear.value),
  )
})

const availableMonthlyYears = computed(() => {
  const years = new Set(monthlyData.value.map((item) => item.year))
  return Array.from(years).sort((a, b) => b - a)
})

const filteredSummary = computed(() => {
  const data = filteredYearlyData.value
  if (data.length === 0) {
    return { totalRevenue: 0, totalOrders: 0, avgGrowth: '0', avgTicket: 0 }
  }
  const totalRevenue = data.reduce((sum, item) => sum + item.revenue, 0)
  const totalOrders = data.reduce((sum, item) => sum + item.orders, 0)
  // Average growth excluding first year if startYear is 2013 (which is 0.0)
  const growthItems = data.filter((item) => item.year !== 2013)
  const avgGrowth =
    growthItems.length > 0
      ? (growthItems.reduce((sum, item) => sum + item.growth, 0) / growthItems.length).toFixed(1)
      : '0.0'

  const avgTicket = totalOrders > 0 ? totalRevenue / totalOrders : 0

  return {
    totalRevenue,
    totalOrders,
    avgGrowth,
    avgTicket,
  }
})

// Fetch data
async function loadReports() {
  loading.value = true
  error.value = null
  try {
    const res = await reportsApi.get()
    const data = res.data as ReportsResponse
    yearlyData.value = data.yearly
    monthlyData.value = data.monthly
    apiSummary.value = data.summary

    // Populate available years from API data
    const years = data.yearly.map((item) => item.year)
    availableYears.value = years.sort((a, b) => a - b)
    if (years.length > 0) {
      startYear.value = Math.min(...years)
      endYear.value = Math.max(...years)
    }

    // Set default selected monthly year to latest available
    const mYears = data.monthly.map((item) => item.year)
    if (mYears.length > 0) {
      selectedMonthlyYear.value = Math.max(...mYears)
    }

    // Desligar o loading ANTES de desenhar, para que os <canvas> já existam no DOM
    loading.value = false
    await nextTick()
    renderAllCharts()
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erro ao obter dados de relatórios.'
    loading.value = false
  }
}

// Chart rendering
function renderAllCharts() {
  renderRevenueChart()
  renderOrdersChart()
  renderMonthlyChart()
}

function renderRevenueChart() {
  if (revenueChartInstance) {
    revenueChartInstance.destroy()
  }
  if (!revenueChartRef.value) return

  const ctx = revenueChartRef.value.getContext('2d')
  if (!ctx) return

  const data = filteredYearlyData.value

  revenueChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: data.map((d) => String(d.year)),
      datasets: [
        {
          label: 'Faturação (€)',
          data: data.map((d) => d.revenue),
          borderColor: getColor('primary'),
          backgroundColor: getColor('primary') + '15',
          fill: true,
          tension: 0.3,
          borderWidth: 3,
          pointBackgroundColor: getColor('primary'),
          pointRadius: 4,
        },
      ],
    },
    options: {
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => `Faturação: ${formatMoney(Number(ctx.raw))}`,
          },
        },
      },
      scales: {
        x: { grid: { display: false } },
        y: {
          beginAtZero: true,
          suggestedMax: 100000,
          ticks: {
            callback: (val) => formatMoney(Number(val)),
          },
        },
      },
    },
  })
}

function renderOrdersChart() {
  if (ordersChartInstance) {
    ordersChartInstance.destroy()
  }
  if (!ordersChartRef.value) return

  const ctx = ordersChartRef.value.getContext('2d')
  if (!ctx) return

  const data = filteredYearlyData.value

  ordersChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: data.map((d) => String(d.year)),
      datasets: [
        {
          label: 'Encomendas',
          data: data.map((d) => d.orders),
          backgroundColor: getColor('success'),
          borderRadius: 4,
          barThickness: 20,
        },
      ],
    },
    options: {
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
      },
      scales: {
        x: { grid: { display: false } },
        y: {
          beginAtZero: true,
        },
      },
    },
  })
}

function renderMonthlyChart() {
  if (monthlyChartInstance) {
    monthlyChartInstance.destroy()
  }
  if (!monthlyChartRef.value) return

  const ctx = monthlyChartRef.value.getContext('2d')
  if (!ctx) return

  const yearData = monthlyData.value.filter((d) => Number(d.year) === Number(selectedMonthlyYear.value))

  monthlyChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: yearData.map((d) => d.month_name),
      datasets: [
        {
          label: 'Faturação (€)',
          data: yearData.map((d) => d.revenue),
          backgroundColor: getColor('info'),
          borderRadius: 4,
        },
      ],
    },
    options: {
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => `Faturação: ${formatMoney(Number(ctx.raw))}`,
          },
        },
      },
      scales: {
        x: { grid: { display: false } },
        y: {
          beginAtZero: true,
          suggestedMax: 100000,
          ticks: {
            callback: (val) => formatMoney(Number(val)),
          },
        },
      },
    },
  })
}

function applyFilters() {
  if (startYear.value > endYear.value) {
    const temp = startYear.value
    startYear.value = endYear.value
    endYear.value = temp
  }
  nextTick(() => {
    renderRevenueChart()
    renderOrdersChart()
  })
}

onMounted(() => {
  loadReports()
})
</script>

<style lang="scss" scoped>
.reports-page {
  padding: 0.5rem;
}
</style>
