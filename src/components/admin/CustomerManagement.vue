<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { MagnifyingGlassIcon, PencilSquareIcon, TrashIcon, PlusIcon, XMarkIcon, ExclamationTriangleIcon, CheckCircleIcon, XCircleIcon, DocumentArrowUpIcon, ArrowDownTrayIcon, ArrowPathIcon } from '@heroicons/vue/24/outline'

// ==================== STATE ====================
const searchQuery = ref('')
const selectedType = ref('')
const customers = ref([])
const isLoading = ref(false)
const nextCustomerId = ref('') // Preview ID for new customer

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)
const totalItems = ref(0)
const totalPages = ref(0)

// Stats
const totalPelangganCount = ref(0)
const typeBreakdown = ref({
  R1: 0,
  R2: 0,
  N1: 0,
  S1: 0
})

// Modal States
const showModal = ref(false)
const isEditing = ref(false)
const currentCustomer = ref(null)

// Form Data
const formData = ref({
  name: '',
  phone: '',
  address: '',
  type: 'R2'
})
const formErrors = ref({})

// Delete Confirmation Dialog
const showDeleteDialog = ref(false)
const customerToDelete = ref(null)

// Import Modal
const showImportModal = ref(false)
const importFile = ref(null)
const isImporting = ref(false)
const importResult = ref(null)

// Toast Notifications
const toasts = ref([])
let toastId = 0

// Golongan Tarif Options
const typeOptions = [
  { value: 'R1', label: 'R1 (Sosial)' },
  { value: 'R2', label: 'R2 (Rumah Tangga)' },
  { value: 'N1', label: 'N1 (Niaga)' },
  { value: 'S1', label: 'S1 (Sosial Khusus)' }
]

// Pagination
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    fetchCustomers()
  }
}

