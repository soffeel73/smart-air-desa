<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { 
  ChartBarIcon, 
  CurrencyDollarIcon, 
  ExclamationTriangleIcon, 
  BeakerIcon,
  EyeIcon,
  XMarkIcon,
  CheckCircleIcon,
  XCircleIcon,
  ChevronDownIcon,
  PencilSquareIcon,
  CheckIcon
} from '@heroicons/vue/24/outline'

// ==================== STATE ====================
const rekapData = ref([])
const isLoading = ref(false)

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)
const totalItems = ref(0)
const totalPages = ref(0)

// Stats
const stats = ref({
  total_pembayaran: 0,
  total_tunggakan: 0,
  total_pemakaian: 0
})

// Active Tab / Submenu
const activeTab = ref('pembayaran')
const tabOptions = [
  { id: 'pembayaran', label: 'Rekap Pembayaran', icon: CurrencyDollarIcon, color: 'primary' },
  { id: 'tunggakan', label: 'Rekap Tunggakan', icon: ExclamationTriangleIcon, color: 'red' },
  { id: 'pemakaian', label: 'Rekap Pemakaian', icon: BeakerIcon, color: 'blue' }
]

// Filter State
const filterYear = ref(new Date().getFullYear())

// Modal State
const showDetailModal = ref(false)
const detailData = ref(null)
const detailLoading = ref(false)

// Breakdown Tooltip State
const showBreakdownTooltip = ref(null)

// Month labels
const monthLabels = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']

// ==================== COMPUTED ====================
const getMonthLabel = (month) => monthLabels[parseInt(month) - 1] || month

const currentTabConfig = computed(() => tabOptions.find(t => t.id === activeTab.value))

// ==================== FORMATTING HELPERS ====================
const formatRupiah = (num) => {
  return 'Rp ' + parseFloat(num || 0).toLocaleString('id-ID')
}

const formatNumber = (num) => {
  return parseFloat(num || 0).toLocaleString('id-ID')
}

// Helper to check if arrears > 3 months
const isOverdueWarning = (data) => {
  if (!data.months) return false
  const unpaidMonths = data.months.filter(m => m.status === 'unpaid').length
  return unpaidMonths >= 3
}

// Toggle breakdown tooltip
const toggleBreakdownTooltip = (id) => {
  showBreakdownTooltip.value = showBreakdownTooltip.value === id ? null : id
}

// ==================== API FUNCTIONS ====================
const API_BASE = '/api/rekapitulasi.php'

const fetchRekapData = async () => {
  isLoading.value = true
  try {
    const params = new URLSearchParams({
      type: activeTab.value,
      year: filterYear.value,
      page: currentPage.value,
      limit: itemsPerPage.value
    })
    const response = await fetch(`${API_BASE}?${params.toString()}`)
    const data = await response.json()
    if (data.success) {
      rekapData.value = data.data
      if (data.pagination) {
        totalItems.value = data.pagination.total_items
        totalPages.value = data.pagination.total_pages
      }
      if (data.stats) {
        stats.value = data.stats
      }
    }
  } catch (error) {
    console.error('Failed to fetch rekap data:', error)
  } finally {
    isLoading.value = false
  }
}

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    fetchRekapData()
  }
}

const fetchDetailData = async (pelangganId) => {
  detailLoading.value = true
  showDetailModal.value = true
  try {
    const response = await fetch(`${API_BASE}?type=${activeTab.value}&year=${filterYear.value}&pelanggan_id=${pelangganId}`)
    const data = await response.json()
    if (data.success) {
      detailData.value = data
    }
  } catch (error) {
    console.error('Failed to fetch detail:', error)
  } finally {
    detailLoading.value = false
  }
}

const closeModal = () => {
  showDetailModal.value = false
  detailData.value = null
}

// ==================== TOAST FUNCTIONS ====================
const showToast = (message, type = 'success') => {
  const id = ++toastId
  toasts.value.push({ id, message, type })
  setTimeout(() => {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }, 4000)
}

