<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { 
  ExclamationTriangleIcon,
  PhotoIcon,
  XMarkIcon,
  CheckCircleIcon,
  ArrowLeftIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()

// Form data
const form = ref({
  nama_lengkap: '',
  no_hpm: '',
  kategori: '',
  detail_laporan: '',
  no_whatsapp: '',
  foto_bukti: null
})

// UI state
const isLoading = ref(false)
const showSuccess = ref(false)
const errorMessage = ref('')
const previewUrl = ref(null)

// Kategori options
const kategoriOptions = [
  { value: 'Pipa Bocor', label: '💧 Pipa Bocor', desc: 'Kebocoran pada pipa atau saluran air' },
  { value: 'Air Tidak Mengalir', label: '🚫 Air Tidak Mengalir', desc: 'Air tidak keluar atau debit sangat kecil' },
  { value: 'Meteran Rusak', label: '🔧 Meteran Rusak', desc: 'Meteran air rusak atau tidak akurat' },
  { value: 'Kesalahan Tagihan', label: '💰 Kesalahan Tagihan', desc: 'Tagihan tidak sesuai dengan pemakaian' },
  { value: 'Kualitas Air', label: '🌊 Kualitas Air', desc: 'Air keruh, berbau, atau tidak layak' },
  { value: 'Lainnya', label: '📋 Lainnya', desc: 'Keluhan lain yang tidak tercantum' }
]

const API_BASE = '/api/keluhan.php'

// Computed validations
const isFormValid = computed(() => {
  return form.value.nama_lengkap.trim() &&
    form.value.no_hpm.trim() &&
    form.value.kategori &&
    form.value.detail_laporan.trim().length >= 10 &&
    form.value.no_whatsapp.trim()
})

// Handle file selection
const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Validate file type
    if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
      errorMessage.value = 'Format file tidak valid. Gunakan JPG atau PNG.'
      return
    }
    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
      errorMessage.value = 'Ukuran file maksimal 5MB'
      return
    }
    
    form.value.foto_bukti = file
    previewUrl.value = URL.createObjectURL(file)
    errorMessage.value = ''
  }
}

// Remove selected file
const removeFile = () => {
  form.value.foto_bukti = null
  if (previewUrl.value) {
    URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = null
  }
}

// Submit form
const handleSubmit = async () => {
  if (!isFormValid.value) {
    errorMessage.value = 'Mohon lengkapi semua field yang wajib diisi'
    return
  }
  
  isLoading.value = true
  errorMessage.value = ''
  
  try {
    const formData = new FormData()
    formData.append('nama_lengkap', form.value.nama_lengkap.trim())
    formData.append('no_hpm', form.value.no_hpm.trim().toUpperCase())
    formData.append('kategori', form.value.kategori)
    formData.append('detail_laporan', form.value.detail_laporan.trim())
    formData.append('no_whatsapp', form.value.no_whatsapp.trim())
    
    if (form.value.foto_bukti) {
      formData.append('foto_bukti', form.value.foto_bukti)
    }
    
    const response = await fetch(`${API_BASE}?action=submit`, {
      method: 'POST',
      body: formData
    })
    
    const data = await response.json()
    
    if (data.success) {
      showSuccess.value = true
    } else {
      errorMessage.value = data.message || 'Gagal mengirim laporan'
    }
  } catch (error) {
    errorMessage.value = 'Gagal menghubungi server. Silakan coba lagi.'
  } finally {
    isLoading.value = false
  }
}

// Go back to home
const goHome = () => {
  router.push('/')
}

