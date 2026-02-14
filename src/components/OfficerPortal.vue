<script setup>
import { RouterLink } from 'vue-router'

const features = [
  {
    title: 'Input Meter Cepat',
    desc: 'Input angka meteran pelanggan dengan cepat dan validasi otomatis.',
    icon: 'lightning',
  },
  {
    title: 'Validasi Foto',
    desc: 'Wajib upload foto meteran sebagai bukti fisik untuk menghindari sengketa.',
    icon: 'camera',
  },
  {
    title: 'Geotagging Akurat',
    desc: 'Sistem mencatat lokasi GPS petugas saat input data untuk memastikan keaslian.',
    icon: 'map',
  },
  {
    title: 'Mode Offline',
    desc: 'Tetap bisa input data di area susah sinyal, data sinkron otomatis saat online.',
    icon: 'wifi-off',
  },
]
</script>

<template>
  <section id="officer-portal" class="py-24 relative overflow-hidden">
    <!-- Decorative Elements -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-white/40 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
    <div class="absolute bottom-0 left-0 w-64 h-64 bg-white/40 rounded-full blur-3xl translate-y-1/2 -translate-x-1/3"></div>

    <div class="container mx-auto px-4 relative z-10">
      <div class="flex flex-col lg:flex-row items-center gap-16">
        <!-- Content Side -->
        <div class="lg:w-1/2" data-aos="fade-right">
          <span class="inline-block py-1 px-3 rounded-lg bg-primary/20 text-primary font-bold text-xs tracking-wider uppercase mb-6 border border-primary/20">
            Khusus Petugas Lapangan
          </span>
          <h2 class="text-4xl lg:text-6xl font-display font-bold mb-6 leading-tight">
            Portal Petugas <br> <span class="text-primary">Pencatat Meter</span>
          </h2>
          <p class="text-gray-600 text-lg mb-8 leading-relaxed">
            Aplikasi khusus untuk petugas lapangan BUMDes. Catat meteran air warga dengan lebih akurat, cepat, dan transparan menggunakan teknologi validasi digital.
          </p>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div 
              v-for="(feature, index) in features" 
              :key="index"
              class="flex gap-4 p-4 rounded-xl bg-white/60 border border-white/50 hover:bg-white hover:shadow-lg transition-all duration-300"
            >
              <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-primary/20 flex items-center justify-center text-primary">
                <!-- Icons -->
                <svg v-if="feature.icon === 'lightning'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <svg v-if="feature.icon === 'camera'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <svg v-if="feature.icon === 'map'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <svg v-if="feature.icon === 'wifi-off'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a 2 2 0 01-2.829-2.829m-2.828 2.828l2.829-2.829"></path></svg>
              </div>
              <div>
                <h4 class="font-bold text-gray-900 mb-1">{{ feature.title }}</h4>
                <p class="text-xs text-gray-500 leading-relaxed">{{ feature.desc }}</p>
              </div>
            </div>
          </div>
          
          <div class="mt-8">
            <RouterLink to="/login" class="inline-flex items-center gap-2 text-primary font-bold hover:text-teal-700 transition-colors">
              Login Petugas
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </RouterLink>
          </div>
        </div>

        <!-- Visual Side -->
        <div class="lg:w-1/2 flex justify-center lg:justify-end" data-aos="fade-left">
          <div class="relative w-64 h-[500px] bg-gray-900 rounded-[2.5rem] border-[8px] border-gray-800 shadow-2xl overflow-hidden">
            <!-- Mockup Status Bar -->
            <div class="h-6 bg-gray-900 flex justify-between items-center px-4">
              <div class="text-[10px] text-white font-medium">09:41</div>
              <div class="flex gap-1">
                <div class="w-3 h-3 bg-white rounded-full opacity-20"></div>
                <div class="w-3 h-3 bg-white rounded-full opacity-20"></div>
                <div class="w-3 h-3 bg-white rounded-full"></div>
              </div>
            </div>
            
            <!-- App Content -->
            <div class="bg-gray-100 h-full relative">
              <!-- App Header -->
              <div class="bg-primary p-4 pb-8 rounded-b-2xl">
                <div class="flex justify-between items-center text-white mb-4">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                  <div class="w-8 h-8 rounded-full bg-white/20"></div>
                </div>
                <div class="text-white">
                  <div class="text-xs opacity-80">Halo, Petugas</div>
                  <div class="font-bold text-lg">HIPPAMS Tirto Joyo</div>
                </div>
              </div>

              <!-- Floating Card -->
              <div class="absolute top-28 left-4 right-4 bg-white p-4 rounded-xl shadow-lg">
                <div class="flex justify-between items-center mb-2">
                  <div class="text-xs text-gray-400 font-bold uppercase">Pelanggan Saat Ini</div>
                  <div class="bg-green-100 text-green-600 text-[10px] px-2 py-0.5 rounded-full font-bold">Online</div>
                </div>
                <div class="font-bold text-gray-800">Davina Karamoy</div>
                <div class="text-xs text-gray-500 mb-4">Gempolpayung, RT 01/01</div>
                
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 flex justify-between items-center">
                  <div>
                    <div class="text-[10px] text-gray-400">Meter Bulan Lalu</div>
                    <div class="font-mono font-bold text-gray-800">1,240</div>
                  </div>
                  <div class="text-right">
                    <div class="text-[10px] text-gray-400">Meter Sekarang</div>
                    <div class="font-mono font-bold text-primary text-lg">1,255</div>
                  </div>
                </div>
              </div>

              <!-- App Actions -->
              <div class="absolute bottom-10 left-4 right-4">
                 <button class="w-full bg-primary text-white py-3 rounded-xl font-bold text-sm shadow-lg shadow-primary/30">
                   Simpan & Lanjut
                 </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
