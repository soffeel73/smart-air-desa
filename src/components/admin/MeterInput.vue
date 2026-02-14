<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { MagnifyingGlassIcon, PencilSquareIcon, TrashIcon, PlusIcon, XMarkIcon, ExclamationTriangleIcon, CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/outline'

// ==================== STATE ====================
const meterReadings = ref([])
const customers = ref([])
const isLoading = ref(false)

// Filter State
const filterYear = ref(new Date().getFullYear())
const filterMonth = ref(null) // null = all months
const searchQuery = ref('')

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)
const totalItems = ref(0)
const totalPages = ref(0)

// Stats
const totalPemakaianCount = ref(0)

// Modal States
const showModal = ref(false)
const isEditing = ref(false)
const currentReading = ref(null)

// Form Data
const formData = ref({
  pelanggan_id: '',
  period_year: new Date().getFullYear(),
  period_month: new Date().getMonth() + 1,
  meter_awal: 0,
  meter_akhir: 0
})
const selectedCustomer = ref(null)
const formErrors = ref({})

// Delete Confirmation Dialog
const showDeleteDialog = ref(false)
const readingToDelete = ref(null)

// Toast Notifications
const toasts = ref([])
let toastId = 0

// Month options
const monthOptions = [
  { value: null, label: 'Semua Bulan' },
  { value: 1, label: 'Januari' },
  { value: 2, label: 'Februari' },
  { value: 3, label: 'Maret' },
  { value: 4, label: 'April' },
  { value: 5, label: 'Mei' },
  { value: 6, label: 'Juni' },
  { value: 7, label: 'Juli' },
  { value: 8, label: 'Agustus' },
  { value: 9, label: 'September' },
  { value: 10, label: 'Oktober' },
  { value: 11, label: 'November' },
  { value: 12, label: 'Desember' }
]

// ==================== COMPUTED ====================
const jumlahPakai = computed(() => {
  const usage = formData.value.meter_akhir - formData.value.meter_awal
  return usage > 0 ? usage : 0
})

// Constants
const BIAYA_ADMIN = 2000

// Progressive tariff calculation + admin fee
const totalBiaya = computed(() => {
  const usage = jumlahPakai.value
  let biayaAir = 0
  
  // Block 1: 0-5 m3 @ Rp 1.500
  if (usage <= 5) {
    biayaAir = usage * 1500
  }
  // Block 2: >5-10 m3 @ Rp 2.000
  else if (usage <= 10) {
    biayaAir = (5 * 1500) + ((usage - 5) * 2000)
  }
  // Block 3: >10 m3 @ Rp 2.500
  else {
    biayaAir = (5 * 1500) + (5 * 2000) + ((usage - 10) * 2500)
  }
  
  // Add admin fee
  return biayaAir + BIAYA_ADMIN
})

const getMonthLabel = (month) => {
  const found = monthOptions.find(m => m.value === month)
  return found ? found.label : month
}

// Pagination
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    fetchMeterReadings()
  }
}

// ==================== TOAST FUNCTIONS ====================
const showToast = (message, type = 'success') => {
  const id = ++toastId
  toasts.value.push({ id, message, type })
  setTimeout(() => {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }, 3000)
}

// ==================== API FUNCTIONS ====================
const API_BASE = '/api/input_meter.php'

const fetchMeterReadings = async () => {
  isLoading.value = true
  try {
    const params = new URLSearchParams()
    if (filterYear.value) params.append('year', filterYear.value)
    if (filterMonth.value) params.append('month', filterMonth.value)
    if (searchQuery.value) params.append('search', searchQuery.value)
    params.append('page', currentPage.value)
    params.append('limit', itemsPerPage.value)
    
    const response = await fetch(`${API_BASE}?${params.toString()}`)
    const data = await response.json()
    if (data.success) {
      meterReadings.value = data.data
      if (data.pagination) {
        totalItems.value = data.pagination.total_items
        totalPages.value = data.pagination.total_pages
      }
      if (data.stats) {
        totalPemakaianCount.value = data.stats.total_pemakaian
      }
    } else {
      showToast('Gagal memuat data input meter', 'error')
    }
  } catch (error) {
    showToast('Gagal memuat data input meter', 'error')
  } finally {
    isLoading.value = false
  }
}

