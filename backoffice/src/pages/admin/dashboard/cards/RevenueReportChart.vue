<template>
  <div class="flex justify-center w-full h-full overflow-hidden relative">
    <canvas ref="canvas" style="max-width: 100%"></canvas>
  </div>
</template>

<script lang="ts" setup>
import { ref, onMounted, watch } from 'vue'
import { Chart, registerables } from 'chart.js'
import type { DashboardStats } from '../../../../stores/dashboard-store'

const props = defineProps<{
  byMonth: DashboardStats['revenue']['by_month']
}>()

Chart.register(...registerables)

const canvas = ref<HTMLCanvasElement | null>(null)
let chartInstance: Chart | null = null

const formatMoney = (amount: number) => {
  return new Intl.NumberFormat('pt-PT', {
    style: 'currency',
    currency: 'EUR',
    maximumFractionDigits: 0,
  }).format(amount)
}

const renderChart = () => {
  if (chartInstance) {
    chartInstance.destroy()
  }

  if (canvas.value) {
    const ctx = canvas.value.getContext('2d')
    if (ctx) {
      chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: props.byMonth.map((d) => d.label),
          datasets: [
            {
              label: 'Faturação (€)',
              data: props.byMonth.map((d) => d.total),
              backgroundColor: '#154EC1',
              borderRadius: 6,
              barThickness: 24,
            },
          ],
        },
        options: {
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false,
            },
            tooltip: {
              callbacks: {
                label: function (context) {
                  return `Faturação: ${formatMoney(Number(context.raw))}`
                },
              },
            },
          },
          scales: {
            x: {
              grid: {
                display: false,
              },
              border: {
                width: 0,
              },
            },
            y: {
              beginAtZero: true,
              ticks: {
                callback: function (value) {
                  return formatMoney(Number(value))
                },
              },
            },
          },
        },
      })
    }
  }
}

onMounted(() => {
  renderChart()
})

watch(
  () => props.byMonth,
  () => {
    renderChart()
  },
  { deep: true },
)
</script>

<style lang="scss" scoped>
canvas {
  position: absolute;
  height: 100%;
  width: 100%;
}
</style>
