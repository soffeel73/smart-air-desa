<script setup>
import { ref } from 'vue'
import { ExclamationCircleIcon } from '@heroicons/vue/24/outline'

const customerId = ref('')
const isLoading = ref(false)
const errorMessage = ref('')
const hasError = ref(false)

const emit = defineEmits(['check-bill'])

const API_BASE = '/api/public_api.php'

const handleCheck = async () => {
  if (!customerId.value.trim()) {
    showError('Silakan masukkan ID Pelanggan')
    return
  }
  
  isLoading.value = true
  errorMessage.value = ''
  hasError.value = false
  
  try {
    const response = await fetch(`${API_BASE}?action=cek_tagihan&id=${encodeURIComponent(customerId.value.trim())}`)
    const data = await response.json()
    
    if (data.success) {
      // Emit event with bill data to navigate to BillResult
      emit('check-bill', data.data)
    } else {
      showError(data.message || 'ID Pelanggan tidak ditemukan')
    }
  } catch (error) {
    showError('Gagal menghubungi server. Coba lagi.')
  } finally {
    isLoading.value = false
  }
}

const showError = (message) => {
  errorMessage.value = message
  hasError.value = true
  // Reset shake animation
  setTimeout(() => {
    hasError.value = false
  }, 500)
}
</script>

<template>
  <section id="home" class="relative pt-32 pb-20 lg:pt-48 lg:pb-32">
    <div class="container mx-auto px-4 relative z-10">
      <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-20">
        <div class="w-full lg:w-1/2 text-center lg:text-left" data-aos="fade-right">
          <span class="inline-block py-1 px-3 rounded-full bg-primary/10 text-primary font-bold text-sm mb-6">
            Sistem Pengelolaan Air Bersih
          </span>
          <h1 class="text-4xl lg:text-6xl font-bold text-gray-900 leading-tight mb-6">
            Pengelolaan Air Lebih <span class="text-primary">Praktis</span>, Transparan &amp; Modern.
          </h1>
          <p class="text-lg text-gray-600 mb-8 leading-relaxed">
            Pantau penggunaan air, cek tagihan, dan ajukan keluhan layanan air Anda hanya dalam satu genggaman. Cepat, mudah, dan transparan.
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
            <router-link to="/lapor-keluhan" class="bg-amber-500 hover:bg-amber-600 text-white px-8 py-4 rounded-full font-bold text-lg transition-all shadow-lg hover:shadow-amber-500/30 transform hover:-translate-y-1 flex items-center gap-2 justify-center">
              <ExclamationCircleIcon class="w-5 h-5" />
              Lapor Keluhan
            </router-link>
            <a href="#check-bill" class="bg-primary hover:bg-teal-600 text-white px-8 py-4 rounded-full font-bold text-lg transition-all shadow-lg hover:shadow-primary/30 transform hover:-translate-y-1">
              Cek Tagihan
            </a>
          </div>
        </div>

        <div class="w-full lg:w-1/2" data-aos="fade-left">
          <div class="relative">
            <!-- Decorative blobs -->
            <div class="absolute -top-10 -right-10 w-72 h-72 bg-primary/20 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-10 -left-10 w-72 h-72 bg-highlight/20 rounded-full blur-3xl animate-pulse delay-700"></div>
            
            <!-- Card Form -->
            <div id="check-bill" class="relative bg-white/60 backdrop-blur-xl border border-white/50 rounded-2xl p-8 shadow-2xl">
              <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-2">🔍 Cek Tagihan Cepat</h3>
                <p class="text-gray-500 text-sm">Masukkan ID Pelanggan Anda untuk melihat tagihan bulan ini.</p>
              </div>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">ID Pelanggan</label>
                  <input 
                    v-model="customerId"
                    @keypress.enter="handleCheck"
                    type="text" 
                    placeholder="Contoh: HPM00001" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all"
                    :class="{ 'shake border-red-500': hasError }"
                    :disabled="isLoading"
                  />
                  <p v-if="errorMessage" class="text-red-500 text-sm mt-2 flex items-center gap-1">
                    <ExclamationCircleIcon class="w-4 h-4" />
                    {{ errorMessage }}
                  </p>
                </div>
                <button 
                  @click="handleCheck"
                  :disabled="isLoading"
                  class="w-full bg-primary hover:bg-teal-600 text-white font-bold py-3 rounded-xl shadow-lg shadow-primary/30 transition-all transform hover:scale-[1.02] disabled:opacity-50 disabled:transform-none flex items-center justify-center gap-2"
                >
                  <span v-if="isLoading" class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></span>
                  {{ isLoading ? 'Mencari...' : 'Lihat Tagihan' }}
                </button>
              </div>

              <!-- Quick Info -->
              <div class="mt-8 pt-6 border-t border-gray-100 grid grid-cols-2 gap-4">
                <div class="text-center p-3 bg-primary/5 rounded-lg">
                  <div class="text-primary font-bold text-lg">24/7</div>
                  <div class="text-xs text-gray-500">Akses Online</div>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-lg">
                  <div class="text-green-600 font-bold text-lg">Gratis</div>
                  <div class="text-xs text-gray-500">Biaya Cek</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.shake {
  animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
}

@keyframes shake {
  10%, 90% { transform: translateX(-1px); }
  20%, 80% { transform: translateX(2px); }
  30%, 50%, 70% { transform: translateX(-4px); }
  40%, 60% { transform: translateX(4px); }
}
</style>
