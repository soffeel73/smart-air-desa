<script setup>
import { ref, onMounted, computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import { ArrowLeftIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline'
import { useRouter } from 'vue-router'

const router = useRouter()
const isLoading = ref(true)
const chartsData = ref({
  labels: [],
  data: {
    pemakaian_air: [],
    tren_tunggakan: [],
    tunggakan_per_wilayah: [],
    pelanggan_per_wilayah: []
  }
})

const year = ref(new Date().getFullYear())
const API_BASE = '/api/dashboard.php'

const fetchData = async () => {
  isLoading.value = true
  try {
    const response = await fetch(`${API_BASE}?action=charts&year=${year.value}`)
    const res = await response.json()
    if (res.success) {
      chartsData.value = res
    }
  } catch (error) {
    console.error('Failed to fetch chart data:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchData()
})

// Summary Calculations
const totalKonsumsi = computed(() => chartsData.value.data.pemakaian_air.reduce((a, b) => a + b, 0))
const totalTunggakan = computed(() => chartsData.value.data.tren_tunggakan.reduce((a, b) => a + b, 0))
const totalPelanggan = computed(() => chartsData.value.data.pelanggan_per_wilayah.reduce((a, b) => a + b.total, 0))

// 1. Line Chart: Tren Pemakaian Air
const lineOptions = computed(() => ({
  chart: {
    id: 'pemakaian-air',
    toolbar: { show: false },
    animations: { enabled: true, easing: 'easeinout', speed: 800 }
  },
  stroke: { curve: 'smooth', width: 3 },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.45,
      opacityTo: 0.05,
      stops: [50, 100, 100]
    }
  },
  colors: ['#0d9488'], // Teal-600
  xaxis: { categories: chartsData.value.labels },
  tooltip: { theme: 'light' },
  dataLabels: { enabled: false }
}))

const lineSeries = computed(() => [
  { name: 'Pemakaian Air (m³)', data: chartsData.value.data.pemakaian_air }
])

// 2. Bar Chart: Tunggakan per Wilayah
const barOptions = computed(() => ({
  chart: { id: 'tunggakan-wilayah', toolbar: { show: false } },
  plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '70%' } },
  colors: ['#f97316'], // Orange-500
  xaxis: {
    categories: chartsData.value.data.tunggakan_per_wilayah.map(i => i.wilayah),
    labels: {
      formatter: (val) => 'Rp ' + val.toLocaleString('id-ID')
    }
  },
  dataLabels: {
    enabled: true,
    formatter: (val) => 'Rp ' + val.toLocaleString('id-ID'),
    style: { fontSize: '12px' }
  }
}))

const barSeries = computed(() => [
  { name: 'Total Tunggakan', data: chartsData.value.data.tunggakan_per_wilayah.map(i => i.total) }
])

// 3. Doughnut Chart: Distribusi Pelanggan
const doughnutOptions = computed(() => ({
  chart: { id: 'distribusi-pelanggan' },
  labels: chartsData.value.data.pelanggan_per_wilayah.map(i => i.wilayah),
  colors: ['#0d9488', '#0ea5e9', '#6366f1', '#8b5cf6', '#ec4899', '#f43f5e'],
  legend: { position: 'right' },
  plotOptions: {
    pie: {
      donut: {
        size: '70%',
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total',
            formatter: () => totalPelanggan.value
          }
        }
      }
    }
  }
}))

const doughnutSeries = computed(() => chartsData.value.data.pelanggan_per_wilayah.map(i => i.total))

