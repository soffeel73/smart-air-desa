<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { UsersIcon, MapPinIcon, BeakerIcon, StarIcon } from '@heroicons/vue/24/outline'

const isLoading = ref(true)
const stats = ref([
  { label: 'Pelanggan Aktif', value: '...', icon: UsersIcon, key: 'total_pelanggan' },
  { label: 'Wilayah Terlayani', value: '...', icon: MapPinIcon, key: 'total_wilayah' },
  { label: 'Air Tersalurkan', value: '...', icon: BeakerIcon, key: 'total_pemakaian', suffix: ' m³' },
])

const API_BASE = '/api/public_api.php'

const fetchStats = async () => {
  try {
    const response = await fetch(`${API_BASE}?action=stats`)
    const data = await response.json()
    
    if (data.success) {
      stats.value = stats.value.map(stat => {
        const value = data.data[stat.key]
        return {
          ...stat,
          value: value !== undefined ? value.toLocaleString('id-ID') + (stat.suffix || '') : stat.value
        }
      })
    }
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchStats()
})
</script>

<template>
  <section class="py-12 relative z-20 -mt-8 mx-4 lg:mx-0">
    <div class="container mx-auto px-4">
      <div class="flex flex-wrap justify-center gap-8">
        <div 
          v-for="(stat, index) in stats" 
          :key="index" 
          class="bg-white p-8 rounded-2xl shadow-xl border border-gray-100 text-center group hover:-translate-y-2 transition-transform duration-300 w-full md:w-80" 
          data-aos="fade-up" 
          :data-aos-delay="index * 100"
        >
          <!-- Loading Skeleton -->
          <template v-if="isLoading">
            <div class="animate-pulse">
              <div class="h-12 bg-gray-200 rounded w-24 mx-auto mb-2"></div>
              <div class="h-4 bg-gray-200 rounded w-32 mx-auto"></div>
            </div>
          </template>
          
          <!-- Actual Content -->
          <template v-else>
            <div class="w-12 h-12 mx-auto mb-4 bg-primary/10 rounded-xl flex items-center justify-center">
              <component :is="stat.icon" class="w-6 h-6 text-primary" />
            </div>
            <div class="text-4xl md:text-5xl font-bold text-primary mb-2 inline-block">
              {{ stat.value }}
            </div>
            <div class="text-gray-500 font-medium uppercase tracking-wider text-sm">{{ stat.label }}</div>
          </template>
        </div>
      </div>
    </div>
  </section>
</template>
