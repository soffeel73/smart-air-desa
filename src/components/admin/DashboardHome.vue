<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import VueApexCharts from 'vue3-apexcharts'
import { 
  UsersIcon, 
  CurrencyDollarIcon, 
  ExclamationCircleIcon,
  BanknotesIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  PlusIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()

// ==================== STATE ====================
const isLoading = ref(true)
const statsData = ref({
  total_pelanggan: 0,
  pelanggan_change: 0,
  pendapatan_bulan_ini: 0,
  pendapatan_change: 0,
  total_piutang: 0,
  piutang_change: 0,
  saldo_kas: 0
})
const chartsData = ref({
  pemakaian_air: [],
  pemasukan: [],
  pengeluaran: [],
  tren_tunggakan: [],
  tunggakan_per_wilayah: []
})
const chartLabels = ref(['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'])
const recentTransactions = ref([])
const filterYear = ref(new Date().getFullYear())

// Toast
const toasts = ref([])
let toastId = 0

// ==================== API FUNCTIONS ====================
const API_BASE = '/api/dashboard.php'

const fetchStats = async () => {
  try {
    const response = await fetch(`${API_BASE}?action=stats`)
    const data = await response.json()
    if (data.success) {
      statsData.value = data.data
    }
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  }
}

const fetchCharts = async () => {
  try {
    const response = await fetch(`${API_BASE}?action=charts&year=${filterYear.value}`)
    const data = await response.json()
    if (data.success) {
      chartsData.value = data.data
      chartLabels.value = data.labels
    }
  } catch (error) {
    console.error('Failed to fetch charts:', error)
  }
}

const fetchRecentTransactions = async () => {
  try {
    const response = await fetch(`${API_BASE}?action=recent`)
    const data = await response.json()
    if (data.success) {
      recentTransactions.value = data.data
    }
  } catch (error) {
    console.error('Failed to fetch recent transactions:', error)
  }
}

const refreshData = async () => {
  isLoading.value = true
  await Promise.all([fetchStats(), fetchCharts(), fetchRecentTransactions()])
  isLoading.value = false
  showToast('Data dashboard diperbarui', 'success')
}

// ==================== HELPERS ====================
const formatRupiah = (value) => {
  if (!value && value !== 0) return 'Rp 0'
  return 'Rp ' + parseFloat(value).toLocaleString('id-ID', { maximumFractionDigits: 0 })
}

const formatRupiahShort = (value) => {
  if (!value && value !== 0) return 'Rp 0'
  const num = parseFloat(value)
  if (num >= 1000000) {
    return 'Rp ' + (num / 1000000).toFixed(1) + ' jt'
  } else if (num >= 1000) {
    return 'Rp ' + (num / 1000).toFixed(0) + 'K'
  }
  return 'Rp ' + num.toLocaleString('id-ID')
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

const showToast = (message, type = 'success') => {
  const id = ++toastId
  toasts.value.push({ id, message, type })
  setTimeout(() => {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }, 3000)
}

// ==================== CHART CONFIGS ====================
const pemakaianAirOptions = computed(() => ({
  chart: { type: 'bar', height: 280, toolbar: { show: false } },
  colors: ['#2EC4B6'],
  plotOptions: { bar: { borderRadius: 6, columnWidth: '60%' } },
  dataLabels: { enabled: false },
  xaxis: { categories: chartLabels.value },
  yaxis: { 
    title: { text: 'm³' },
    labels: { formatter: (val) => val.toLocaleString() }
  },
  tooltip: { y: { formatter: (val) => val.toLocaleString() + ' m³' } }
}))

const pemakaianAirSeries = computed(() => [{
  name: 'Pemakaian',
  data: chartsData.value.pemakaian_air || []
}])

const arusKasOptions = computed(() => ({
  chart: { type: 'line', height: 280, toolbar: { show: false } },
  colors: ['#2EC4B6', '#FF9F1C'],
  stroke: { curve: 'smooth', width: 3 },
  markers: { size: 4 },
  xaxis: { categories: chartLabels.value },
  yaxis: { labels: { formatter: (val) => formatRupiahShort(val) } },
  tooltip: { y: { formatter: (val) => formatRupiah(val) } },
  legend: { position: 'top' }
}))

const arusKasSeries = computed(() => [
  { name: 'Pemasukan', data: chartsData.value.pemasukan || [] },
  { name: 'Pengeluaran', data: chartsData.value.pengeluaran || [] }
])

const trenTunggakanOptions = computed(() => ({
  chart: { type: 'area', height: 200, toolbar: { show: false }, sparkline: { enabled: false } },
  colors: ['#FF9F1C'],
  fill: { type: 'gradient', gradient: { opacityFrom: 0.5, opacityTo: 0.1 } },
  stroke: { curve: 'smooth', width: 2 },
  xaxis: { categories: chartLabels.value },
  yaxis: { labels: { formatter: (val) => formatRupiahShort(val) } },
  tooltip: { y: { formatter: (val) => formatRupiah(val) } }
}))

const trenTunggakanSeries = computed(() => [{
  name: 'Tunggakan',
  data: chartsData.value.tren_tunggakan || []
}])

const tunggakanWilayahOptions = computed(() => ({
  chart: { type: 'bar', height: 250, toolbar: { show: false } },
  colors: ['#FF9F1C'],
  plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '70%' } },
  dataLabels: { enabled: false },
  xaxis: { labels: { formatter: (val) => formatRupiahShort(val) } },
  yaxis: { labels: { style: { fontSize: '11px' } } },
  tooltip: { y: { formatter: (val) => formatRupiah(val) } }
}))

