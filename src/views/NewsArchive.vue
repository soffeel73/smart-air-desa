<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import Header from '../components/Header.vue'
import Footer from '../components/Footer.vue'

const router = useRouter()
const allBerita = ref([])
const loading = ref(true)
const searchQuery = ref('')
const selectedCategory = ref('semua')
const sortBy = ref('terbaru')
const currentPage = ref(1)
const itemsPerPage = 6

const API_BASE = '/api/konten.php'

// Categories
const categories = [
  { id: 'semua', label: 'Semua' },
  { id: 'layanan', label: 'Layanan' },
  { id: 'pengumuman', label: 'Pengumuman' },
  { id: 'tips', label: 'Tips Air' },
  { id: 'lainnya', label: 'Lainnya' }
]

// Fetch all berita
const fetchAllBerita = async () => {
  loading.value = true
  try {
    const res = await fetch(`${API_BASE}?action=berita`)
    const data = await res.json()
    if (data.success) {
      allBerita.value = data.data.filter(b => b.status === 'published')
    }
  } catch (e) {
    console.error('Error:', e)
  } finally {
    loading.value = false
  }
}

// Filtered & sorted berita
const filteredBerita = computed(() => {
  let result = [...allBerita.value]
  
  // Filter by search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(b => 
      b.judul.toLowerCase().includes(query) ||
      (b.ringkasan && b.ringkasan.toLowerCase().includes(query))
    )
  }
  
  // Sort
  if (sortBy.value === 'terbaru') {
    result.sort((a, b) => new Date(b.tanggal_publish) - new Date(a.tanggal_publish))
  } else if (sortBy.value === 'terlama') {
    result.sort((a, b) => new Date(a.tanggal_publish) - new Date(b.tanggal_publish))
  }
  
  return result
})

// Featured (first item)
const featuredBerita = computed(() => filteredBerita.value[0] || null)

// Rest of berita (excluding featured)
const restBerita = computed(() => filteredBerita.value.slice(1))

// Paginated berita
const paginatedBerita = computed(() => {
  const start = 0
  const end = currentPage.value * itemsPerPage
  return restBerita.value.slice(start, end)
})

// Has more
const hasMore = computed(() => paginatedBerita.value.length < restBerita.value.length)

// Load more
const loadMore = () => {
  currentPage.value++
}

// Reset pagination on filter change
watch([searchQuery, selectedCategory, sortBy], () => {
  currentPage.value = 1
})

// Format date
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
}

// Format date short
const formatDateShort = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

// Estimate read time
const readTime = (text) => {
  if (!text) return '2 min read'
  const words = text.split(/\s+/).length
  const mins = Math.ceil(words / 200)
  return `${mins} min read`
}

// Navigate to detail
const goToDetail = (id) => {
  router.push(`/news/${id}`)
}

const goBack = () => {
  router.push('/')
}

onMounted(() => {
  fetchAllBerita()
})
</script>

