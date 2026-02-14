<script setup>
import { ref, onMounted, computed } from 'vue'
import { PhotoIcon, XMarkIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'

// State
const galleryItems = ref([])
const loading = ref(true)
const activeFilter = ref('Semua')
const filters = ['Semua', 'Kegiatan', 'Perbaikan', 'Rapat', 'Sosialisasi']
const showLightbox = ref(false)
const lightboxIndex = ref(0)

// Fallback data
const fallbackItems = [
  { id: 1, image_path: 'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1', judul: 'Perbaikan Jaringan', kategori: 'Perbaikan', caption: 'Perbaikan pipa utama' },
  { id: 2, image_path: 'https://images.unsplash.com/photo-1581244277943-fe4a9c777189', judul: 'Pengecekan Meter', kategori: 'Kegiatan', caption: 'Verifikasi meteran' },
  { id: 3, image_path: 'https://images.unsplash.com/photo-1559827260-dc66d52bef19', judul: 'Rapat Desa', kategori: 'Rapat', caption: 'Koordinasi rutin' },
]

// Fetch Data
const fetchGallery = async () => {
  try {
    const res = await fetch('/api/konten.php?action=galeri')
    const data = await res.json()
    if (data.success && data.data.length > 0) {
      galleryItems.value = data.data.filter(item => item.is_active == 1)
    } else {
      galleryItems.value = fallbackItems
    }
  } catch (e) {
    console.error(e)
    galleryItems.value = fallbackItems
  } finally {
    loading.value = false
  }
}

// Computed Items based on Filter
const filteredItems = computed(() => {
  if (activeFilter.value === 'Semua') return galleryItems.value
  return galleryItems.value.filter(item => item.kategori === activeFilter.value)
})

// Lightbox Logic
const openLightbox = (index) => {
  // Find actual index in filtered list
  lightboxIndex.value = index
  showLightbox.value = true
  document.body.style.overflow = 'hidden'
}

const closeLightbox = () => {
  showLightbox.value = false
  document.body.style.overflow = 'auto'
}

const nextImage = () => {
  if (lightboxIndex.value < filteredItems.value.length - 1) lightboxIndex.value++
  else lightboxIndex.value = 0
}

const prevImage = () => {
  if (lightboxIndex.value > 0) lightboxIndex.value--
  else lightboxIndex.value = filteredItems.value.length - 1
}

onMounted(fetchGallery)
</script>

<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-[#2EC4B6] to-[#20B2AA] py-20 text-white relative overflow-hidden">
      <div class="absolute inset-0 bg-pattern opacity-10"></div>
      
      <!-- Back Link -->
      <div class="absolute top-6 left-4 md:left-10 z-20">
        <RouterLink to="/" class="inline-flex items-center gap-2 text-white/90 hover:text-white transition-colors bg-white/10 backdrop-blur-md px-4 py-2 rounded-full text-sm font-medium hover:bg-white/20 border border-white/20">
          <ChevronLeftIcon class="w-4 h-4" />
          Kembali ke Beranda
        </RouterLink>
      </div>

      <div class="container mx-auto px-4 relative z-10 text-center" data-aos="fade-up">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Galeri Kegiatan</h1>
        <p class="text-white/90 text-lg max-w-2xl mx-auto">Dokumentasi visual aktivitas dan layanan HIPPAMS TIRTO JOYO untuk transparansi dan informasi masyarakat.</p>
      </div>
    </div>

    <!-- Content -->
    <div class="container mx-auto px-4 -mt-10 relative z-20">
      <!-- Filter Tabs -->
      <div class="bg-white rounded-xl shadow-lg p-2 mb-10 flex flex-wrap justify-center gap-2 max-w-3xl mx-auto" data-aos="fade-up">
        <button 
          v-for="filter in filters" 
          :key="filter"
          @click="activeFilter = filter"
          class="px-5 py-2.5 rounded-lg font-medium transition-all text-sm md:text-base"
          :class="activeFilter === filter ? 'bg-[#2EC4B6] text-white shadow-md' : 'text-gray-600 hover:bg-gray-100'"
        >
          {{ filter }}
        </button>
      </div>

      <!-- Masonry Grid -->
      <div v-if="loading" class="flex justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-[#2EC4B6] border-t-transparent"></div>
      </div>

      <div v-else class="columns-1 sm:columns-2 lg:columns-3 xl:columns-4 gap-6 space-y-6 mx-auto">
        <div 
          v-for="(item, index) in filteredItems" 
          :key="item.id"
          class="break-inside-avoid group relative rounded-2xl overflow-hidden bg-white shadow-sm hover:shadow-xl transition-all cursor-zoom-in"
          data-aos="fade-up"
          :data-aos-delay="index * 100"
          @click="openLightbox(index)"
        >
          <img :src="item.image_path" :alt="item.judul" class="w-full h-auto object-cover transform group-hover:scale-105 transition-transform duration-700" loading="lazy" />
          
          <!-- Overlay -->
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-6">
            <span class="inline-block px-3 py-1 bg-[#2EC4B6] text-white text-xs font-bold rounded-full w-fit mb-2 shadow-sm">{{ item.kategori }}</span>
            <h3 class="text-white font-bold text-lg leading-tight mb-1">{{ item.judul }}</h3>
            <p class="text-white/80 text-sm line-clamp-2">{{ item.caption }}</p>
          </div>
        </div>
      </div>

      <div v-if="!loading && filteredItems.length === 0" class="text-center py-20">
        <PhotoIcon class="w-20 h-20 text-gray-300 mx-auto mb-4" />
        <h3 class="text-xl font-medium text-gray-500">Tidak ada foto ditemukan</h3>
      </div>
    </div>

    <!-- Lightbox -->
    <Transition name="fade">
      <div v-if="showLightbox" class="fixed inset-0 bg-black/95 z-[60] flex items-center justify-center p-4">
        <!-- Close Button -->
        <button @click="closeLightbox" class="absolute top-6 right-6 p-2 text-white/50 hover:text-white transition-colors z-[70]">
          <XMarkIcon class="w-10 h-10" />
        </button>

        <!-- Nav Buttons -->
        <button @click.stop="prevImage" class="absolute left-4 top-1/2 -translate-y-1/2 p-3 text-white/50 hover:text-white hover:bg-white/10 rounded-full transition-all hidden md:block">
          <ChevronLeftIcon class="w-10 h-10" />
        </button>
        <button @click.stop="nextImage" class="absolute right-4 top-1/2 -translate-y-1/2 p-3 text-white/50 hover:text-white hover:bg-white/10 rounded-full transition-all hidden md:block">
          <ChevronRightIcon class="w-10 h-10" />
        </button>

        <!-- Image Container -->
        <div class="max-w-7xl max-h-[90vh] w-full flex flex-col items-center relative">
          <img :src="filteredItems[lightboxIndex].image_path" class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-xl" />
          
          <!-- Caption -->
          <div class="text-center mt-6 text-white max-w-2xl px-4">
            <h3 class="text-xl font-bold mb-2">{{ filteredItems[lightboxIndex].judul }}</h3>
            <p class="text-white/70">{{ filteredItems[lightboxIndex].caption }}</p>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.bg-pattern {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
