<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const beritaList = ref([])
const loading = ref(true)

const API_BASE = '/api/konten.php'

const fetchLatestBerita = async () => {
  try {
    const res = await fetch(`${API_BASE}?action=berita_latest`)
    const data = await res.json()
    if (data.success) {
      beritaList.value = data.data
    }
  } catch (e) {
    console.error('Error fetching berita:', e)
  } finally {
    loading.value = false
  }
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

const goToNewsArchive = () => {
  router.push('/news')
}

const goToNewsDetail = (id) => {
  router.push(`/news/${id}`)
}

onMounted(() => {
  fetchLatestBerita()
})
</script>

<template>
  <section class="py-20 bg-white">
    <div class="container mx-auto px-4 lg:px-8">
      <!-- Section Header -->
      <div class="text-center mb-12" data-aos="fade-up">
        <span class="inline-block py-1 px-3 rounded-full bg-primary/10 text-primary font-bold text-sm mb-4">
          Berita & Informasi
        </span>
        <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
          Berita Terbaru
        </h2>
        <p class="text-gray-600 max-w-2xl mx-auto">
          Dapatkan informasi terkini seputar layanan air bersih dan kegiatan kami
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent"></div>
      </div>

      <!-- Berita Grid -->
      <div v-else-if="beritaList.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" data-aos="fade-up" data-aos-delay="100">
        <article 
          v-for="(berita, index) in beritaList" 
          :key="berita.id"
          class="group bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-100"
          :data-aos-delay="index * 100"
        >
          <!-- Image -->
          <div class="aspect-video bg-gray-100 overflow-hidden relative">
            <img 
              v-if="berita.foto_url" 
              :src="berita.foto_url" 
              :alt="berita.judul"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            />
            <div v-else class="w-full h-full bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center">
              <svg class="w-16 h-16 text-primary/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
              </svg>
            </div>
            <div class="absolute top-4 left-4">
              <span class="px-3 py-1 bg-primary/90 text-white text-xs font-bold rounded-full backdrop-blur-sm">
                {{ formatDate(berita.tanggal_publish) }}
              </span>
            </div>
          </div>
          
          <!-- Content -->
          <div class="p-6">
            <h3 class="font-bold text-xl text-gray-900 mb-3 line-clamp-2 group-hover:text-primary transition-colors">
              {{ berita.judul }}
            </h3>
            <p class="text-gray-600 text-sm line-clamp-3 leading-relaxed mb-4">
              {{ berita.ringkasan || 'Baca selengkapnya untuk informasi lebih detail...' }}
            </p>
            <button 
              @click="goToNewsDetail(berita.id)"
              class="inline-flex items-center gap-1 text-primary font-medium text-sm hover:text-teal-700 transition-colors"
            >
              Baca Selengkapnya
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
          </div>
        </article>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
        </svg>
        <p class="text-gray-500">Belum ada berita saat ini</p>
      </div>

      <!-- View All Button -->
      <div v-if="beritaList.length > 0" class="text-center mt-12" data-aos="fade-up" data-aos-delay="300">
        <button 
          @click="goToNewsArchive"
          class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-primary to-teal-500 text-white font-bold rounded-xl hover:shadow-xl hover:shadow-primary/30 transition-all duration-300 group"
        >
          Lihat Berita Lainnya
          <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
          </svg>
        </button>
      </div>
    </div>
  </section>
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
