<script setup>
import { ref, onMounted } from 'vue'
import { ChevronLeftIcon, BuildingOffice2Icon, MapPinIcon, UserGroupIcon } from '@heroicons/vue/24/outline'

// State
const pengurus = ref([])
const loading = ref(true)

// Fallback data (jika API belum tersedia)
const fallbackPengurus = [
  { id: 1, nama: 'ACH. HABIBURROHMAN', jabatan: 'Ketua', foto_url: '/uploads/pengurus/ketua.png' },
  { id: 2, nama: 'M. ROFI\'I', jabatan: 'Bendahara', foto_url: '/uploads/pengurus/bendahara.png' },
  { id: 3, nama: 'M. SHOFIL MUBARRID', jabatan: 'Sekretaris', foto_url: '/uploads/pengurus/sekretaris.png' },
  { id: 4, nama: 'ALI MA\'RUF', jabatan: 'Teknisi Lapangan', foto_url: '/uploads/pengurus/teknisi_1.png' },
  { id: 5, nama: 'UBAIDILLAH', jabatan: 'Teknisi Lapangan', foto_url: '/uploads/pengurus/teknisi_2.png' },
  { id: 6, nama: 'HUSNAN MUBAROK', jabatan: 'Teknisi Lapangan', foto_url: '/uploads/pengurus/teknisi_3.png' },
]

// Fetch Data Pengurus
const fetchPengurus = async () => {
  try {
    const res = await fetch('/api/konten.php?action=pengurus')
    const data = await res.json()
    if (data.success && data.data.length > 0) {
      pengurus.value = data.data.filter(item => item.is_active == 1)
    } else {
      pengurus.value = fallbackPengurus
    }
  } catch (e) {
    console.error('Error fetching pengurus:', e)
    pengurus.value = fallbackPengurus
  } finally {
    loading.value = false
  }
}

// Get initials for avatar placeholder
const getInitials = (nama) => {
  if (!nama || nama === '-') return '?'
  const parts = nama.split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase()
  }
  return nama.substring(0, 2).toUpperCase()
}

// Get random gradient for avatar
const getAvatarGradient = (index) => {
  const gradients = [
    'from-teal-400 to-cyan-500',
    'from-emerald-400 to-teal-500',
    'from-cyan-400 to-blue-500',
    'from-green-400 to-emerald-500',
  ]
  return gradients[index % gradients.length]
}

