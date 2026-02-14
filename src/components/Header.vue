<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'

const emit = defineEmits(['login'])
const route = useRoute()

const isMenuOpen = ref(false)

// Check if currently on profil page
const isProfilPage = computed(() => route.path === '/profil')

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value
}

const scrollToSection = (sectionId) => {
  const element = document.querySelector(sectionId)
  if (element) {
    const offset = 80 // navbar height
    const elementPosition = element.getBoundingClientRect().top
    const offsetPosition = elementPosition + window.pageYOffset - offset
    
    window.scrollTo({
      top: offsetPosition,
      behavior: 'smooth'
    })
  }
  // Close mobile menu after clicking
  isMenuOpen.value = false
}
</script>

<template>
  <nav class="bg-white/10 backdrop-blur-md fixed w-full z-50 shadow-sm border-gray-100 transition-all duration-300">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-20">
        <!-- Logo -->
        <RouterLink to="/" class="flex items-center gap-3 group">
          <img src="@/assets/logo.png" alt="Logo HIPPAMS" class="w-12 h-12 object-contain group-hover:scale-105 transition-transform duration-300">
          <span class="font-bold text-2xl text-gray-800 tracking-tight">HIPPAMS <span class="text-primary">Tirto Joyo</span></span>
        </RouterLink>

        <!-- Desktop Menu -->
        <div class="hidden md:flex items-center gap-8 font-medium">
          <a @click.prevent="scrollToSection('#home')" href="#home" class="hover:text-[#2EC4B6] transition-colors cursor-pointer">Beranda</a>
          <RouterLink 
            to="/profil" 
            class="relative transition-colors cursor-pointer"
            :class="isProfilPage ? 'text-[#2EC4B6] font-semibold' : 'hover:text-[#2EC4B6]'"
          >
            Profil
            <span v-if="isProfilPage" class="absolute -bottom-1 left-0 w-full h-0.5 bg-[#2EC4B6] rounded-full"></span>
          </RouterLink>
          <RouterLink to="/gallery" class="hover:text-[#2EC4B6] transition-colors cursor-pointer">Galeri</RouterLink>
          <RouterLink to="/statistik" class="hover:text-[#2EC4B6] transition-colors cursor-pointer">Statistik</RouterLink>
          <a @click.prevent="scrollToSection('#tarif')" href="#tarif" class="hover:text-[#2EC4B6] transition-colors cursor-pointer">Tarif</a>
          <a @click.prevent="scrollToSection('#check-bill')" href="#check-bill" class="hover:text-[#2EC4B6] transition-colors cursor-pointer">Cek Tagihan</a>
          <button @click="emit('login')" class="bg-[#2EC4B6] text-white px-6 py-2 rounded-full hover:bg-opacity-90 transition-all shadow-lg shadow-[#2EC4B6]/20">
            Login Admin
          </button>
        </div>

        <!-- Mobile Menu Button -->
        <div class="md:hidden flex items-center">
          <button @click="toggleMenu" class="text-gray-600 hover:text-primary focus:outline-none p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="!isMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div v-show="isMenuOpen" class="md:hidden bg-white border-t border-gray-100 absolute w-full shadow-xl">
      <div class="px-4 pt-2 pb-6 space-y-2">
        <a @click.prevent="scrollToSection('#home')" href="#home" class="block px-4 py-3 text-gray-600 hover:bg-frozen/30 hover:text-primary rounded-lg font-medium cursor-pointer">Beranda</a>
        <RouterLink 
          to="/profil" 
          @click="toggleMenu()"
          class="block px-4 py-3 rounded-lg font-medium cursor-pointer"
          :class="isProfilPage ? 'bg-[#2EC4B6]/10 text-[#2EC4B6]' : 'text-gray-600 hover:bg-frozen/30 hover:text-primary'"
        >
          Profil
        </RouterLink>
        <RouterLink to="/gallery" @click="toggleMenu()" class="block px-4 py-3 text-gray-600 hover:bg-frozen/30 hover:text-primary rounded-lg font-medium cursor-pointer">Galeri</RouterLink>
        <RouterLink to="/statistik" @click="toggleMenu()" class="block px-4 py-3 text-gray-600 hover:bg-frozen/30 hover:text-primary rounded-lg font-medium cursor-pointer">Statistik</RouterLink>
        <a @click.prevent="scrollToSection('#tarif')" href="#tarif" class="block px-4 py-3 text-gray-600 hover:bg-frozen/30 hover:text-primary rounded-lg font-medium cursor-pointer">Tarif</a>
        <a @click.prevent="scrollToSection('#check-bill')" href="#check-bill" class="block px-4 py-3 text-gray-600 hover:bg-frozen/30 hover:text-primary rounded-lg font-medium cursor-pointer">Cek Tagihan</a>
        <div class="pt-4">
          <button @click="emit('login'); toggleMenu()" class="block w-full text-center bg-primary text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-primary/30">Login Admin</button>
        </div>
      </div>
    </div>
  </nav>
</template>
