<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import { 
  MagnifyingGlassIcon,
  FunnelIcon,
  EyeIcon,
  XMarkIcon,
  CheckCircleIcon,
  ClockIcon,
  WrenchScrewdriverIcon,
  PhotoIcon,
  ChatBubbleLeftRightIcon
} from '@heroicons/vue/24/outline'

// Data
const keluhans = ref([])
const isLoading = ref(true)
const selectedKeluhan = ref(null)
const showModal = ref(false)
const isUpdating = ref(false)

// Filters
const searchQuery = ref('')
const filterStatus = ref('')
const filterKategori = ref('')

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)
const totalItems = ref(0)
const totalPages = ref(0)

// Toast
const toast = ref({ show: false, message: '', type: '' })

// Watch for filter changes
watch([filterStatus, filterKategori], () => {
  currentPage.value = 1
  fetchKeluhans()
})

// Debounced search
let searchTimeout
watch(searchQuery, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    fetchKeluhans()
  }, 500)
})

onMounted(() => {
  fetchKeluhans()
})

const API_BASE = '/api/keluhan.php'

// Kategori list
const kategoriList = [
  'Pipa Bocor',
  'Air Tidak Mengalir',
  'Meteran Rusak',
  'Kesalahan Tagihan',
  'Kualitas Air',
  'Lainnya'
]

// Status config
const statusConfig = {
  'Menunggu': { color: 'bg-yellow-100 text-yellow-800', icon: ClockIcon },
  'Diproses': { color: 'bg-blue-100 text-blue-800', icon: WrenchScrewdriverIcon },
  'Selesai': { color: 'bg-green-100 text-green-800', icon: CheckCircleIcon }
}

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    fetchKeluhans()
  }
}

// Fetch keluhans
const fetchKeluhans = async () => {
  isLoading.value = true
  try {
    const params = new URLSearchParams({ action: 'list' })
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (filterStatus.value) params.append('status', filterStatus.value)
    if (filterKategori.value) params.append('kategori', filterKategori.value)
    params.append('page', currentPage.value)
    params.append('limit', itemsPerPage.value)

    const response = await fetch(`${API_BASE}?${params.toString()}`)
    const data = await response.json()
    if (data.success) {
      keluhans.value = data.data || []
      if (data.pagination) {
        totalItems.value = data.pagination.total_items
        totalPages.value = data.pagination.total_pages
      }
    } else {
      showToast(data.message || 'Gagal memuat data keluhan', 'error')
    }
  } catch (error) {
    showToast('Gagal memuat data keluhan', 'error')
  } finally {
    isLoading.value = false
  }
}

// Open detail modal
const openDetail = (keluhan) => {
  selectedKeluhan.value = { ...keluhan, newStatus: keluhan.status, catatanAdmin: keluhan.catatan_admin || '' }
  showModal.value = true
}

// Close modal
const closeModal = () => {
  showModal.value = false
  selectedKeluhan.value = null
}

// Update status
const updateStatus = async () => {
  if (!selectedKeluhan.value) return
  
  isUpdating.value = true
  try {
    const response = await fetch(`${API_BASE}?action=update_status`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        id: selectedKeluhan.value.id,
        status: selectedKeluhan.value.newStatus,
        catatan_admin: selectedKeluhan.value.catatanAdmin
      })
    })
    
    const data = await response.json()
    
    if (data.success) {
      showToast('Status berhasil diupdate', 'success')
      await fetchKeluhans()
      closeModal()
    } else {
      showToast(data.message || 'Gagal mengupdate status', 'error')
    }
  } catch (error) {
    showToast('Gagal menghubungi server', 'error')
  } finally {
    isUpdating.value = false
  }
}

// Toast helper
const showToast = (message, type) => {
  toast.value = { show: true, message, type }
  setTimeout(() => {
    toast.value.show = false
  }, 3000)
}