const exportChart = (chartId) => {
  const chartInstance = window.ApexCharts.getChartByID(chartId)
  if (chartInstance) {
    chartInstance.dataURI().then(({ imgURI }) => {
      const a = document.createElement('a')
      a.href = imgURI
      a.download = `${chartId}.png`
      a.click()
    })
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 p-6 lg:p-10">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div class="flex items-center gap-4">
        <button 
          @click="router.back()" 
          class="p-2 bg-white rounded-lg shadow-sm border border-slate-200 hover:bg-slate-50 transition-colors"
        >
          <ArrowLeftIcon class="w-5 h-5 text-slate-600" />
        </button>
        <div>
          <h1 class="text-2xl font-bold text-slate-900">Analitik Mendalam</h1>
          <p class="text-slate-500">Monitoring performa HIPPAMS TIRTO JOYO</p>
        </div>
      </div>
      
      <div class="flex items-center gap-2 bg-white p-1 rounded-lg border border-slate-200 shadow-sm">
        <button 
          v-for="y in [2024, 2025, 2026]" 
          :key="y"
          @click="year = y; fetchData()"
          :class="[
            'px-4 py-1.5 rounded-md text-sm font-medium transition-all',
            year === y ? 'bg-teal-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50'
          ]"
        >
          {{ y }}
        </button>
      </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      
      <!-- 1. Tren Pemakaian Air -->
      <div 
        class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow relative group"
        data-aos="fade-up"
      >
        <div class="flex items-center justify-between mb-6">
          <div>
            <h3 class="font-bold text-slate-800">Tren Pemakaian Air</h3>
            <p class="text-sm text-slate-500">Data bulanan dalam m³</p>
          </div>
          <div class="text-right">
            <div class="text-2xl font-bold text-teal-600">{{ totalKonsumsi.toLocaleString('id-ID') }} m³</div>
            <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Total Konsumsi</div>
          </div>
        </div>

        <div v-if="isLoading" class="h-64 flex items-center justify-center bg-slate-50 rounded-xl animate-pulse">
           <div class="text-slate-300">Memuat grafik...</div>
        </div>
        <div v-else>
          <VueApexCharts height="300" :options="lineOptions" :series="lineSeries" />
        </div>

        <button 
          @click="exportChart('pemakaian-air')"
          class="absolute top-4 right-4 p-2 bg-slate-50 text-slate-400 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity hover:bg-slate-100 hover:text-slate-600"
          title="Export PNG"
        >
          <ArrowDownTrayIcon class="w-4 h-4" />
        </button>
      </div>

      <!-- 2. Analisis Tunggakan -->
      <div 
        class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow relative group"
        data-aos="fade-up"
        data-aos-delay="100"
      >
        <div class="flex items-center justify-between mb-6">
          <div>
            <h3 class="font-bold text-slate-800">Tunggakan per Wilayah</h3>
            <p class="text-sm text-slate-500">Beban piutang terbanyak</p>
          </div>
          <div class="text-right">
            <div class="text-2xl font-bold text-orange-600">Rp {{ totalTunggakan.toLocaleString('id-ID') }}</div>
            <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Total Tunggakan</div>
          </div>
        </div>

        <div v-if="isLoading" class="h-64 flex items-center justify-center bg-slate-50 rounded-xl animate-pulse">
           <div class="text-slate-300">Memuat grafik...</div>
        </div>
        <div v-else>
          <VueApexCharts height="300" :options="barOptions" :series="barSeries" />
        </div>

        <button 
          @click="exportChart('tunggakan-wilayah')"
          class="absolute top-4 right-4 p-2 bg-slate-50 text-slate-400 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity hover:bg-slate-100 hover:text-slate-600"
          title="Export PNG"
        >
          <ArrowDownTrayIcon class="w-4 h-4" />
        </button>
      </div>

      <!-- 3. Distribusi Pelanggan -->
      <div 
        class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 lg:col-span-2 hover:shadow-md transition-shadow relative group"
        data-aos="fade-up"
        data-aos-delay="200"
      >
        <div class="flex items-center justify-between mb-6">
          <div>
            <h3 class="font-bold text-slate-800">Distribusi Pelanggan</h3>
            <p class="text-sm text-slate-500">Persebaran pelanggan per wilayah</p>
          </div>
          <div class="text-right">
            <div class="text-2xl font-bold text-indigo-600">{{ totalPelanggan.toLocaleString('id-ID') }}</div>
            <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Total Pelanggan</div>
          </div>
        </div>

        <div v-if="isLoading" class="h-80 flex items-center justify-center bg-slate-50 rounded-xl animate-pulse">
           <div class="text-slate-300">Memuat grafik...</div>
        </div>
        <div v-else class="flex flex-col md:flex-row items-center justify-center">
          <VueApexCharts height="350" width="100%" :options="doughnutOptions" :series="doughnutSeries" type="donut" />
        </div>

        <button 
          @click="exportChart('distribusi-pelanggan')"
          class="absolute top-4 right-4 p-2 bg-slate-50 text-slate-400 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity hover:bg-slate-100 hover:text-slate-600"
          title="Export PNG"
        >
          <ArrowDownTrayIcon class="w-4 h-4" />
        </button>
      </div>

    </div>
  </div>
</template>

<style scoped>
:deep(.apexcharts-tooltip) {
  border-radius: 12px !important;
  border: none !important;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
}
</style>
