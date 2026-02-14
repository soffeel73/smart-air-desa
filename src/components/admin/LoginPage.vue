<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { EyeIcon, EyeSlashIcon, ShieldCheckIcon, ClipboardDocumentListIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const emit = defineEmits(['login-success', 'back'])

// Role state
const activeRole = ref('admin') // 'admin' or 'petugas'

const form = ref({
  username: '',
  password: '',
  remember: false
})

const showPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')

// Dynamic content based on role
const roleContent = computed(() => {
  if (activeRole.value === 'admin') {
    return {
      title: 'Masuk sebagai Administrator',
      subtitle: 'Kelola sistem pusat dengan akses penuh'
    }
  } else {
    return {
      title: 'Masuk sebagai Petugas Lapangan',
      subtitle: 'Input data meteran air pelanggan'
    }
  }
})

const isFormValid = computed(() => {
  return form.value.username.length > 0 && form.value.password.length > 0
})

const handleSubmit = async () => {
  errorMessage.value = ''
  
  if (!isFormValid.value) {
    errorMessage.value = 'Username dan Password tidak boleh kosong.'
    return
  }

  isLoading.value = true

  // Simulate API call (replace with actual axios call)
  try {
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    // Role-based authentication
    let isValid = false
    
    if (activeRole.value === 'admin') {
      // Admin credentials
      if (form.value.username === 'admin' && form.value.password === 'admin123') {
        isValid = true
      }
    } else if (activeRole.value === 'petugas') {
      // Field officer credentials
      if (form.value.username === 'petugas' && form.value.password === 'petugas123') {
        isValid = true
      }
    }
    
    if (isValid) {
      // Success - store auth data and navigate based on role
      localStorage.setItem('auth_token', 'mock_sanctum_token_12345')
      localStorage.setItem('user_role', activeRole.value)
      localStorage.setItem('username', form.value.username)
      
      // Navigate based on role
      if (activeRole.value === 'admin') {
        router.push('/admin/dashboard')
      } else {
        router.push('/petugas/input-meter')
      }
    } else {
      errorMessage.value = 'Username atau password tidak sesuai dengan role yang dipilih.'
    }
  } catch (error) {
    errorMessage.value = 'Terjadi kesalahan server. Silakan coba lagi nanti.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-frozen via-white to-frozen flex items-center justify-center p-4 font-sans relative overflow-hidden">
    
    <!-- Background Decorative Elements -->
    <div class="absolute top-0 left-0 w-96 h-96 bg-primary/20 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-80 h-80 bg-accent/20 rounded-full blur-3xl translate-x-1/3 translate-y-1/3"></div>
    <div class="absolute top-1/2 right-1/4 w-64 h-64 bg-highlight/20 rounded-full blur-2xl"></div>

    <!-- Login Card -->
    <div class="w-full max-w-md relative z-10">
      <!-- Back Button -->
      <button @click="router.push('/')" class="mb-4 flex items-center gap-2 text-primary hover:text-teal-700 transition-colors font-medium text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Kembali ke Beranda
      </button>
      <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl shadow-primary/10 border border-white/50 p-8 md:p-10">
        
        <!-- Role Selection Tabs (Logo-Shaped) -->
        <div class="flex justify-center gap-8 mb-8">
          <!-- Administrator Tab -->
          <div 
            @click="activeRole = 'admin'; errorMessage = ''"
            :class="[
              'flex flex-col items-center cursor-pointer transition-all duration-300 transform hover:scale-105',
              activeRole === 'admin' ? '' : 'opacity-50'
            ]"
          >
            <div 
              :class="[
                'p-6 rounded-full shadow-sm transition-all duration-300',
                activeRole === 'admin' 
                  ? 'bg-primary/10 ring-2 ring-primary shadow-primary/30' 
                  : 'bg-gray-100 hover:bg-gray-200'
              ]"
            >
              <ShieldCheckIcon 
                :class="[
                  'w-10 h-10 transition-colors duration-300',
                  activeRole === 'admin' ? 'text-primary' : 'text-slate-400'
                ]" 
              />
            </div>
            <span 
              :class="[
                'text-sm font-bold mt-3 transition-colors duration-300',
                activeRole === 'admin' ? 'text-primary' : 'text-slate-400'
              ]"
            >
              Administrator
            </span>
          </div>

          <!-- Field Officer Tab -->
          <div 
            @click="activeRole = 'petugas'; errorMessage = ''"
            :class="[
              'flex flex-col items-center cursor-pointer transition-all duration-300 transform hover:scale-105',
              activeRole === 'petugas' ? '' : 'opacity-50'
            ]"
          >
            <div 
              :class="[
                'p-6 rounded-full shadow-sm transition-all duration-300',
                activeRole === 'petugas' 
                  ? 'bg-accent/10 ring-2 ring-accent shadow-accent/30' 
                  : 'bg-gray-100 hover:bg-gray-200'
              ]"
            >
              <ClipboardDocumentListIcon 
                :class="[
                  'w-10 h-10 transition-colors duration-300',
                  activeRole === 'petugas' ? 'text-accent' : 'text-slate-400'
                ]" 
              />
            </div>
            <span 
              :class="[
                'text-sm font-bold mt-3 transition-colors duration-300',
                activeRole === 'petugas' ? 'text-accent' : 'text-slate-400'
              ]"
            >
              Petugas Lapangan
            </span>
          </div>
        </div>

        <!-- Logo & Branding -->
        <div class="text-center mb-8">
          <img src="@/assets/logo.png" alt="Logo HIPPAMS" class="w-20 h-20 mx-auto mb-6 object-contain">
          <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ roleContent.title }}</h1>
          <p class="text-gray-500 text-sm">{{ roleContent.subtitle }}</p>
        </div>

        <!-- Error Alert -->
        <div v-if="errorMessage" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3">
          <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <p class="text-red-700 text-sm font-medium">{{ errorMessage }}</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-5">
          <!-- Username -->
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Username</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
              </div>
              <input 
                v-model="form.username"
                type="text" 
                placeholder="Masukkan username"
                class="w-full pl-12 pr-4 py-3.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all text-gray-900 placeholder-gray-400"
              >
            </div>
          </div>

          <!-- Password -->
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Password</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
              </div>
              <input 
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'" 
                placeholder="Masukkan password"
                class="w-full pl-12 pr-12 py-3.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all text-gray-900 placeholder-gray-400"
              >
              <button 
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-primary transition-colors"
              >
                <EyeSlashIcon v-if="showPassword" class="w-5 h-5" />
                <EyeIcon v-else class="w-5 h-5" />
              </button>
            </div>
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer group">
              <input 
                v-model="form.remember"
                type="checkbox" 
                class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/50 cursor-pointer"
              >
              <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors">Ingat Saya</span>
            </label>
            <a href="#" class="text-sm font-medium text-primary hover:text-teal-700 transition-colors">Lupa Password?</a>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit"
            :disabled="isLoading"
            class="w-full py-4 bg-accent hover:bg-orange-600 text-white font-bold rounded-xl shadow-lg shadow-accent/30 transition-all duration-300 flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed transform hover:-translate-y-0.5 active:translate-y-0"
          >
            <svg v-if="isLoading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ isLoading ? 'Sedang Masuk...' : 'Masuk ke Dashboard' }}</span>
          </button>
        </form>

        <!-- Footer -->
        <p class="text-center text-sm text-gray-500 mt-8">
          Butuh bantuan? <a href="#" class="text-primary font-medium hover:underline">Hubungi Administrator</a>
        </p>
      </div>

      <!-- Copyright -->
      <p class="text-center text-xs text-gray-400 mt-6">
        © 2024 TirtaDesa Digital. All rights reserved.
      </p>
    </div>
  </div>
</template>
