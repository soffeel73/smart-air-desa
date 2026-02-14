<script setup>
import { ref, onMounted } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, Pagination, Navigation } from 'swiper/modules'

// Import Swiper styles
import 'swiper/css'
import 'swiper/css/pagination'
import 'swiper/css/navigation'

// Gallery state
const galleryItems = ref([])
const loading = ref(true)

// Fallback data if API fails or empty
const fallbackItems = [
  {
    id: 1,
    image_path: 'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?w=800&h=600&fit=crop',
    judul: 'Perbaikan Jaringan Pipa',
    caption: 'Tim HIPPAMS melakukan perbaikan pipa distribusi utama'
  },
  {
    id: 2,
    image_path: 'https://images.unsplash.com/photo-1581244277943-fe4a9c777189?w=800&h=600&fit=crop',
    judul: 'Pengecekan Meter Air',
    caption: 'Petugas melakukan verifikasi dan kalibrasi meteran air pelanggan'
  },
  {
    id: 3,
    image_path: 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop',
    judul: 'Rapat Koordinasi Desa',
    caption: 'Koordinasi rutin bersama perangkat desa membahas program air bersih'
  },
  {
    id: 4,
    image_path: 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=800&h=600&fit=crop',
    judul: 'Pemasangan Instalasi Baru',
    caption: 'Instalasi sambungan baru untuk pelanggan di area pengembangan'
  }
]

const fetchGallery = async () => {
  try {
    const res = await fetch('/api/konten.php?action=galeri')
    const data = await res.json()
    if (data.success && data.data.length > 0) {
      // Filter active items only if backend doesn't filter
      galleryItems.value = data.data.filter(item => item.is_active == 1)
    } 
    
    // Use fallback if empty
    if (galleryItems.value.length === 0) {
      galleryItems.value = fallbackItems
    }
  } catch (e) {
    console.error('Failed to fetch gallery:', e)
    galleryItems.value = fallbackItems
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchGallery()
})

// Swiper modules
const modules = [Autoplay, Pagination, Navigation]
</script>

<template>
  <section id="galeri-kegiatan" class="py-20 bg-gradient-to-b from-white to-[#F0FBFA]">
    <div class="container mx-auto px-4">
      <!-- Section Header -->
      <div class="text-center max-w-2xl mx-auto mb-12" data-aos="fade-up">
        <span class="inline-block py-1 px-4 rounded-full bg-primary/10 text-primary font-bold text-xs tracking-wider uppercase mb-4 border border-primary/20">
          Dokumentasi Kegiatan
        </span>
        <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Galeri Kegiatan</h2>
        <p class="text-gray-600">Lihat aktivitas dan dedikasi tim <span class="font-bold text-primary">HIPPAMS TIRTO JOYO</span> dalam melayani kebutuhan air bersih masyarakat desa.</p>
      </div>

      <!-- Swiper Carousel -->
      <div class="relative" data-aos="fade-up" data-aos-delay="100">
        <Swiper
          :modules="modules"
          :slides-per-view="1"
          :space-between="24"
          :loop="galleryItems.length > 3"
          :autoplay="{
            delay: 5000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true
          }"
          :pagination="{
            clickable: true,
            dynamicBullets: true
          }"
          :navigation="true"
          :breakpoints="{
            640: {
              slidesPerView: 2,
              spaceBetween: 20
            },
            1024: {
              slidesPerView: 3,
              spaceBetween: 24
            }
          }"
          class="gallery-swiper !pb-14"
        >
          <SwiperSlide v-for="item in galleryItems" :key="item.id">
            <div class="relative aspect-[4/3] rounded-2xl overflow-hidden group shadow-lg border border-gray-100 bg-white">
              <img 
                :src="item.image_path" 
                :alt="item.judul"
                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                loading="lazy"
              />
              <!-- Gradient Overlay -->
              <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent opacity-80 group-hover:opacity-90 transition-opacity duration-300"></div>
              
              <!-- Branding Badge -->
              <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-lg shadow-sm">
                <span class="text-primary text-xs font-bold tracking-wide">HIPPAMS TIRTO JOYO</span>
              </div>
              
              <!-- Caption Overlay -->
              <div class="absolute bottom-0 left-0 right-0 p-5 text-white transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
                <h4 class="font-bold text-lg mb-1 drop-shadow-lg">{{ item.judul }}</h4>
                <p class="text-sm text-white/90 line-clamp-2 drop-shadow-md">{{ item.caption || item.description }}</p>
              </div>
            </div>
          </SwiperSlide>
        </Swiper>
      </div>

      <!-- View More Link -->
      <div class="text-center" data-aos="fade-up" data-aos-delay="200">
        <RouterLink to="/gallery" class="inline-flex items-center gap-2 text-primary font-bold text-lg hover:text-teal-700 transition-colors group">
          Lihat gambar lainnya
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 transition-transform group-hover:translate-x-1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" />
          </svg>
        </RouterLink>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Swiper Carousel Styling */
.gallery-swiper {
  --swiper-theme-color: #2EC4B6;
  --swiper-pagination-bullet-size: 10px;
  --swiper-pagination-bullet-inactive-color: #CBD5E1;
  --swiper-pagination-bullet-inactive-opacity: 1;
  --swiper-navigation-size: 20px;
}

/* Navigation Arrows */
.gallery-swiper :deep(.swiper-button-prev),
.gallery-swiper :deep(.swiper-button-next) {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.gallery-swiper :deep(.swiper-button-prev):hover,
.gallery-swiper :deep(.swiper-button-next):hover {
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
  transform: scale(1.3);
}

.gallery-swiper :deep(.swiper-button-prev)::after,
.gallery-swiper :deep(.swiper-button-next)::after {
  font-size: 14px;
  font-weight: bold;
  color: #104F47;
}

/* Hide arrows on mobile */
@media (max-width: 640px) {
  .gallery-swiper :deep(.swiper-button-prev),
  .gallery-swiper :deep(.swiper-button-next) {
    display: none;
  }
}

/* Pagination */
.gallery-swiper :deep(.swiper-pagination) {
  bottom: 0 !important;
  position: relative;
  margin-top: 20px;
}

.gallery-swiper :deep(.swiper-pagination-bullet) {
  transition: all 0.3s ease;
  border-radius: 5px;
}

.gallery-swiper :deep(.swiper-pagination-bullet-active) {
  width: 28px;
  border-radius: 5px;
  background: linear-gradient(90deg, #2EC4B6, #104F47);
}

/* Line clamp for description */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