// Watch for changes
watch([activeTab, filterYear], () => {
  currentPage.value = 1
  fetchRekapData()
})

onMounted(() => {
  fetchRekapData()
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
              'flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-white font-medium max-w-sm',
              toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'
            ]"
          >
            <CheckCircleIcon v-if="toast.type === 'success'" class="w-5 h-5 flex-shrink-0" />
            <XCircleIcon v-else class="w-5 h-5 flex-shrink-0" />
            {{ toast.message }}
          </div>
        </TransitionGroup>
      </div>
    </Teleport>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Rekapitulasi</h2>
        <p class="text-gray-500">Laporan rekap tahunan pembayaran, tunggakan, dan pemakaian.</p>
      </div>
    </div>

    <!-- Tab Selector & Filter -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
      <div class="flex flex-wrap items-center gap-4">
        <!-- Tab Dropdown -->
        <div class="flex-1 min-w-[250px]">
          <div class="relative">
            <select 
              v-model="activeTab"
              class="w-full appearance-none bg-white border border-gray-200 rounded-xl px-4 py-3 pr-10 font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/50 cursor-pointer"
            >
              <option v-for="tab in tabOptions" :key="tab.id" :value="tab.id">
                {{ tab.label }}
              </option>
            </select>
            <ChevronDownIcon class="w-5 h-5 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" />
          </div>
        </div>
        
        <!-- Tab Buttons (Desktop) -->
        <div class="hidden lg:flex gap-2">
          <button 
            v-for="tab in tabOptions" :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold transition-all',
              activeTab === tab.id 
                ? 'bg-primary text-white shadow-lg' 
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
            ]"
          >
            <component :is="tab.icon" class="w-5 h-5" />
            {{ tab.label }}
          </button>
        </div>

        <!-- Year Filter -->
        <div class="flex items-center gap-2">
          <label class="text-sm font-medium text-gray-600">Tahun:</label>
          <select 
            v-model="filterYear"
            class="px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/50 bg-white font-bold"
          >
            <option v-for="y in [2026, 2027, 2028, 2029, 2030]" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <!-- Total Pembayaran -->
      <div 
        class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition-all hover:shadow-md cursor-pointer"
        @click="activeTab = 'pembayaran'"
        :class="activeTab === 'pembayaran' ? 'ring-2 ring-primary border-transparent' : ''"
      >
        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
          <CurrencyDollarIcon class="w-6 h-6" />
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500">Total Pembayaran ({{ filterYear }})</p>
          <p class="text-xl font-bold text-gray-900">{{ formatRupiah(stats.total_pembayaran) }}</p>
        </div>
      </div>

      <!-- Total Tunggakan -->
      <div 
        class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition-all hover:shadow-md cursor-pointer"
        @click="activeTab = 'tunggakan'"
        :class="activeTab === 'tunggakan' ? 'ring-2 ring-red-500 border-transparent' : ''"
      >
        <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center text-red-600">
          <ExclamationTriangleIcon class="w-6 h-6" />
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500">Total Tunggakan ({{ filterYear }})</p>
          <p class="text-xl font-bold text-gray-900">{{ formatRupiah(stats.total_tunggakan) }}</p>
        </div>
      </div>

      <!-- Total Pemakaian -->
      <div 
        class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition-all hover:shadow-md cursor-pointer"
        @click="activeTab = 'pemakaian'"
        :class="activeTab === 'pemakaian' ? 'ring-2 ring-blue-500 border-transparent' : ''"
      >
        <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
          <BeakerIcon class="w-6 h-6" />
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500">Total Pemakaian ({{ filterYear }})</p>
          <p class="text-xl font-bold text-gray-900">{{ formatNumber(stats.total_pemakaian) }} m³</p>
        </div>
      </div>
    </div>

    <!-- Current Tab Indicator -->
    <div class="flex items-center gap-3 p-4 rounded-xl" :class="{
      'bg-primary/10 border border-primary/20': activeTab === 'pembayaran',
      'bg-red-50 border border-red-200': activeTab === 'tunggakan',
      'bg-blue-50 border border-blue-200': activeTab === 'pemakaian'
    }">
      <component :is="currentTabConfig.icon" :class="{
        'w-6 h-6': true,
        'text-primary': activeTab === 'pembayaran',
        'text-red-500': activeTab === 'tunggakan',
        'text-blue-500': activeTab === 'pemakaian'
      }" />
      <div>
        <div class="font-bold text-gray-900">{{ currentTabConfig.label }}</div>
        <div class="text-sm text-gray-500">
          <template v-if="activeTab === 'pembayaran'">Total tagihan per pelanggan selama tahun {{ filterYear }}</template>
          <template v-else-if="activeTab === 'tunggakan'">Daftar pelanggan dengan tunggakan belum lunas</template>
          <template v-else>Total volume pemakaian air per pelanggan</template>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <!-- Loading -->
      <div v-if="isLoading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
      </div>
      
      <!-- Empty State -->
      <div v-else-if="rekapData.length === 0" class="text-center py-12">
        <ChartBarIcon class="w-12 h-12 mx-auto text-gray-300 mb-4" />
        <p class="text-gray-500">Belum ada data untuk tahun {{ filterYear }}</p>
      </div>
      
      <!-- Data Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-primary text-white font-bold uppercase text-xs">
            <tr>
              <th class="px-4 py-4 text-center w-12">No</th>
              <th class="px-4 py-4 text-left">ID Pelanggan</th>
              <th class="px-4 py-4 text-left">Nama</th>
              <th class="px-4 py-4 text-left">Alamat</th>
              <th class="px-4 py-4 text-center">Bulan Terakhir</th>
              <th class="px-4 py-4 text-center">Tahun</th>
              <th class="px-4 py-4 text-right">
                <template v-if="activeTab === 'pembayaran'">Total Tagihan</template>
                <template v-else-if="activeTab === 'tunggakan'">Total Tunggakan</template>
                <template v-else>Total Pemakaian</template>
              </th>
              <th class="px-4 py-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr 
              v-for="(item, index) in rekapData" 
              :key="item.pelanggan_id" 
              :class="[
                'transition-colors',
                activeTab === 'tunggakan' && isOverdueWarning(item) ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-frozen/20'
              ]"
            >
              <td class="px-4 py-4 text-center font-medium text-gray-500">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
              <td class="px-4 py-4 font-mono text-gray-600 font-medium">
                <div class="flex items-center gap-2">
                  <span>{{ item.customer_id }}</span>
                  <span 
                    v-if="activeTab === 'tunggakan' && isOverdueWarning(item)"
                    class="px-2 py-0.5 bg-red-500 text-white text-[10px] font-bold rounded-full animate-pulse"
                    title="Tunggakan >3 Bulan - Peringatan Putus"
                  >
                    !
                  </span>
                </div>
              </td>
              <td class="px-4 py-4 font-bold text-gray-900">{{ item.name }}</td>
              <td class="px-4 py-4 text-gray-600 text-sm max-w-xs truncate">{{ item.address }}</td>
              <td class="px-4 py-4 text-center">{{ getMonthLabel(item.last_month) }}</td>
              <td class="px-4 py-4 text-center font-bold">{{ item.year }}</td>
              <td class="px-4 py-4 text-right">
                <!-- Pembayaran -->
                <template v-if="activeTab === 'pembayaran'">
                  <span class="text-lg font-bold text-amber-500">{{ formatRupiah(item.grand_total || item.total_tagihan) }}</span>
                </template>
                <!-- Tunggakan with Akumulasi label -->
                <template v-else-if="activeTab === 'tunggakan'">
                  <div class="text-lg font-bold text-red-600">{{ formatRupiah(item.total_tunggakan) }}</div>
                  <div class="text-[10px] text-red-400 uppercase tracking-wider">Akumulasi</div>
                </template>
                <!-- Pemakaian -->
                <template v-else>
                  <span class="text-lg font-bold text-blue-500">{{ formatNumber(item.total_pemakaian) }} m³</span>
                </template>
              </td>
              <td class="px-4 py-4">
                <div class="flex items-center justify-center">
                  <button 
                    @click="fetchDetailData(item.pelanggan_id)"
                    class="p-2 text-primary hover:bg-primary/10 rounded-full transition-colors"
                    title="Lihat Detail"
                  >
                    <EyeIcon class="w-5 h-5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination -->
      <div v-if="rekapData.length > 0" class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-sm text-gray-500">
          Menampilkan {{ rekapData.length }} dari {{ totalItems }} data
        </div>
        <div v-if="totalPages > 1" class="flex items-center gap-2">
          <button @click="goToPage(1)" :disabled="currentPage === 1" class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">&laquo;</button>
          <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">&lsaquo;</button>
          <template v-for="page in totalPages" :key="page">
            <button 
              v-if="page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)"
              @click="goToPage(page)"
              class="px-3 py-1.5 text-sm rounded-lg border transition-colors"
              :class="page === currentPage ? 'bg-primary text-white border-primary' : 'border-gray-200 hover:bg-gray-50'"
            >{{ page }}</button>
            <span v-else-if="page === currentPage - 2 || page === currentPage + 2" class="text-gray-400">...</span>
          </template>
          <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages" class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">&rsaquo;</button>
          <button @click="goToPage(totalPages)" :disabled="currentPage === totalPages" class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">&raquo;</button>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <Teleport to="body">
      <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
        
        <!-- Modal Content -->
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden transform transition-all">
          <!-- Header -->
          <div class="bg-primary text-white px-6 py-4 flex items-center justify-between">
            <h3 class="text-lg font-bold">
              <template v-if="activeTab === 'pembayaran'">Detail Tagihan {{ filterYear }}</template>
              <template v-else-if="activeTab === 'tunggakan'">Detail Tunggakan {{ filterYear }}</template>
              <template v-else>Detail Pemakaian {{ filterYear }}</template>
            </h3>
            <button @click="closeModal" class="p-1 hover:bg-white/20 rounded-full transition-colors">
              <XMarkIcon class="w-6 h-6" />
            </button>
          </div>
          
          <!-- Content -->
          <div class="p-6 overflow-y-auto max-h-[calc(90vh-180px)]">
            <!-- Loading -->
            <div v-if="detailLoading" class="flex items-center justify-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>
            
            <template v-else-if="detailData">
              <!-- Customer Info -->
              <div class="mb-6 p-4 bg-frozen/50 rounded-xl border border-frozen">
                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span class="text-gray-500">ID Pelanggan:</span>
                    <span class="ml-2 font-bold font-mono">{{ detailData.pelanggan?.customer_id }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500">Nama:</span>
                    <span class="ml-2 font-bold">{{ detailData.pelanggan?.name }}</span>
                  </div>
                  <div class="col-span-2">
                    <span class="text-gray-500">Alamat:</span>
                    <span class="ml-2 font-bold">{{ detailData.pelanggan?.address }}</span>
                  </div>
                </div>
              </div>
              
              <!-- Monthly Table -->
              <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full text-sm">
                  <thead class="bg-gray-100">
                    <tr>
                      <th class="px-4 py-3 text-left font-bold text-gray-700">Bulan</th>
                      <!-- Pembayaran columns -->
                      <template v-if="activeTab === 'pembayaran'">
                        <th class="px-4 py-3 text-right font-bold text-gray-700">Biaya Pemakaian</th>
                        <th class="px-4 py-3 text-right font-bold text-gray-700">Tunggakan</th>
                        <th class="px-4 py-3 text-right font-bold text-gray-700">Total Tagihan</th>
                        <th class="px-4 py-3 text-center font-bold text-gray-700">Status</th>
                      </template>
                      <!-- Tunggakan columns -->
                      <template v-else-if="activeTab === 'tunggakan'">
                        <th class="px-4 py-3 text-right font-bold text-gray-700">Tunggakan</th>
                        <th class="px-4 py-3 text-center font-bold text-gray-700">Status</th>
                      </template>
                      <!-- Pemakaian columns -->
                      <template v-else>
                        <th class="px-4 py-3 text-center font-bold text-gray-700">Meter Awal</th>
                        <th class="px-4 py-3 text-center font-bold text-gray-700">Meter Akhir</th>
                        <th class="px-4 py-3 text-right font-bold text-gray-700">Pemakaian</th>
                      </template>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100">
                    <tr v-for="month in detailData.months" :key="month.period_month" class="hover:bg-gray-50">
                      <td class="px-4 py-3 font-medium">{{ getMonthLabel(month.period_month) }}</td>
                      
                      <!-- Pembayaran data -->
                      <template v-if="activeTab === 'pembayaran'">
                        <td class="px-4 py-3 text-right">{{ formatRupiah(month.biaya_pemakaian) }}</td>
                        <td class="px-4 py-3 text-right" :class="parseFloat(month.tunggakan) > 0 ? 'text-red-500 font-bold' : 'text-gray-400'">
                          {{ formatRupiah(month.tunggakan) }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-amber-500">{{ formatRupiah(month.total_tagihan) }}</td>
                        <td class="px-4 py-3 text-center">
                          <span :class="[
                            'px-2 py-1 rounded-full text-xs font-bold',
                            month.status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'
                          ]">
                            {{ month.status === 'paid' ? 'Lunas' : 'Belum' }}
                          </span>
                        </td>
                      </template>
                      
                      <!-- Tunggakan data (READ-ONLY - Auto Calculated) -->
                      <template v-else-if="activeTab === 'tunggakan'">
                        <td class="px-4 py-3 text-right">
                          <div v-if="parseFloat(month.tunggakan) > 0">
                            <div class="text-red-600 font-bold">{{ formatRupiah(month.tunggakan) }}</div>
                            <div class="text-[10px] text-red-400 uppercase tracking-wider">Auto-Akumulasi</div>
                          </div>
                          <span v-else class="text-gray-400">{{ formatRupiah(0) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                          <span :class="[
                            'px-2 py-1 rounded-full text-xs font-bold',
                            month.status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'
                          ]">
                            {{ month.status === 'paid' ? 'Lunas' : 'Belum' }}
                          </span>
                        </td>
                      </template>
                      
                      <!-- Pemakaian data -->
                      <template v-else>
                        <td class="px-4 py-3 text-center font-mono">{{ month.meter_awal }}</td>
                        <td class="px-4 py-3 text-center font-mono">{{ month.meter_akhir }}</td>
                        <td class="px-4 py-3 text-right font-bold text-blue-500">{{ formatNumber(month.jumlah_pakai) }} m³</td>
                      </template>
                    </tr>
                  </tbody>
                </table>
              </div>
            </template>
          </div>
          
          <!-- Footer with Total -->
          <div v-if="detailData && !detailLoading" class="px-6 py-4 bg-amber-500 text-white">
            <div class="flex items-center justify-between">
              <span class="font-bold text-lg">
                <template v-if="activeTab === 'pembayaran'">Total Tagihan Tahunan:</template>
                <template v-else-if="activeTab === 'tunggakan'">Total Tunggakan Tahunan:</template>
                <template v-else>Total Konsumsi Tahunan:</template>
              </span>
              <span class="text-2xl font-bold">
                <template v-if="activeTab === 'pembayaran'">{{ formatRupiah(detailData.total_tagihan_tahunan) }}</template>
                <template v-else-if="activeTab === 'tunggakan'">{{ formatRupiah(detailData.total_tunggakan_tahunan) }}</template>
                <template v-else>{{ formatNumber(detailData.total_pemakaian_tahunan) }} m³</template>
              </span>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
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