const tunggakanWilayahSeries = computed(() => [{
  name: 'Tunggakan',
  data: (chartsData.value.tunggakan_per_wilayah || []).map(w => w.total)
}])

const tunggakanWilayahCategories = computed(() => 
  (chartsData.value.tunggakan_per_wilayah || []).map(w => w.wilayah || 'Tidak Ada')
)

// ==================== QUICK ACTIONS ====================
const navigateToMeter = () => {
  router.push('/admin/meter')
}

const navigateToFinancial = () => {
  router.push('/admin/financial')
}

// ==================== LIFECYCLE ====================
onMounted(async () => {
  await Promise.all([fetchStats(), fetchCharts(), fetchRecentTransactions()])
  isLoading.value = false
})
</script>

<template>
  <div class="space-y-6">
    <!-- Toast Notifications -->
    <Teleport to="body">
      <div class="fixed top-4 right-4 z-[100] space-y-2">
        <TransitionGroup name="toast">
          <div 
            v-for="toast in toasts" 
            :key="toast.id"
            :class="[
              'flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-white font-medium',
              toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'
            ]"
          >
            <CheckCircleIcon v-if="toast.type === 'success'" class="w-5 h-5" />
            <XCircleIcon v-else class="w-5 h-5" />
            {{ toast.message }}
          </div>
        </TransitionGroup>
      </div>
    </Teleport>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Dashboard Overview</h2>
        <p class="text-gray-500">Ringkasan statistik pengelolaan air bersih real-time.</p>
      </div>
      <div class="flex items-center gap-3">
        <button 
          @click="refreshData"
          :disabled="isLoading"
          class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-all disabled:opacity-50"
        >
          <ArrowPathIcon class="w-5 h-5" :class="{ 'animate-spin': isLoading }" />
          Refresh
        </button>
      </div>
    </div>

    <!-- Quick Action Buttons -->
    <div class="flex flex-wrap gap-3">
      <button 
        @click="navigateToMeter"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white rounded-xl font-bold shadow-lg shadow-primary/30 hover:bg-teal-600 transition-all"
      >
        <PlusIcon class="w-5 h-5" />
        Input Meter Baru
      </button>
      <button 
        @click="navigateToFinancial"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-accent text-white rounded-xl font-bold shadow-lg shadow-accent/30 hover:bg-amber-600 transition-all"
      >
        <CurrencyDollarIcon class="w-5 h-5" />
        Catat Pengeluaran
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex items-center justify-center py-20">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
        <p class="text-gray-500">Memuat data dashboard...</p>
      </div>
    </div>

    <template v-else>
      <!-- Stats Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Pelanggan -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center">
              <UsersIcon class="w-5 h-5 text-primary" />
            </div>
            <span 
              v-if="statsData.pelanggan_change !== 0"
              class="text-xs font-bold px-2 py-1 rounded-full flex items-center gap-1"
              :class="statsData.pelanggan_change >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
            >
              <ArrowTrendingUpIcon v-if="statsData.pelanggan_change >= 0" class="w-3 h-3" />
              <ArrowTrendingDownIcon v-else class="w-3 h-3" />
              {{ Math.abs(statsData.pelanggan_change) }}%
            </span>
          </div>
          <div class="text-2xl font-bold text-gray-900 mb-1">{{ statsData.total_pelanggan.toLocaleString() }}</div>
          <div class="text-sm text-gray-500">Total Pelanggan</div>
        </div>

        <!-- Pendapatan Bulan Ini -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center">
              <CurrencyDollarIcon class="w-5 h-5 text-primary" />
            </div>
            <span 
              v-if="statsData.pendapatan_change !== 0"
              class="text-xs font-bold px-2 py-1 rounded-full flex items-center gap-1"
              :class="statsData.pendapatan_change >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
            >
              <ArrowTrendingUpIcon v-if="statsData.pendapatan_change >= 0" class="w-3 h-3" />
              <ArrowTrendingDownIcon v-else class="w-3 h-3" />
              {{ Math.abs(statsData.pendapatan_change) }}%
            </span>
          </div>
          <div class="text-2xl font-bold text-primary mb-1">{{ formatRupiahShort(statsData.pendapatan_bulan_ini) }}</div>
          <div class="text-sm text-gray-500">Pendapatan Bulan Ini</div>
        </div>

        <!-- Total Piutang -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center">
              <ExclamationCircleIcon class="w-5 h-5 text-accent" />
            </div>
            <span 
              v-if="statsData.piutang_change !== 0"
              class="text-xs font-bold px-2 py-1 rounded-full flex items-center gap-1"
              :class="statsData.piutang_change <= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
            >
              <ArrowTrendingDownIcon v-if="statsData.piutang_change <= 0" class="w-3 h-3" />
              <ArrowTrendingUpIcon v-else class="w-3 h-3" />
              {{ Math.abs(statsData.piutang_change) }}%
            </span>
          </div>
          <div class="text-2xl font-bold text-accent mb-1">{{ formatRupiahShort(statsData.total_piutang) }}</div>
          <div class="text-sm text-gray-500">Total Piutang</div>
        </div>

        <!-- Saldo Kas -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 rounded-xl bg-green-100 flex items-center justify-center">
              <BanknotesIcon class="w-5 h-5 text-green-600" />
            </div>
          </div>
          <div class="text-2xl font-bold mb-1" :class="statsData.saldo_kas >= 0 ? 'text-green-600' : 'text-red-500'">
            {{ formatRupiahShort(statsData.saldo_kas) }}
          </div>
          <div class="text-sm text-gray-500">Saldo Kas Tersedia</div>
        </div>
      </div>

      <!-- Charts Row 1 -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Pemakaian Air Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="p-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-900">📊 Pemakaian Air Bulanan</h3>
            <p class="text-sm text-gray-500">Total konsumsi air seluruh pelanggan (m³)</p>
          </div>
          <div class="p-4">
            <VueApexCharts 
              type="bar" 
              :options="pemakaianAirOptions" 
              :series="pemakaianAirSeries"
            />
          </div>
        </div>

        <!-- Arus Kas Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="p-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-900">💰 Arus Kas Bulanan</h3>
            <p class="text-sm text-gray-500">Perbandingan pemasukan vs pengeluaran</p>
          </div>
          <div class="p-4">
            <VueApexCharts 
              type="line" 
              :options="arusKasOptions" 
              :series="arusKasSeries"
            />
          </div>
        </div>
      </div>

      <!-- Charts Row 2 -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Tren Tunggakan Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="p-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-900">📈 Tren Tunggakan Bulanan</h3>
            <p class="text-sm text-gray-500">Akumulasi tunggakan setiap bulan</p>
          </div>
          <div class="p-4">
            <VueApexCharts 
              type="area" 
              :options="trenTunggakanOptions" 
              :series="trenTunggakanSeries"
            />
          </div>
        </div>

        <!-- Tunggakan per Wilayah -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="p-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-900">🗺️ Tunggakan per Wilayah</h3>
            <p class="text-sm text-gray-500">Persebaran tunggakan berdasarkan alamat</p>
          </div>
          <div class="p-4">
            <VueApexCharts 
              v-if="chartsData.tunggakan_per_wilayah?.length > 0"
              type="bar" 
              :options="{
                ...tunggakanWilayahOptions,
                xaxis: { ...tunggakanWilayahOptions.xaxis, categories: tunggakanWilayahCategories }
              }" 
              :series="tunggakanWilayahSeries"
            />
            <div v-else class="text-center py-8 text-gray-500">
              Belum ada data tunggakan per wilayah
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Transactions -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
          <div>
            <h3 class="font-bold text-gray-900">🕐 Transaksi Terakhir</h3>
            <p class="text-sm text-gray-500">5 transaksi terbaru yang tercatat</p>
          </div>
          <button @click="navigateToFinancial" class="text-primary text-sm font-bold hover:underline">
            Lihat Semua
          </button>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 font-bold uppercase text-xs">
              <tr>
                <th class="px-4 py-3 text-left">Tanggal</th>
                <th class="px-4 py-3 text-left">Nama</th>
                <th class="px-4 py-3 text-left">Kategori</th>
                <th class="px-4 py-3 text-right">Nominal</th>
                <th class="px-4 py-3 text-center">Jenis</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-if="recentTransactions.length === 0">
                <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                  Belum ada transaksi tercatat
                </td>
              </tr>
              <tr v-for="trx in recentTransactions" :key="trx.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-600">{{ formatDate(trx.tanggal) }}</td>
                <td class="px-4 py-3 font-bold text-gray-900">{{ trx.nama }}</td>
                <td class="px-4 py-3 text-gray-600">{{ trx.kategori }}</td>
                <td class="px-4 py-3 text-right font-bold" :class="trx.tipe === 'pemasukan' ? 'text-primary' : 'text-red-500'">
                  {{ trx.tipe === 'pemasukan' ? '+' : '-' }}{{ formatRupiah(trx.nominal) }}
                </td>
                <td class="px-4 py-3 text-center">
                  <span 
                    class="px-2 py-1 rounded-full text-xs font-bold"
                    :class="trx.tipe === 'pemasukan' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                  >
                    {{ trx.tipe === 'pemasukan' ? 'Masuk' : 'Keluar' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>
