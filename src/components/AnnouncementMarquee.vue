<script setup>
import { ref, onMounted, computed } from 'vue'

const pengumumanList = ref([])
const loading = ref(true)

const API_BASE = '/api/konten.php'

const fetchActivePengumuman = async () => {
  try {
    const res = await fetch(`${API_BASE}?action=pengumuman_aktif`)
    const data = await res.json()
    if (data.success) {
      pengumumanList.value = data.data
    }
  } catch (e) {
    console.error('Error fetching pengumuman:', e)
  } finally {
    loading.value = false
  }
}

// Combined text for marquee
const marqueeText = computed(() => {
  if (pengumumanList.value.length === 0) return ''
  return pengumumanList.value.map(p => p.teks).join('     •     ')
})

onMounted(() => {
  fetchActivePengumuman()
})
</script>

<template>
  <div 
    v-if="!loading && pengumumanList.length > 0" 
    class="bg-white/80 backdrop-blur-sm border-b border-amber-200 py-3 overflow-hidden relative"
  >
    <div class="container mx-auto px-4 flex items-center">
      <!-- Icon -->
      <div class="flex-shrink-0 mr-4 flex items-center gap-2 text-amber-500">
        <svg class="w-5 h-5 animate-pulse" fill="currentColor" viewBox="0 0 24 24">
          <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"></path>
        </svg>
        <span class="font-bold text-sm hidden sm:inline">PENGUMUMAN:</span>
      </div>
      
      <!-- Marquee Container -->
      <div class="flex-1 overflow-hidden">
        <div class="marquee-container">
          <div class="marquee-content text-amber-600 font-medium">
            <span class="inline-block whitespace-nowrap">{{ marqueeText }}</span>
            <span class="inline-block whitespace-nowrap ml-12">{{ marqueeText }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.marquee-container {
  overflow: hidden;
  position: relative;
}

.marquee-content {
  display: inline-flex;
  animation: marquee 30s linear infinite;
}

@keyframes marquee {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(-50%);
  }
}

.marquee-content:hover {
  animation-play-state: paused;
}
</style>