<template>
  <div class="min-h-screen bg-[#F9FAFB]">
    <Header />
    
    <main class="pt-20">
      <!-- Hero Header -->
      <div class="bg-[#CBF3F0] py-16 lg:py-20">
        <div class="container mx-auto px-4 lg:px-8">
          <button 
            @click="goBack"
            class="mb-6 flex items-center gap-2 text-[#2EC4B6] hover:text-teal-700 transition-colors font-medium"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Beranda
          </button>
          <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
            Pusat Informasi & Berita
          </h1>
          <p class="text-lg text-gray-600 max-w-2xl">
            Dapatkan informasi terkini seputar layanan air bersih, pengumuman penting, dan tips bermanfaat dari HIPPAMS Tirto Joyo.
          </p>
        </div>
      </div>

      <div class="container mx-auto px-4 lg:px-8 py-12">
        <!-- Filter Bar -->
        <div class="flex flex-col lg:flex-row gap-4 justify-between items-start lg:items-center mb-10 bg-white p-4 rounded-2xl shadow-sm">
          <!-- Categories -->
          <div class="flex flex-wrap gap-2">
            <button
              v-for="cat in categories"
              :key="cat.id"
              @click="selectedCategory = cat.id"
              class="px-4 py-2 rounded-full text-sm font-medium transition-all"
              :class="selectedCategory === cat.id 
                ? 'bg-[#2EC4B6] text-white shadow-lg shadow-[#2EC4B6]/30' 
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
            >
              {{ cat.label }}
            </button>
          </div>

          <!-- Search & Sort -->
          <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <!-- Search -->
            <div class="relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              <input 
                v-model="searchQuery"
                type="text"
                placeholder="Cari berita..."
                class="w-full sm:w-64 pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2EC4B6]/20 focus:border-[#2EC4B6] outline-none transition-all"
              />
            </div>
            
            <!-- Sort -->
            <select 
              v-model="sortBy"
              class="px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2EC4B6]/20 focus:border-[#2EC4B6] outline-none bg-white text-gray-700"
            >
              <option value="terbaru">Terbaru</option>
              <option value="terlama">Terlama</option>
            </select>
          </div>
        </div>

        <!-- Loading Skeleton -->
        <div v-if="loading" class="space-y-8">
          <!-- Featured Skeleton -->
          <div class="bg-white rounded-3xl shadow-sm overflow-hidden animate-pulse">
            <div class="grid lg:grid-cols-5 gap-0">
              <div class="lg:col-span-3 aspect-video lg:aspect-auto bg-gray-200"></div>
              <div class="lg:col-span-2 p-8 space-y-4">
                <div class="h-4 w-24 bg-gray-200 rounded"></div>
                <div class="h-8 w-full bg-gray-200 rounded"></div>
                <div class="h-4 w-3/4 bg-gray-200 rounded"></div>
                <div class="h-4 w-1/2 bg-gray-200 rounded"></div>
              </div>
            </div>
          </div>
          <!-- Grid Skeleton -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="i in 6" :key="i" class="bg-white rounded-2xl shadow-sm overflow-hidden animate-pulse">
              <div class="aspect-video bg-gray-200"></div>
              <div class="p-5 space-y-3">
                <div class="h-3 w-20 bg-gray-200 rounded"></div>
                <div class="h-5 w-full bg-gray-200 rounded"></div>
                <div class="h-3 w-3/4 bg-gray-200 rounded"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div v-else>
          <!-- Empty State -->
          <div v-if="filteredBerita.length === 0" class="text-center py-20">
            <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z"></path>
            </svg>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Tidak Ada Berita</h2>
            <p class="text-gray-500">Tidak ditemukan berita yang sesuai dengan pencarian Anda</p>
          </div>

          <template v-else>
            <!-- Featured Post -->
            <article 
              v-if="featuredBerita"
              @click="goToDetail(featuredBerita.id)"
              class="group cursor-pointer bg-white rounded-3xl shadow-sm hover:shadow-xl transition-all duration-500 overflow-hidden mb-10"
            >
              <div class="grid lg:grid-cols-5 gap-0">
                <!-- Image -->
                <div class="lg:col-span-3 relative overflow-hidden">
                  <div class="aspect-video lg:aspect-[16/10] lg:h-full">
                    <img 
                      v-if="featuredBerita.foto_url" 
                      :src="featuredBerita.foto_url" 
                      :alt="featuredBerita.judul"
                      class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    />
                    <div v-else class="w-full h-full bg-gradient-to-br from-[#2EC4B6]/30 to-[#CBF3F0] flex items-center justify-center">
                      <svg class="w-20 h-20 text-[#2EC4B6]/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                      </svg>
                    </div>
                  </div>
                  <!-- Gradient Overlay -->
                  <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent lg:hidden"></div>
                  <!-- Badge -->
                  <span class="absolute top-4 left-4 px-3 py-1 bg-[#FF9F1C] text-white text-xs font-bold rounded-full shadow-lg">
                    TERBARU
                  </span>
                </div>
                
                <!-- Content -->
                <div class="lg:col-span-2 p-6 lg:p-8 flex flex-col justify-center">
                  <div class="flex items-center gap-3 text-sm text-gray-500 mb-4">
                    <span class="px-2.5 py-1 bg-[#2EC4B6]/10 text-[#2EC4B6] font-medium rounded-full text-xs">Berita Utama</span>
                    <span>•</span>
                    <span>{{ formatDateShort(featuredBerita.tanggal_publish) }}</span>
                    <span>•</span>
                    <span>{{ readTime(featuredBerita.isi_berita) }}</span>
                  </div>
                  <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4 group-hover:text-[#2EC4B6] transition-colors leading-tight">
                    {{ featuredBerita.judul }}
                  </h2>
                  <p class="text-gray-600 leading-relaxed line-clamp-3 mb-6">
                    {{ featuredBerita.ringkasan || 'Baca selengkapnya untuk informasi lebih detail...' }}
                  </p>
                  <span class="inline-flex items-center gap-2 text-[#2EC4B6] font-semibold group-hover:gap-3 transition-all">
                    Baca Selengkapnya
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                  </span>
                </div>
              </div>
            </article>

            <!-- Grid Berita -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <article 
                v-for="berita in paginatedBerita" 
                :key="berita.id"
                @click="goToDetail(berita.id)"
                class="group cursor-pointer bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden"
              >
                <!-- Image -->
                <div class="aspect-video overflow-hidden relative">
                  <img 
                    v-if="berita.foto_url" 
                    :src="berita.foto_url" 
                    :alt="berita.judul"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                  />
                  <div v-else class="w-full h-full bg-gradient-to-br from-[#2EC4B6]/20 to-[#CBF3F0] flex items-center justify-center">
                    <svg class="w-12 h-12 text-[#2EC4B6]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                  </div>
                </div>
                
                <!-- Content -->
                <div class="p-5">
                  <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">Berita</span>
                    <span>•</span>
                    <span>{{ formatDateShort(berita.tanggal_publish) }}</span>
                    <span>•</span>
                    <span>{{ readTime(berita.isi_berita) }}</span>
                  </div>
                  <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-2 group-hover:text-[#2EC4B6] transition-colors leading-snug">
                    {{ berita.judul }}
                  </h3>
                  <p class="text-gray-600 text-sm line-clamp-2 leading-relaxed">
                    {{ berita.ringkasan || 'Baca selengkapnya untuk informasi lebih detail...' }}
                  </p>
                </div>
              </article>
            </div>

            <!-- Load More -->
            <div v-if="hasMore" class="text-center mt-12">
              <button 
                @click="loadMore"
                class="px-8 py-4 bg-white text-gray-700 font-semibold rounded-xl shadow-sm hover:shadow-lg hover:bg-gray-50 transition-all border border-gray-200"
              >
                <span class="flex items-center gap-2">
                  Muat Lebih Banyak
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </span>
              </button>
            </div>

            <!-- End Message -->
            <div v-else-if="restBerita.length > 0" class="text-center mt-12">
              <p class="text-gray-500 text-sm">Anda telah melihat semua berita</p>
            </div>
          </template>
        </div>
      </div>
    </main>

    <Footer />
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