// Submit another complaint
const submitAnother = () => {
  form.value = {
    nama_lengkap: '',
    no_hpm: '',
    kategori: '',
    detail_laporan: '',
    no_whatsapp: '',
    foto_bukti: null
  }
  previewUrl.value = null
  showSuccess.value = false
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-cyan-50">
    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-lg shadow-sm sticky top-0 z-50">
      <div class="container mx-auto px-4 py-4 flex items-center gap-4">
        <button @click="goHome" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
          <ArrowLeftIcon class="w-5 h-5 text-gray-600" />
        </button>
        <div>
          <h1 class="text-xl font-bold text-gray-900">Lapor Keluhan</h1>
          <p class="text-sm text-gray-500">HIPPAMS - Layanan Air Bersih</p>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-4 py-8 max-w-2xl">
      <!-- Success State -->
      <div v-if="showSuccess" class="bg-white rounded-2xl shadow-xl p-8 text-center" data-aos="zoom-in">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
          <CheckCircleIcon class="w-10 h-10 text-green-600" />
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-3">Laporan Terkirim!</h2>
        <p class="text-gray-600 mb-8 leading-relaxed">
          Laporan Anda telah diterima. Tim HIPPAMS akan segera menghubungi Anda melalui WhatsApp untuk tindak lanjut.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <button 
            @click="goHome"
            class="px-6 py-3 bg-primary text-white rounded-xl font-semibold hover:bg-teal-600 transition-colors"
          >
            Kembali ke Beranda
          </button>
          <button 
            @click="submitAnother"
            class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-colors"
          >
            Kirim Laporan Lain
          </button>
        </div>
      </div>

      <!-- Form -->
      <div v-else class="space-y-6">
        <!-- Info Card -->
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex gap-3">
          <ExclamationTriangleIcon class="w-6 h-6 text-amber-600 flex-shrink-0" />
          <div>
            <h3 class="font-semibold text-amber-800">Laporkan Kendala Anda</h3>
            <p class="text-sm text-amber-700">Isi formulir di bawah dengan lengkap agar tim kami dapat segera menindaklanjuti keluhan Anda.</p>
          </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
          <div class="bg-gradient-to-r from-amber-500 to-orange-500 p-6">
            <h2 class="text-xl font-bold text-white">Formulir Pelaporan</h2>
            <p class="text-amber-100 text-sm mt-1">Tanda (*) wajib diisi</p>
          </div>

          <form @submit.prevent="handleSubmit" class="p-6 space-y-5">
            <!-- Error Message -->
            <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-start gap-2">
              <ExclamationTriangleIcon class="w-5 h-5 flex-shrink-0 mt-0.5" />
              <span>{{ errorMessage }}</span>
            </div>

            <!-- Nama Lengkap -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Nama Lengkap <span class="text-red-500">*</span>
              </label>
              <input 
                v-model="form.nama_lengkap"
                type="text" 
                placeholder="Masukkan nama lengkap Anda"
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all"
                :disabled="isLoading"
              />
            </div>

            <!-- No. HPM -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                No. HPM (ID Pelanggan) <span class="text-red-500">*</span>
              </label>
              <input 
                v-model="form.no_hpm"
                type="text" 
                placeholder="Contoh: HPM00001"
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all uppercase"
                :disabled="isLoading"
              />
              <p class="text-xs text-gray-500 mt-1">ID pelanggan tercantum di struk tagihan Anda</p>
            </div>

            <!-- Kategori Keluhan -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Kategori Keluhan <span class="text-red-500">*</span>
              </label>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <button
                  v-for="kat in kategoriOptions"
                  :key="kat.value"
                  type="button"
                  @click="form.kategori = kat.value"
                  :class="[
                    'p-3 rounded-xl border-2 text-left transition-all',
                    form.kategori === kat.value 
                      ? 'border-amber-500 bg-amber-50' 
                      : 'border-gray-200 hover:border-gray-300'
                  ]"
                  :disabled="isLoading"
                >
                  <div class="font-medium text-gray-800">{{ kat.label }}</div>
                  <div class="text-xs text-gray-500">{{ kat.desc }}</div>
                </button>
              </div>
            </div>

            <!-- Detail Laporan -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Detail Laporan <span class="text-red-500">*</span>
              </label>
              <textarea 
                v-model="form.detail_laporan"
                rows="4"
                placeholder="Jelaskan kronologi atau detail masalah yang Anda alami..."
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all resize-none"
                :disabled="isLoading"
              ></textarea>
              <p class="text-xs text-gray-500 mt-1">Minimal 10 karakter ({{ form.detail_laporan.length }}/10)</p>
            </div>

            <!-- Upload Foto -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Unggah Bukti Foto <span class="text-gray-400">(Opsional)</span>
              </label>
              
              <div v-if="!previewUrl" class="relative">
                <input 
                  type="file" 
                  accept="image/jpeg,image/png,image/jpg"
                  @change="handleFileChange"
                  class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                  :disabled="isLoading"
                />
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-amber-400 transition-colors">
                  <PhotoIcon class="w-10 h-10 text-gray-400 mx-auto mb-2" />
                  <p class="text-sm text-gray-600">Klik untuk memilih foto</p>
                  <p class="text-xs text-gray-400 mt-1">JPG atau PNG, maksimal 5MB</p>
                </div>
              </div>
              
              <div v-else class="relative">
                <img :src="previewUrl" alt="Preview" class="w-full h-48 object-cover rounded-xl" />
                <button 
                  type="button"
                  @click="removeFile"
                  class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors"
                >
                  <XMarkIcon class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- No. WhatsApp -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                No. WhatsApp <span class="text-red-500">*</span>
              </label>
              <input 
                v-model="form.no_whatsapp"
                type="tel" 
                placeholder="Contoh: 081234567890"
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all"
                :disabled="isLoading"
              />
              <p class="text-xs text-gray-500 mt-1">Tim kami akan menghubungi Anda melalui nomor ini</p>
            </div>

            <!-- Submit Button -->
            <button 
              type="submit"
              :disabled="isLoading || !isFormValid"
              class="w-full bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-amber-500/30 transition-all transform hover:scale-[1.02] disabled:opacity-50 disabled:transform-none disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <span v-if="isLoading" class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></span>
              {{ isLoading ? 'Mengirim...' : 'Kirim Laporan' }}
            </button>
          </form>
        </div>

        <!-- Help Text -->
        <p class="text-center text-sm text-gray-500">
          Butuh bantuan segera? Hubungi kami di <a href="tel:085784466697" class="text-amber-600 font-semibold hover:underline">085784466697</a>
        </p>
      </div>
    </main>
  </div>
</template>

<style scoped>
/* Custom styles */
</style>