// Reset to page 1 when filters change
const resetPage = () => {
  currentPage.value = 1
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
const API_BASE = '/api/pelanggan.php'

const fetchCustomers = async () => {
  isLoading.value = true
  try {
    const params = new URLSearchParams()
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (selectedType.value) params.append('type', selectedType.value)
    params.append('page', currentPage.value)
    params.append('limit', itemsPerPage.value)

    const response = await fetch(`${API_BASE}?${params.toString()}`)
    const data = await response.json()
    if (data.success) {
      customers.value = data.data
      if (data.pagination) {
        totalItems.value = data.pagination.total_items
        totalPages.value = data.pagination.total_pages
      }
      if (data.stats) {
        totalPelangganCount.value = data.stats.total_pelanggan
        typeBreakdown.value = data.stats.breakdown || { R1: 0, R2: 0, N1: 0, S1: 0 }
      }
      // Generate next customer ID preview
      generateNextCustomerId()
    } else {
      showToast('Gagal memuat data pelanggan', 'error')
    }
  } catch (error) {
    showToast('Gagal memuat data pelanggan', 'error')
  } finally {
    isLoading.value = false
  }
}

// Generate preview of next customer ID (format: HPM00001)
const generateNextCustomerId = () => {
  const hpmCustomers = customers.value.filter(c => c.customer_id && c.customer_id.startsWith('HPM'))
  if (hpmCustomers.length > 0) {
    const lastNumber = Math.max(...hpmCustomers.map(c => parseInt(c.customer_id.slice(3)) || 0))
    nextCustomerId.value = `HPM${String(lastNumber + 1).padStart(5, '0')}`
  } else {
    nextCustomerId.value = 'HPM00001'
  }
}

const saveCustomer = async () => {
  formErrors.value = {}
  
  // Validation
  if (!formData.value.name.trim()) {
    formErrors.value.name = 'Nama pelanggan wajib diisi'
  }
  if (!formData.value.phone.trim()) {
    formErrors.value.phone = 'No. Handphone wajib diisi'
  } else if (!/^\d{10,15}$/.test(formData.value.phone)) {
    formErrors.value.phone = 'No. Handphone harus 10-15 digit angka'
  }
  if (!formData.value.address.trim()) {
    formErrors.value.address = 'Alamat wajib diisi'
  }
  
  if (Object.keys(formErrors.value).length > 0) return

  isLoading.value = true
  try {
    if (isEditing.value) {
      // Update existing customer via API
      const response = await fetch(`${API_BASE}?id=${currentCustomer.value.id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData.value)
      })
      const data = await response.json()
      
      if (data.success) {
        // Update local state
        const index = customers.value.findIndex(c => c.id === currentCustomer.value.id)
        if (index !== -1) {
          customers.value[index] = data.data
        }
        showToast('Pelanggan berhasil diperbarui', 'success')
      } else {
        showToast(data.message || 'Gagal memperbarui pelanggan', 'error')
        return
      }
    } else {
      // Create new customer via API
      const response = await fetch(API_BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData.value)
      })
      const data = await response.json()
      
      if (data.success) {
        // Add to local state (push for bottom placement)
        customers.value.push(data.data)
        // Generate next ID for future adds
        generateNextCustomerId()
        showToast('Pelanggan berhasil ditambahkan', 'success')
      } else {
        showToast(data.message || 'Gagal menyimpan pelanggan', 'error')
        return
      }
    }
    closeModal()
  } catch (error) {
    showToast('Gagal menyimpan data pelanggan', 'error')
  } finally {
    isLoading.value = false
  }
}

const deleteCustomer = async () => {
  if (!customerToDelete.value) return
  
  isLoading.value = true
  try {
    const response = await fetch(`${API_BASE}?id=${customerToDelete.value.id}`, { 
      method: 'DELETE' 
    })
    const data = await response.json()
    
    if (data.success) {
      customers.value = customers.value.filter(c => c.id !== customerToDelete.value.id)
      generateNextCustomerId()
      showToast('Pelanggan berhasil dihapus', 'success')
    } else {
      showToast(data.message || 'Gagal menghapus pelanggan', 'error')
    }
    closeDeleteDialog()
  } catch (error) {
    showToast('Gagal menghapus pelanggan', 'error')
  } finally {
    isLoading.value = false
  }
}

// ==================== IMPORT FUNCTIONS ====================
const openImportModal = () => {
  showImportModal.value = true
  importFile.value = null
  importResult.value = null
}

const downloadTemplate = () => {
  window.open('/api/template_pelanggan.php', '_blank')
}

const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Validate file type
    const validTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel']
    if (!validTypes.includes(file.type) && !file.name.endsWith('.xlsx')) {
      showToast('Hanya file Excel (.xlsx/.xls) yang diperbolehkan', 'error')
      return
    }
    importFile.value = file
  }
}

const uploadFile = async () => {
  if (!importFile.value) return

  isImporting.value = true
  importResult.value = null
  
  const formData = new FormData()
  formData.append('file', importFile.value)

  try {
    const response = await fetch('/api/import_pelanggan.php', {
      method: 'POST',
      body: formData
    })
    const data = await response.json()
    
    if (data.success) {
      showToast('Import berhasil', 'success')
      fetchCustomers() // Refresh data
      importResult.value = { success: true, message: data.message, details: data.details || [] }
      // Don't close modal immediately so user can see result
    } else {
      importResult.value = { success: false, message: data.message, details: data.details || [] }
    }
  } catch (error) {
    importResult.value = { 
      success: false, 
      message: 'Terjadi kesalahan sistem saat mengimport file.', 
      details: [] 
    }
  } finally {
    isImporting.value = false
  }
}

// ==================== MODAL FUNCTIONS ====================
const openAddModal = () => {
  isEditing.value = false
  currentCustomer.value = null
  formData.value = { name: '', phone: '', address: '', type: 'R2' }
  formErrors.value = {}
  showModal.value = true
}

const openEditModal = (customer) => {
  isEditing.value = true
  currentCustomer.value = customer
  formData.value = {
    name: customer.name,
    phone: customer.phone,
    address: customer.address,
    type: customer.type
  }
  formErrors.value = {}
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  formErrors.value = {}
}

const openDeleteDialog = (customer) => {
  customerToDelete.value = customer
  showDeleteDialog.value = true
}

const closeDeleteDialog = () => {
  showDeleteDialog.value = false
  customerToDelete.value = null
}

// Get type label
const getTypeLabel = (type) => {
  const option = typeOptions.find(o => o.value === type)
  return option ? option.label : type
}

// ==================== LIFECYCLE ====================
// Watch for filter changes
watch(selectedType, () => {
  currentPage.value = 1
  fetchCustomers()
})

// Debounced search
let searchTimeout
watch(searchQuery, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    fetchCustomers()
  }, 500)
})

onMounted(() => {
  fetchCustomers()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Toast Notifications -->
    <div class="fixed top-4 right-4 z-50 space-y-2">
      <transition-group name="toast">
        <div 
          v-for="toast in toasts" 
          :key="toast.id"
          class="flex items-center gap-2 px-4 py-3 rounded-xl shadow-lg text-white font-medium min-w-[280px]"
          :class="toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'"
        >
          <CheckCircleIcon v-if="toast.type === 'success'" class="w-5 h-5" />
          <XCircleIcon v-else class="w-5 h-5" />
          {{ toast.message }}
        </div>
      </transition-group>
    </div>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Manajemen Pelanggan</h2>
        <p class="text-gray-500">Kelola data pelanggan air bersih desa.</p>
      </div>
      <div class="flex gap-2">
        <button 
          @click="openImportModal"
          class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl font-bold shadow-lg shadow-green-600/20 flex items-center gap-2 transition-all"
        >
          <DocumentArrowUpIcon class="w-5 h-5" />
          Import Excel
        </button>
        <button 
          @click="openAddModal"
          class="bg-primary hover:bg-teal-600 text-white px-4 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 flex items-center gap-2 transition-all"
        >
        <PlusIcon class="w-5 h-5" />
        Tambah Pelanggan
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-2">
      <!-- Total Pelanggan -->
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col gap-3 transition-all hover:shadow-md group relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 -mt-8 -mr-8 bg-primary/5 rounded-full group-hover:bg-primary/10 transition-colors"></div>
        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary relative z-10 transition-transform group-hover:scale-110">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
        <div class="relative z-10">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-tighter mb-1">Total Pelanggan</p>
          <div class="flex items-baseline gap-1">
            <p class="text-2xl font-black text-gray-900 leading-none">{{ totalPelangganCount.toLocaleString('id-ID') }}</p>
            <span class="text-gray-400 text-[10px] font-bold uppercase">Pax</span>
          </div>
        </div>
      </div>

      <!-- R1 (Sosial) -->
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col gap-3 transition-all hover:shadow-md group relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 -mt-8 -mr-8 bg-amber-50 rounded-full group-hover:bg-amber-100 transition-colors"></div>
        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 relative z-10 transition-transform group-hover:scale-110">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
        </div>
        <div class="relative z-10">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-tighter mb-1">R1 (Sosial)</p>
          <div class="flex items-baseline gap-1">
            <p class="text-2xl font-black text-gray-900 leading-none">{{ typeBreakdown.R1 }}</p>
            <span class="text-gray-400 text-[10px] font-bold uppercase">PLG</span>
          </div>
        </div>
      </div>

      <!-- R2 (Rumah Tangga) -->
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col gap-3 transition-all hover:shadow-md group relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 -mt-8 -mr-8 bg-indigo-50 rounded-full group-hover:bg-indigo-100 transition-colors"></div>
        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 relative z-10 transition-transform group-hover:scale-110">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
        </div>
        <div class="relative z-10">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-tighter mb-1">R2 (Rumah Tangga)</p>
          <div class="flex items-baseline gap-1">
            <p class="text-2xl font-black text-gray-900 leading-none">{{ typeBreakdown.R2 }}</p>
            <span class="text-gray-400 text-[10px] font-bold uppercase">PLG</span>
          </div>
        </div>
      </div>

      <!-- N1 (Niaga) -->
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col gap-3 transition-all hover:shadow-md group relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 -mt-8 -mr-8 bg-emerald-50 rounded-full group-hover:bg-emerald-100 transition-colors"></div>
        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 relative z-10 transition-transform group-hover:scale-110">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
        </div>
        <div class="relative z-10">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-tighter mb-1">N1 (Niaga)</p>
          <div class="flex items-baseline gap-1">
            <p class="text-2xl font-black text-gray-900 leading-none">{{ typeBreakdown.N1 }}</p>
            <span class="text-gray-400 text-[10px] font-bold uppercase">PLG</span>
          </div>
        </div>
      </div>

      <!-- S1 (Sosial Khusus) -->
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col gap-3 transition-all hover:shadow-md group relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 -mt-8 -mr-8 bg-rose-50 rounded-full group-hover:bg-rose-100 transition-colors"></div>
        <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600 relative z-10 transition-transform group-hover:scale-110">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
        </div>
        <div class="relative z-10">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-tighter mb-1">S1 (Sosial Khusus)</p>
          <div class="flex items-baseline gap-1">
            <p class="text-2xl font-black text-gray-900 leading-none">{{ typeBreakdown.S1 }}</p>
            <span class="text-gray-400 text-[10px] font-bold uppercase">PLG</span>
          </div>
        </div>
      </div>
    </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
      <div class="relative flex-1">
        <MagnifyingGlassIcon class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Cari pelanggan berdasarkan Nama atau ID..." 
          class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm"
        >
      </div>
      <select 
        v-model="selectedType"
        class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary/50"
      >
        <option value="">Semua Golongan</option>
        <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
      </select>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading && customers.length === 0" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
      <div class="animate-spin w-8 h-8 border-4 border-primary border-t-transparent rounded-full mx-auto mb-4"></div>
      <p class="text-gray-500">Memuat data pelanggan...</p>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="bg-primary text-white font-bold uppercase text-xs">
            <tr>
              <th class="px-6 py-4">ID Pelanggan</th>
              <th class="px-6 py-4">Nama Lengkap</th>
              <th class="px-6 py-4">No. Handphone</th>
              <th class="px-6 py-4">Alamat</th>
              <th class="px-6 py-4">Golongan</th>
              <th class="px-6 py-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="customers.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                Tidak ada data pelanggan ditemukan.
              </td>
            </tr>
            <tr v-for="customer in customers" :key="customer.id" class="hover:bg-frozen/20 transition-colors">
              <td class="px-6 py-4 font-mono text-gray-600 font-medium">{{ customer.customer_id }}</td>
              <td class="px-6 py-4 font-bold text-gray-900">{{ customer.name }}</td>
              <td class="px-6 py-4 text-gray-600">{{ customer.phone }}</td>
              <td class="px-6 py-4 text-gray-600 max-w-xs truncate">{{ customer.address }}</td>
              <td class="px-6 py-4">
                <span class="px-2 py-1 rounded-lg bg-gray-100 text-gray-600 font-medium text-xs border border-gray-200">
                  {{ getTypeLabel(customer.type) }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <button 
                    @click="openEditModal(customer)"
                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-full transition-colors"
                    title="Edit"
                  >
                    <PencilSquareIcon class="w-5 h-5" />
                  </button>
                  <button 
                    @click="openDeleteDialog(customer)"
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
          Menampilkan {{ customers.length }} dari {{ totalItems }} data
        </div>
        <div v-if="totalPages > 1" class="flex items-center gap-2">
          <button 
            @click="goToPage(1)" 
            :disabled="currentPage === 1"
            class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            &laquo;
          </button>
          <button 
            @click="goToPage(currentPage - 1)" 
            :disabled="currentPage === 1"
            class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            &lsaquo;
          </button>
          <template v-for="page in totalPages" :key="page">
            <button 
              v-if="page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)"
              @click="goToPage(page)"
              class="px-3 py-1.5 text-sm rounded-lg border transition-colors"
              :class="page === currentPage ? 'bg-primary text-white border-primary' : 'border-gray-200 hover:bg-gray-50'"
            >
              {{ page }}
            </button>
            <span v-else-if="page === currentPage - 2 || page === currentPage + 2" class="text-gray-400">...</span>
          </template>
          <button 
            @click="goToPage(currentPage + 1)" 
            :disabled="currentPage === totalPages"
            class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            &rsaquo;
          </button>
          <button 
            @click="goToPage(totalPages)" 
            :disabled="currentPage === totalPages"
            class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            &raquo;
          </button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
        
        <!-- Modal Content -->
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 transform transition-all">
          <!-- Close Button -->
          <button 
            @click="closeModal"
            class="absolute top-4 right-4 p-1 text-gray-400 hover:text-gray-600 transition-colors"
          >
            <XMarkIcon class="w-6 h-6" />
          </button>
          
          <!-- Header -->
          <h3 class="text-xl font-bold text-gray-900 mb-6">
            {{ isEditing ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru' }}
          </h3>
          
          <!-- Form -->
          <form @submit.prevent="saveCustomer" class="space-y-4">
            <!-- ID Pelanggan -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">ID Pelanggan</label>
              <div class="w-full px-4 py-2 bg-gray-100 border border-gray-200 rounded-lg text-gray-600 font-mono font-medium">
                {{ isEditing ? currentCustomer?.customer_id : nextCustomerId }}
                <span v-if="!isEditing" class="text-xs text-gray-400 ml-2">(Auto-generate)</span>
              </div>
            </div>
            
            <!-- Nama -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pelanggan</label>
              <input 
                v-model="formData.name"
                type="text"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50"
                :class="formErrors.name ? 'border-red-500' : 'border-gray-200'"
                placeholder="Masukkan nama lengkap"
              >
              <p v-if="formErrors.name" class="text-red-500 text-xs mt-1">{{ formErrors.name }}</p>
            </div>
            
            <!-- No. Handphone -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">No. Handphone</label>
              <input 
                v-model="formData.phone"
                type="text"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50"
                :class="formErrors.phone ? 'border-red-500' : 'border-gray-200'"
                placeholder="Contoh: 081234567890"
              >
              <p v-if="formErrors.phone" class="text-red-500 text-xs mt-1">{{ formErrors.phone }}</p>
            </div>
            
            <!-- Alamat -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
              <textarea 
                v-model="formData.address"
                rows="3"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none"
                :class="formErrors.address ? 'border-red-500' : 'border-gray-200'"
                placeholder="Masukkan alamat lengkap"
              ></textarea>
              <p v-if="formErrors.address" class="text-red-500 text-xs mt-1">{{ formErrors.address }}</p>
            </div>
            
            <!-- Golongan Tarif -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Golongan Tarif</label>
              <select 
                v-model="formData.type"
                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50"
              >
                <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
            </div>
            
            <!-- Buttons -->
            <div class="flex gap-3 pt-4">
              <button 
                type="button"
                @click="closeModal"
                class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors"
              >
                Batal
              </button>
              <button 
                type="submit"
                :disabled="isLoading"
                class="flex-1 px-4 py-2 bg-primary text-white rounded-xl font-bold hover:bg-teal-600 transition-colors disabled:opacity-50"
              >
                {{ isLoading ? 'Menyimpan...' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Import Modal -->
    <Teleport to="body">
      <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showImportModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 transform transition-all">
          <button @click="showImportModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <XMarkIcon class="w-6 h-6" />
          </button>
          
          <h3 class="text-xl font-bold text-gray-900 mb-2">Import Data Pelanggan</h3>
          <p class="text-sm text-gray-500 mb-6">Unggah file Excel untuk menambahkan banyak pelanggan sekaligus.</p>
          
          <!-- Drop Zone -->
          <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center bg-gray-50 mb-4 hover:bg-gray-100 transition-colors relative">
            <input 
              type="file" 
              class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
              accept=".xlsx, .xls"
              @change="handleFileChange"
            >
            <div v-if="importFile" class="flex flex-col items-center">
              <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-2">
                <DocumentArrowUpIcon class="w-6 h-6" />
              </div>
              <p class="font-medium text-gray-800">{{ importFile.name }}</p>
              <p class="text-xs text-gray-500">{{ (importFile.size / 1024).toFixed(1) }} KB</p>
            </div>
            <div v-else class="flex flex-col items-center">
              <DocumentArrowUpIcon class="w-12 h-12 text-gray-400 mb-2" />
              <p class="font-medium text-gray-600">Klik atau tarik file Excel ke sini</p>
              <p class="text-xs text-gray-400 mt-1">Format: .xlsx atau .xls</p>
            </div>
          </div>
          
          <!-- Template Link -->
          <div class="flex justify-between items-center mb-6">
            <button @click="downloadTemplate" class="text-primary hover:text-teal-700 text-sm font-medium flex items-center gap-1">
              <ArrowDownTrayIcon class="w-4 h-4" />
              Unduh Template Excel
            </button>
          </div>
          
          <!-- Result / Progress -->
          <div v-if="isImporting" class="mb-6 p-4 bg-blue-50 rounded-xl flex items-center gap-3">
            <ArrowPathIcon class="w-5 h-5 text-blue-600 animate-spin" />
            <span class="text-blue-700 font-medium">Memproses data... Mohon tunggu.</span>
          </div>
          
          <div v-if="importResult" class="mb-6 p-4 rounded-xl text-sm" :class="importResult.success ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
            <div class="flex items-start gap-2">
              <CheckCircleIcon v-if="importResult.success" class="w-5 h-5 flex-shrink-0 mt-0.5" />
              <ExclamationTriangleIcon v-else class="w-5 h-5 flex-shrink-0 mt-0.5" />
              <div>
                <p class="font-bold">{{ importResult.message }}</p>
                <ul v-if="importResult.details.length > 0" class="mt-2 text-xs list-disc list-inside opacity-80 max-h-32 overflow-y-auto">
                  <li v-for="(err, idx) in importResult.details" :key="idx">{{ err }}</li>
                </ul>
              </div>
            </div>
          </div>
          
          <!-- Actions -->
          <button 
            @click="uploadFile" 
            :disabled="!importFile || isImporting"
            class="w-full py-3 rounded-xl font-bold text-white transition-all shadow-lg flex items-center justify-center gap-2"
            :class="(!importFile || isImporting) ? 'bg-gray-300 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700 shadow-green-600/20'"
          >
            Import Sekarang
          </button>
        </div>
      </div>
    </Teleport>

    <!-- Delete Confirmation Dialog -->
    <Teleport to="body">
      <div v-if="showDeleteDialog" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeDeleteDialog"></div>
        
        <!-- Dialog Content -->
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center transform transition-all">
          <!-- Warning Icon -->
          <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <ExclamationTriangleIcon class="w-8 h-8 text-red-600" />
          </div>
          
          <!-- Title -->
          <h3 class="text-xl font-bold text-gray-900 mb-2">Konfirmasi Hapus</h3>
          
          <!-- Message -->
          <p class="text-gray-600 mb-6">
            Apakah Anda yakin ingin menghapus pelanggan <strong>{{ customerToDelete?.name }}</strong>? 
            Tindakan ini tidak dapat dibatalkan.
          </p>
          
          <!-- Buttons -->
          <div class="flex gap-3">
            <button 
              @click="closeDeleteDialog"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors"
            >
              Batal
            </button>
            <button 
              @click="deleteCustomer"
              :disabled="isLoading"
              class="flex-1 px-4 py-2 bg-accent text-white rounded-xl font-bold hover:bg-orange-600 transition-colors disabled:opacity-50"
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
/* Toast Animation */
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