// Format date
const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleDateString('id-ID', { 
    day: 'numeric', 
    month: 'short', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Generate WhatsApp link with pre-filled message
const getWhatsAppLink = (keluhan) => {
  let phone = keluhan.no_whatsapp.replace(/[^0-9]/g, '')
  // Convert 08xxx to 628xxx
  if (phone.startsWith('0')) {
    phone = '62' + phone.substring(1)
  }
  
  const message = `Halo ${keluhan.nama_lengkap}, kami dari tim HIPPAMS TIRTO JOYO ingin menindaklanjuti laporan Anda mengenai "${keluhan.kategori}" (No. Laporan #${keluhan.id}). Mohon informasi detail lokasi atau kendala yang Anda alami.`
  
  return `https://wa.me/${phone}?text=${encodeURIComponent(message)}`
}

onMounted(() => {
  fetchKeluhans()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Daftar Keluhan</h1>
        <p class="text-gray-500 text-sm mt-1">Kelola laporan keluhan dari pelanggan</p>
      </div>
      <div class="flex items-center gap-2">
        <span class="px-3 py-1.5 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
          {{ keluhans.filter(k => k.status === 'Menunggu').length }} Menunggu
        </span>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
      <div class="flex flex-col sm:flex-row gap-3">
        <!-- Search -->
        <div class="flex-1 relative">
          <MagnifyingGlassIcon class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Cari nama, No. HPM, atau detail..."
            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"
          />
        </div>
        
        <!-- Status Filter -->
        <select 
          v-model="filterStatus"
          class="px-4 py-2.5 rounded-lg border border-gray-200 focus:border-primary outline-none bg-white"
        >
          <option value="">Semua Status</option>
          <option value="Menunggu">Menunggu</option>
          <option value="Diproses">Diproses</option>
          <option value="Selesai">Selesai</option>
        </select>

        <!-- Kategori Filter -->
        <select 
          v-model="filterKategori"
          class="px-4 py-2.5 rounded-lg border border-gray-200 focus:border-primary outline-none bg-white"
        >
          <option value="">Semua Kategori</option>
          <option v-for="kat in kategoriList" :key="kat" :value="kat">{{ kat }}</option>
        </select>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-10 w-10 border-4 border-primary border-t-transparent"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="keluhans.length === 0" class="bg-white rounded-xl shadow-sm p-12 text-center">
      <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <FunnelIcon class="w-8 h-8 text-gray-400" />
      </div>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">Tidak ada keluhan</h3>
      <p class="text-gray-500">{{ searchQuery || filterStatus || filterKategori ? 'Coba ubah filter pencarian' : 'Belum ada laporan keluhan dari pelanggan' }}</p>
    </div>

    <!-- Keluhan Table -->
    <div v-else class="bg-white rounded-xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Pelapor</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Kategori</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">Tanggal</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="keluhan in keluhans" :key="keluhan.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-4 py-4">
                <div class="font-medium text-gray-900">{{ keluhan.nama_lengkap }}</div>
                <div class="text-sm text-gray-500">{{ keluhan.no_hpm }}</div>
              </td>
              <td class="px-4 py-4 hidden sm:table-cell">
                <span class="text-sm text-gray-700">{{ keluhan.kategori }}</span>
              </td>
              <td class="px-4 py-4 hidden md:table-cell">
                <span class="text-sm text-gray-500">{{ formatDate(keluhan.created_at) }}</span>
              </td>
              <td class="px-4 py-4">
                <span 
                  :class="[statusConfig[keluhan.status]?.color, 'px-2.5 py-1 rounded-full text-xs font-medium inline-flex items-center gap-1']"
                >
                  <component :is="statusConfig[keluhan.status]?.icon" class="w-3.5 h-3.5" />
                  {{ keluhan.status }}
                </span>
              </td>
              <td class="px-4 py-4 text-center">
                <div class="flex items-center justify-center gap-1">
                  <a 
                    :href="getWhatsAppLink(keluhan)"
                    target="_blank"
                    class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                    title="Hubungi via WhatsApp"
                  >
                    <ChatBubbleLeftRightIcon class="w-5 h-5" />
                  </a>
                  <button 
                    @click="openDetail(keluhan)"
                    class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors"
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
      <div v-if="keluhans.length > 0" class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-sm text-gray-500">
          Menampilkan {{ keluhans.length }} dari {{ totalItems }} data
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
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50" @click="closeModal"></div>
        
        <!-- Modal -->
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
          <!-- Header -->
          <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">Detail Keluhan #{{ selectedKeluhan?.id }}</h2>
            <button @click="closeModal" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
              <XMarkIcon class="w-5 h-5" />
            </button>
          </div>
          
          <div v-if="selectedKeluhan" class="p-6 space-y-6">
            <!-- Info Pelapor -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-xs text-gray-500 uppercase tracking-wide">Nama Pelapor</label>
                <p class="font-medium text-gray-900">{{ selectedKeluhan.nama_lengkap }}</p>
              </div>
              <div>
                <label class="text-xs text-gray-500 uppercase tracking-wide">No. HPM</label>
                <p class="font-medium text-gray-900">{{ selectedKeluhan.no_hpm }}</p>
              </div>
              <div>
                <label class="text-xs text-gray-500 uppercase tracking-wide">No. WhatsApp</label>
                <a :href="'https://wa.me/' + selectedKeluhan.no_whatsapp.replace(/^0/, '62')" target="_blank" class="font-medium text-primary hover:underline">
                  {{ selectedKeluhan.no_whatsapp }}
                </a>
              </div>
              <div>
                <label class="text-xs text-gray-500 uppercase tracking-wide">Tanggal Lapor</label>
                <p class="font-medium text-gray-900">{{ formatDate(selectedKeluhan.created_at) }}</p>
              </div>
            </div>

            <!-- Kategori & Status Saat Ini -->
            <div class="flex items-center gap-3">
              <span class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">
                {{ selectedKeluhan.kategori }}
              </span>
              <span :class="[statusConfig[selectedKeluhan.status]?.color, 'px-3 py-1.5 rounded-full text-sm font-medium inline-flex items-center gap-1']">
                <component :is="statusConfig[selectedKeluhan.status]?.icon" class="w-4 h-4" />
                {{ selectedKeluhan.status }}
              </span>
            </div>

            <!-- Detail Laporan -->
            <div>
              <label class="text-xs text-gray-500 uppercase tracking-wide">Detail Laporan</label>
              <p class="mt-1 text-gray-700 whitespace-pre-wrap bg-gray-50 rounded-lg p-4">{{ selectedKeluhan.detail_laporan }}</p>
            </div>

            <!-- Foto Bukti -->
            <div v-if="selectedKeluhan.foto_bukti">
              <label class="text-xs text-gray-500 uppercase tracking-wide">Foto Bukti</label>
              <div class="mt-2">
                <img :src="'/' + selectedKeluhan.foto_bukti" alt="Bukti" class="max-w-full h-auto rounded-lg border" />
              </div>
            </div>

            <!-- Update Status -->
            <div class="border-t pt-6">
              <label class="text-sm font-medium text-gray-700 mb-2 block">Update Status</label>
              <div class="flex gap-2 mb-4">
                <button 
                  v-for="status in ['Menunggu', 'Diproses', 'Selesai']" 
                  :key="status"
                  @click="selectedKeluhan.newStatus = status"
                  :class="[
                    'flex-1 py-2.5 rounded-lg font-medium text-sm transition-all',
                    selectedKeluhan.newStatus === status
                      ? (status === 'Menunggu' ? 'bg-yellow-500 text-white' : status === 'Diproses' ? 'bg-blue-500 text-white' : 'bg-green-500 text-white')
                      : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                  ]"
                >
                  {{ status }}
                </button>
              </div>

              <label class="text-sm font-medium text-gray-700 mb-2 block">Catatan Admin (Opsional)</label>
              <textarea 
                v-model="selectedKeluhan.catatanAdmin"
                rows="3"
                placeholder="Tambahkan catatan..."
                class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none resize-none"
              ></textarea>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
              <button 
                @click="closeModal"
                class="flex-1 py-3 border border-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors"
              >
                Batal
              </button>
              <button 
                @click="updateStatus"
                :disabled="isUpdating"
                class="flex-1 py-3 bg-primary text-white rounded-xl font-medium hover:bg-teal-600 transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
              >
                <span v-if="isUpdating" class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
                {{ isUpdating ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Toast -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-4 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-4 opacity-0"
      >
        <div 
          v-if="toast.show"
          :class="[
            'fixed bottom-4 right-4 z-50 px-4 py-3 rounded-xl shadow-lg flex items-center gap-2',
            toast.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
          ]"
        >
          <CheckCircleIcon v-if="toast.type === 'success'" class="w-5 h-5" />
          <XMarkIcon v-else class="w-5 h-5" />
          {{ toast.message }}
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
