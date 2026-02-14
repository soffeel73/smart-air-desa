<script setup>
import { ref } from 'vue'

// Toast notification state
const toastMessage = ref('')
const toastType = ref('error')
const showToast = ref(false)

// Global toast function for navigation guards
window.showToast = (message, type = 'error') => {
  toastMessage.value = message
  toastType.value = type
  showToast.value = true
  
  setTimeout(() => {
    showToast.value = false
  }, 3000)
}
</script>

<template>
  <div id="app">
    <!-- Toast Notification -->
    <transition name="slide-down">
      <div 
        v-if="showToast" 
        :class="[
          'fixed top-4 right-4 z-[9999] px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 max-w-md',
          toastType === 'error' ? 'bg-red-500 text-white' : 'bg-green-500 text-white'
        ]"
      >
        <svg v-if="toastType === 'error'" class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <svg v-else class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="font-medium">{{ toastMessage }}</p>
        <button @click="showToast = false" class="ml-auto">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </transition>

    <!-- Router View -->
    <router-view />
  </div>
</template>

<style>
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
}

.slide-down-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}

.slide-down-leave-to {
  opacity: 0;
  transform: translateX(20px);
}
</style>