const fetchCustomers = async () => {
  try {
    const response = await fetch(`${API_BASE}?action=pelanggans`)
    const data = await response.json()
    if (data.success) {
      // Sort customers by customer_id
      customers.value = data.data.sort((a, b) => {
        return a.customer_id.localeCompare(b.customer_id)
      })
    }
  } catch (error) {
    console.error('Failed to fetch customers:', error)
  }
}

const fetchMeterAwal = async () => {
  if (!formData.value.pelanggan_id) {
    formData.value.meter_awal = 0
    return
  }
  
  try {
    const params = new URLSearchParams({
      action: 'meter_awal',
      pelanggan_id: formData.value.pelanggan_id,
      year: formData.value.period_year,
      month: formData.value.period_month
    })
    
    const response = await fetch(`${API_BASE}?${params.toString()}`)
    const data = await response.json()
    if (data.success) {
      formData.value.meter_awal = data.meter_awal
    }
  } catch (error) {
    console.error('Failed to fetch meter awal:', error)
  }
}

const saveMeterReading = async () => {
  formErrors.value = {}
  
  // Validation
  if (!formData.value.pelanggan_id) {
    formErrors.value.pelanggan_id = 'Pelanggan wajib dipilih'
  }
  if (formData.value.meter_akhir < formData.value.meter_awal) {
    formErrors.value.meter_akhir = 'Meter akhir tidak boleh lebih kecil dari meter awal'
  }
  
  if (Object.keys(formErrors.value).length > 0) return

  isLoading.value = true
  try {
    if (isEditing.value) {
      const response = await fetch(`${API_BASE}?id=${currentReading.value.id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData.value)
      })
      const data = await response.json()
      
      if (data.success) {
        const index = meterReadings.value.findIndex(m => m.id === currentReading.value.id)
        if (index !== -1) {
          meterReadings.value[index] = data.data
        }
        showToast('Data input meter berhasil diperbarui', 'success')
      } else {
        showToast(data.message || 'Gagal memperbarui data', 'error')
        return
      }
    } else {
      const response = await fetch(API_BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData.value)
      })
      const data = await response.json()
      
      if (data.success) {
        meterReadings.value.push(data.data)
        showToast('Data input meter berhasil disimpan', 'success')
      } else {
        showToast(data.message || 'Gagal menyimpan data', 'error')
        return
      }
    }
    closeModal()
  } catch (error) {
    showToast('Gagal menyimpan data input meter', 'error')
  } finally {
    isLoading.value = false
  }
}

const deleteMeterReading = async () => {
  if (!readingToDelete.value) return
  
  isLoading.value = true
  try {
    const response = await fetch(`${API_BASE}?id=${readingToDelete.value.id}`, { 
      method: 'DELETE' 
    })
    const data = await response.json()
    
    if (data.success) {
      meterReadings.value = meterReadings.value.filter(m => m.id !== readingToDelete.value.id)
      showToast('Data input meter berhasil dihapus', 'success')
    } else {
      showToast(data.message || 'Gagal menghapus data', 'error')
    }
    closeDeleteDialog()
  } catch (error) {
    showToast('Gagal menghapus data', 'error')
  } finally {
    isLoading.value = false
  }
}

// ==================== MODAL FUNCTIONS ====================
const openAddModal = () => {
  isEditing.value = false
  currentReading.value = null
  formData.value = {
    pelanggan_id: '',
    period_year: filterYear.value,
    period_month: filterMonth.value,
    meter_awal: 0,
    meter_akhir: 0
  }
  selectedCustomer.value = null
  formErrors.value = {}
  showModal.value = true
}

const openEditModal = (reading) => {
  isEditing.value = true
  currentReading.value = reading
  formData.value = {
    pelanggan_id: reading.pelanggan_id,
    period_year: reading.period_year,
    period_month: reading.period_month,
    meter_awal: parseInt(reading.meter_awal),
    meter_akhir: parseInt(reading.meter_akhir)
  }
  selectedCustomer.value = customers.value.find(c => c.id == reading.pelanggan_id) || null
  formErrors.value = {}
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  formErrors.value = {}
}

const openDeleteDialog = (reading) => {
  readingToDelete.value = reading
  showDeleteDialog.value = true
}

const closeDeleteDialog = () => {
  showDeleteDialog.value = false
  readingToDelete.value = null
}

// Watch for customer selection
watch(() => formData.value.pelanggan_id, (newVal) => {
  if (newVal) {
    selectedCustomer.value = customers.value.find(c => c.id == newVal) || null
    if (!isEditing.value) {
      fetchMeterAwal()
    }
  } else {
    selectedCustomer.value = null
    formData.value.meter_awal = 0
  }
})

// Watch for period change
watch([() => formData.value.period_year, () => formData.value.period_month], () => {
  if (formData.value.pelanggan_id && !isEditing.value) {
    fetchMeterAwal()
  }
})

// Watch for filter change
watch([filterYear, filterMonth], () => {
  currentPage.value = 1
  fetchMeterReadings()
})

// Debounced search
let searchTimeout
watch(searchQuery, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    fetchMeterReadings()
  }, 500)
})

onMounted(() => {
  fetchMeterReadings()
  fetchCustomers()
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
        <h2 class="text-2xl font-bold text-gray-900">Input Meter</h2>
        <p class="text-gray-500">Kelola data penggunaan air pelanggan bulanan.</p>
      </div>
      <button 
        @click="openAddModal"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white rounded-xl font-bold shadow-lg shadow-primary/30 hover:bg-teal-600 transition-all"
      >
        <PlusIcon class="w-5 h-5" />
        Tambah Input Meter
      </button>
    </div>

    <!-- Stats Card -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 transition-all hover:shadow-md">
        <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500">Total Pemakaian</p>
          <p class="text-xl font-bold text-gray-900">{{ totalPemakaianCount.toLocaleString('id-ID') }} m³</p>
        </div>
      </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
      <div class="flex flex-wrap items-center gap-4">
        <!-- Search -->
        <div class="flex-1 min-w-[200px]">
          <div class="relative">
            <MagnifyingGlassIcon class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari ID Pelanggan atau Nama..."
              class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50"
            >
          </div>
        </div>
        <!-- Periode Filter -->
        <div class="flex items-center gap-2">
          <label class="text-sm font-medium text-gray-600">Periode:</label>
          <select 
            v-model="filterMonth"
            class="px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 bg-white"
          >
            <option v-for="m in monthOptions" :key="m.value" :value="m.value">{{ m.label }}</option>
          </select>
          <select 
            v-model="filterYear"
            class="px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 bg-white"
          >
            <option v-for="y in [2026, 2027, 2028, 2029, 2030]" :key="y" :value="y">{{ y }}</option>
          </select>
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
      <div v-else-if="meterReadings.length === 0" class="text-center py-12">
        <MagnifyingGlassIcon class="w-12 h-12 mx-auto text-gray-300 mb-4" />
        <p class="text-gray-500">{{ searchQuery ? 'Tidak ada data yang cocok dengan pencarian' : 'Belum ada data input meter untuk periode ini' }}</p>
      </div>
      
      <!-- Data Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-primary/10 border-b border-primary/20">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-bold text-primary uppercase tracking-wider">ID Pelanggan</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-primary uppercase tracking-wider">Nama & Alamat</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-primary uppercase tracking-wider">Periode</th>
              <th class="px-6 py-4 text-center text-xs font-bold text-primary uppercase tracking-wider">Meter Awal</th>
              <th class="px-6 py-4 text-center text-xs font-bold text-primary uppercase tracking-wider">Meter Akhir</th>
              <th class="px-6 py-4 text-center text-xs font-bold text-primary uppercase tracking-wider">Jumlah Pakai</th>
              <th class="px-6 py-4 text-right text-xs font-bold text-primary uppercase tracking-wider">Total Biaya</th>
              <th class="px-6 py-4 text-center text-xs font-bold text-primary uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="reading in meterReadings" :key="reading.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 font-mono text-gray-600">{{ reading.customer_id }}</td>
              <td class="px-6 py-4">
                <div class="font-bold text-gray-900">{{ reading.pelanggan_name }}</div>
                <div class="text-sm text-gray-500 truncate max-w-xs">{{ reading.pelanggan_address }}</div>
              </td>
              <td class="px-6 py-4 text-gray-600">{{ getMonthLabel(reading.period_month) }} {{ reading.period_year }}</td>
              <td class="px-6 py-4 text-center font-mono">{{ reading.meter_awal }} m³</td>
              <td class="px-6 py-4 text-center font-mono font-bold">{{ reading.meter_akhir }} m³</td>
              <td class="px-6 py-4 text-center">
                <span class="px-2 py-1 rounded-lg bg-blue-100 text-blue-700 font-bold text-sm">
                  {{ reading.jumlah_pakai }} m³
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <span class="font-bold text-amber-500 text-lg">
                  Rp {{ parseFloat(reading.total_biaya).toLocaleString('id-ID') }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <button 
                    @click="openEditModal(reading)"
                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-full transition-colors"
                    title="Edit"
                  >
                    <PencilSquareIcon class="w-5 h-5" />
                  </button>
                  <button 
                    @click="openDeleteDialog(reading)"
                    class="p-2 text-red-600 hover:bg-red-50 rounded-full transition-colors"
                    title="Hapus"
                  >
                    <TrashIcon class="w-5 h-5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination -->
      <div class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-sm text-gray-500">
          Menampilkan {{ meterReadings.length }} dari {{ totalItems }} data
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

    <!-- Add/Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
        
        <!-- Modal Content -->
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6 transform transition-all max-h-[90vh] overflow-y-auto">
          <!-- Close Button -->
          <button 
            @click="closeModal"
            class="absolute top-4 right-4 p-1 text-gray-400 hover:text-gray-600 transition-colors"
          >
            <XMarkIcon class="w-6 h-6" />
          </button>
          
          <!-- Header -->
          <h3 class="text-xl font-bold text-gray-900 mb-6">
            {{ isEditing ? 'Edit Input Meter' : 'Tambah Input Meter Baru' }}
          </h3>
          
          <!-- Form -->
          <form @submit.prevent="saveMeterReading" class="space-y-6">
            <!-- Pelanggan Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Pelanggan</label>
              <select 
                v-model="formData.pelanggan_id"
                :disabled="isEditing"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 bg-white"
                :class="[formErrors.pelanggan_id ? 'border-red-500' : 'border-gray-200', isEditing ? 'bg-gray-100 cursor-not-allowed' : '']"
              >
                <option value="">-- Pilih Pelanggan --</option>
                <option v-for="c in customers" :key="c.id" :value="c.id">
                  {{ c.customer_id }} - {{ c.name }}
                </option>
              </select>
              <p v-if="formErrors.pelanggan_id" class="text-red-500 text-xs mt-1">{{ formErrors.pelanggan_id }}</p>
            </div>
            
            <!-- Customer Info (Auto-fill) -->
            <div v-if="selectedCustomer" class="p-4 bg-frozen/30 rounded-xl border border-frozen">
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-gray-500">Nama:</span>
                  <span class="ml-2 font-bold text-gray-900">{{ selectedCustomer.name }}</span>
                </div>
                <div>
                  <span class="text-gray-500">Golongan:</span>
                  <span class="ml-2 font-bold text-gray-900">{{ selectedCustomer.type }}</span>
                </div>
                <div class="col-span-2">
                  <span class="text-gray-500">Alamat:</span>
                  <span class="ml-2 font-bold text-gray-900">{{ selectedCustomer.address }}</span>
                </div>
              </div>
            </div>
            
            <!-- Periode -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                <select 
                  v-model="formData.period_month"
                  :disabled="isEditing"
                  class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 bg-white"
                  :class="isEditing ? 'bg-gray-100 cursor-not-allowed' : ''"
                >
                  <option v-for="m in monthOptions" :key="m.value" :value="m.value">{{ m.label }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                <select 
                  v-model="formData.period_year"
                  :disabled="isEditing"
                  class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 bg-white"
                  :class="isEditing ? 'bg-gray-100 cursor-not-allowed' : ''"
                >
                  <option v-for="y in [2026, 2027, 2028, 2029, 2030]" :key="y" :value="y">{{ y }}</option>
                </select>
              </div>
            </div>
            
            <!-- Meter Readings -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Meter Awal (m³)</label>
                <input 
                  v-model.number="formData.meter_awal"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 font-mono font-bold"
                  placeholder="Masukkan angka meteran awal"
                >
                <p class="text-xs text-gray-400 mt-1">Otomatis terisi dari periode sebelumnya (dapat diubah manual)</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Meter Akhir (m³)</label>
                <input 
                  v-model.number="formData.meter_akhir"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 font-mono font-bold"
                  :class="formErrors.meter_akhir ? 'border-red-500' : 'border-gray-200'"
                  placeholder="Masukkan angka meteran"
                >
                <p v-if="formErrors.meter_akhir" class="text-red-500 text-xs mt-1">{{ formErrors.meter_akhir }}</p>
              </div>
            </div>
            
            <!-- Live Calculation Preview -->
            <div class="p-4 bg-primary text-white rounded-xl">
              <div class="text-sm text-white/80 mb-3">Kalkulasi Real-time</div>
              <div class="grid grid-cols-2 gap-4 mb-3">
                <div>
                  <div class="text-xs uppercase tracking-wider opacity-70 mb-1">Jumlah Pakai</div>
                  <div class="text-2xl font-bold">{{ jumlahPakai }} <span class="text-sm font-normal">m³</span></div>
                </div>
                <div>
                  <div class="text-xs uppercase tracking-wider opacity-70 mb-1">Biaya Air</div>
                  <div class="text-xl font-bold">Rp {{ (totalBiaya - BIAYA_ADMIN).toLocaleString('id-ID') }}</div>
                </div>
              </div>
              <div class="pt-3 border-t border-white/20 space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="opacity-80">Biaya Admin</span>
                  <span class="font-bold">Rp {{ BIAYA_ADMIN.toLocaleString('id-ID') }}</span>
                </div>
                <div class="flex justify-between text-lg pt-2 border-t border-white/20">
                  <span class="font-bold">Total Tagihan</span>
                  <span class="font-bold text-amber-400">Rp {{ totalBiaya.toLocaleString('id-ID') }}</span>
                </div>
              </div>
              <div class="mt-3 pt-3 border-t border-white/20 text-xs text-white/70">
                Tarif: 0-5m³ @Rp1.500 | 5-10m³ @Rp2.000 | >10m³ @Rp2.500 + Admin Rp2.000
              </div>
            </div>
            
            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
              <button 
                type="button"
                @click="closeModal"
                class="px-6 py-2.5 rounded-xl text-gray-600 font-bold hover:bg-gray-100 transition-colors border border-gray-200"
              >
                Batal
              </button>
              <button 
                type="submit"
                :disabled="isLoading"
                class="px-6 py-2.5 rounded-xl bg-primary text-white font-bold shadow-lg shadow-primary/30 hover:bg-teal-600 transition-all disabled:opacity-50"
              >
                {{ isLoading ? 'Menyimpan...' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Delete Confirmation Dialog -->
    <Teleport to="body">
      <div v-if="showDeleteDialog" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeDeleteDialog"></div>
        
        <!-- Dialog Content -->
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
          <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
            <ExclamationTriangleIcon class="w-8 h-8 text-red-500" />
          </div>
          
          <h3 class="text-xl font-bold text-gray-900 mb-2">Konfirmasi Hapus</h3>
          <p class="text-gray-500 mb-6">
            Apakah Anda yakin ingin menghapus data input meter 
            <span class="font-bold text-gray-900">{{ readingToDelete?.pelanggan_name }}</span> 
            periode {{ getMonthLabel(readingToDelete?.period_month) }} {{ readingToDelete?.period_year }}?
          </p>
          
          <div class="flex items-center justify-center gap-3">
            <button 
              @click="closeDeleteDialog"
              class="px-6 py-2.5 rounded-xl text-gray-600 font-bold hover:bg-gray-100 transition-colors border border-gray-200"
            >
              Batal
            </button>
            <button 
              @click="deleteMeterReading"
              :disabled="isLoading"
              class="px-6 py-2.5 rounded-xl bg-amber-500 text-white font-bold shadow-lg shadow-amber-500/30 hover:bg-amber-600 transition-all disabled:opacity-50"
            >
              {{ isLoading ? 'Menghapus...' : 'Ya, Hapus' }}
            </button>
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