onMounted(fetchPengurus)
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="relative bg-gradient-to-br from-[#2EC4B6] via-[#20B2AA] to-[#1a9a8f] py-24 md:py-32 text-white overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0 bg-pattern"></div>
      </div>
      
      <!-- Decorative Elements -->
      <div class="absolute top-20 left-10 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
      <div class="absolute bottom-10 right-20 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
      
      <!-- Back Link -->
      <div class="absolute top-6 left-4 md:left-10 z-20">
        <RouterLink to="/" class="inline-flex items-center gap-2 text-white/90 hover:text-white transition-colors bg-white/10 backdrop-blur-md px-4 py-2 rounded-full text-sm font-medium hover:bg-white/20 border border-white/20">
          <ChevronLeftIcon class="w-4 h-4" />
          Kembali ke Beranda
        </RouterLink>
      </div>

      <!-- Hero Content -->
      <div class="container mx-auto px-4 relative z-10 text-center">
        <div class="max-w-4xl mx-auto" data-aos="fade-up">
          <span class="inline-block px-4 py-2 bg-white/15 backdrop-blur-sm rounded-full text-sm font-medium mb-6 border border-white/20">
            Himpunan Penduduk Pengguna Air Minum dan Sanitasi
          </span>
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
            Profil <span class="text-yellow-300">HIPPAMS</span><br class="hidden sm:block"> TIRTO JOYO
          </h1>
          <p class="text-lg md:text-xl text-white/90 max-w-3xl mx-auto leading-relaxed">
            Melayani kebutuhan air bersih masyarakat <strong>Dusun Gempolpayung, Desa Gempoltukmloko,</strong> Kecamatan Sarirejo dengan komitmen pelayanan prima dan transparansi pengelolaan.
          </p>
        </div>
      </div>

      <!-- Wave Divider -->
      <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto">
          <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#f9fafb"/>
        </svg>
      </div>
    </div>

    <!-- Visi & Misi Section -->
    <section class="py-16 md:py-20 bg-gray-50">
      <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-8 max-w-6xl mx-auto">
          <!-- Visi -->
          <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-300" data-aos="fade-right">
            <div class="w-14 h-14 bg-gradient-to-br from-[#2EC4B6] to-[#20B2AA] rounded-xl flex items-center justify-center mb-6">
              <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Visi</h3>
            <p class="text-gray-600 leading-relaxed">
              Menjadi lembaga pengelola air bersih yang <strong class="text-[#2EC4B6]">terpercaya, transparan, dan berkelanjutan</strong> dalam menyediakan akses air bersih untuk seluruh masyarakat desa.
            </p>
          </div>

          <!-- Misi -->
          <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-300" data-aos="fade-left">
            <div class="w-14 h-14 bg-gradient-to-br from-emerald-400 to-green-500 rounded-xl flex items-center justify-center mb-6">
              <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Misi</h3>
            <ul class="text-gray-600 space-y-3">
              <li class="flex items-start gap-3">
                <span class="w-2 h-2 bg-[#2EC4B6] rounded-full mt-2 flex-shrink-0"></span>
                <span>Menyediakan layanan air bersih dengan kualitas terbaik dan harga terjangkau</span>
              </li>
              <li class="flex items-start gap-3">
                <span class="w-2 h-2 bg-[#2EC4B6] rounded-full mt-2 flex-shrink-0"></span>
                <span>Mengelola infrastruktur air secara profesional dan bertanggung jawab</span>
              </li>
              <li class="flex items-start gap-3">
                <span class="w-2 h-2 bg-[#2EC4B6] rounded-full mt-2 flex-shrink-0"></span>
                <span>Menerapkan sistem digital untuk transparansi penagihan dan pelaporan</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Struktur Organisasi Section -->
    <section class="py-16 md:py-20 bg-white">
      <div class="container mx-auto px-4">
        <div class="text-center max-w-3xl mx-auto mb-12" data-aos="fade-up">
          <span class="inline-block px-4 py-2 bg-[#2EC4B6]/10 text-[#2EC4B6] rounded-full text-sm font-semibold mb-4">
            <UserGroupIcon class="w-4 h-4 inline mr-1" />
            Kepengurusan
          </span>
          <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Struktur Organisasi</h2>
          <p class="text-gray-600">
            Pengurus HIPPAMS TIRTO JOYO yang bertanggung jawab mengelola layanan air bersih untuk masyarakat.
          </p>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center py-16">
          <div class="animate-spin rounded-full h-12 w-12 border-4 border-[#2EC4B6] border-t-transparent"></div>
        </div>

        <!-- Pengurus Cards -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 max-w-6xl mx-auto">
          <div 
            v-for="(item, index) in pengurus" 
            :key="item.id"
            class="bg-white rounded-2xl p-6 shadow-md hover:shadow-xl transition-all duration-300 border border-gray-100 text-center group"
            data-aos="fade-up"
            :data-aos-delay="index * 100"
          >
            <!-- Avatar -->
            <div class="relative inline-block mb-4">
              <div 
                v-if="item.foto_url"
                class="w-24 h-24 rounded-full overflow-hidden ring-4 ring-[#2EC4B6]/20 group-hover:ring-[#2EC4B6]/40 transition-all mx-auto"
              >
                <img :src="item.foto_url" :alt="item.nama" class="w-full h-full object-cover" />
              </div>
              <div 
                v-else
                class="w-24 h-24 rounded-full flex items-center justify-center text-white text-2xl font-bold ring-4 ring-white shadow-lg mx-auto bg-gradient-to-br"
                :class="getAvatarGradient(index)"
              >
                {{ getInitials(item.nama) }}
              </div>
              <!-- Badge -->
              <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 px-3 py-1 bg-[#2EC4B6] text-white text-xs font-semibold rounded-full shadow-lg whitespace-nowrap">
                {{ item.jabatan }}
              </div>
            </div>

            <!-- Info -->
            <h3 class="text-lg font-bold text-gray-800 mt-4 mb-2">{{ item.nama }}</h3>
            
            <!-- Social Media Icons -->
            <div class="flex justify-center gap-3 mt-3">
              <!-- WhatsApp -->
              <a href="#" class="text-gray-400 hover:text-green-500 transition-colors">
                <span class="sr-only">WhatsApp</span>
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.13 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2zM12.05 20.21c-1.5 0-2.97-.39-4.27-1.14l-.3-.18-3.14.82.84-3.06-.19-.32a8.216 8.216 0 01-1.07-4.42c0-4.52 3.68-8.2 8.2-8.2 2.19 0 4.25.86 5.8 2.4 1.55 1.55 2.41 3.61 2.41 5.8 0 4.52-3.68 8.2-8.2 8.2h-.03z"/>
                  <path d="M16.53 14.15c-.24-.12-1.43-.71-1.65-.79-.22-.08-.39-.12-.54.12-.17.24-.65.79-.79.95-.15.16-.29.18-.53.06-.24-.12-1.02-.38-1.94-1.2-7.2-.64-1.2-1.08-1.35-1.32-.14-.24-.01-.36.1-.48.11-.11.24-.29.36-.43.12-.14.16-.24.24-.4.08-.16.03-.3-.02-.42-.05-.11-.53-1.28-.73-1.75-.19-.46-.39-.39-.54-.4-.14 0-.3-.01-.46-.01-.16 0-.42.06-.64.3-.22.24-.85.83-.85 2.02s.87 2.34.99 2.5c.12.16 1.71 2.61 4.15 3.66 1.45.62 2 .5 2.73.47.8-.04 1.83-.75 2.09-1.47.26-.72.26-1.34.18-1.48-.08-.14-.3-.22-.54-.34z"/>
                </svg>
              </a>

              <!-- Facebook -->
              <a href="#" class="text-gray-400 hover:text-blue-600 transition-colors">
                <span class="sr-only">Facebook</span>
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                </svg>
              </a>

              <!-- Instagram -->
              <a href="#" class="text-gray-400 hover:text-pink-500 transition-colors">
                <span class="sr-only">Instagram</span>
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772 4.902 4.902 0 011.772-1.153c.636-.247 1.363-.416 2.427-.465 1.067-.047 1.409-.06 4.123-.06h.08zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                </svg>
              </a>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && pengurus.length === 0" class="text-center py-16">
          <UserGroupIcon class="w-20 h-20 text-gray-300 mx-auto mb-4" />
          <h3 class="text-xl font-medium text-gray-500">Belum ada data pengurus</h3>
        </div>
      </div>
    </section>

    <!-- Sejarah & Lokasi Section -->
    <section class="py-16 md:py-20 bg-gradient-to-br from-gray-50 to-gray-100">
      <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-12 max-w-6xl mx-auto items-center">
          <!-- Sejarah -->
          <div data-aos="fade-right">
            <span class="inline-block px-4 py-2 bg-[#2EC4B6]/10 text-[#2EC4B6] rounded-full text-sm font-semibold mb-4">
              <BuildingOffice2Icon class="w-4 h-4 inline mr-1" />
              Sejarah
            </span>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-6">Tentang Kami</h2>
            <div class="prose prose-gray max-w-none">
              <p class="text-gray-600 leading-relaxed mb-4">
                <strong class="text-gray-800">HIPPAMS TIRTO JOYO</strong> adalah Himpunan Penduduk Pengguna Air Minum dan Sanitasi yang didirikan untuk mengelola dan menyediakan layanan air bersih bagi masyarakat di wilayah Dusun Gempolpayung dan sekitarnya.
              </p>
              <p class="text-gray-600 leading-relaxed mb-4">
                Dengan semangat gotong royong dan transparansi, kami berkomitmen untuk memberikan pelayanan terbaik dalam penyediaan air bersih dengan tarif yang terjangkau dan sistem pengelolaan yang modern.
              </p>
              <p class="text-gray-600 leading-relaxed">
                Melalui penerapan sistem digital, kami memastikan setiap transaksi tercatat dengan baik dan pelanggan dapat dengan mudah memantau tagihan serta penggunaan air mereka.
              </p>
            </div>
          </div>

          <!-- Lokasi -->
          <div data-aos="fade-left">
            <div class="bg-white rounded-2xl p-8 shadow-xl border border-gray-100">
              <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 bg-gradient-to-br from-[#2EC4B6] to-[#20B2AA] rounded-xl flex items-center justify-center">
                  <MapPinIcon class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h3 class="text-xl font-bold text-gray-800">Lokasi Kantor</h3>
                  <p class="text-sm text-gray-500">Area Layanan</p>
                </div>
              </div>
              
              <div class="space-y-4">
                <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl">
                  <div class="w-10 h-10 bg-[#2EC4B6]/10 rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="text-[#2EC4B6] font-bold">📍</span>
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-800">Alamat Kantor</h4>
                    <p class="text-gray-600 text-sm">Dusun Gempolpayung, Desa Gempoltukmloko<br>Kecamatan Sarirejo, Kabupaten Lamongan</p>
                  </div>
                </div>

                <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl">
                  <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="text-emerald-500 font-bold">🏘️</span>
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-800">Wilayah Layanan</h4>
                    <p class="text-gray-600 text-sm">
                      Dusun Gempolpayung, Desa Gempoltukmloko, Kecamatan Sarirejo, Kabupaten Lamongan
                    </p>
                  </div>
                </div>

                <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl">
                  <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="text-blue-500 font-bold">⏰</span>
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-800">Jam Operasional</h4>
                    <p class="text-gray-600 text-sm">Senin - Jumat: 08:00 - 16:00 WIB<br>Sabtu: 08:00 - 12:00 WIB</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer CTA -->
    <section class="py-16 bg-gradient-to-r from-[#2EC4B6] to-[#20B2AA] text-white">
      <div class="container mx-auto px-4 text-center" data-aos="fade-up">
        <h2 class="text-2xl md:text-3xl font-bold mb-4">Ada Pertanyaan?</h2>
        <p class="text-white/90 mb-8 max-w-xl mx-auto">
          Hubungi kantor HIPPAMS TIRTO JOYO untuk informasi lebih lanjut tentang layanan air bersih di desa Anda.
        </p>
        <RouterLink 
          to="/" 
          class="inline-flex items-center gap-2 bg-white text-[#2EC4B6] px-8 py-4 rounded-full font-bold hover:bg-gray-100 transition-colors shadow-xl"
        >
          <ChevronLeftIcon class="w-5 h-5" />
          Kembali ke Beranda
        </RouterLink>
      </div>
    </section>
  </div>
</template>

<style scoped>
.bg-pattern {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

/* Fade-up animation for AOS-like effect */
[data-aos="fade-up"] {
  animation: fadeUp 0.6s ease-out forwards;
}

[data-aos="fade-right"] {
  animation: fadeRight 0.6s ease-out forwards;
}

[data-aos="fade-left"] {
  animation: fadeLeft 0.6s ease-out forwards;
}

@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeRight {
  from {
    opacity: 0;
    transform: translateX(-30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes fadeLeft {
  from {
    opacity: 0;
    transform: translateX(30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}
</style>
