<script setup>
import { ref, onMounted } from 'vue'
import { CurrencyDollarIcon, CheckIcon } from '@heroicons/vue/24/outline'

const isLoading = ref(true)
const tarifData = ref([])

const API_BASE = '/api/public_api.php'

const fetchTarif = async () => {
  try {
    const response = await fetch(`${API_BASE}?action=tarif`)
    const data = await response.json()
    
    if (data.success) {
      tarifData.value = data.data
    }
  } catch (error) {
    console.error('Failed to fetch tarif:', error)
  } finally {
    isLoading.value = false
  }
}

const formatRupiah = (value) => {
  return 'Rp ' + parseFloat(value).toLocaleString('id-ID')
}

onMounted(() => {
  fetchTarif()
})
</script>

<template>
  <section id="tarif" class="py-20 bg-gradient-to-b from-white to-frozen/30">
    <div class="container mx-auto px-4">
      <div class="text-center max-w-2xl mx-auto mb-16" data-aos="fade-up">
        <span class="inline-block py-1 px-3 rounded-full bg-primary/10 text-primary font-bold text-sm mb-4">
          💰 Informasi Tarif
        </span>
        <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Tarif Pemakaian Air</h2>
        <p class="text-gray-600">Tarif progresif berdasarkan penggunaan. Semakin hemat, semakin murah!</p>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- Tarif Cards -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div 
          v-for="(tarif, index) in tarifData" 
          :key="tarif.golongan"
          class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow duration-300 group"
          data-aos="fade-up"
          :data-aos-delay="index * 100"
        >
          <!-- Header -->
          <div class="bg-gradient-to-r from-primary to-teal-500 p-6 text-white group-hover:from-teal-500 group-hover:to-primary transition-all duration-500">
            <div class="text-xs font-bold uppercase tracking-wider opacity-80 mb-1">{{ tarif.golongan }}</div>
            <h3 class="text-xl font-bold mb-1">{{ tarif.nama }}</h3>
            <p class="text-sm opacity-80">{{ tarif.deskripsi }}</p>
          </div>
          
          <!-- Pricing -->
          <div class="p-6">
            <div class="space-y-3 mb-6">
              <div 
                v-for="blok in tarif.blok" 
                :key="blok.range"
                class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0"
              >
                <div class="flex items-center gap-2">
                  <CheckIcon class="w-4 h-4 text-primary" />
                  <span class="text-gray-600 text-sm">{{ blok.range }}</span>
                </div>
                <span class="font-bold text-gray-900">{{ formatRupiah(blok.harga) }}/m³</span>
              </div>
            </div>
            
            <!-- Admin Fee -->
            <div class="bg-frozen/50 rounded-xl p-4 text-center">
              <div class="text-gray-500 text-xs uppercase tracking-wider mb-1">Biaya Admin</div>
              <div class="font-bold text-primary text-lg">{{ formatRupiah(tarif.admin) }}/bulan</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Info Box -->
      <div class="mt-12 bg-white rounded-2xl border border-gray-100 p-8 shadow-sm" data-aos="fade-up">
        <div class="flex flex-col md:flex-row items-center gap-6">
          <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center flex-shrink-0">
            <CurrencyDollarIcon class="w-8 h-8 text-amber-600" />
          </div>
          <div class="text-center md:text-left">
            <h4 class="font-bold text-gray-900 text-lg mb-2">📋 Cara Perhitungan Tagihan</h4>
            <p class="text-gray-600 text-sm">
              Tagihan dihitung secara <strong>progresif</strong>: pemakaian 0-5 m³ dengan tarif terendah, 5-10 m³ tarif menengah, dan diatas 10 m³ tarif tertinggi. 
              Ditambah biaya administrasi bulanan Rp 2.000.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
